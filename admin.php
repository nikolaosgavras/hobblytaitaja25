<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();


if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] !== 'staff') {
    header('Location: userpanel.php');
    exit;
}


mysqli_close($link);

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="col-6 mx-auto text-center">
                    <h1>Admin panel tools</h1>
                </div>
            <div class="row align-items-center">
                <div class="col-4">
                    <h3>Add a new user</h3>
                    <form action="accountcreation.php" method="post">
                    <div class="mb-3">
                        <label for="accountemail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="accountemail" name="accountemail" required>
                    </div>
                    <div class="mb-3">
                        <label for="accountpassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="accountpassword" name="accountpassword">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label><br>
                        <input type="radio" id="staff" name="accountrole" value="staff" required>
                        <label for="staff">Staff</label><br>
                        <input type="radio" id="user" name="accountrole" value="user">
                        <label for="user">User</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>