<?php
$host = "127.0.0.1";
$user = "root";
$password = "root";
$database = "souq_alnakhil_db";
$port = 8889;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>