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
    $post_link = !empty($_POST['postLink']) ? trim($_POST['postLink']) : NULL;
    $post_image = !empty($_POST['postImage']) ? trim($_POST['postImage']) : NULL;
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
    
    // Prepare and execute the update statement
    if ($stmt = $link->prepare('UPDATE posts SET title = ?, description = ?, url = ?, img_url = ?, activity_type = ?, category = ?, tags = ?, address = ?, latitude = ?, longitude = ? WHERE post_id = ? AND creator_id = ?')) {
        $stmt->bind_param('ssssssssssii', $title, $description, $post_link, $post_image, $activity_type, $post_category, $tags, $post_address, $post_latitude, $post_longitude, $post_id, $admin_id);
        
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

                            <form method="post">
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
                                    <label for="postImage" class="form-label">Post image (2 MB maximum)</label>
                                    <input type="text" class="form-control" name="postImage" value="<?php echo htmlspecialchars($post['img_url'] ?? ''); ?>">
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
    </body>
</html>
