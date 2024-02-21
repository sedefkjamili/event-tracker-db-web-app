<?php
session_start(); // Initialize the session

// Include the database connection file
require_once("connection.php");

// Handle form submission for attendance
if (isset($_POST['submit'])) {
    $eventID = $_POST['event'];
    $date = $_POST['date'];

    // Check if the 'attendance' array is set and not empty
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        // Loop through the list of students for the selected event
        foreach ($_POST['attendance'] as $studentID => $status) {
            // Ensure the status is either '1' (present) or '0' (absent)
            $status = ($status === '1') ? '1' : '0';

            // Check if a record already exists for this event and student
            $existingRecordQuery = "SELECT id FROM Attendance WHERE event_id = ? AND student_id = ? AND attendance_date = ?";
            $stmt = $con->prepare($existingRecordQuery);
            $stmt->bind_param("iis", $eventID, $studentID, $date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the existing record
                $updateQuery = "UPDATE Attendance SET status = ? WHERE event_id = ? AND student_id = ? AND attendance_date = ?";
                $stmt = $con->prepare($updateQuery);
                $stmt->bind_param("iiis", $status, $eventID, $studentID, $date);
                $stmt->execute();
            } else {
                // Insert a new record
                $insertQuery = "INSERT INTO Attendance (event_id, student_id, attendance_date, status)
                                VALUES (?, ?, ?, ?)";
                $stmt = $con->prepare($insertQuery);
                $stmt->bind_param("iisi", $eventID, $studentID, $date, $status);
                $stmt->execute();
            }
        }
    }
}

// Check for logout and perform the action before any HTML output
if (isset($_POST['logout'])) {
    session_destroy();
    header("location: login.php");
    exit; // Add exit to terminate script execution after the redirect
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="dashboard1.css">
    <link rel="stylesheet" type="text/css" href="atttendance.css">
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
        <h2 class="white-heading">Yoklama</h2>

        <div class="attendance-container">
            <h1>Yoklama Al</h1>
            <form method="post">
                <br>
                <label for="event">Etkinlik Seç:</label>
                <select name="event" id="event">
                    <?php
                    // Query to fetch events from the 'events' table
                    $eventQuery = "SELECT id, event_name FROM events";
                    $eventResult = mysqli_query($con, $eventQuery);

                    // Loop through the events and populate the dropdown options
                    while ($row = mysqli_fetch_assoc($eventResult)) {
                        echo '<option value="' . $row['id'] . '">' . $row['event_name'] . '</option>';
                    }
                    ?>
                </select><br>

                <label for="date">Tarih:</label>
                <input type="date" name="date" id="date"><br>

                <!-- Students for the selected event will be dynamically loaded here -->
                <div id="students-container">
                    <!-- Example checkbox for student attendance -->
                    <?php
                    // You can load the students dynamically using AJAX as shown earlier
                    // For now, here's a static example
                    $studentQuery = "SELECT id FROM students";
                    $studentResult = mysqli_query($con, $studentQuery);

                    while ($studentRow = mysqli_fetch_assoc($studentResult)) {
                        echo '<input type="checkbox" name="attendance[' . $studentRow['id'] . ']" value="1"> Student ' . $studentRow['id'] . '<br>';
                        echo '<input type="checkbox" name="attendance[' . $studentRow['id'] . ']" value="0"> Absent Student ' . $studentRow['id'] . '<br>';
                    }
                    ?>
                </div>
                <br>
                <input type="submit" name="submit" value="GÖNDER">
            </form>
        </div>

        <!-- Separate container for the "Generate PDF Report" form -->
        <div class="report-container">
            <h1>PDF Raporu Oluştur</h1>
            <form method="post" action="generate_report.php" target="_blank">
                <br>
                <label for="event">Etkinlik Seç:</label>
                <select name="event" id="event">
                    <?php
                    // Query to fetch events from the 'events' table
                    $eventQuery = "SELECT id, event_name FROM events";
                    $eventResult = mysqli_query($con, $eventQuery);

                    // Loop through the events and populate the dropdown options
                    while ($row = mysqli_fetch_assoc($eventResult)) {
                        echo '<option value="' . $row['id'] . '">' . $row['event_name'] . '</option>';
                    }
                    ?>
                </select><br>

                <label for="date">Tarih:</label>
                <input type="date" name="fromDate" id="fromdate">
                <input type="date" name="toDate" id="todate"><br>


                <!-- Add a submit button to generate the PDF report -->
                <input type="submit" name="generate_report" value="RAPOR OLUŞTUR">
            </form>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // Function to load students based on the selected event
            function loadStudents() {
                var eventId = $('#event').val();
                $.ajax({
                    url: 'load_students.php', // Create a separate PHP file to handle this AJAX request
                    type: 'POST',
                    data: { event_id: eventId },
                    success: function (data) {
                        $('#students-container').html(data);
                    }
                });
            }

            // Call the function when the event dropdown changes
            $('#event').on('change', function () {
                loadStudents();
            });

            // Initial load when the page loads
            loadStudents();
        </script>
        <?php
        if(isset($_POST['logout'])){
            session_destroy();
            header("location: login.php");
        }
        ?>
        </div>
    </div>
</body>
</html>
