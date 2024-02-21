<!DOCTYPE html>
<html>
<head>
    <title>Search</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="stylesheet" type="text/css" href="dashboard1.css">
    <link rel="stylesheet" type="text/css" href="search.css">

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
            <h2 class="white-heading">Ara</h2>
            <div class="search-container">
                <form method="post">
                    <input type="text" name="bursluluknumara" placeholder="Bursluluk Numarasi">
                    <button class="search-button" onclick="toggleSearchContainer()">Ara</button>

                </form>
            </div>

            <div class="search-table">

            <?php
            // Include the database connection file
            require_once("connection.php");

            // Check if the form has been submitted
            if (isset($_POST['bursluluknumara'])) {
                $bursluluknumara = $_POST['bursluluknumara'];

                // Query to retrieve student's name and "bursluluknumara" based on the provided 'bursluluknumara'
                $sql_student = "SELECT name, bursluluknumara FROM students WHERE bursluluknumara = ?";
                
                $stmt_student = $con->prepare($sql_student);
                $stmt_student->bind_param("s", $bursluluknumara);
                $stmt_student->execute();
                $result_student = $stmt_student->get_result();

                if ($result_student->num_rows > 0) {
                    // Fetch student's data
                    $student_data = $result_student->fetch_assoc();
                    $student_name = $student_data['name'];
                    $student_bursluluknumara = $student_data['bursluluknumara'];

                    // Query to retrieve events associated with the student's 'bursluluknumara'
                    $sql_events = "SELECT events.event_name, events.teacher_name
                            FROM students
                            INNER JOIN events ON students.event_id = events.id
                            WHERE students.bursluluknumara = ?";

                    $stmt_events = $con->prepare($sql_events);
                    $stmt_events->bind_param("s", $bursluluknumara);
                    $stmt_events->execute();
                    $result_events = $stmt_events->get_result();
                    echo '<h2 class="custom-heading">Öğrencinin katıldığı etkinlikler:</h2>';
                    echo "<h3>$student_name - $student_bursluluknumara</h3>";
                    echo "<ul>";
                    while ($row = $result_events->fetch_assoc()) {
                        echo '<li class="custom-line">' . $row['event_name'] . ' (Teacher: ' . $row['teacher_name'] . ')</li>';
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Belirtilen burs numarasına sahip öğrenci bulunamadı.</p>";
                }

                $stmt_student->close();
            }
            ?>
            </div>
        </div>
    </div>

    <?php
    session_start();
    if (isset($_POST['logout'])) {
        session_destroy();
        header("location: login.php");
    }
    ?>
</body>
</html>

