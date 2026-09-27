<?php

require_once __DIR__ . "/../config/database.php";

$rsvp_id = $_GET["id"] ?? null;
$status = $_GET["status"] ?? null;

$allowed_statuses = ["pending", "accepted", "declined"];

if (
    !$rsvp_id ||
    !filter_var($rsvp_id, FILTER_VALIDATE_INT)
) {
    die("Invalid RSVP ID.");
}

if (!in_array($status, $allowed_statuses, true)) {
    die("Invalid RSVP status.");
}

$stmt = $conn->prepare(
    "UPDATE RSVPs
     SET status = ?
     WHERE rsvp_id = ?"
);

$stmt->bind_param(
    "si",
    $status,
    $rsvp_id
);

if ($stmt->execute()) {
    header("Location: rsvps.php");
    exit;
}

die("Failed to update RSVP.");

?>