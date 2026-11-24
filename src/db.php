<?php
$host = getenv('MYSQLHOST') ?: 'db';
$port = getenv('MYSQLPORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'your_local_password';
$name = getenv('MYSQLDATABASE') ?: 'your_local_db_name';

$conn = new mysqli($host, $user, $pass, $name, (int)$port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
