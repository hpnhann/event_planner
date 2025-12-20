<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include('assets/config.php');

// Get event ID
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id < 1) {
    die("Invalid event ID");
}

// ========== FETCH EVENT DATA ==========
$eventQuery = "SELECT e.*, 
               COUNT(DISTINCT r.id) as registered_count,
               u.name as organizer_name, u.email as organizer_email
               FROM events e
               LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
               LEFT JOIN users u ON e.created_by = u.id
               WHERE e.id = ?
               GROUP BY e.id";

$stmt = mysqli_prepare($conn, $eventQuery);
if (!$stmt) {
    die("Query prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    mysqli_stmt_close($stmt);
    die("Event not found");
}

$event = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ========== CHECK IF USER REGISTERED ==========
$isRegistered = false;
if (isset($_SESSION['uid'])) {
    $checkReg = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
    $stmtCheck = mysqli_prepare($conn, $checkReg);
    mysqli_stmt_bind_param($stmtCheck, "ii", $event_id, $_SESSION['uid']);
    mysqli_stmt_execute($stmtCheck);
    $checkResult = mysqli_stmt_get_result($stmtCheck);
    $isRegistered = mysqli_num_rows($checkResult) > 0;
    mysqli_stmt_close($stmtCheck);
}

// Calculate spots
$spots_left = $event['max_volunteers'] - $event['registered_count'];
$is_full = $spots_left <= 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_title']); ?> - Event Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .event-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .event-info-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .info-item i {
            width: 30px;
            text-align: center;
            margin-right: 1rem;
            color: #667eea;
        }
        .registration-section {
            position: sticky;
            bottom: 20px;
            z-index: 100;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal-header {
            border-radius: 15px 15px 0 0;
        }
        #modalEventTitle {
            color: #333;
            font-weight: 600;
        }
        .badge-spots {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
        }
    </style>
