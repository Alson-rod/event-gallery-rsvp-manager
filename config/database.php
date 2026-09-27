<?php

if (file_exists(__DIR__ . "/database.local.php")) {
    require_once __DIR__ . "/database.local.php";
    return;
}

$host = getenv("DB_HOST") ?: "localhost";
$db = getenv("DB_NAME") ?: "event_gallery";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$port = (int)(getenv("DB_PORT") ?: 3306);

$conn = new mysqli($host, $user, $password, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>