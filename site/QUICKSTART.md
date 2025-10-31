# Hobbly - Quick Start Guide

## Getting Started in 5 Minutes

### Prerequisites
- XAMPP installed and running
- MySQL database "hobbly" with posts and organizations tables
- Modern web browser (Chrome, Firefox, Safari, or Edge)

### Step 1: Verify Setup
1. Make sure XAMPP Apache and MySQL are running
2. Navigate to: http://localhost/hobblytaitaja2025/site/test.html
3. Click the test buttons to verify API connectivity
4. If tests pass, you're ready to use the app!

### Step 2: Access the Application
**Main Announcements Page:**
http://localhost/hobblytaitaja2025/site/index.html

**Search Page:**
http://localhost/hobblytaitaja2025/site/search.html

**Map Page (placeholder):**
http://localhost/hobblytaitaja2025/site/map.html

### Step 3: Test Mobile View
1. Open DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M / Cmd+Shift+M)
3. Select "iPhone SE" or set custom width to 375px
4. Test all features in mobile view

### Step 4: Run Accessibility Audit
**Using Chrome Lighthouse:**
1. Open DevTools (F12)
2. Go to "Lighthouse" tab
3. Select "Mobile" device
4. Check "Accessibility" and "Performance"
5. Click "Generate report"

**Using axe DevTools:**
1. Install axe DevTools browser extension
2. Open DevTools and go to "axe DevTools" tab
3. Click "Scan ALL of my page"
4. Review and fix any issues

## Key Features to Test

### Announcements Page
- ✅ Scroll down to trigger infinite scroll
- ✅ Click any announcement card to view details
- ✅ Try sorting by A-Z and Newest
- ✅ Check that sorting resets the list

### Search Page
- ✅ Type keywords to search
- ✅ Try searching by title, description, organization, or tags
- ✅ Clear search with X button or ESC key
- ✅ Scroll for more results

### Accessibility Features
- ✅ Tab through navigation items
- ✅ Use keyboard to open cards (Enter/Space)
- ✅ Close modal with ESC key
- ✅ Check focus indicators are visible
- ✅ Test with screen reader (optional)

## Troubleshooting

### Issue: API returns errors
**Solution:** 
- Check database connection in `conn.php`
- Verify database tables exist and have data
- Check PHP error logs in XAMPP

### Issue: Images not loading
**Solution:**
- Verify image paths in database
- Check that placeholder SVG displays
- Ensure images exist in correct directory

### Issue: Infinite scroll not working
**Solution:**
- Check browser console for JavaScript errors
- Verify Intersection Observer is supported
- Try scrolling to bottom slowly

### Issue: Search not finding results
**Solution:**
- Verify database has posts with searchable content
- Check API endpoint in browser: `api/get_posts.php?search=test`
- Ensure database charset is utf8mb4

## Performance Tips

1. **Clear browser cache** before testing
2. **Use incognito mode** for clean testing
3. **Test on real mobile device** if possible
4. **Check Network tab** in DevTools for API calls
5. **Monitor Console** for any errors or warnings

## Project Structure Overview

```
site/
├── index.html              # Home - Announcements list
├── search.html             # Search functionality
├── map.html                # Map view (placeholder)
├── test.html               # API testing page
├── README.md               # Full documentation
├── QUICKSTART.md           # This file
├── css/
│   └── styles.css          # All styles (mobile-first)
├── js/
│   ├── app.js              # Main app logic
│   └── search.js           # Search logic
└── api/
    ├── get_posts.php       # Fetch posts endpoint
    └── get_post_detail.php # Single post endpoint
```

## API Endpoints Quick Reference

### GET /api/get_posts.php
Fetch paginated posts
- `offset`: Starting position (default: 0)
- `limit`: Number of posts (default: 10)
- `sort`: 'alphabetical' or 'newest'
- `search`: Search keyword (optional)

### GET /api/get_post_detail.php
Fetch single post
- `id`: Post ID (required)

## Next Steps

1. ✅ Test all functionality
2. ✅ Run Lighthouse audit
3. ✅ Run axe DevTools audit
4. ✅ Test on mobile device
5. ✅ Verify infinite scroll performance
6. ✅ Check accessibility features
7. ✅ Review code quality

## Need Help?

- Check browser Console for errors
- Review Network tab for API issues
- Verify database connection
- Check README.md for detailed docs
- Test API endpoints individually with test.html

---

**Good luck with Taitaja 2025! 🚀**
