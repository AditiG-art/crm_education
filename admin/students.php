<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || ($_SESSION['role'] != "admin" && $_SESSION['role'] != "teacher"))
{
    header("Location:../login.php");
    exit();
}


// ==========================
// SEARCH FILTERS
// ==========================

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$course = isset($_GET['course']) ? trim($_GET['course']) : "";
$gender = isset($_GET['gender']) ? trim($_GET['gender']) : "";

$userCollegeId = (int)($_SESSION['college_id'] ?? 1);

$query = "SELECT * FROM students WHERE (college_id = ? OR (college_id IS NULL AND ? = 1))";
$params = [$userCollegeId, $userCollegeId];
$types = "ii";

if($search !== "")
{
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%" . $search . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if($course !== "")
{
    $query .= " AND course = ?";
    $params[] = $course;
    $types .= "s";
}

if($gender !== "")
{
    $query .= " AND gender = ?";
    $params[] = $gender;
    $types .= "s";
}

$query .= " ORDER BY id DESC";

$stmt = $conn->prepare($query);
if(!empty($params))
{
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ==========================
// TOTAL STUDENTS
// ==========================

$countQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1)"
);
$total = mysqli_fetch_assoc($countQuery)['total'] ?? 0;



?>



<!DOCTYPE html>

<html>

<head>

<title>Students | Smart Campus CRM</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="../assets/css/dashboard.css">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


</head>


<body>



<?php include "../includes/sidebar.php"; ?>


<div class="main">


<?php

$pageTitle = "Students Management";

include "../includes/topbar.php";

?>



<div class="header">

<h1>
Students Management
</h1>


<p>
Manage all student records from one place.
</p>


</div>




<div class="stats-grid mt-4">


<div class="stat-card">


<i class="fa-solid fa-user-graduate icon"></i>


<h2>
<?php echo $total; ?>
</h2>


<p>
Total Students
</p>


</div>


</div>





<div class="table-box mt-4">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

<form method="GET" class="row g-2 align-items-center flex-grow-1">

<div class="col-md-4">
<input 
type="text"
name="search"
class="form-control"
placeholder="Search Student name, email, phone..."
value="<?php echo htmlspecialchars($search); ?>"
>
</div>

<div class="col-md-3">
<select name="course" class="form-select">
<option value="">All Courses</option>
<?php
$courseList = mysqli_query($conn, "SELECT DISTINCT course FROM students WHERE course IS NOT NULL AND course != ''");
while($c = mysqli_fetch_assoc($courseList)) {
?>
<option value="<?php echo htmlspecialchars($c['course']); ?>" <?php if($course === $c['course']) echo 'selected'; ?>>
<?php echo htmlspecialchars($c['course']); ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-2">
<select name="gender" class="form-select">
<option value="">All Gender</option>
<option value="Male" <?php if($gender === 'Male') echo 'selected'; ?>>Male</option>
<option value="Female" <?php if($gender === 'Female') echo 'selected'; ?>>Female</option>
<option value="Other" <?php if($gender === 'Other') echo 'selected'; ?>>Other</option>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">
<i class="fa-solid fa-magnifying-glass"></i> Search
</button>
</div>

</form>

<div class="crm-table-actions">
<button class="btn-crm-export" onclick="exportTableToCSV('studentsTable', 'students_master_list.csv')">
<i class="fa-solid fa-file-csv"></i> Export CSV
</button>
<button class="btn-crm-print" onclick="printPage()">
<i class="fa-solid fa-print"></i> Print
</button>
<a href="add_student.php" class="btn btn-success rounded-pill px-3">
<i class="fa-solid fa-user-plus"></i> Add Student
</a>
</div>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle" id="studentsTable">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Gender</th>
<th>Course</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php
if(mysqli_num_rows($result) > 0) {
$sn = 1;
while($row = mysqli_fetch_assoc($result)) {
    $detailJson = htmlspecialchars(json_encode([
        'ID' => $sn,
        'DatabaseID' => $row['id'],
        'Name' => $row['full_name'],
        'Email' => $row['email'],
        'Phone' => $row['phone'],
        'Gender' => $row['gender'],
        'DOB' => $row['date_of_birth'],
        'Course' => $row['course'],
        'Address' => $row['address']
    ]), ENT_QUOTES, 'UTF-8');
?>

<tr>
<td>#<?php echo $sn++; ?></td>
<td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo htmlspecialchars($row['phone']); ?></td>
<td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['gender']); ?></span></td>
<td><span class="badge bg-primary"><?php echo htmlspecialchars($row['course']); ?></span></td>
<td><span class="badge-crm-enrolled">Active Enrolled</span></td>

<td>
<div class="btn-group btn-group-sm">
<button type="button" class="btn btn-info text-white" title="Quick View" onclick='viewStudentDetails(<?php echo $detailJson; ?>)'>
<i class="fa-solid fa-eye"></i>
</button>
<a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
<i class="fa-solid fa-pen"></i>
</a>
<a href="delete_student.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this student?');" title="Delete">
<i class="fa-solid fa-trash"></i>
</a>
</div>
</td>
</tr>

<?php
}
} else {
?>
<tr>
<td colspan="8" class="text-center text-muted py-4">No Students Found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>

</div>

</div>

<script>
function viewStudentDetails(data) {
    let html = `
        <div class="p-2">
            <div class="text-center mb-3">
                <div class="display-6 text-primary mb-2"><i class="fa-solid fa-user-graduate"></i></div>
                <h5>${escapeHtml(data.Name)}</h5>
                <span class="badge-crm-enrolled">Active Student</span>
            </div>
            <table class="table table-bordered">
                <tr><th>Serial No</th><td>#${escapeHtml(data.ID)}</td></tr>
                <tr><th>Email</th><td>${escapeHtml(data.Email)}</td></tr>
                <tr><th>Phone</th><td>${escapeHtml(data.Phone || 'N/A')}</td></tr>
                <tr><th>Gender</th><td>${escapeHtml(data.Gender || 'N/A')}</td></tr>
                <tr><th>Date of Birth</th><td>${escapeHtml(data.DOB || 'N/A')}</td></tr>
                <tr><th>Course</th><td>${escapeHtml(data.Course || 'N/A')}</td></tr>
                <tr><th>Address</th><td>${escapeHtml(data.Address || 'N/A')}</td></tr>
            </table>
        </div>
    `;
    showQuickViewModal('Student Profile Details', html);
}
</script>

</body>
</html>