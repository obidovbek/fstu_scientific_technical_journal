#!/bin/bash
# Diagnostic script to check guest submission setup

echo "========================================="
echo "Guest Submission Setup Diagnostic"
echo "========================================="
echo ""

# Configuration
NGINX_PUBLIC_DIR="/home/web/stj/ojs_public_files"
PROXY_SERVER="stj.fstu.uz"

echo "1. Checking file locations..."
echo ""

FILES=(
    "guest-submission.html"
    "guest-submission-handler.php"
    "guest-submission-config.php"
    "guest-submission.css"
    "guest-submission.js"
    "test-email.php"
)

ALL_FOUND=true
for file in "${FILES[@]}"; do
    if [ -f "$NGINX_PUBLIC_DIR/$file" ]; then
        echo "  ✓ Found: $file"
        ls -lh "$NGINX_PUBLIC_DIR/$file" | awk '{print "    Size: " $5 " Permissions: " $1}'
    else
        echo "  ✗ Missing: $file"
        ALL_FOUND=false
    fi
done

echo ""
echo "2. Checking file permissions..."
for file in "${FILES[@]}"; do
    if [ -f "$NGINX_PUBLIC_DIR/$file" ]; then
        perms=$(stat -c "%a" "$NGINX_PUBLIC_DIR/$file" 2>/dev/null || stat -f "%OLp" "$NGINX_PUBLIC_DIR/$file" 2>/dev/null)
        if [ "$perms" != "644" ] && [ "$perms" != "0644" ]; then
            echo "  ⚠ Warning: $file has permissions $perms (should be 644)"
        fi
    fi
done

echo ""
echo "3. Checking PHP configuration..."
if command -v php &> /dev/null; then
    echo "  PHP Version: $(php -v | head -n 1)"
    echo "  upload_max_filesize: $(php -i 2>/dev/null | grep 'upload_max_filesize' | head -1 | awk '{print $3}')"
    echo "  post_max_size: $(php -i 2>/dev/null | grep 'post_max_size' | head -1 | awk '{print $3}')"
    echo "  sendmail_path: $(php -i 2>/dev/null | grep 'sendmail_path' | head -1 | awk -F'=> ' '{print $2}')"
else
    echo "  ✗ PHP not found in PATH"
fi

echo ""
echo "4. Testing URL accessibility..."
echo "  Testing: https://$PROXY_SERVER/public/test-email.php"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$PROXY_SERVER/public/test-email.php" 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    echo "  ✓ test-email.php is accessible (HTTP $HTTP_CODE)"
elif [ "$HTTP_CODE" = "404" ]; then
    echo "  ✗ test-email.php returns 404 - File not found or nginx misconfigured"
elif [ "$HTTP_CODE" = "000" ]; then
    echo "  ⚠ Could not test URL (curl failed or server unreachable)"
else
    echo "  ⚠ test-email.php returned HTTP $HTTP_CODE"
fi

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$PROXY_SERVER/public/guest-submission.html" 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    echo "  ✓ guest-submission.html is accessible (HTTP $HTTP_CODE)"
elif [ "$HTTP_CODE" = "404" ]; then
    echo "  ✗ guest-submission.html returns 404 - File not found or nginx misconfigured"
else
    echo "  ⚠ guest-submission.html returned HTTP $HTTP_CODE"
fi

echo ""
echo "5. Checking nginx configuration..."
if [ -f "/etc/nginx/sites-available/stj-fstu" ] || [ -f "/etc/nginx/conf.d/stj-fstu.conf" ]; then
    echo "  ✓ Nginx config file found"
    if grep -q "ojs_public_files" /etc/nginx/sites-available/stj-fstu 2>/dev/null || grep -q "ojs_public_files" /etc/nginx/conf.d/stj-fstu.conf 2>/dev/null; then
        echo "  ✓ Nginx config references ojs_public_files"
    fi
else
    echo "  ⚠ Could not find nginx config file"
fi

echo ""
echo "========================================="
if [ "$ALL_FOUND" = true ]; then
    echo "Status: ✓ All files found"
else
    echo "Status: ✗ Some files are missing"
    echo ""
    echo "Run deploy-guest-submission.sh to copy files"
fi
echo "========================================="

