<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



$userCollegeId = (int)($_SESSION['college_id'] ?? 1);
$query = "SELECT * FROM teachers WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1) ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$countQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM teachers WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1)");
$total = mysqli_fetch_assoc($countQuery)['total'] ?? 0;

?>

<!DOCTYPE html>

<html>

<head>

<title>Teachers | Smart Campus CRM</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="../assets/css/dashboard.css">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


</head>


<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Teacher Management";
include "../includes/topbar.php";
?>





<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-0"><i class="fa-solid fa-chalkboard-user text-primary"></i> Teacher Management</h4>
        <small class="text-muted">Manage faculty members and teaching staff.</small>
    </div>
    <div class="crm-table-actions">
        <button class="btn-crm-export" onclick="exportTableToCSV('teachersTable', 'teachers_list.csv')">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-crm-print" onclick="printPage()">
            <i class="fa-solid fa-print"></i> Print
        </button>
        <a href="add_teacher.php" class="btn btn-success rounded-pill px-3">
            <i class="fa-solid fa-user-plus"></i> Add Teacher
        </a>
    </div>
</div>

<div class="alert alert-primary d-flex align-items-center gap-2 rounded-3 shadow-sm mb-4">
    <i class="fa-solid fa-users fa-lg"></i>
    <span>Total Faculty Members: <strong><?php echo $total; ?></strong></span>
</div>

<div class="table-box">
<div class="table-responsive">

<table class="table table-hover align-middle" id="teachersTable">

<thead class="table-dark">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Subject</th>
<th>Qualification</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php 
if($result && mysqli_num_rows($result) > 0) {
$sn = 1;
while($row = mysqli_fetch_assoc($result)) { 
    $tJson = htmlspecialchars(json_encode([
        'ID' => $sn,
        'DatabaseID' => $row['id'],
        'Name' => $row['full_name'],
        'Email' => $row['email'],
        'Phone' => $row['phone'],
        'Subject' => $row['subject'],
        'Qualification' => $row['qualification']
    ]), ENT_QUOTES, 'UTF-8');
?>

<tr>
<td>#<?php echo $sn++; ?></td>
<td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo htmlspecialchars($row['phone']); ?></td>
<td><span class="badge bg-primary"><?php echo htmlspecialchars($row['subject']); ?></span></td>
<td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['qualification']); ?></span></td>

<td>
<div class="btn-group btn-group-sm">
<button type="button" class="btn btn-info text-white" title="Quick View" onclick='viewTeacherDetails(<?php echo $tJson; ?>)'>
<i class="fa-solid fa-eye"></i>
</button>
<a href="edit_teacher.php?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
<i class="fa-solid fa-pen"></i>
</a>
<a href="delete_teacher.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete Teacher?')" title="Delete">
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
<td colspan="7" class="text-center text-muted py-4">No Faculty Members Found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>
</div>

</div>

<script>
function viewTeacherDetails(data) {
    let html = `
        <div class="p-2">
            <div class="text-center mb-3">
                <div class="display-6 text-primary mb-2"><i class="fa-solid fa-chalkboard-user"></i></div>
                <h5>${escapeHtml(data.Name)}</h5>
                <span class="badge bg-primary">${escapeHtml(data.Subject)} Faculty</span>
            </div>
            <table class="table table-bordered">
                <tr><th>Serial No</th><td>#${escapeHtml(data.ID)}</td></tr>
                <tr><th>Email</th><td>${escapeHtml(data.Email)}</td></tr>
                <tr><th>Phone</th><td>${escapeHtml(data.Phone || 'N/A')}</td></tr>
                <tr><th>Subject Handled</th><td>${escapeHtml(data.Subject || 'N/A')}</td></tr>
                <tr><th>Qualification</th><td>${escapeHtml(data.Qualification || 'N/A')}</td></tr>
            </table>
        </div>
    `;
    showQuickViewModal('Teacher Profile Details', html);
}
</script>

</body>
</html>