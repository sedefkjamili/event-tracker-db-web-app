<?php
// Include the FPDF library
require('fpdf/fpdf.php');
require_once("fpdf/font/helveticab.php"); // Include FPDF library
require_once("connection.php");

// Retrieve form data and sanitize
$selectedEventID = isset($_POST['event']) ? intval($_POST['event']) : 0;
$fromDate = isset($_POST['fromDate']) ? $_POST['fromDate'] : '';
$toDate = isset($_POST['toDate']) ? $_POST['toDate'] : '';

// Validate input data (e.g., date format and event ID existence) here if needed

// Query to fetch event name based on the selected event ID
$eventQuery = "SELECT event_name FROM events WHERE id = ?";
$stmtEvent = mysqli_prepare($con, $eventQuery);
mysqli_stmt_bind_param($stmtEvent, "i", $selectedEventID);
mysqli_stmt_execute($stmtEvent);
$eventResult = mysqli_stmt_get_result($stmtEvent);
$eventRow = mysqli_fetch_assoc($eventResult);
$eventName = $eventRow['event_name'];

// Query to fetch dates for the selected event and date range
$datesQuery = "SELECT DISTINCT attendance_date
               FROM attendance
               WHERE event_id = ?
               AND attendance_date BETWEEN ? AND ?";
$stmtDates = mysqli_prepare($con, $datesQuery);
mysqli_stmt_bind_param($stmtDates, "iss", $selectedEventID, $fromDate, $toDate);
mysqli_stmt_execute($stmtDates);
$datesResult = mysqli_stmt_get_result($stmtDates);

// Create a PDF class that extends FPDF
class PDF extends FPDF {
    private $title;
    private $dates;

    // Constructor
    function __construct($title) {
        parent::__construct();
        $this->title = $title;
    }

    function setDates($dates) {
        $this->dates = $dates;
    }

    function Header() {
        // Header content (e.g., report title)
        if (!empty($this->title)) {
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, 'Attendance Report for ' . $this->title, 0, 1, 'C');
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
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial','I',12);
        // Print centered page number
        $this->Cell(0,10,'Sayfa '.$this->PageNo(),0,0,'C');
    }
}

// Create a PDF instance
$pdf = new PDF($eventName); // Pass the event name as a parameter
$pdf->AddPage('L');

// Set the title and dates for the report
$dates = [];
while ($row = mysqli_fetch_assoc($datesResult)) {
    $dates[] = $row['attendance_date'];
}
$pdf->setDates($dates);

// Query to fetch students for the selected event including "bursluluk numara"
$studentsQuery = "SELECT DISTINCT students.name, students.bursluluknumara
                  FROM students
                  INNER JOIN attendance ON students.id = attendance.student_id
                  WHERE attendance.event_id = ?
                  AND attendance_date BETWEEN ? AND ?";
$stmtStudents = mysqli_prepare($con, $studentsQuery);
mysqli_stmt_bind_param($stmtStudents, "iss", $selectedEventID, $fromDate, $toDate);
mysqli_stmt_execute($stmtStudents);
$studentsResult = mysqli_stmt_get_result($stmtStudents);

// Create an associative array to store student attendance data
$studentAttendanceData = [];

// Fetch and store student attendance data
while ($studentRow = mysqli_fetch_assoc($studentsResult)) {
    $studentName = $studentRow['name'];
    $burslulukNumara = $studentRow['bursluluknumara'];
    $studentAttendanceData[$studentName] = [
        'bursluluk_numara' => $burslulukNumara,
        'attendance' => [],
    ];
}

// Query to fetch attendance data for students in the selected date range
$attendanceQuery = "SELECT students.name, attendance.attendance_date, attendance.status
                   FROM students
                   LEFT JOIN attendance ON students.id = attendance.student_id
                   AND attendance.event_id = ?
                   AND attendance_date BETWEEN ? AND ?";
$stmtAttendance = mysqli_prepare($con, $attendanceQuery);
mysqli_stmt_bind_param($stmtAttendance, "iss", $selectedEventID, $fromDate, $toDate);
mysqli_stmt_execute($stmtAttendance);
$attendanceResult = mysqli_stmt_get_result($stmtAttendance);

// Initialize student attendance data for all dates
foreach ($dates as $date) {
    foreach ($studentAttendanceData as &$studentData) {
        $studentData['attendance'][$date] = '-';
    }
}

// Update attendance data for the dates when the student was present
while ($attendanceRow = mysqli_fetch_assoc($attendanceResult)) {
    $studentName = $attendanceRow['name'];
    $attendanceDate = $attendanceRow['attendance_date'];
    $status = $attendanceRow['status'];
    
    if (isset($studentAttendanceData[$studentName]['attendance'][$attendanceDate])) {
        // Update attendance status
        $studentAttendanceData[$studentName]['attendance'][$attendanceDate] = ($status === '1') ? '+' : '-';
    }
}

// Add table headers for "bursluluk numara" and student names
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'BURSLULUK NO', 1);
$pdf->Cell(40, 10, 'KATILIMCI', 1);

// Add dates as headers
$pdf->SetFont('Arial', 'B', 12);
foreach ($dates as $date) {
    $dayNumber = date('d', strtotime($date)); // Extract the day number from the date
    $monthNumber = date('m', strtotime($date)); // Extract the month number from the date
    $formattedDate = $dayNumber . '.' . $monthNumber; // Concatenate day and month
    $pdf->Cell(15, 10, $formattedDate, 1, 0, 'C'); // Write the formatted date in the cell
}
$pdf->Ln();

// Add student names, "bursluluk numara," and attendance data to the table
$pdf->SetFont('Arial', '', 12);
foreach ($studentAttendanceData as $studentName => $attendanceArray) {
    $pdf->Cell(40, 10, $attendanceArray['bursluluk_numara'], 1);
    $pdf->Cell(40, 10, $studentName, 1);
    foreach ($attendanceArray['attendance'] as $attendance) {
        $pdf->Cell(15, 10, $attendance, 1, 0, 'C');
    }
    $pdf->Ln();
}

// Output the PDF
$pdf->Output('report.pdf', 'D');

// Close the database connection
mysqli_close($con);
?>
