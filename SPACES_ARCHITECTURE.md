# WordPress Spaces-Backed Architecture

## Overview

This WordPress deployment uses DigitalOcean Spaces as persistent storage for `wp-content`, enabling:
- User editing capabilities (plugins, themes, media uploads)
- Persistence across app restarts and redeployments
- Efficient CDN delivery (when CDN mode enabled)
- Cost-effective scalability

## Architecture

### Build Time
1. Download WordPress core (latest from wordpress.org)
2. Download `wp-content` from Spaces using s3cmd
3. Install auto-sync mechanism (mu-plugin + sync script)
4. Deploy to App Platform

### Runtime
1. WordPress runs normally with full editing capabilities
2. WordPress cron syncs changes to Spaces every 5 minutes
3. Immediate sync triggered on plugin/theme installations
4. Next deployment pulls latest `wp-content` from Spaces

## Environment Variables Required

```bash
SPACES_BUCKET=everydaytech-wordpress
SPACES_FOLDER=<site-name>           # e.g., preciseitservices
SPACES_KEY=<DO_SPACES_ACCESS_KEY>
SPACES_SECRET=<DO_SPACES_SECRET_KEY>
```

## File Structure

```
s3://everydaytech-wordpress/
└── <site-name>/
    └── wp-content/
        ├── plugins/         # All plugins disabled by default (.old suffix)
        ├── themes/          # Site themes
        ├── uploads/         # Media uploads (auto-created by WordPress)
        └── mu-plugins/      # Must-use plugins (auto-sync)
```

## Deployment Files

### `.do/deploy.sh`
Downloads WordPress core and `wp-content` from Spaces at build time.

Key sections:
```bash
# Configure s3cmd
cat > /tmp/.s3cfg << EOF
access_key = ${SPACES_KEY}
secret_key = ${SPACES_SECRET}
host_base = syd1.digitaloceanspaces.com
EOF

# Download wp-content from Spaces
s3cmd -c /tmp/.s3cfg sync \
    s3://${SPACES_BUCKET}/${SPACES_FOLDER}/wp-content/ \
    wp-content/
```

### `sync-to-spaces.php`
PHP CLI script that syncs local `wp-content` changes back to Spaces.

Syncs:
- `wp-content/uploads/` (media files)
- `wp-content/plugins/` (installed plugins)
- `wp-content/themes/` (installed themes)

Excludes:
- `*.log` (log files)
- `cache/*` (cache directories)

### `wp-content/mu-plugins/do-spaces-sync.php`
WordPress must-use plugin that automatically triggers syncs.

Features:
- 5-minute cron schedule for regular syncs
- Immediate sync on plugin/theme installation
- Admin notice showing sync status

## Security Considerations

### CDN Mode
- **DISABLED** for `wp-content` storage (contains config files, plugin code)
- Enable CDN only for specific paths if needed (e.g., `uploads/` only)
- Never enable CDN on folders containing PHP files or sensitive data

### ACL Settings
- Bucket ACL: Private by default
- Files uploaded by WordPress: Public read (for serving assets)
- Plugin/theme files: Can be private (served through WordPress)

## Setting Up a New Site

### 1. Prepare Spaces Folder

```bash
# Create base structure
SITE_NAME="newsite"
s3cmd mkdir s3://everydaytech-wordpress/${SITE_NAME}/
s3cmd mkdir s3://everydaytech-wordpress/${SITE_NAME}/wp-content/
s3cmd mkdir s3://everydaytech-wordpress/${SITE_NAME}/wp-content/plugins/
s3cmd mkdir s3://everydaytech-wordpress/${SITE_NAME}/wp-content/themes/
s3cmd mkdir s3://everydaytech-wordpress/${SITE_NAME}/wp-content/uploads/

# Disable all plugins by default (safety measure)
# Plugins are enabled after initial setup via WordPress admin
```

### 2. Deploy via Backend API

The backend `/api/wordpress/create` endpoint automatically:
- Creates MySQL database
- Sets up Spaces environment variables
- Deploys from `wordpress-template` repo
- Downloads `wp-content` from Spaces

