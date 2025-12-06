<!DOCTYPE html> 
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đội Công Tác Xã Hội UIT - School Management</title>
    <link rel="stylesheet" href="./landing_page_assets/css/style.css">
    <link rel="stylesheet" href="./landing_page_assets/font/themify-icons/themify-icons.css">
    <link rel="icon" type="image/x-icon" href="images/1.png">
    <style>
        .user-info {
            display: inline-block;
            padding: 0 16px;
            line-height: 46px;
            color: #fff;
            cursor: pointer;
            position: relative;
        }
        .user-info i {
            margin-right: 5px;
        }
        .user-dropdown {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1000;
            top: 100%;
            right: 0;
            border-radius: 4px;
        }
        .user-info:hover .user-dropdown {
            display: block;
        }
        .user-dropdown a {
            color: #000;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .user-dropdown a:hover {
            background-color: #f1f1f1;
        }
        .user-dropdown a i {
            margin-right: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div id="main">
        <div id="header">
            <ul id="nav">
                <li><a href="#slider">Home</a></li>
                <li><a href="#about">Band</a></li>
                <li><a href="#tour">Tour</a></li>
                <li><a href="#contact">Contact</a></li>
                
                <?php if ($isLoggedIn && ($userRole === 'admin' || $userRole === 'owner')): ?>
                <li>
                    <a href="#">
                        Management
                        <i class="nav-arrow-down ti-angle-down"></i>
                    </a>
                    <ul class="subnav">
                        <?php if ($userRole === 'admin'): ?>
                            <li><a href="admin_panel/dashboard.php">Dashboard</a></li>
                            <li><a href="admin_panel/students.php">Students</a></li>
                            <li><a href="admin_panel/teachers.php">Teachers</a></li>
                            <li><a href="admin_panel/classes.php">Classes</a></li>
                        <?php elseif ($userRole === 'owner'): ?>
                            <li><a href="owner_panel/index.php">Dashboard</a></li>
                            <li><a href="owner_panel/users.php">Users</a></li>
                            <li><a href="owner_panel/settings.php">Settings</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <div style="float: right; display: flex; align-items: center;">
                <?php if ($isLoggedIn): ?>
                    <div class="user-info">
                        <i class="ti-user"></i>
                        <?php echo htmlspecialchars($userName); ?>
                        <i class="ti-angle-down"></i>
                        <div class="user-dropdown">
                            <a href="<?php 
                                if($userRole === 'admin') echo 'admin_panel/dashboard.php';
                                elseif($userRole === 'owner') echo 'owner_panel/index.php';
                            ?>">
                                <i class="ti-dashboard"></i> Dashboard
                            </a>
                            <a href="<?php 
                                if($userRole === 'admin') echo 'admin_panel/profile.php';
                                elseif($userRole === 'owner') echo 'owner_panel/profile.php';
                            ?>">
                                <i class="ti-settings"></i> Profile
                            </a>
                            <a href="logout.php"><i class="ti-power-off"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                <a href="login.php" style="color: #fff; padding: 0 10px; text-decoration: none; line-height: 46px;">
                    <i class="ti-user"></i> Login
                </a>
                <a href="register.php" style="color: #fff; padding: 0 10px; text-decoration: none; line-height: 46px; background: #009688; border-radius: 4px;">
                    <i class="ti-write"></i> Register
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

        <div id="content">
            <!-- ABOUT SECTION -->
            <div id="about" class="content-section">
                <h2 class="section-heading">ĐỘI CÔNG TÁC XÃ HỘI</h2>
                <p class="section-sub-heading">Cống Hiến Để Trưởng Thành</p>
                <p class="about-text">
                    Đến với Đội Công tác Xã hội UIT - với phương châm hoạt động "Cống hiến để trưởng thành" - 
                    ta sẽ có dịp được bắt gặp những con người năng động, nhiệt huyết và tràn đầy sức sống nhất, 
                    vẫn luôn cống hiến bản thân mình để giúp đỡ những mảnh đời khó khăn và qua đó mà trưởng thành nhiều hơn.
                    <?php if ($isLoggedIn): ?>
                        <br><br>
                        <strong>Chào mừng <?php echo htmlspecialchars($userName); ?> (<?php echo ucfirst($userRole); ?>) đã quay trở lại!</strong>
                    <?php endif; ?>
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

            <!-- TOUR SECTION -->
            <div id="tour" class="tour-section">
                <div class="content-section">
                    <h2 class="section-heading text-white">TOUR DATES</h2>
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
                                <button class="place-buy-btn js-buy-ticket">Buy Tickets</button>
                            </div>
                        </div>

                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place5.jpg" alt="Bình Chánh" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">Bình Chánh</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Chiến dịch tình nguyện Mùa Hè Xanh</p>
                                <button class="place-buy-btn js-buy-ticket">Buy Tickets</button>
                            </div>
                        </div>

                        <div class="places-item">
                            <img src="./landing_page_assets/img/place/place6.jpg" alt="TP HCM" class="place-img">
                            <div class="place-body">
                                <h3 class="place-heading">TP HCM</h3>
                                <p class="place-time">Fri 27 Nov 2024</p>
                                <p class="place-desc">Tình Nguyện Trung Thu Cho Em</p>
                                <button class="place-buy-btn js-buy-ticket">Buy Tickets</button>
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
                Powered by <a href="#">Đội Công Tác Xã Hội UIT</a> | 
                <?php if (!$isLoggedIn): ?>
                    <a href="login.php">Admin Login</a>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- MODAL BUY TICKETS -->
    <div class="modal js-modal">
        <div class="modal-container js-modal-container">
            <div class="modal-close js-modal-close">
                <i class="ti-close"></i>
            </div>
            <header class="modal-header">
                <i class="modal-heading-icon ti-bag"></i>
                Tickets
            </header>
            <div class="modal-body">
                <?php if ($isLoggedIn): ?>
                    <label for="tickets-quantity" class="modal-label">
                        <i class="ti-shopping-cart"></i>
                        Tickets, $15 per person
                    </label>
                    <input id="tickets-quantity" type="text" class="modal-input" placeholder="How many?">

                    <label for="tickets-email" class="modal-label">
                        <i class="ti-user"></i>
                        Send to
                    </label>
                    <input id="tickets-email" type="email" class="modal-input" placeholder="Enter email..." value="<?php echo isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?>">
                    
                    <button id="buy-tickets">
                        Pay <i class="ti-check"></i>
                    </button>
                <?php else: ?>
                    <p style="text-align: center; padding: 20px;">
                        Vui lòng <a href="login.php" style="color: #009688; font-weight: bold;">đăng nhập</a> để mua vé!
                    </p>
                <?php endif; ?>
            </div>
            <footer class="modal-footer">
                <p class="modal-help">Need <a href="#contact">help?</a></p>
            </footer>   
        </div>
    </div>

    <script>
        const buyBtns = document.querySelectorAll('.js-buy-ticket')
        const modal = document.querySelector('.js-modal')
        const modalContainer = document.querySelector('.js-modal-container')
        const modalClose = document.querySelector('.js-modal-close')
        
        function showBuyTickets(){
            modal.classList.add('open')
        }

        function hideBuyTickets(){
            modal.classList.remove('open')
        }

        for(const buyBtn of buyBtns) {
            buyBtn.addEventListener('click', showBuyTickets)
        }
        
        modalClose.addEventListener('click', hideBuyTickets)
        modal.addEventListener('click', hideBuyTickets)
        
        modalContainer.addEventListener('click', function(event){
            event.stopPropagation()
        })
    </script>
</body>
</html>