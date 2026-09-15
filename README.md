# Disable Menu Self Links - WordPress Plugin

A simple, focused WordPress plugin that adds an option to disable self-referencing links in navigation menus.

## The Problem

By default, WordPress menu items link to themselves even when you're already on that page. For example, if you're on the "Home" page and click the "Home" menu item, it reloads the same page. This is standard behavior but isn't always desirable.

## The Solution

This plugin adds a checkbox option to each menu item: **"Disable Link to Self"**

- **Unchecked (default)**: Normal WordPress behavior - link works everywhere
- **Checked**: When viewing that specific page, the link becomes non-clickable using self-referencing anchors with triple protection (HTML + CSS + JavaScript)

## Features

✅ **Simple checkbox interface** - Integrates seamlessly with WordPress menu editor  
✅ **Default unchecked** - Maintains WordPress default behavior unless you opt-in  
✅ **Triple protection** - Self-referencing anchors + CSS pointer-events + JavaScript preventDefault  
✅ **Preserves all styling** - Still an `<a>` tag, so all your theme's CSS works automatically  
✅ **Smart ID handling** - Preserves existing IDs or generates unique ones  
✅ **No settings page needed** - Configure per menu item  
✅ **Lightweight** - Minimal code, maximum compatibility  
✅ **Translation ready** - Text domain for internationalization

## Installation

1. Download the plugin folder
2. Upload `disable-menu-self-links` to `/wp-content/plugins/`
3. Activate through the 'Plugins' menu in WordPress
4. Configure your menu items as needed

## Usage

### Step 1: Go to Menu Editor

Navigate to **Appearance > Menus** in your WordPress admin

### Step 2: Edit Menu Item

1. Click on any menu item to expand its settings
2. Find the new checkbox: **"Disable Link to Self"**
3. **Unchecked** = Link works normally (default)
4. **Checked** = Link becomes non-clickable when on that page

### Step 3: Save Menu

Click **Save Menu** to apply your changes

## Example

### Before (Default Behavior)

When on the Home page, the menu HTML looks like:
```html
<li class="menu-item current-menu-item">
    <a href="https://example.com/" class="nav-link">Home</a>
</li>
```

### After (With Option Enabled)

When on the Home page with "Disable Link to Self" checked, the menu HTML becomes:
```html
<li class="menu-item current-menu-item">
    <a href="#dmsl-home" id="dmsl-home" class="nav-link">Home</a>
</li>
```

The link now references itself via anchor (`#dmsl-home`), making it non-clickable. Additional CSS and JavaScript protections ensure it can't be activated. All CSS classes are preserved, so your styling remains intact!

## Technical Details

### How It Works

1. **Menu Item Meta**: Stores setting as post meta (`_dmsl_enable_self_link`)
2. **Current Page Detection**: Uses WordPress's built-in `current-menu-item` class
3. **HTML Modification**: Changes `href` to self-referencing anchor (e.g., `href="#dmsl-home" id="dmsl-home"`)
4. **Triple Protection**: 
   - Self-referencing anchor (links to itself, browser does nothing)
   - CSS `pointer-events: none` (blocks mouse interaction)
   - JavaScript `preventDefault()` (catches any remaining clicks)
5. **Smart ID Handling**: Preserves existing IDs or generates unique ones with `dmsl-` prefix

### Hooks Used

- `wp_setup_nav_menu_item` - Load custom field data
- `wp_nav_menu_item_custom_fields` - Display checkbox in menu editor
- `wp_update_nav_menu_item` - Save checkbox value
- `wp_nav_menu_objects` - Prepare items for modification
- `walker_nav_menu_start_el` - Modify HTML output

### Database Storage

Stores one meta value per menu item:
- **Meta Key**: `_dmsl_enable_self_link`
- **Meta Value**: `1` (link enabled/normal) or `0` (link disabled)
- **Default**: `1` (enabled/normal) - WordPress default behavior maintained

## Use Cases

### 1. Accessibility Improvement
Non-clickable current items can improve screen reader navigation

### 2. User Experience
Prevents accidental page reloads when clicking current page link

### 3. Custom Styling
Style current page differently when it's not a clickable link

### 4. Single Page Sites
Useful for one-page sites where menu items are anchor links

## CSS Styling Example

You can style non-clickable current page items differently:

```css
/* Normal menu link */
.menu-item a {
    color: blue;
    text-decoration: underline;
    cursor: pointer;
}

/* Current page (non-clickable link) */
.current-menu-item a[href^="#"] {
    color: gray;
    cursor: default;
    opacity: 0.8;
}
```

## Compatibility

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Themes**: Compatible with all themes using `wp_nav_menu()`
- **Plugins**: No known conflicts

## Security

✅ Escapes all output (`esc_html`, `esc_attr`)  
✅ Sanitizes input (WordPress handles menu item saves)  
✅ Checks user capabilities (WordPress menu permissions)  
✅ Direct file access prevention  
✅ Prepared statements for database (uses WordPress meta API)

## Frequently Asked Questions

### Q: Does this affect SEO?
**A:** No. The links remain as `<a>` tags with self-referencing anchors, so search engines see them as normal links.

### Q: Will my menu styling break?
**A:** No. The element remains an `<a>` tag, so all your theme's CSS automatically works.

### Q: Does this work with custom walkers?
**A:** Yes, as long as your walker uses the standard `walker_nav_menu_start_el` filter.

### Q: Can I enable it for some pages but not others?
**A:** Yes! The setting is per menu item, so you can configure each one individually.

### Q: What happens to dropdown menus?
**A:** Parent items work the same way - if disabled and you're on that page, the parent link becomes non-clickable.

### Q: Does it work with mega menus?
**A:** Yes, as long as the mega menu plugin uses standard WordPress menu filters.

## Troubleshooting

### Checkbox doesn't appear

Make sure you're using the default WordPress menu editor at **Appearance > Menus**. Some page builders use custom menu systems.

### Changes don't apply

1. Clear your cache (browser + WordPress caching plugins)
2. Make sure you clicked "Save Menu"
3. Check if another plugin might be overriding menu HTML

### Link still clickable

1. Check the HTML (F12 > Inspect) - should see `<a href="#dmsl-slug" id="dmsl-slug">`
2. Verify CSS is loading (look for `frontend.css` in Network tab)
3. Check JavaScript console for errors
4. Clear all caches (browser, WordPress, server)

## Uninstallation

When you delete the plugin (not just deactivate):

1. All `_dmsl_enable_self_link` meta data is removed
2. Menu items return to default WordPress behavior
3. No database tables are left behind

## Changelog

### Version 1.2.1
- Parent menu items with a disabled self-link now keep working hover-triggered submenus (previously `pointer-events: none` blocked hover on the whole item, including its dropdown)
- Has-children detection is now computed by the plugin itself from menu item relationships, instead of depending on the theme's walker to emit a `menu-item-has-children` class

### Version 1.2.0
- Changed to self-referencing anchor approach
- Added triple protection (HTML + CSS + JavaScript)
- Smart ID handling (preserves existing IDs)
- Reversed checkbox logic ("Disable Link to Self")
- Updated all documentation

### Version 1.0.0
- Initial release
- Basic menu link disabling functionality

## Credits

**Author:** Marcel  
**License:** GPL v2 or later

## Support

For issues or questions, check:
1. This README for common solutions
2. WordPress.org support forums
3. GitHub issues (if repository available)

---

**Pro Tip:** Combine this with custom CSS to create sophisticated "current page" indicators in your navigation!
