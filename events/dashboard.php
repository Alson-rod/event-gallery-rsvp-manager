<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Dashboard Summary Statistics
|--------------------------------------------------------------------------
*/

$total_events_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM Events"
);

if (!$total_events_result) {
    die("Failed to load total events: " . $conn->error);
}

$total_events = $total_events_result->fetch_assoc()["total"];


$total_rsvps_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM RSVPs"
);

if (!$total_rsvps_result) {
    die("Failed to load total RSVPs: " . $conn->error);
}

$total_rsvps = $total_rsvps_result->fetch_assoc()["total"];


$accepted_rsvps_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM RSVPs
     WHERE status = 'accepted'"
);

if (!$accepted_rsvps_result) {
    die("Failed to load accepted RSVPs: " . $conn->error);
}

$accepted_rsvps = $accepted_rsvps_result->fetch_assoc()["total"];


$pending_rsvps_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM RSVPs
     WHERE status = 'pending'"
);

if (!$pending_rsvps_result) {
    die("Failed to load pending RSVPs: " . $conn->error);
}

$pending_rsvps = $pending_rsvps_result->fetch_assoc()["total"];


$declined_rsvps_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM RSVPs
     WHERE status = 'declined'"
);

if (!$declined_rsvps_result) {
    die("Failed to load declined RSVPs: " . $conn->error);
}

$declined_rsvps = $declined_rsvps_result->fetch_assoc()["total"];


$total_media_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM Media"
);

if (!$total_media_result) {
    die("Failed to load total media: " . $conn->error);
}

$total_media = $total_media_result->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| RSVP Count Per Event
|--------------------------------------------------------------------------
*/

$event_stats_result = $conn->query(
    "SELECT
        e.event_id,
        e.title,
        e.event_date,
        COUNT(r.rsvp_id) AS total_rsvps,
        SUM(r.status = 'accepted') AS accepted_rsvps,
        SUM(r.status = 'pending') AS pending_rsvps,
        SUM(r.status = 'declined') AS declined_rsvps
     FROM Events e
     LEFT JOIN RSVPs r
        ON e.event_id = r.event_id
     GROUP BY
        e.event_id,
        e.title,
        e.event_date
     ORDER BY e.event_date ASC"
);

if (!$event_stats_result) {
    die("Failed to load event RSVP statistics: " . $conn->error);
}


/*
|--------------------------------------------------------------------------
| Attendance Trend
|--------------------------------------------------------------------------
*/

$attendance_result = $conn->query(
    "SELECT
        e.event_date,
        e.title,
        COUNT(r.rsvp_id) AS accepted_attendees
     FROM Events e
     LEFT JOIN RSVPs r
        ON e.event_id = r.event_id
        AND r.status = 'accepted'
     GROUP BY
        e.event_id,
        e.event_date,
        e.title
     ORDER BY e.event_date ASC"
);

if (!$attendance_result) {
    die("Failed to load attendance trend: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Event Dashboard</title>

</head>

<body>

<h1>Event Dashboard</h1>


<!--
|--------------------------------------------------------------------------
| Overall Statistics
|--------------------------------------------------------------------------
-->

<h2>Overall Statistics</h2>

<p>
    <strong>Total Events:</strong>
    <?= $total_events ?>
</p>

<p>
    <strong>Total RSVPs:</strong>
    <?= $total_rsvps ?>
</p>

<p>
    <strong>Accepted RSVPs:</strong>
    <?= $accepted_rsvps ?>
</p>

<p>
    <strong>Pending RSVPs:</strong>
    <?= $pending_rsvps ?>
</p>

<p>
    <strong>Declined RSVPs:</strong>
    <?= $declined_rsvps ?>
</p>

<p>
    <strong>Total Media:</strong>
    <?= $total_media ?>
</p>


<hr>


<!--
|--------------------------------------------------------------------------
| RSVP Statistics Per Event
|--------------------------------------------------------------------------
-->

<h2>RSVP Statistics Per Event</h2>

<?php if ($event_stats_result->num_rows === 0): ?>

    <p>No events found.</p>

<?php else: ?>

    <?php while ($event = $event_stats_result->fetch_assoc()): ?>

        <article>

            <h3>
                <?= htmlspecialchars($event["title"]) ?>
            </h3>

            <p>
                <strong>Date:</strong>
                <?= htmlspecialchars($event["event_date"]) ?>
            </p>

            <p>
                <strong>Total RSVPs:</strong>
                <?= $event["total_rsvps"] ?>
            </p>

            <p>
                <strong>Accepted:</strong>
                <?= $event["accepted_rsvps"] ?? 0 ?>
            </p>

            <p>
                <strong>Pending:</strong>
                <?= $event["pending_rsvps"] ?? 0 ?>
            </p>

            <p>
                <strong>Declined:</strong>
                <?= $event["declined_rsvps"] ?? 0 ?>
            </p>

        </article>

        <hr>

    <?php endwhile; ?>

<?php endif; ?>


<!--
|--------------------------------------------------------------------------
| Attendance Trend
|--------------------------------------------------------------------------
-->

<h2>Attendance Trend</h2>

<?php if ($attendance_result->num_rows === 0): ?>

    <p>No attendance data available.</p>

<?php else: ?>

    <table border="1" cellpadding="8">

        <thead>

            <tr>

                <th>Event Date</th>

                <th>Event</th>

                <th>Accepted Attendees</th>

            </tr>

        </thead>

        <tbody>

            <?php while ($attendance = $attendance_result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($attendance["event_date"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($attendance["title"]) ?>
                    </td>

                    <td>
                        <?= $attendance["accepted_attendees"] ?? 0 ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

<?php endif; ?>


<hr>


<p>
    <a href="index.php">Back to Events</a>
</p>

</body>

</html>