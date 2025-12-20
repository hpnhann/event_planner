<?php
error_reporting(0);
session_start();

if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    die("Error: Cannot find config.php file!");
}
$user_role = '';
if (isset($_SESSION['uid'])) {
    $user_id = $_SESSION['uid'];
    $roleQuery = "SELECT role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $roleQuery);
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $roleResult = mysqli_stmt_get_result($stmt);
    if ($roleData = mysqli_fetch_assoc($roleResult)) {
        $user_role = $roleData['role'];
    }
    mysqli_stmt_close($stmt);
}

$eventsQuery = "SELECT e.*, 
                COUNT(DISTINCT r.id) as registered_count 
                FROM events e 
                LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status != 'cancelled'
                WHERE e.status = 'published'
                GROUP BY e.id 
                ORDER BY e.event_date ASC";

$eventsResult = mysqli_query($conn, $eventsQuery);

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
            background-color: #dc3545;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn-full:hover {
            background-color: #c82333;
            color: white;
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
        /* Modal Success Animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
        }
        .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid #4CAF50;
        }
        .check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }
        .check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
            animation: rotate-circle 4.25s ease-in;
        }
        .check-icon::before, .check-icon::after {
            content: '';
            height: 100px;
            position: absolute;
            background: #FFFFFF;
            transform: rotate(-45deg);
        }
        .icon-line {
            height: 5px;
            background-color: #4CAF50;
            display: block;
            border-radius: 2px;
            position: absolute;
            z-index: 10;
        }
        .icon-line.line-tip {
            top: 46px;
            left: 14px;
            width: 25px;
            transform: rotate(45deg);
            animation: icon-line-tip 0.75s;
        }
        .icon-line.line-long {
            top: 38px;
            right: 8px;
            width: 47px;
            transform: rotate(-45deg);
            animation: icon-line-long 0.75s;
        }
        @keyframes icon-line-tip {
            0% { width: 0; left: 1px; top: 19px; }
            54% { width: 0; left: 1px; top: 19px; }
            70% { width: 50px; left: -8px; top: 37px; }
            84% { width: 17px; left: 21px; top: 48px; }
            100% { width: 25px; left: 14px; top: 45px; }
        }
        @keyframes icon-line-long {
            0% { width: 0; right: 46px; top: 54px; }
            65% { width: 0; right: 46px; top: 54px; }
            84% { width: 55px; right: 0px; top: 35px; }
            100% { width: 47px; right: 8px; top: 38px; }
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
    <div class="hero-section">
        <div class="container text-center">
            <h1><i class="fas fa-hands-helping"></i> Upcoming Volunteer Events</h1>
            <p class="lead">Join us in making a difference in our community!</p>
        </div>
    </div>

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
                                        <button class="btn btn-register" 
                                                onclick="showRegisterModal(<?php echo $event['id']; ?>, '<?php echo addslashes($event['event_title']); ?>', <?php echo $spots_left; ?>)">
                                            <i class="fas fa-hand-paper"></i> Register Now
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-full" onclick="showFullModal()">
                                            <i class="fas fa-times-circle"></i> No Slots Available
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-5x text-muted mb-3"></i>
                <h3>No Upcoming Events</h3>
                <p class="text-muted">Check back later for new volunteer opportunities!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Confirm Register -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-hand-paper"></i> Confirm Registration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <h5 id="modalEventTitle"></h5>
                    <p class="text-muted">Do you want to register for this event?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span id="spotsLeft"></span> spots remaining
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmRegisterBtn">
                        <i class="fas fa-check"></i> OK, Register Me
                    </button>
                </div>
            </div>
        </div>
    </div>
        <!-- Modal Warning -->
    <div class="modal fade" id="adminWarningModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Cannot Register</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-user-shield fa-4x text-warning mb-3"></i>
                    <h5>Không thể đăng ký khi đang là Admin</h5>
                    <p class="text-muted">Vui lòng đăng nhập bằng tài khoản Student hoặc Member.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Success -->
    <div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="success-checkmark">
                        <div class="check-icon">
                            <span class="icon-line line-tip"></span>
                            <span class="icon-line line-long"></span>
                            <div class="icon-circle"></div>
                            <div class="icon-fix"></div>
                        </div>
                    </div>
                    <h3 class="text-success mb-3">Xin cảm ơn!</h3>
                    <p>Form đã được gửi thành công.</p>
                    <p class="text-muted small">Redirecting in <span id="countdown">3</span>s...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Event Full -->
<div class="modal fade" id="fullModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle"></i> Event Full</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-users fa-3x text-danger mb-3"></i>
                <h5>No Slots Available</h5>
                <p class="text-muted">Registration for this event is now closed. See you in the upcoming ones!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <footer class="text-center py-4 mt-5" style="background-color: #333; color: white;">
        <div class="container">
            <p class="mb-0">&copy; 2024 Volunteer Management System. All rights reserved.</p>
            <p class="small">Making a difference, one event at a time.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentEventId = null;
        const userRole = '<?php echo $user_role ?? ""; ?>';
        function showRegisterModal(eventId, eventTitle, spotsLeft) {
            currentEventId = eventId;
            document.getElementById('modalEventTitle').textContent = eventTitle;
            document.getElementById('spotsLeft').textContent = spotsLeft;
            
            const modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        }
        function showFullModal() {
            const modal = new bootstrap.Modal(document.getElementById('fullModal'));
            modal.show();
        }

        document.getElementById('confirmRegisterBtn').addEventListener('click', function() {
            // Check if logged in
            <?php if (!isset($_SESSION['uid'])): ?>
                window.location.href = 'login.php?redirect=register_event&event_id=' + currentEventId;
                return;
            <?php endif; ?>
            if (userRole === 'admin' || userRole === 'teacher') {
                // Tắt modal xác nhận
                const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                registerModal.hide();
                
                // Hiện modal cảnh báo sau 300ms (để hiệu ứng mượt hơn)
                setTimeout(() => {
                    new bootstrap.Modal(document.getElementById('adminWarningModal')).show();
                }, 300);
                return; // Dừng lại, không cho chạy tiếp
            }
            // Proceed to registration page
            window.location.href = 'register_event.php?id=' + currentEventId;
        });

        // Show success modal (when redirected back after successful registration)
        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
        window.addEventListener('load', function() {
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            let countdown = 3;
            const countdownEl = document.getElementById('countdown');
            
            const timer = setInterval(function() {
                countdown--;
                countdownEl.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    successModal.hide();
                    // Remove query param
                    window.history.replaceState({}, document.title, 'public_events.php');
                }
            }, 1000);
        });
        <?php endif; ?>
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>