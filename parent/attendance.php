<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "parent") {
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];
$parentRes = mysqli_query($conn, "SELECT * FROM parents WHERE email='".mysqli_real_escape_string($conn, $email)."'");
$parent = mysqli_fetch_assoc($parentRes);
if(!$parent) {
    $uRes = mysqli_query($conn, "SELECT * FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."'");
    $uData = mysqli_fetch_assoc($uRes);
    $parentSurname = $uData ? ($uData['last_name'] ?: end(explode(' ', trim($uData['full_name'])))) : '';
} else {
    $parentSurname = $parent['last_name'] ?: end(explode(' ', trim($parent['full_name'])));
}

$surEscaped = mysqli_real_escape_string($conn, trim($parentSurname));
$clgId = (int)($parent['college_id'] ?? ($_SESSION['college_id'] ?? 1));
$childrenRes = mysqli_query($conn, "SELECT * FROM students WHERE (last_name = '$surEscaped' OR full_name LIKE '% $surEscaped') AND (college_id = '$clgId' OR college_id = 0 OR college_id IS NULL) ORDER BY id ASC");
if(!$childrenRes || mysqli_num_rows($childrenRes) === 0) {
    $childrenRes = mysqli_query($conn, "SELECT * FROM students WHERE last_name = '$surEscaped' OR full_name LIKE '% $surEscaped' ORDER BY id ASC");
}
$children = [];
if($childrenRes) {
    while($ch = mysqli_fetch_assoc($childrenRes)) {
        $children[] = $ch;
    }
}

$selectedChildId = isset($_GET['child_id']) ? (int)$_GET['child_id'] : ($children[0]['id'] ?? 0);
$selectedChild = null;
foreach($children as $ch) {
    if($ch['id'] == $selectedChildId) {
        $selectedChild = $ch;
        break;
    }
}
if(!$selectedChild && !empty($children)) {
    $selectedChild = $children[0];
    $selectedChildId = $selectedChild['id'];
}

$attendanceLogs = [];
$totalAtt = 0; $presentAtt = 0; $absentAtt = 0; $lateAtt = 0; $attPct = 100;

if($selectedChild) {
    $sid = $selectedChild['id'];
    $attRes = mysqli_query($conn, "SELECT * FROM attendance WHERE student_id='$sid' ORDER BY attendance_date DESC");
    if($attRes) {
        while($r = mysqli_fetch_assoc($attRes)) {
            $attendanceLogs[] = $r;
            $totalAtt++;
            if($r['status'] == 'Present') $presentAtt++;
            elseif($r['status'] == 'Absent') $absentAtt++;
            elseif($r['status'] == 'Late') $lateAtt++;
        }
    }
    $attPct = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100) : 100;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Child Attendance | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<style>
.child-tabs {
    display: flex; gap: 10px; margin-bottom: 20px; background: white; padding: 12px 18px; border-radius: 16px; border: 1px solid #E2E8F0;
}
.child-tab-btn {
    padding: 8px 18px; border-radius: 12px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #334155; font-weight: 500; font-size: 14px; text-decoration: none; transition: 0.3s;
}
.child-tab-btn.active {
    background: #2563EB; color: white; border-color: #2563EB;
}
</style>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Child Attendance Reports"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1>Child Attendance Log 📅</h1>
    <p>View daily attendance records and tracking statistics for your child</p>
</div>

<?php if(empty($children)): ?>
    <div class="crm-card text-center p-5 my-4">
        <i class="fa-solid fa-calendar-xmark text-muted fa-3x mb-3"></i>
        <h4>No Student Linked</h4>
        <p class="text-muted">No student found matching surname "<?= htmlspecialchars($parentSurname) ?>".</p>
    </div>
<?php else: ?>

    <?php if(count($children) > 1): ?>
        <div class="child-tabs">
            <span class="fw-semibold text-muted d-flex align-items-center me-2">Select Child:</span>
            <?php foreach($children as $ch): ?>
                <a href="attendance.php?child_id=<?= $ch['id'] ?>" class="child-tab-btn <?= $ch['id'] == $selectedChildId ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate me-1"></i> <?= htmlspecialchars($ch['full_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-calendar"></i></div>
            <h2><?= $totalAtt ?></h2>
            <p>Total Class Days</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-circle-check" style="color:var(--success);"></i></div>
            <h2><?= $presentAtt ?></h2>
            <p>Days Present</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i></div>
            <h2><?= $absentAtt ?></h2>
            <p>Days Absent</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-clock" style="color:var(--warning);"></i></div>
            <h2><?= $lateAtt ?></h2>
            <p>Days Late</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-percent"></i></div>
            <h2><?= $attPct ?>%</h2>
            <p>Attendance Rate</p>
        </div>
    </div>

    <div class="crm-card">
        <div class="crm-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa-solid fa-list-check"></i> Attendance Record Table for <?= htmlspecialchars($selectedChild['full_name']) ?></h4>
            <span class="badge <?= $attPct >= 75 ? 'bg-success' : 'bg-danger' ?> px-3 py-2">
                <?= $attPct >= 75 ? 'Satisfactory Attendance' : 'Action Required (< 75%)' ?>
            </span>
        </div>
        <div class="crm-card-body p-0">
            <?php if(empty($attendanceLogs)): ?>
                <div class="text-center py-5">
                    <i class="fa-regular fa-folder-open text-muted fa-3x mb-2"></i>
                    <p class="text-muted mb-0">No attendance entries recorded for this student yet.</p>
                </div>
            <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($attendanceLogs as $idx => $att): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="fw-semibold">
                                    <i class="fa-regular fa-calendar me-2 text-primary"></i>
                                    <?= date('l, M d, Y', strtotime($att['attendance_date'])) ?>
                                </td>
                                <td>
                                    <?php if($att['status'] == 'Present'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                                            <i class="fa-solid fa-circle-check me-1"></i> Present
                                        </span>
                                    <?php elseif($att['status'] == 'Absent'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">
                                            <i class="fa-solid fa-circle-xmark me-1"></i> Absent
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1 rounded-pill">
                                            <i class="fa-solid fa-clock me-1"></i> Late
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted">
                                    <?= $att['status'] == 'Present' ? 'Attended scheduled session' : ($att['status'] == 'Absent' ? 'Marked absent by course instructor' : 'Arrived late to class') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

</div>
</body>
</html>
