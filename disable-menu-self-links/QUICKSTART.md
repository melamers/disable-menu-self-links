# Quick Start Guide - Disable Menu Self Links

Get started in 60 seconds!

## Installation

1. **Upload**: Copy `disable-menu-self-links` folder to `/wp-content/plugins/`
2. **Activate**: Go to WordPress admin > Plugins > Activate "Disable Menu Self Links"
3. **Done!** No configuration needed - it just works.

## How to Use

### Option 1: Keep Default Behavior (Do Nothing)
By default, all menu items work exactly as they did before - all links work normally.

### Option 2: Disable Self Link for Specific Items

1. Go to **Appearance > Menus**
2. Click on a menu item to expand its settings
3. Find the checkbox **"Disable Link to Self"**
4. **Check it** to disable the self link
5. Click **Save Menu**

That's it! 🎉

## What Happens

### When Checkbox is Unchecked (Default)
```html
<!-- On the Home page, clicking Home works normally -->
<a href="/home" class="nav-link">Home</a>
```

### When Checkbox is Checked
```html
<!-- On the Home page, Home is not clickable -->
<a href="#dmsl-home" id="dmsl-home" class="nav-link">Home</a>
```

**Note:** The link still works normally on *other* pages - it only becomes non-clickable on its own page.

## Common Use Cases

### Example 1: Prevent Accidental Reloads
Your "Home" page loads slowly. You don't want users accidentally clicking Home when they're already there.

**Solution:** Check "Disable Link to Self" for the Home menu item.

### Example 2: One-Page Website with Sections
You have a one-page site where menu items are anchor links (#about, #contact). Current section shouldn't be clickable.

**Solution:** Create menu items for each section, check the option for all of them.

### Example 3: Better Visual Feedback
You want to clearly show which page is active by making it non-clickable.

**Solution:** Check for all pages, then add CSS:
```css
.current-menu-item a[href^="#"] {
    color: #999;
    cursor: default;
    opacity: 0.8;
}
```

## Testing It Works

1. Save your menu with the option checked
2. View the page on the frontend
3. Try clicking that menu item - it should not work
4. Inspect element - you'll see `<a href="#dmsl-slug" id="dmsl-slug">`
5. Navigate to a different page - the link works normally there!

## Troubleshooting (If It Doesn't Work)

❌ **Checkbox not showing up?**
→ Make sure plugin is activated

❌ **Link still clickable?**  
→ Clear your cache and hard refresh (Ctrl+F5)

❌ **Want different cursor?**
→ The plugin sets cursor:default, but you can override with custom CSS:
```css
.current-menu-item a[href^="#"] {
    cursor: not-allowed;
    /* or any other cursor style */
}
```

## Pro Tips

💡 **Selective Disabling**: You don't have to disable self links for all items - pick and choose!

💡 **Style Differently**: Add unique styles to non-clickable current page items

💡 **Test First**: Try it on one menu item first to see how it looks with your theme

💡 **Mobile Menus**: Works on mobile menus too - no special configuration needed

## What Gets Changed

✅ Menu item `href` attribute (becomes self-referencing on current page)  
✅ Adds `id` attribute if not present  
✅ That's it! Nothing else changes.

## What Doesn't Change

✅ Your menu structure  
✅ CSS classes  
✅ Menu order  
✅ Other page links  
✅ Theme behavior

## Next Steps

Once you're comfortable with the basics:

1. **Experiment** with different menu items
2. **Style** the non-clickable links with custom CSS if desired
3. **Check** how it looks on mobile
4. **Test** with your caching plugin enabled

## Need Help?

Check the full README.md for:
- Technical details
- Advanced use cases
- CSS examples
- Troubleshooting guide

---

**Remember:** The option defaults to UNCHECKED so nothing changes until you actively check it!

Happy menu customizing! 🎯
