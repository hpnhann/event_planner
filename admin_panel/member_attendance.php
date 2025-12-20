<?php
error_reporting(0);
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}

include('../assets/config.php');
$uid = $_SESSION['uid'];

// Verify Admin Logic
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

// Get Date (Default to today)
$attendance_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch Members and existing attendance
$membersQuery = "SELECT m.*, ma.status as attendance_status 
                 FROM `members` m 
                 LEFT JOIN `member_attendance` ma ON m.id = ma.member_id AND ma.date = ? 
                 WHERE m.status = 'active' 
                 ORDER BY m.id DESC";

$stmtMembers = mysqli_prepare($conn, $membersQuery);
mysqli_stmt_bind_param($stmtMembers, "s", $attendance_date);
mysqli_stmt_execute($stmtMembers);
$membersResult = mysqli_stmt_get_result($stmtMembers);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Attendance - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 2rem; }
        .navbar-brand { color: white !important; font-weight: bold; }
        .navbar-text { color: white; }
        .container-main { max-width: 1200px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .page-header { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0; }
        .table th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .status-radio { margin-right: 15px; cursor: pointer; }
        .status-radio input { margin-right: 5px; cursor: pointer; }
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
                <h2><i class="fas fa-clipboard-check"></i> Member Attendance</h2>
                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
        </div>

        <form action="" method="GET" class="row mb-4 align-items-end">
            <div class="col-md-4">
                <label for="date" class="form-label">Select Date:</label>
                <input type="date" name="date" id="date" class="form-control" value="<?php echo $attendance_date; ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-8 text-end">
                <span class="text-muted">Showing active members for <?php echo date('d/m/Y', strtotime($attendance_date)); ?></span>
            </div>
        </form>

        <form id="attendanceForm">
            <input type="hidden" name="date" value="<?php echo $attendance_date; ?>">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th class="text-center">Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($membersResult) > 0): ?>
                            <?php $stt = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($membersResult)): ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td class="text-center">
                                        <label class="status-radio text-success">
                                            <input type="radio" name="attendance[<?php echo $row['id']; ?>]" value="present" 
                                                <?php echo ($row['attendance_status'] == 'present' || !$row['attendance_status']) ? 'checked' : ''; ?>> 
                                            Present
                                        </label>
                                        <label class="status-radio text-danger">
                                            <input type="radio" name="attendance[<?php echo $row['id']; ?>]" value="absent" 
                                                <?php echo ($row['attendance_status'] == 'absent') ? 'checked' : ''; ?>> 
                                            Absent
                                        </label>
                                        <label class="status-radio text-warning">
                                            <input type="radio" name="attendance[<?php echo $row['id']; ?>]" value="excused" 
                                                <?php echo ($row['attendance_status'] == 'excused') ? 'checked' : ''; ?>> 
                                            Excused
                                        </label>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">No active members found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Attendance</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('member_attendance_save.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ Attendance saved successfully!');
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred while saving.');
            });
        });
    </script>
</body>
</html>
