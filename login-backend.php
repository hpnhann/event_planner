<?php
error_reporting(0);
session_start();
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(404);
    die();
}

if (isset($_POST['email']) && isset($_POST['password'])) {
    include("assets/config.php");

    if ($conn) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        
        // Lấy return URL nếu có
        $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : '';

        $sql = "SELECT id, role, password_hash FROM users WHERE email=?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                $row = mysqli_fetch_assoc($result);

                if ($row) {
                    if (password_verify($password, $row['password_hash'])) {
                        $_SESSION['uid'] = $row['id'];
                        $response['status'] = 'success';
                        $response['role'] = $row['role'];
                        
                        // Thêm return URL vào response nếu có
                        if (!empty($return_url)) {
                            $response['return_url'] = $return_url;
                        }
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Invalid email or password!';
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Invalid email or password!';
                }

                mysqli_stmt_close($stmt);
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error fetching result';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error preparing statement';
        }
        
        mysqli_close($conn);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Database connection error';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Both fields are required';
}

echo json_encode($response);