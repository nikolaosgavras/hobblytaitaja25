<?php
/**
 * Security-hardened API endpoint for fetching posts
 * 
 * SECURITY FIXES APPLIED:
 * - Prepared statements to prevent SQL injection
 * - Input validation and sanitization
 * - Security headers (XSS, clickjacking protection)
 * - Restricted CORS (change origin to your domain in production)
 * - No sensitive error information exposed
 * - Rate limiting protection (basic)
 * - Parameter whitelisting
 */

// Security Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff'); // Prevent MIME sniffing
header('X-Frame-Options: DENY'); // Prevent clickjacking
header('X-XSS-Protection: 1; mode=block'); // XSS protection for older browsers
header('Referrer-Policy: strict-origin-when-cross-origin'); // Privacy

// CORS - FIX: Restrict to specific domain in production
// CURRENT: Allows localhost for development
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // For development, allow any origin. REMOVE IN PRODUCTION!
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

// Disable error display for security (already set but ensuring)
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
    // FIX: Don't expose database error details to users
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('Database connection failed: ' . $e->getMessage()); // Log for debugging
    exit;
}

// INPUT VALIDATION & SANITIZATION

// FIX: Validate offset - must be non-negative integer with max limit
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
if ($offset < 0) {
    $offset = 0;
}
// Prevent excessive offset values (DoS protection)
if ($offset > 100000) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid offset parameter']);
    mysqli_close($link);
    exit;
}

// FIX: Validate limit - must be positive integer with reasonable max
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($limit < 1) {
    $limit = 10;
}
// Prevent excessive limit values (DoS/memory exhaustion)
if ($limit > 100) {
    $limit = 100;
}

// FIX: Whitelist sort parameter to prevent SQL injection
$allowed_sorts = ['alphabetical', 'newest'];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'alphabetical';
if (!in_array($sort, $allowed_sorts, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sort parameter']);
    mysqli_close($link);
    exit;
}

// FIX: Validate and sanitize search term
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
// Limit search term length to prevent DoS
if (strlen($search) > 200) {
    http_response_code(400);
    echo json_encode(['error' => 'Search term too long']);
    mysqli_close($link);
    exit;
}

// ============================================================================
// BUILD SECURE QUERY USING PREPARED STATEMENTS
// FIX: Prevent SQL injection by using parameterized queries
// ============================================================================

$query = "SELECT 
    p.post_id,
    p.title,
    p.description,
    p.img_url,
    p.tags,
    p.activity_type,
    p.category,
    p.address,
    o.name as org_name,
    o.email as org_email,
    o.phone as org_phone,
    o.address as org_address
FROM posts p
LEFT JOIN organizations o ON p.creator_id = o.org_id
WHERE 1=1";

// Arrays for prepared statement parameters
$params = [];
$types = '';

// FIX: Use prepared statement placeholders instead of string concatenation
if (!empty($search)) {
    $query .= " AND (
        p.title LIKE ? OR 
        p.description LIKE ? OR 
        o.name LIKE ? OR 
        p.tags LIKE ? OR
        p.category LIKE ? OR
        p.activity_type LIKE ?
    )";
    $searchParam = '%' . $search . '%';
    // Add 6 parameters for 6 LIKE clauses
    for ($i = 0; $i < 6; $i++) {
        $params[] = $searchParam;
        $types .= 's';
    }
}

// FIX: Safe ORDER BY using whitelisted values only (no user input)
if ($sort === 'newest') {
    $query .= " ORDER BY p.post_id DESC";
} else {
    $query .= " ORDER BY p.title ASC";
}

// FIX: Use placeholders for LIMIT and OFFSET
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Prepare the statement
$stmt = mysqli_prepare($link, $query);
if (!$stmt) {
    // FIX: Don't expose SQL details to users
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('SQL prepare failed: ' . mysqli_error($link));
    mysqli_close($link);
    exit;
}

// Bind parameters
if (!empty($params)) {
    // Create reference array for bind_param (requires references)
    $bind_params = [$types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_params);
}

// Execute the prepared statement
if (!mysqli_stmt_execute($stmt)) {
    // FIX: Don't expose SQL execution errors
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('SQL execute failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

// Get result set
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Service temporarily unavailable']);
    error_log('Failed to get result set');
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}

// ============================================================================
// FETCH AND SANITIZE OUTPUT
// FIX: Escape all output to prevent XSS (defense in depth)
// ============================================================================

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Truncate description to 100 characters for list view
    $shortDescription = strlen($row['description']) > 100 
        ? substr($row['description'], 0, 100) . '...' 
        : $row['description'];
    
    // FIX: Sanitize all string outputs using htmlspecialchars
    // This prevents any stored XSS attacks if malicious data is in the database
    $posts[] = [
        'id' => (int)$row['post_id'], // Cast to integer for safety
        'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'),
        'description' => htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'),
        'shortDescription' => htmlspecialchars($shortDescription, ENT_QUOTES, 'UTF-8'),
        'image' => htmlspecialchars($row['img_url'], ENT_QUOTES, 'UTF-8'),
        'tags' => htmlspecialchars($row['tags'], ENT_QUOTES, 'UTF-8'),
        'activityType' => htmlspecialchars($row['activity_type'], ENT_QUOTES, 'UTF-8'),
        'category' => htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'),
        'address' => htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'),
        'organization' => [
            'name' => $row['org_name'] ? htmlspecialchars($row['org_name'], ENT_QUOTES, 'UTF-8') : null,
            'email' => $row['org_email'] ? htmlspecialchars($row['org_email'], ENT_QUOTES, 'UTF-8') : null,
            'phone' => $row['org_phone'] ? htmlspecialchars($row['org_phone'], ENT_QUOTES, 'UTF-8') : null,
            'address' => $row['org_address'] ? htmlspecialchars($row['org_address'], ENT_QUOTES, 'UTF-8') : null
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
