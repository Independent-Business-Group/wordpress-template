# WordPress Content

This directory contains custom WordPress content that persists across deployments.

WordPress core files are downloaded fresh on each deployment, but this wp-content directory is version-controlled.

## Structure

- `themes/` - Custom themes
- `plugins/` - Custom plugins
- `uploads/` - Media files (recommend using DO Spaces for production)

## Uploads Directory

For production, configure DO Spaces to store media files instead of local filesystem.

This prevents media loss during deployments and scales better.
