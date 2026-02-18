#!/bin/bash

# Deploy WordPress instance from template
# Usage: ./deploy-wordpress-instance.sh <credentials-file>

set -e

if [ $# -lt 1 ]; then
    echo "Usage: $0 <credentials-file>"
    echo ""
    echo "Example: $0 credentials-customer-name.txt"
    exit 1
fi

CREDS_FILE="$1"

if [ ! -f "$CREDS_FILE" ]; then
    echo "Error: Credentials file not found: $CREDS_FILE"
    exit 1
fi

echo "=========================================="
echo "WordPress Instance Deployment"
echo "=========================================="
echo ""
echo "Reading credentials from: $CREDS_FILE"
echo ""

# Parse credentials file
DB_NAME=$(grep "^DB_NAME=" "$CREDS_FILE" | cut -d= -f2)
DB_USER=$(grep "^DB_USER=" "$CREDS_FILE" | cut -d= -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" "$CREDS_FILE" | cut -d= -f2)
DB_HOST=$(grep "^DB_HOST=" "$CREDS_FILE" | cut -d= -f2)
DB_PORT=$(grep "^DB_PORT=" "$CREDS_FILE" | cut -d= -f2)
TABLE_PREFIX=$(grep "^TABLE_PREFIX=" "$CREDS_FILE" | cut -d= -f2)

# Validate all credentials are present
if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Error: Missing required credentials in file"
    exit 1
fi

# Extract site name from database name
SITE_NAME=$(echo "$DB_NAME" | sed 's/_/-/g')
APP_NAME="wordpress-${SITE_NAME}"

echo "Configuration:"
echo "  App Name: $APP_NAME"
echo "  Database: $DB_NAME"
echo "  User: $DB_USER"
echo ""

# Create deployment spec
DEPLOY_SPEC="/tmp/${APP_NAME}-spec.yaml"

cat > "$DEPLOY_SPEC" <<EOF
name: $APP_NAME
region: nyc

services:
  - name: wordpress
    github:
      repo: Independent-Business-Group/wordpress-template
      branch: main
      deploy_on_push: true
    build_command: chmod +x .do/deploy.sh && ./.do/deploy.sh
    run_command: heroku-php-apache2
    environment_slug: php
    envs:
      - key: DB_NAME
        value: $DB_NAME
        scope: RUN_AND_BUILD_TIME
        type: SECRET
      - key: DB_USER
        value: $DB_USER
        scope: RUN_AND_BUILD_TIME
        type: SECRET
      - key: DB_PASSWORD
        value: $DB_PASSWORD
        scope: RUN_AND_BUILD_TIME
        type: SECRET
      - key: DB_HOST
        value: $DB_HOST
        scope: RUN_AND_BUILD_TIME
        type: SECRET
      - key: DB_PORT
        value: "$DB_PORT"
        scope: RUN_AND_BUILD_TIME
        type: SECRET
      - key: TABLE_PREFIX
        value: $TABLE_PREFIX
        scope: RUN_AND_BUILD_TIME
    health_check:
      http_path: /
      port: 8080
      initial_delay_seconds: 90
      period_seconds: 10
      timeout_seconds: 5
      success_threshold: 1
      failure_threshold: 3
    http_port: 8080
    instance_count: 1
    instance_size_slug: apps-s-1vcpu-0.5gb
EOF

echo "Deployment spec created: $DEPLOY_SPEC"
echo ""
echo "Creating app on DigitalOcean App Platform..."
echo ""

# Create the app
if ! APP_OUTPUT=$(timeout 60 doctl apps create --spec "$DEPLOY_SPEC" --format ID,Spec.Name,DefaultIngress --no-header 2>&1); then
    echo "Error creating app:"
    echo "$APP_OUTPUT"
    exit 1
fi

# Extract app ID from output
APP_ID=$(echo "$APP_OUTPUT" | awk '{print $1}' | head -1)

if [ -z "$APP_ID" ]; then
    echo "Warning: Could not extract app ID from output"
    echo "$APP_OUTPUT"
    echo ""
    echo "Check apps list manually:"
    echo "  doctl apps list | grep $APP_NAME"
else
    echo "✓ App created successfully!"
    echo ""
    echo "=========================================="
    echo "Deployment Details"
    echo "=========================================="
    echo ""
    echo "App ID: $APP_ID"
    echo "App Name: $APP_NAME"
    echo ""
    echo "Monitor deployment:"
    echo "  doctl apps get $APP_ID"
    echo ""
    echo "View logs:"
    echo "  doctl apps logs $APP_ID --type build"
    echo "  doctl apps logs $APP_ID --type run"
    echo ""
    echo "The app should be available in 3-5 minutes at:"
    echo "  https://${APP_NAME}-<random>.ondigitalocean.app"
    echo ""
    echo "=========================================="
fi

# Clean up spec file
rm -f "$DEPLOY_SPEC"
