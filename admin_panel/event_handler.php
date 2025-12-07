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

// ========================================
// CREATE EVENT
// ========================================
if ($action === 'create') {
    $event_title = mysqli_real_escape_string($conn, trim($_POST['event_title']));
    $event_description = mysqli_real_escape_string($conn, trim($_POST['event_description']));
    $event_location = mysqli_real_escape_string($conn, trim($_POST['event_location']));
    
    // --- [SỬA] Thay thế event_date/time cũ bằng các trường DateTime mới ---
    $start_date = $_POST['start_date']; // Chọn cả ngày và giờ
    $end_date = $_POST['end_date'];     // Chọn cả ngày và giờ
    $registration_deadline = $_POST['registration_deadline']; // Hạn chót đăng ký
    
    // --- [THÊM] Các trường mới ---
    $max_volunteers = intval($_POST['max_volunteers']);
    $cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0; // Numeric textbox
    $benefits = mysqli_real_escape_string($conn, trim($_POST['benefits'])); // Textbox
    
    $submit_type = $_POST['submit_type']; // 'draft' or 'publish'

    // --- [SỬA] Cập nhật Validate inputs ---
    if (empty($event_title) || empty($event_description) || empty($event_location) || 
        empty($start_date) || empty($end_date) || empty($registration_deadline) || $max_volunteers < 1) {
        $response['status'] = 'error';
        $response['message'] = 'All fields (Title, Location, Dates, Volunteers) are required!';
        echo json_encode($response);
        exit();
    }

    // Handle image upload (GIỮ NGUYÊN CODE CỦA BẠN)
    $event_image = NULL;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $file_type = $_FILES['event_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/events/';
            
            // Create directory if not exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $event_image = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $event_image;
            
            if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                $event_image = NULL;
            }
        }
    }

    // Determine status (GIỮ NGUYÊN)
    $status = ($submit_type == 'publish') ? 'published' : 'draft';
    $published_at = ($status == 'published') ? 'NOW()' : 'NULL';

    // --- [SỬA] Cập nhật câu lệnh INSERT ---
    // Lưu ý: Database cần có các cột: start_date, end_date, cost, benefits, registration_deadline
    $sql = "INSERT INTO events 
            (event_title, event_description, event_location, start_date, end_date, 
             max_volunteers, cost, benefits, registration_deadline, event_image, status, created_by, published_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, $published_at)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    // Type string mapping:
    // s (title), s (desc), s (loc), s (start), s (end), i (max), d (cost - double), s (benefits), s (deadline), s (img), s (status), i (uid)
    mysqli_stmt_bind_param($stmt, "sssssidssssi", 
        $event_title, $event_description, $event_location, 
        $start_date, $end_date, 
        $max_volunteers, $cost, $benefits, $registration_deadline,
        $event_image, $status, $uid
    );

    if (mysqli_stmt_execute($stmt)) {
        if ($status == 'published') {
            $response['status'] = 'success';
            $response['message'] = 'Event published successfully! Volunteers can now register.';
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Event saved as draft! You can publish it later.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to create event: ' . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// ========================================
// EDIT EVENT
// ========================================
elseif ($action === 'edit') {
    $event_id = intval($_POST['event_id']);
    $event_title = mysqli_real_escape_string($conn, trim($_POST['event_title']));
    $event_description = mysqli_real_escape_string($conn, trim($_POST['event_description']));
    $event_location = mysqli_real_escape_string($conn, trim($_POST['event_location']));
    
    // --- [SỬA] Các trường DateTime mới ---
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $registration_deadline = $_POST['registration_deadline'];
    
    // --- [THÊM] Các trường mới ---
    $max_volunteers = intval($_POST['max_volunteers']);
    $cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0;
    $benefits = mysqli_real_escape_string($conn, trim($_POST['benefits']));

    // --- [SỬA] Validate inputs ---
    if (empty($event_title) || empty($event_description) || empty($event_location) || 
        empty($start_date) || empty($end_date) || empty($registration_deadline) || $max_volunteers < 1) {
        $response['status'] = 'error';
        $response['message'] = 'All fields are required!';
        echo json_encode($response);
        exit();
    }

    // Check ownership (GIỮ NGUYÊN)
    $checkQuery = "SELECT event_image FROM events WHERE id=? AND created_by=?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "ii", $event_id, $uid);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) == 0) {
        mysqli_stmt_close($checkStmt);
        $response['status'] = 'error';
        $response['message'] = 'Event not found or access denied!';
        echo json_encode($response);
        exit();
    }
    
    $oldData = mysqli_fetch_assoc($checkResult);
    $event_image = $oldData['event_image'];
    mysqli_stmt_close($checkStmt);

    // Handle new image upload (GIỮ NGUYÊN)
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $file_type = $_FILES['event_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/events/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old image
            if ($event_image && file_exists($upload_dir . $event_image)) {
                unlink($upload_dir . $event_image);
            }
            
            $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $event_image = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $event_image;
            
            move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path);
        }
    }

    // --- [SỬA] Update SQL ---
    $sql = "UPDATE events SET 
            event_title=?, event_description=?, event_location=?, 
            start_date=?, end_date=?, max_volunteers=?, 
            cost=?, benefits=?, registration_deadline=?, 
            event_image=?
            WHERE id=? AND created_by=?";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    // Type string mapping:
    // s (title), s (desc), s (loc), s (start), s (end), i (max), d (cost), s (benefits), s (deadline), s (img), i (id), i (uid)
    mysqli_stmt_bind_param($stmt, "sssssidsssii", 
        $event_title, $event_description, $event_location, 
        $start_date, $end_date, $max_volunteers, 
        $cost, $benefits, $registration_deadline, 
        $event_image, $event_id, $uid
    );

    if (mysqli_stmt_execute($stmt)) {
        $response['status'] = 'success';
        $response['message'] = 'Event updated successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update event: ' . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
// ========================================
// TOGGLE PUBLISH/UNPUBLISH
// ========================================
elseif ($action === 'toggle_status') {
    $event_id = intval($_POST['event_id']);
    
    // Get current status
    $query = "SELECT status FROM events WHERE id=? AND created_by=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $event_id, $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($stmt);
        $response['status'] = 'error';
        $response['message'] = 'Event not found or access denied!';
        echo json_encode($response);
        exit();
    }
    
    $row = mysqli_fetch_assoc($result);
    $current_status = $row['status'];
    mysqli_stmt_close($stmt);
    
    // Toggle status
    $new_status = ($current_status == 'draft') ? 'published' : 'draft';
    
    if ($new_status == 'published') {
        $update = "UPDATE events SET status=?, published_at=NOW() WHERE id=? AND created_by=?";
    } else {
        $update = "UPDATE events SET status=?, published_at=NULL WHERE id=? AND created_by=?";
    }
    
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "sii", $new_status, $event_id, $uid);
    
    if (mysqli_stmt_execute($stmt)) {
        if ($new_status == 'published') {
            $response['status'] = 'success';
            $response['message'] = 'Event published successfully! Volunteers can now see and register for this event.';
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Event unpublished! It is now hidden from volunteers.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to update status: ' . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// ========================================
// DELETE EVENT
// ========================================
elseif ($action === 'delete') {
    $event_id = intval($_POST['event_id']);
    
    // Get event image before deleting
    $query = "SELECT event_image FROM events WHERE id=? AND created_by=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $event_id, $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($stmt);
        $response['status'] = 'error';
        $response['message'] = 'Event not found or access denied!';
        echo json_encode($response);
        exit();
    }
    
    $row = mysqli_fetch_assoc($result);
    $event_image = $row['event_image'];
    mysqli_stmt_close($stmt);
    
    // Delete event (registrations will be deleted automatically if using CASCADE)
    $deleteQuery = "DELETE FROM events WHERE id=? AND created_by=?";
    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, "ii", $event_id, $uid);
    
    if (mysqli_stmt_execute($deleteStmt)) {
        // Delete image file
        if ($event_image) {
            $image_path = '../uploads/events/' . $event_image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Event deleted successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to delete event: ' . mysqli_error($conn);
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