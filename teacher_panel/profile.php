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

$query = "SELECT `role`, `email`, `id` FROM `users` WHERE `users`.`id`=?";
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
$userId = $row['id'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Teacher Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .navbar {
            background-color: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        .container-main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: white;
        }
        .profile-info {
            margin-bottom: 1.5rem;
        }
        .profile-info label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.5rem;
            display: block;
        }
        .profile-info .value {
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3><?php echo htmlspecialchars($userName); ?></h3>
                <p class="text-muted mb-0">Teacher Account</p>
            </div>

            <div class="profile-info">
                <label><i class="fas fa-id-card"></i> User ID</label>
                <div class="value"><?php echo htmlspecialchars($userId); ?></div>
            </div>

            <div class="profile-info">
                <label><i class="fas fa-envelope"></i> Email</label>
                <div class="value"><?php echo htmlspecialchars($userName); ?></div>
            </div>

            <div class="profile-info">
                <label><i class="fas fa-user-tag"></i> Role</label>
                <div class="value">
                    <span class="badge bg-primary">Teacher</span>
                </div>
            </div>

            <div class="profile-info">
                <label><i class="fas fa-calendar"></i> Member Since</label>
                <div class="value">January 2025</div>
            </div>

            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Để cập nhật thông tin cá nhân, vui lòng liên hệ quản trị viên.
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="dashboard.php" class="btn btn-back flex-fill">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="../index.php" class="btn btn-edit flex-fill">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>