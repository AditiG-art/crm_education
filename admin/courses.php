<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
header("Location:../login.php");
exit();
}


$result=mysqli_query($conn,"SELECT * FROM courses ORDER BY id DESC");


$count=mysqli_query($conn,"SELECT COUNT(*) as total FROM courses");

$total=mysqli_fetch_assoc($count)['total'];

?>


<!DOCTYPE html>
<html>

<head>
<title>Course Management | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Course Management";
include "../includes/topbar.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 mt-2">
    <div>
        <h4 class="mb-0"><i class="fa-solid fa-book text-primary"></i> Course Management</h4>
        <small class="text-muted">Manage academic courses, fees, and assigned faculty.</small>
    </div>
    <div class="crm-table-actions">
        <button class="btn-crm-export" onclick="exportTableToCSV('coursesTable', 'courses_catalog.csv')">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-crm-print" onclick="printPage()">
            <i class="fa-solid fa-print"></i> Print
        </button>
        <a href="add_course.php" class="btn btn-primary rounded-pill px-3">
            <i class="fa-solid fa-plus"></i> Add Course
        </a>
    </div>
</div>

<div class="alert alert-primary d-flex align-items-center gap-2 rounded-3 shadow-sm mb-4">
    <i class="fa-solid fa-graduation-cap fa-lg"></i>
    <span>Total Active Courses: <strong><?php echo $total; ?></strong></span>
</div>

<div class="table-box mt-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="coursesTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Duration</th>
                    <th>Tuition Fees</th>
                    <th>Assigned Faculty</th>
                    <th>Active Enrolments</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && mysqli_num_rows($result) > 0){ ?>
                    <?php while($row = mysqli_fetch_assoc($result)){ 
                        $cName = $row['course_name'];
                        $stuEnrolled = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM students WHERE course = '" . mysqli_real_escape_string($conn, $cName) . "'"))['cnt'] ?? 0;
                    ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['course_name']); ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['duration']); ?></span></td>
                            <td><strong class="text-success"><?php echo htmlspecialchars($row['fees']); ?></strong></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['teacher'] ?: 'Unassigned'); ?></span></td>
                            <td><span class="badge-crm-enrolled"><?php echo $stuEnrolled; ?> Students</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_course.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="delete_course.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this course?');" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No Courses Found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</div>

</body>
</html>