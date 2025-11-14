<?php 
error_reporting(0);
session_start();

// Kiểm tra user đã login chưa và role là gì
$isLoggedIn = isset($_SESSION['uid']);
$userRole = '';
$userName = 'Guest';

if ($isLoggedIn) {
    include('assets/config.php'); // SỬA: landing_page_assets/config.php → assets/config.php
    $uid = $_SESSION['uid'];
    
    // Dùng email làm tên hiển thị (vì bảng users chỉ có: id, email, password_hash, role, theme)
    $query = "SELECT `role`, `email` as name FROM `users` WHERE `users`.`id`=?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);
    
    if ($row && isset($row['role'])) {
        $userRole = $row['role'];
        $userName = $row['name'] ?? 'User'; // Nếu null thì hiển thị 'User'
    }
}

// ============================================================
// KHÔNG TỰ ĐỘNG REDIRECT - CHO TẤT CẢ USER VÀO HOMEPAGE
// User tự quyết định vào panel qua navigation menu
// ============================================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đội Công Tác Xã Hội UIT - Trang Chủ</title>
    <link rel="stylesheet" href="./landing_page_assets/css/style.css">
    <link rel="stylesheet" href="./landing_page_assets/font/themify-icons/themify-icons.css">
    <link rel="icon" type="image/x-icon" href="images/1.png">
    <style>
        /* Style cho dropdown user */
        .user-dropdown {
            position: relative;
        }
        .user-dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            top: 100%;
            right: 0;
        }
        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }
        .user-dropdown-content a {
            color: #000;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .user-dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .user-info {
            display: inline-block;
            padding: 0 16px;
            line-height: 46px;
            color: #fff;
            cursor: pointer;
        }
        .user-info i {
            margin-right: 5px;
        }
        
        /* Style cho button đăng ký sự kiện */
        .event-register-btn {
            background: #009688;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .event-register-btn:hover {
            background: #00796b;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div id="main">
        <!-- HEADER -->
        <div id="header">
            <ul id="nav">
                <li><a href="#slider">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#tour">Events</a></li>
                <li><a href="#contact">Contact</a></li>
                
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'admin'): ?>
                    <!-- Menu cho Admin -->
                    <li>
                        <a href="#">
                            MANAGEMENT
                            <i class="nav-arrow-down ti-angle-down"></i>
                        </a>
                        <ul class="subnav">
                            <li><a href="admin_panel/dashboard.php">Dashboard</a></li>
                            <li><a href="admin_panel/members.php">Members</a></li>
                            <li><a href="admin_panel/organisers.php">Organisers</a></li>
                            <li><a href="admin_panel/classes.php">Classes</a></li>
                        </ul>
                    </li>
                    <?php elseif ($userRole === 'owner'): ?>
                    <!-- Menu cho Owner/Organiser -->
                    <li>
                        <a href="#">
                            Organiser Tools
                            <i class="nav-arrow-down ti-angle-down"></i>
                        </a>
                        <ul class="subnav">
                            <li><a href="owner_panel/index.php">Dashboard</a></li>
                            <li><a href="owner_panel/events.php">Manage Events</a></li>
                            <li><a href="owner_panel/volunteers.php">Volunteers</a></li>
                        </ul>
                    </li>
                    <?php elseif ($userRole === 'teacher'): ?>
                    <!-- Menu cho Teacher -->
                    <li>
                        <a href="#">
                            My Panel
                            <i class="nav-arrow-down ti-angle-down"></i>
                        </a>
                        <ul class="subnav">
                            <li><a href="teacher_panel/dashboard.php">Dashboard</a></li>
                            <li><a href="teacher_panel/students.php">My Students</a></li>
                        </ul>
                    </li>
                    <?php elseif ($userRole === 'student'): ?>
                    <!-- Menu cho Student -->
                    <li>
                        <a href="#">
                            My Panel
                            <i class="nav-arrow-down ti-angle-down"></i>
                        </a>
                        <ul class="subnav">
                            <li><a href="student_panel/index.php">Dashboard</a></li>
                            <li><a href="student_panel/grades.php">My Grades</a></li>
                            <li><a href="student_panel/my-events.php">My Events</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Search & User Info -->
            <div style="float: right; display: flex; align-items: center;">
                <?php if ($isLoggedIn): ?>
                    <div class="user-dropdown">
                        <div class="user-info">
                            <i class="ti-user"></i>
                            <?php echo htmlspecialchars($userName); ?>
                            <i class="ti-angle-down"></i>
                        </div>
                        <div class="user-dropdown-content">
                            <a href="<?php 
                                if($userRole === 'admin') echo 'admin_panel/dashboard.php';
                                elseif($userRole === 'owner') echo 'owner_panel/index.php';
                                elseif($userRole === 'teacher') echo 'teacher_panel/dashboard.php';
                                elseif($userRole === 'student') echo 'student_panel/index.php';
                            ?>">
                                <i class="ti-dashboard"></i> Dashboard
                            </a>
                            <a href="<?php 
                                if($userRole === 'admin') echo 'admin_panel/settings.php';
                                elseif($userRole === 'owner') echo 'owner_panel/profile.php';
                                elseif($userRole === 'teacher') echo 'teacher_panel/profile.php';
                                elseif($userRole === 'student') echo 'student_panel/profile.php';
                            ?>">
                                <i class="ti-settings"></i> Profile
                            </a>
                            <a href="logout.php"><i class="ti-power-off"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" style="color: #fff; padding: 0 20px; text-decoration: none; line-height: 46px;">
                        <i class="ti-user"></i> Login
                    </a>
                <?php endif; ?>
                
                <div class="search-btn">
                    <i class="search-icon ti-search"></i>
                </div>
            </div>
        </div>
        
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
                                <button class="place-buy-btn event-register-btn" data-event-id="1">Đăng Ký Tham Gia</button>
                            </div>
                        </div>

                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place5.jpg" alt="Bình Chánh" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">Bình Chánh</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Chiến dịch tình nguyện Mùa Hè Xanh</p>
                                <button class="place-buy-btn event-register-btn" data-event-id="2">Đăng Ký Tham Gia</button>
                            </div>
                        </div>

                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place6.jpg" alt="TP HCM" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">TP HCM</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Tình Nguyện Trung Thu Cho Em</p>
                                <button class="place-buy-btn event-register-btn" data-event-id="3">Đăng Ký Tham Gia</button>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>  
            </div>

            <!-- CONTACT SECTION -->
            <div id="contact" class="content-section">
                <h2 class="section-heading">CONTACT</h2>
                <p class="section-sub-heading">Liên Hệ Với Chúng Tôi</p>
                
                <div class="row contact-content">
                    <div class="col col-half contact-info">
                        <p><i class="ti-location-pin"></i> TP Hồ Chí Minh, Việt Nam</p>
                        <p><i class="ti-mobile"></i> Phone: <a href="tel:+84123456789">+84 123 456 789</a></p>
                        <p><i class="ti-email"></i> Email: <a href="mailto:ctxh@uit.edu.vn">ctxh@uit.edu.vn</a></p>
                    </div>
                    <div class="col col-half contact-form">
                        <form action="">
                            <div class="row">
                                <div class="col col-half">
                                    <input type="text" name="name" placeholder="Họ và tên" required class="form-control">
                                </div>
                                <div class="col col-half">
                                    <input type="email" name="email" placeholder="Email" required class="form-control">
                                </div>
                            </div>
                            <div class="row mt-8">
                                <div class="col col-full">
                                    <input type="text" name="message" placeholder="Lời nhắn" required class="form-control">
                                </div>
                            </div>
                            <input class="contact-submit-btn mt-16" type="submit" value="GỬI">
                        </form>
                    </div>
                </div>
            </div>

            <!-- MAP -->
            <div class="map-section">
                <img src="./landing_page_assets/img/map.jpg" alt="Map" class="map-img">
            </div>
        </div>

        <!-- FOOTER -->
        <div id="footer">
            <div class="socials-list">
                <a href="#"><i class="ti-facebook"></i></a>
                <a href="#"><i class="ti-instagram"></i></a>
                <a href="#"><i class="ti-youtube"></i></a>
                <a href="#"><i class="ti-pinterest"></i></a>
                <a href="#"><i class="ti-twitter"></i></a>
                <a href="#"><i class="ti-linkedin"></i></a>
            </div>
            <p class="copyright">
                Powered by <a href="#">Đội Công Tác Xã Hội UIT</a>
            </p>
        </div>
    </div>

    <!-- MODAL ĐĂNG KÝ SỰ KIỆN -->
    <div class="modal js-modal">
        <div class="modal-container js-modal-container">
            <div class="modal-close js-modal-close">
                <i class="ti-close"></i>
            </div>
            <header class="modal-header">
                <i class="modal-heading-icon ti-calendar"></i>
                Đăng Ký Sự Kiện
            </header>
            <div class="modal-body">
                <?php if ($isLoggedIn): ?>
                    <p style="text-align: center; padding: 20px; color: #333;">
                        <strong>Xác nhận đăng ký tham gia sự kiện?</strong>
                    </p>
                    <form id="event-registration-form">
                        <input type="hidden" id="event-id" name="event_id">
                        <label for="notes" class="modal-label">
                            <i class="ti-pencil"></i>
                            Ghi chú (tùy chọn)
                        </label>
                        <textarea id="notes" name="notes" class="modal-input" rows="3" placeholder="Nhập ghi chú của bạn..."></textarea>
                        
                        <button type="submit" id="register-event-btn">
                            Xác Nhận Đăng Ký <i class="ti-check"></i>
                        </button>
                    </form>
                <?php else: ?>
                    <p style="text-align: center; padding: 20px;">
                        Vui lòng <a href="login.php" style="color: #009688; font-weight: bold;">đăng nhập</a> để đăng ký sự kiện!
                    </p>
                <?php endif; ?>
            </div>
            <footer class="modal-footer">
                <p class="modal-help">Cần <a href="#contact">hỗ trợ?</a></p>
            </footer>   
        </div>
    </div>

    <script>
        // Xử lý mở modal đăng ký sự kiện
        const registerBtns = document.querySelectorAll('.event-register-btn');
        const modal = document.querySelector('.js-modal');
        const modalContainer = document.querySelector('.js-modal-container');
        const modalClose = document.querySelector('.js-modal-close');
        
        function showEventModal(eventId) {
            const eventIdInput = document.getElementById('event-id');
            if (eventIdInput) {
                eventIdInput.value = eventId;
            }
            modal.classList.add('open');
        }

        function hideEventModal() {
            modal.classList.remove('open');
        }

        // Gắn sự kiện cho tất cả button đăng ký
        registerBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                <?php if ($isLoggedIn): ?>
                    showEventModal(eventId);
                <?php else: ?>
                    // Nếu chưa login, redirect về trang login với return URL
                    window.location.href = 'login.php?return=' + encodeURIComponent(window.location.href);
                <?php endif; ?>
            });
        });
        
        if (modalClose) {
            modalClose.addEventListener('click', hideEventModal);
        }
        
        if (modal) {
            modal.addEventListener('click', hideEventModal);
        }
        
        if (modalContainer) {
            modalContainer.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        }

        <?php if ($isLoggedIn): ?>
        // Xử lý submit form đăng ký sự kiện
        const eventForm = document.getElementById('event-registration-form');
        if (eventForm) {
            eventForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('register-event.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Đăng ký thành công!');
                        hideEventModal();
                    } else {
                        alert('Đăng ký thất bại: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi đăng ký!');
                });
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>