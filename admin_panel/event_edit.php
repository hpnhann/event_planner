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

// Get event details
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$eventQuery = "SELECT * FROM events WHERE id=? AND created_by=?";
$stmt = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt, "ii", $event_id, $uid);
mysqli_stmt_execute($stmt);
$eventResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($eventResult) == 0) {
    header('Location: events.php');
    exit();
}

$event = mysqli_fetch_assoc($eventResult);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin Panel</title>
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
        .navbar-brand, .navbar-text {
            color: white !important;
            font-weight: bold;
        }
        .container-main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1.5rem;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 1rem;
        }
        .current-image {
            max-width: 200px;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .btn-update {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            border: none;
        }
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
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
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Event</h4>
                    </div>
                    <div class="card-body">
                        <form id="editEventForm" enctype="multipart/form-data">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Event Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="event_title" required 
                                       value="<?php echo htmlspecialchars($event['event_title']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="event_description" rows="5" required><?php echo htmlspecialchars($event['event_description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="event_date" required
                                           value="<?php echo $event['event_date']; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="event_time" required
                                           value="<?php echo $event['event_time']; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="event_location" required
                                       value="<?php echo htmlspecialchars($event['event_location']); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Maximum Volunteers <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="max_volunteers" required
                                       min="1" value="<?php echo $event['max_volunteers']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Event Image</label>
                                <?php if ($event['event_image']): ?>
                                    <div>
                                        <p class="mb-2">Current image:</p>
                                        <img src="../uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                             class="current-image" alt="Current Event Image">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="event_image" accept="image/*"
                                       onchange="previewImage(event)">
                                <small class="text-muted">Upload new image to replace current one</small>
                                <img id="imagePreview" class="image-preview" alt="Preview" style="display:none;">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Event Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($event['status'] == 'draft'): ?>
                            <div class="alert alert-secondary mb-0">
                                <strong><i class="fas fa-file-alt"></i> Draft</strong>
                                <p class="mb-0 small">This event is not visible to volunteers.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success mb-0">
                                <strong><i class="fas fa-check-circle"></i> Published</strong>
                                <p class="mb-0 small">Published on: <?php echo date('M d, Y', strtotime($event['published_at'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-update btn-lg" onclick="updateEvent()">
                                <i class="fas fa-save"></i> Update Event
                            </button>
                            <a href="events.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Back to Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event) {
            const preview = document.getElementById('imagePreview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        function updateEvent() {
            const form = document.getElementById('editEventForm');
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            formData.append('action', 'edit');

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

            fetch('event_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✅ ' + data.message);
                    window.location.href = 'events.php';
                } else {
                    alert('❌ Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred!');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    </script>
</body>
</html>