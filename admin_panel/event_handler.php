<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

function sendJson($data) {
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['uid'])) {
    sendJson(['status' => 'error', 'message' => 'Not logged in']);
}

if (!file_exists('../assets/config.php')) {
    sendJson(['status' => 'error', 'message' => 'Config not found']);
}

include('../assets/config.php');

if (!$conn) {
    sendJson(['status' => 'error', 'message' => 'Database connection failed']);
}

$uid = $_SESSION['uid'];

// Check admin
$query = "SELECT role FROM users WHERE id=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row || $row['role'] !== 'admin') {
    sendJson(['status' => 'error', 'message' => 'Unauthorized']);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// CREATE EVENT
if ($action === 'create') {
    $event_title = mysqli_real_escape_string($conn, trim($_POST['event_title'] ?? ''));
    $event_description = mysqli_real_escape_string($conn, trim($_POST['event_description'] ?? ''));
    $event_location = mysqli_real_escape_string($conn, trim($_POST['event_location'] ?? ''));
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $registration_deadline = $_POST['registration_deadline'] ?? '';
    $activity_code = $_POST['activity_code'] ?? '';
    $max_volunteers = intval($_POST['max_volunteers'] ?? 0);
    $cost = floatval($_POST['cost'] ?? 0);
    $benefits = mysqli_real_escape_string($conn, trim($_POST['benefits'] ?? ''));
    $status = $_POST['status'] ?? 'draft';

    if (empty($event_title)) sendJson(['status' => 'error', 'message' => 'Title required']);
    if (empty($event_description)) sendJson(['status' => 'error', 'message' => 'Description required']);
    if (empty($event_location)) sendJson(['status' => 'error', 'message' => 'Location required']);
    if (empty($event_date)) sendJson(['status' => 'error', 'message' => 'Date required']);
    if (empty($event_time)) sendJson(['status' => 'error', 'message' => 'Time required']);
    if (empty($end_date)) sendJson(['status' => 'error', 'message' => 'End date required']);
    if (empty($registration_deadline)) sendJson(['status' => 'error', 'message' => 'Deadline required']);
    if ($max_volunteers < 1) sendJson(['status' => 'error', 'message' => 'Max volunteers must be >= 1']);

    $event_image = NULL;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (in_array($_FILES['event_image']['type'], $allowed)) {
            $upload_dir = '../uploads/events/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $ext = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $event_image = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_dir . $event_image);
        }
    }

    $end_date = str_replace('T', ' ', $end_date);
    if (strlen($end_date) == 16) $end_date .= ':00';
    
    $registration_deadline = str_replace('T', ' ', $registration_deadline);
    if (strlen($registration_deadline) == 16) $registration_deadline .= ':00';

    $published_at = ($status == 'published') ? date('Y-m-d H:i:s') : NULL;

    $sql = "INSERT INTO events 
            (activity_code, event_title, event_description, event_location, 
             event_date, event_time, end_date, max_volunteers, cost, benefits, 
             registration_deadline, event_image, status, created_by, published_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssidsssiss", 
        $activity_code, $event_title, $event_description, $event_location, 
        $event_date, $event_time, $end_date, $max_volunteers, $cost, 
        $benefits, $registration_deadline, $event_image, $status, $uid, $published_at
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendJson(['status' => 'success', 'message' => 'Event created!']);
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendJson(['status' => 'error', 'message' => 'Insert failed']);
    }
}

// EDIT EVENT
elseif ($action === 'edit') {
    $event_id = intval($_POST['event_id'] ?? 0);
    if ($event_id < 1) sendJson(['status' => 'error', 'message' => 'Invalid ID']);
    
    $event_title = mysqli_real_escape_string($conn, trim($_POST['event_title'] ?? ''));
    $event_description = mysqli_real_escape_string($conn, trim($_POST['event_description'] ?? ''));
    $event_location = mysqli_real_escape_string($conn, trim($_POST['event_location'] ?? ''));
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $registration_deadline = $_POST['registration_deadline'] ?? '';
    $activity_code = $_POST['activity_code'] ?? '';
    $max_volunteers = intval($_POST['max_volunteers'] ?? 0);
    $cost = floatval($_POST['cost'] ?? 0);
    $benefits = mysqli_real_escape_string($conn, trim($_POST['benefits'] ?? ''));
    $status = $_POST['status'] ?? 'draft';

    if (empty($event_title)) sendJson(['status' => 'error', 'message' => 'Title required']);
    if ($max_volunteers < 1) sendJson(['status' => 'error', 'message' => 'Max volunteers must be >= 1']);

    $checkSql = "SELECT event_image FROM events WHERE id=? AND created_by=?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "is", $event_id, $uid);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($checkResult) == 0) {
        mysqli_stmt_close($checkStmt);
        sendJson(['status' => 'error', 'message' => 'Event not found']);
    }
    
    $oldData = mysqli_fetch_assoc($checkResult);
    $event_image = $oldData['event_image'];
    mysqli_stmt_close($checkStmt);

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (in_array($_FILES['event_image']['type'], $allowed)) {
            $upload_dir = '../uploads/events/';
            if ($event_image && file_exists($upload_dir . $event_image)) {
                unlink($upload_dir . $event_image);
            }
            
            $ext = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $event_image = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_dir . $event_image);
        }
    }

    $end_date = str_replace('T', ' ', $end_date);
    if (strlen($end_date) == 16) $end_date .= ':00';
    
    $registration_deadline = str_replace('T', ' ', $registration_deadline);
    if (strlen($registration_deadline) == 16) $registration_deadline .= ':00';

    $sql = "UPDATE events SET 
            activity_code=?, event_title=?, event_description=?, event_location=?, 
            event_date=?, event_time=?, end_date=?, max_volunteers=?, cost=?, 
            benefits=?, registration_deadline=?, event_image=?, status=?
            WHERE id=? AND created_by=?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssidssssis", 
        $activity_code, $event_title, $event_description, $event_location, 
        $event_date, $event_time, $end_date, $max_volunteers, $cost, 
        $benefits, $registration_deadline, $event_image, $status, $event_id, $uid
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendJson(['status' => 'success', 'message' => 'Event updated!']);
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        sendJson(['status' => 'error', 'message' => 'Update failed']);
    }
}

// DELETE EVENT
elseif ($action === 'delete') {
    $event_id = intval($_POST['event_id'] ?? 0);
    
    $query = "SELECT event_image FROM events WHERE id=? AND created_by=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $event_id, $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($stmt);
        sendJson(['status' => 'error', 'message' => 'Event not found']);
    }
    
    $row = mysqli_fetch_assoc($result);
    $event_image = $row['event_image'];
    mysqli_stmt_close($stmt);
    
    $delSql = "DELETE FROM events WHERE id=? AND created_by=?";
    $delStmt = mysqli_prepare($conn, $delSql);
    mysqli_stmt_bind_param($delStmt, "is", $event_id, $uid);
    
    if (mysqli_stmt_execute($delStmt)) {
        if ($event_image && file_exists('../uploads/events/' . $event_image)) {
            unlink('../uploads/events/' . $event_image);
        }
        mysqli_stmt_close($delStmt);
        mysqli_close($conn);
        sendJson(['status' => 'success', 'message' => 'Event deleted!']);
    } else {
        mysqli_stmt_close($delStmt);
        mysqli_close($conn);
        sendJson(['status' => 'error', 'message' => 'Delete failed']);
    }
} else {
    sendJson(['status' => 'error', 'message' => 'Invalid action']);
}
?>