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


// We can utilize the isset() function to check if the form has been submitted
if (isset($_POST['oldpassword'], $_POST['newpassword'])) {
    // Make sure the submitted values are not empty
    if (empty($_POST['oldpassword']) || empty($_POST['newpassword'])) {
        // One or more values are empty.
        exit('Please complete the password change form');
    }

    // Get the current user's information from session
    $id = $_SESSION['account_id'];

    if ($stmt = $link->prepare('SELECT paneladmin_id, password_hash FROM admins WHERE paneladmin_id = ?')) {
        // Bind parameters (s = string, i = int, b = blob, etc)
        $stmt->bind_param('i', $id);
        $stmt->execute();
        // Store the result so we can check if the account exists in the database
        $stmt->store_result();
        
        // Check if the account exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $stored_password_hash);
            $stmt->fetch();
            
            // Verify the old password
            if (password_verify($_POST['oldpassword'], $stored_password_hash)) {
                // Old password is correct, update with new password
                $new_password_hash = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);
                
                if ($update_stmt = $link->prepare('UPDATE admins SET password_hash = ? WHERE paneladmin_id = ?')) {
                    $update_stmt->bind_param('si', $new_password_hash, $id);
                    $update_stmt->execute();
                    echo 'Password changed successfully!';
                    $update_stmt->close();
                } else {
                    echo 'Could not prepare update statement!';
                }
            } else {
                echo 'Old password is incorrect!';
            }
        } else {
            echo 'Account not found!';
        }
        // Close the statement
        $stmt->close();
    } else {
        // Something is wrong with the SQL statement
        echo 'Could not prepare statement!';
    }
}




mysqli_close($link);

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Change password</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row align-items-center">
                <div class="col-4 mx-auto">
                <h1>Change your password</h1>

                <br>

                <form method="post">
                <div class="mb-3">
                    <label for="password" class="form-label">Old password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="oldpassword" id="oldpassword" placeholder="Enter your current password here">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('oldpassword')">
                            <i class="bi bi-eye" id="oldpassword-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="newpassword" id="newpassword" placeholder="Enter your new password here">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newpassword')">
                            <i class="bi bi-eye" id="newpassword-icon"></i>
                        </button>
                    </div>
                </div>
                <script>
                    function togglePassword(inputId) {
                        const passwordInput = document.getElementById(inputId);
                        const icon = document.getElementById(inputId + '-icon');
                        
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            icon.classList.remove('bi-eye');
                            icon.classList.add('bi-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            icon.classList.remove('bi-eye-slash');
                            icon.classList.add('bi-eye');
                        }
                    }
                </script>
                <button type="submit" class="btn btn-primary">Change password</button>
                </form>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
