<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}

include('../assets/config.php');
$uid = $_SESSION['uid'];

// Check Admin Role
$query = "SELECT `role` FROM `users` WHERE `id`=?";
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

// Get Event ID
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id === 0) {
    header('Location: events.php');
    exit();
}

// Get Event Details
$eventQuery = "SELECT event_title, event_date, event_time, event_location FROM events WHERE id = ?";
$stmt = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$eventResult = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($eventResult);
mysqli_stmt_close($stmt);

if (!$event) {
    die("Event not found!");
}

// Get Registrations with status counts
$regQuery = "SELECT r.*, u.full_name, u.name, u.email, u.phone 
             FROM event_registrations r 
             JOIN users u ON r.user_id = u.id 
             WHERE r.event_id = ? 
             ORDER BY 
                CASE r.status 
                    WHEN 'pending' THEN 1 
                    WHEN 'approved' THEN 2 
                    WHEN 'rejected' THEN 3 
                    ELSE 4 
                END,
                r.created_at DESC";
$stmt = mysqli_prepare($conn, $regQuery);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$registrations = mysqli_stmt_get_result($stmt);

// Calculate stats
$stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0];
$regs_array = [];
while ($row = mysqli_fetch_assoc($registrations)) {
    $regs_array[] = $row;
    $status = strtolower($row['status'] ?? 'pending');
    if (isset($stats[$status])) {
        $stats[$status]++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration List - <?php echo htmlspecialchars($event['event_title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
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
        .event-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 5px solid #667eea;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            text-align: center;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .stat-card.pending .number { color: #ffc107; }
        .stat-card.approved .number { color: #28a745; }
        .stat-card.rejected .number { color: #dc3545; }
        
        .status-badge {
            padding: 0.4em 0.8em;
            border-radius: 50px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-cancelled { background-color: #e2e3e5; color: #383d41; }
        
        .action-btn {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
            margin: 0 0.2rem;
        }
        .btn-approve {
            background: #28a745;
            color: white;
        }
        .btn-approve:hover {
            background: #218838;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .btn-reject:hover {
            background: #c82333;
        }
        .alert-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            display: none;
        }
        .dt-buttons {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- Alert Message -->
        <div id="alertMessage" class="alert alert-custom"></div>

        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-list-alt"></i> Registration List</h2>
                <p class="text-muted mb-0">Manage volunteers for this event</p>
            </div>
            <a href="events.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
        </div>

        <div class="event-info">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-2"><?php echo htmlspecialchars($event['event_title']); ?></h4>
                    <p class="mb-1">
                        <i class="fas fa-calendar text-primary"></i> 
                        <?php echo date('d/m/Y', strtotime($event['event_date'])); ?> 
                        - 
                        <?php echo date('H:i', strtotime($event['event_time'])); ?>
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt text-danger"></i> 
                        <?php echo htmlspecialchars($event['event_location']); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end align-self-center">
                    <div class="h5">
                        Total Registrations: 
                        <span class="badge bg-primary"><?php echo count($regs_array); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <!-- Stats Cards Removed -->

        <div class="table-responsive">
            <table id="regTable" class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Notes</th>
                        <!-- <th>Status</th> Removed -->
                        <th>Attendance</th>
                        <!-- <th>Actions</th> Removed -->
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    foreach ($regs_array as $row): 
                        // Fix null values
                        $full_name = !empty($row['full_name']) ? $row['full_name'] : 
                                    (!empty($row['name']) ? $row['name'] : 'N/A');
                        $phone = !empty($row['phone']) ? $row['phone'] : 'N/A';
                        $status = $row['status'] ?? 'pending';
                    ?>
                    <tr id="row-<?php echo $row['id']; ?>">
                        <td><?php echo $count++; ?></td>
                        <td><span class="fw-bold"><?php echo htmlspecialchars($row['user_id']); ?></span></td>
                        <td><?php echo htmlspecialchars($full_name); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($phone); ?></td>
                        <td>
                            <?php if(!empty($row['notes'])): ?>
                                <button class="btn btn-sm btn-outline-info" 
                                        data-bs-toggle="popover" 
                                        title="Notes" 
                                        data-bs-content="<?php echo htmlspecialchars($row['notes']); ?>">
                                    <i class="fas fa-comment-dots"></i> View
                                </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <!-- Removed Status Column -->
                        <!-- Removed Status Column -->
                        <td>
                        <?php 
                        $status = strtolower($row['status'] ?? 'pending'); // Normalize status
                            $attendance = $row['attendance'] ?? null; 
                            $isPresent = $attendance === 'present';
                        ?>
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" 
                                       id="attendance-check-<?php echo $row['id']; ?>" 
                                       <?php echo $isPresent ? 'checked' : ''; ?>
                                       onchange="updateAttendance(<?php echo $row['id']; ?>, this.checked ? 'present' : 'absent')">
                                <label class="form-check-label ms-2" for="attendance-check-<?php echo $row['id']; ?>">
                                    <?php echo $isPresent ? 'Present' : 'Absent'; ?>
                                </label>
                            </div>
                        </td>
                        <!-- Removed Actions Column -->
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            // ... (DataTable init hidden for brevity if not changed) ... 
             // Initialize Popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            })

            // Initialize DataTable with Export buttons
            $('#regTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: { 
                            columns: [0,1,2,3,4,6,7]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: { 
                            columns: [0,1,2,3,4,6,7]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: { 
                            columns: [0,1,2,3,4,6,7]
                        }
                    }
                ],
                order: [[7, 'desc']], // Sort by date
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search volunteers..."
                }
            });
        });

        // ... (updateStatus function hidden) ...

        function updateAttendance(regId, status) {
            console.log('updateAttendance called:', regId, status);
            const formData = new FormData();
            formData.append('registration_id', regId);
            formData.append('status', status);

            // Optimistic UI update
            const checkbox = document.getElementById('attendance-check-' + regId);
            const label = checkbox ? checkbox.nextElementSibling : null;

            if (!checkbox) {
                 console.error('Checkbox not found for ID:', regId);
                 alert('Error: Checkbox not found. Please refresh.');
                 return;
            }

            // Update Label text
            if (label) {
                label.textContent = (status === 'present') ? 'Present' : 'Absent';
            }

            fetch('process_attendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Server returned invalid JSON:', text);
                    throw new Error('Server returned invalid JSON response');
                }
            })
            .then(data => {
                console.log('Attendance update response:', data);
                if (data.success) {
                    // Success, logic handled by optimistic UI
                } else {
                    showAlert('Error: ' + data.message, 'error');
                    // Revert UI on logic error
                    checkbox.checked = !checkbox.checked;
                    if (label) label.textContent = checkbox.checked ? 'Present' : 'Absent';
                }
            })
            .catch(error => {
                console.error('Error updating attendance:', error);
                showAlert('Failed to update attendance. Check console.', 'error');
                // Revert UI handling
                checkbox.checked = !checkbox.checked; 
                if (label) label.textContent = checkbox.checked ? 'Present' : 'Absent';
            });
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>