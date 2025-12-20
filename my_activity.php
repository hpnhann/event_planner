<?php
error_reporting(0);
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['uid'];

if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    die("Error: Cannot find config.php file!");
}

// Get user info
$userQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($stmt);

// Get user's event registrations with event details
$registrationsQuery = "SELECT 
    er.id as registration_id,
    er.status,
    er.registration_date,
    er.notes,
    e.id as event_id,
    e.event_title,
    e.event_description,
    e.event_date,
    e.event_time,
    e.event_location,
    e.event_image
FROM event_registrations er
INNER JOIN events e ON er.event_id = e.id
WHERE er.user_id = ?
ORDER BY er.registration_date DESC";

$stmt = mysqli_prepare($conn, $registrationsQuery);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$registrations = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Activity - Event History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        /* Navigation Bar */
        .main-nav {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 3rem;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: color 0.3s;
            position: relative;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #667eea;
            transition: width 0.3s;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #667eea;
        }

        .nav-menu a:hover::after,
        .nav-menu a.active::after {
            width: 100%;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .user-info:hover {
            background: #e9ecef;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-email {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-menu-nav {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            min-width: 200px;
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s;
        }

        /* Fix gap between trigger and menu */
        .dropdown-menu-nav::before {
            content: '';
            position: absolute;
            top: -0.5rem;
            left: 0;
            width: 100%;
            height: 0.5rem;
            background: transparent;
        }

        .dropdown:hover .dropdown-menu-nav {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }

        .dropdown-item:first-child {
            border-radius: 10px 10px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 10px 10px;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item i {
            width: 20px;
            color: #667eea;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        /* Content Container */
        .content-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.pending { background: #fff3cd; color: #856404; }
        .stat-icon.approved { background: #d1e7dd; color: #0f5132; }
        .stat-icon.total { background: #e7e8ff; color: #667eea; }

        .stat-info h3 {
            font-size: 2rem;
            margin: 0;
        }

        .stat-info p {
            margin: 0;
            color: #666;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-badge.approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-badge.cancelled {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        /* Event Cards */
        .events-section {
            margin-top: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .event-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .event-card-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .event-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }

        .event-details h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .event-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 0.5rem 0;
            color: #666;
            font-size: 0.9rem;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .registration-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .registration-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .registration-date {
            color: #666;
            font-size: 0.9rem;
        }

        .status-alert {
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .status-alert.pending {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        .status-alert.approved {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .status-alert.rejected {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .event-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 15px;
        }

        .btn-view {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-view:hover {
            background: #5568d3;
            color: white;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.9rem;
        }

        .btn-cancel:hover {
            background: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .event-card-content {
                grid-template-columns: 1fr;
            }

            .event-image {
                width: 100%;
                height: 200px;
            }

            .nav-menu {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <ul class="nav-menu">
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="about.php">ABOUT</a></li>
                    <li><a href="public_events.php">EVENTS</a></li>
                    <li><a href="contact.php">CONTACT</a></li>
                    <li><a href="my_activity.php" class="active">MY ACTIVITY</a></li>
                </ul>
            </div>
            
            <div class="nav-right">
                <div class="dropdown">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['email'], 0, 1)); ?>
                        </div>
                        <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #666;"></i>
                    </div>
                    <div class="dropdown-menu-nav">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile</span>
                        </a>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-history"></i> My Activity</h1>
        <p>Track your volunteer event participation history</p>
    </div>

    <!-- Content -->
    <div class="content-container">
        <?php
        // Calculate statistics
        $total = mysqli_num_rows($registrations);
        mysqli_data_seek($registrations, 0); // Reset pointer
        
        $pending = $approved = $rejected = 0;
        $events_data = [];
        
        while ($row = mysqli_fetch_assoc($registrations)) {
            $events_data[] = $row;
            switch ($row['status']) {
                case 'pending': $pending++; break;
                case 'approved': $approved++; break;
                case 'rejected': $rejected++; break;
            }
        }
        ?>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $pending; ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $approved; ?></h3>
                    <p>Approved</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total; ?></h3>
                    <p>Total Events</p>
                </div>
            </div>
        </div>

        <!-- Events List -->
        <div class="events-section">
            <h2 class="section-title">Event History</h2>
            
            <?php if (count($events_data) > 0): ?>
                <?php foreach ($events_data as $event): ?>
                    <div class="event-card">
                        <div class="event-card-content">
                            <?php 
                            $imagePath = !empty($event['event_image']) ? 
                                'uploads/events/' . htmlspecialchars($event['event_image']) : 
                                'https://via.placeholder.com/200x150?text=Event';
                            ?>
                            <img src="<?php echo $imagePath; ?>" 
                                 alt="<?php echo htmlspecialchars($event['event_title']); ?>" 
                                 class="event-image">
                            
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                
                                <div class="event-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['event_location']); ?></span>
                                </div>
                                
                                <div class="registration-info">
                                    <div class="registration-header">
                                        <div class="registration-date">
                                            <i class="fas fa-user-check"></i> 
                                            Registered: <?php echo date('M j, Y g:i A', strtotime($event['registration_date'])); ?>
                                        </div>
                                        <div>
                                            <?php
                                            $statusIcons = [
                                                'pending' => 'fa-clock',
                                                'approved' => 'fa-check-circle',
                                                'rejected' => 'fa-times-circle',
                                                'cancelled' => 'fa-ban'
                                            ];
                                            $icon = $statusIcons[$event['status']] ?? 'fa-circle';
                                            ?>
                                            <span class="status-badge <?php echo $event['status']; ?>">
                                                <i class="fas <?php echo $icon; ?>"></i>
                                                <?php echo strtoupper($event['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($event['notes'])): ?>
                                        <div style="margin-top: 10px; font-style: italic; color: #666; font-size: 0.9rem;">
                                            <i class="fas fa-comment"></i> 
                                            <strong>Your note:</strong> <?php echo htmlspecialchars($event['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Status Messages -->
                                    <?php if ($event['status'] === 'pending'): ?>
                                        <div class="status-alert pending">
                                            <i class="fas fa-info-circle"></i> 
                                            Your registration is pending approval from the organizer.
                                        </div>
                                    <?php elseif ($event['status'] === 'approved'): ?>
                                        <div class="status-alert approved">
                                            <i class="fas fa-check-circle"></i> 
                                            Your registration has been approved! See you at the event.
                                        </div>
                                    <?php elseif ($event['status'] === 'rejected'): ?>
                                        <div class="status-alert rejected">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            Your registration was not approved. Please contact the organizer for more information.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Action Buttons -->
                                    <div class="event-actions">
                                        <a href="event_detail.php?id=<?php echo $event['event_id']; ?>" class="btn-view">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        
                                        <?php if ($event['status'] === 'pending'): ?>
                                            <button class="btn-cancel" onclick="cancelRegistration(<?php echo $event['registration_id']; ?>)">
                                                <i class="fas fa-times"></i> Cancel Registration
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Event History</h3>
                    <p>You haven't registered for any events yet.</p>
                    <a href="public_events.php" class="btn-view" style="margin-top: 1rem;">
                        <i class="fas fa-search"></i> Browse Events
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function cancelRegistration(registrationId) {
        if (!confirm('Are you sure you want to cancel this registration?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('registration_id', registrationId);
        formData.append('action', 'cancel');
        
        fetch('cancel_registration.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to cancel registration');
        });
    }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>