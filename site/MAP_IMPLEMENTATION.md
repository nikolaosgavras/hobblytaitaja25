# Map Implementation Guide

## Overview
The map feature displays all activities from the MySQL database that have valid latitude and longitude coordinates. Markers are placed on a Google Map centered on Finland, and clicking on a marker shows detailed information about the activity.

## Files Created/Modified

### New Files
1. **`site/api/get_map_posts.php`** - API endpoint that fetches posts with valid location data
2. **`site/js/map.js`** - JavaScript for map initialization, marker creation, and info windows

### Modified Files
1. **`site/map.html`** - Updated to use dynamic map with data from database
2. **`site/css/styles.css`** - Added map-specific styling
3. **`site/api/get_posts.php`** - Added latitude/longitude to response

## Key Features

### 1. **Finland-Centered Map**
- Map is centered at coordinates: **61.9241°N, 25.7482°E** (approximate center of Finland)
- Default zoom level: 6 (shows entire Finland)

### 2. **Dynamic Markers**
- Fetches posts with valid `latitude` and `longitude` from the database
- Only displays posts where coordinates are not NULL and not 0
- Color-coded markers based on activity type:
  - 🟢 Sports - Green (#4CAF50)
  - 🟣 Arts - Purple (#9C27B0)
  - 🟠 Music - Orange (#FF9800)
  - 🔵 Education - Blue (#2196F3)
  - 🔵 Technology - Cyan (#00BCD4)
  - 🟢 Outdoor - Light Green (#8BC34A)
  - 🟠 Indoor - Deep Orange (#FF5722)
  - 🩷 Social - Pink (#E91E63)
  - 🟡 Workshop - Amber (#FFC107)
  - 🟣 Event - Deep Purple (#673AB7)
  - ⚫ Default - Blue Grey (#607D8B)

### 3. **Interactive Info Windows**
When a marker is clicked, an info window displays:
- Activity image (if available)
- Title
- Organization name
- Address
- Description (truncated to 150 characters)
- Activity type and category tags
- Contact information (phone and email)
- "View Details" button linking to full post

### 4. **Post Counter**
- Shows the number of activities displayed on the map
- Displayed at the top center of the map

### 5. **Security Features**
- Prepared statements to prevent SQL injection
- XSS protection through htmlspecialchars
- CORS headers for security
- Input validation for all user inputs
- No sensitive error information exposed

## Database Requirements

### Posts Table Columns
The implementation expects the following columns in the `posts` table:
```sql
- post_id (INT)
- title (VARCHAR)
- description (TEXT)
- img_url (VARCHAR)
- tags (VARCHAR)
- activity_type (VARCHAR)
- category (VARCHAR)
- address (VARCHAR)
- latitude (DECIMAL)
- longitude (DECIMAL)
- creator_id (INT, foreign key to organizations)
```

### Adding Coordinates to Existing Posts
If your posts don't have latitude/longitude data yet, you'll need to:

1. **Manual Entry**: Update posts with coordinates:
```sql
UPDATE posts 
SET latitude = 60.1699, longitude = 24.9384 
WHERE post_id = 1;  -- Helsinki coordinates
```

2. **Geocoding API**: Use a service like Google Geocoding API to convert addresses to coordinates
3. **Admin Interface**: Add latitude/longitude fields to your post creation/edit forms

## Google Maps API

### API Key
The implementation uses the Google Maps JavaScript API with the key visible in the code. For production:
1. Restrict the API key to your domain
2. Enable only required APIs:
   - Maps JavaScript API
   - Marker Library

### API Usage
```javascript
// Load required libraries
const { Map, InfoWindow } = await google.maps.importLibrary("maps");
const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");
```

## Testing the Implementation

### 1. Check API Response
Visit: `http://localhost/hobblytaitaja2025/site/api/get_map_posts.php`

Expected response:
```json
{
  "success": true,
  "posts": [
    {
      "id": 1,
      "title": "Activity Name",
      "latitude": 60.1699,
      "longitude": 24.9384,
      ...
    }
  ],
  "count": 1
}
```

### 2. View Map
Visit: `http://localhost/hobblytaitaja2025/site/map.html`

You should see:
- A map centered on Finland
- Markers for all activities with coordinates
- Clicking markers shows info windows
- Post counter at the top

## Troubleshooting

### No Markers Showing
1. Check if posts have valid latitude/longitude in database
2. Verify API endpoint returns data
3. Check browser console for JavaScript errors
4. Verify Google Maps API key is valid

### Map Not Loading
1. Check internet connection (Google Maps requires external resources)
2. Verify API key has not reached quota limits
3. Check browser console for API errors
4. Ensure XAMPP server is running

### Info Windows Not Opening
1. Check browser console for JavaScript errors
2. Verify post data structure matches expected format
3. Ensure XSS protection isn't blocking content

## Future Enhancements

### Possible Improvements
1. **Clustering**: Group nearby markers to reduce clutter
2. **Filtering**: Filter markers by activity type, category, or date
3. **Search**: Search for activities by location or name
4. **Directions**: Add "Get Directions" button in info windows
5. **User Location**: Show user's current location on map
6. **Custom Map Styles**: Apply custom styling to match brand colors
7. **Full Screen**: Add fullscreen toggle button
8. **Mobile Gestures**: Improve touch controls for mobile devices

### Database Improvements
1. Add indexes on latitude/longitude columns for faster queries
2. Implement spatial indexing for proximity searches
3. Store location data in a dedicated table for better normalization

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- Requires internet connection for Google Maps

## Mobile Support
- Responsive design adapts to mobile screens
- Touch-friendly marker interactions
- Optimized info window size for mobile displays
