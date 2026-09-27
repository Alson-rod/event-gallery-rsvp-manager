<?php

require_once __DIR__ . "/../config/database.php";

$result = $conn->query(
    "SELECT * FROM Events ORDER BY event_date ASC, event_time ASC"
);

if (!$result) {
    die("Failed to load events: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Event</title>

    <link
        rel="stylesheet"
        href="../public/style.css"
    >

</head>

<body>

<h1>Events</h1>

<p>
    <a href="create.php">Create Event</a>
</p>

<?php if ($result->num_rows === 0): ?>

    <p>No events found.</p>

<?php else: ?>

    <?php while ($event = $result->fetch_assoc()): ?>

        <article>

            <h2>
                <?= htmlspecialchars($event['title']) ?>
            </h2>

            <p>
                <strong>Date:</strong>
                <?= htmlspecialchars($event['event_date']) ?>
            </p>

            <p>
                <strong>Time:</strong>
                <?= htmlspecialchars($event['event_time'] ?? '') ?>
            </p>

            <p>
                <strong>Location:</strong>
                <?= htmlspecialchars($event['location'] ?? '') ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= htmlspecialchars($event['status']) ?>
            </p>

            <p>
                <?= nl2br(htmlspecialchars($event['description'] ?? '')) ?>
            </p>

            <p>
                <a href="edit.php?id=<?= $event['event_id'] ?>">
                    Edit Event
                </a>
            </p>

        </article>

        <hr>

    <?php endwhile; ?>

<?php endif; ?>

</body>
</html>