<!-- Shared Navigation Bar - Add this to all pages -->
<style>
    /* Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Navigation Styles */
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

    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.6rem 1.5rem;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: transform 0.3s, box-shadow 0.3s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        color: white;
    }

    .dropdown {
        position: relative;
    }

    .dropdown-menu {
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

    .dropdown:hover .dropdown-menu {
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

    @media (max-width: 768px) {
        .nav-menu {
            display: none;
        }
        
        .user-email {
            display: none;
        }
    }
</style>

<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-left">
            <ul class="nav-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">HOME</a></li>
                <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">ABOUT</a></li>
                <li><a href="public_events.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'public_events.php' ? 'active' : ''; ?>">EVENTS</a></li>
                <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">CONTACT</a></li>
                <?php if (isset($_SESSION['uid'])): ?>
                    <li><a href="my_activity.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'my_activity.php' ? 'active' : ''; ?>">MY ACTIVITY</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="nav-right">
            <?php if (isset($_SESSION['uid'])): ?>
                <div class="dropdown">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['uid'], 0, 1)); ?>
                        </div>
                        <span class="user-email"><?php echo htmlspecialchars($_SESSION['uid']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #666;"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="my_activity.php" class="dropdown-item">
                            <i class="fas fa-history"></i>
                            <span>My Activity</span>
                        </a>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-login">
                    <i class="fas fa-user"></i>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
</head>
<body>
    <!-- Header -->
    <div class="event-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2"><?php echo htmlspecialchars($event['event_title']); ?></h1>
                    <p class="mb-0">
                        <i class="fas fa-user"></i> Organized by: <?php echo htmlspecialchars($event['organizer_name'] ?? 'Unknown'); ?>
                    </p>
                </div>
                <!-- <div>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Left Column: Event Details -->
            <div class="col-lg-8">
                <!-- Event Image -->
                <?php if ($event['event_image']): ?>
                    <img src="uploads/events/<?php echo htmlspecialchars($event['event_image']); ?>" 
                         class="event-image" alt="Event Image">
                <?php else: ?>
                    <div class="event-image d-flex align-items-center justify-content-center" 
                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-calendar-alt fa-5x text-white"></i>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="event-info-card">
                    <h3><i class="fas fa-info-circle text-primary"></i> About This Event</h3>
                    <hr>
                    <p><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
                </div>

                <!-- Benefits -->
                <?php if (!empty($event['benefits'])): ?>
                <div class="event-info-card">
                    <h4><i class="fas fa-gift text-success"></i> Benefits</h4>
                    <hr>
                    <p><?php echo nl2br(htmlspecialchars($event['benefits'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Event Info -->
            <div class="col-lg-4">
                <div class="event-info-card">
                    <h4 class="mb-3"><i class="fas fa-calendar-check"></i> Event Information</h4>
                    
                    <!-- Date -->
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Date</strong><br>
                            <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                        </div>
                    </div>

                    <!-- Time -->
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Time</strong><br>
                            <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                            <?php if ($event['end_date']): ?>
                                - <?php echo date('g:i A', strtotime($event['end_date'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Location</strong><br>
                            <?php echo htmlspecialchars($event['event_location']); ?>
                        </div>
                    </div>

                    <!-- Cost -->
                    <?php if ($event['cost'] > 0): ?>
                    <div class="info-item">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <strong>Cost</strong><br>
                            $<?php echo number_format($event['cost'], 2); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Volunteers -->
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <strong>Volunteers</strong><br>
                            <?php echo $event['registered_count']; ?> / <?php echo $event['max_volunteers']; ?>
                            <span class="badge <?php echo $is_full ? 'bg-danger' : 'bg-success'; ?> badge-spots ms-2">
                                <?php echo $is_full ? 'FULL' : $spots_left . ' spots left'; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Registration Deadline -->
                    <?php if ($event['registration_deadline']): ?>
                    <div class="info-item">
                        <i class="fas fa-hourglass-end"></i>
                        <div>
                            <strong>Registration Closes</strong><br>
                            <?php echo date('M d, Y g:i A', strtotime($event['registration_deadline'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Status -->
                    <div class="info-item">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Status</strong><br>
                            <span class="badge <?php echo $event['status'] == 'published' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Registration Button -->
                <div class="registration-section">
                    <?php if (!isset($_SESSION['uid'])): ?>
                        <a href="login.php?redirect=event_detail&event_id=<?php echo $event['id']; ?>" 
                           class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt"></i> Login to Register
                        </a>
                    <?php elseif ($isRegistered): ?>
                        <button class="btn btn-success btn-lg w-100" disabled>
                            <i class="fas fa-check-circle"></i> Already Registered
                        </button>
                    <?php elseif ($is_full): ?>
                        <button class="btn btn-danger btn-lg w-100" disabled>
                            <i class="fas fa-times-circle"></i> Event Full - No Spots Available
                        </button>
                    <?php elseif ($event['status'] !== 'published'): ?>
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-lock"></i> Registration Not Available
                        </button>
                    <?php else: ?>
                        <button class="btn btn-primary btn-lg w-100" 
                                onclick="showRegistrationConfirm(<?php echo $event['id']; ?>, '<?php echo addslashes($event['event_title']); ?>', <?php echo $spots_left; ?>)">
                            <i class="fas fa-hand-paper"></i> Register for This Event
                        </button>
                        <div class="text-center mt-2">
                            <small class="text-info">
                                <i class="fas fa-users"></i> <?php echo $spots_left; ?> spots remaining
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmRegistrationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-hand-paper"></i> Confirm Registration
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <h4 id="modalEventTitle" class="mb-3"></h4>
                    <p class="text-muted">Do you want to register for this event?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <span id="modalSpotsInfo"></span>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmRegisterBtn">
                        <i class="fas fa-check"></i> OK, Register Me
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedEventId = null;

        function showRegistrationConfirm(eventId, eventTitle, spotsLeft) {
            selectedEventId = eventId;
            document.getElementById('modalEventTitle').textContent = eventTitle;
            document.getElementById('modalSpotsInfo').textContent = spotsLeft + ' spots remaining';
            
            const modal = new bootstrap.Modal(document.getElementById('confirmRegistrationModal'));
            modal.show();
        }

        // When user confirms, redirect to registration form
        document.getElementById('confirmRegisterBtn').addEventListener('click', function() {
            if (selectedEventId) {
                window.location.href = 'register_event.php?id=' + selectedEventId;
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>