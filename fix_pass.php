<?php
// FILE: fix_pass.php
// Đặt file này cùng cấp với file login.php và thư mục assets

// 1. Kết nối Database
require_once 'assets/config.php'; // Đảm bảo đường dẫn này đúng

// 2. Tạo hash cho mật khẩu "123"
$newPassword = '123';
$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

echo "<h1>Đang reset mật khẩu...</h1>";

// 3. Danh sách các email cần reset
$users = ['admin@gmail.com', 'teacher@gmail.com', 'student@gmail.com', 'owner@gmail.com'];

foreach ($users as $email) {
    // Cập nhật mật khẩu mới vào DB
    $updateQuery = "UPDATE users SET password_hash = ? WHERE email = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ss", $newHash, $email);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "<p style='color:green'>✅ Đã đổi pass của <b>$email</b> thành: 123</p>";
        } else {
            echo "<p style='color:orange'>⚠️ <b>$email</b>: Không tìm thấy user hoặc mật khẩu đã là 123 rồi.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ Lỗi: " . mysqli_error($conn) . "</p>";
    }
    
    mysqli_stmt_close($stmt);
}

echo "<hr><h3>🎉 Xong! Bây giờ bạn hãy quay lại trang Login và đăng nhập bằng mật khẩu: 123</h3>";
mysqli_close($conn);
?>