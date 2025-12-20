<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    // Fallback for root vs subfolder
    if (file_exists('../assets/config.php')) include('../assets/config.php');
}

$message = '';
$msgType = '';

// Handle Form Submission
// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Only proceed if password field is filled
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $message = "Passwords do not match.";
            $msgType = "danger";
        } else {
            // Update password
            // Use password_hash as verified in login-backend.php
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Column defined as `password_hash` in database
            $updateQuery = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Password updated successfully.";
                $msgType = "success";
            } else {
                $message = "Error updating password: " . mysqli_error($conn);
                $msgType = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $message = "Please enter a new password to update.";
        $msgType = "warning";
    }
}

// Fetch User Data
$userQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($stmt);

// Handle POST logic properly now that I know table schema is unknown.
// Safe bet: Update whatever columns are available.
// Logic:
// 1. If password provided, update it.
// 2. If name column exists, update it?
// Let's look at what fields are available from $user.
// I will output a simple form that mirrors `my_activity.php` structure.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - My Account</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reusing styles from my_activity.php + index.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Navbar */
        .main-nav { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1400px; margin: 0 auto; padding: 0 2rem; display: flex; justify-content: space-between; align-items: center; height: 70px; }
        .nav-left { display: flex; align-items: center; gap: 3rem; }
        .nav-menu { display: flex; list-style: none; gap: 2.5rem; align-items: center; margin: 0; padding: 0; }
        .nav-menu a { text-decoration: none; color: #333; font-weight: 600; font-size: 0.95rem; letter-spacing: 0.5px; transition: color 0.3s; position: relative; }
        .nav-menu a::after { content: ''; position: absolute; bottom: -5px; left: 0; width: 0; height: 2px; background: #667eea; transition: width 0.3s; }
        .nav-menu a:hover, .nav-menu a.active { color: #667eea; }
        .nav-menu a:hover::after, .nav-menu a.active::after { width: 100%; }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        
        .user-info { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 1rem; background: #f8f9fa; border-radius: 25px; cursor: pointer; transition: background 0.3s; }
        .user-info:hover { background: #e9ecef; }
        .user-avatar { width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .user-email { font-weight: 500; color: #333; font-size: 0.9rem; }
        
        .dropdown { position: relative; }
        .dropdown-menu-nav { position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); min-width: 200px; display: none; opacity: 0; transform: translateY(-10px); transition: all 0.3s; z-index: 1001; }
        .dropdown-menu-nav::before { content: ''; position: absolute; top: -0.5rem; left: 0; width: 100%; height: 0.5rem; background: transparent; }
        .dropdown:hover .dropdown-menu-nav { display: block; opacity: 1; transform: translateY(0); }
        .dropdown-item { padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; color: #333; text-decoration: none; transition: background 0.3s; }
        .dropdown-item:first-child { border-radius: 10px 10px 0 0; }
        .dropdown-item:last-child { border-radius: 0 0 10px 10px; }
        .dropdown-item:hover { background: #f8f9fa; }
        .dropdown-item i { width: 20px; color: #667eea; }

        @media (max-width: 768px) { .nav-menu { display: none; } }

        /* Page Header */
        .page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 2rem; text-align: center; }
        .page-header h1 { font-size: 2.5rem; margin-bottom: 0.5rem; }

        /* Profile Form */
        .content-container { max-width: 800px; margin: 2rem auto; padding: 0 2rem; }
        .profile-card { background: white; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .profile-content { padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { font-weight: 600; color: #333; margin-bottom: 0.5rem; display: block; }
        .form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; transition: border-color 0.3s; }
        .form-control:focus { border-color: #667eea; outline: none; }
        .btn-save { background: #667eea; color: white; border: none; padding: 0.75rem 2rem; border-radius: 25px; font-weight: 600; cursor: pointer; transition: background 0.3s; display: inline-block; }
        .btn-save:hover { background: #5568d3; }
        .alert { border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <ul class="nav-menu">
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="about.php">ABOUT</a></li>
                    <li><a href="public_events.php">EVENTS</a></li>
                    <li><a href="my_activity.php">MY ACTIVITY</a></li>
                    <li><a href="contact.php">CONTACT</a></li>
                </ul>
            </div>
            
            <div class="nav-right">
                <div class="dropdown">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($user['email'], 0, 1)); ?></div>
                        <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #666;"></i>
                    </div>
                    <div class="dropdown-menu-nav">
                        <a href="profile.php" class="dropdown-item"><i class="fas fa-user-cog"></i><span>Profile</span></a>
                        <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> User Profile</h1>
        <p>Manage your account information</p>
    </div>

    <!-- Content -->
    <div class="content-container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msgType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-content">
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email Address (Username)</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled style="background: #f8f9fa;">
                        <small class="text-muted">Email cannot be changed.</small>
                    </div>

                    <!-- 
                    If there is a 'name' or 'full_name' column, I'd show it here.
                    Skipping for now until I can check schema.
                    -->

                    <hr class="my-4">
                    <h5 class="mb-3">Change Password</h5>
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
