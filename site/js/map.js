/**
 * Map functionality for displaying posts with location data
 * Uses Google Maps JavaScript API
 */

let map;
let infoWindow;
let markers = [];

/**
 * Initialize the map centered on Finland
 */
async function initMap() {
    try {
        console.log('Initializing map...');
        
        // Finland center coordinates (approximately Helsinki)
        const finlandCenter = { lat: 65, lng: 26 };
        
        // Check if map container exists
        const mapElement = document.getElementById("map");
        if (!mapElement) {
            console.error('Map container element not found!');
            showErrorMessage('Map container not found.');
            return;
        }
        
        console.log('Map element found:', mapElement);
        console.log('Map element dimensions:', mapElement.offsetWidth, 'x', mapElement.offsetHeight);
        
        // Request needed libraries
        console.log('Loading Google Maps libraries...');
        const { Map, RenderingType } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        console.log('Libraries loaded successfully');
        
        // Create map centered on Finland with Map ID
        console.log('Creating map with ID: 5206f42bb70582823a4ef3a1');
        map = new Map(mapElement, {
            zoom: 5,
            center: finlandCenter,
            mapId: "5206f42bb70582823a4ef3a1",
            mapTypeControl: false,
            renderingType: RenderingType.VECTOR,
            streetViewControl: false,
            fullscreenControl: false,
            zoomControl: false
            
        });
        
        console.log('Map created successfully:', map);
        
        // Create info window for marker popups
        infoWindow = new google.maps.InfoWindow();
        
        // Load posts and create markers
        await loadMapPosts();
    } catch (error) {
        console.error('Error initializing map:', error);
        showErrorMessage('Failed to initialize map: ' + error.message);
    }
}

/**
 * Fetch posts with location data from the API
 */
async function loadMapPosts() {
    try {
        const response = await fetch('api/get_map_posts.php');
        
        if (!response.ok) {
            console.error('API Response Error:', response.status, response.statusText);
            const errorText = await response.text();
            console.error('Error details:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Map data loaded:', data);
        
        if (data.success && data.posts && data.posts.length > 0) {
            createMarkers(data.posts);
            // Activity count on map is now handled inside createMarkers after filtering
        } else {
            console.log('No posts with location data found');
            showNoPostsMessage();
        }
    } catch (error) {
        console.error('Error loading map posts:', error);
        showErrorMessage('Failed to load map data. Please try again later.');
    }
}

/**
 * Check if an event has ended
 */
function isEventEnded(eventEndTimestamp) {
    if (!eventEndTimestamp) return false; // If no end time, don't hide
    
    const now = Date.now() / 1000; // Current time in seconds
    const eventEnd = eventEndTimestamp > 10000000000 ? eventEndTimestamp / 1000 : eventEndTimestamp;
    
    return eventEnd < now;
}


/**
 * Create markers for all posts with location data
 */
async function createMarkers(posts) {
    console.log('Creating markers for', posts.length, 'posts');
    console.log('Posts data:', posts);
    
    
    const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");
    
    // Clear existing markers
    markers.forEach(marker => marker.map = null);
    markers = [];
    
    posts.forEach((post, index) => {
        console.log(`Processing post ${index + 1}:`, post);
        
        // Skip posts where the event has already ended
        if (isEventEnded(post.event_end)) {
            console.log(`Skipping ended event: ${post.title}`);
            return;
        }
        
        // Validate coordinates
        if (!post.latitude || !post.longitude) {
            console.warn(`Post ${post.id} has invalid coordinates:`, post.latitude, post.longitude);
            return;
        }
        
        const position = {
            lat: parseFloat(post.latitude),
            lng: parseFloat(post.longitude)
        };
        
        console.log(`Creating marker at position:`, position);
        
        // Get color based on activity type
        const pinColor = getPinColor(post.activityType);
        console.log(`Pin color for ${post.activityType}:`, pinColor);
        
        // Create custom pin with color
        const pinElement = new PinElement({
            background: pinColor,
            borderColor: darkenColor(pinColor, 20),
            glyphColor: '#ffffff',
            scale: 1.2
        });
        
        // Create advanced marker
        const marker = new AdvancedMarkerElement({
            map: map,
            position: position,
            content: pinElement.element,
            title: post.title
        });
        
        console.log(`Marker created for "${post.title}":`, marker);
        
        // Add click listener to show info window
        marker.addListener('click', () => {
            showInfoWindow(marker, post);
        });
        
        markers.push(marker);
    });
    
    console.log(`Total markers created: ${markers.length}`);
    
    // Show count of active (non-ended) events
    if (markers.length > 0) {
        showPostCount(markers.length);
        
        // Adjust map bounds to fit all markers
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => {
            bounds.extend(marker.position);
        });
        map.fitBounds(bounds);
        
        // Add some padding if only one marker
        if (markers.length === 1) {
            google.maps.event.addListenerOnce(map, 'bounds_changed', () => {
                map.setZoom(Math.min(map.getZoom(), 12));
            });
        }
        
        console.log('Map bounds adjusted to fit markers');
    } else {
        // All events have ended
        showNoPostsMessage();
    }
}

