<?php
error_reporting(0);
session_start();

// Kiểm tra đã login chưa
if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}

// Kiểm tra role có phải teacher không
include('../assets/config.php');
$uid = $_SESSION['uid'];

$query = "SELECT `role`, `email` FROM `users` WHERE `users`.`id`=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$row || $row['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

$userName = $row['email'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Members - Teacher Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .navbar {
            background-color: #2c3e50;
            padding: 1rem 2rem;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .navbar-text {
            color: white;
        }
        .container-main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e0e0;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
        .student-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-chalkboard-teacher"></i> Teacher Panel
            </a>
            <span class="navbar-text">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?>
            </span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-main">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-users"></i> My Members</h2>
                    <p class="text-muted mb-0">Quản lý danh sách thành viên</p>
                </div>
                <a href="dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php
        // Lấy danh sách students (giả lập - bạn cần tạo bảng teacher_students trong DB)
        // Tạm thời hiển thị empty state
        ?>

        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-user-graduate"></i>
            <h4>Chưa có thành viên nào</h4>
            <p>Bạn chưa được phân công tổ chức nào.</p>
            <p class="text-muted">
                <small>Vui lòng liên hệ quản trị viên để được phân công .</small>
            </p>
        </div>

        <!-- Students List (sẽ hiển thị khi có dữ liệu) -->
        <div class="row" style="display: none;">
            <div class="col-md-6 col-lg-4">
                <div class="student-card">
                    <h5><i class="fas fa-user"></i> Nguyễn Văn A</h5>
                    <p class="mb-1"><strong>Email:</strong> student1@example.com</p>
                    <p class="mb-1"><strong>Lớp:</strong> 10A1</p>
                    <p class="mb-1"><strong>Điểm TB:</strong> 8.5</p>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>