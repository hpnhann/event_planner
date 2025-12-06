<?php
error_reporting(0);
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}

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
$membersQuery = "SELECT * FROM `members` ORDER BY `id` DESC";
$membersResult = mysqli_query($conn, $membersQuery);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 1600px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .table {
            background: white;
            font-size: 14px;
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table thead th {
            border: none;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge {
            padding: 0.5rem 1rem;
        }
        .badge-active {
            background-color: #28a745;
        }
        .badge-inactive {
            background-color: #6c757d;
        }
        .btn-action {
            padding: 0.25rem 0.75rem;
            margin: 0 0.25rem;
        }
        .modal-header {
            color: white;
        }
    </style>
</head>
<body>
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

    <div class="container-main">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-users"></i> Members Management</h2>
                    <p class="text-muted mb-0">Quản lý danh sách thành viên tham gia tình nguyện</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-back me-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="fas fa-plus"></i> Thêm mới nhân sự
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Tìm kiếm theo MSSV, tên, email...">
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-primary">
                    Tổng số: <?php echo mysqli_num_rows($membersResult); ?> thành viên
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="membersTable">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã thành viên</th>
                        <th>Tên thành viên</th>
                        <th>Vị trí</th>
                        <th>Ban hoạt động</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Sinh nhật</th>
                        <th>Khoa</th>
                        <th>Khóa</th>
                        <th>Lớp</th>
                        <th>Trạng thái</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($membersResult) > 0): ?>
                        <?php $stt = 1; ?>
                        <?php while ($member = mysqli_fetch_assoc($membersResult)): ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($member['student_id']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($member['full_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($member['position']); ?></td>
                                <td><?php echo htmlspecialchars($member['department']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                <td><?php echo $member['birthday'] ? date('d/m/Y', strtotime($member['birthday'])) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($member['faculty']); ?></td>
                                <td><?php echo htmlspecialchars($member['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($member['class_name']); ?></td>
                                <td>
                                    <span class="badge <?php echo $member['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo $member['status'] === 'active' ? 'Hoạt động' : 'Ngưng hoạt động'; ?>
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <button class="btn btn-sm btn-primary btn-action" onclick="viewMember(<?php echo $member['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning btn-action" onclick='editMember(<?php echo json_encode($member); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-action" onclick="deleteMember(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['full_name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có thành viên nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Add Member -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Thêm mới nhân sự</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addMemberForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Mã thành viên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="student_id" required placeholder="001">
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Tên thành viên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" required placeholder="Nguyễn Trần Hoàng Anh">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vị trí</label>
                                <select class="form-control" name="position">
                                    <option value="">Chọn vị trí</option>
                                    <option value="Ban tổ chức">Ban tổ chức</option>
                                    <option value="Truyền thông">Truyền thông</option>
                                    <option value="Hành chính">Hành chính</option>
                                    <option value="Thành viên">Thành viên</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ban hoạt động</label>
                                <input type="text" class="form-control" name="department" placeholder="Truyền thông">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required placeholder="hoanganh@gmail.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" required placeholder="0109293829">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sinh nhật</label>
                                <input type="date" class="form-control" name="birthday">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Khoa</label>
                                <input type="text" class="form-control" name="faculty" placeholder="Hệ thống thông tin">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Khóa</label>
                                <input type="text" class="form-control" name="academic_year" placeholder="K16">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lớp</label>
                                <input type="text" class="form-control" name="class_name" placeholder="CTTT2021">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-control" name="status" required>
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Ngưng hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Member -->
    <div class="modal fade" id="editMemberModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-edit"></i> Sửa thông tin nhân sự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMemberForm">
                    <input type="hidden" name="id" id="editMemberId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Mã thành viên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="student_id" id="editStudentId" required>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label class="form-label">Tên thành viên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" id="editFullName" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vị trí</label>
                                <select class="form-control" name="position" id="editPosition">
                                    <option value="">Chọn vị trí</option>
                                    <option value="Ban tổ chức">Ban tổ chức</option>
                                    <option value="Truyền thông">Truyền thông</option>
                                    <option value="Hành chính">Hành chính</option>
                                    <option value="Thành viên">Thành viên</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ban hoạt động</label>
                                <input type="text" class="form-control" name="department" id="editDepartment">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="editEmail" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" id="editPhone" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sinh nhật</label>
                                <input type="date" class="form-control" name="birthday" id="editBirthday">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Khoa</label>
                                <input type="text" class="form-control" name="faculty" id="editFaculty">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Khóa</label>
                                <input type="text" class="form-control" name="academic_year" id="editAcademicYear">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lớp</label>
                                <input type="text" class="form-control" name="class_name" id="editClassName">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-control" name="status" id="editStatus" required>
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Ngưng hoạt động</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Member -->
    <div class="modal fade" id="viewMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-user"></i> Chi tiết thành viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewMemberContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#membersTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        document.getElementById('addMemberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');

            fetch('members_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred!');
            });
        });

        function editMember(memberData) {
            document.getElementById('editMemberId').value = memberData.id;
            document.getElementById('editStudentId').value = memberData.student_id;
            document.getElementById('editFullName').value = memberData.full_name;
            document.getElementById('editPosition').value = memberData.position || '';
            document.getElementById('editDepartment').value = memberData.department || '';
            document.getElementById('editEmail').value = memberData.email;
            document.getElementById('editPhone').value = memberData.phone;
            document.getElementById('editBirthday').value = memberData.birthday || '';
            document.getElementById('editFaculty').value = memberData.faculty || '';
            document.getElementById('editAcademicYear').value = memberData.academic_year || '';
            document.getElementById('editClassName').value = memberData.class_name || '';
            document.getElementById('editStatus').value = memberData.status || 'active';
            
            new bootstrap.Modal(document.getElementById('editMemberModal')).show();
        }

        document.getElementById('editMemberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'edit');

            fetch('members_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred!');
            });
        });

        function deleteMember(id, fullName) {
            if (confirm('⚠️ Bạn có chắc muốn xóa thành viên: ' + fullName + '?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('members_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ An error occurred!');
                });
            }
        }

        function viewMember(id) {
            const modal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
            modal.show();
            
            fetch('members_handler.php?action=view&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const m = data.member;
                        document.getElementById('viewMemberContent').innerHTML = `
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-id-card text-primary"></i> Mã thành viên:</strong>
                                            <p class="ms-4">${m.student_id}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-user text-success"></i> Tên:</strong>
                                            <p class="ms-4">${m.full_name}</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Vị trí:</strong>
                                            <p class="ms-4">${m.position || 'N/A'}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Ban hoạt động:</strong>
                                            <p class="ms-4">${m.department || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-envelope text-info"></i> Email:</strong>
                                            <p class="ms-4">${m.email}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong><i class="fas fa-phone text-warning"></i> Phone:</strong>
                                            <p class="ms-4">${m.phone}</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Sinh nhật:</strong>
                                            <p class="ms-4">${m.birthday || 'N/A'}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Khoa:</strong>
                                            <p class="ms-4">${m.faculty || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong>Khóa:</strong>
                                            <p class="ms-4">${m.academic_year || 'N/A'}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Lớp:</strong>
                                            <p class="ms-4">${m.class_name || 'N/A'}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Trạng thái:</strong>
                                            <p class="ms-4">
                                                <span class="badge ${m.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                                    ${m.status === 'active' ? 'Hoạt động' : 'Ngưng hoạt động'}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        document.getElementById('viewMemberContent').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('viewMemberContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Failed to load member details
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>