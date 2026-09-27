<?php

require_once __DIR__ . "/../config/database.php";

$result = $conn->query(
    "SELECT
        r.rsvp_id,
        e.title,
        e.event_date,
        a.name,
        a.email,
        a.phone,
        r.status,
        r.rsvp_at
     FROM RSVPs r
     INNER JOIN Events e
        ON r.event_id = e.event_id
     INNER JOIN Attendees a
        ON r.attendee_id = a.attendee_id
     ORDER BY r.rsvp_at DESC"
);

if (!$result) {
    die("Failed to load RSVPs: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RSVPs</title>
</head>

<body>

<h1>RSVPs</h1>

<p>
    <a href="index.php">Back to Events</a>
</p>

<?php if ($result->num_rows === 0): ?>

    <p>No RSVPs found.</p>

<?php else: ?>

    <?php while ($rsvp = $result->fetch_assoc()): ?>

        <article>

            <h2>
                <?= htmlspecialchars($rsvp["title"]) ?>
            </h2>

            <p>
                <strong>Event Date:</strong>
                <?= htmlspecialchars($rsvp["event_date"]) ?>
            </p>

            <p>
                <strong>Attendee:</strong>
                <?= htmlspecialchars($rsvp["name"]) ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= htmlspecialchars($rsvp["email"]) ?>
            </p>

            <p>
                <strong>Phone:</strong>
                <?= htmlspecialchars($rsvp["phone"] ?? "") ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= htmlspecialchars($rsvp["status"]) ?>
            </p>

            <p>
                <strong>RSVP Date:</strong>
                <?= htmlspecialchars($rsvp["rsvp_at"]) ?>
            </p>

            <p>
                <a href="update_rsvp.php?id=<?= $rsvp["rsvp_id"] ?>&status=accepted">
                    Accept
                </a>
                |
                <a href="update_rsvp.php?id=<?= $rsvp["rsvp_id"] ?>&status=declined">
                    Decline
                </a>
            </p>

        </article>

        <hr>

    <?php endwhile; ?>

<?php endif; ?>

</body>

</html>