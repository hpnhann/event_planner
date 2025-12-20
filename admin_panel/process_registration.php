<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if running from admin_panel or root
if (file_exists('../assets/config.php')) {
    require_once('../assets/config.php');
} elseif (file_exists('assets/config.php')) {
    require_once('assets/config.php');
} else {
    echo json_encode(['success' => false, 'message' => 'Config file not found']);
    exit();
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Verify Admin Role from DB
$uid = $_SESSION['uid'];
$roleQuery = "SELECT `role` FROM `users` WHERE `id`=?";
$stmt = mysqli_prepare($conn, $roleQuery);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$row || $row['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access (Role mismatch)']);
    exit();
}

// Validate input
if (!isset($_POST['registration_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$registration_id = (int)$_POST['registration_id'];
$action = mysqli_real_escape_string($conn, $_POST['action']);

// Validate action
if (!in_array($action, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Get registration details before updating
$check_sql = "SELECT er.*, e.event_title, u.email as user_email, u.full_name, u.name
              FROM event_registrations er
              JOIN events e ON er.event_id = e.id
              JOIN users u ON er.user_id = u.id
              WHERE er.id = ?";

$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "i", $registration_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    echo json_encode(['success' => false, 'message' => 'Registration not found']);
    exit();
}

$registration = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Check if already processed
if ($registration['status'] !== 'pending') {
    echo json_encode([
        'success' => false, 
        'message' => 'This registration has already been ' . $registration['status']
    ]);
    exit();
}

// Update registration status
$update_sql = "UPDATE event_registrations 
               SET status = ?,
                   updated_at = CURRENT_TIMESTAMP
               WHERE id = ?";

$stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt, "si", $action, $registration_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    
    // Get user name for success message
    $user_name = !empty($registration['full_name']) ? $registration['full_name'] : $registration['name'];
    
    $message = $action === 'approved' 
        ? "Registration approved successfully for {$user_name}!" 
        : "Registration rejected for {$user_name}";
    
    // Optional: Send email notification to user
    // You can add email functionality here
    
    mysqli_close($conn);
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action,
        'registration_id' => $registration_id
    ]);
} else {
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update registration: ' . $error
    ]);
}
?>