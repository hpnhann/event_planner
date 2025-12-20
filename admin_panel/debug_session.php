<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// DEBUG: Show session info
echo "<pre>";
echo "=== SESSION DEBUG ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session UID: " . (isset($_SESSION['uid']) ? $_SESSION['uid'] : 'NOT SET') . "\n";
echo "Session Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . "\n";
echo "\nFull Session Data:\n";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    echo "<h2 style='color: red;'>NOT LOGGED IN</h2>";
    echo "<p>Session UID is not set. You need to login first.</p>";
    echo "<a href='../login.php'>Go to Login</a>";
    exit();
}

// Check database for user role
require_once('../assets/config.php');

$uid = $_SESSION['uid'];
$query = "SELECT role, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

echo "<pre>";
echo "\n=== DATABASE CHECK ===\n";
echo "User ID: " . $uid . "\n";
echo "User Email: " . ($user ? $user['email'] : 'NOT FOUND') . "\n";
echo "User Role (DB): " . ($user ? $user['role'] : 'NOT FOUND') . "\n";
echo "</pre>";

if (!$user) {
    echo "<h2 style='color: red;'>USER NOT FOUND IN DATABASE</h2>";
    exit();
}

if ($user['role'] !== 'admin') {
    echo "<h2 style='color: orange;'>NOT ADMIN</h2>";
    echo "<p>Your role is: " . $user['role'] . "</p>";
    echo "<p>This page requires admin role.</p>";
    echo "<p>Options:</p>";
    echo "<ul>";
    echo "<li><a href='../index.php'>Go to Homepage</a></li>";
    echo "<li><a href='../logout.php'>Logout</a></li>";
    echo "</ul>";
    exit();
}

echo "<h2 style='color: green;'>✓ ALL CHECKS PASSED</h2>";
echo "<p>You are logged in as admin. The page should load now.</p>";
echo "<p><a href='manage_registrations_standalone.php'>Continue to Manage Registrations</a></p>";
?>