### 3. Initial WordPress Setup

1. Visit site URL
2. Complete WordPress installation wizard
3. Log in to admin panel
4. Enable desired plugins from Plugins → Installed Plugins
5. Changes sync automatically to Spaces

## Migrating Existing Sites

### 1. Export wp-content from WHM

```bash
# SSH to WHM server
ssh -i ~/key.pem centos@server.com

# Compress wp-content
cd /home/<cPanel-user>/public_html
sudo tar czf /tmp/wp-content.tar.gz wp-content
sudo chown centos:centos /tmp/wp-content.tar.gz
```

### 2. Download and Upload to Spaces

```bash
# Download from WHM
scp -i ~/key.pem centos@server.com:/tmp/wp-content.tar.gz ./

# Extract locally
tar xzf wp-content.tar.gz

# Disable all plugins initially
cd wp-content/plugins
for dir in */; do
  mv "$dir" "${dir%.old/}.old/"
done

# Upload to Spaces
s3cmd sync --acl-public wp-content/ \
  s3://everydaytech-wordpress/<site-name>/wp-content/
```

### 3. Deploy WordPress App

Use backend API or manual deployment with environment variables set.

### 4. Enable Required Plugins

After successful deployment:
1. Log in to WordPress admin
2. Go to Plugins → Installed Plugins
3. Rename required plugins from `.old` back to original name via Spaces
4. Activate in WordPress admin
5. Test functionality

## Troubleshooting

### Site shows 500 error
- **Check runtime logs**: `doctl apps logs <app-id> --type run`
- **Common cause**: Broken plugin with missing dependencies
- **Solution**: Disable problematic plugin (rename to `.old` in Spaces), redeploy

### wp-content not loading
- **Check build logs**: `doctl apps logs <app-id> --type build`
- **Look for**: "Downloading wp-content from Spaces" message
- **Verify**: Environment variables (SPACES_*) are set correctly
- **Check**: Spaces folder exists and has content

### Sync not working
- **Check**: WordPress cron is running (`wp cron event list`)
- **Manual sync**: SSH to app console, run `php sync-to-spaces.php`
- **Verify**: s3cmd credentials in environment
- **Check**: Spaces permissions allow write access

### Changes not persisting
- **Verify**: Auto-sync plugin is active (mu-plugins/do-spaces-sync.php)
- **Check**: Admin notice shows sync status
- **Wait**: 5 minutes for next scheduled sync
- **Trigger**: Install/update a plugin to trigger immediately

## Monitoring

### Check Sync Status
```bash
# View WordPress cron schedule
wp cron event list

# View last sync time (from admin notice in WordPress)
# Or check Spaces last-modified timestamps
s3cmd ls -l s3://everydaytech-wordpress/<site-name>/wp-content/plugins/
```

### Verify Spaces Content
```bash
# List all files
s3cmd ls -r s3://everydaytech-wordpress/<site-name>/

# Check file count
s3cmd ls -r s3://everydaytech-wordpress/<site-name>/ | wc -l

# Check total size
s3cmd du s3://everydaytech-wordpress/<site-name>/
```

## Cost Optimization

- Spaces: $5/month for 250GB + transfer
- Files served via Spaces (no App Platform egress)
- CDN disabled = lower cost, higher security
- Enable CDN for `uploads/` only if high traffic

## Benefits

1. **Persistent Storage**: Changes survive app restarts
2. **User Editing**: Full WordPress capabilities maintained
3. **Scalability**: Add/remove apps without data loss
4. **Backup**: Spaces acts as primary backup
5. **Migration**: Easy to export entire wp-content
6. **Security**: Plugin/theme isolation, controlled access
7. **Cost-Effective**: No persistent volumes needed

## Future Enhancements

- Automated backups to separate Spaces bucket
- Read-only mode for staging environments
- Selective sync (uploads only, plugins separately)
- Compression for large media files
- Multi-region replication for DR
