<?php
error_reporting(0);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Volunteer Management</title>
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

        /* Navigation */
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

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInUp 0.8s;
        }

        .hero-section p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
            animation: fadeInUp 0.8s 0.2s both;
        }

        /* Content Section */
        .content-section {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-10px);
        }

        .card-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
        }

        .card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .card p {
            color: #666;
            line-height: 1.8;
        }

        /* Values Section */
        .values-section {
            background: white;
            padding: 4rem 2rem;
            margin: 4rem 0;
        }

        .values-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 3rem;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .value-item {
            text-align: center;
            padding: 2rem;
        }

        .value-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #667eea;
            margin: 0 auto 1.5rem;
        }

        .value-item h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .value-item p {
            color: #666;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 2rem;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Team Section */
        .team-section {
            padding: 4rem 2rem;
        }

        .team-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-member {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .team-member:hover {
            transform: translateY(-10px);
        }

        .member-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 1.5rem;
        }

        .member-name {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .member-role {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .member-bio {
            color: #666;
            line-height: 1.6;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <ul class="nav-menu">
                <li><a href="index.php">HOME</a></li>
                <li><a href="about.php" class="active">ABOUT</a></li>
                <li><a href="public_events.php">EVENTS</a></li>
                <li><a href="contact.php">CONTACT</a></li>
                <?php if (isset($_SESSION['uid'])): ?>
                    <li><a href="my_activity.php">MY ACTIVITY</a></li>
                <?php endif; ?>
            </ul>
            <div>
                <?php if (isset($_SESSION['uid'])): ?>
                    <a href="logout.php" class="btn-login">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">
                        <i class="fas fa-user"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1><i class="fas fa-heart"></i> About Us</h1>
        <p>We are a community-driven platform dedicated to connecting volunteers with meaningful opportunities to make a difference in their communities.</p>
    </div>

    <!-- Mission & Vision -->
    <div class="content-section">
        <div class="mission-vision">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3>Our Mission</h3>
                <p>To empower individuals to create positive change by providing accessible volunteer opportunities that address community needs and foster social responsibility.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Our Vision</h3>
                <p>A world where everyone has the opportunity to contribute their time and talents to build stronger, more connected communities.</p>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3>Our Values</h3>
                <p>Compassion, integrity, inclusivity, and impact drive everything we do. We believe in the power of collective action to transform lives.</p>
            </div>
        </div>
    </div>

    <!-- Values Section -->
    <div class="values-section">
        <div class="values-container">
            <h2 class="section-title">Our Core Values</h2>
            <div class="values-grid">
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Compassion</h4>
                    <p>We care deeply about the communities we serve</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Community</h4>
                    <p>Together, we achieve more than we could alone</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>Excellence</h4>
                    <p>We strive for quality in every interaction</p>
                </div>
                <div class="value-item">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p>We act with honesty and transparency</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <h3>500+</h3>
                <p>Active Volunteers</p>
            </div>
            <div class="stat-item">
                <h3>100+</h3>
                <p>Events Organized</p>
            </div>
            <div class="stat-item">
                <h3>5,000+</h3>
                <p>Hours Contributed</p>
            </div>
            <div class="stat-item">
                <h3>50+</h3>
                <p>Partner Organizations</p>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="team-section">
        <div class="team-container">
            <h2 class="section-title">Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="member-name">Sarah Johnson</h3>
                    <p class="member-role">Executive Director</p>
                    <p class="member-bio">Leading our mission to connect volunteers with impactful opportunities.</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="member-name">Michael Chen</h3>
                    <p class="member-role">Community Manager</p>
                    <p class="member-bio">Building relationships with local organizations and volunteers.</p>
                </div>
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="member-name">Emily Rodriguez</h3>
                    <p class="member-role">Events Coordinator</p>
                    <p class="member-bio">Organizing and managing volunteer events and programs.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>