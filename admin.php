<?php include 'conn.php';
    
$link = createMysqliConnection();  

session_start();

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: adminlogin.php');
    exit;
}

if (!isset($_SESSION['account_loggedin'])) {
    header('Location: adminlogin.php');
    exit;
}

if ($_SESSION['account_role'] !== 'staff') {
    header('Location: userpanel.php');
    exit;
}

// Fetch all users from the database
$users = [];
if ($stmt = $link->prepare('SELECT paneladmin_id, username, role FROM admins ORDER BY username')) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
}

$posts = [];
if ($stmt = $link->prepare('SELECT post_id, title, event_start, event_end FROM posts ORDER BY post_id')) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}



mysqli_close($link);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,minimum-scale=1">
        <title>Admin Panel - Hobbly</title>
        <link rel="icon" type="image/png" href="images/symbols/symbol_primary@low-res.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="admin.php">
                    <img src="images/logos/logo_primary_1@high-res.png" alt="Hobbly Logo" class="navbar-logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="site/index.html">
                                <i class="bi bi-globe"></i> View Site
                            </a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['account_name']); ?>
                        </span>
                        <form method="post">
                            <button type="submit" name="logout" class="btn btn-outline-danger btn-sm">
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
                <h1><i class="bi bi-gear-fill"></i> Admin Panel</h1>
                <p class="text-muted mb-0">Manage users, posts, and system settings</p>
            </div>

            <div class="row g-4">
                <!-- Add New User Section -->
                <div class="col-lg-4 col-md-12">
                    <div class="panel-card h-100">
                        <div class="section-header">
                            <h3><i class="bi bi-person-plus"></i> Add New User</h3>
                        </div>
                        <form action="accountcreation.php" method="post">
                            <div class="mb-3">
                                <label for="accountemail" class="form-label">
                                    <i class="bi bi-envelope"></i> Email address
                                </label>
                                <input type="email" class="form-control" id="accountemail" name="accountemail" placeholder="user@example.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="accountpassword" class="form-label">
                                    <i class="bi bi-lock"></i> Password
                                </label>
                                <input type="password" class="form-control" id="accountpassword" name="accountpassword" placeholder="Enter password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-shield-check"></i> Role</label>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="staff" name="accountrole" value="staff" required>
                                    <label class="form-check-label" for="staff">
                                        Staff <span class="badge bg-primary">Admin</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="user" name="accountrole" value="user">
                                    <label class="form-check-label" for="user">
                                        User <span class="badge bg-secondary">Regular</span>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle"></i> Create User
                            </button>
                        </form>
                    </div>
                </div>

                <!-- User List Section -->
                <div class="col-lg-8 col-md-12">
                    <div class="panel-card">
                        <div class="section-header">
                            <h3><i class="bi bi-people"></i> User List</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-light table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="bi bi-hash"></i> ID</th>
                                        <th><i class="bi bi-person"></i> Username/Email</th>
                                        <th><i class="bi bi-shield"></i> Role</th>
                                        <th><i class="bi bi-gear"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                <i class="bi bi-inbox"></i> No users found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['paneladmin_id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $user['role'] === 'staff' ? 'bg-primary' : 'bg-secondary'; ?>">
                                                        <?php echo htmlspecialchars($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="edituser.php?id=<?php echo $user['paneladmin_id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <a href="deleteuser.php?id=<?php echo $user['paneladmin_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Post List Section -->
                <div class="col-12">
                    <div class="panel-card">
                        <div class="section-header">
                            <h3><i class="bi bi-file-earmark-text"></i> Post List</h3>
                        </div>
                        <div class="filter-bar mb-3">
                            <input type="text" id="postSearch" class="form-control" placeholder="🔍 Search by ID or Title...">
                            <select id="postSort" class="form-select">
                                <option value="">Sort by...</option>
                                <option value="id-asc">ID (Ascending)</option>
                                <option value="id-desc">ID (Descending)</option>
                                <option value="title-asc">Title (A-Z)</option>
                                <option value="title-desc">Title (Z-A)</option>
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-light table-hover" id="postsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="bi bi-hash"></i> Post ID</th>
                                        <th><i class="bi bi-card-text"></i> Post Title</th>
                                        <th><i class="bi bi-calendar-check"></i> Start Time</th>
                                        <th><i class="bi bi-calendar-x"></i> End Time</th>
                                        <th><i class="bi bi-gear"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($posts)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                <i class="bi bi-inbox"></i> No posts found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($post['post_id']); ?></td>
                                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                <td><?php echo $post['event_start'] ? date('Y-m-d H:i', strtotime($post['event_start'])) : 'N/A'; ?></td>
                                                <td><?php echo $post['event_end'] ? date('Y-m-d H:i', strtotime($post['event_end'])) : 'N/A'; ?></td>
                                                <td>
                                                    <a href="editpostadmin.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <a href="deletepostadmin.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        // Posts data from PHP
        const postsData = <?php echo json_encode($posts); ?>;
        let filteredPosts = [...postsData];

        // Render posts table
        function renderPostsTable(posts) {
            const tbody = document.querySelector('#postsTable tbody');
            if (posts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No posts found</td></tr>';
                return;
            }
            
            tbody.innerHTML = posts.map(post => `
                <tr>
                    <td>${escapeHtml(post.post_id)}</td>
                    <td>${escapeHtml(post.title)}</td>
                    <td>${formatDateTime(post.event_start)}</td>
                    <td>${formatDateTime(post.event_end)}</td>
                    <td>
                        <a href="editpostadmin.php?id=${post.post_id}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="deletepostadmin.php?id=${post.post_id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                    </td>
                </tr>
            `).join('');
        }

        // Format datetime string
        function formatDateTime(datetime) {
            if (!datetime) return 'N/A';
            const date = new Date(datetime);
            return date.toLocaleString('fi-FI', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            }).replace(',', '');
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Search functionality
        document.getElementById('postSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filteredPosts = postsData.filter(post => 
                post.post_id.toString().includes(searchTerm) ||
                post.title.toLowerCase().includes(searchTerm)
            );
            applySorting();
        });

        // Sort functionality
        document.getElementById('postSort').addEventListener('change', function() {
            applySorting();
        });

        function applySorting() {
            const sortValue = document.getElementById('postSort').value;
            let sorted = [...filteredPosts];
            
            switch(sortValue) {
                case 'id-asc':
                    sorted.sort((a, b) => parseInt(a.post_id) - parseInt(b.post_id));
                    break;
                case 'id-desc':
                    sorted.sort((a, b) => parseInt(b.post_id) - parseInt(a.post_id));
                    break;
                case 'title-asc':
                    sorted.sort((a, b) => a.title.localeCompare(b.title));
                    break;
                case 'title-desc':
                    sorted.sort((a, b) => b.title.localeCompare(a.title));
                    break;
            }
            
            renderPostsTable(sorted);
        }

        // Initial render
        renderPostsTable(postsData);
    </script>
    </body>
</html>