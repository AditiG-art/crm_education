<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user']) || $_SESSION['role'] != "student") {
    header("Location:../login.php"); exit();
}

$email = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM students WHERE email='".mysqli_real_escape_string($conn,$email)."'");
$student = mysqli_fetch_assoc($query);
if(!$student) die("Student profile not found.");

$sid = $student['id'];

/* ─ Attendance ───────────────────────────────── */
$attData = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) AS present,
            SUM(CASE WHEN status='Absent'  THEN 1 ELSE 0 END) AS absent,
            SUM(CASE WHEN status='Late'    THEN 1 ELSE 0 END) AS late
     FROM attendance WHERE student_id='$sid'"));
$totalAtt   = $attData['total']   ?? 0;
$presentAtt = $attData['present'] ?? 0;
$absentAtt  = $attData['absent']  ?? 0;
$lateAtt    = $attData['late']    ?? 0;
$attPct     = $totalAtt > 0 ? round(($presentAtt/$totalAtt)*100) : 100;

/* ─ Results ──────────────────────────────────── */
$avgData    = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT AVG(marks) AS avg, COUNT(*) as cnt FROM results WHERE student_id='$sid'"));
$avgMarks   = round($avgData['avg'] ?? 0, 1);
$subjCount  = $avgData['cnt'] ?? 0;
$cgpa       = round($avgMarks/10, 2);

/* ─ Subject marks (for chart) ────────────────── */
$resultRows = mysqli_query($conn,"SELECT * FROM results WHERE student_id='$sid' ORDER BY id DESC");
$subjectLabels = []; $subjectData = [];
$tmpRes = mysqli_query($conn,"SELECT subject, marks FROM results WHERE student_id='$sid'");
while($r = mysqli_fetch_assoc($tmpRes)) {
    $subjectLabels[] = $r['subject'];
    $subjectData[]   = (int)$r['marks'];
}

/* ─ Achievements ─────────────────────────────── */
$achQuery = mysqli_query($conn,"SELECT * FROM achievements WHERE student_id='$sid' ORDER BY awarded_date DESC");
$achCount = mysqli_num_rows($achQuery);

/* ─ Upcoming Exams ───────────────────────────── */
$upcomingExams = mysqli_query($conn,"SELECT * FROM marks_schedule WHERE exam_date >= CURDATE() ORDER BY exam_date ASC LIMIT 3");

/* ─ Announcements ────────────────────────────── */
$announcements = mysqli_query($conn,"SELECT * FROM announcements ORDER BY created_at DESC LIMIT 2");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Student Dashboard"; include "../includes/topbar.php"; ?>

<!-- Welcome Header -->
<div class="page-header">
    <h1>Welcome Back, <?= htmlspecialchars($student['full_name']) ?> 👋</h1>
    <p>Track your attendance, academic progress, achievements, and upcoming exams</p>
    <div class="header-actions">
        <a href="results.php" class="btn-crm-primary btn-crm-sm"><i class="fa-solid fa-chart-bar"></i> My Results</a>
        <a href="attendance.php" class="btn-crm-warning btn-crm-sm"><i class="fa-solid fa-calendar-check"></i> Attendance</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-book-open"></i></div>
        <h2 style="font-size:20px;font-weight:800;"><?= htmlspecialchars($student['course'] ?: 'Not Set') ?></h2>
        <p>Enrolled Course</p>
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
        <p>Current CGPA</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-chart-bar"></i></div>
        <h2><?= $avgMarks ?>%</h2>
        <p>Average Score</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-trophy"></i></div>
        <h2 class="counter" data-target="<?= $achCount ?>">0</h2>
        <p>Achievements</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-section">
    <a href="results.php" class="action-card">
        <i class="fa-solid fa-chart-bar"></i>
        <h5>My Results</h5><p>View marks and grades</p>
    </a>
    <a href="attendance.php" class="action-card">
        <i class="fa-solid fa-calendar-check"></i>
        <h5>Attendance</h5><p>Check attendance log</p>
    </a>
    <a href="profile.php" class="action-card">
        <i class="fa-solid fa-id-card"></i>
        <h5>My Profile</h5><p>Update personal info</p>
    </a>
</div>

