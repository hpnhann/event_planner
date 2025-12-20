<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if running from admin_panel or root
if (file_exists('../assets/config.php')) {
    require_once('../assets/config.php');
} elseif (file_exists('assets/config.php')) {
    require_once('assets/config.php');
} else {
    echo json_encode(['success' => false, 'message' => 'Config file not found']);
    exit();
}

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get filter status
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Get counts for each status
$count_sql = "SELECT 
                status,
                COUNT(*) as count
              FROM event_registrations
              GROUP BY status";
$count_result = mysqli_query($conn, $count_sql);

$counts = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'cancelled' => 0
];

while ($row = mysqli_fetch_assoc($count_result)) {
    $counts[$row['status']] = (int)$row['count'];
}
$counts['all'] = array_sum($counts);

// Build main query
$sql = "SELECT 
            er.id,
            er.event_id,
            er.user_id,
            er.notes,
            er.registration_date,
            er.status,
            e.event_title,
            e.event_date,
            e.event_time,
            e.event_location,
            e.event_image,
            u.name as user_name,
            u.full_name,
            u.email as user_email,
            u.phone
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        JOIN users u ON er.user_id = u.id";

// Add filter condition
if ($filter_status !== 'all') {
    $sql .= " WHERE er.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

$sql .= " ORDER BY er.registration_date DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'success' => false, 
        'message' => 'Query failed: ' . mysqli_error($conn)
    ]);
    exit();
}

$registrations = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Use full_name if available, otherwise use name
    $row['user_name'] = !empty($row['full_name']) ? $row['full_name'] : $row['name'];
    unset($row['full_name'], $row['name']); // Clean up
    
    $registrations[] = $row;
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'counts' => $counts,
    'registrations' => $registrations,
    'filter' => $filter_status
]);
?>