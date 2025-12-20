<?php
header('Content-Type: application/json');
session_start();
include('../assets/config.php');

if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Logic to check admin role again if needed, but session check is basic here.
// Ideally should duplicate the admin check logic.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $attendanceData = $_POST['attendance']; // Array of member_id => status

    if (!$date || !is_array($attendanceData)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        exit();
    }

    $successCount = 0;
    $errorCount = 0;

    foreach ($attendanceData as $memberId => $status) {
        // Check if record exists
        $checkQuery = "SELECT id FROM member_attendance WHERE member_id = ? AND date = ?";
        $stmtCheck = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmtCheck, "is", $memberId, $date);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);
        
        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
            // Update
            $updateQuery = "UPDATE member_attendance SET status = ? WHERE member_id = ? AND date = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmtUpdate, "sis", $status, $memberId, $date);
            if (mysqli_stmt_execute($stmtUpdate)) {
                $successCount++;
            } else {
                $errorCount++;
            }
            mysqli_stmt_close($stmtUpdate);
        } else {
            // Insert
            $insertQuery = "INSERT INTO member_attendance (member_id, date, status) VALUES (?, ?, ?)";
            $stmtInsert = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($stmtInsert, "iss", $memberId, $date, $status);
            if (mysqli_stmt_execute($stmtInsert)) {
                $successCount++;
            } else {
                $errorCount++;
            }
            mysqli_stmt_close($stmtInsert);
        }
        mysqli_stmt_close($stmtCheck);
    }

    if ($errorCount == 0) {
        echo json_encode(['status' => 'success', 'message' => 'Saved ' . $successCount . ' records.']);
    } else {
        echo json_encode(['status' => 'warning', 'message' => 'Saved ' . $successCount . ' records, but ' . $errorCount . ' failed.']);
    }

    mysqli_close($conn);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
