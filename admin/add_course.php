<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "admin") {
    header("Location:../login.php");
    exit();
}

$error = "";

// Fetch available colleges
$availableColleges = [];
$clgRes = mysqli_query($conn, "SELECT id, college_name, college_code FROM colleges ORDER BY id ASC");
if($clgRes) {
    while($clg = mysqli_fetch_assoc($clgRes)) {
        $availableColleges[] = $clg;
    }
}

if(isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name'] ?? '');
    $duration    = trim($_POST['duration'] ?? '');
    $fees        = trim($_POST['fees'] ?? '');
    $teacher     = trim($_POST['teacher'] ?? '');
    $collegeId   = (int)($_POST['college_id'] ?? ($_SESSION['college_id'] ?? 1));

    // Resolve College Name
    $collegeName = "Smart Campus Main Institute";
    foreach($availableColleges as $cObj) {
        if((int)$cObj['id'] === $collegeId) {
            $collegeName = $cObj['college_name'];
            break;
        }
    }

    if(empty($course_name)) {
        $error = "Course Name is required.";
    } else {
        $sql = "INSERT INTO courses (college_id, college_name, course_name, duration, fees, teacher) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $collegeId, $collegeName, $course_name, $duration, $fees, $teacher);

        if($stmt->execute()) {
            echo "<script>alert('Course Added Successfully at " . json_encode($collegeName) . "!'); window.location='courses.php';</script>";
            exit();
        } else {
            $error = "Error adding course: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Course | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.form-card {
    background: white;
    padding: 36px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
label { font-weight: 600; font-size: 14px; margin-bottom: 6px; }
.form-control, .form-select { padding: 12px 16px; border-radius: 12px; border: 1px solid #CBD5E1; }
.form-control:focus, .form-select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
</style>
</head>

<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php
$pageTitle = "Add New Course";
include "../includes/topbar.php";
?>

<div class="page-header mb-4">
    <h1><i class="fa-solid fa-book-open text-primary"></i> Add Academic Course</h1>
    <p>Create a course and assign it to a university campus</p>
</div>

<?php if(!empty($error)): ?>
    <div class="alert alert-danger rounded-3 mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card col-lg-8">
    <form method="POST" action="add_course.php">
        <div class="row g-3">

            <div class="col-md-12">
                <label><i class="fa-solid fa-building-columns text-primary me-1"></i> College / Institution Campus *</label>
                <select name="college_id" class="form-select" required>
                    <?php foreach($availableColleges as $clg): ?>
                        <option value="<?= $clg['id'] ?>" <?= (int)($_SESSION['college_id'] ?? 1) === (int)$clg['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($clg['college_name']) ?> (<?= htmlspecialchars($clg['college_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label><i class="fa-solid fa-graduation-cap text-primary me-1"></i> Course Name *</label>
                <input type="text" name="course_name" class="form-control" placeholder="e.g. B.Tech Computer Science & Engineering" value="<?= htmlspecialchars($_POST['course_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-clock text-primary me-1"></i> Duration</label>
                <input type="text" name="duration" class="form-control" placeholder="e.g. 4 Years (8 Semesters)" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-indian-rupee-sign text-primary me-1"></i> Annual Tuition Fees</label>
                <input type="text" name="fees" class="form-control" placeholder="e.g. $12,500 / year" value="<?= htmlspecialchars($_POST['fees'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <label><i class="fa-solid fa-chalkboard-user text-primary me-1"></i> Assigned Lead Instructor</label>
                <input type="text" name="teacher" class="form-control" placeholder="e.g. Dr. Sarah Jenkins" value="<?= htmlspecialchars($_POST['teacher'] ?? '') ?>">
            </div>

        </div>

        <div class="mt-4 d-flex gap-3">
            <button type="submit" name="add_course" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="fa-solid fa-plus me-1"></i> Add Course
            </button>
            <a href="courses.php" class="btn btn-light border px-4 py-2 rounded-pill">Cancel</a>
        </div>
    </form>
</div>

</div>
</body>
</html>