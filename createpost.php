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


if (!isset($_POST['submitPost'])) {
    header('Location: userpanel.php');
    exit;
}




// Process form submission
if (isset($_POST['submitPost'])) {
    
    // Sanitize and assign variables
    $creator_id = intval($_POST['creator_id']);
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
    $post_longitude = trim($_POST['postLongitude']);
    
    // Prepare and execute the insert statement
    if ($stmt = $link->prepare('INSERT INTO posts (creator_id, title, description, url, img_url, activity_type, category, tags, address, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
        $stmt->bind_param('issssssssss', $creator_id, $title, $description, $post_link, $post_image, $activity_type, $post_category, $tags, $post_address, $post_latitude, $post_longitude);
        
        if ($stmt->execute()) {
            echo 'Post created successfully!';
            header('Location: posts.php');
            exit;
        } else {
            echo 'Error creating post: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        echo 'Could not prepare statement!';
    }
}

mysqli_close($link);

?>