#!/bin/bash
# Deploy Guest Submission Files to Nginx-Served Location
# This script copies the guest submission files to the location nginx serves from

# Configuration - Update these paths based on your server setup
OJS_ROOT="/home/web/stj/frontend2"
NGINX_PUBLIC_DIR="/home/web/stj/ojs_public_files"
PROJECT_PUBLIC_DIR="${OJS_ROOT}/public"

echo "========================================="
echo "Guest Submission Form Deployment Script"
echo "========================================="
echo ""

# Check if source directory exists
if [ ! -d "$PROJECT_PUBLIC_DIR" ]; then
    echo "ERROR: Source directory not found: $PROJECT_PUBLIC_DIR"
    echo "Please update OJS_ROOT variable in this script"
    exit 1
fi

# Check if destination directory exists
if [ ! -d "$NGINX_PUBLIC_DIR" ]; then
    echo "WARNING: Destination directory not found: $NGINX_PUBLIC_DIR"
    echo "Creating directory..."
    mkdir -p "$NGINX_PUBLIC_DIR"
    chmod 755 "$NGINX_PUBLIC_DIR"
fi

echo "Source directory: $PROJECT_PUBLIC_DIR"
echo "Destination directory: $NGINX_PUBLIC_DIR"
echo ""

# Files to copy
FILES=(
    "guest-submission.html"
    "guest-submission-handler.php"
    "guest-submission-config.php"
    "guest-submission.css"
    "guest-submission.js"
    "test-email.php"
)

echo "Copying files..."
for file in "${FILES[@]}"; do
    if [ -f "$PROJECT_PUBLIC_DIR/$file" ]; then
        cp "$PROJECT_PUBLIC_DIR/$file" "$NGINX_PUBLIC_DIR/"
        chmod 644 "$NGINX_PUBLIC_DIR/$file"
        echo "  ✓ Copied: $file"
    else
        echo "  ✗ Not found: $file"
    fi
done

echo ""
echo "Setting permissions..."
chmod 644 "$NGINX_PUBLIC_DIR"/*.php
chmod 644 "$NGINX_PUBLIC_DIR"/*.html
chmod 644 "$NGINX_PUBLIC_DIR"/*.css
chmod 644 "$NGINX_PUBLIC_DIR"/*.js

echo ""
echo "========================================="
echo "Deployment Complete!"
echo "========================================="
echo ""
echo "Test URLs:"
echo "  Email Test: https://stj.fstu.uz/public/test-email.php"
echo "  Submission Form: https://stj.fstu.uz/public/guest-submission.html"
echo ""
echo "Next steps:"
echo "  1. Test email functionality first"
echo "  2. Update email addresses in test-email.php"
echo "  3. Test the submission form"
echo "  4. Delete test-email.php after testing"
echo ""

