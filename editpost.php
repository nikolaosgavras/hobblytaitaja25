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

// Get post ID from URL
if (!isset($_GET['id'])) {
    header('Location: posts.php');
    exit;
}

$post_id = intval($_GET['id']);
$admin_id = $_SESSION['account_id'];

// Fetch the post data
$post = null;
if ($stmt = $link->prepare('SELECT * FROM posts WHERE post_id = ? AND creator_id = ?')) {
    $stmt->bind_param('ii', $post_id, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        header('Location: posts.php');
        exit;
    }
    $stmt->close();
}

// Process form submission
if (isset($_POST['updatePost'])) {
    
    // Sanitize and assign variables
    $title = trim($_POST['postTitle']);
    $description = trim($_POST['postDesc']);
    
    // Validate and fix URL format
    $post_link = NULL;
    if (!empty($_POST['postLink'])) {
        $post_link = trim($_POST['postLink']);
        // Add http:// if no protocol is specified
        if (!preg_match("~^(?:f|ht)tps?://~i", $post_link)) {
            $post_link = "http://" . $post_link;
        }
    }
    
    // Handle image upload
    $post_image = $post['img_url']; // Keep existing image by default
    
    // Check if user wants to remove the image
    if (isset($_POST['removeImage']) && $_POST['removeImage'] === '1') {
        // Delete old image file if it exists
        if (!empty($post['img_url']) && file_exists($post['img_url'])) {
            unlink($post['img_url']);
        }
        $post_image = NULL;
    } 
    // Check if a new image was uploaded
    elseif (isset($_FILES['postImage']) && $_FILES['postImage']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['postImage']['tmp_name'];
        $fileName = $_FILES['postImage']['name'];
        $fileSize = $_FILES['postImage']['size'];
        $fileType = $_FILES['postImage']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Allowed file extensions
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Check file size (2MB maximum)
            if ($fileSize <= 2097152) {
                // Generate unique filename
                $newFileName = 'post_' . time() . '_' . uniqid() . '.' . $fileExtension;
                
                // Upload directory
                $uploadFileDir = './images/posts/';
                $dest_path = $uploadFileDir . $newFileName;
                
                // Move file to destination
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Delete old image file if it exists
                    if (!empty($post['img_url']) && file_exists($post['img_url'])) {
                        unlink($post['img_url']);
                    }
                    $post_image = 'images/posts/' . $newFileName;
                } else {
                    $error_message = 'Error moving the uploaded file.';
                }
            } else {
                $error_message = 'File size exceeds 2MB limit.';
            }
        } else {
            $error_message = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
        }
    }
    
    $activity_type = !empty($_POST['postActivityType']) ? trim($_POST['postActivityType']) : NULL;
    $post_category = trim($_POST['postCategory']);

    // Get tags from both checkboxes and text input
    $tagsArray = [];
    
    // Get checkbox tags (will be an array if any selected)
    if (isset($_POST['postTags']) && is_array($_POST['postTags'])) {
        $tagsArray = $_POST['postTags'];
    }
    
    // Get custom tags from text input
    if (isset($_POST['customTags']) && !empty(trim($_POST['customTags']))) {
        $customTags = array_map('trim', explode(',', trim($_POST['customTags'])));
        $tagsArray = array_merge($tagsArray, $customTags);
    }

    // Combine and format as comma-separated string for database
    $tags = implode(', ', array_filter($tagsArray));

    $post_address = trim($_POST['postAddress']);
    $post_latitude = !empty($_POST['postLatitude']) ? trim($_POST['postLatitude']) : NULL;
    $post_longitude = !empty($_POST['postLongitude']) ? trim($_POST['postLongitude']) : NULL;
    $event_start = !empty($_POST['eventStart']) ? trim($_POST['eventStart']) : NULL;
    $event_end = !empty($_POST['eventEnd']) ? trim($_POST['eventEnd']) : NULL;
    
    // Prepare and execute the update statement
    if ($stmt = $link->prepare('UPDATE posts SET title = ?, description = ?, url = ?, img_url = ?, activity_type = ?, category = ?, tags = ?, address = ?, latitude = ?, longitude = ?, event_start = ?, event_end = ? WHERE post_id = ? AND creator_id = ?')) {
        $stmt->bind_param('ssssssssssssii', $title, $description, $post_link, $post_image, $activity_type, $post_category, $tags, $post_address, $post_latitude, $post_longitude, $event_start, $event_end, $post_id, $admin_id);
        
        if ($stmt->execute()) {
            header('Location: posts.php?success=updated');
            exit;
        } else {
            $error_message = 'Error updating post: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = 'Could not prepare statement!';
    }
}

