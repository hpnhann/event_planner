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

// Lấy tất cả events của admin (cả draft và published)
$eventsQuery = "SELECT e.*, 
                COUNT(DISTINCT r.id) as registered_count 
                FROM events e 
                LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
                WHERE e.created_by = ?
                GROUP BY e.id 
                ORDER BY e.created_at DESC";
$stmt = mysqli_prepare($conn, $eventsQuery);
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$eventsResult = mysqli_stmt_get_result($stmt);

// Count statistics
$statsQuery = "SELECT 
               COUNT(*) as total_events,
               SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) as draft_count,
               SUM(CASE WHEN status='published' THEN 1 ELSE 0 END) as published_count
               FROM events WHERE created_by = ?";
$stmt = mysqli_prepare($conn, $statsQuery);
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$statsResult = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($statsResult);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - Admin Panel</title>
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .page-header {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .events-table {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .badge-draft {
            background-color: #6c757d;
        }
        .badge-published {
            background-color: #28a745;
        }
        .event-image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            margin: 0 0.125rem;
            font-size: 0.875rem;
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

    <div class="container-main">
        <!-- Statistics -->
        <div class="stats-card">
            <div class="row">
                <div class="col-md-4 stat-item">
                    <div class="stat-number"><?php echo $stats['total_events']; ?></div>
                    <div class="stat-label">Total Events</div>
                </div>
                <div class="col-md-4 stat-item">
                    <div class="stat-number text-success"><?php echo $stats['published_count']; ?></div>
                    <div class="stat-label">Published Events</div>
                </div>
                <div class="col-md-4 stat-item">
                    <div class="stat-number text-secondary"><?php echo $stats['draft_count']; ?></div>
                    <div class="stat-label">Draft Events</div>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-calendar-alt"></i> Events Management</h2>
                    <p class="text-muted mb-0">Quản lý các sự kiện tình nguyện</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a href="event_create.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Create New Event
                    </a>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="events-table">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Volunteers</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($eventsResult) > 0): ?>
                            <?php while ($event = mysqli_fetch_assoc($eventsResult)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($event['event_image']): ?>
                                                <img src="../uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                                     class="event-image-thumb me-2" alt="Event">
                                            <?php else: ?>
                                                <div class="event-image-thumb me-2 bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-calendar-day text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($event['event_title']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Created: <?php echo date('M d, Y', strtotime($event['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar text-primary"></i> 
                                        <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                        <br>
                                        <i class="fas fa-clock text-warning"></i> 
                                        <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <?php echo htmlspecialchars($event['event_location']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $event['registered_count']; ?> / <?php echo $event['max_volunteers']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($event['status'] == 'draft'): ?>
                                            <span class="badge badge-draft">
                                                <i class="fas fa-file-alt"></i> Draft
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-published">
                                                <i class="fas fa-check-circle"></i> Published
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo date('M d', strtotime($event['published_at'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical" role="group">
                                            <a href="event_edit.php?id=<?php echo $event['id']; ?>" 
                                               class="btn btn-warning btn-action">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <?php if ($event['status'] == 'draft'): ?>
                                                <button onclick="publishEvent(<?php echo $event['id']; ?>)" 
                                                        class="btn btn-success btn-action">
                                                    <i class="fas fa-paper-plane"></i> Publish
                                                </button>
                                            <?php else: ?>
                                                <button onclick="unpublishEvent(<?php echo $event['id']; ?>)" 
                                                        class="btn btn-secondary btn-action">
                                                    <i class="fas fa-eye-slash"></i> Unpublish
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="event_registrations.php?id=<?php echo $event['id']; ?>" 
                                               class="btn btn-info btn-action">
                                                <i class="fas fa-users"></i> Registrations
                                            </a>
                                            
                                            <button onclick="deleteEvent(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['event_title']); ?>')" 
                                                    class="btn btn-danger btn-action">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No events created yet</p>
                                    <a href="event_create.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Your First Event
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Publish Event
        function publishEvent(eventId) {
            if (confirm('Publish this event? It will be visible to all volunteers and they can start registering.')) {
                fetch('event_handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=toggle_status&event_id=' + eventId
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

        // Unpublish Event
        function unpublishEvent(eventId) {
            if (confirm('Unpublish this event? It will no longer be visible to volunteers.')) {
                fetch('event_handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=toggle_status&event_id=' + eventId
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

        // Delete Event
        function deleteEvent(eventId, eventTitle) {
            if (confirm('⚠️ Are you sure you want to delete "' + eventTitle + '"?\n\nThis action cannot be undone and will also delete all registrations.')) {
                fetch('event_handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=delete&event_id=' + eventId
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
    </script>
</body>
</html>