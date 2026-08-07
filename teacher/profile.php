<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "teacher") {
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email'");
$teacher = mysqli_fetch_assoc($query);

if(!$teacher) {
    $name_esc = mysqli_real_escape_string($conn, $_SESSION['user'] ?? 'Teacher');
    $email_esc = mysqli_real_escape_string($conn, $email);
    mysqli_query($conn, "INSERT INTO teachers (full_name, email, phone, subject, qualification) VALUES ('$name_esc', '$email_esc', '', '', '')");
    $query = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email'");
    $teacher = mysqli_fetch_assoc($query);
}

$attendanceMsg = "";
$attendanceErr = "";
$selectedStudentId = 0;
$selectedDate = date('Y-m-d');
$selectedStatus = 'Present';

// Handle Attendance Submission directly in Teacher Profile
if($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['mark_attendance']) || isset($_POST['student_id']))) {
    $student_id      = intval($_POST['student_id'] ?? 0);
    $attendance_date = mysqli_real_escape_string($conn, trim($_POST['attendance_date'] ?? ''));
    $status          = mysqli_real_escape_string($conn, trim($_POST['status'] ?? ''));

    $selectedStudentId = $student_id;
    $selectedDate      = $attendance_date ?: date('Y-m-d');
    $selectedStatus    = $status ?: 'Present';

    if($student_id > 0 && !empty($attendance_date) && !empty($status)) {
        // Check if attendance record already exists for student on this date
        $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id='$student_id' AND attendance_date='$attendance_date'");

        if($check && mysqli_num_rows($check) > 0) {
            $upd = mysqli_query($conn, "UPDATE attendance SET status='$status' WHERE student_id='$student_id' AND attendance_date='$attendance_date'");
            if($upd) {
                $attendanceMsg = "Attendance updated successfully as {$status}!";
            } else {
                $attendanceErr = "Failed to update attendance: " . mysqli_error($conn);
            }
        } else {
            $ins = mysqli_query($conn, "INSERT INTO attendance (student_id, attendance_date, status) VALUES ('$student_id', '$attendance_date', '$status')");
            if($ins) {
                $attendanceMsg = "Attendance marked successfully as {$status}!";
            } else {
                $attendanceErr = "Failed to mark attendance: " . mysqli_error($conn);
            }
        }
    } else {
        $attendanceErr = "Please select a student, date, and status.";
    }
}

// Fetch all students for dropdown
$studentsResult = $conn->query("SELECT id, full_name, email, course FROM students ORDER BY full_name ASC");

// Fetch recent attendance logs
$recentLogs = $conn->query("SELECT a.*, s.full_name, s.course FROM attendance a JOIN students s ON a.student_id = s.id ORDER BY a.attendance_date DESC, a.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Profile | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<style>
.profile-card{ background:white; padding:35px; border-radius:25px; box-shadow:0 10px 25px rgba(0,0,0,.08); }
.profile-header{ display:flex; align-items:center; gap:25px; margin-bottom:30px; }
.profile-icon{ height:100px; width:100px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#6f42c1); display:flex; align-items:center; justify-content:center; color:white; font-size:45px; }
.info-box{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
.info-item{ background:#f8fafc; padding:18px; border-radius:15px; }
.info-item i{ color:#2563eb; margin-right:10px; }

.attendance-card {
    background: white;
    padding: 30px;
    border-radius: 25px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
    margin-top: 30px;
}
.attendance-card h4 {
    font-weight: 700;
    margin-bottom: 20px;
    color: #0f172a;
}
.form-label {
    font-weight: 600;
    color: #334155;
}
.form-control, .form-select {
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
}
.btn-save-attendance {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 600;
    border: none;
    transition: 0.3s;
}
.btn-save-attendance:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-2px);
}
@media(max-width:700px){ .info-box{ grid-template-columns:1fr; } }
</style>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Teacher Profile"; include "../includes/topbar.php"; ?>

<div class="profile-card mt-4">
    <div class="profile-header">
        <div class="profile-icon">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <h2><?= htmlspecialchars($teacher['full_name']) ?></h2>
            <p class="text-muted mb-0">Teacher Profile</p>
        </div>
    </div>

    <div class="info-box">
        <div class="info-item">
            <i class="fa-solid fa-envelope"></i> <strong>Email:</strong><br>
            <?= htmlspecialchars($teacher['email']) ?>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-phone"></i> <strong>Phone:</strong><br>
            <?= !empty($teacher['phone']) ? htmlspecialchars($teacher['phone']) : '<span class="text-muted">Not Provided</span>' ?>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-book"></i> <strong>Subject:</strong><br>
            <?= !empty($teacher['subject']) ? htmlspecialchars($teacher['subject']) : '<span class="text-muted">Not Assigned</span>' ?>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-graduation-cap"></i> <strong>Qualification:</strong><br>
            <?= !empty($teacher['qualification']) ? htmlspecialchars($teacher['qualification']) : '<span class="text-muted">Not Provided</span>' ?>
        </div>
    </div>
</div>

<!-- MARK STUDENT ATTENDANCE SECTION -->
<div class="attendance-card" id="attendanceSection">
    <h4><i class="fa-solid fa-calendar-check text-primary me-2"></i> Mark Student Attendance</h4>
    <p class="text-muted small mb-4">Record or update student daily attendance directly from your profile.</p>

    <?php if(!empty($attendanceMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($attendanceMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(!empty($attendanceErr)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($attendanceErr) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="mark_attendance" value="1">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Select Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Choose Student --</option>
                    <?php if($studentsResult && $studentsResult->num_rows > 0): ?>
                        <?php while($s = $studentsResult->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>" <?= $selectedStudentId == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['course'] ?: 'General') ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>No students found in system</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Attendance Date</label>
                <input type="date" name="attendance_date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="Present" <?= $selectedStatus === 'Present' ? 'selected' : '' ?>>Present</option>
                    <option value="Absent" <?= $selectedStatus === 'Absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="Late" <?= $selectedStatus === 'Late' ? 'selected' : '' ?>>Late</option>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn-save-attendance">
                <i class="fa-solid fa-check-circle me-1"></i> Save Attendance
            </button>
        </div>
    </form>

    <?php if($recentLogs && $recentLogs->num_rows > 0): ?>
    <hr class="my-4">
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recent Attendance Records</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($log = $recentLogs->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($log['full_name']) ?></strong></td>
                    <td><?= htmlspecialchars($log['course'] ?: 'General') ?></td>
                    <td><?= date('M d, Y', strtotime($log['attendance_date'])) ?></td>
                    <td>
                        <?php if($log['status'] === 'Present'): ?>
                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Present</span>
                        <?php elseif($log['status'] === 'Absent'): ?>
                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Absent</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Late</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

</div>
</body>
</html>