<!-- Bento Grid -->
<div class="bento-grid">

    <!-- Attendance Ring + Progress -->
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
            <div class="growth-metric"><span class="metric-label"><i class="fa-solid fa-circle-check" style="color:var(--success);"></i> Present</span><div class="metric-bar-wrap"><div class="metric-bar" style="width:<?= $totalAtt>0?round($presentAtt/$totalAtt*100):0 ?>%;background:var(--success);"></div></div><span class="metric-val"><?= $presentAtt ?></span></div>
            <div class="growth-metric"><span class="metric-label"><i class="fa-solid fa-circle-xmark" style="color:var(--danger);"></i> Absent</span><div class="metric-bar-wrap"><div class="metric-bar" style="width:<?= $totalAtt>0?round($absentAtt/$totalAtt*100):0 ?>%;background:var(--danger);"></div></div><span class="metric-val"><?= $absentAtt ?></span></div>
            <div class="growth-metric"><span class="metric-label"><i class="fa-solid fa-clock" style="color:var(--warning);"></i> Late</span><div class="metric-bar-wrap"><div class="metric-bar" style="width:<?= $totalAtt>0?round($lateAtt/$totalAtt*100):0 ?>%;background:var(--warning);"></div></div><span class="metric-val"><?= $lateAtt ?></span></div>
            <a href="attendance.php" class="btn-crm-primary btn-crm-sm mt-3 w-100">Full Log</a>
        </div>
    </div>

    <!-- Marks Chart -->
    <div class="crm-card bento-col-8">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-chart-bar"></i> Subject Marks Breakdown</h4>
            <a href="results.php" class="btn-crm-primary btn-crm-sm">Full Transcript</a>
        </div>
        <div class="crm-card-body">
            <?php if(!empty($subjectLabels)): ?>
            <canvas id="marksChart" height="200"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-chart-bar fa-3x mb-3 d-block" style="color:var(--border);"></i>
                No results uploaded yet. Results will appear here once your teacher uploads marks.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Achievements -->
    <div class="crm-card bento-col-5">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-trophy"></i> My Achievements</h4>
            <span class="badge bg-warning text-dark rounded-pill"><?= $achCount ?> earned</span>
        </div>
        <div class="crm-card-body">
            <?php if($achCount > 0): ?>
            <div class="achievements-grid" style="grid-template-columns:repeat(auto-fill,minmax(130px,1fr));">
            <?php while($ach = mysqli_fetch_assoc($achQuery)): ?>
            <div class="achievement-card">
                <div class="achievement-badge-icon badge-<?= htmlspecialchars($ach['badge_type']) ?>">
                    <i class="fa-solid <?= htmlspecialchars($ach['badge_icon']) ?>"></i>
                </div>
                <h6><?= htmlspecialchars($ach['title']) ?></h6>
                <p><?= htmlspecialchars($ach['description']) ?></p>
                <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">
                    <i class="fa-solid fa-calendar"></i> <?= date('M Y',strtotime($ach['awarded_date'])) ?>
                </div>
            </div>
            <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-trophy fa-2x mb-2 d-block" style="color:var(--border);"></i>
                Keep working hard — achievements will be awarded by your teachers!
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Exams -->
    <div class="crm-card bento-col-4">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-calendar-days"></i> Upcoming Exams</h4>
        </div>
        <div class="crm-card-body">
            <div class="exam-schedule-list">
            <?php if(mysqli_num_rows($upcomingExams) > 0): while($ex = mysqli_fetch_assoc($upcomingExams)):
                $dLeft = (int)((strtotime($ex['exam_date'])-time())/86400); ?>
            <div class="exam-item <?= $ex['exam_type']=='Final Exam' ? 'exam-urgent' : ($dLeft<=7?'exam-soon':'') ?>">
                <div class="exam-date-badge">
                    <div class="day"><?= date('d',strtotime($ex['exam_date'])) ?></div>
                    <div class="month"><?= date('M',strtotime($ex['exam_date'])) ?></div>
                </div>
                <div class="exam-info">
                    <div class="subject"><?= htmlspecialchars($ex['subject']) ?></div>
                    <div class="meta"><span><?= htmlspecialchars($ex['exam_type']) ?></span><span>Max: <?= $ex['max_marks'] ?></span></div>
                </div>
                <span class="exam-countdown <?= $dLeft<=3?'urgent':'' ?>"><?= $dLeft<=0?'Today!':"In {$dLeft}d" ?></span>
            </div>
            <?php endwhile; else: ?>
            <p class="text-muted text-center py-3">No upcoming exams scheduled.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="crm-card bento-col-3">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-bullhorn"></i> Announcements</h4>
        </div>
        <div class="crm-card-body">
            <div class="announcement-list">
            <?php
            $typeIcons=['urgent'=>['fa-triangle-exclamation','notice-urgent'],'info'=>['fa-circle-info','notice-info'],'success'=>['fa-circle-check','notice-success'],'general'=>['fa-bullhorn','']];
            if(mysqli_num_rows($announcements)>0): while($ann=mysqli_fetch_assoc($announcements)):
                $ti=$typeIcons[$ann['notice_type']]??$typeIcons['general']; ?>
            <div class="announcement-item <?= $ti[1] ?>">
                <h6><i class="fa-solid <?= $ti[0] ?>" style="margin-right:5px;"></i><?= htmlspecialchars($ann['title']) ?></h6>
                <p style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:12px;"><?= htmlspecialchars($ann['content']) ?></p>
                <div class="announcement-meta"><span><i class="fa-solid fa-clock"></i><?= date('M d',strtotime($ann['created_at'])) ?></span></div>
            </div>
            <?php endwhile; else: ?>
            <p class="text-muted py-2 text-center">No announcements.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /bento-grid -->
</div><!-- /main -->

<script>
document.querySelectorAll('.counter').forEach(c=>{
    const target=+c.getAttribute('data-target'),speed=Math.max(1,Math.ceil(target/40));
    let count=0;
    (function tick(){if(count<target){count+=speed;if(count>target)count=target;c.innerText=count;setTimeout(tick,25);}else c.innerText=target;})();
});
<?php if(!empty($subjectLabels)): ?>
new Chart(document.getElementById('marksChart').getContext('2d'),{
    type:'bar',
    data:{
        labels:<?= json_encode($subjectLabels) ?>,
        datasets:[{
            label:'Marks',
            data:<?= json_encode($subjectData) ?>,
            backgroundColor:<?= json_encode(array_map(fn($i)=>['rgba(37,99,235,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(139,92,246,0.8)','rgba(236,72,153,0.8)','rgba(6,182,212,0.8)'][$i%6],array_keys($subjectLabels))) ?>,
            borderRadius:10,
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true,max:100,ticks:{precision:0}}}
    }
});
<?php endif; ?>
document.getElementById('mobileSidebarToggle')?.addEventListener('click',()=>{
    document.getElementById('mainSidebar').classList.toggle('open');
});
</script>
</body>
</html>