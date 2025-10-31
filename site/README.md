# Hobbly Mobile-Optimized Web Application

## Module C - Implementation Documentation

### Overview
This is a mobile-first web application for displaying hobby and activity announcements from a MySQL database. The application includes three main pages: Announcements (Home), Search, and Map.

### Project Structure
```
site/
├── index.html          # Main announcements page
├── search.html         # Search functionality page
├── map.html            # Map view (placeholder)
├── css/
│   └── styles.css      # Main stylesheet with mobile-first design
├── js/
│   ├── app.js          # Main app logic with infinite scroll
│   └── search.js       # Search functionality logic
└── api/
    ├── get_posts.php       # API endpoint for fetching posts with pagination
    └── get_post_detail.php # API endpoint for fetching single post details
```

### Features Implemented

#### 1. Announcements List (Home Page)
- ✅ Fetches data from MySQL database via API
- ✅ Infinite scroll mechanism for loading posts
- ✅ Two sorting options: Alphabetical (A-Z) and Newest First
- ✅ Default sorting: Alphabetical
- ✅ Each announcement displays:
  - Title
  - Image (with placeholder fallback)
  - Short description (first 100 characters + "...")
  - Organization name
  - Tags

#### 2. Announcement Detail View
- ✅ Modal popup showing full announcement details
- ✅ Complete announcement information including:
  - Full title and description
  - Organization contact details (name, email, phone, address)
  - Activity type and category
  - Location and website URL
  - All tags
- ✅ Accessible modal with keyboard support (ESC to close)

#### 3. Search Functionality
- ✅ Dedicated search page with search input
- ✅ Real-time search with debouncing (500ms delay)
- ✅ Searches across:
  - Title
  - Description
  - Organization name
  - Tags
- ✅ Same layout as announcements list
- ✅ Clear search button
- ✅ Infinite scroll for search results
- ✅ Empty state and no results state

#### 4. Navigation
- ✅ Bottom navigation bar (fixed)
- ✅ Three navigation items: Home, Search, Map
- ✅ Active state indication
- ✅ Smooth page transitions

### Technical Implementation

#### Mobile-First Design
- Base styles optimized for 375px mobile screens
- Responsive breakpoints for larger screens
- Touch-friendly tap targets (minimum 44x44px)
- Optimized for vertical scrolling

#### Performance Optimizations
- **Infinite Scroll**: Uses Intersection Observer API
- **Lazy Loading**: Images load only when needed
- **Debounced Search**: Reduces API calls (500ms delay)
- **Pagination**: Loads 10 posts per request
- **Efficient Rendering**: Uses DocumentFragment for batch DOM updates

#### Accessibility Features
- Semantic HTML5 elements (`<header>`, `<nav>`, `<main>`, `<article>`)
- ARIA labels and roles
- Keyboard navigation support
- Focus management in modals
- Alt text for all images
- High contrast mode support
- Reduced motion support
- Screen reader friendly

#### Security Features
- XSS prevention with HTML escaping
- SQL injection prevention in API (using mysqli_real_escape_string)
- CORS headers configured
- Input validation

### API Endpoints

#### GET /api/get_posts.php
Fetches paginated announcements with optional filtering.

**Parameters:**
- `offset` (int): Starting position for pagination
- `limit` (int): Number of posts to return (default: 10)
- `sort` (string): 'alphabetical' or 'newest'
- `search` (string): Optional search keyword

**Response:**
```json
{
  "success": true,
  "posts": [
    {
      "id": 1,
      "title": "Yoga Class",
      "description": "Full description...",
      "shortDescription": "First 100 characters...",
      "image": "path/to/image.jpg",
      "createdAt": "2025-10-31 12:00:00",
      "tags": "Free of charge,Beginner-friendly",
      "organization": {
        "name": "Wellness Center",
        "email": "info@wellness.com",
        "phone": "123-456-7890",
        "address": "123 Main St"
      }
    }
  ],
  "count": 10
}
```

#### GET /api/get_post_detail.php
Fetches detailed information for a single announcement.

**Parameters:**
- `id` (int, required): Post ID

**Response:**
```json
{
  "success": true,
  "post": {
    "id": 1,
    "title": "Yoga Class",
    "description": "Full description...",
    "image": "path/to/image.jpg",
    "createdAt": "2025-10-31 12:00:00",
    "tags": "Free of charge,Beginner-friendly",
    "activityType": "Activity",
    "category": "Sports and exercise",
    "url": "https://example.com",
    "address": "123 Main St",
    "latitude": 60.1699,
    "longitude": 24.9384,
    "organization": { ... }
  }
}
```

### Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- iOS Safari 12+
- Android Chrome 80+
- Requires ES6 support
- Uses native JavaScript (no framework dependencies)

### Testing & Validation

#### Recommended Testing Tools
1. **Chrome Lighthouse**
   - Performance score optimization
   - Accessibility audit
   - Best practices check
   - SEO audit

2. **axe DevTools**
   - Detailed accessibility testing
   - WCAG compliance checking
   - Color contrast verification

3. **Browser DevTools**
   - Mobile device emulation (375px width)
   - Network throttling
   - Console error checking

#### Validation Checklist
- ✅ HTML5 semantic markup
- ✅ CSS valid (no syntax errors)
- ✅ JavaScript ES6+ compliant
- ✅ WCAG 2.1 AA accessibility standards
- ✅ Mobile responsiveness (375px+)
- ✅ Performance optimization
- ✅ Cross-browser compatibility

### Database Requirements

The application expects the following database structure:

**Tables:**
- `posts`: Announcements data
  - post_id, title, description, image, created_at, tags
  - activity_type, category, url, address, latitude, longitude
  - creator_id (foreign key to organizations)
  
- `organizations`: Organization data
  - org_id, org_name, org_email, org_phone, org_address

### Setup Instructions

1. **Database Setup**
   - Ensure MySQL database "hobbly" is created
   - Import required tables and data
   - Verify conn.php configuration

2. **File Placement**
   - Place all files in the `/site` directory
   - Ensure API files are in `/site/api`
   - Main admin panel should remain outside `/site`

3. **Web Server**
   - XAMPP or similar PHP server
   - Apache with mod_rewrite enabled
   - PHP 7.4+ recommended

4. **Testing**
   - Access via: http://localhost/hobblytaitaja2025/site/
   - Test on mobile viewport (375px width)
   - Verify API endpoints return data

### Performance Metrics

**Target Performance:**
- First Contentful Paint: < 1.5s
- Time to Interactive: < 3.5s
- Lighthouse Performance Score: > 90
- Lighthouse Accessibility Score: 100

**Optimization Techniques:**
- Minimal HTTP requests
- Lazy loading images
- Efficient DOM manipulation
- CSS minification ready
- JavaScript optimization

### Future Enhancements

- Map view implementation with geolocation
- Offline support with Service Workers
- Progressive Web App (PWA) features
- Image optimization and compression
- Advanced filtering options
- User favorites/bookmarks
- Social sharing capabilities

### Credits

Developed for Taitaja 2025 Competition - Module C
Mobile-optimized web application for hobby and activity announcements.

---

## Quick Start

1. Start XAMPP
2. Open: http://localhost/hobblytaitaja2025/site/
3. Test mobile view in DevTools (375px width)
4. Run Lighthouse audit
5. Test accessibility with axe DevTools
