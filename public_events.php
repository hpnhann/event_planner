<?php
error_reporting(0);

// Kiểm tra file config có tồn tại không
if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    die("Error: Cannot find config.php file!");
}

// Lấy ONLY published events (bỏ điều kiện ngày để test)
$eventsQuery = "SELECT e.*, 
                COUNT(DISTINCT r.id) as registered_count 
                FROM events e 
                LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
                WHERE e.status = 'published'
                GROUP BY e.id 
                ORDER BY e.event_date ASC";

$eventsResult = mysqli_query($conn, $eventsQuery);

// Check query error
if (!$eventsResult) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events - Volunteer Management</title>
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
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 2rem;
        }
        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .event-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .event-placeholder {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .event-body {
            padding: 2rem;
        }
        .event-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }
        .event-info {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .event-info i {
            width: 20px;
            text-align: center;
        }
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-register:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .btn-full {
            background-color: #6c757d;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
        }
        .volunteers-badge {
            display: inline-block;
            background-color: #e7f3ff;
            color: #667eea;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 1rem;
        }
        .no-events {
            text-align: center;
            padding: 5rem 0;
        }
        .no-events i {
            font-size: 5rem;
            color: #ccc;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1><i class="fas fa-hands-helping"></i> Upcoming Volunteer Events</h1>
            <p class="lead">Join us in making a difference in our community!</p>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="container">
        <?php if (mysqli_num_rows($eventsResult) > 0): ?>
            <div class="row">
                <?php while ($event = mysqli_fetch_assoc($eventsResult)): ?>
                    <?php 
                    $spots_left = $event['max_volunteers'] - $event['registered_count'];
                    $is_full = $spots_left <= 0;
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card">
                            <?php if ($event['event_image']): ?>
                                <img src="uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                     class="event-image" alt="<?php echo htmlspecialchars($event['event_title']); ?>">
                            <?php else: ?>
                                <div class="event-placeholder">
                                    <i class="fas fa-calendar-day fa-4x text-white"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="event-body">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                
                                <div class="event-info">
                                    <i class="fas fa-calendar text-primary"></i>
                                    <?php echo date('l, F j, Y', strtotime($event['event_date'])); ?>
                                </div>
                                
                                <div class="event-info">
                                    <i class="fas fa-clock text-warning"></i>
                                    <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                </div>
                                
                                <div class="event-info">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <?php echo htmlspecialchars($event['event_location']); ?>
                                </div>
                                
                                <div class="volunteers-badge">
                                    <i class="fas fa-users"></i>
                                    <?php echo $event['registered_count']; ?> / <?php echo $event['max_volunteers']; ?> Volunteers
                                    <?php if (!$is_full): ?>
                                        <span class="text-success">(<?php echo $spots_left; ?> spots left)</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="mt-3 text-muted">
                                    <?php echo substr(htmlspecialchars($event['event_description']), 0, 120); ?>...
                                </p>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <a href="event_detail.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </a>
                                    
                                    <?php if (!$is_full): ?>
                                        <a href="login.php?redirect=event_register&event_id=<?php echo $event['id']; ?>" 
                                           class="btn btn-register">
                                            <i class="fas fa-hand-paper"></i> Register Now
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-full" disabled>
                                            <i class="fas fa-times-circle"></i> Event Full
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-times"></i>
                <h3>No Upcoming Events</h3>
                <p class="text-muted">Check back later for new volunteer opportunities!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5" style="background-color: #333; color: white;">
        <div class="container">
            <p class="mb-0">&copy; 2024 Volunteer Management System. All rights reserved.</p>
            <p class="small">Making a difference, one event at a time.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>