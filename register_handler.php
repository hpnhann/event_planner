<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

// Kiểm tra file config
if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    echo json_encode(['status' => 'error', 'message' => 'Cannot find config.php']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array();

// ========================================
// REGISTER FOR EVENT
// ========================================
if ($action === 'register') {
    $event_id = intval($_POST['event_id']);
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes']));

    // Validate inputs
    if (empty($student_id) || empty($full_name) || empty($email) || empty($phone)) {
        $response['status'] = 'error';
        $response['message'] = 'All required fields must be filled!';
        echo json_encode($response);
        exit();
    }

    // Check if event exists and is published
    $eventQuery = "SELECT e.*, 
                   COUNT(DISTINCT r.id) as registered_count 
                   FROM events e 
                   LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
                   WHERE e.id = ? AND e.status = 'published'
                   GROUP BY e.id";
    
    $stmt = mysqli_prepare($conn, $eventQuery);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $eventResult = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($eventResult) == 0) {
        mysqli_stmt_close($stmt);
        $response['status'] = 'error';
        $response['message'] = 'Event not found or not available!';
        echo json_encode($response);
        exit();
    }
    
    $event = mysqli_fetch_assoc($eventResult);
    mysqli_stmt_close($stmt);
    
    // Check if event is full
    if ($event['registered_count'] >= $event['max_volunteers']) {
        $response['status'] = 'error';
        $response['message'] = 'Sorry, this event is already full!';
        echo json_encode($response);
        exit();
    }

    // Check if member already exists in members table
    $checkMemberQuery = "SELECT id FROM members WHERE student_id = ? OR email = ?";
    $checkStmt = mysqli_prepare($conn, $checkMemberQuery);
    mysqli_stmt_bind_param($checkStmt, "ss", $student_id, $email);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        // Member exists, get their ID
        $memberData = mysqli_fetch_assoc($checkResult);
        $member_id = $memberData['id'];
        mysqli_stmt_close($checkStmt);
    } else {
        mysqli_stmt_close($checkStmt);
        
        // Member doesn't exist, create new member
        $insertMemberQuery = "INSERT INTO members (student_id, full_name, phone, email, created_at) 
                             VALUES (?, ?, ?, ?, NOW())";
        $insertMemberStmt = mysqli_prepare($conn, $insertMemberQuery);
        mysqli_stmt_bind_param($insertMemberStmt, "ssss", $student_id, $full_name, $phone, $email);
        
        if (mysqli_stmt_execute($insertMemberStmt)) {
            $member_id = mysqli_insert_id($conn);
            mysqli_stmt_close($insertMemberStmt);
        } else {
            mysqli_stmt_close($insertMemberStmt);
            $response['status'] = 'error';
            $response['message'] = 'Failed to create member profile: ' . mysqli_error($conn);
            echo json_encode($response);
            exit();
        }
    }

    // Check if already registered for this event
    $checkRegQuery = "SELECT id FROM event_registrations WHERE event_id = ? AND member_id = ?";
    $checkRegStmt = mysqli_prepare($conn, $checkRegQuery);
    mysqli_stmt_bind_param($checkRegStmt, "ii", $event_id, $member_id);
    mysqli_stmt_execute($checkRegStmt);
    $checkRegResult = mysqli_stmt_get_result($checkRegStmt);
    
    if (mysqli_num_rows($checkRegResult) > 0) {
        mysqli_stmt_close($checkRegStmt);
        $response['status'] = 'error';
        $response['message'] = 'You have already registered for this event!';
        echo json_encode($response);
        exit();
    }
    mysqli_stmt_close($checkRegStmt);

    // Register for event
    $registerQuery = "INSERT INTO event_registrations 
                     (event_id, member_id, notes, registration_date, status) 
                     VALUES (?, ?, ?, NOW(), 'pending')";
    $registerStmt = mysqli_prepare($conn, $registerQuery);
    mysqli_stmt_bind_param($registerStmt, "iis", $event_id, $member_id, $notes);
    
    if (mysqli_stmt_execute($registerStmt)) {
        $response['status'] = 'success';
        $response['message'] = 'Registration submitted successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to register: ' . mysqli_error($conn);
    }
    mysqli_stmt_close($registerStmt);
}

else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid action';
}

mysqli_close($conn);
echo json_encode($response);
?>