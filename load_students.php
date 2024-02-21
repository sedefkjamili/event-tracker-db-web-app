<?php
// Include the database connection file
require_once("connection.php");

if (isset($_POST['event_id'])) {
    $eventId = $_POST['event_id'];

    // Query to fetch students for the selected event
    $studentQuery = "SELECT id, name FROM students WHERE event_id = ?";
    $stmt = $con->prepare($studentQuery);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display students as checkboxes
    while ($row = $result->fetch_assoc()) {
        echo '<input type="checkbox" name="attendance[' . $row['id'] . ']" value="1"> ' . $row['name'] . '<br>';
    }
}
?>
