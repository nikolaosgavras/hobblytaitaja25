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

// Get post ID
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId === 0) {
    echo json_encode(['error' => 'Post ID is required']);
    exit;
}

// Using correct column names from database
$query = "SELECT 
    p.post_id,
    p.title,
    p.description,
    p.img_url,
    p.created_at,
    p.tags,
    p.activity_type,
    p.category,
    p.url,
    p.address,
    p.latitude,
    p.longitude,
    o.org_name,
    o.org_email,
    o.org_phone,
    o.org_address
FROM posts p
LEFT JOIN organizations o ON p.creator_id = o.org_id
WHERE p.post_id = $postId";

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

$post = mysqli_fetch_assoc($result);

if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    mysqli_close($link);
    exit;
}

mysqli_close($link);

echo json_encode([
    'success' => true,
    'post' => [
        'id' => $post['post_id'],
        'title' => $post['title'],
        'description' => $post['description'],
        'image' => $post['img_url'],
        'createdAt' => $post['created_at'],
        'tags' => $post['tags'],
        'activityType' => $post['activity_type'],
        'category' => $post['category'],
        'url' => $post['url'],
        'address' => $post['address'],
        'latitude' => $post['latitude'],
        'longitude' => $post['longitude'],
        'organization' => [
            'name' => $post['org_name'],
            'email' => $post['org_email'],
            'phone' => $post['org_phone'],
            'address' => $post['org_address']
        ]
    ]
]);
?>
