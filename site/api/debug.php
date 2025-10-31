<?php
// Debug file to test API endpoints
echo "<h1>API Debug Test</h1>";

// Test 1: Check if conn.php exists
echo "<h2>Test 1: Connection File</h2>";
$conn_path = __DIR__ . '/../conn.php';
if (file_exists($conn_path)) {
    echo "✅ Connection file found at: $conn_path<br>";
    include $conn_path;
    echo "✅ Connection file included successfully<br>";
} else {
    echo "❌ Connection file NOT found at: $conn_path<br>";
}

// Test 2: Try to create connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    $link = createMysqliConnection();
    echo "✅ Database connection successful<br>";
    echo "Connection object: " . get_class($link) . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Test 3: Check if posts table exists
echo "<h2>Test 3: Posts Table</h2>";
if (isset($link)) {
    $result = mysqli_query($link, "SHOW TABLES LIKE 'posts'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✅ Posts table exists<br>";
        
        // Get table structure
        $result2 = mysqli_query($link, "DESCRIBE posts");
        echo "<h3>Posts Table Structure:</h3><ul>";
        while ($row = mysqli_fetch_assoc($result2)) {
            echo "<li>" . $row['Field'] . " (" . $row['Type'] . ")</li>";
        }
        echo "</ul>";
        
        // Count posts
        $result3 = mysqli_query($link, "SELECT COUNT(*) as count FROM posts");
        $count = mysqli_fetch_assoc($result3)['count'];
        echo "<p>Total posts in database: <strong>$count</strong></p>";
        
        // Get first post
        if ($count > 0) {
            $result4 = mysqli_query($link, "SELECT * FROM posts LIMIT 1");
            $post = mysqli_fetch_assoc($result4);
            echo "<h3>Sample Post:</h3><pre>" . print_r($post, true) . "</pre>";
        }
        
    } else {
        echo "❌ Posts table does not exist<br>";
    }
}

// Test 4: Check organizations table
echo "<h2>Test 4: Organizations Table</h2>";
if (isset($link)) {
    $result = mysqli_query($link, "SHOW TABLES LIKE 'organizations'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✅ Organizations table exists<br>";
        
        // Count organizations
        $result3 = mysqli_query($link, "SELECT COUNT(*) as count FROM organizations");
        $count = mysqli_fetch_assoc($result3)['count'];
        echo "<p>Total organizations in database: <strong>$count</strong></p>";
    } else {
        echo "❌ Organizations table does not exist<br>";
    }
}

// Test 5: Test the actual API query
echo "<h2>Test 5: API Query Test</h2>";
if (isset($link)) {
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
    ORDER BY p.title ASC
    LIMIT 5";
    
    $result = mysqli_query($link, $query);
    
    if ($result) {
        echo "✅ Query executed successfully<br>";
        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
        echo "<p>Found " . count($posts) . " posts</p>";
        echo "<pre>" . print_r($posts, true) . "</pre>";
    } else {
        echo "❌ Query failed: " . mysqli_error($link) . "<br>";
    }
}

if (isset($link)) {
    mysqli_close($link);
}
?>
