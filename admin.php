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
if ($stmt = $link->prepare('SELECT post_id, title FROM posts ORDER BY post_id')) {
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
                 <div class="col-8">
                    <h3>User List</h3>
                    <div class="table-responsive">
                        <table class="table table-light table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username/Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No users found</td>
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
                                                <a href="edituser.php?id=<?php echo $user['paneladmin_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="deleteuser.php?id=<?php echo $user['paneladmin_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-12 col-sm-12 mt-5">
                    <h3>Post list</h3>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="postSearch" class="form-control" placeholder="Search by ID or Title...">
                        </div>
                        <div class="col-md-6">
                            <select id="postSort" class="form-select">
                                <option value="">Sort by...</option>
                                <option value="id-asc">ID (Ascending)</option>
                                <option value="id-desc">ID (Descending)</option>
                                <option value="title-asc">Title (A-Z)</option>
                                <option value="title-desc">Title (Z-A)</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-light table-hover" id="postsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Post ID</th>
                                    <th>Post Title</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No posts found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['post_id']); ?></td>
                                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                                            <td>
                                                <a href="editpostadmin.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="deletepostadmin.php?id=<?php echo $post['post_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        // Posts data from PHP
        const postsData = <?php echo json_encode($posts); ?>;
        let filteredPosts = [...postsData];

        // Render posts table
        function renderPostsTable(posts) {
            const tbody = document.querySelector('#postsTable tbody');
            if (posts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center">No posts found</td></tr>';
                return;
            }
            
            tbody.innerHTML = posts.map(post => `
                <tr>
                    <td>${escapeHtml(post.post_id)}</td>
                    <td>${escapeHtml(post.title)}</td>
                    <td>
                        <a href="editpostadmin.php?id=${post.post_id}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="deletepostadmin.php?id=${post.post_id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                    </td>
                </tr>
            `).join('');
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