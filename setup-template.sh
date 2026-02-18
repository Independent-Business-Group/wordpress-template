#!/bin/bash

# Initial setup script for WordPress template testing

echo "=========================================="
echo "WordPress Template - Initial Test Setup"
echo "=========================================="
echo ""
echo "This script will:"
echo "1. Create a GitHub repository for the template"
echo "2. Create a database for testing"
echo "3. Deploy the template to App Platform"
echo ""
echo "Prerequisites:"
echo "- gh CLI installed and authenticated"
echo "- doctl CLI installed and authenticated"
echo "- MariaDB cluster running"
echo ""

read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 0
fi

echo ""
echo "Step 1: Creating GitHub repository..."
cd /home/cw/Documents/IBG_HUB/wordpress-template

if gh repo view Independent-Business-Group/wordpress-template >/dev/null 2>&1; then
    echo "Repository already exists"
else
    gh repo create Independent-Business-Group/wordpress-template \
        --public \
        --description "WordPress template for App Platform deployments" \
        --source=. \
        --remote=origin \
        --push
fi

echo ""
echo "Step 2: Creating test database..."
./create-wordpress-instance.sh wordpress-template-test

echo ""
echo "Step 3: Ready to deploy"
echo ""
echo "Next steps:"
echo "1. Create new app in App Platform:"
echo "   doctl apps create --spec app.yaml"
echo ""
echo "2. Or use the DigitalOcean dashboard:"
echo "   - Go to App Platform"
echo "   - Create App from GitHub: Independent-Business-Group/wordpress-template"
echo "   - Add environment variables from credentials file"
echo "   - Deploy"
echo ""
echo "=========================================="
