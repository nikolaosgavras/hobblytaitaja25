
<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();

// Check if user is logged in
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] == 'staff') {
    header('Location: admin.php');
    exit;
}

// Check if ID parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: posts.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Check if the post belongs to the logged-in user
if ($stmt = $link->prepare('SELECT creator_id FROM posts WHERE post_id = ?')) {
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $stmt->bind_result($creator_id);
    $stmt->fetch();
    $stmt->close();
    
    if ($creator_id != $_SESSION['account_id']) {
        echo '<script>alert("You cannot delete posts that do not belong to you!"); window.location.href = "posts.php";</script>';
        exit;
    }
} else {
    echo '<script>alert("Database error!"); window.location.href = "posts.php";</script>';
    exit;
}

// Delete the post from database
if ($stmt = $link->prepare('DELETE FROM posts WHERE post_id = ?')) {
    $stmt->bind_param('i', $post_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Post deleted successfully
            echo '<script>alert("Post deleted successfully!"); window.location.href = "posts.php";</script>';
        } else {
            // No post found with that ID
            echo '<script>alert("Post not found!"); window.location.href = "posts.php";</script>';
        }
    } else {
        // Error executing query
        echo '<script>alert("Error deleting post!"); window.location.href = "posts.php";</script>';
    }
    
    $stmt->close();
} else {
    echo '<script>alert("Database error!"); window.location.href = "posts.php";</script>';
}


mysqli_close($link);

?>