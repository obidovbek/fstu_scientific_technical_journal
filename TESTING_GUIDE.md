# Guest Submission Form - Testing Guide

## Step 1: Fix File Location Issue

Your nginx configuration serves `/public/` from `/home/web/stj/ojs_public_files/`, but your files are in the project's `/public` directory.

### Option A: Copy Files to Nginx-Served Location (Recommended)

```bash
# Copy all guest submission files to the nginx-served directory
cp -r /home/web/stj/frontend2/public/guest-submission* /home/web/stj/ojs_public_files/
cp /home/web/stj/frontend2/public/test-email.php /home/web/stj/ojs_public_files/
```

### Option B: Update Nginx Configuration

If you want to serve from the project's `/public` directory, update your backend nginx config:

```nginx
# In your backend server block, change:
location ~ ^/(public|ojs_public_files)/(.*)$ {
    alias /home/web/stj/frontend2/public/$2;  # Changed from ojs_public_files
    include /etc/nginx/mime.types;
    expires 30d;
    add_header Cache-Control "public, immutable";
    add_header X-Content-Type-Options "nosniff";
}
```

## Step 2: Test Email Functionality First

### 2.1 Access the Email Test Script

1. Open your browser and navigate to:
   ```
   https://stj.fstu.uz/public/test-email.php
   ```

2. You should see the email test interface.

### 2.2 Update Email Addresses

Before testing, edit `/home/web/stj/ojs_public_files/test-email.php` (or your project's `/public/test-email.php`):

```php
$test_email = 'your-email@example.com'; // Change to YOUR email
$admin_email = 'stj_admin@fstu.uz'; // Admin email
```

### 2.3 Run Email Tests

1. Click "Test Simple Email" - sends a plain text email
2. Click "Test HTML Email" - sends a formatted HTML email
3. Click "Test Email with Attachment" - sends email with file attachment

### 2.4 Verify Results

- Check your inbox (and spam folder) for test emails
- If emails don't arrive, check:
  - PHP error logs: `/var/log/php8.1-fpm.log` or `/var/log/nginx/stj-fstu-error.log`
  - Mail logs: `/var/log/mail.log`
  - Verify sendmail is working: `echo "Test" | sendmail -v your@email.com`

## Step 3: Test Guest Submission Form

### 3.1 Access the Form

Navigate to:
```
https://stj.fstu.uz/public/guest-submission.html
```

### 3.2 Fill Out Test Submission

1. **Author Information:**
   - Title: Select "Dr."
   - Name: Test
   - Surname: Author
   - Authorship: First Author
   - Email: your-test-email@example.com
   - Address: Test Address 123
   - Affiliation: Test University

2. **Submission Details:**
   - Article Type: Select "Original article"
   - Manuscript Title: "Test Submission - Please Ignore"
   - Abstract: Enter 200-300 words (must be under 350 words)

3. **Keywords:**
   - Enter 4-6 keywords separated by semicolons
   - Example: `machine learning; artificial intelligence; neural networks; deep learning`

4. **Manuscript File:**
   - Upload a test .doc or .docx file (under 17 MB)

### 3.3 Submit and Verify

1. Click "Submit"
2. You should see a success message
3. Check your email for confirmation
4. Check admin email (`stj_admin@fstu.uz`) for notification with attachment

## Step 4: Troubleshooting

### 404 Errors

If you still get 404 errors:

1. **Check file permissions:**
   ```bash
   ls -la /home/web/stj/ojs_public_files/
   chmod 644 /home/web/stj/ojs_public_files/*.php
   chmod 644 /home/web/stj/ojs_public_files/*.html
   ```

2. **Check nginx error logs:**
   ```bash
   tail -f /var/log/nginx/stj-fstu-error.log
   ```

3. **Test nginx configuration:**
   ```bash
   nginx -t
   sudo systemctl reload nginx
   ```

### Email Not Sending

1. **Check PHP mail configuration:**
   ```bash
   php -i | grep sendmail_path
   ```

2. **Test sendmail directly:**
   ```bash
   echo "Test email" | sendmail -v your@email.com
   ```

3. **Check if SMTP is needed:**
   - If `mail()` function doesn't work, you may need to configure SMTP in `config.inc.php`
   - See email settings section in config file

### File Upload Issues

1. **Check PHP upload limits:**
   ```bash
   php -i | grep upload_max_filesize
   php -i | grep post_max_size
   ```

2. **Update PHP settings if needed** (in `/etc/php/8.1/fpm/php.ini`):
   ```ini
   upload_max_filesize = 20M
   post_max_size = 20M
   ```

3. **Restart PHP-FPM:**
   ```bash
   sudo systemctl restart php8.1-fpm
   ```

## Step 5: Security Checklist

After testing, ensure:

- [ ] Delete `test-email.php` from production
- [ ] Verify file permissions are correct (644 for files, 755 for directories)
- [ ] Check that error messages don't expose sensitive information
- [ ] Verify email addresses are correct in handler
- [ ] Test with invalid data to ensure validation works
- [ ] Check that uploaded files are properly validated

## Step 6: Production Deployment

Once testing is complete:

1. Remove test files:
   ```bash
   rm /home/web/stj/ojs_public_files/test-email.php
   ```

2. Update email addresses in `guest-submission-handler.php`:
   ```php
   define('ADMIN_EMAIL', 'stj_admin@fstu.uz'); // Verify this is correct
   ```

3. Consider adding rate limiting or CAPTCHA for production use

4. Monitor error logs for any issues

