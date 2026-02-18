# WordPress Template

A template for deploying fresh WordPress instances on DigitalOcean App Platform.

## Features

- Fresh WordPress installation
- MariaDB database with unique credentials per instance
- DO Spaces integration for media storage
- Health check compatible (port 8080)
- Environment-based configuration
- No hardcoded credentials

## Quick Deploy

### Option 1: Manual Setup (Recommended for first template)

1. **Create database** in your MariaDB cluster:
   ```bash
   ./create-wordpress-instance.sh <site-name>
   ```

  This will:
   - Create a database named `<site-name>`
   - Create a user with full access
   - Generate secure credentials
   - Output environment variables for App Platform

2. **Deploy to App Platform**:
   - Create new app from GitHub repo
   - Set environment variables from step 1
   - Deploy

### Option 2: Automated Provisioning (Future)

Once the template is working, create a provisioning API that:
- Accepts customer signup requests
- Creates database + credentials
- Creates DO Spaces bucket
- Deploys app with generated config
- Returns WordPress site URL

## Architecture

```
┌─────────────────┐
│   App Platform  │
│   (PHP 8.x)     │
│                 │
│  WordPress Core │
│  wp-content/    │
└────────┬────────┘
         │
         ├──────────► MariaDB Cluster
         │            (dedicated database per site)
         │
         └──────────► DO Spaces
                      (media storage)
```

## Files

- `.do/deploy.sh` - Build script (downloads WordPress, creates wp-config.php)
- `app.yaml` - App Platform spec template
- `wp-content/` - Custom themes/plugins (in git)
-  `create-wordpress-instance.sh` - Provision new instance script
- `README.md` - This file

## Environment Variables

Required for each deployment:

- `DB_NAME` - Database name
- `DB_USER` - Database user
- `DB_PASSWORD` - Database password
- `DB_HOST` - Database host
- `DB_PORT` - Database port (usually 25060 for DO managed)
- `TABLE_PREFIX` - WordPress table prefix (e.g., `wp_`)

Optional:

- `DO_SPACES_BUCKET` - Bucket name for media
- `DO_SPACES_KEY` - Spaces access key
- `DO_SPACES_SECRET` - Spaces secret key
- `DO_SPACES_REGION` - Spaces region

## Database Connection

Uses MySQL 5.x legacy authentication:
```sql
CREATE DATABASE sitename;
CREATE USER 'sitename_user'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON sitename.* TO 'sitename_user'@'%';
FLUSH PRIVILEGES;
```

## Health Checks

Configured to check port 8080 (PHP buildpack default):
- Path: `/`
- Initial delay: 90 seconds
- Period: 10 seconds
- Timeout: 5 seconds

## Next Steps

1. Test this template with a single deployment
2. Verify WordPress installation works
3. Create provisioning API
4. Integrate with customer dashboard
