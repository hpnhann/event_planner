<?php
// auth_register.php
header('Content-Type: application/json');
require_once 'assets/config.php'; // Đảm bảo đường dẫn này đúng

// Hàm trả về JSON nhanh
function sendResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Invalid request method');
}

try {
    // 1. Lấy dữ liệu
    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    // 2. Validate
    if (empty($student_id) || empty($full_name) || empty($phone) || empty($email) || empty($password)) {
        sendResponse('error', 'Vui lòng điền đầy đủ thông tin!');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse('error', 'Email không hợp lệ!');
    }
    
    if (strlen($password) < 6) { // Tùy chỉnh độ dài
        sendResponse('error', 'Mật khẩu phải từ 6 ký tự trở lên!');
    }

    // 3. Kiểm tra trùng lặp
    $check_sql = "SELECT s_no FROM users WHERE email = ? OR id = ?";    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $student_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        sendResponse('error', 'Email hoặc Mã sinh viên/ID đã tồn tại!');
    }
    $stmt->close();

    // 4. Insert vào Database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Role mặc định, ví dụ 'member' hoặc 'student'
    $role = 'student'; 

    $sql = "INSERT INTO users (id, full_name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $student_id, $full_name, $phone, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        sendResponse('success', 'Đăng ký thành công! Đang chuyển hướng...');
    } else {
        sendResponse('error', 'Lỗi hệ thống: ' . $conn->error);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    sendResponse('error', 'Lỗi: ' . $e->getMessage());
}
?>