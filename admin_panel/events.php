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

// Get events with stats
$eventsQuery = "SELECT e.*, 
                COUNT(DISTINCT r.id) as registered_count,
                u.email as organizer_name
                FROM events e 
                LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
                LEFT JOIN users u ON e.created_by = u.id
                GROUP BY e.id 
                ORDER BY e.created_at DESC";
$eventsResult = mysqli_query($conn, $eventsQuery);

// Stats
$totalEvents = mysqli_num_rows($eventsResult);
$publishedQuery = "SELECT COUNT(*) as count FROM events WHERE status='published'";
$publishedCount = mysqli_fetch_assoc(mysqli_query($conn, $publishedQuery))['count'];
$draftCount = $totalEvents - $publishedCount;
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
            max-width: 1800px;
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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .stats-item {
            text-align: center;
        }
        .stats-item h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stats-item p {
            margin: 0;
            opacity: 0.9;
        }
        .btn-create {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: bold;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .event-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }
        .event-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .event-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .event-placeholder {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .badge-published {
            background-color: #28a745;
            padding: 0.5rem 1rem;
        }
        .badge-draft {
            background-color: #ffc107;
            color: #333;
            padding: 0.5rem 1rem;
        }
        .badge-volunteers {
            background-color: #17a2b8;
            padding: 0.5rem 1rem;
        }
        .action-buttons .btn {
            margin: 0.25rem;
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
                    <h2><i class="fas fa-calendar-alt"></i> Events Management</h2>
                    <p class="text-muted mb-0">Quản lý các hoạt động và sự kiện tình nguyện</p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button class="btn btn-create" data-bs-toggle="modal" data-bs-target="#createEventModal">
                        <i class="fas fa-plus"></i> Create New Event
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-card">
            <div class="row">
                <div class="col-md-4 stats-item">
                    <h3><?php echo $totalEvents; ?></h3>
                    <p>Total Events</p>
                </div>
                <div class="col-md-4 stats-item">
                    <h3><?php echo $publishedCount; ?></h3>
                    <p>Published Events</p>
                </div>
                <div class="col-md-4 stats-item">
                    <h3><?php echo $draftCount; ?></h3>
                    <p>Draft Events</p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" 
                   placeholder="🔍 Tìm kiếm theo tên sự kiện, địa điểm...">
        </div>

        <!-- Events List -->
        <div id="eventsList">
            <?php if (mysqli_num_rows($eventsResult) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($eventsResult)): ?>
                    <?php 
                    $spots_left = $event['max_volunteers'] - $event['registered_count'];
                    $is_full = $spots_left <= 0;
                    ?>
                    <div class="event-card" data-search="<?php echo strtolower($event['event_title'] . ' ' . $event['event_location']); ?>">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <?php if ($event['event_image']): ?>
                                    <img src="../uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                         class="event-image" alt="Event">
                                <?php else: ?>
                                    <div class="event-placeholder">
                                        <i class="fas fa-calendar-day fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-1"><?php echo htmlspecialchars($event['event_title']); ?></h5>
                                <small class="text-muted">Created: <?php echo date('M d, Y', strtotime($event['created_at'])); ?></small>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <i class="fas fa-calendar text-primary"></i>
                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                </div>
                                <div>
                                    <i class="fas fa-clock text-warning"></i>
                                    <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                <?php echo htmlspecialchars($event['event_location']); ?>
                            </div>
                            <div class="col-md-1">
                                <span class="badge badge-volunteers">
                                    <?php echo $event['registered_count']; ?>/<?php echo $event['max_volunteers']; ?>
                                </span>
                            </div>
                            <div class="col-md-1">
                                <span class="badge <?php echo $event['status'] === 'published' ? 'badge-published' : 'badge-draft'; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </div>
                            <div class="col-md-2 action-buttons text-end">
                                <button class="btn btn-sm btn-primary" onclick="viewEvent(<?php echo $event['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick='editEvent(<?php echo json_encode($event); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="viewRegistrations(<?php echo $event['id']; ?>)">
                                    <i class="fas fa-users"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>, '<?php echo addslashes($event['event_title']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-5x text-muted mb-3"></i>
                    <h4>No Events Yet</h4>
                    <p class="text-muted">Create your first event to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Create/Edit Event -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle"></i> Create New Event
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="eventForm" enctype="multipart/form-data">
                    <input type="hidden" name="event_id" id="eventId">
                    <input type="hidden" name="action" id="formAction" value="create">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Activity Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="activity_code" id="activityCode" 
                                       placeholder="000001" required>
                                <small class="text-muted">Format: 6 digits, auto increment</small>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title" 
                                       maxlength="255" required placeholder="Beach Cleanup Drive">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" id="description" 
                                      rows="4" maxlength="5000" required 
                                      placeholder="Describe the event..."></textarea>
                            <small class="text-muted">Max 5000 characters</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="start_date" 
                                       id="startDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="end_date" 
                                       id="endDate" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="location" id="location" 
                                   maxlength="255" required placeholder="Sunset Beach">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Maximum Volunteer <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="max_volunteers" 
                                       id="maxVolunteers" required min="1" placeholder="50">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Cost</label>
                                <input type="number" class="form-control" name="cost" id="cost" 
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Benefits</label>
                                <input type="text" class="form-control" name="benefits" id="benefits" 
                                       maxlength="255" placeholder="Certificate, Free lunch">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image" id="image" 
                                       accept="image/*">
                                <div id="currentImage" class="mt-2"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Close Registration At</label>
                                <input type="datetime-local" class="form-control" name="close_registration" 
                                       id="closeRegistration">
                                <small class="text-muted">Hệ thống tự động đóng đăng ký khi đến thời gian</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <span id="submitBtnText">Create Event</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const eventCards = document.querySelectorAll('.event-card');
            
            eventCards.forEach(card => {
                const searchText = card.getAttribute('data-search');
                card.style.display = searchText.includes(searchValue) ? '' : 'none';
            });
        });

        // Auto-generate activity code
        document.querySelector('[data-bs-target="#createEventModal"]').addEventListener('click', function() {
            if (document.getElementById('formAction').value === 'create') {
                fetch('events_handler.php?action=get_next_code')
                    .then(response => response.json())
                    .then(data => {
                        if (data.code) {
                            document.getElementById('activityCode').value = data.code;
                        }
                    });
            }
        });

        // Form submit
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('events_handler.php', {
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

        function editEvent(eventData) {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Event';
            document.getElementById('submitBtnText').textContent = 'Update Event';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('eventId').value = eventData.id;
            document.getElementById('activityCode').value = eventData.activity_code || '';
            document.getElementById('title').value = eventData.event_title;
            document.getElementById('description').value = eventData.event_description;
            document.getElementById('startDate').value = eventData.event_date + 'T' + eventData.event_time;
            document.getElementById('endDate').value = eventData.event_end_date || '';
            document.getElementById('location').value = eventData.event_location;
            document.getElementById('maxVolunteers').value = eventData.max_volunteers;
            document.getElementById('cost').value = eventData.cost || '';
            document.getElementById('benefits').value = eventData.benefits || '';
            document.getElementById('closeRegistration').value = eventData.close_registration || '';
            document.getElementById('status').value = eventData.status;
            
            if (eventData.event_image) {
                document.getElementById('currentImage').innerHTML = 
                    '<img src="../uploads/events/' + eventData.event_image + '" style="max-width: 200px;" class="img-thumbnail">';
            }
            
            new bootstrap.Modal(document.getElementById('createEventModal')).show();
        }

        function deleteEvent(id, title) {
            if (confirm('⚠️ Are you sure you want to delete event: ' + title + '?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('event_id', id);

                fetch('events_handler.php', {
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
                });
            }
        }

        function viewEvent(id) {
            window.open('../event_detail.php?id=' + id, '_blank');
        }

        function viewRegistrations(id) {
            window.location.href = 'event_registrations.php?event_id=' + id;
        }

        // Reset form when modal closes
        document.getElementById('createEventModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('eventForm').reset();
            document.getElementById('formAction').value = 'create';
            document.getElementById('eventId').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Create New Event';
            document.getElementById('submitBtnText').textContent = 'Create Event';
            document.getElementById('currentImage').innerHTML = '';
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>