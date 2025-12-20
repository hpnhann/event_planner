<?php
session_start();
error_reporting(0); // Disable error reporting for production/AJAX
ini_set('display_errors', 0);

// buffer output IMMEDIATELY to catch any stray whitespaces from includes
ob_start();

header('Content-Type: application/json');

include('../assets/config.php');

$response = array();

try {
    // Check Admin Role
    if (!isset($_SESSION['uid'])) {
        throw new Exception('Unauthorized');
    }

    $uid = $_SESSION['uid'];
    
    // Check connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $query = "SELECT `role` FROM `users` WHERE `id`=?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
       throw new Exception('Db error: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);

    if (!$row || $row['role'] !== 'admin') {
        throw new Exception('Access denied');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $regId = isset($_POST['registration_id']) ? intval($_POST['registration_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';

        if ($regId <= 0 || !in_array($status, ['present', 'absent'])) {
            throw new Exception('Invalid parameters');
        }

        $updateQuery = "UPDATE event_registrations SET attendance = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        if (!$stmt) {
             throw new Exception('Prepare failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "si", $status, $regId);

        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Attendance updated';
        } else {
            throw new Exception('Database error: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
        
    } else {
        throw new Exception('Invalid request method');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

mysqli_close($conn);

// Clean buffer and output JSON
ob_clean();
echo json_encode($response);
exit();
?>
