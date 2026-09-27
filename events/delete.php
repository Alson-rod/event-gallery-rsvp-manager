<?php

require_once __DIR__ . "/../config/database.php";

$event_id = $_GET["id"] ?? null;

if (!$event_id || !filter_var($event_id, FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}

$stmt = $conn->prepare("DELETE FROM Events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);

if ($stmt->execute()) {
    header("Location: index.php");
    exit;
}

die("Failed to delete event.");

?>