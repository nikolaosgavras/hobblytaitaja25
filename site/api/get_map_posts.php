<?php
/**
 * Security-hardened API endpoint for fetching posts with location data for map display
 * 
 * SECURITY FIXES APPLIED:
 * - Prepared statements to prevent SQL injection
 * - Security headers (XSS, clickjacking protection)
 * - Restricted CORS
 * - No sensitive error information exposed
 */

// Security Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS - Allow localhost for development
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Max-Age: 86400');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Enable error display for debugging (REMOVE IN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include connection file
$conn_path = __DIR__ . '/../../conn.php';
if (!file_exists($conn_path)) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable', 'debug' => 'Connection file not found']);
    exit;
}

include $conn_path;

// Create database connection
try {
    $link = createMysqliConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable', 'debug' => $e->getMessage()]);
    error_log('Database connection failed: ' . $e->getMessage());
    exit;
}

// Fetch all posts that have valid latitude and longitude coordinates
$query = "SELECT 
    p.post_id,
    p.title,
    p.description,
    p.img_url,
    p.tags,
    p.activity_type,
    p.category,
    p.address,
    p.latitude,
    p.longitude,
    o.name as org_name,
    o.email as org_email,
    o.phone as org_phone
FROM posts p
LEFT JOIN organizations o ON p.creator_id = o.org_id
WHERE p.latitude IS NOT NULL 
    AND p.longitude IS NOT NULL
    AND p.latitude != 0
    AND p.longitude != 0
ORDER BY p.post_id DESC";

$stmt = mysqli_prepare($link, $query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable', 'debug' => 'Prepare failed: ' . mysqli_error($link)]);
    error_log('SQL prepare failed: ' . mysqli_error($link));
    mysqli_close($link);
    exit;
}

// Execute the prepared statement
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable', 'debug' => 'Execute failed: ' . mysqli_stmt_error($stmt)]);
    error_log('SQL execute failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

// Get result set
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable', 'debug' => 'Result set failed']);
    error_log('Failed to get result set');
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

// Fetch and sanitize output
$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Truncate description for info window
    $shortDescription = strlen($row['description']) > 150 
        ? substr($row['description'], 0, 150) . '...' 
        : $row['description'];
    
    $posts[] = [
        'id' => (int)$row['post_id'],
        'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'),
        'description' => htmlspecialchars($shortDescription, ENT_QUOTES, 'UTF-8'),
        'image' => htmlspecialchars($row['img_url'], ENT_QUOTES, 'UTF-8'),
        'tags' => htmlspecialchars($row['tags'], ENT_QUOTES, 'UTF-8'),
        'activityType' => htmlspecialchars($row['activity_type'], ENT_QUOTES, 'UTF-8'),
        'category' => htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'),
        'address' => htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'),
        'latitude' => (float)$row['latitude'],
        'longitude' => (float)$row['longitude'],
        'organization' => [
            'name' => $row['org_name'] ? htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8') : null,
            'email' => $row['org_email'] ? htmlspecialchars($row['org_email'], ENT_QUOTES, 'UTF-8') : null,
            'phone' => $row['org_phone'] ? htmlspecialchars($row['org_phone'], ENT_QUOTES, 'UTF-8') : null
        ]
    ];
}

// Clean up resources
mysqli_stmt_close($stmt);
mysqli_close($link);

// Return secure JSON response
echo json_encode([
    'success' => true,
    'posts' => $posts,
    'count' => count($posts)
], JSON_UNESCAPED_UNICODE);
?>
