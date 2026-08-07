<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "parent") {
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch Parent Info
$parentRes = mysqli_query($conn, "SELECT * FROM parents WHERE email='".mysqli_real_escape_string($conn, $email)."'");
$parent = mysqli_fetch_assoc($parentRes);

// If parent profile missing in parents table, check users table
if(!$parent) {
    $uRes = mysqli_query($conn, "SELECT * FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."'");
    $uData = mysqli_fetch_assoc($uRes);
    if($uData) {
        $parent = [
            'first_name' => $uData['first_name'] ?: 'Parent',
            'last_name'  => $uData['last_name'] ?: '',
            'full_name'  => $uData['full_name'],
            'email'      => $uData['email'],
            'phone'      => '',
            'address'    => ''
        ];
    } else {
        die("Parent profile not found.");
    }
}

$parentSurname = trim($parent['last_name']);
if(empty($parentSurname)) {
    // Fallback: extract last word of full name as surname
    $nameParts = explode(' ', trim($parent['full_name']));
    $parentSurname = end($nameParts);
}

// Find all students matching parent's surname
$surEscaped = mysqli_real_escape_string($conn, $parentSurname);
$childrenRes = mysqli_query($conn, "SELECT * FROM students WHERE last_name = '$surEscaped' OR full_name LIKE '% $surEscaped' ORDER BY id ASC");
$children = [];
if($childrenRes) {
    while($ch = mysqli_fetch_assoc($childrenRes)) {
        $children[] = $ch;
    }
}

// Active selected child
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

/* ─ Child Academic Data ───────────────────────────────── */
$attPct = 100; $totalAtt = 0; $presentAtt = 0; $absentAtt = 0; $lateAtt = 0;
$avgMarks = 0; $subjCount = 0; $cgpa = 0;
$subjectLabels = []; $subjectData = [];
$achievements = []; $upcomingExams = [];

if($selectedChild) {
    $sid = $selectedChild['id'];

    // Attendance
    $attData = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN status='Absent'  THEN 1 ELSE 0 END) AS absent,
                SUM(CASE WHEN status='Late'    THEN 1 ELSE 0 END) AS late
         FROM attendance WHERE student_id='$sid'"));
    $totalAtt   = $attData['total']   ?? 0;
    $presentAtt = $attData['present'] ?? 0;
    $absentAtt  = $attData['absent']  ?? 0;
    $lateAtt    = $attData['late']    ?? 0;
    $attPct     = $totalAtt > 0 ? round(($presentAtt / $totalAtt) * 100) : 100;

    // Results & GPA
    $avgData  = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT AVG(marks) AS avg, COUNT(*) as cnt FROM results WHERE student_id='$sid'"));
    $avgMarks = round($avgData['avg'] ?? 0, 1);
    $subjCount= $avgData['cnt'] ?? 0;
    $cgpa     = round($avgMarks / 10, 2);

    // Subject marks chart
    $tmpRes = mysqli_query($conn, "SELECT subject, marks FROM results WHERE student_id='$sid'");
    if($tmpRes) {
        while($r = mysqli_fetch_assoc($tmpRes)) {
            $subjectLabels[] = $r['subject'];
            $subjectData[]   = (int)$r['marks'];
        }
    }

    // Achievements
    $achQuery = mysqli_query($conn, "SELECT * FROM achievements WHERE student_id='$sid' ORDER BY awarded_date DESC");
    if($achQuery) {
        while($a = mysqli_fetch_assoc($achQuery)) {
            $achievements[] = $a;
        }
    }

    // Upcoming Exams
    $examQuery = mysqli_query($conn, "SELECT * FROM marks_schedule WHERE exam_date >= CURDATE() ORDER BY exam_date ASC LIMIT 4");
    if($examQuery) {
        while($ex = mysqli_fetch_assoc($examQuery)) {
            $upcomingExams[] = $ex;
        }
    }
}

// Announcements
$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Parent Portal | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.child-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    background: white;
    padding: 12px 18px;
    border-radius: 16px;
    border: 1px solid var(--border-color, #E2E8F0);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
.child-tab-btn {
    padding: 8px 18px;
    border-radius: 12px;
    border: 1px solid #CBD5E1;
    background: #F8FAFC;
    color: #334155;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.child-tab-btn.active {
    background: var(--primary, #2563EB);
    color: white;
    border-color: var(--primary, #2563EB);
    box-shadow: 0 4px 12px rgba(37,99,235,0.25);
}
</style>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Parent Portal Dashboard"; include "../includes/topbar.php"; ?>

<!-- Header -->
<div class="page-header">
    <h1>Welcome, <?= htmlspecialchars($parent['full_name']) ?> 👋</h1>
    <p>Monitor your child's academic progress, attendance percentage, exam grades, and achievements</p>
</div>

<?php if(empty($children)): ?>
    <!-- No Linked Child Alert -->
    <div class="crm-card text-center p-5 my-4">
        <div class="mb-3">
            <i class="fa-solid fa-user-slash text-warning fa-4x"></i>
        </div>
        <h3>No Student Found with Surname "<?= htmlspecialchars($parentSurname) ?>"</h3>
        <p class="text-muted max-w-lg mx-auto">
            Your parent account is configured to automatically link with students bearing the last name <strong>"<?= htmlspecialchars($parentSurname) ?>"</strong>.
            Currently, no student registered under this surname is in the system. When your child registers, their details will automatically appear here.
        </p>
        <a href="profile.php" class="btn-crm-primary btn-crm-md mt-2">
            <i class="fa-solid fa-user-gear"></i> Manage Profile Info
        </a>
    </div>
<?php else: ?>

    <!-- Multiple Children Tab Selector -->
    <?php if(count($children) > 1): ?>
        <div class="child-tabs">
            <span class="fw-semibold text-muted d-flex align-items-center me-2">Select Child:</span>
            <?php foreach($children as $ch): ?>
                <a href="dashboard.php?child_id=<?= $ch['id'] ?>" class="child-tab-btn <?= $ch['id'] == $selectedChildId ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-graduate"></i> <?= htmlspecialchars($ch['full_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Active Child Banner -->
    <div class="alert alert-primary d-flex align-items-center justify-content-between rounded-4 shadow-sm mb-4 px-4 py-3" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE;">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;font-size:20px;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <h5 class="mb-0 text-primary-emphasis font-bold"><?= htmlspecialchars($selectedChild['full_name']) ?></h5>
                <small class="text-primary-emphasis"><i class="fa-solid fa-book-open me-1"></i> Course: <?= htmlspecialchars($selectedChild['course'] ?: 'Not Assigned') ?> | Surname Match: "<?= htmlspecialchars($parentSurname) ?>"</small>
            </div>
        </div>
        <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="fa-solid fa-link me-1"></i> Linked Student</span>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-book-open"></i></div>
            <h2 style="font-size:18px;font-weight:800;"><?= htmlspecialchars($selectedChild['course'] ?: 'N/A') ?></h2>
            <p>Enrolled Program</p>
        </div>
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div class="icon"><i class="fa-solid fa-calendar-check"></i></div>
                <span class="trend <?= $attPct >= 75 ? 'trend-up' : 'trend-down' ?>"><?= $attPct >= 75 ? '✓ Good' : '⚠ Low' ?></span>
            </div>
            <h2><?= $attPct ?>%</h2>
            <p>Attendance Rate</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-star"></i></div>
            <h2><?= $cgpa ?><small style="font-size:16px;">/10</small></h2>
            <p>Child CGPA</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-chart-bar"></i></div>
            <h2><?= $avgMarks ?>%</h2>
            <p>Average Marks</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fa-solid fa-trophy"></i></div>
            <h2><?= count($achievements) ?></h2>
            <p>Total Achievements</p>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="quick-section">
        <a href="attendance.php?child_id=<?= $selectedChildId ?>" class="action-card">
            <i class="fa-solid fa-calendar-check"></i>
            <h5>Child Attendance</h5><p>Detailed attendance log</p>
        </a>
        <a href="results.php?child_id=<?= $selectedChildId ?>" class="action-card">
            <i class="fa-solid fa-chart-bar"></i>
            <h5>Child Results</h5><p>Exam scores & subject grades</p>
        </a>
        <a href="profile.php" class="action-card">
            <i class="fa-solid fa-id-card"></i>
            <h5>Parent Profile</h5><p>Update personal contact info</p>
        </a>
    </div>

    <!-- Main Content Bento Grid -->
    <div class="bento-grid">

        <!-- Attendance Health -->
        <div class="crm-card bento-col-4">
            <div class="crm-card-header">
                <h4><i class="fa-solid fa-calendar-check"></i> Attendance Health</h4>
            </div>
            <div class="crm-card-body text-center">
                <div class="progress-ring" style="margin-bottom:16px;">
                    <svg width="100" height="100" viewBox="0 0 100 100">
                        <circle class="ring-bg" cx="50" cy="50" r="41"/>
                        <circle class="ring-fill" id="attRing" cx="50" cy="50" r="41"
                            stroke="<?= $attPct >= 75 ? '#10B981' : '#EF4444' ?>"
                            stroke-dasharray="258" stroke-dashoffset="<?= 258 - (258 * $attPct / 100) ?>"/>
                    </svg>
                    <div class="progress-ring-text">
                        <div class="pct"><?= $attPct ?>%</div>
                        <div class="lbl">Present</div>
                    </div>
                </div>
                <div class="growth-metric">
                    <span class="metric-label"><i class="fa-solid fa-circle-check" style="color:var(--success);"></i> Present</span>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:<?= $totalAtt>0?round($presentAtt/$totalAtt*100):0 ?>%;background:var(--success);"></div></div>
                    <span class="metric-val"><?= $presentAtt ?></span>
                </div>
                <div class="growth-metric">
                    <span class="metric-label"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i> Absent</span>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:<?= $totalAtt>0?round($absentAtt/$totalAtt*100):0 ?>%;background:var(--danger);"></div></div>
                    <span class="metric-val"><?= $absentAtt ?></span>
                </div>
                <div class="growth-metric">
                    <span class="metric-label"><i class="fa-solid fa-clock" style="color:var(--warning);"></i> Late</span>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:<?= $totalAtt>0?round($lateAtt/$totalAtt*100):0 ?>%;background:var(--warning);"></div></div>
                    <span class="metric-val"><?= $lateAtt ?></span>
                </div>
            </div>
        </div>

        <!-- Academic Performance Chart -->
        <div class="crm-card bento-col-8">
            <div class="crm-card-header d-flex align-items-center justify-content-between">
                <h4><i class="fa-solid fa-square-poll-vertical"></i> Subject Performance Scores</h4>
                <a href="results.php?child_id=<?= $selectedChildId ?>" class="btn-crm-outline btn-crm-sm">Full Report</a>
            </div>
            <div class="crm-card-body">
                <?php if(empty($subjectLabels)): ?>
                    <p class="text-muted text-center py-4">No examination results published yet for this student.</p>
                <?php else: ?>
                    <canvas id="parentSubjectChart" height="210"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- Achievements -->
        <div class="crm-card bento-col-6">
            <div class="crm-card-header">
                <h4><i class="fa-solid fa-trophy"></i> Recognized Achievements</h4>
            </div>
            <div class="crm-card-body">
                <?php if(empty($achievements)): ?>
                    <p class="text-muted text-center py-3">No achievements recorded yet.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach($achievements as $ach): ?>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#F8FAFC; border:1px solid #E2E8F0;">
                                <div class="badge-icon-box text-warning fs-3">
                                    <i class="fa-solid <?= htmlspecialchars($ach['badge_icon'] ?: 'fa-trophy') ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($ach['title']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($ach['description']) ?></small>
                                    <div><small class="text-primary" style="font-size:11px;"><i class="fa-regular fa-calendar me-1"></i> Awarded: <?= date('M d, Y', strtotime($ach['awarded_date'])) ?></small></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Exam Schedule -->
        <div class="crm-card bento-col-6">
            <div class="crm-card-header">
                <h4><i class="fa-solid fa-calendar-days"></i> Upcoming Examination Schedule</h4>
            </div>
            <div class="crm-card-body p-0">
                <?php if(empty($upcomingExams)): ?>
                    <p class="text-muted text-center py-3">No upcoming exams scheduled.</p>
                <?php else: ?>
                    <table class="table align-middle mb-0" style="font-size:14px;">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Exam Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($upcomingExams as $ex): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($ex['subject']) ?></td>
                                    <td><span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($ex['exam_type']) ?></span></td>
                                    <td><i class="fa-regular fa-calendar-check text-primary me-1"></i> <?= date('M d, Y', strtotime($ex['exam_date'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>

<?php endif; ?>

</div>

<script>
<?php if(!empty($subjectLabels)): ?>
const ctx = document.getElementById('parentSubjectChart');
if(ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($subjectLabels) ?>,
            datasets: [{
                label: 'Marks Obtained',
                data: <?= json_encode($subjectData) ?>,
                backgroundColor: 'rgba(37, 99, 235, 0.75)',
                borderColor: '#2563EB',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { callback: function(value) { return value + '%'; } }
                }
            }
        }
    });
}
<?php endif; ?>
</script>

</body>
</html>
