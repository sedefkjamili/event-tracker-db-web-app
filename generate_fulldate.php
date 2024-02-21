<?php
// Include the FPDF library
require('fpdf/fpdf.php');
require_once("fpdf/font/helveticab.php"); // Include FPDF library
require_once("connection.php");

// Retrieve form data
$selectedEventID = $_POST['event'];
$fromDate = $_POST['fromDate'];
$toDate = $_POST['toDate'];

// Query to fetch event name based on the selected event ID
$eventQuery = "SELECT event_name FROM events WHERE id = $selectedEventID";
$eventResult = mysqli_query($con, $eventQuery);
$eventRow = mysqli_fetch_assoc($eventResult);
$eventName = $eventRow['event_name'];

// Query to fetch dates for the selected event and date range
$datesQuery = "SELECT DISTINCT attendance_date
               FROM attendance
               WHERE event_id = $selectedEventID
               AND attendance_date BETWEEN '$fromDate' AND '$toDate'";
$datesResult = mysqli_query($con, $datesQuery);

// Create a PDF class that extends FPDF
class PDF extends FPDF {
    private $title;
    private $dates;

    function setTitle($title, $isUTF8 = false) {
        $this->title = $title;
    }

    function setDates($dates) {
        $this->dates = $dates;
    }

    function Header() {
        // Header content (e.g., report title)
        if (!empty($this->title)) {
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, $this->title, 0, 1, 'C');
        }
        if (!empty($this->dates)) {
            // Add dates as headers
            $this->SetFont('Arial', 'B', 12);
            foreach ($this->dates as $date) {
                $this->Cell(30, 10, $date, 1);
            }
            $this->Ln();
        }
    }

    function Footer() {
        // Footer content (e.g., page number)
    }
}

// Create a PDF instance
$pdf = new PDF('L');
$pdf->AddPage();

// Set the title and dates for the report
$pdf->setTitle('Attendance Report for ' . $eventName);
$dates = [];
while ($row = mysqli_fetch_assoc($datesResult)) {
    $dates[] = $row['attendance_date'];
}
$pdf->setDates($dates);

// Fetch students for the selected event
$studentsQuery = "SELECT DISTINCT students.name
                  FROM students
                  INNER JOIN attendance ON students.id = attendance.student_id
                  WHERE attendance.event_id = $selectedEventID
                  AND attendance_date BETWEEN '$fromDate' AND '$toDate'";
$studentsResult = mysqli_query($con, $studentsQuery);

// Create an associative array to store student attendance data
$studentAttendanceData = [];

// Fetch and store student attendance data
while ($studentRow = mysqli_fetch_assoc($studentsResult)) {
    $studentName = $studentRow['name'];
    $studentAttendanceData[$studentName] = [];

    // Query to fetch attendance data for the student
    $studentAttendanceQuery = "SELECT attendance_date, status
                               FROM attendance
                               WHERE student_id = (SELECT id FROM students WHERE name = '$studentName')
                               AND event_id = $selectedEventID
                               AND attendance_date BETWEEN '$fromDate' AND '$toDate'";
    $studentAttendanceResult = mysqli_query($con, $studentAttendanceQuery);

    // Initialize student attendance data for all dates
    foreach ($dates as $date) {
        $studentAttendanceData[$studentName][$date] = '0'; // Initialize with '0' for absent
    }

    // Update attendance data for the dates when the student was present
    while ($attendanceRow = mysqli_fetch_assoc($studentAttendanceResult)) {
        $attendanceDate = $attendanceRow['attendance_date'];
        $pdf->SetFillColor(0, 255, 0); // Set green background for '1'
        $studentAttendanceData[$studentName][$attendanceDate] = '1'; // Set '1' for present
    
    }
}

// Add table headers for student names
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'KATILIMCI', 1);

// Add dates as headers
$pdf->SetFont('Arial', 'B', 12);
foreach ($dates as $date) {
    $pdf->Cell(30, 10, $date, 1);
}
$pdf->Ln();

// Add student names and attendance data to the table
$pdf->SetFont('Arial', '', 12);
foreach ($studentAttendanceData as $studentName => $attendanceArray) {
    $pdf->Cell(40, 10, $studentName, 1);
    foreach ($attendanceArray as $attendance) {
        $pdf->Cell(30, 10, $attendance, 1);
    }
    $pdf->Ln();
}

// Output the PDF
$pdf->Output('report.pdf', 'D');

// Close the database connection
mysqli_close($con);
?>
