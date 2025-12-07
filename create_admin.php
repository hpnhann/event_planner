<?php
// FILE: create_admin.php
require_once 'assets/config.php';

// Cấu hình tài khoản Admin muốn tạo
$id = 'ADMIN01'; // ID tùy chọn
$email = 'admin@gmail.com';
$password = '123';
$role = 'admin';
$fullName = 'Super Admin';
$phone = '0999999999';

// 1. Mã hóa mật khẩu
$hash = password_hash($password, PASSWORD_BCRYPT);

// 2. Kiểm tra xem email này đã có chưa
$check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

echo "<h1>Khôi phục tài khoản Admin</h1>";

if (mysqli_num_rows($check) > 0) {
    // Nếu có rồi thì Update lại mật khẩu cho chắc
    $sql = "UPDATE users SET password_hash = '$hash', role = '$role' WHERE email = '$email'";
    if (mysqli_query($conn, $sql)) {
        echo "<div style='color:green; font-size: 20px;'>✅ Tài khoản <b>$email</b> đã tồn tại -> Đã reset mật khẩu về: <b>123</b></div>";
    }
} else {
    // Nếu chưa có thì Insert mới
    // Lưu ý: Kiểm tra xem bảng có cột full_name/phone không, nếu lỗi thì xóa 2 trường đó đi
    $sql = "INSERT INTO users (id, email, password_hash, role, full_name, phone) 
            VALUES ('$id', '$email', '$hash', '$role', '$fullName', '$phone')";
            
    if (mysqli_query($conn, $sql)) {
        echo "<div style='color:green; font-size: 20px;'>🎉 Đã tạo mới thành công tài khoản <b>$email</b> <br> Mật khẩu: <b>123</b></div>";
    } else {
        echo "<div style='color:red;'>❌ Lỗi tạo mới: " . mysqli_error($conn) . "</div>";
        echo "<p>Gợi ý: Nếu lỗi 'Unknown column', hãy thử bỏ bớt trường full_name hoặc phone trong code.</p>";
    }
}

echo "<br><a href='login.php' style='font-size:20px'>👉 Bấm vào đây để Đăng Nhập ngay</a>";
?>