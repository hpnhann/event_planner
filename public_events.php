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
                    <h5>Không thể đăng ký khi đang là admin/teacher</h5>
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

        document.getElementById('confirmRegisterBtn').addEventListener('click', function() {
            // Check if logged in
            <?php if (!isset($_SESSION['uid'])): ?>
                window.location.href = 'login.php?redirect=event_register&event_id=' + currentEventId;
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
            window.location.href = 'event_register.php?id=' + currentEventId;
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