
<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();

// Check if user is logged in and has staff role
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] !== 'staff') {
    header('Location: userpanel.php');
    exit;
}

// Check if ID parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin.php');
    exit;
}


$user_id = (int)$_GET['id'];

// Prevent users from deleting themselves
if ($user_id == $_SESSION['account_id']) {
    echo '<script>alert("You cannot delete your own account!"); window.location.href = "admin.php";</script>';
    exit;
}

// Delete the user from database
if ($stmt = $link->prepare('DELETE FROM admins WHERE paneladmin_id = ?')) {
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // User deleted successfully
            echo '<script>alert("User deleted successfully!"); window.location.href = "admin.php";</script>';
        } else {
            // No user found with that ID
            echo '<script>alert("User not found!"); window.location.href = "admin.php";</script>';
        }
    } else {
        // Error executing query
        echo '<script>alert("Error deleting user!"); window.location.href = "admin.php";</script>';
    }
    
    $stmt->close();
} else {
    echo '<script>alert("Database error!"); window.location.href = "admin.php";</script>';
}


mysqli_close($link);

?>