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

// Get Registrations
$regQuery = "SELECT r.*, u.full_name, u.email, u.phone 
             FROM event_registrations r 
             JOIN users u ON r.user_id = u.id 
             WHERE r.event_id = ? 
             ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $regQuery);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$registrations = mysqli_stmt_get_result($stmt);
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
        .event-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
            border-left: 5px solid #667eea;
        }
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 50px;
            font-size: 0.85em;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-confirmed { background-color: #28a745; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
        .status-attended { background-color: #17a2b8; color: #fff; }
    </style>
</head>
<body>
    <div class="container-main">
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
                <div class="col-md-6">
                    <h4 class="mb-2"><?php echo htmlspecialchars($event['event_title']); ?></h4>
                    <p class="mb-1"><i class="fas fa-calendar text-primary"></i> <?php echo date('d/m/Y', strtotime($event['event_date'])); ?> - <?php echo $event['event_time']; ?></p>
                    <p class="mb-0"><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($event['event_location']); ?></p>
                </div>
                <div class="col-md-6 text-md-end align-self-center">
                    <div class="h5">Total Registrations: <span class="badge bg-primary"><?php echo mysqli_num_rows($registrations); ?></span></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="regTable" class="table table-hover border">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($registrations)): 
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><span class="fw-bold"><?php echo htmlspecialchars($row['user_id']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td>
                            <?php if(!empty($row['notes'])): ?>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="popover" title="Notes" data-bs-content="<?php echo htmlspecialchars($row['notes']); ?>">
                                    <i class="fas fa-comment-dots"></i> View
                                </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status'] ?? 'pending'); ?>">
                                <?php echo ucfirst($row['status'] ?? 'pending'); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
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
                        exportOptions: { columns: [0,1,2,3,4,6,7] }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: { columns: [0,1,2,3,4,6,7] }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-secondary btn-sm',
                        exportOptions: { columns: [0,1,2,3,4,6,7] }
                    }
                ],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search volunteers..."
                }
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
