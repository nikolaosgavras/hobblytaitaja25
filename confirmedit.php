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

if (!isset($_POST['id'], $_POST['username'], $_POST['role'])) {
    // Could not get the data that should have been sent
    exit('Please fill out all the fields');
}

// Validate and sanitize inputs
$id = intval($_POST['id']);
$username = trim($_POST['username']);
$role = trim($_POST['role']);

// Ensure username and role are not empty
if (empty($username) || empty($role)) {
    exit('Username and role cannot be empty');
}

// Prepare and execute the update query
$stmt = $link->prepare("UPDATE admins SET username = ?, role = ? WHERE paneladmin_id = ?");
$stmt->bind_param("ssi", $username, $role, $id);
if ($stmt->execute()) {
    echo 'User updated successfully';
} else {
    echo 'Error updating user: ' . $stmt->error;
}
$stmt->close();

mysqli_close($link);

?>