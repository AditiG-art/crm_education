<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || ($_SESSION['role'] != "admin" && $_SESSION['role'] != "teacher"))
{
    header("Location:../login.php");
    exit();
}

$sql = "SELECT attendance.*, students.full_name
        FROM attendance
        INNER JOIN students ON attendance.student_id = students.id
        ORDER BY attendance.attendance_date DESC";

$result = mysqli_query($conn,$sql);

$tot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance"))['t'] ?? 0;
$pres = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE status='Present'"))['t'] ?? 0;
$abs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE status='Absent'"))['t'] ?? 0;
$late = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE status='Late'"))['t'] ?? 0;
$rate = $tot > 0 ? round(($pres / $tot) * 100, 1) : 100;
?>

<!DOCTYPE html>
<html>
<head>
<title>Attendance Management | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Attendance Management";
include "../includes/topbar.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 mt-2">
    <div>
        <h4 class="mb-0"><i class="fa-solid fa-calendar-check text-primary"></i> Attendance Management</h4>
        <small class="text-muted">Track and mark student daily attendance records.</small>
    </div>
    <div class="crm-table-actions">
        <button class="btn-crm-export" onclick="exportTableToCSV('attendanceTable', 'attendance_report.csv')">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-crm-print" onclick="printPage()">
            <i class="fa-solid fa-print"></i> Print
        </button>
        <a href="mark_attendance.php" class="btn btn-primary rounded-pill px-3">
            <i class="fa-solid fa-plus"></i> Mark Attendance
        </a>
    </div>
</div>

<!-- Summary Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">TOTAL LOGS</small>
                    <h3 class="fw-bold mb-0 text-primary"><?php echo $tot; ?></h3>
                </div>
                <div class="bg-light p-3 rounded-circle text-primary"><i class="fa-solid fa-list-check fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">PRESENT</small>
                    <h3 class="fw-bold mb-0 text-success"><?php echo $pres; ?></h3>
                </div>
                <div class="bg-light p-3 rounded-circle text-success"><i class="fa-solid fa-user-check fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">ABSENT / LATE</small>
                    <h3 class="fw-bold mb-0 text-danger"><?php echo ($abs + $late); ?></h3>
                </div>
                <div class="bg-light p-3 rounded-circle text-danger"><i class="fa-solid fa-user-xmark fa-lg"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">ATTENDANCE RATE</small>
                    <h3 class="fw-bold mb-0 text-info"><?php echo $rate; ?>%</h3>
                </div>
                <div class="bg-light p-3 rounded-circle text-info"><i class="fa-solid fa-percent fa-lg"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="table-box">
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="attendanceTable">
            <thead class="table-dark">
                <tr>
                    <th>Student Name</th>
                    <th>Attendance Date</th>
                    <th>Attendance Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0){ ?>
                    <?php while($row = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
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
                        <td colspan="3" class="text-center text-muted py-4">No Attendance Records Found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</div>

</body>
</html>