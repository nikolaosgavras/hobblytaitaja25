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




mysqli_close($link);

?>


<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>User Panel - Hobbly</title>
        <link rel="icon" type="image/png" href="images/symbols/symbol_primary@low-res.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="userpanel.php">
                    <img src="images/logos/logo_primary_1@high-res.png" alt="Hobbly Logo" class="navbar-logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="posts.php">
                                <i class="bi bi-file-earmark-text"></i> Posts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="site/index.html">
                                <i class="bi bi-globe"></i> View Site
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear"></i> Account Settings
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="changepassword.php">
                                    <i class="bi bi-key"></i> Change Password
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="bi bi-person"></i> Profile Settings
                                </a></li>
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
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['account_name']); ?>
                        </span>
                        <form method="post" class="d-inline">
                            <button class="btn btn-outline-danger btn-sm" type="submit" name="logout">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        
        <div class="container mt-4 mb-5">
            <!-- Page Header -->
            <div class="admin-header text-center mb-4">
                <h1><i class="bi bi-person-badge"></i> User Panel</h1>
                <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['account_name']); ?>!</p>
            </div>

            <div id="organizationFormContainer" style="display: none;">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-building"></i> Create Your Organization
                                </h5>
                                <p class="text-muted mb-4">Please complete your organization profile to start posting events.</p>
                                <form method="post" action="createorg.php">
                                    <input type="hidden" name="id" value='<?php echo htmlspecialchars($admin_id);?>'>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="orgName" class="form-label">
                                                <i class="bi bi-building"></i> Organization Name *
                                            </label>
                                            <input type="text" class="form-control" id="orgName" name="orgName" placeholder="Enter organization name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="orgEmail" class="form-label">
                                                <i class="bi bi-envelope"></i> Organization Email *
                                            </label>
                                            <input type="email" class="form-control" id="orgEmail" name="orgEmail" placeholder="contact@organization.com" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="orgPhone" class="form-label">
                                                <i class="bi bi-telephone"></i> Phone Number *
                                            </label>
                                            <input type="text" class="form-control" id="orgPhone" name="orgPhone" placeholder="+358 12 345 6789" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="orgType" class="form-label">
                                                <i class="bi bi-tag"></i> Organization Type *
                                            </label>
                                            <select class="form-select" id="orgType" name="orgType" required>
                                                <option value="" selected disabled>Select type</option>
                                                <option value="Association">Association</option>
                                                <option value="Private">Private</option>
                                                <option value="Limited company">Limited company</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="orgImg" class="form-label">
                                            <i class="bi bi-image"></i> Profile Photo URL (Optional)
                                        </label>
                                        <input type="text" class="form-control" id="orgImg" name="orgImg" placeholder="https://example.com/logo.png">
                                    </div>

                                    <div class="mb-3">
                                        <label for="orgDesc" class="form-label">
                                            <i class="bi bi-card-text"></i> Organization Description (Optional)
                                        </label>
                                        <textarea class="form-control" name="orgDesc" id="orgDesc" rows="3" placeholder="Tell us about your organization..."></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="orgAddress" class="form-label">
                                                <i class="bi bi-geo-alt"></i> Address *
                                            </label>
                                            <input type="text" class="form-control" id="orgAddress" name="orgAddress" placeholder="Street address" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="orgCity" class="form-label">
                                                <i class="bi bi-pin-map"></i> City *
                                            </label>
                                            <input type="text" class="form-control" id="orgCity" name="orgCity" placeholder="City" required>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="orgPcode" class="form-label">
                                                <i class="bi bi-mailbox"></i> Postcode *
                                            </label>
                                            <input type="text" class="form-control" id="orgPcode" name="orgPcode" placeholder="00100" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="orgBid" class="form-label">
                                            <i class="bi bi-briefcase"></i> Business ID/Y-tunnus (If applicable)
                                        </label>
                                        <input type="text" class="form-control" id="orgBid" name="orgBid" placeholder="1234567-8">
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg" name="submitOrg">
                                            <i class="bi bi-check-circle"></i> Create Organization
                                        </button>
                                    </div>
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
