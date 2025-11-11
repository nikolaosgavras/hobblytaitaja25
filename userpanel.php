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
$org_data = null;
$total_posts = 0;
$active_posts = 0;
$upcoming_posts = 0;
$recent_posts = [];

if ($stmt = $link->prepare('SELECT * FROM organizations WHERE org_id = ?')) {
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $has_organization = true;
        $org_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// If organization exists, get post statistics
if ($has_organization) {
    // Get total posts count
    if ($stmt = $link->prepare('SELECT COUNT(*) as total FROM posts WHERE creator_id = ?')) {
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total_posts = $row['total'];
        $stmt->close();
    }
    
    // Get active posts (currently happening)
    if ($stmt = $link->prepare('SELECT COUNT(*) as active FROM posts WHERE creator_id = ? AND event_start <= NOW() AND event_end >= NOW()')) {
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $active_posts = $row['active'];
        $stmt->close();
    }
    
    // Get upcoming posts
    if ($stmt = $link->prepare('SELECT COUNT(*) as upcoming FROM posts WHERE creator_id = ? AND event_start > NOW()')) {
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $upcoming_posts = $row['upcoming'];
        $stmt->close();
    }
    
    // Get recent posts (last 5)
    if ($stmt = $link->prepare('SELECT post_id, title, event_start, event_end, activity_type FROM posts WHERE creator_id = ? ORDER BY post_id DESC LIMIT 5')) {
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recent_posts[] = $row;
        }
        $stmt->close();
    }
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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="userpanel.php">
                    <img src="images/logos/green_logo@low-res.png" alt="Hobbly Logo" class="navbar-logo">
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
                                <li><a class="dropdown-item" href="profilesettings.php">
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

            <!-- Dashboard for users with organization -->
            <?php if ($has_organization): ?>
            <div class="row g-4">
                <!-- Statistics Cards -->
                <div class="col-md-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                            <h3 class="mt-3 mb-1"><?php echo $total_posts; ?></h3>
                            <p class="mb-0">Total Posts</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check" style="font-size: 3rem;"></i>
                            <h3 class="mt-3 mb-1"><?php echo $active_posts; ?></h3>
                            <p class="mb-0">Active Events</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                            <h3 class="mt-3 mb-1"><?php echo $upcoming_posts; ?></h3>
                            <p class="mb-0">Upcoming Events</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organization Info and Quick Actions -->
            <div class="row g-4 mt-2">
                <!-- Organization Information -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-building"></i> Organization Information
                            </h5>
                            <hr>
                            <?php if ($org_data): ?>
                            <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="bi bi-building"></i> Name:</div>
                                <div class="col-7"><strong><?php echo htmlspecialchars($org_data['name']); ?></strong></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="bi bi-envelope"></i> Email:</div>
                                <div class="col-7"><?php echo htmlspecialchars($org_data['email']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="bi bi-telephone"></i> Phone:</div>
                                <div class="col-7"><?php echo htmlspecialchars($org_data['phone']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="bi bi-tag"></i> Type:</div>
                                <div class="col-7">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($org_data['organization_type']); ?></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="bi bi-geo-alt"></i> Address:</div>
                                <div class="col-7"><?php echo htmlspecialchars($org_data['address']); ?>, <?php echo htmlspecialchars($org_data['city']); ?> <?php echo htmlspecialchars($org_data['postcode']); ?></div>
                            </div>
                            <?php if (!empty($org_data['businessid'])): ?>
                            <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="bi bi-briefcase"></i> Business ID:</div>
                                <div class="col-7"><?php echo htmlspecialchars($org_data['businessid']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($org_data['description'])): ?>
                            <div class="row mb-2">
                                <div class="col-12 text-muted"><i class="bi bi-card-text"></i> Description:</div>
                                <div class="col-12 mt-1">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($org_data['description'])); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <hr>
                            <a href="profilesettings.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit Organization
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-lightning-charge"></i> Quick Actions
                            </h5>
                            <hr>
                            <div class="d-grid gap-3">
                                <a href="posts.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-plus-circle"></i> Create New Post
                                </a>
                                <a href="posts.php" class="btn btn-outline-primary">
                                    <i class="bi bi-list-ul"></i> View All My Posts
                                </a>
                                <a href="site/index.html" class="btn btn-outline-secondary">
                                    <i class="bi bi-globe"></i> View Public Site
                                </a>
                                <a href="site/search.html" class="btn btn-outline-info">
                                    <i class="bi bi-search"></i> Search Events
                                </a>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-book"></i> Quick Links
                            </h6>
                            <div class="list-group list-group-flush">
                                <a href="changepassword.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-key"></i> Change Password
                                </a>
                                <a href="profilesettings.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-person-gear"></i> Profile Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Posts -->
            <?php if (!empty($recent_posts)): ?>
            <div class="row g-4 mt-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-clock-history"></i> Recent Posts
                            </h5>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-hash"></i> ID</th>
                                            <th><i class="bi bi-card-text"></i> Title</th>
                                            <th><i class="bi bi-tag"></i> Type</th>
                                            <th><i class="bi bi-calendar"></i> Event Start</th>
                                            <th><i class="bi bi-calendar"></i> Event End</th>
                                            <th><i class="bi bi-info-circle"></i> Status</th>
                                            <th><i class="bi bi-gear"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_posts as $post): 
                                            $now = new DateTime();
                                            $start = new DateTime($post['event_start']);
                                            $end = new DateTime($post['event_end']);
                                            
                                            if ($now >= $start && $now <= $end) {
                                                $status = '<span class="badge bg-success">Active</span>';
                                            } elseif ($now < $start) {
                                                $status = '<span class="badge bg-info">Upcoming</span>';
                                            } else {
                                                $status = '<span class="badge bg-secondary">Ended</span>';
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['post_id']); ?></td>
                                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($post['activity_type']); ?></span></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($post['event_start'])); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($post['event_end'])); ?></td>
                                            <td><?php echo $status; ?></td>
                                            <td>
                                                <a href="editpost.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="posts.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i> View All Posts
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- Organization Creation Form (for users without organization) -->
            <div id="organizationFormContainer">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i> <strong>Get Started!</strong> Create your organization profile to start posting events and activities.
                        </div>
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
            <?php endif; ?>
        </div>
        
        <script>
            // Legacy code for organization check - kept for backward compatibility
            var hasOrganization = <?php echo $has_organization ? 'true' : 'false'; ?>;
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
