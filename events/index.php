<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Search and Filter Values
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";
$event_date = $_GET["event_date"] ?? "";


/*
|--------------------------------------------------------------------------
| Build Events Query
|--------------------------------------------------------------------------
*/

$sql = "SELECT *
        FROM Events
        WHERE 1=1";

$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| Search by Event Title
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= " AND title LIKE ?";

    $params[] = "%" . $search . "%";
    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Filter by Status
|--------------------------------------------------------------------------
*/

$allowed_statuses = [
    "upcoming",
    "ongoing",
    "completed",
    "cancelled"
];

if (in_array($status, $allowed_statuses, true)) {

    $sql .= " AND status = ?";

    $params[] = $status;
    $types .= "s";

} else {

    $status = "";

}


/*
|--------------------------------------------------------------------------
| Filter by Date
|--------------------------------------------------------------------------
*/

if ($event_date !== "") {

    $sql .= " AND event_date = ?";

    $params[] = $event_date;
    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Sort Events
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY event_date ASC, event_time ASC";


/*
|--------------------------------------------------------------------------
| Prepare Query
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Failed to prepare events query: " . $conn->error);
}


/*
|--------------------------------------------------------------------------
| Bind Search/Filter Parameters
|--------------------------------------------------------------------------
*/

if (!empty($params)) {

    $stmt->bind_param($types, ...$params);

}


/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

if (!$stmt->execute()) {
    die("Failed to load events: " . $stmt->error);
}

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Events</title>

</head>

<body>

<h1>Events</h1>


<!--
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
-->

<p>

    <a href="create.php">
        Create Event
    </a>

    |

    <a href="dashboard.php">
        Dashboard
    </a>

    |

    <a href="rsvps.php">
        View RSVPs
    </a>

</p>


<hr>


<!--
|--------------------------------------------------------------------------
| Search and Filters
|--------------------------------------------------------------------------
-->

<h2>Search / Filter Events</h2>

<form method="GET">

    <p>

        <label>

            Search by title:

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Enter event title"
            >

        </label>

    </p>


    <p>

        <label>

            Status:

            <select name="status">

                <option value="">
                    All statuses
                </option>

                <option
                    value="upcoming"
                    <?= $status === "upcoming" ? "selected" : "" ?>
                >
                    Upcoming
                </option>

                <option
                    value="ongoing"
                    <?= $status === "ongoing" ? "selected" : "" ?>
                >
                    Ongoing
                </option>

                <option
                    value="completed"
                    <?= $status === "completed" ? "selected" : "" ?>
                >
                    Completed
                </option>

                <option
                    value="cancelled"
                    <?= $status === "cancelled" ? "selected" : "" ?>
                >
                    Cancelled
                </option>

            </select>

        </label>

    </p>


    <p>

        <label>

            Event date:

            <input
                type="date"
                name="event_date"
                value="<?= htmlspecialchars($event_date) ?>"
            >

        </label>

    </p>


    <button type="submit">
        Apply Filters
    </button>


    <a href="index.php">
        Reset
    </a>

</form>


<hr>


<!--
|--------------------------------------------------------------------------
| Event List
|--------------------------------------------------------------------------
-->

<h2>Events</h2>

<?php if ($result->num_rows === 0): ?>

    <p>
        No events found matching the selected filters.
    </p>

<?php else: ?>

    <?php while ($event = $result->fetch_assoc()): ?>

        <article>

            <h3>
                <?= htmlspecialchars($event["title"]) ?>
            </h3>


            <p>

                <strong>Date:</strong>

                <?= htmlspecialchars($event["event_date"]) ?>

            </p>


            <p>

                <strong>Time:</strong>

                <?= htmlspecialchars($event["event_time"] ?? "") ?>

            </p>


            <p>

                <strong>Location:</strong>

                <?= htmlspecialchars($event["location"] ?? "") ?>

            </p>


            <p>

                <strong>Status:</strong>

                <?= htmlspecialchars($event["status"]) ?>

            </p>


            <p>

                <?= nl2br(
                    htmlspecialchars($event["description"] ?? "")
                ) ?>

            </p>


            <p>

                <a href="edit.php?id=<?= $event["event_id"] ?>">
                    Edit Event
                </a>

                |

                <a href="delete.php?id=<?= $event["event_id"] ?>">
                    Delete Event
                </a>

                |

                <a href="rsvp.php?event_id=<?= $event["event_id"] ?>">
                    RSVP to Event
                </a>

                |

                <a href="gallery.php?event_id=<?= $event["event_id"] ?>">
                    View Gallery
                </a>

            </p>

        </article>

        <hr>

    <?php endwhile; ?>

<?php endif; ?>


</body>

</html>