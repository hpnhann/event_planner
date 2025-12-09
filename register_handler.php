<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

if (!file_exists('assets/config.php')) {
    echo json_encode(['status' => 'error', 'message' => 'Config not found']);
    exit();
}

require_once('assets/config.php');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database failed']);
    exit();
}

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if (!isset($_POST['action']) || $_POST['action'] !== 'register') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit();
}

$user_email = $_SESSION['uid']; // Email
$event_id = intval($_POST['event_id']);
$full_name = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
$notes = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));

// ===== QUAN TRỌNG: Lấy USER INT ID từ email =====
$userQuery = "SELECT id FROM users WHERE id = ?";
$userStmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($userStmt, "s", $user_email);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);

if (mysqli_num_rows($userResult) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit();
}

$userData = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($userStmt);

// NẾU bảng users có cột int_id riêng, dùng nó
// Nếu không, dùng email làm user_id (nhưng phải VARCHAR trong event_registrations)
$user_id = $user_email; // Giữ nguyên email

// Validate
if (empty($full_name) || empty($phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
    exit();
}

if ($event_id < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid event ID']);
    exit();
}

// Check event
$sql = "SELECT id, event_title, max_volunteers, 
        (SELECT COUNT(*) FROM event_registrations 
         WHERE event_id = events.id AND status != 'cancelled') as registered_count
        FROM events 
        WHERE id = ? AND status = 'published'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['status' => 'error', 'message' => 'Event not found']);
    exit();
}

$event = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($event['registered_count'] >= $event['max_volunteers']) {
    echo json_encode(['status' => 'error', 'message' => 'Event is full']);
    exit();
}

// Check already registered
$sql = "SELECT id FROM event_registrations 
        WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $event_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['status' => 'error', 'message' => 'Already registered']);
    exit();
}
mysqli_stmt_close($stmt);

// ===== XÓA UNIQUE CONSTRAINT TRƯỚC KHI INSERT =====
// Chạy query này 1 LẦN trong phpMyAdmin:
// ALTER TABLE event_registrations DROP INDEX unique_registration;

// Insert
$sql = "INSERT INTO event_registrations (event_id, user_id, status, notes) 
        VALUES (?, ?, 'pending', ?)";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . mysqli_error($conn)]);
    exit();
}

mysqli_stmt_bind_param($stmt, "iss", $event_id, $user_id, $notes);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    
    $sql = "UPDATE users SET full_name = ?, phone = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $full_name, $phone, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    mysqli_close($conn);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Đăng ký thành công cho ' . $event['event_title'] . '!'
    ]);
} else {
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $error]);
}
?>