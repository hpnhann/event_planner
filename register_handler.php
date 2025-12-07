<?php
// Tắt báo lỗi hiển thị ra màn hình để tránh hỏng JSON
error_reporting(0);
session_start();
header('Content-Type: application/json');

// 1. Kết nối Database
if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    echo json_encode(['status' => 'error', 'message' => 'Cannot find config.php']);
    exit();
}

// 2. Check Login
if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để đăng ký!']);
    exit();
}

$user_id = $_SESSION['uid']; // Lấy ID người đang đăng nhập
$action = isset($_POST['action']) ? $_POST['action'] : '';

// ========================================
// REGISTER FOR EVENT
// ========================================
if ($action === 'register') {
    $event_id = intval($_POST['event_id']);
    
    // Lấy dữ liệu từ form (Chỉ để lưu note hoặc update info nếu cần)
    // Vì user đã login nên ta tin tưởng vào $_SESSION['uid'] hơn là dữ liệu nhập tay
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes']));

    // 1. Kiểm tra sự kiện có tồn tại và còn chỗ không
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
        echo json_encode(['status' => 'error', 'message' => 'Sự kiện không tồn tại hoặc đã đóng!']);
        exit();
    }
    
    $event = mysqli_fetch_assoc($eventResult);
    
    // Check Full Slot
    if ($event['registered_count'] >= $event['max_volunteers']) {
        echo json_encode(['status' => 'error', 'message' => 'Rất tiếc, sự kiện đã hết chỗ!']);
        exit();
    }

    // 2. Kiểm tra đã đăng ký chưa (Dùng user_id)
    $checkRegQuery = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
    $checkRegStmt = mysqli_prepare($conn, $checkRegQuery);
    mysqli_stmt_bind_param($checkRegStmt, "is", $event_id, $user_id);
    mysqli_stmt_execute($checkRegStmt);
    
    if (mysqli_num_rows(mysqli_stmt_get_result($checkRegStmt)) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn đã đăng ký sự kiện này rồi!']);
        exit();
    }

    // 3. Thực hiện đăng ký (Insert vào event_registrations)
    // Lưu ý: Cột là user_id (không phải member_id)
    // Nếu bảng bạn có cột 'notes' thì thêm vào, nếu không thì bỏ biến $notes đi
    $registerQuery = "INSERT INTO event_registrations 
                      (event_id, user_id, status, registered_at) 
                      VALUES (?, ?, 'confirmed', NOW())";
                      
    $registerStmt = mysqli_prepare($conn, $registerQuery);
    mysqli_stmt_bind_param($registerStmt, "is", $event_id, $user_id);
    
    if (mysqli_stmt_execute($registerStmt)) {
        // (Tùy chọn) Update lại số điện thoại/Họ tên mới nhất vào bảng users nếu user có thay đổi
        if (!empty($phone) || !empty($full_name)) {
            $updateUser = "UPDATE users SET phone = ?, full_name = ? WHERE id = ?";
            $upStmt = $conn->prepare($updateUser);
            $upStmt->bind_param("sss", $phone, $full_name, $user_id);
            $upStmt->execute();
        }

        echo json_encode(['status' => 'success', 'message' => 'Đăng ký thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . mysqli_error($conn)]);
    }
} 
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

mysqli_close($conn);
?>