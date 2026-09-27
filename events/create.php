<?php

require_once __DIR__ . "/../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $event_time = $_POST["event_time"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $status = $_POST["status"] ?? "upcoming";

    if ($title === "" || $event_date === "") {
        $message = "Title and event date are required.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO Events
            (title, description, event_date, event_time, location, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $title,
            $description,
            $event_date,
            $event_time,
            $location,
            $status
        );

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        }

        $message = "Failed to create event.";
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

    <title>Create Event</title>

    <link
        rel="stylesheet"
        href="../public/style.css"
    >

</head>
<body>

<h1>Create Event</h1>

<form method="POST">

    <p>
        <label>
            Title:
            <input type="text" name="title" required>
        </label>
    </p>

    <p>
        <label>
            Description:
            <textarea name="description"></textarea>
        </label>
    </p>

    <p>
        <label>
            Event Date:
            <input type="date" name="event_date" required>
        </label>
    </p>

    <p>
        <label>
            Event Time:
            <input type="time" name="event_time">
        </label>
    </p>

    <p>
        <label>
            Location:
            <input type="text" name="location">
        </label>
    </p>

    <p>
        <label>
            Status:
            <select name="status">
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </label>
    </p>

    <button type="submit">Create Event</button>

</form>

<p><a href="index.php">Back to Events</a></p>

</body>
</html>
