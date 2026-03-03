#!/bin/bash
# WordPress Plugin Testing Script
# Tests each plugin individually to find which ones work

set -e

APP_ID="d571bec6-cc1f-4200-b5b1-1343a26a66f1"
SITE_URL="https://preciseitservices.com.au"
NEW_PASSWORD="lrV1tXYA/uZRT9IKbSXJTL/UcTborCpccvyBUhPcrrE="

# Plugin list
PLUGINS=(
    "addons-for-beaver-builder"
    "bb-header-footer"
    "beaver-builder-lite-version"
    "global-site-tag-tracking"
    "google-analytics-for-wordpress"
    "jetpack"
    "jetpack-boost"
    "log-cleaner-for-ithemes-security"
    "powerpack-addon-for-beaver-builder"
    "ultimate-addons-for-beaver-builder-lite"
    "wpforms-lite"
    "wp-free-ssl"
)

WORKING_PLUGINS=()
BROKEN_PLUGINS=()
REPORT_FILE="/tmp/plugin-test-report.txt"

echo "==================================================================" > "$REPORT_FILE"
echo "WordPress Plugin Testing Report" >> "$REPORT_FILE"
echo "Site: $SITE_URL" >> "$REPORT_FILE"
echo "Date: $(date)" >> "$REPORT_FILE"
echo "==================================================================" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Function to test homepage
test_homepage() {
    local response=$(curl -s -w "\n%{http_code}" "$SITE_URL" 2>&1)
    local http_code=$(echo "$response" | tail -n1)
    local content=$(echo "$response" | head -n-1)
    local content_length=${#content}
    
    echo "$http_code|$content_length"
}

# Function to wait for deployment
wait_for_deployment() {
    echo "  Waiting for deployment to complete..."
    local max_wait=180
    local waited=0
    
    while [ $waited -lt $max_wait ]; do
        local phase=$(doctl apps list-deployments "$APP_ID" --format Phase --no-header | head -1)
        if [ "$phase" = "ACTIVE" ]; then
            echo "  ✓ Deployment complete"
            sleep 10  # Extra wait for propagation
            return 0
        fi
        sleep 5
        waited=$((waited + 5))
        echo -n "."
    done
    
    echo "  ✗ Deployment timeout"
    return 1
}

# Update password first
echo "==================================================================="
echo "Step 1: Updating WordPress password to secure random value"
echo "==================================================================="
echo ""
echo "Password: $NEW_PASSWORD" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Update password using file-based approach to avoid escaping issues
doctl apps spec get "$APP_ID" --format yaml > /tmp/app-spec-temp.yaml
sed -i 's/value: Xd4qweASDcxz/value: "lrV1tXYA\/uZRT9IKbSXJTL\/UcTborCpccvyBUhPcrrE="/' /tmp/app-spec-temp.yaml
doctl apps update "$APP_ID" --spec /tmp/app-spec-temp.yaml > /dev/null 2>&1

echo "✓ Password updated in app spec"
echo ""

# Test baseline (no plugins)
echo "==================================================================="
echo "Step 2: Testing baseline (all plugins disabled)"
echo "==================================================================="
echo ""

echo "## Baseline Test (No Plugins) ##" >> "$REPORT_FILE"
baseline=$(test_homepage)
baseline_code=$(echo "$baseline" | cut -d'|' -f1)
baseline_size=$(echo "$baseline" | cut -d'|' -f2)

if [ "$baseline_code" = "200" ]; then
    echo "✓ Baseline test PASSED"
    echo "  HTTP: $baseline_code | Content: ${baseline_size} bytes"
    echo "Status: PASSED" >> "$REPORT_FILE"
    echo "HTTP Code: $baseline_code" >> "$REPORT_FILE"
    echo "Content Size: ${baseline_size} bytes" >> "$REPORT_FILE"
else
    echo "✗ Baseline test FAILED"
    echo "  HTTP: $baseline_code | Content: ${baseline_size} bytes"
    echo "  Cannot proceed - site broken with no plugins!"
    echo "Status: FAILED - Site broken without plugins!" >> "$REPORT_FILE"
    exit 1
fi

echo "" >> "$REPORT_FILE"
echo ""

# Test each plugin
echo "==================================================================="
echo "Step 3: Testing plugins individually"
echo "==================================================================="
echo ""

PLUGIN_NUM=1
TOTAL_PLUGINS=${#PLUGINS[@]}

for plugin in "${PLUGINS[@]}"; do
    echo "-------------------------------------------------------------------"
    echo "[$PLUGIN_NUM/$TOTAL_PLUGINS] Testing plugin: $plugin"
    echo "-------------------------------------------------------------------"
    echo ""
    
    echo "## Plugin $PLUGIN_NUM: $plugin ##" >> "$REPORT_FILE"
    
    # Enable plugin
    if [ -d "wp-content/plugins/${plugin}.old" ]; then
        echo "  Enabling plugin..."
        mv "wp-content/plugins/${plugin}.old" "wp-content/plugins/${plugin}"
        
        # Commit and push
        git add "wp-content/plugins/${plugin}" "wp-content/plugins/${plugin}.old" 2>/dev/null || true
        git commit -m "Test: Enable $plugin" > /dev/null 2>&1
        git push > /dev/null 2>&1
        
        # Deploy
        echo "  Deploying..."
        doctl apps create-deployment "$APP_ID" --wait > /dev/null 2>&1
        
        # Wait a bit more for propagation
        sleep 15
        
        # Test
        echo "  Testing homepage..."
        result=$(test_homepage)
        http_code=$(echo "$result" | cut -d'|' -f1)
        content_size=$(echo "$result" | cut -d'|' -f2)
        
        if [ "$http_code" = "200" ] && [ "$content_size" -gt 1000 ]; then
            echo "  ✓ WORKING - HTTP: $http_code | Content: ${content_size} bytes"
            WORKING_PLUGINS+=("$plugin")
            echo "Status: WORKING" >> "$REPORT_FILE"
            echo "HTTP Code: $http_code" >> "$REPORT_FILE"
            echo "Content Size: ${content_size} bytes" >> "$REPORT_FILE"
            echo "Action: Plugin kept enabled" >> "$REPORT_FILE"
        else
            echo "  ✗ BROKEN - HTTP: $http_code | Content: ${content_size} bytes"
            echo "  Disabling plugin..."
            BROKEN_PLUGINS+=("$plugin")
            
            # Disable it again
            mv "wp-content/plugins/${plugin}" "wp-content/plugins/${plugin}.old"
            git add "wp-content/plugins/${plugin}.old" "wp-content/plugins/${plugin}" 2>/dev/null || true
            git commit -m "Test: Disable $plugin (broken)" > /dev/null 2>&1
            git push > /dev/null 2>&1
            
            echo "Status: BROKEN" >> "$REPORT_FILE"
            echo "HTTP Code: $http_code" >> "$REPORT_FILE"
            echo "Content Size: ${content_size} bytes" >> "$REPORT_FILE"
            echo "Action: Plugin disabled" >> "$REPORT_FILE"
            
            # Redeploy to fix site
            doctl apps create-deployment "$APP_ID" --wait > /dev/null 2>&1
        fi
    else
        echo "  ⚠ Plugin directory not found"
        echo "Status: NOT FOUND" >> "$REPORT_FILE"
    fi
    
    echo "" >> "$REPORT_FILE"
    echo ""
    PLUGIN_NUM=$((PLUGIN_NUM + 1))
done

# Final summary
echo "==================================================================="
echo "FINAL SUMMARY"
echo "==================================================================="
echo ""
echo "==================================================================" >> "$REPORT_FILE"
echo "FINAL SUMMARY" >> "$REPORT_FILE"
echo "==================================================================" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

echo "Working Plugins (${#WORKING_PLUGINS[@]}):"
echo "Working Plugins (${#WORKING_PLUGINS[@]}):" >> "$REPORT_FILE"
for plugin in "${WORKING_PLUGINS[@]}"; do
    echo "  ✓ $plugin"
    echo "  ✓ $plugin" >> "$REPORT_FILE"
done

echo ""
echo "" >> "$REPORT_FILE"

echo "Broken Plugins (${#BROKEN_PLUGINS[@]}):"
echo "Broken Plugins (${#BROKEN_PLUGINS[@]}):" >> "$REPORT_FILE"
for plugin in "${BROKEN_PLUGINS[@]}"; do
    echo "  ✗ $plugin"
    echo "  ✗ $plugin" >> "$REPORT_FILE"
done

echo ""
echo "" >> "$REPORT_FILE"
echo "Full report saved to: $REPORT_FILE"
echo "Report completed: $(date)" >> "$REPORT_FILE"

cat "$REPORT_FILE"
