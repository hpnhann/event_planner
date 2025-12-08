<?php
error_reporting(0);
session_start();


// CHECK IF USER IS LOGGED IN
if (!isset($_SESSION['uid'])) {
    $event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header('Location: login.php?redirect=register_event&event_id=' . $event_id);
    exit();
}

$user_id = $_SESSION['uid'];

if (file_exists('assets/config.php')) {
    include('assets/config.php');
} else {
    die("Error: Cannot find config.php file!");
}

// Get user details
$userQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($userResult) == 0) {
    header('Location: login.php');
    exit();
}

$user = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($stmt);

// Get event ID
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get event details with registration count
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

// If event is full, redirect back
if ($is_full) {
    echo "<script>alert('Sorry, this event is now full!'); window.location.href='event_detail.php?id=" . $event_id . "';</script>";
    exit();
}

// Check if already registered
$checkReg = "SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'";
$stmtCheck = mysqli_prepare($conn, $checkReg);
mysqli_stmt_bind_param($stmtCheck, "is", $event_id, $user_id);
mysqli_stmt_execute($stmtCheck);
if (mysqli_num_rows(mysqli_stmt_get_result($stmtCheck)) > 0) {
    echo "<script>alert('You have already registered for this event!'); window.location.href='public_events.php';</script>";
    exit();
}
mysqli_stmt_close($stmtCheck);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for <?php echo htmlspecialchars($event['event_title']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        .register-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .card-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .card-body {
            padding: 2rem;
        }
        .event-info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .event-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .event-info-item:last-child {
            margin-bottom: 0;
        }
        .event-info-item i {
            width: 30px;
            font-size: 1.2rem;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 50px;
            width: 100%;
        }
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="card-header">
                <h2><i class="fas fa-hand-paper"></i> Complete Your Registration</h2>
                <p class="mb-0">Please fill in your information below</p>
            </div>
            <div class="card-body">
                <!-- Event Info -->
                <div class="event-info-box">
                    <h5 class="mb-3"><strong><?php echo htmlspecialchars($event['event_title']); ?></strong></h5>
                    <div class="event-info-item">
                        <i class="fas fa-calendar text-primary"></i>
                        <span><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?></span>
                    </div>
                    <div class="event-info-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span><?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                    </div>
                    <div class="event-info-item">
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        <span><?php echo htmlspecialchars($event['event_location']); ?></span>
                    </div>
                    <div class="event-info-item">
                        <i class="fas fa-users text-success"></i>
                        <span><strong><?php echo $spots_left; ?> spots left</strong> (<?php echo $event['registered_count']; ?>/<?php echo $event['max_volunteers']; ?> registered)</span>
                    </div>
                </div>

                <!-- Registration Form -->
                <form id="registrationForm">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    
                    <div class="alert alert-success">
                        <i class="fas fa-user-check"></i> 
                        Logged in as: <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Student ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="student_id" required 
                               placeholder="e.g., S2023001"
                               value="<?php echo htmlspecialchars($user['id']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required 
                               placeholder="Nguyen Van A"
                               value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required 
                               placeholder="your.email@example.com"
                               value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="phone" required 
                               placeholder="0901234567"
                               value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Why do you want to join this event?</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Tell us your motivation..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <small>Your registration will be confirmed immediately.</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-check-circle"></i> Submit Registration
                        </button>
                        <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Event Details
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'register');

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            fetch('register_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Server trả về:', text);
                
                try {
                    const data = JSON.parse(text);
                    
                    if (data.status === 'success') {
                        alert('✅ ' + data.message + '\n\nThank you for registering!');
                        window.location.href = 'public_events.php';
                    } else {
                        alert('❌ Lỗi: ' + data.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (e) {
                    console.error('Lỗi parse JSON:', e);
                    console.error('Response:', text);
                    alert('❌ Server trả về dữ liệu lỗi!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('❌ Không thể kết nối tới server!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        'use strict';
        
        const form = document.getElementById('registrationForm');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Ngăn event bubble up
            
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            console.log('Form data being sent:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            fetch('register_handler.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Server response:', text);
                
                try {
                    const data = JSON.parse(text);
                    
                    if (data.status === 'success') {
                        alert('✅ ' + data.message);
                        window.location.href = 'public_events.php';
                    } else {
                        alert('❌ Lỗi: ' + data.message);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Raw response:', text);
                    alert('❌ Lỗi: Server trả về dữ liệu không hợp lệ!');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('❌ Không thể kết nối server!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
            
            return false;
        }, false);
    })();
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>