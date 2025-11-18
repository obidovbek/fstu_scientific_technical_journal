# How to Add "Submit your article" to Primary Navigation Menu

Since OJS uses a database-driven navigation system, the easiest and most reliable way to add the "Submit your article" link is through the admin interface using a **Custom Link**.

## Step-by-Step Instructions:

### 1. Login to OJS Admin
- Go to: `https://stj.fstu.uz/itj/management`
- Login with your admin credentials

### 2. Navigate to Navigation Menus
- Click **Settings** (in the left sidebar)
- Click **Website**
- Click **Setup** tab
- Click **Navigation Menus** (in the left menu)

### 3. Edit Primary Navigation Menu
- You should see a list of navigation menus
- Click **"Primary Navigation Menu"** to edit it

### 4. Add Custom Menu Item
- Click the **"Add Item"** button
- In the dropdown, select **"Remote URL"** or **"Custom Page"**
- Fill in the form:
  - **Title**: `Submit your article`
  - **Remote URL**: `https://stj.fstu.uz/itj/guest/form`
  
### 5. Position the Menu Item
- After creating the item, it will appear in the **"Unassigned Menu Items"** list
- Drag it to the **"Assigned Menu Items"** section
- Position it where you want (e.g., after "Archives" or before "About")
- Use the arrows or drag-and-drop to reorder

### 6. Save Changes
- Click **"Save"** button at the bottom

### 7. Verify on Frontend
- Visit your journal homepage: `https://stj.fstu.uz/itj`
- You should now see **"Submit your article"** in the header navigation menu
- Click it to verify it goes to the guest submission form

## Alternative: If the Above Doesn't Work

If you don't see the "Add Item" button or the interface is different:

1. Go to: **Settings → Website → Appearance → Setup → Navigation**
2. Look for **"Primary Navigation"** or **"Main Menu"**
3. Add a new menu item with:
   - Type: Custom Link / Remote URL
   - Title: Submit your article
   - URL: `/guest/form` (relative URL)

## Troubleshooting

### Menu Item Not Appearing After Save
- Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
- Clear OJS cache: Visit `https://stj.fstu.uz/itj/guest/clear-cache`
- Log out and log back in

### Link Goes to Wrong Page
- Make sure the URL is exactly: `https://stj.fstu.uz/itj/guest/form`
- Or use relative URL: `/itj/guest/form`

### Can't Find Navigation Menus Settings
The location might vary by OJS version:
- **OJS 3.3+**: Settings → Website → Setup → Navigation Menus
- **OJS 3.2**: Settings → Website → Appearance → Menus
- **OJS 3.1**: Settings → Website → Navigation Menus

## Why This Approach?

OJS stores navigation menu items in the database, not in template files. The `NavigationMenuService.php` changes we made would require:
1. Database migration to register the new menu item type
2. Clearing multiple caches
3. Potentially rebuilding the navigation menu cache

Using a **Custom Link** is:
- ✅ Immediate - works right away
- ✅ Reliable - doesn't depend on code changes
- ✅ Flexible - easy to edit or remove later
- ✅ Standard - uses OJS's built-in functionality

## Screenshot Reference

When you're in the Navigation Menu editor, you should see:
```
┌─────────────────────────────────────────────────────┐
│ Primary Navigation Menu                              │
├─────────────────────────────────────────────────────┤
│                                                       │
│ [Add Item ▼]                                         │
│                                                       │
│ Assigned Menu Items     │  Unassigned Menu Items    │
│ ─────────────────────   │  ──────────────────────   │
│ • About                 │  • Register                │
│ • Editorial board       │  • Login                   │
│ • Archive               │  • Dashboard               │
│ • Submission Guidelines │  ...                       │
│                         │                            │
└─────────────────────────────────────────────────────┘
```

After adding "Submit your article", drag it to the left column and position it.

