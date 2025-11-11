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
    $post_image = NULL;
    if (isset($_FILES['postImage']) && $_FILES['postImage']['error'] === UPLOAD_ERR_OK) {
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
                    $post_image = 'images/posts/' . $newFileName;
                } else {
                    echo 'Error moving the uploaded file.';
                }
            } else {
                echo 'File size exceeds 2MB limit.';
            }
        } else {
            echo 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
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
    $post_longitude = trim($_POST['postLongitude']);
    $event_start = !empty($_POST['eventStart']) ? trim($_POST['eventStart']) : NULL;
    $event_end = !empty($_POST['eventEnd']) ? trim($_POST['eventEnd']) : NULL;
    
    // Prepare and execute the insert statement
    if ($stmt = $link->prepare('INSERT INTO posts (creator_id, title, description, url, img_url, activity_type, category, tags, address, latitude, longitude, event_start, event_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')) {
        $stmt->bind_param('issssssssssss', $creator_id, $title, $description, $post_link, $post_image, $activity_type, $post_category, $tags, $post_address, $post_latitude, $post_longitude, $event_start, $event_end);
        
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