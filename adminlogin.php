<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();


if (isset($_SESSION['account_loggedin'])) {
    header('Location: admin.php');
    exit;
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
        $_SESSION['account_role'] = $role; // account role doesnt update without refreshing login after changing role in edituser.php
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
} 

    // Close the prepared statement
    $stmt->close();
}



mysqli_close($link);

?>

<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Admin Login - Hobbly</title>
        <link rel="icon" type="image/png" href="images/symbols/symbol_primary@low-res.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo">
                    <img src="images/logos/logo_primary_1@high-res.png" alt="Hobbly Logo">
                </div>
                <h1><i class="bi bi-shield-lock"></i> Admin Login</h1>
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person"></i> Username
                        </label>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Password
                        </label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                    </button>
                </form>
                <div class="login-footer">
                    <p>Secure admin access for Hobbly platform</p>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
