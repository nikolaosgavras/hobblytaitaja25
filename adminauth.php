<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();

// $2y$10$XtyorDW1FIIdGEP8Go7G3ejZC8Ebkh9VvvqEtEUiUwFf2.1Q5iUzm

// Now we check if the data from the login form was submitted, isset() will check if the data exists
if (!isset($_POST['username'], $_POST['password'])) {
    // Could not get the data that should have been sent
    exit('Please fill both the username and password fields!');
}

// Prepare our SQL, which will prevent SQL injection
if ($stmt = $link->prepare('SELECT paneladmin_id, password_hash, role FROM admins WHERE username = ?')) {
    // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    // Store the result so we can check if the account exists in the database
    $stmt->store_result();
    
    // Check if account exists with the input username
if ($stmt->num_rows > 0) {
    // Account exists, so bind the results to variables
    $stmt->bind_result($id, $password, $role);
    $stmt->fetch();
    // Note: remember to use password_hash in your registration file to store the hashed passwords
    if (password_verify($_POST['password'], $password)) {
        // Password is correct! User has logged in!
        // Regenerate the session ID to prevent session fixation attacks
        session_regenerate_id();
        // Declare session variables (they basically act like cookies but the data is remembered on the server)
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['account_name'] = $_POST['username'];
        $_SESSION['account_id'] = $id;
        $_SESSION['account_role'] = $role;
        // redirect to admin panel
        if ($role !== 'staff') {
        header('Location: userpanel.php');
        exit;
    } else {
        header('Location: admin.php');
        exit;
    }
    } else {
        // Incorrect password
        echo 'Incorrect username and/or password!';
    }
} else {
    // Incorrect username
    echo 'Incorrect username and/or password!';
}

    // Close the prepared statement
    $stmt->close();
}


mysqli_close($link);

?>