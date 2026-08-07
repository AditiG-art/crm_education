<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || ($_SESSION['role'] != "admin" && $_SESSION['role'] != "teacher"))
{
    header("Location:../login.php");
    exit();
}

$message = "";
$error = "";

if(isset($_POST['save_attendance']) || (isset($_POST['student_id']) && isset($_POST['status'])))
{
    $student_id = intval($_POST['student_id'] ?? 0);
    $attendance_date = mysqli_real_escape_string($conn, trim($_POST['attendance_date'] ?? ''));
    $status = mysqli_real_escape_string($conn, trim($_POST['status'] ?? ''));

    if($student_id > 0 && !empty($attendance_date) && !empty($status))
    {
        // Check if attendance record already exists for student on this date
        $check = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id='$student_id' AND attendance_date='$attendance_date'");

        if($check && mysqli_num_rows($check) > 0)
        {
            $upd = mysqli_query($conn, "UPDATE attendance SET status='$status' WHERE student_id='$student_id' AND attendance_date='$attendance_date'");
            if($upd)
            {
                $message = "Attendance record updated successfully as {$status}!";
            }
            else
            {
                $error = "Failed to update attendance record: " . mysqli_error($conn);
            }
        }
        else
        {
            $ins = mysqli_query($conn, "INSERT INTO attendance (student_id, attendance_date, status) VALUES ('$student_id', '$attendance_date', '$status')");
            if($ins)
            {
                $message = "Attendance marked successfully as {$status}!";
            }
            else
            {
                $error = "Failed to mark attendance: " . mysqli_error($conn);
            }
        }
    }
    else
    {
        $error = "Please fill all required fields.";
    }
}

// Fetch all students for dropdown
$studentsResult = $conn->query("SELECT id, full_name, email, course FROM students ORDER BY full_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Mark Attendance | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.form-card{
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}
label{ font-weight:600; margin-bottom:8px; }
.form-control, .form-select{ padding:13px; border-radius:12px; }
</style>
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Mark Attendance";
include "../includes/topbar.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-2">
    <div>
        <h4 class="mb-0"><i class="fa-solid fa-calendar-check"></i> Mark Student Attendance</h4>
        <small class="text-muted">Record student presence or absence for classes.</small>
    </div>
    <a href="attendance.php" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Attendance
    </a>
</div>

<?php if(!empty($message)){ ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php } ?>

<?php if(!empty($error)){ ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php } ?>

<div class="panel mt-3">
    <div class="form-card">
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Select Student</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">-- Select Student --</option>
                        <?php if($studentsResult){ ?>
                            <?php while($s = $studentsResult->fetch_assoc()){ ?>
                                <option value="<?php echo $s['id']; ?>">
                                    <?php echo htmlspecialchars($s['full_name']); ?> (<?php echo htmlspecialchars($s['course'] ?? 'N/A'); ?>)
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Attendance Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="save_attendance" class="btn btn-primary px-4 py-2 mt-2">
                <i class="fa-solid fa-save"></i> Save Attendance
            </button>
        </form>
    </div>
</div>

</div>

</body>
</html>
