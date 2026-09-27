<?php

require_once __DIR__ . "/../config/database.php";

$message = "";

$selected_event_id = $_GET["event_id"] ?? $_POST["event_id"] ?? "";

if (
    $selected_event_id !== "" &&
    !filter_var($selected_event_id, FILTER_VALIDATE_INT)
) {
    $selected_event_id = "";
}

$events = $conn->query(
    "SELECT event_id, title, event_date
     FROM Events
     WHERE status != 'cancelled'
     ORDER BY event_date ASC"
);

if (!$events) {
    die("Failed to load events: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $event_id = $_POST["event_id"] ?? "";

    if ($name === "" || $email === "" || $event_id === "") {

        $message = "Name, email, and event are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (!filter_var($event_id, FILTER_VALIDATE_INT)) {

        $message = "Invalid event selected.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO Attendees (name, email, phone)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $name,
            $email,
            $phone
        );

        if ($stmt->execute()) {

            $attendee_id = $stmt->insert_id;

            $rsvp = $conn->prepare(
                "INSERT INTO RSVPs (event_id, attendee_id, status)
                 VALUES (?, ?, 'pending')"
            );

            $rsvp->bind_param(
                "ii",
                $event_id,
                $attendee_id
            );

            if ($rsvp->execute()) {

                $message = "RSVP submitted successfully.";

                $selected_event_id = $event_id;

            } else {

                $message = "Attendee was registered, but the RSVP could not be created.";

            }

        } else {

            $message = "Failed to register attendee.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RSVP</title>
</head>

<body>

<h1>RSVP</h1>

<?php if ($message !== ""): ?>

    <p>
        <?= htmlspecialchars($message) ?>
    </p>

<?php endif; ?>

<form method="POST">

    <p>
        <label>
            Event:

            <select name="event_id" required>

                <option value="">
                    Select an event
                </option>

                <?php while ($event = $events->fetch_assoc()): ?>

                    <option
                        value="<?= $event["event_id"] ?>"
                        <?= (string)$selected_event_id === (string)$event["event_id"] ? "selected" : "" ?>
                    >

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
            Name:

            <input
                type="text"
                name="name"
                required
            >

        </label>
    </p>

    <p>
        <label>
            Email:

            <input
                type="email"
                name="email"
                required
            >

        </label>
    </p>

    <p>
        <label>
            Phone:

            <input
                type="text"
                name="phone"
            >

        </label>
    </p>

    <button type="submit">
        Submit RSVP
    </button>

</form>

<p>
    <a href="index.php">Back to Events</a>
</p>

</body>

</html>