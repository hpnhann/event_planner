<?php
header('Content-Type: application/json');
session_start();
require_once('../assets/config.php');

// 1. Check Login & Admin Role
if (!isset($_SESSION['uid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$current_uid = $_SESSION['uid'];
$checkAdmin = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($checkAdmin);
$stmt->bind_param("s", $current_uid);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || $row['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit();
}

// 2. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

try {
    // 3. Get Data
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';

    // 4. Validate Inputs
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        throw new Exception("Please fill in all fields");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters");
    }

    // 5. Check Duplicate Email
    $checkEmail = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists");
    }
    $stmt->close();

    // 6. Insert New Organiser
    // ID strategy: use email or generated ID? Project seems to use mixed.
    // Seeing register.php, user manually input 'student_id'. 
    // For organisers, let's auto-generate or use email prefix + random or just time().
    // Let's use a unique string ID based on time to avoid collision if not provided.
    // Or users table might separate ID and Email. 
    // In auth_register.php: $student_id was passed manually.
    // Validating from organizers.php list: ID seems to be int or string?
    // Let's generate a unique ID for teacher if not provided manually.
    $new_id = "TCH" . time(); 
    $role = 'teacher';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insertSql = "INSERT INTO users (id, full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ssssss", $new_id, $full_name, $email, $phone, $hashed_password, $role);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Organiser added successfully']);
    } else {
        throw new Exception("Database error: " . $conn->error);
    }
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
