<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

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

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array();

// ADD ORGANISER
if ($action === 'add') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = 'teacher'; // Organisers are teachers

    // Check if email exists
    $checkQuery = "SELECT id FROM users WHERE email=?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "s", $email);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        mysqli_stmt_close($checkStmt);
        $response['status'] = 'error';
        $response['message'] = 'Email already exists!';
    } else {
        mysqli_stmt_close($checkStmt);
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new organiser
        $insertQuery = "INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "sss", $email, $password_hash, $role);
        
        if (mysqli_stmt_execute($insertStmt)) {
            $response['status'] = 'success';
            $response['message'] = 'Organiser added successfully!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to add organiser: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($insertStmt);
    }
}

// EDIT ORGANISER
elseif ($action === 'edit') {
    $id = intval($_POST['id']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Check if email exists (except current user)
    $checkQuery = "SELECT id FROM users WHERE email=? AND id!=?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "si", $email, $id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) > 0) {
        mysqli_stmt_close($checkStmt);
        $response['status'] = 'error';
        $response['message'] = 'Email already exists!';
    } else {
        mysqli_stmt_close($checkStmt);
        
        if (!empty($password)) {
            // Update with new password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET email=?, password_hash=? WHERE id=?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "ssi", $email, $password_hash, $id);
        } else {
            // Update without password
            $updateQuery = "UPDATE users SET email=? WHERE id=?";
            $updateStmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "si", $email, $id);
        }
        
        if (mysqli_stmt_execute($updateStmt)) {
            $response['status'] = 'success';
            $response['message'] = 'Organiser updated successfully!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to update organiser: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($updateStmt);
    }
}

// DELETE ORGANISER
elseif ($action === 'delete') {
    $id = intval($_POST['id']);
    
    // Check if user is deleting themselves
    if ($id == $uid) {
        $response['status'] = 'error';
        $response['message'] = 'You cannot delete yourself!';
    } else {
        $deleteQuery = "DELETE FROM users WHERE id=? AND role='teacher'";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "i", $id);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            $response['status'] = 'success';
            $response['message'] = 'Organiser deleted successfully!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to delete organiser: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($deleteStmt);
    }
}

else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid action';
}

mysqli_close($conn);
echo json_encode($response);
?>