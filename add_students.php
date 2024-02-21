<?php
// Include the database connection file
require_once("connection.php");

// Initialize variables
$eventName = "";
$teacherName = "";

// Check if the event ID is present in the URL
if (isset($_GET['id'])) {
    $eventId = $_GET['id'];

    // Check if the form has been submitted to add a student
    if (isset($_POST['studentName']) && isset($_POST['studentEmail']) && isset($_POST['bursNo'])) {
        $studentName = $_POST['studentName'];
        $studentEmail = $_POST['studentEmail'];
        $bursNo = $_POST['bursNo'];

        // Insert the student data into the database and associate it with the event
        $sql = "INSERT INTO students (name, bursluluknumara, country, event_id) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssi", $studentName, $bursNo, $studentEmail, $eventId);

        if ($stmt->execute()) {
            // Data was successfully inserted
            // You can also add a success message here
        } else {
            // Error handling for database insertion
            // You can add an error message here
        }

        $stmt->close();
    }

    // Retrieve event details from the database
    $sql = "SELECT event_name, teacher_name FROM events WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $stmt->bind_result($eventName, $teacherName);

    if (!$stmt->fetch()) {
        // Event not found
        // Handle this case accordingly
        header("Location: events.php"); // Redirect to events page
        exit();
    }

    $stmt->close();

    // Retrieve students related to the same event ID
    $sql = "SELECT id, name, bursluluknumara, country, event_id FROM students WHERE event_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create an array to store the students
    $students = array();

    while ($row = $result->fetch_assoc()) {
        // Add each student to the array
        $students[] = $row;
    }

    $stmt->close();
} else {
    // Event ID not present in the URL
    // Handle this case accordingly, e.g., redirect to the events page
    header("Location: events.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Students</title>
    <link rel="stylesheet" type="text/css" href="dashboard1.css">
    <link rel="stylesheet" type="text/css" href="add_students.css">
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
            <h2 class="white-heading">Katılımcı Ekle</h2>
            <div class="student_container">
                <h2><?= htmlspecialchars($eventName) ?></h2>
                <form method="post">
                    <br>
                    <input type="text" id="studentName" name="studentName" placeholder="Ad Soyad">
                    <input type="text" id="bursNo" name="bursNo" placeholder="Bursluluk Numarasi">
                    <input type="text" id="studentEmail" name="studentEmail" placeholder="Ülke"><br>
                    <input type="submit" value="KATILIMCI EKLE">
                    <a href="events.php" class="aa">GERİ</a>
                </form>
            </div> 

            <!-- Display the table for students -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>Bursluluk Numarasi</th>
                            <th>Ülke</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- PHP code to populate the table with students -->
                        <?php
                        foreach ($students as $student) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($student['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($student['bursluluknumara']) . "</td>";
                            echo "<td>" . htmlspecialchars($student['country']) . "</td>";
                            echo "<td><a href='deletestudents.php?id=" . urlencode($student['id']) . "&event_id=" . urlencode($eventId) . "'>Sil</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>   
    </div>
</body>
</html>
