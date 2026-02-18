#!/bin/bash

# Check WordPress deployment status
# Usage: ./check-deployment-status.sh [app-id]

APP_ID="${1}"

if [ -z "$APP_ID" ]; then
    echo "Usage: $0 <app-id>"
    echo ""
    echo "Example: $0 7a35c2a3-4c19-4051-87fc-c8fb932586a8"
    exit 1
fi

DO_TOKEN=$(grep "^access-token:" ~/.config/doctl/config.yaml | awk '{print $2}')

if [ -z "$DO_TOKEN" ]; then
    echo "Error: Could not get API token"
    exit 1
fi

echo "Checking deployment status..."
echo ""

curl -s "https://api.digitalocean.com/v2/apps/$APP_ID" \
  -H "Authorization: Bearer $DO_TOKEN" | \
  python3 << 'PYTHON'
import sys, json
try:
    app = json.load(sys.stdin)['app']
    
    print(f"App Name: {app['spec']['name']}")
    print(f"App ID: {app['id']}")
    print(f"Region: {app['region']['slug']}")
    print(f"")
    
    # Get URL
    url = app.get('default_ingress', 'Not assigned yet')
    print(f"URL: {url}")
    print(f"")
    
    # Get deployment status
    if 'active_deployment' in app:
        deploy = app['active_deployment']
        print(f"Deployment Status: {deploy.get('phase', 'UNKNOWN')}")
        print(f"Progress: {deploy.get('progress', {}).get('percent_complete', 0)}%")
        
        if 'created_at' in deploy:
            print(f"Started: {deploy['created_at']}")
        if 'updated_at' in deploy:
            print(f"Updated: {deploy['updated_at']}")
    else:
        print("Deployment Status: NOT STARTED")
    
    print(f"")
    
    # Show service status
    if 'services' in app['spec']:
        print("Services:")
        for svc in app['spec']['services']:
            print(f"  - {svc['name']}: {svc.get('environment_slug', 'N/A')}")
    
    print(f"")
    print(f"Dashboard: https://cloud.digitalocean.com/apps/{app['id']}")
    
except Exception as e:
    print(f"Error parsing response: {e}")
    sys.exit(1)
PYTHON
