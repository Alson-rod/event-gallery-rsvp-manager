<?php

require_once __DIR__ . "/../config/database.php";

$message = "";

$upload_directory = __DIR__ . "/../uploads/";

$allowed_types = [
    "image/jpeg" => "photo",
    "image/png" => "photo",
    "image/gif" => "photo",
    "image/webp" => "photo",
    "video/mp4" => "video",
    "video/webm" => "video",
    "video/quicktime" => "video"
];

$max_file_size = 10 * 1024 * 1024; // 10 MB

$events = $conn->query(
    "SELECT event_id, title, event_date
     FROM Events
     ORDER BY event_date ASC"
);

if (!$events) {
    die("Failed to load events: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_id = $_POST["event_id"] ?? "";

    if (!filter_var($event_id, FILTER_VALIDATE_INT)) {

        $message = "Please select a valid event.";

    } elseif (!isset($_FILES["media"])) {

        $message = "Please select a file.";

    } else {

        $file = $_FILES["media"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $message = "File upload failed.";

        } elseif ($file["size"] > $max_file_size) {

            $message = "File is too large. Maximum size is 10 MB.";

        } else {

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file["tmp_name"]);

            if (!isset($allowed_types[$mime_type])) {

                $message = "File type is not allowed.";

            } else {

                $media_type = $allowed_types[$mime_type];

                $original_name = basename($file["name"]);

                $extension = strtolower(
                    pathinfo($original_name, PATHINFO_EXTENSION)
                );

                $safe_name = bin2hex(random_bytes(16)) . "." . $extension;

                $destination = $upload_directory . $safe_name;

                if (!is_dir($upload_directory)) {

                    $message = "Upload directory does not exist.";

                } elseif (move_uploaded_file($file["tmp_name"], $destination)) {

                    $file_path = "uploads/" . $safe_name;
                    $file_size = $file["size"];

                    $stmt = $conn->prepare(
                        "INSERT INTO Media
                        (event_id, file_name, file_path, media_type, file_size)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "isssi",
                        $event_id,
                        $original_name,
                        $file_path,
                        $media_type,
                        $file_size
                    );

                    if ($stmt->execute()) {

                        $message = "Media uploaded successfully.";

                    } else {

                        unlink($destination);

                        $message = "File was uploaded, but the database record could not be created.";
                    }

                } else {

                    $message = "Failed to save uploaded file.";

                }
            }
        }
    }
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

    <title>Upload Media</title>

    <link
        rel="stylesheet"
        href="../public/style.css"
    >

</head>
<body>

<h1>Upload Photo / Video</h1>

<?php if ($message !== ""): ?>

    <p>
        <?= htmlspecialchars($message) ?>
    </p>

<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <p>
        <label>
            Event:

            <select name="event_id" required>

                <option value="">
                    Select an event
                </option>

                <?php while ($event = $events->fetch_assoc()): ?>

                    <option value="<?= $event["event_id"] ?>">

                        <?= htmlspecialchars($event["title"]) ?>
                        -
                        <?= htmlspecialchars($event["event_date"]) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </label>
    </p>

    <p>
        <label>
            Photo / Video:

            <input
                type="file"
                name="media"
                accept="image/*,video/*"
                required
            >

        </label>
    </p>

    <p>
        Maximum file size: 10 MB
    </p>

    <button type="submit">
        Upload Media
    </button>

</form>

<p>
    <a href="index.php">Back to Events</a>
</p>

</body>

</html>