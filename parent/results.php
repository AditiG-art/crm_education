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
$childrenRes = mysqli_query($conn, "SELECT * FROM students WHERE last_name = '$surEscaped' OR full_name LIKE '% $surEscaped' ORDER BY id ASC");
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

$resultsList = [];
$totalMarks = 0; $subjCount = 0; $avgMarks = 0; $cgpa = 0;

if($selectedChild) {
    $sid = $selectedChild['id'];
    $resQ = mysqli_query($conn, "SELECT * FROM results WHERE student_id='$sid' ORDER BY id DESC");
    if($resQ) {
        while($r = mysqli_fetch_assoc($resQ)) {
            $resultsList[] = $r;
            $totalMarks += $r['marks'];
            $subjCount++;
        }
    }
    $avgMarks = $subjCount > 0 ? round($totalMarks / $subjCount, 1) : 0;
    $cgpa = round($avgMarks / 10, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Child Academic Results | Smart Campus CRM</title>
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
<?php $pageTitle = "Child Academic Results & Grades"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1>Academic Results & Grades 📊</h1>
    <p>View complete examination scores and grades for your child</p>
</div>

<?php if(empty($children)): ?>
    <div class="crm-card text-center p-5 my-4">
        <i class="fa-solid fa-square-poll-vertical text-muted fa-3x mb-3"></i>
        <h4>No Student Linked</h4>
        <p class="text-muted">No student found matching surname "<?= htmlspecialchars($parentSurname) ?>".</p>
    </div>
<?php else: ?>

    <?php if(count($children) > 1): ?>
        <div class="child-tabs">
            <span class="fw-semibold text-muted d-flex align-items-center me-2">Select Child:</span>
            <?php foreach($children as $ch): ?>
                <a href="results.php?child_id=<?= $ch['id'] ?>" class="child-tab-btn <?= $ch['id'] == $selectedChildId ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate me-1"></i> <?= htmlspecialchars($ch['full_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-book-bookmark"></i></div>
            <h2><?= $subjCount ?></h2>
            <p>Subjects Evaluated</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-chart-line"></i></div>
            <h2><?= $avgMarks ?>%</h2>
            <p>Average Score</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-star"></i></div>
            <h2><?= $cgpa ?><small style="font-size:16px;">/10</small></h2>
            <p>Overall CGPA</p>
        </div>
    </div>

    <div class="crm-card">
        <div class="crm-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa-solid fa-graduation-cap"></i> Published Subject Marks & Grades for <?= htmlspecialchars($selectedChild['full_name']) ?></h4>
            <span class="badge bg-primary px-3 py-2">Official Transcript View</span>
        </div>
        <div class="crm-card-body p-0">
            <?php if(empty($resultsList)): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-chart-bar text-muted fa-3x mb-2"></i>
                    <p class="text-muted mb-0">No result cards published for this student yet.</p>
                </div>
            <?php else: ?>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Grade</th>
                            <th>Performance Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($resultsList as $idx => $r): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td class="fw-semibold text-primary">
                                    <i class="fa-solid fa-book-open me-2 text-secondary"></i>
                                    <?= htmlspecialchars($r['subject']) ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:10px;max-width:140px;">
                                            <div class="progress-bar <?= $r['marks']>=75 ? 'bg-success' : ($r['marks']>=50 ? 'bg-primary' : 'bg-danger') ?>"
                                                 style="width:<?= $r['marks'] ?>%"></div>
                                        </div>
                                        <span class="fw-bold"><?= $r['marks'] ?>/100</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= strpos($r['grade'], 'A') !== false ? 'bg-success' : (strpos($r['grade'], 'B') !== false ? 'bg-primary' : 'bg-warning') ?> px-3 py-1 rounded-pill">
                                        <?= htmlspecialchars($r['grade']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($r['marks'] >= 85): ?>
                                        <span class="text-success small fw-semibold"><i class="fa-solid fa-circle-check"></i> Excellent</span>
                                    <?php elseif($r['marks'] >= 70): ?>
                                        <span class="text-primary small fw-semibold"><i class="fa-solid fa-thumbs-up"></i> Good</span>
                                    <?php elseif($r['marks'] >= 50): ?>
                                        <span class="text-warning small fw-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Average</span>
                                    <?php else: ?>
                                        <span class="text-danger small fw-semibold"><i class="fa-solid fa-circle-xmark"></i> Needs Improvement</span>
                                    <?php endif; ?>
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
