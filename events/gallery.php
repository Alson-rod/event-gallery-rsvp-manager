<?php

require_once __DIR__ . "/../config/database.php";

$event_id = $_GET["event_id"] ?? null;

if (!$event_id || !filter_var($event_id, FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}

$event_stmt = $conn->prepare(
    "SELECT event_id, title, event_date
     FROM Events
     WHERE event_id = ?"
);

$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();

$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$media_stmt = $conn->prepare(
    "SELECT media_id, file_name, file_path, media_type, file_size, uploaded_at
     FROM Media
     WHERE event_id = ?
     ORDER BY uploaded_at DESC"
);

$media_stmt->bind_param("i", $event_id);
$media_stmt->execute();

$media_result = $media_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Event Gallery</title>

    <link
        rel="stylesheet"
        href="../public/style.css"
    >

</head>

<body>

<h1>
    Gallery: <?= htmlspecialchars($event["title"]) ?>
</h1>

<p>
    <strong>Event Date:</strong>
    <?= htmlspecialchars($event["event_date"]) ?>
</p>

<p>
    <a href="upload.php">Upload Photo / Video</a>
</p>

<p>
    <a href="index.php">Back to Events</a>
</p>

<hr>

<?php if ($media_result->num_rows === 0): ?>

    <p>No media uploaded for this event.</p>

<?php else: ?>

    <?php while ($media = $media_result->fetch_assoc()): ?>

        <article>

            <h2>
                <?= htmlspecialchars($media["file_name"]) ?>
            </h2>

            <?php if ($media["media_type"] === "photo"): ?>

                <img
                    src="../<?= htmlspecialchars($media["file_path"]) ?>"
                    alt="<?= htmlspecialchars($media["file_name"]) ?>"
                    style="max-width: 500px;"
                >

            <?php elseif ($media["media_type"] === "video"): ?>

                <video
                    controls
                    style="max-width: 500px;"
                >
                    <source
                        src="../<?= htmlspecialchars($media["file_path"]) ?>"
                    >
                    Your browser does not support video playback.
                </video>

            <?php endif; ?>

            <p>
                <strong>Type:</strong>
                <?= htmlspecialchars($media["media_type"]) ?>
            </p>

            <p>
                <strong>Uploaded:</strong>
                <?= htmlspecialchars($media["uploaded_at"]) ?>
            </p>

        </article>

        <hr>

    <?php endwhile; ?>

<?php endif; ?>

</body>

</html>