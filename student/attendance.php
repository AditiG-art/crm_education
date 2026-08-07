<?php

session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role']!="student")
{
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];

// Get student details
$studentQuery = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");
$student = mysqli_fetch_assoc($studentQuery);

if(!$student)
{
    die("Student profile not found");
}

$student_id = $student['id'];

// Attendance Records
$attendanceQuery = mysqli_query(
    $conn,
    "SELECT * FROM attendance 
    WHERE student_id='$student_id'
    ORDER BY attendance_date DESC"
);

// Attendance Percentage
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM attendance WHERE student_id='$student_id'");
$total = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

$presentQuery = mysqli_query($conn, "SELECT COUNT(*) AS present FROM attendance WHERE student_id='$student_id' AND status='Present'");
$present = mysqli_fetch_assoc($presentQuery)['present'] ?? 0;

$percentage = $total > 0 ? round(($present / $total) * 100) : 100;

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Attendance | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "My Attendance";
include "../includes/topbar.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 mt-2">
    <div>
        <h4 class="mb-0"><i class="fa-solid fa-calendar-check text-primary"></i> Attendance Summary</h4>
        <small class="text-muted">Review your class attendance records and history.</small>
    </div>
    <div class="crm-table-actions">
        <button class="btn-crm-export" onclick="exportTableToCSV('studentAttendanceTable', 'my_attendance_history.csv')">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-crm-print" onclick="printPage()">
            <i class="fa-solid fa-print"></i> Print
        </button>
    </div>
</div>

<!-- Attendance Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">OVERALL PERCENTAGE</small>
                    <h2 class="fw-bold mb-0 text-primary"><?php echo $percentage; ?>%</h2>
                </div>
                <div class="bg-light p-3 rounded-circle text-primary"><i class="fa-solid fa-percent fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">CLASSES ATTENDED</small>
                    <h2 class="fw-bold mb-0 text-success"><?php echo $present; ?> / <?php echo $total; ?></h2>
                </div>
                <div class="bg-light p-3 rounded-circle text-success"><i class="fa-solid fa-user-check fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">ATTENDANCE STATUS</small>
                    <h4 class="fw-bold mb-0 mt-1">
                        <?php if($percentage >= 75): ?>
                            <span class="badge-crm-passed">Good Standing</span>
                        <?php else: ?>
                            <span class="badge-crm-failed">Low Attendance</span>
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="bg-light p-3 rounded-circle text-info"><i class="fa-solid fa-shield-halved fa-lg"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="table-box">
    <h5 class="mb-3">Attendance History Logs</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="studentAttendanceTable">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($attendanceQuery && mysqli_num_rows($attendanceQuery) > 0){ ?>
                    <?php while($row = mysqli_fetch_assoc($attendanceQuery)){ ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['attendance_date']); ?></strong></td>
                            <td>
                                <?php 
                                if($row['status'] == "Present") {
                                    echo "<span class='badge-crm-passed'>Present</span>";
                                } elseif($row['status'] == "Late") {
                                    echo "<span class='badge-crm-lead'>Late</span>";
                                } else {
                                    echo "<span class='badge-crm-failed'>Absent</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">No Attendance History Recorded Yet</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</div>

</body>
</html>