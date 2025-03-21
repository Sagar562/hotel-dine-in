<?php

$localHost = 'localhost';
$userName = 'sagar';
$password = '123456';
$dbName = 'hotel_dine_in_system';
// $port = '';

    $conn = mysqli_connect($localHost, $userName, $password, $dbName);

    if ($conn)
    {
        echo "Database connected successfully";
    }
    else
    {
        echo "error while connecting Database" . mysqli_connect_error();
    }

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description, X-Requested-With, Authorization');
?>
