<?php
// Include the database connection file
require_once("connection.php");

// Check if the form has been submitted and insert data into the database
if (isset($_POST['eventName']) && isset($_POST['teacherName'])) {
    $eventName = $_POST['eventName'];
    $teacherName = $_POST['teacherName'];

    // Insert the data into the database
    $sql = "INSERT INTO events (event_name, teacher_name) VALUES (?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $eventName, $teacherName);

    if ($stmt->execute()) {
        // Data was successfully inserted
        // You can also add a success message here
    } else {
        // Error handling for database insertion
        // You can add an error message here
    }

    $stmt->close();
}

// Retrieve events from the database (including the newly added ones)
$sql = "SELECT id, event_name, teacher_name FROM events";
$result = $con->query($sql);

// Create an array to store the events
$events = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add each event to the array
        $events[] = $row;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Events</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="eventts.css">
    <link rel="stylesheet" type="text/css" href="dashboard1.css">

</head>
<body>
<div class="dashboard">
        <div class="sidebar">
            <div class="logos">
                <img src="trackingimage.png" alt="Logo" class="logo3">
            </div>

            <h2></h2>
            <a href="dashboard.php">Gösterge Paneli</a>
            <a href="events.php">Etkinlikler</a>
            <a href="attendance.php">Yoklama</a>
            <a href="search.php">Ara</a><br>
            <form method="POST">
                <button type="submit" name="logout">ÇIKIŞ YAP</button>
            </form>
            
        </div>
        <div class="content">
        <h2 class="white-heading">Etkinlikler</h2>
        <div class="addcard">
            <form method="post">
                <input type="text" id="eventName" name="eventName" placeholder="Etkinlik İsmi">
                <input type="text" id="teacherName" name="teacherName" placeholder="Yetkili"><br>
                <input type="submit" value="Ekle">
            </form>
        </div>

        <!-- Display the table for events -->
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Etkinlik</th>
                    <th>Yetkili</th>
                    <th>Ekle</th>
                    <th>Sil</th>
                </tr>
            </thead>
            <tbody>
                <!-- PHP code to populate the table with events -->
                <?php
                    foreach ($events as $event) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($event['event_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($event['teacher_name']) . "</td>";
                        echo "<td><a href='add_students.php?id=" . urlencode($event['id']) . "'>Katılımcı Ekle</a></td>";
                        echo "<td><a href='delete.php?id=" . urlencode($event['id']) . "'>Sil</a></td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php
    if(isset($_POST['logout'])){
        session_destroy();
        header("location: login.php");
    }
    ?>
</body>
</html>