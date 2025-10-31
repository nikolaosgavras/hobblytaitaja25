<?php
// Direct test of get_posts.php with error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct API Test</h1>";
echo "<p>Testing get_posts.php directly...</p>";

// Include and test
$apiFile = __DIR__ . '/get_posts.php';
echo "<p>API file path: $apiFile</p>";
echo "<p>File exists: " . (file_exists($apiFile) ? 'Yes' : 'No') . "</p>";

if (file_exists($apiFile)) {
    echo "<h2>Including API file...</h2>";
    include $apiFile;
}
?>
