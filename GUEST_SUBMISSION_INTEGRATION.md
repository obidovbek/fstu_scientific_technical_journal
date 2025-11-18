# Guest Submission Integration - Complete

## What Was Done

The guest submission form has been successfully **integrated into OJS** instead of being a separate standalone page. Now it includes:

✅ **OJS Header** - Full navigation menu with logo and site branding  
✅ **Primary Navigation Menu** - Including the "Submit your article" link  
✅ **OJS Footer** - Standard footer with links and copyright  
✅ **Consistent Styling** - Matches the OJS theme and design  

## Changes Made

### 1. Created OJS Template File
**File:** `templates/frontend/pages/guestSubmission.tpl`

This template file:
- Uses OJS's template system with `{include file="frontend/components/header.tpl"}` and footer
- Includes all CSS styles scoped with `.guest-` prefixes to avoid conflicts
- Includes all JavaScript functionality inline
- Integrates seamlessly with OJS's navigation and layout

### 2. Updated GuestHandler
**File:** `pages/guest/GuestHandler.php`

Changed from:
- Directly outputting standalone HTML
- Bypassing OJS template system

To:
- Using `TemplateManager` to render templates
- Calling `setupTemplate()` to include header/footer
- Displaying the integrated template

### 3. Navigation Menu Already Configured
The "Submit your article" link is already in the primary navigation menu:
- **File:** `templates/frontend/components/primaryNavMenu.tpl` (lines 35-39)
- **URL:** Points to `/guest/form`

## How to Access

### For Users (Frontend)
1. Visit your journal homepage: `https://stj.fstu.uz/itj`
2. Look for **"Submit your article"** in the top navigation menu
3. Click it to access the integrated guest submission form
4. The form will now appear with the full OJS header, navigation, and footer

### Direct URL
- **Integrated Form:** `https://stj.fstu.uz/itj/guest/form`
- **Alternative:** `https://stj.fstu.uz/itj/guest/index`

## Clear Cache (Important!)

After making these changes, you MUST clear the OJS cache:

### Option 1: Via Web Interface
Visit: `https://stj.fstu.uz/itj/guest/clear-cache`

### Option 2: Via Command Line
```bash
cd d:\projects\stj.fstu.uz
php tools/clearCache.php
```

### Option 3: Manual Cache Deletion
Delete files in: `d:\projects\stj.fstu.uz\cache\`

## Verification Steps

1. **Clear Cache** (see above)
2. **Visit Homepage:** `https://stj.fstu.uz/itj`
3. **Check Navigation:** Look for "Submit your article" in the menu
4. **Click Link:** Should show the form with OJS header/footer
5. **Test Form:** Fill out and submit to verify functionality

## What's Different Now?

### Before (Separate Page)
```
┌─────────────────────────────────────┐
│  Guest Submission Form              │  ← Standalone HTML
│  (No OJS header/navigation)         │
│  ─────────────────────────────────  │
│  Form content...                    │
└─────────────────────────────────────┘
```

### After (Integrated)
```
┌─────────────────────────────────────┐
│  OJS Header + Logo                  │  ← Full OJS integration
│  [Home] [Current] [Archives]        │
│  [Submit your article] [About]      │  ← Navigation menu
├─────────────────────────────────────┤
│  Guest Submission Form              │
│  Form content...                    │
├─────────────────────────────────────┤
│  OJS Footer + Links                 │
└─────────────────────────────────────┘
```

## Benefits

1. **Consistent User Experience** - Users stay within OJS interface
2. **Better Navigation** - Easy to navigate back to other pages
3. **Professional Look** - Matches journal branding
4. **SEO Friendly** - Better site structure and internal linking
5. **Easier Maintenance** - Uses OJS's template system

## Technical Details

### CSS Scoping
All CSS classes are prefixed with `.guest-` to avoid conflicts:
- `.guest-submission-container`
- `.guest-form-header`
- `.guest-form-section`
- `.guest-btn-submit`
- etc.

### JavaScript Scoping
- All code wrapped in IIFE (Immediately Invoked Function Expression)
- Global function `window.guestRemoveAuthorBlock()` for remove buttons
- No conflicts with OJS's existing JavaScript

### Template Variables
The template uses OJS's Smarty template engine:
- `{url page="guest" op="submit"}` - Generates proper URLs
- `{include file="frontend/components/header.tpl"}` - Includes header
- `{include file="frontend/components/footer.tpl"}` - Includes footer
- `{literal}...{/literal}` - Protects CSS/JS from Smarty parsing

## Troubleshooting

### "Submit your article" not appearing in menu?
1. Clear cache: Visit `/itj/guest/clear-cache`
2. Check file: `templates/frontend/components/primaryNavMenu.tpl`
3. Verify lines 35-39 have the menu item code

### Form shows without header/footer?
1. Check `GuestHandler.php` is using `TemplateManager`
2. Verify `setupTemplate($request)` is called
3. Clear cache and refresh

### Styling looks broken?
1. Check CSS is within `{literal}...{/literal}` tags
2. Verify `.guest-` prefixes on all classes
3. Clear browser cache (Ctrl+F5)

### Form submission not working?
1. Check `guest-submission-handler.php` still exists
2. Verify form action URL in template
3. Check JavaScript console for errors

## Files Reference

### Modified Files
- `pages/guest/GuestHandler.php` - Updated to use OJS templates
- `templates/frontend/components/primaryNavMenu.tpl` - Already has menu item

### New Files
- `templates/frontend/pages/guestSubmission.tpl` - Integrated template

### Unchanged Files (Still Used)
- `pages/guest/guest-submission-handler.php` - Form submission handler
- `pages/guest/test-email-handler.php` - Test email functionality
- `pages/guest/guest-submission-config.php` - Configuration
- `pages/guest/GuestHandler.php` - Handler class (updated but still used)

## Next Steps

1. **Clear Cache** - Essential for changes to take effect
2. **Test the Form** - Submit a test manuscript
3. **Verify Email** - Check that confirmation emails work
4. **Update Documentation** - Update user guides if needed
5. **Train Users** - Show them where the new menu item is

## Support

If you encounter any issues:
1. Check the troubleshooting section above
2. Clear all caches (OJS + browser)
3. Check PHP error logs
4. Verify file permissions

---

**Status:** ✅ Complete and Ready to Use  
**Date:** November 18, 2025  
**Version:** OJS 3.x Compatible

