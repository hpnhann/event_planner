<?php
    $server = "localhost";
   $host = '127.0.0.1';
    $user = "root";
    $password = "";
    $db = "_sms";
    $port = '3307'; 

    // $servername = "sql309.infinityfree.com"; 
    // $username   = "if0_40632035";            
    // $password   = "gUDhKP80GfDBxEL"; 
    // $dbname     = "if0_40632035_db";         

    
    $conn = mysqli_connect($server, $user, $password, $db, 3307);

    if (!$conn) {
        // header('Location: ../errors/error.html');
        // exit();
    }
    
    mysqli_set_charset($conn, "utf8mb4");


?>