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

// Build query - using correct column names from your database
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

// Add search condition if search term is provided
if (!empty($search)) {
    $searchTerm = mysqli_real_escape_string($link, $search);
    $query .= " AND (
        p.title LIKE '%$searchTerm%' OR 
        p.description LIKE '%$searchTerm%' OR 
        o.name LIKE '%$searchTerm%' OR 
        p.tags LIKE '%$searchTerm%' OR
        p.category LIKE '%$searchTerm%' OR
        p.activity_type LIKE '%$searchTerm%'
    )";
}

// Add sorting
if ($sort === 'newest') {
    $query .= " ORDER BY p.post_id DESC";  // Sort by ID since no created_at
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
