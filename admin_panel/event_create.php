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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Admin Panel</title>
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
        .btn-save-draft {
            background-color: #6c757d;
            color: white;
        }
        .btn-save-draft:hover {
            background-color: #5a6268;
            color: white;
        }
        .btn-publish {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
        }
        .btn-publish:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        .publish-info {
            background-color: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 1rem;
            display: none;
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
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Create New Event</h4>
                    </div>
                    <div class="card-body">
                        <form id="createEventForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Event Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="event_title" required 
                                       placeholder="e.g., Beach Cleanup Drive">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="event_description" rows="5" required
                                          placeholder="Describe the event, what volunteers will do, what to bring, etc."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="event_date" required
                                           min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" name="event_time" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="event_location" required
                                       placeholder="e.g., Sunset Beach, City Park">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Maximum Volunteers <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="max_volunteers" required
                                       min="1" value="20" placeholder="20">
                                <small class="text-muted">How many volunteers can register for this event?</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Event Image</label>
                                <input type="file" class="form-control" name="event_image" accept="image/*"
                                       onchange="previewImage(event)">
                                <small class="text-muted">Optional. Recommended size: 800x600px</small>
                                <img id="imagePreview" class="image-preview" alt="Preview">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Publish Options Sidebar -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Publish Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="publish-info">
                            <h6><i class="fas fa-info-circle"></i> Publishing Info</h6>
                            <p class="mb-2"><strong>Draft:</strong> Event is saved but not visible to volunteers. You can edit it anytime.</p>
                            <p class="mb-0"><strong>Publish:</strong> Event is immediately visible on the website and volunteers can register.</p>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <small>Make sure all information is correct before publishing.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-save-draft btn-lg" onclick="saveEvent('draft')">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <button type="button" class="btn btn-publish btn-lg" onclick="saveEvent('publish')">
                                <i class="fas fa-paper-plane"></i> Save & Publish
                            </button>
                            <a href="events.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>

                        <hr class="my-3">

                        <h6 class="mb-3"><i class="fas fa-lightbulb"></i> Tips</h6>
                        <ul class="small text-muted">
                            <li>Use clear, descriptive titles</li>
                            <li>Include all important details</li>
                            <li>Add an attractive image</li>
                            <li>Set realistic volunteer limits</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview uploaded image
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

        // Save event (draft or publish)
        function saveEvent(submitType) {
            const form = document.getElementById('createEventForm');
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Prepare form data
            const formData = new FormData(form);
            formData.append('action', 'create');
            formData.append('submit_type', submitType);

            // Show loading state
            const originalText = event.target.innerHTML;
            event.target.disabled = true;
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            // Submit via AJAX
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
                    event.target.disabled = false;
                    event.target.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred while saving the event!');
                event.target.disabled = false;
                event.target.innerHTML = originalText;
            });
        }
    </script>
</body>
</html>