/**
 * Show info window with post details when marker is clicked
 */
function showInfoWindow(marker, post) {
    // Fix image path - prepend ../ if image path doesn't start with http or data:
    let imageUrl = post.image;
    if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('data:')) {
        imageUrl = '../' + imageUrl;
    }
    
    const contentString = `
        <div class="map-info-window" style="max-width: 300px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            ${imageUrl ? `
                <img src="${imageUrl}" 
                     alt="${post.title}" 
                     style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 12px;"
                     onerror="this.style.display='none'">
            ` : ''}
            <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #333;">${post.title}</h3>
            ${post.organization.name ? `
                <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">
                    <strong>🏢 ${post.organization.name}</strong>
                </p>
            ` : ''}
            ${post.address ? `
                <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">
                    📍 ${post.address}
                </p>
            ` : ''}
            <p style="margin: 0 0 12px 0; font-size: 14px; color: #555; line-height: 1.4;">
                ${post.description}
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                ${post.activityType ? `
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                        ${post.activityType}
                    </span>
                ` : ''}
                ${post.category ? `
                    <span style="background: #f3e5f5; color: #7b1fa2; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                        ${post.category}
                    </span>
                ` : ''}
            </div>
            ${post.organization.phone || post.organization.email ? `
                <div style="border-top: 1px solid #eee; padding-top: 8px; font-size: 13px; color: #666;">
                    ${post.organization.phone ? `<div>📞 ${post.organization.phone}</div>` : ''}
                    ${post.organization.email ? `<div>✉️ ${post.organization.email}</div>` : ''}
                </div>
            ` : ''}
            <a href="index.html?postId=${post.id}" 
               style="display: inline-block; margin-top: 12px; padding: 8px 16px; background: #1976d2; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                View Details
            </a>
        </div>
    `;
    
    infoWindow.setContent(contentString);
    infoWindow.open(map, marker);
}

/**
 * Get pin color based on activity type
 */
function getPinColor(activityType) {
    const colors = {
        'Sports': '#4CAF50',      // Green
        'Arts': '#9C27B0',        // Purple
        'Music': '#FF9800',       // Orange
        'Education': '#2196F3',   // Blue
        'Technology': '#00BCD4',  // Cyan
        'Outdoor': '#8BC34A',     // Light Green
        'Indoor': '#FF5722',      // Deep Orange
        'Social': '#E91E63',      // Pink
        'Workshop': '#FFC107',    // Amber
        'Event': '#673AB7'        // Deep Purple
    };
    
    return colors[activityType] || '#607D8B'; // Default: Blue Grey
}

/**
 * Darken a hex color by a percentage
 */
function darkenColor(hex, percent) {
    const num = parseInt(hex.replace('#', ''), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) - amt;
    const G = (num >> 8 & 0x00FF) - amt;
    const B = (num & 0x0000FF) - amt;
    return '#' + (
        0x1000000 +
        (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 +
        (G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 +
        (B < 255 ? (B < 1 ? 0 : B) : 255)
    ).toString(16).slice(1);
}

/**
 * Show count of posts on the map
 */
function showPostCount(count) {
    const mapContainer = document.querySelector('.map');
    const existingCount = document.querySelector('.map-post-count');
    
    if (existingCount) {
        existingCount.remove();
    }
    
    const countElement = document.createElement('div');
    countElement.className = 'map-post-count';
    countElement.innerHTML = `
        <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); 
                    background: black; padding: 8px 16px; border-radius: 20px; 
                    box-shadow: 0 2px 6px rgba(0,0,0,0.3); z-index: 1000; 
                    font-size: 14px; font-weight: 500;">
            📍 ${count} ${count === 1 ? 'activity' : 'activities'} on map
        </div>
    `;
    mapContainer.appendChild(countElement);
}

/**
 * Show message when no posts are available
 */
function showNoPostsMessage() {
    const mapContainer = document.querySelector('.map');
    const messageElement = document.createElement('div');
    messageElement.innerHTML = `
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                    background: white; padding: 24px; border-radius: 8px; 
                    box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 1000; text-align: center;">
            <p style="margin: 0; font-size: 16px; color: #636363ff;">
                No activities with location data available yet.
            </p>
        </div>
    `;
    mapContainer.appendChild(messageElement);
}
    
/**
 * Show error message
 */
function showErrorMessage(message) {
    const mapContainer = document.querySelector('.map');
    const errorElement = document.createElement('div');
    errorElement.innerHTML = `
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                    background: #ffebee; padding: 24px; border-radius: 8px; border: 1px solid #ef5350;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 1000; text-align: center; max-width: 400px;">
            <p style="margin: 0; font-size: 16px; color: #c62828;">
                ${message}
            </p>
        </div>
    `;
    mapContainer.appendChild(errorElement);
}

// Initialize map when the page loads
window.initMap = initMap;