// Parse existing tags for pre-selecting checkboxes
$existingTags = [];
if (!empty($post['tags'])) {
    $existingTags = array_map('trim', explode(',', $post['tags']));
}

// Predefined tags list
$predefinedTags = [
    'Free of charge',
    'Open to everyone',
    'Beginner-friendly',
    'Ongoing event',
    'Online',
    'Suitable for families',
    'Suitable for seniors',
    'Suitable for special groups',
    'Equipment available on-site',
    'Requires pre-registration'
];

// Separate predefined and custom tags
$selectedPredefined = array_intersect($existingTags, $predefinedTags);
$customTagsOnly = array_diff($existingTags, $predefinedTags);
$customTagsString = implode(', ', $customTagsOnly);

mysqli_close($link);

?>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Edit Post</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                        <li><a class="dropdown-item" href="profilesettings.php">Profile settings</a></li>
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
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Edit Post</h5>
                            
                            <?php if (isset($error_message)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="postTitle" class="form-label">Title</label>
                                    <input type="text" class="form-control" name="postTitle" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="postDesc" class="form-label">Description</label>
                                    <textarea class="form-control" name="postDesc" rows="4" required><?php echo htmlspecialchars($post['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="postLink" class="form-label">URL (optional)</label>
                                    <input type="text" class="form-control" name="postLink" value="<?php echo htmlspecialchars($post['url'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="postImage" class="form-label">Post Image (2 MB maximum)</label>
                                    
                                    <?php if (!empty($post['img_url'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo htmlspecialchars($post['img_url']); ?>" alt="Current post image" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="removeImage" value="1" id="removeImage">
                                                <label class="form-check-label text-danger" for="removeImage">
                                                    Remove current image
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <input type="file" class="form-control" id="postImage" name="postImage" accept="image/*">
                                    <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF, WEBP (Max size: 2MB). Leave empty to keep current image.</small>
                                    <div id="imagePreview" class="mt-2" style="display: none;">
                                        <p class="text-muted"><strong>New image preview:</strong></p>
                                        <img id="previewImg" src="" alt="Image preview" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="postActivityType" class="form-label">Activity type</label>
                                    <select class="form-select" name="postActivityType" required>
                                        <option value="" disabled>Select type</option>
                                        <option value="Activity" <?php echo ($post['activity_type'] == 'Activity') ? 'selected' : ''; ?>>Activity</option>
                                        <option value="Event" <?php echo ($post['activity_type'] == 'Event') ? 'selected' : ''; ?>>Event</option>
                                        <option value="Hobby opportunity" <?php echo ($post['activity_type'] == 'Hobby opportunity') ? 'selected' : ''; ?>>Hobby opportunity</option>
                                        <option value="Club" <?php echo ($post['activity_type'] == 'Club') ? 'selected' : ''; ?>>Club</option>
                                        <option value="Competition" <?php echo ($post['activity_type'] == 'Competition') ? 'selected' : ''; ?>>Competition</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="postCategory" class="form-label">Category</label>
                                    <select class="form-select" name="postCategory" required>
                                        <option value="" disabled>Select category</option>
                                        <option value="Sports and exercise" <?php echo ($post['category'] == 'Sports and exercise') ? 'selected' : ''; ?>>Sports and exercise</option>
                                        <option value="Music and performing arts" <?php echo ($post['category'] == 'Music and performing arts') ? 'selected' : ''; ?>>Music and performing arts</option>
                                        <option value="Crafts and arts" <?php echo ($post['category'] == 'Crafts and arts') ? 'selected' : ''; ?>>Crafts and arts</option>
                                        <option value="Science and technology" <?php echo ($post['category'] == 'Science and technology') ? 'selected' : ''; ?>>Science and technology</option>
                                        <option value="Games and eSports" <?php echo ($post['category'] == 'Games and eSports') ? 'selected' : ''; ?>>Games and eSports</option>
                                        <option value="Food and cooking" <?php echo ($post['category'] == 'Food and cooking') ? 'selected' : ''; ?>>Food and cooking</option>
                                        <option value="Nature and outdoor activities" <?php echo ($post['category'] == 'Nature and outdoor activities') ? 'selected' : ''; ?>>Nature and outdoor activities</option>
                                        <option value="Culture and history" <?php echo ($post['category'] == 'Culture and history') ? 'selected' : ''; ?>>Culture and history</option>
                                        <option value="Community and volunteering" <?php echo ($post['category'] == 'Community and volunteering') ? 'selected' : ''; ?>>Community and volunteering</option>
                                        <option value="Children and families" <?php echo ($post['category'] == 'Children and families') ? 'selected' : ''; ?>>Children and families</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="postTags" class="form-label">Tags</label>
                                    <div class="mb-3">
                                        <small class="text-muted">Select predefined tags:</small>
                                        <div class="d-flex flex-wrap gap-2 mt-2" id="predefinedTags">
                                            <?php 
                                            $tagId = 1;
                                            foreach ($predefinedTags as $tag): 
                                                $isChecked = in_array($tag, $selectedPredefined) ? 'checked' : '';
                                            ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="postTags[]" value="<?php echo htmlspecialchars($tag); ?>" id="tag<?php echo $tagId; ?>" <?php echo $isChecked; ?>>
                                                <label class="form-check-label" for="tag<?php echo $tagId; ?>"><?php echo htmlspecialchars($tag); ?></label>
                                            </div>
                                            <?php 
                                            $tagId++;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="customTags" class="form-label">Additional custom tags</label>
                                        <input type="text" class="form-control" name="customTags" value="<?php echo htmlspecialchars($customTagsString); ?>" placeholder="Enter custom tags separated by commas">
                                        <small class="form-text text-muted">Separate multiple tags with commas</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="postAddress" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="postAddress" name="postAddress" value="<?php echo htmlspecialchars($post['address']); ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="eventStart" class="form-label">
                                            <i class="bi bi-calendar-check"></i> Event Start Time
                                        </label>
                                        <input type="datetime-local" class="form-control" id="eventStart" name="eventStart" value="<?php echo $post['event_start'] ? date('Y-m-d\TH:i', strtotime($post['event_start'])) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="eventEnd" class="form-label">
                                            <i class="bi bi-calendar-x"></i> Event End Time
                                        </label>
                                        <input type="datetime-local" class="form-control" id="eventEnd" name="eventEnd" value="<?php echo $post['event_end'] ? date('Y-m-d\TH:i', strtotime($post['event_end'])) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="postLatitude" class="form-label">Latitude</label>
                                    <input type="number" step="any" class="form-control" id="postLatitude" name="postLatitude" value="<?php echo htmlspecialchars($post['latitude'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="postLongitude" class="form-label">Longitude</label>
                                    <input type="number" step="any" class="form-control" id="postLongitude" name="postLongitude" value="<?php echo htmlspecialchars($post['longitude'] ?? ''); ?>">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" name="updatePost">Update Post</button>
                                    <a href="posts.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script>
            // Image preview functionality
            document.addEventListener('DOMContentLoaded', function() {
                const imageInput = document.getElementById('postImage');
                const imagePreview = document.getElementById('imagePreview');
                const previewImg = document.getElementById('previewImg');
                const removeCheckbox = document.getElementById('removeImage');

                if (imageInput) {
                    imageInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        
                        if (file) {
                            // Check file size (2MB = 2097152 bytes)
                            if (file.size > 2097152) {
                                alert('File size exceeds 2MB. Please choose a smaller image.');
                                imageInput.value = '';
                                imagePreview.style.display = 'none';
                                return;
                            }

                            // Check file type
                            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                            if (!validTypes.includes(file.type)) {
                                alert('Please select a valid image file (JPG, PNG, GIF, or WEBP).');
                                imageInput.value = '';
                                imagePreview.style.display = 'none';
                                return;
                            }

                            // Show preview
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImg.src = e.target.result;
                                imagePreview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                            
                            // Uncheck remove image if a new image is selected
                            if (removeCheckbox) {
                                removeCheckbox.checked = false;
                            }
                        } else {
                            imagePreview.style.display = 'none';
                        }
                    });
                }
                
                // Handle remove image checkbox
                if (removeCheckbox) {
                    removeCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            imageInput.value = '';
                            imagePreview.style.display = 'none';
                        }
                    });
                }
            });
        </script>
    </body>
</html>
