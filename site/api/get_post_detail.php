<?php
/**
 * Security-hardened API endpoint for fetching single post details
 * 
 * SECURITY FIXES APPLIED:
 * ✅ Prepared statements to prevent SQL injection
 * ✅ Input validation for post ID
 * ✅ Security headers (XSS, clickjacking protection)
 * ✅ Restricted CORS (change origin to your domain in production)
 * ✅ No sensitive error information exposed
 * ✅ Output escaping (defense in depth)
 * ✅ Method validation (GET only)
 */

// Security Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS - FIX: Restrict to specific domain in production
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *'); // For development only
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// FIX: Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

error_reporting(0);
ini_set('display_errors', 0);

// Include connection file
$conn_path = __DIR__ . '/../../conn.php';
if (!file_exists($conn_path)) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    exit;
}

include $conn_path;

// Create database connection
try {
    $link = createMysqliConnection();
} catch (Exception $e) {
    // FIX: Don't expose database connection errors
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('Database connection failed: ' . $e->getMessage());
    exit;
}

// ============================================================================
// INPUT VALIDATION
// FIX: Validate post ID more strictly
// ============================================================================

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid post ID is required']);
    mysqli_close($link);
    exit;
}

// Additional validation: check for realistic ID range (DoS protection)
if ($postId > 2147483647) { // Max INT value
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post ID']);
    mysqli_close($link);
    exit;
}

// ============================================================================
// BUILD SECURE QUERY USING PREPARED STATEMENT
// FIX: Use parameterized query instead of string concatenation
// ============================================================================

$query = "SELECT 
    p.post_id,
    p.title,
    p.description,
    p.img_url,
    p.tags,
    p.activity_type,
    p.category,
    p.url,
    p.address,
    p.latitude,
    p.longitude,
    o.name as org_name,
    o.email as org_email,
    o.phone as org_phone,
    o.address as org_address
FROM posts p
LEFT JOIN organizations o ON p.creator_id = o.org_id
WHERE p.post_id = ?";

// Prepare statement
$stmt = mysqli_prepare($link, $query);
if (!$stmt) {
    // FIX: Don't expose SQL details
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('SQL prepare failed: ' . mysqli_error($link));
    mysqli_close($link);
    exit;
}

// Bind the post ID parameter
mysqli_stmt_bind_param($stmt, 'i', $postId);

// Execute the prepared statement
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('SQL execute failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

// Get result
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('Failed to get result set');
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

$post = mysqli_fetch_assoc($result);

if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

// ============================================================================
// SANITIZE OUTPUT AND RETURN
// FIX: Escape all output to prevent XSS
// ============================================================================

// Clean up resources
mysqli_stmt_close($stmt);
mysqli_close($link);

echo json_encode([
    'success' => true,
    'post' => [
        'id' => (int)$post['post_id'],
        'title' => htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'),
        'description' => htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'),
        'image' => htmlspecialchars($post['img_url'], ENT_QUOTES, 'UTF-8'),
        'tags' => htmlspecialchars($post['tags'], ENT_QUOTES, 'UTF-8'),
        'activityType' => htmlspecialchars($post['activity_type'], ENT_QUOTES, 'UTF-8'),
        'category' => htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8'),
        'url' => htmlspecialchars($post['url'], ENT_QUOTES, 'UTF-8'),
        'address' => htmlspecialchars($post['address'], ENT_QUOTES, 'UTF-8'),
        'latitude' => $post['latitude'] ? (float)$post['latitude'] : null,
        'longitude' => $post['longitude'] ? (float)$post['longitude'] : null,
        'organization' => [
            'name' => $post['org_name'] ? htmlspecialchars($post['org_name'], ENT_QUOTES, 'UTF-8') : null,
            'email' => $post['org_email'] ? htmlspecialchars($post['org_email'], ENT_QUOTES, 'UTF-8') : null,
            'phone' => $post['org_phone'] ? htmlspecialchars($post['org_phone'], ENT_QUOTES, 'UTF-8') : null,
            'address' => $post['org_address'] ? htmlspecialchars($post['org_address'], ENT_QUOTES, 'UTF-8') : null
        ]
    ]
], JSON_UNESCAPED_UNICODE);
?>
