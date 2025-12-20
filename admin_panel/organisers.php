<?php
error_reporting(0);
session_start();

// Kiểm tra đã login chưa
if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}

// Kiểm tra role có phải admin không
include('../assets/config.php');
$uid = $_SESSION['uid'];

$query = "SELECT `role`, `email` FROM `users` WHERE `users`.`id`=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$row || $row['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$userName = $row['email'];

// Lấy danh sách organisers (teachers) từ database
$teachersQuery = "SELECT `id`, `email`, `role` FROM `users` WHERE `role`='teacher' ORDER BY `id` DESC";
$teachersResult = mysqli_query($conn, $teachersQuery);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organisers Management - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            max-width: 1400px;
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
        .btn-add {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 147, 251, 0.4);
        }
        .table {
            background: white;
        }
        .table thead {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .table thead th {
            border: none;
            font-weight: 600;
        }
        .badge {
            padding: 0.5rem 1rem;
        }
        .btn-action {
            padding: 0.25rem 0.75rem;
            margin: 0 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-user-shield"></i> Admin Panel
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
                    <h2><i class="fas fa-user-tie"></i> Organisers Management</h2>
                    <p class="text-muted mb-0">Quản lý danh sách ban tổ chức</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-back me-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addOrganiserModal">
                        <i class="fas fa-plus"></i> Add Organiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="🔍 Search organisers...">
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-primary">
                    Total Organisers: <?php echo mysqli_num_rows($teachersResult); ?>
                </span>
            </div>
        </div>

        <!-- Teachers Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($teachersResult) > 0): ?>
                        <?php while ($teacher = mysqli_fetch_assoc($teachersResult)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                                <td>
                                    <i class="fas fa-user-circle text-danger"></i>
                                    <?php echo htmlspecialchars($teacher['email']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Organiser
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-action">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-warning btn-action">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-action">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No organisers found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Organiser Modal -->
    <div class="modal fade" id="addOrganiserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Organiser</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addOrganiserForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        <div class="alert alert-danger d-none" id="addError"></div>
                        <div class="alert alert-success d-none" id="addSuccess"></div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="saveOrganiserBtn">Save Organiser</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('addOrganiserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let btn = document.getElementById('saveOrganiserBtn');
        let errorAlert = document.getElementById('addError');
        let successAlert = document.getElementById('addSuccess');
        
        btn.disabled = true;
        btn.textContent = 'Saving...';
        errorAlert.classList.add('d-none');
        successAlert.classList.add('d-none');

        fetch('create_organiser.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                successAlert.textContent = data.message;
                successAlert.classList.remove('d-none');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                errorAlert.textContent = data.message;
                errorAlert.classList.remove('d-none');
                btn.disabled = false;
                btn.textContent = 'Save Organiser';
            }
        })
        .catch(err => {
            console.error(err);
            errorAlert.textContent = 'An error occurred. Please try again.';
            errorAlert.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = 'Save Organiser';
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>