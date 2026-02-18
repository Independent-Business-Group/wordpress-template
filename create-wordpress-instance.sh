#!/bin/bash

# Script to provision a new WordPress instance
# Creates database, user, and generates credentials for App Platform

set -e

if [ $# -lt 1 ]; then
    echo "Usage: $0 <site-name> [mysql-root-password]"
    echo ""
    echo "Example: $0 customer-site"
    echo ""
    echo "If mysql-root-password is not provided, will prompt for it."
    exit 1
fi

SITE_NAME="$1"
MYSQL_ROOT_PW="$2"

# Database cluster details
DB_HOST="${DB_HOST:-wordpress-mysql-cluster-do-user-28531160-0.i.db.ondigitalocean.com}"
DB_PORT="${DB_PORT:-25060}"

# Generate safe database name (replace hyphens with underscores)
DB_NAME=$(echo "$SITE_NAME" | sed 's/-/_/g')
DB_USER="${DB_NAME}_user"
DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-32)
TABLE_PREFIX=$(echo "$DB_NAME" | cut -c1-10)_

echo "========================================"
echo "WordPress Instance Provisioning"
echo "========================================"
echo ""
echo "Site Name: $SITE_NAME"
echo "Database: $DB_NAME"
echo "User: $DB_USER"
echo "Table Prefix: $TABLE_PREFIX"
echo ""

# Prompt for root password if not provided
if [ -z "$MYSQL_ROOT_PW" ]; then
    echo -n "Enter MariaDB cluster root password (doadmin): "
    read -s MYSQL_ROOT_PW
    echo ""
    echo ""
fi

echo "Connecting to database cluster..."
echo "Host: $DB_HOST:$DB_PORT"
echo ""

# Create SQL commands
SQL_COMMANDS=$(cat <<EOF
-- Drop existing database and user if they exist
DROP DATABASE IF EXISTS \`$DB_NAME\`;
DROP USER IF EXISTS '$DB_USER'@'%';

-- Create new database
CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with legacy authentication (MySQL 5.x compatible)
CREATE USER '$DB_USER'@'%' IDENTIFIED WITH mysql_native_password BY '$DB_PASSWORD';

-- Grant privileges
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES LIKE '$DB_NAME';
SELECT User, Host FROM mysql.user WHERE User = '$DB_USER';
EOF
)

# Execute SQL
echo "$SQL_COMMANDS" | mysql \
    -h "$DB_HOST" \
    -P "$DB_PORT" \
    -u doadmin \
    "-p$MYSQL_ROOT_PW" \
    --ssl \
    2>&1 | grep -v "mysql: [Warning]"

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Database created successfully!"
    echo ""
    echo "========================================"
    echo "App Platform Environment Variables"
    echo "========================================"
    echo ""
    echo "Add these to your App Platform deployment:"
    echo ""
    echo "DB_NAME=$DB_NAME"
    echo "DB_USER=$DB_USER"
    echo "DB_PASSWORD=$DB_PASSWORD"
    echo "DB_HOST=$DB_HOST"
    echo "DB_PORT=$DB_PORT"
    echo "TABLE_PREFIX=$TABLE_PREFIX"
    echo ""
    echo "========================================"
    echo ""
    echo "Save these credentials securely!"
    echo ""
    
    # Save to file
    CREDS_FILE="credentials-${SITE_NAME}.txt"
    cat > "$CREDS_FILE" <<CREDS
WordPress Instance: $SITE_NAME
Generated: $(date)

Database Credentials:
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
TABLE_PREFIX=$TABLE_PREFIX

Next Steps:
1. Create app in DigitalOcean App Platform
2. Set above environment variables
3. Deploy from wordpress-template repository
4. Access site and complete WordPress installation
CREDS
    
    echo "Credentials saved to: $CREDS_FILE"
    echo ""
else
    echo ""
    echo "✗ Failed to create database"
    exit 1
fi
