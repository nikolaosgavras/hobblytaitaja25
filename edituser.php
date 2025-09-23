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

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_data = null;

// Fetch user data if ID is provided
if ($user_id > 0) {
    if ($stmt = $link->prepare('SELECT paneladmin_id, username, role FROM admins WHERE paneladmin_id = ?')) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
    }
}

// If no user found, redirect back to admin panel
if (!$user_data) {
    header('Location: admin.php');
    exit;
}

mysqli_close($link);

?>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Edit user</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row align-items-center">
                <div class="col-4 mx-auto">
                <h1>Edit user info</h1>

                <br>

                <form action="confirmedit.php" method="post">
                <div class="mb-3">
                    <label for="id" class="form-label">ID</label>
                    <input type="text" class="form-control" name="id" id="id" value="<?php echo htmlspecialchars($user_data['paneladmin_id']); ?>" readonly>
                </div>                    
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>">
                    
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_staff" value="staff" <?php echo ($user_data['role'] === 'staff') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="role_staff">
                            Staff
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_user" value="user" <?php echo ($user_data['role'] === 'user') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="role_user">
                            User
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="admin.php" class="btn btn-secondary">Cancel</a>
                </form>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVlz7YYwJrWVlz7YYwJrWVlz7YYwJrWVlz7YYwJrWVlz"></script>
    </body>
</html>