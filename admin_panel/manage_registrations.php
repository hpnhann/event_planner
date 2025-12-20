<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if admin is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: ../login.php');
    exit();
}

require_once('../assets/config.php');

if (!$conn) {
    die("Database connection failed");
}

// Validate Role (Session or DB Fallback)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Session role missing/mismatch, verify against DB to be sure
    $uid_check = $_SESSION['uid'];
    $role_query = "SELECT role FROM users WHERE id = ?";
    $stmt_role = mysqli_prepare($conn, $role_query);
    mysqli_stmt_bind_param($stmt_role, "s", $uid_check);
    mysqli_stmt_execute($stmt_role);
    $res_role = mysqli_stmt_get_result($stmt_role);
    $user_role = mysqli_fetch_assoc($res_role);
    mysqli_stmt_close($stmt_role);
    
    if ($user_role && $user_role['role'] === 'admin') {
        $_SESSION['role'] = 'admin'; // Heal the session
    } else {
        // Really not an admin
        header('Location: ../login.php');
        exit();
    }
}

// Get admin info
$admin_id = $_SESSION['uid'];
$adminQuery = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $adminQuery);
mysqli_stmt_bind_param($stmt, "s", $admin_id);
mysqli_stmt_execute($stmt);
$adminResult = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($adminResult);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event Registrations</title>
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
            gap: 2rem;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
        }

        .nav-title {
            font-size: 1.1rem;
            color: #333;
            font-weight: 600;
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

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            color: white;
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Filter Cards */
        .filter-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
            border: 3px solid transparent;
        }

        .filter-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .filter-card.active {
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .filter-card-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .filter-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .filter-icon.all { background: #e7e8ff; color: #667eea; }
        .filter-icon.pending { background: #fff3cd; color: #856404; }
        .filter-icon.approved { background: #d1e7dd; color: #0f5132; }
        .filter-icon.rejected { background: #f8d7da; color: #842029; }

        .filter-info h3 {
            font-size: 2rem;
            margin: 0;
        }

        .filter-info p {
            margin: 0;
            color: #666;
            font-weight: 600;
        }

        /* Registration Table */
        .registration-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e9ecef;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .event-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .event-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .event-details h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            color: #333;
        }

        .event-meta {
            font-size: 0.85rem;
            color: #666;
        }

        .user-info-cell {
            font-size: 0.9rem;
        }

        .user-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .user-email {
            color: #666;
            font-size: 0.85rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.approved {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-badge.rejected {
            background: #f8d7da;
            color: #842029;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            background: #5568d3;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
            border-left: 4px solid #dc3545;
        }

        /* Loading State */
        .loading-spinner {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .filter-cards {
                grid-template-columns: 1fr;
            }

            .event-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                flex-direction: column;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-left">
                <a href="../index.php" class="nav-brand">
                    <i class="fas fa-calendar-alt"></i> Event Manager
                </a>
                <span class="nav-title">Manage Registrations</span>
            </div>
            
            <div class="nav-right">
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($admin['email'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($admin['email']); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-tasks"></i> Event Registration Management</h1>
        <p>Review and approve volunteer event registrations</p>
    </div>

    <!-- Content -->
    <div class="content-container">
        <!-- Alert Messages -->
        <div id="alertMessage" class="alert"></div>

        <!-- Filter Cards -->
        <div class="filter-cards">
            <div class="filter-card active" onclick="filterStatus('all')" id="filter-all">
                <div class="filter-card-content">
                    <div class="filter-icon all">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div class="filter-info">
                        <h3 id="allCount">0</h3>
                        <p>All Registrations</p>
                    </div>
                </div>
            </div>

            <div class="filter-card" onclick="filterStatus('pending')" id="filter-pending">
                <div class="filter-card-content">
                    <div class="filter-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="filter-info">
                        <h3 id="pendingCount">0</h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>

            <div class="filter-card" onclick="filterStatus('approved')" id="filter-approved">
                <div class="filter-card-content">
                    <div class="filter-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="filter-info">
                        <h3 id="approvedCount">0</h3>
                        <p>Approved</p>
                    </div>
                </div>
            </div>

            <div class="filter-card" onclick="filterStatus('rejected')" id="filter-rejected">
                <div class="filter-card-content">
                    <div class="filter-icon rejected">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="filter-info">
                        <h3 id="rejectedCount">0</h3>
                        <p>Rejected</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration List -->
        <div class="registration-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-table"></i>
                    Registration List
                </h2>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Participant</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="registrationTableBody">
                        <tr>
                            <td colspan="5">
                                <div class="loading-spinner">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                    <p>Loading registrations...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-inbox"></i>
                <h3>No Registrations Found</h3>
                <p>There are no registrations matching your filter.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentFilter = 'all';

        // Load registrations on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadRegistrations('all');
        });

        function filterStatus(status) {
            currentFilter = status;
            
            // Update active filter card
            document.querySelectorAll('.filter-card').forEach(card => {
                card.classList.remove('active');
            });
            document.getElementById('filter-' + status).classList.add('active');
            
            // Load data
            loadRegistrations(status);
        }

        function loadRegistrations(status) {
            const tableBody = document.getElementById('registrationTableBody');
            const emptyState = document.getElementById('emptyState');
            
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Loading registrations...</p>
                        </div>
                    </td>
                </tr>
            `;
            
            fetch('fetch_registrations.php?status=' + status)
                .then(response => response.json())
                .then(data => {
                    console.log('API Response:', data); // Debug
                    
                    if (data.success) {
                        // Update counts
                        document.getElementById('allCount').textContent = data.counts.all || 0;
                        document.getElementById('pendingCount').textContent = data.counts.pending || 0;
                        document.getElementById('approvedCount').textContent = data.counts.approved || 0;
                        document.getElementById('rejectedCount').textContent = data.counts.rejected || 0;
                        
                        // Display registrations
                        if (data.registrations && data.registrations.length > 0) {
                            tableBody.innerHTML = '';
                            emptyState.style.display = 'none';
                            
                            data.registrations.forEach(reg => {
                                const row = createRegistrationRow(reg);
                                tableBody.innerHTML += row;
                            });
                        } else {
                            tableBody.innerHTML = '';
                            emptyState.style.display = 'block';
                        }
                    } else {
                        showAlert('Error loading registrations: ' + data.message, 'error');
                        tableBody.innerHTML = '';
                        emptyState.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showAlert('Failed to load registrations. Please check console for details.', 'error');
                    tableBody.innerHTML = '';
                    emptyState.style.display = 'block';
                });
        }

        function createRegistrationRow(reg) {
            const statusClass = 'status-badge ' + reg.status;
            const eventImage = reg.event_image ? 
                `<img src="../uploads/events/${escapeHtml(reg.event_image)}" class="event-image" alt="Event">` :
                `<div class="event-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>`;
            
            const statusIcons = {
                'pending': 'fa-clock',
                'approved': 'fa-check-circle',
                'rejected': 'fa-times-circle',
                'cancelled': 'fa-ban'
            };
            const statusIcon = statusIcons[reg.status] || 'fa-circle';
            
            let actionButtons = '';
            if (reg.status === 'pending') {
                actionButtons = `
                    <div class="action-buttons">
                        <button class="action-btn btn-approve" onclick="updateStatus(${reg.id}, 'approved')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="action-btn btn-reject" onclick="updateStatus(${reg.id}, 'rejected')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                `;
            } else {
                actionButtons = `
                    <div class="action-buttons">
                        <button class="action-btn btn-view" onclick="viewDetails(${reg.event_id})">
                            <i class="fas fa-eye"></i> View Event
                        </button>
                    </div>
                `;
            }
            
            return `
                <tr id="row-${reg.id}">
                    <td>
                        <div class="event-info">
                            ${eventImage}
                            <div class="event-details">
                                <h4>${escapeHtml(reg.event_title)}</h4>
                                <div class="event-meta">
                                    <i class="fas fa-calendar"></i> ${formatDate(reg.event_date)}
                                    &nbsp;&nbsp;
                                    <i class="fas fa-clock"></i> ${formatTime(reg.event_time)}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-name">${escapeHtml(reg.user_name || 'N/A')}</div>
                            <div class="user-email">${escapeHtml(reg.user_email)}</div>
                        </div>
                    </td>
                    <td>${formatDateTime(reg.registration_date)}</td>
                    <td>
                        <span class="${statusClass}">
                            <i class="fas ${statusIcon}"></i>
                            ${reg.status.charAt(0).toUpperCase() + reg.status.slice(1)}
                        </span>
                    </td>
                    <td>${actionButtons}</td>
                </tr>
            `;
        }

        function updateStatus(regId, newStatus) {
            const confirmMsg = newStatus === 'approved' ? 
                'Are you sure you want to APPROVE this registration?' :
                'Are you sure you want to REJECT this registration?';
            
            if (!confirm(confirmMsg)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('registration_id', regId);
            formData.append('action', newStatus);
            
            fetch('process_registration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadRegistrations(currentFilter);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to update registration', 'error');
            });
        }

        function viewDetails(eventId) {
            window.location.href = '../event_detail.php?id=' + eventId;
        }

        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.className = 'alert alert-' + type + ' show';
            alertDiv.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '"></i> ' + message;
            
            setTimeout(() => {
                alertDiv.classList.remove('show');
            }, 5000);
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function formatTime(timeStr) {
            if (!timeStr) return '';
            const [hours, minutes] = timeStr.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }

        function formatDateTime(dateTimeStr) {
            const date = new Date(dateTimeStr);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>