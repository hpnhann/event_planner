<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "_sms";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("❌ Kết nối database thất bại: " . mysqli_connect_error());
}
?>