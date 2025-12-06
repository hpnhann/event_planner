<?php
error_reporting(0);
session_start();

// Check if admin
if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

include('../assets/config.php');
$uid = $_SESSION['uid'];

$query = "SELECT `role` FROM `users` WHERE `users`.`id`=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$row || $row['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Handle GET request for viewing member details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'view') {
    header('Content-Type: application/json');
    $id = intval($_GET['id']);
    
    $viewQuery = "SELECT * FROM members WHERE id=?";
    $viewStmt = mysqli_prepare($conn, $viewQuery);
    mysqli_stmt_bind_param($viewStmt, "i", $id);
    mysqli_stmt_execute($viewStmt);
    $viewResult = mysqli_stmt_get_result($viewStmt);
    
    if ($member = mysqli_fetch_assoc($viewResult)) {
        echo json_encode([
            'status' => 'success',
            'member' => $member
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Member not found'
        ]);
    }
    mysqli_stmt_close($viewStmt);
    mysqli_close($conn);
    exit();
}

// Handle POST requests
header('Content-Type: application/json');
$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array();

// ADD MEMBER với đầy đủ fields
if ($action === 'add') {
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position'] ?? ''));
    $department = mysqli_real_escape_string($conn, trim($_POST['department'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $birthday = mysqli_real_escape_string($conn, trim($_POST['birthday'] ?? ''));
    $faculty = mysqli_real_escape_string($conn, trim($_POST['faculty'] ?? ''));
    $academic_year = mysqli_real_escape_string($conn, trim($_POST['academic_year'] ?? ''));
    $class_name = mysqli_real_escape_string($conn, trim($_POST['class_name'] ?? ''));
    $status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'active'));

    // Validate required fields
    if (empty($student_id) || empty($full_name) || empty($phone) || empty($email)) {
        $response['status'] = 'error';
        $response['message'] = 'Student ID, Full Name, Phone, and Email are required!';
        echo json_encode($response);
        exit();
    }

    // Check if student_id already exists
    $checkQuery = "SELECT id FROM members WHERE student_id=?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "s", $student_id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        mysqli_stmt_close($checkStmt);
        $response['status'] = 'error';
        $response['message'] = 'Student ID already exists!';
    } else {
        mysqli_stmt_close($checkStmt);
        
        // Check if email already exists
        $checkEmailQuery = "SELECT id FROM members WHERE email=?";
        $checkEmailStmt = mysqli_prepare($conn, $checkEmailQuery);
        mysqli_stmt_bind_param($checkEmailStmt, "s", $email);
        mysqli_stmt_execute($checkEmailStmt);
        $checkEmailResult = mysqli_stmt_get_result($checkEmailStmt);
        
        if (mysqli_num_rows($checkEmailResult) > 0) {
            mysqli_stmt_close($checkEmailStmt);
            $response['status'] = 'error';
            $response['message'] = 'Email already exists!';
        } else {
            mysqli_stmt_close($checkEmailStmt);
            
            // Insert new member với tất cả fields
            $insertQuery = "INSERT INTO members (student_id, full_name, position, department, email, phone, birthday, faculty, academic_year, class_name, status, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "sssssssssss", 
                $student_id, $full_name, $position, $department, $email, $phone, 
                $birthday, $faculty, $academic_year, $class_name, $status);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $response['status'] = 'success';
                $response['message'] = 'Member added successfully!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to add member: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($insertStmt);
        }
    }
}

// EDIT MEMBER với đầy đủ fields
elseif ($action === 'edit') {
    $id = intval($_POST['id']);
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position'] ?? ''));
    $department = mysqli_real_escape_string($conn, trim($_POST['department'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $birthday = mysqli_real_escape_string($conn, trim($_POST['birthday'] ?? ''));
    $faculty = mysqli_real_escape_string($conn, trim($_POST['faculty'] ?? ''));
    $academic_year = mysqli_real_escape_string($conn, trim($_POST['academic_year'] ?? ''));
    $class_name = mysqli_real_escape_string($conn, trim($_POST['class_name'] ?? ''));
    $status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? 'active'));

    // Validate required fields
    if (empty($student_id) || empty($full_name) || empty($phone) || empty($email)) {
        $response['status'] = 'error';
        $response['message'] = 'Student ID, Full Name, Phone, and Email are required!';
        echo json_encode($response);
        exit();
    }

    // Check if student_id exists (except current member)
    $checkQuery = "SELECT id FROM members WHERE student_id=? AND id!=?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "si", $student_id, $id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        mysqli_stmt_close($checkStmt);
        $response['status'] = 'error';
        $response['message'] = 'Student ID already exists!';
    } else {
        mysqli_stmt_close($checkStmt);
        
        // Check if email exists (except current member)
        $checkEmailQuery = "SELECT id FROM members WHERE email=? AND id!=?";
        $checkEmailStmt = mysqli_prepare($conn, $checkEmailQuery);
        mysqli_stmt_bind_param($checkEmailStmt, "si", $email, $id);
        mysqli_stmt_execute($checkEmailStmt);
        $checkEmailResult = mysqli_stmt_get_result($checkEmailStmt);
        
        if (mysqli_num_rows($checkEmailResult) > 0) {
            mysqli_stmt_close($checkEmailStmt);
            $response['status'] = 'error';
            $response['message'] = 'Email already exists!';
        } else {
            mysqli_stmt_close($checkEmailStmt);
            
            // Update member với tất cả fields
            $updateQuery = "UPDATE members SET student_id=?, full_name=?, position=?, department=?, email=?, phone=?, 
                           birthday=?, faculty=?, academic_year=?, class_name=?, status=? WHERE id=?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "sssssssssssi", 
                $student_id, $full_name, $position, $department, $email, $phone, 
                $birthday, $faculty, $academic_year, $class_name, $status, $id);
            
            if (mysqli_stmt_execute($updateStmt)) {
                $response['status'] = 'success';
                $response['message'] = 'Member updated successfully!';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to update member: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($updateStmt);
        }
    }
}

// DELETE MEMBER
elseif ($action === 'delete') {
    $id = intval($_POST['id']);
    
    $deleteQuery = "DELETE FROM members WHERE id=?";
    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    
    if (mysqli_stmt_execute($deleteStmt)) {
        $response['status'] = 'success';
        $response['message'] = 'Member deleted successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete member: ' . mysqli_error($conn);
    }
    mysqli_stmt_close($deleteStmt);
}

else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid action';
}

mysqli_close($conn);
echo json_encode($response);
?>