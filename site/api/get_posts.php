<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
error_reporting(0);
ini_set('display_errors', 0);

// Include connection file
$conn_path = __DIR__ . '/../../conn.php';
if (!file_exists($conn_path)) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection file not found']);
    exit;
}

include $conn_path;

// Create database connection
try {
    $link = createMysqliConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get parameters
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'alphabetical'; // alphabetical or newest
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query - using correct column names from database
$query = "SELECT 
    p.post_id,
    p.title,
    p.description,
    p.img_url,
    p.created_at,
    p.tags,
    p.activity_type,
    p.category,
    p.address,
    o.org_name,
    o.org_email,
    o.org_phone,
    o.org_address
FROM posts p
LEFT JOIN organizations o ON p.creator_id = o.org_id
WHERE 1=1";

// Add search condition if search term is provided
if (!empty($search)) {
    $searchTerm = mysqli_real_escape_string($link, $search);
    $query .= " AND (
        p.title LIKE '%$searchTerm%' OR 
        p.description LIKE '%$searchTerm%' OR 
        o.org_name LIKE '%$searchTerm%' OR 
        p.tags LIKE '%$searchTerm%' OR
        p.category LIKE '%$searchTerm%' OR
        p.activity_type LIKE '%$searchTerm%'
    )";
}

// Add sorting
if ($sort === 'newest') {
    $query .= " ORDER BY p.created_at DESC";
} else {
    $query .= " ORDER BY p.title ASC";
}

// Add pagination
$query .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($link, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Query failed: ' . mysqli_error($link),
        'query' => $query
    ]);
    mysqli_close($link);
    exit;
}

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Truncate description to 100 characters for list view
    $shortDescription = strlen($row['description']) > 100 
        ? substr($row['description'], 0, 100) . '...' 
        : $row['description'];
    
    $posts[] = [
        'id' => $row['post_id'],
        'title' => $row['title'],
        'description' => $row['description'],
        'shortDescription' => $shortDescription,
        'image' => $row['img_url'],
        'createdAt' => $row['created_at'],
        'tags' => $row['tags'],
        'activityType' => $row['activity_type'],
        'category' => $row['category'],
        'address' => $row['address'],
        'organization' => [
            'name' => $row['org_name'],
            'email' => $row['org_email'],
            'phone' => $row['org_phone'],
            'address' => $row['org_address']
        ]
    ];
}

mysqli_close($link);

echo json_encode([
    'success' => true,
    'posts' => $posts,
    'count' => count($posts)
]);
?>
