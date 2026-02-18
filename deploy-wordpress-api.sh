#!/bin/bash

# Deploy WordPress instance using DigitalOcean API
# More reliable than doctl for automation
# Usage: ./deploy-wordpress-api.sh <credentials-file>

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

# Get DO API token from doctl config
DO_TOKEN=$(grep "^access-token:" ~/.config/doctl/config.yaml 2>/dev/null | awk '{print $2}')

if [ -z "$DO_TOKEN" ]; then
    echo "Error: Could not get DigitalOcean API token"
    echo "Make sure you're authenticated: doctl auth init"
    exit 1
fi

echo "=========================================="
echo "WordPress Instance Deployment (API)"
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

# Validate
if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Error: Missing required credentials"
    exit 1
fi

# Extract site name
SITE_NAME=$(echo "$DB_NAME" | sed 's/_/-/g')
APP_NAME="wordpress-${SITE_NAME}"

echo "Configuration:"
echo "  App Name: $APP_NAME"
echo "  Database: $DB_NAME"
echo ""

# Create JSON spec for API
APP_SPEC=$(cat <<EOF
{
  "spec": {
    "name": "$APP_NAME",
    "region": "nyc",
    "services": [
      {
        "name": "wordpress",
        "github": {
          "repo": "Independent-Business-Group/wordpress-template",
          "branch": "main",
          "deploy_on_push": true
        },
        "build_command": "chmod +x .do/deploy.sh && ./.do/deploy.sh",
        "run_command": "heroku-php-apache2",
        "environment_slug": "php",
        "envs": [
          {
            "key": "DB_NAME",
            "value": "$DB_NAME",
            "scope": "RUN_AND_BUILD_TIME",
            "type": "SECRET"
          },
          {
            "key": "DB_USER",
            "value": "$DB_USER",
            "scope": "RUN_AND_BUILD_TIME",
            "type": "SECRET"
          },
          {
            "key": "DB_PASSWORD",
            "value": "$DB_PASSWORD",
            "scope": "RUN_AND_BUILD_TIME",
            "type": "SECRET"
          },
          {
            "key": "DB_HOST",
            "value": "$DB_HOST",
            "scope": "RUN_AND_BUILD_TIME",
            "type": "SECRET"
          },
          {
            "key": "DB_PORT",
            "value": "$DB_PORT",
            "scope": "RUN_AND_BUILD_TIME",
            "type": "SECRET"
          },
          {
            "key": "TABLE_PREFIX",
            "value": "$TABLE_PREFIX",
            "scope": "RUN_AND_BUILD_TIME"
          }
        ],
        "health_check": {
          "http_path": "/health.php",
          "port": 8080,
          "initial_delay_seconds": 60,
          "period_seconds": 10,
          "timeout_seconds": 3,
          "success_threshold": 1,
          "failure_threshold": 3
        },
        "http_port": 8080,
        "instance_count": 1,
        "instance_size_slug": "apps-s-1vcpu-0.5gb"
      }
    ]
  }
}
EOF
)

echo "Creating app via DigitalOcean API..."
echo ""

# Make API request
RESPONSE=$(curl -s -X POST \
  "https://api.digitalocean.com/v2/apps" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $DO_TOKEN" \
  -d "$APP_SPEC")

# Save response for debugging
echo "$RESPONSE" > /tmp/do-api-response.json

# Check for errors
if echo "$RESPONSE" | grep -q '"app"'; then
    # Parse using Python for better JSON handling
    APP_ID=$(echo "$RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data.get('app', {}).get('id', ''))" 2>/dev/null || echo "")
    DEFAULT_INGRESS=$(echo "$RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data.get('app', {}).get('default_ingress', ''))" 2>/dev/null || echo "")
    
    if [ -z "$APP_ID" ]; then
        # Fallback to grep
        APP_ID=$(echo "$RESPONSE" | grep -oP '"id"\s*:\s*"\K[^"]+' | head -1)
        DEFAULT_INGRESS=$(echo "$RESPONSE" | grep -oP '"default_ingress"\s*:\s*"\K[^"]+' | head -1)
    fi
    
    echo "✓ App created successfully!"
    echo ""
    echo "=========================================="
    echo "Deployment Details"
    echo "=========================================="
    echo ""
    echo "App ID: $APP_ID"
    echo "App Name: $APP_NAME"
    echo "URL: $DEFAULT_INGRESS"
    echo ""
    echo "Monitor deployment:"
    echo "  https://cloud.digitalocean.com/apps/$APP_ID"
    echo ""
    echo "Or via CLI:"
    echo "  doctl apps get $APP_ID"
    echo "  doctl apps logs $APP_ID --type build"
    echo ""
    echo "Deployment will take 3-5 minutes."
    echo "Once complete, visit the URL to set up WordPress."
    echo ""
    echo "=========================================="
    
    # Save deployment info
    DEPLOY_INFO="deployment-${SITE_NAME}.txt"
    cat > "$DEPLOY_INFO" <<INFO
WordPress Deployment: $SITE_NAME
Created: $(date)

App Details:
App ID: $APP_ID
App Name: $APP_NAME
URL: $DEFAULT_INGRESS
Region: nyc

Database:
DB_NAME: $DB_NAME
DB_USER: $DB_USER
TABLE_PREFIX: $TABLE_PREFIX

Next Steps:
1. Wait for deployment to complete (3-5 minutes)
2. Visit: $DEFAULT_INGRESS
3. Complete WordPress installation
INFO
    
    echo "Deployment info saved to: $DEPLOY_INFO"
    echo ""
else
    echo "✗ Error creating app:"
    echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
    exit 1
fi
