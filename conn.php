<?php
// Database configuration
$server = "localhost";
$username = "root";
$password = "";
$db = "hobbly";

// Set charset for proper handling of Finnish characters
$charset = "utf8mb4";

// Function to create database connection

function createMysqliConnection() {
    global $server, $username, $password, $db;
    
    $link = mysqli_connect($server, $username, $password, $db);
    
    if ($link === false) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Set charset for proper Finnish character handling
    mysqli_set_charset($link, "utf8mb4");
    
    return $link;
}
?>
