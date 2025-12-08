<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    echo json_encode(['status' => 'error', 'message' => 'Cannot find config.php']);
    exit();
}

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to register!']);
    exit();
}

$user_id = $_SESSION['uid'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'register') {
    $event_id = intval($_POST['event_id']);
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, trim($_POST['notes'])) : '';

    $eventQuery = "SELECT e.*, COUNT(DISTINCT r.id) as registered_count 
                   FROM events e 
                   LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
                   WHERE e.id = ? AND e.status = 'published'
                   GROUP BY e.id";
    
    $stmt = mysqli_prepare($conn, $eventQuery);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $eventResult = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($eventResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Event does not exist!']);
        exit();
    }
    
    $event = mysqli_fetch_assoc($eventResult);
    mysqli_stmt_close($stmt);
    
    if ($event['registered_count'] >= $event['max_volunteers']) {
        echo json_encode(['status' => 'error', 'message' => 'Event is full!']);
        exit();
    }

    $checkRegQuery = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
    $checkRegStmt = mysqli_prepare($conn, $checkRegQuery);
    mysqli_stmt_bind_param($checkRegStmt, "is", $event_id, $user_id);
    mysqli_stmt_execute($checkRegStmt);
    
    if (mysqli_num_rows(mysqli_stmt_get_result($checkRegStmt)) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Already registered!']);
        mysqli_stmt_close($checkRegStmt);
        exit();
    }
    mysqli_stmt_close($checkRegStmt);

    $registerQuery = "INSERT INTO event_registrations (event_id, user_id, status, notes) 
                      VALUES (?, ?, 'pending', NOW(), ?)";
                      
    $registerStmt = mysqli_prepare($conn, $registerQuery);
    mysqli_stmt_bind_param($registerStmt, "iss", $event_id, $user_id, $notes);
    
    if (mysqli_stmt_execute($registerStmt)) {
        mysqli_stmt_close($registerStmt);
        
        if (!empty($phone) || !empty($full_name)) {
            $updateUser = "UPDATE users SET phone = ?, full_name = ? WHERE id = ?";
            $upStmt = mysqli_prepare($conn, $updateUser);
            mysqli_stmt_bind_param($upStmt, "sss", $phone, $full_name, $user_id);
            mysqli_stmt_execute($upStmt);
            mysqli_stmt_close($upStmt);
        }

        echo json_encode(['status' => 'success', 'message' => 'Đăng ký thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

mysqli_close($conn);
?>