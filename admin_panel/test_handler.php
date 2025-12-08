<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";

// Test include config
if (file_exists('../assets/config.php')) {
    echo "Config file exists!<br>";
    include('../assets/config.php');
    
    if ($conn) {
        echo "Database connected!<br>";
    } else {
        echo "Database connection failed!<br>";
    }
} else {
    echo "Config file NOT FOUND!<br>";
}

// Test session
session_start();
$_SESSION['uid'] = 1; // Fake user
echo "Session UID: " . $_SESSION['uid'] . "<br>";

// Test query
$query = "SELECT * FROM users LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result) {
    echo "Query works!<br>";
} else {
    echo "Query failed: " . mysqli_error($conn) . "<br>";
}

echo "All tests passed!";
?>