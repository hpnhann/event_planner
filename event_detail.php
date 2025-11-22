<?php
error_reporting(0);
session_start();

// Kiểm tra file config
if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    die("Error: Cannot find config.php file!");
}

// Get event ID
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get event details
$eventQuery = "SELECT e.*, 
               COUNT(DISTINCT r.id) as registered_count 
               FROM events e 
               LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
               WHERE e.id = ? AND e.status = 'published'
               GROUP BY e.id";

$stmt = mysqli_prepare($conn, $eventQuery);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: public_events.php');
    exit();
}

$event = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$spots_left = $event['max_volunteers'] - $event['registered_count'];
$is_full = $spots_left <= 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_title']); ?> - Event Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .event-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .event-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .info-box {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-item i {
            font-size: 2rem;
            width: 60px;
            text-align: center;
        }
        .info-item .info-content {
            flex: 1;
        }
        .info-item h5 {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
        }
        .info-item p {
            margin: 0.5rem 0 0 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .btn-register:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .volunteers-progress {
            background: #e7f3ff;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }
        .progress {
            height: 30px;
            border-radius: 15px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1><i class="fas fa-calendar-alt"></i> Event Details</h1>
            <p class="lead">Everything you need to know about this volunteer opportunity</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Event Image -->
                <?php if ($event['event_image']): ?>
                    <img src="uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" 
                         class="event-image" alt="<?php echo htmlspecialchars($event['event_title']); ?>">
                <?php else: ?>
                    <div class="event-placeholder">
                        <i class="fas fa-calendar-day fa-5x text-white"></i>
                    </div>
                <?php endif; ?>

                <!-- Event Description -->
                <div class="info-box">
                    <h2><?php echo htmlspecialchars($event['event_title']); ?></h2>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Event Info -->
                <div class="info-box">
                    <h4 class="mb-4"><i class="fas fa-info-circle"></i> Event Information</h4>
                    
                    <div class="info-item">
                        <i class="fas fa-calendar text-primary"></i>
                        <div class="info-content">
                            <h5>Date</h5>
                            <p><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-clock text-warning"></i>
                        <div class="info-content">
                            <h5>Time</h5>
                            <p><?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        <div class="info-content">
                            <h5>Location</h5>
                            <p><?php echo htmlspecialchars($event['event_location']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Volunteers Progress -->
                <div class="volunteers-progress">
                    <h4><i class="fas fa-users"></i> Volunteers</h4>
                    <h2 class="text-primary">
                        <?php echo $event['registered_count']; ?> / <?php echo $event['max_volunteers']; ?>
                    </h2>
                    
                    <?php 
                    $percentage = ($event['registered_count'] / $event['max_volunteers']) * 100;
                    $progress_color = $percentage < 50 ? 'bg-success' : ($percentage < 80 ? 'bg-warning' : 'bg-danger');
                    ?>
                    
                    <div class="progress">
                        <div class="progress-bar <?php echo $progress_color; ?>" 
                             style="width: <?php echo $percentage; ?>%">
                            <?php echo round($percentage); ?>%
                        </div>
                    </div>
                    
                    <?php if (!$is_full): ?>
                        <p class="mt-3 mb-0 text-success">
                            <strong><?php echo $spots_left; ?> spots remaining!</strong>
                        </p>
                    <?php else: ?>
                        <p class="mt-3 mb-0 text-danger">
                            <strong>Event is full!</strong>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Register Button -->
                <div class="d-grid gap-2 mt-3">
                    <?php if (!$is_full): ?>
                        <a href="login.php?redirect=event_register&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-register">
                            <i class="fas fa-hand-paper"></i> Login to Register
                        </a>
                        <p class="text-center text-muted small mb-0">
                            Don't have an account? <a href="register.php">Sign up here</a>
                        </p>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>
                            <i class="fas fa-times-circle"></i> Event Full
                        </button>
                    <?php endif; ?>
                    
                    <a href="public_events.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5" style="background-color: #333; color: white;">
        <div class="container">
            <p class="mb-0">&copy; 2024 Volunteer Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>