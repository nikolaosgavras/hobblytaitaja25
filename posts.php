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

// Check if the admin has an associated organization
$has_organization = false;
$admin_id = $_SESSION['account_id'];

if ($stmt = $link->prepare('SELECT org_id FROM organizations WHERE org_id = ?')) {
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $has_organization = true;
    }
    
    $stmt->close();
}



if ($stmt = $link->prepare('SELECT org_id FROM organizations WHERE org_id = ?')) {
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $has_organization = true;
    }
    $stmt->close();
}




mysqli_close($link);

?>


<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>User panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="images\logos\green_logo@low-res.png" width="100" alt="">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="nav-link" href="posts.php">Posts</a>
                    </li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Account settings
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="changepassword.php">Change password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Profile settings</a></li>
                    </ul>
                    </li>
                </ul>
                <?php
                if (isset($_POST['logout'])) {
                    session_destroy();
                    header('Location: adminlogin.php');
                    exit;
                }




                ?>
                <form method="post" class="d-flex pt-3">
                    <button class="btn btn-outline-danger" type="submit" name="logout">Logout</button>
                </form>
                </div>
            </div>
        </nav>
        
        <div class="container mt-5">
            <div id="organizationFormContainer" style="display: none;">
                <div class="row">
                    <div class="col-6 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Organization Form</h5>
                                <form method="post" action="createorg.php">
                                    <input type="hidden" name="id" value='<?php echo htmlspecialchars($admin_id);?>'>
                                    <div class="mb-3">
                                        <label for="orgName" class="form-label">Organization Name</label>
                                        <input type="text" class="form-control" id="orgName" name="orgName" placeholder="Enter organization name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgEmail" class="form-label">Organization Email</label>
                                        <input type="email" class="form-control" id="orgEmail" name="orgEmail" placeholder="Enter organization email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgPhone" class="form-label">Organization phone number</label>
                                        <input type="text" class="form-control" id="orgPhone" name="orgPhone" placeholder="Enter organization phone number" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgImg" class="form-label">Profile photo (optional)</label>
                                        <input type="text" class="form-control" id="orgImg" name="orgImg" placeholder="Enter image url">
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgDesc" class="form-label">Organization description (optional)</label>
                                        <textarea class="form-control" name="orgDesc" id="orgDesc" rows="3" placeholder="Enter description here"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgAddress" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="orgAddress" name="orgAddress" placeholder="Enter organization address" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgCity" class="form-label">City</label>
                                        <input type="text" class="form-control" id="orgCity" name="orgCity" placeholder="Enter city here" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgPcode" class="form-label">Postcode</label>
                                        <input type="text" class="form-control" id="orgPcode" name="orgPcode" placeholder="Enter postcode here" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgBid" class="form-label">Business ID/Y-tunnus (if applicable)</label>
                                        <input type="text" class="form-control" id="orgBid" name="orgBid">
                                    </div>
                                    <div class="mb-3">
                                        <label for="orgType" class="form-label">Organization type</label>
                                        <select class="form-select" id="orgType" name="orgType" required>
                                            <option value="" selected disabled>Select type</option>
                                            <option value="Association">Association</option>
                                            <option value="Private">Private</option>
                                            <option value="Limited company">Limited company</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary" name="submitOrg">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Check if admin has organization and display form accordingly
            var hasOrganization = <?php echo $has_organization ? 'true' : 'false'; ?>;
            
            if (!hasOrganization) {
                document.getElementById('organizationFormContainer').style.display = 'block';
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
