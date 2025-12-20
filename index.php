<?php 
error_reporting(0);
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Kiểm tra user đã login chưa và role là gì
$isLoggedIn = isset($_SESSION['uid']);
$userRole = '';
$userName = 'Guest';

if ($isLoggedIn) {
    include('assets/config.php');
    $uid = $_SESSION['uid'];
    
    $query = "SELECT `role`, `email` as name FROM `users` WHERE `users`.`id`=?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);
    
    if ($row && isset($row['role'])) {
        $userRole = $row['role'];
        $userName = $row['name'] ?? 'User';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đội Công Tác Xã Hội UIT - Trang Chủ</title>
    <link rel="stylesheet" href="./landing_page_assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* New Navbar Styles from my_activity.php */
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
            margin: 0; 
            padding: 0;
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
            z-index: 1001;
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

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div id="main">
        <!-- HEADER -->
        <!-- HEADER -->
        <nav class="main-nav">
            <div class="nav-container">
                <div class="nav-left">
                    <ul class="nav-menu">
                        <li><a href="#slider">HOME</a></li>
                        <li><a href="#about">ABOUT</a></li>
                        <li><a href="public_events.php">EVENTS</a></li>
                        <li><a href="my_activity.php">MY ACTIVITY</a></li>
                        <li><a href="#contact">CONTACT</a></li>
                        
                        <?php if ($isLoggedIn): ?>
                            <?php if ($userRole === 'admin'): ?>
                            <li class="dropdown">
                                <a href="#">
                                    MANAGEMENT
                                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                                </a>
                                <div class="dropdown-menu-nav">
                                    <a href="admin_panel/members.php" class="dropdown-item">Members</a>
                                    <a href="admin_panel/organisers.php" class="dropdown-item">Organisers</a>
                                    <a href="admin_panel/events.php" class="dropdown-item">Events</a>
                                </div>
                            </li>
                            <?php elseif ($userRole === 'owner'): ?>
                            <li class="dropdown">
                                <a href="#">
                                    Organiser Tools
                                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                                </a>
                                <div class="dropdown-menu-nav">
                                    <a href="owner_panel/index.php" class="dropdown-item">Dashboard</a>
                                    <a href="owner_panel/events.php" class="dropdown-item">Manage Events</a>
                                    <a href="owner_panel/volunteers.php" class="dropdown-item">Volunteers</a>
                                </div>
                            </li>
                            <?php elseif ($userRole === 'teacher'): ?>
                            <li class="dropdown">
                                <a href="#">
                                    My Panel
                                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                                </a>
                                <div class="dropdown-menu-nav">
                                    <a href="teacher_panel/dashboard.php" class="dropdown-item">Dashboard</a>
                                    <a href="teacher_panel/students.php" class="dropdown-item">My Students</a>
                                </div>
                            </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="nav-right">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                                </div>
                                <span class="user-email"><?php echo htmlspecialchars($userName); ?></span>
                                <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #666;"></i>
                            </div>
                            <div class="dropdown-menu-nav">
                                <?php if ($userRole !== 'admin' && $userRole !== 'student'): ?>
                                <a href="<?php 
                                    if($userRole === 'owner') echo 'owner_panel/index.php';
                                    elseif($userRole === 'teacher') echo 'teacher_panel/dashboard.php';
                                ?>" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                <?php endif; ?>
                                
                                <a href="<?php 
                                    if($userRole === 'admin') echo 'admin_panel/settings.php';
                                    elseif($userRole === 'owner') echo 'owner_panel/profile.php';
                                    elseif($userRole === 'teacher') echo 'teacher_panel/profile.php';
                                    elseif($userRole === 'student') echo 'profile.php';
                                ?>" class="dropdown-item">
                                    <i class="fas fa-user-cog"></i> Profile
                                </a>
                                <a href="logout.php" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" style="color: #333; text-decoration: none; font-weight: 600;">
                            <i class="fas fa-user"></i> Login
                        </a>
                    <?php endif; ?>
                    
                    <div class="search-btn" style="cursor: pointer; padding: 0.5rem;">
                        <i class="fas fa-search" style="color: #333;"></i>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- SLIDER -->
        <div id="slider">
            <div class="text-content">
                <h2 class="text-heading">Đội Công Tác Xã Hội UIT</h2>
                <div class="text-description">Cống Hiến Để Trưởng Thành</div>
            </div>
        </div>

        <!-- CONTENT -->
        <div id="content">
            <!-- ABOUT SECTION -->
            <div id="about" class="content-section">
                <h2 class="section-heading">ĐỘI CÔNG TÁC XÃ HỘI</h2>
                <p class="section-sub-heading">Cống Hiến Để Trưởng Thành</p>
                <p class="about-text">
                    Đến với Đội Công tác Xã hội UIT - với phương châm hoạt động "Cống hiến để trưởng thành" - 
                    ta sẽ có dịp được bắt gặp những con người năng động, nhiệt huyết và tràn đầy sức sống nhất, 
                    vẫn luôn cống hiến bản thân mình để giúp đỡ những mảnh đời khó khăn và qua đó mà trưởng thành nhiều hơn.
                </p>
            
                <div class="member-list">
                    <div class="member-item">
                        <p class="member-name">Hoàng Yến</p>
                        <img src="./landing_page_assets/img/band/member2.jpg" alt="Hoàng Yến" class="member-img">
                    </div>
                    <div class="member-item">
                        <p class="member-name">Minh Anh</p>
                        <img src="./landing_page_assets/img/band/member1.jpg" alt="Minh Anh" class="member-img">
                    </div>
                    <div class="member-item">
                        <p class="member-name">Thanh Tú</p>
                        <img src="./landing_page_assets/img/band/member3.jpg" alt="Thanh Tú" class="member-img">
                    </div>
                    <div class="clear"></div>  
                </div>
            </div>

            <!-- TOUR/EVENTS SECTION -->
            <div id="tour" class="tour-section">
                <div class="content-section">
                    <h2 class="section-heading text-white">UPCOMING EVENTS</h2>
                    <p class="section-sub-heading text-white">Cống Hiến Để Trưởng Thành</p>
                
                    <ul class="tickets-list">
                        <li>September <span class="sold-out">Sold out</span></li>
                        <li>October <span class="sold-out">Sold out</span></li>
                        <li>November <span class="quantity">3</span></li>
                    </ul>       

                    <div class="places-list">
                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place4.jpg" alt="Đắk Nông" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">Đắk Nông</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Chiến dịch tình nguyện Mùa Đông Yêu Thương.</p>
                                <a href="public_events.php" class="place-buy-btn">Xem chi tiết</a>
                            </div>
                        </div>

                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place5.jpg" alt="Bình Chánh" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">Bình Chánh</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Chiến dịch tình nguyện Mùa Hè Xanh</p>
                                <a href="public_events.php" class="place-buy-btn">Xem chi tiết</a>
                            </div>
                        </div>

                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place6.jpg" alt="TP HCM" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">TP HCM</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Tình Nguyện Trung Thu Cho Em</p>
                                <a href="public_events.php" class="place-buy-btn">Xem chi tiết</a>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>  
            </div>

            <!-- CONTACT SECTION -->
            <div id="contact" class="content-section" style="background-color: #f9f9f9; padding: 64px 16px;">
                <div style="max-width: 800px; margin: 0 auto;">
                    <h2 class="section-heading" style="text-align: center; font-size: 30px; letter-spacing: 4px; margin-bottom: 25px;">CONTACT US</h2>
                    <p class="section-sub-heading" style="text-align: center; font-style: italic; opacity: 0.6; margin-bottom: 48px;">Liên Hệ Với Chúng Tôi</p>
                    
                    <div class="row contact-content" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
                        <div class="col col-half contact-info" style="flex: 1; min-width: 300px; margin-bottom: 16px;">
                            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 15px;"><i class="ti-location-pin" style="width: 30px; display: inline-block;"></i> TP Hồ Chí Minh, Việt Nam</p>
                            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 15px;"><i class="ti-mobile" style="width: 30px; display: inline-block;"></i> Phone: <a href="tel:+84123456789" style="color: #009688; text-decoration: none;">+84 123 456 789</a></p>
                            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 15px;"><i class="ti-email" style="width: 30px; display: inline-block;"></i> Email: <a href="mailto:ctxh@uit.edu.vn" style="color: #009688; text-decoration: none;">ctxh@uit.edu.vn</a></p>
                        </div>
                        <div class="col col-half contact-form" style="flex: 1; min-width: 300px;">
                            <form action="">
                                <div class="row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                    <div class="col col-half" style="flex: 1;">
                                        <input type="text" name="name" placeholder="Họ và tên" required class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                                    </div>
                                    <div class="col col-half" style="flex: 1;">
                                        <input type="email" name="email" placeholder="Email" required class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom: 10px;">
                                    <div class="col col-full">
                                        <input type="text" name="message" placeholder="Lời nhắn" required class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                                    </div>
                                </div>
                                <button type="submit" style="background-color: #000; color: #fff; padding: 12px 24px; border: none; font-size: 16px; cursor: pointer; float: right; transition: all 0.3s; border-radius: 4px;">
                                    GỬI <i class="ti-check"></i>
                                </button>
                                <div style="clear: both;"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAP -->
            <!-- <div class="map-section">
                <img src="./landing_page_assets/img/map.jpg" alt="Map" class="map-img">
            </div> -->
        </div>

        <!-- FOOTER -->
        <div id="footer" style="padding: 64px 16px; text-align: center; background-color: #f1f1f1;">
            <div class="socials-list" style="margin-bottom: 20px;">
                <a href="#" style="font-size: 24px; color: #666; text-decoration: none; margin: 0 8px;"><i class="ti-facebook"></i></a>
                <a href="#" style="font-size: 24px; color: #666; text-decoration: none; margin: 0 8px;"><i class="ti-instagram"></i></a>
                <a href="#" style="font-size: 24px; color: #666; text-decoration: none; margin: 0 8px;"><i class="ti-youtube"></i></a>
                <a href="#" style="font-size: 24px; color: #666; text-decoration: none; margin: 0 8px;"><i class="ti-pinterest"></i></a>
                <a href="#" style="font-size: 24px; color: #666; text-decoration: none; margin: 0 8px;"><i class="ti-twitter"></i></a>
                <a href="#" style="font-size: 24px; color: #666; text-decoration: none; margin: 0 8px;"><i class="ti-linkedin"></i></a>
            </div>
            <p class="copyright" style="margin-top: 15px; color: #666; font-size: 15px;">
                Powered by <a href="#" style="color: #666;">Đội Công Tác Xã Hội UIT</a>
            </p>
        </div>
    </div>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>