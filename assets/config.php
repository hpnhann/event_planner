<?php
    $server = "localhost";
   $host = '127.0.0.1';
    $user = "root";
    $password = "";
    $db = "_sms";
    $port = '3307'; 

    
    $conn = mysqli_connect($server, $user, $password, $db, 3307);

    if (!$conn) {
        header('Location: ../errors/error.html');
        exit();
    }


?>