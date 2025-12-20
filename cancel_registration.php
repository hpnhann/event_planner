<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once('assets/config.php');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Validate input
if (!isset($_POST['registration_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$registration_id = (int)$_POST['registration_id'];
$user_id = $_SESSION['uid'];

// Verify ownership and status
$check_sql = "SELECT id, status FROM event_registrations 
              WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "is", $registration_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo json_encode(['success' => false, 'message' => 'Registration not found or you do not have permission']);
    exit();
}

$registration = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Only allow canceling pending registrations
if ($registration['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Only pending registrations can be cancelled']);
    exit();
}

// Update status to cancelled
$update_sql = "UPDATE event_registrations 
               SET status = 'cancelled', 
                   updated_at = CURRENT_TIMESTAMP
               WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt, "i", $registration_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration cancelled successfully'
    ]);
} else {
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to cancel registration: ' . $error
    ]);
}
?>