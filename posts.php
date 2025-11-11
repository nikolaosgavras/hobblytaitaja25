<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();


if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] == 'staff') {
    header('Location: admin.php');
    exit;
}

// Check if the admin has an associated organization
$has_organization = false;
$admin_id = $_SESSION['account_id'];

if ($stmt = $link->prepare('SELECT org_id FROM organizations WHERE org_id = ?')) {
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $has_organization = true;
    } else {
        $has_organization = false;
        header('Location: userpanel.php');
    }
    
    $stmt->close();
}

$posts = [];
if ($stmt = $link->prepare('SELECT post_id, title FROM posts WHERE creator_id = ? ORDER BY post_id')) {
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}



mysqli_close($link);

?>


<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>User panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="images\logos\green_logo@low-res.png" width="100" alt="">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="nav-link" href="posts.php">Posts</a>
                    </li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Account settings
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="changepassword.php">Change password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Profile settings</a></li>
                    </ul>
                    </li>
                </ul>
                <?php
                if (isset($_POST['logout'])) {
                    session_destroy();
                    header('Location: adminlogin.php');
                    exit;
                }




                ?>
                <form method="post" class="d-flex pt-3">
                    <button class="btn btn-outline-danger" type="submit" name="logout">Logout</button>
                </form>
                </div>
            </div>
        </nav>

    <main>
        <div class="container-fluid mt-5">
            <div class="row">
            <div class="col-lg-8 col-sm-12">
                    <h3>Post list</h3>
                    <div class="table-responsive">
                        <table class="table table-light table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Post ID</th>
                                    <th>Post Title</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No posts found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['post_id']); ?></td>
                                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                                            <td>
                                                <a href="editpost.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="deletepost.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Create a new post</h5>
                            <form method="post" action="createpost.php">
                                <input type="hidden" name="creator_id" value='<?php echo htmlspecialchars($admin_id);?>'> <!-- admin id is "creator_id" in posts db -->
                                <div class="mb-3">
                                    <label for="postTitle" class="form-label">Title</label>
                                    <input type="text" class="form-control" name="postTitle" placeholder="Enter a title for the post" required>
                                </div>
                                <div class="mb-3">
                                    <label for="postDesc" class="form-label">Description</label>
                                    <input type="text" class="form-control" name="postDesc" placeholder="Enter post description" required>
                                </div>
                                <div class="mb-3">
                                    <label for="postLink" class="form-label">URL (optional)</label>
                                    <input type="text" class="form-control" name="postLink" placeholder="Enter website link">
                                </div>
                                <div class="mb-3">
                                    <label for="postImage" class="form-label">Post image (2 MB maximum)</label>
                                    <input type="text" class="form-control" name="postImage" placeholder="ADD UPLOAD SYSTEM HERE!!! UNFINISHED">
                                </div>
                                <div class="mb-3">
                                    <label for="postActivityType" class="form-label">Activity type</label>
                                    <select class="form-select" name="postActivityType" required>
                                        <option value="" selected disabled>Select type</option>
                                        <option value="Activity">Activity</option>
                                        <option value="Event">Event</option>
                                        <option value="Hobby opportunity">Hobby opportunity</option>
                                        <option value="Club">Club</option>
                                        <option value="Competition">Competition</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="postCategory" class="form-label">Category</label>
                                    <select class="form-select" name="postCategory" required>
                                        <option value="" selected disabled>Select category</option>
                                        <option value="Sports and exercise">Sports and exercise</option>
                                        <option value="Music and performing arts">Music and performing arts</option>
                                        <option value="Crafts and arts">Crafts and arts</option>
                                        <option value="Science and technology">Science and technology</option>
                                        <option value="Games and eSports">Games and eSports</option>
                                        <option value="Food and cooking">Food and cooking</option>
                                        <option value="Nature and outdoor activities">Nature and outdoor activities</option>
                                        <option value="Culture and history">Culture and history</option>
                                        <option value="Community and volunteering">Community and volunteering</option>
                                        <option value="Children and families">Children and families</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="postTags" class="form-label">Tags</label>
                                    <div class="mb-3">
                                        <small class="text-muted">Select predefined tags:</small>
                                        <div class="d-flex flex-wrap gap-2 mt-2" id="predefinedTags">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Free of charge" id="tag1">
                                                <label class="form-check-label" for="tag1">Free of charge</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Open to everyone" id="tag2">
                                                <label class="form-check-label" for="tag2">Open to everyone</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Beginner-friendly" id="tag3">
                                                <label class="form-check-label" for="tag3">Beginner-friendly</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Ongoing event" id="tag4">
                                                <label class="form-check-label" for="tag4">Ongoing event</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Online" id="tag5">
                                                <label class="form-check-label" for="tag5">Online</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Suitable for families" id="tag6">
                                                <label class="form-check-label" for="tag6">Suitable for families</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Suitable for seniors" id="tag7">
                                                <label class="form-check-label" for="tag7">Suitable for seniors</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Suitable for special groups" id="tag8">
                                                <label class="form-check-label" for="tag8">Suitable for special groups</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Equipment available on-site" id="tag9">
                                                <label class="form-check-label" for="tag9">Equipment available on-site</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="Requires pre-registration" id="tag10">
                                                <label class="form-check-label" for="tag10">Requires pre-registration</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="customTags" class="form-label">Additional custom tags</label>
                                        <input type="text" class="form-control" name="customTags" placeholder="Enter custom tags separated by commas">
                                        <small class="form-text text-muted">Separate multiple tags with commas</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="postAddress" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="postAddress" name="postAddress" placeholder="Enter address here" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <div id="locationMap" style="height: 400px; border-radius: 8px; margin-bottom: 10px;"></div>
                                    <div id="selectedCoords" class="alert alert-info" style="display: none;">
                                        <strong>Selected location:</strong><br>
                                        Latitude: <span id="displayLat">-</span><br>
                                        Longitude: <span id="displayLng">-</span>
                                    </div>
                                    <button type="button" class="btn btn-success" id="confirmLocation" style="display: none;">
                                        <i class="bi bi-check-circle"></i> Confirm Location
                                    </button>
                                    <input type="hidden" id="postLatitude" name="postLatitude" required>
                                    <input type="hidden" id="postLongitude" name="postLongitude" required>
                                </div>
                                <button type="submit" class="btn btn-primary" name="submitPost" id="submitPost">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <style>
            .leaflet-control-search {
                background: white;
                padding: 8px;
                border-radius: 4px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            }
            .leaflet-control-search input {
                border: 1px solid #ccc;
                padding: 6px 10px;
                font-size: 14px;
                border-radius: 4px;
                width: 250px;
            }
            .leaflet-control-search input:focus {
                outline: none;
                border-color: #4CAF50;
            }
        </style>
        <script>
            // Initialize Leaflet map centered on Finland
            let map;
            let marker;
            let selectedLat = null;
            let selectedLng = null;

            // Initialize map when page loads
            document.addEventListener('DOMContentLoaded', function() {
                // Center on Finland (approximately center of Finland)
                map = L.map('locationMap').setView([64.0, 26.0], 5);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);

                // Add custom search control to map
                L.Control.Search = L.Control.extend({
                    onAdd: function(map) {
                        const container = L.DomUtil.create('div', 'leaflet-control-search leaflet-bar');
                        const input = L.DomUtil.create('input', '', container);
                        input.type = 'text';
                        input.placeholder = 'Search location in Finland...';
                        input.id = 'locationSearch';
                        
                        // Prevent map clicks when interacting with search box
                        L.DomEvent.disableClickPropagation(container);
                        L.DomEvent.disableScrollPropagation(container);
                        
                        return container;
                    }
                });

                L.control.search = function(opts) {
                    return new L.Control.Search(opts);
                }

                // Add search control to top right
                L.control.search({ position: 'topright' }).addTo(map);

                // Handle map clicks
                map.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    
                    // Remove existing marker if any
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    
                    // Add new marker
                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);
                    
                    // Update marker on drag
                    marker.on('dragend', function(e) {
                        const position = marker.getLatLng();
                        updateSelectedLocation(position.lat, position.lng);
                    });
                    
                    // Update selected location
                    updateSelectedLocation(lat, lng);
                });

                // Location search functionality - attach after control is added
                setTimeout(() => {
                    const searchInput = document.getElementById('locationSearch');
                    let searchTimeout;
                    
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        const query = this.value.trim();
                        
                        if (query.length < 3) return;
                        
                        searchTimeout = setTimeout(() => {
                            searchLocation(query);
                        }, 500);
                    });
                }, 100);

                // Confirm location button
                document.getElementById('confirmLocation').addEventListener('click', function() {
                    if (selectedLat !== null && selectedLng !== null) {
                        document.getElementById('postLatitude').value = selectedLat;
                        document.getElementById('postLongitude').value = selectedLng;
                        
                        // Visual feedback
                        this.innerHTML = '<i class="bi bi-check-circle-fill"></i> Location Confirmed!';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-primary');
                        
                        setTimeout(() => {
                            this.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Location';
                            this.classList.remove('btn-primary');
                            this.classList.add('btn-success');
                        }, 2000);
                    }
                });

                // Form validation - prevent submission without coordinates
                const form = document.querySelector('form[action="createpost.php"]');
                form.addEventListener('submit', function(e) {
                    const latInput = document.getElementById('postLatitude');
                    const lngInput = document.getElementById('postLongitude');
                    
                    if (!latInput.value || !lngInput.value) {
                        e.preventDefault();
                        alert('Please select and confirm a location on the map before submitting.');
                        
                        // Scroll to map
                        document.getElementById('locationMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Highlight the location section
                        const locationSection = document.getElementById('locationMap').parentElement;
                        locationSection.style.border = '2px solid red';
                        locationSection.style.borderRadius = '8px';
                        locationSection.style.padding = '10px';
                        
                        setTimeout(() => {
                            locationSection.style.border = '';
                            locationSection.style.padding = '';
                        }, 3000);
                        
                        return false;
                    }
                });
            });

            // Update selected location display
            function updateSelectedLocation(lat, lng) {
                selectedLat = lat;
                selectedLng = lng;
                
                document.getElementById('displayLat').textContent = lat.toFixed(6);
                document.getElementById('displayLng').textContent = lng.toFixed(6);
                document.getElementById('selectedCoords').style.display = 'block';
                document.getElementById('confirmLocation').style.display = 'inline-block';
            }

            // Search for location using Nominatim API
            async function searchLocation(query) {
                try {
                    // Add Finland as country bias
                    const searchQuery = encodeURIComponent(query + ', Finland');
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${searchQuery}&limit=1&countrycodes=fi`);
                    const data = await response.json();
                    
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        // Center map on result
                        map.setView([lat, lng], 13);
                        
                        // Remove existing marker
                        if (marker) {
                            map.removeLayer(marker);
                        }
                        
                        // Add marker
                        marker = L.marker([lat, lng], {
                            draggable: true
                        }).addTo(map);
                        
                        marker.on('dragend', function(e) {
                            const position = marker.getLatLng();
                            updateSelectedLocation(position.lat, position.lng);
                        });
                        
                        // Update selected location
                        updateSelectedLocation(lat, lng);
                    }
                } catch (error) {
                    console.error('Error searching location:', error);
                }
            }
        </script>
    </body>
</html>
