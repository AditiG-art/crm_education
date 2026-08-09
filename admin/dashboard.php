<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user']) || $_SESSION['role'] != "admin") {
    header("Location:../login.php"); exit();
}

$userCollegeId = (int)($_SESSION['college_id'] ?? 1);

/* ─ Stats ─────────────────────────────────────── */
$studentCount  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM students WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1)"))['t'] ?? 0;
$teacherCount  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM teachers WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1)"))['t'] ?? 0;
$courseCount   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM courses WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1)"))['t']  ?? 0;
$attendCount   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM attendance a JOIN students s ON a.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1)"))['t'] ?? 0;
$resultCount   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM results r JOIN students s ON r.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1)"))['t']   ?? 0;
$achCount      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM achievements a JOIN students s ON a.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1)"))['t'] ?? 0;

/* ─ Attendance Breakdown ──────────────────────── */
$presentCount  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM attendance a JOIN students s ON a.student_id=s.id WHERE (s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1)) AND a.status='Present'"))['t'] ?? 0;
$absentCount   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM attendance a JOIN students s ON a.student_id=s.id WHERE (s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1)) AND (a.status='Absent' OR a.status='Late')"))['t'] ?? 0;
$attendRate    = $attendCount > 0 ? round(($presentCount / $attendCount) * 100) : 0;

/* ─ Subject-wise marks (for chart) ───────────── */
$subjectMarks  = [];
$smQuery = mysqli_query($conn,"SELECT r.subject, ROUND(AVG(r.marks),1) as avg_marks FROM results r JOIN students s ON r.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1) GROUP BY r.subject ORDER BY avg_marks DESC LIMIT 6");
if($smQuery) {
    while($r = mysqli_fetch_assoc($smQuery)) {
        $subjectMarks[] = $r;
    }
}

/* ─ Gender Distribution ──────────────────────── */
$maleCount   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM students WHERE gender='Male' AND (college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1))"))['t'] ?? 0;
$femaleCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM students WHERE gender='Female' AND (college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1))"))['t'] ?? 0;

/* ─ Latest Students ──────────────────────────── */
$latestStudents = mysqli_query($conn,"SELECT * FROM students WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1) ORDER BY id DESC LIMIT 5");

/* ─ Upcoming Exams ───────────────────────────── */
$upcomingExams  = mysqli_query($conn,"SELECT * FROM marks_schedule WHERE exam_date >= CURDATE() ORDER BY exam_date ASC LIMIT 4");

/* ─ Latest Announcements ─────────────────────── */
$announcements  = mysqli_query($conn,"SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");

/* ─ Activity Feed ────────────────────────────── */
$activities = [];
$rStud = mysqli_query($conn,"SELECT full_name, created_at FROM students WHERE college_id = $userCollegeId OR (college_id IS NULL AND $userCollegeId = 1) ORDER BY id DESC LIMIT 3");
if($rStud) {
    while($s = mysqli_fetch_assoc($rStud)) {
        $activities[] = ['icon'=>'fa-user-plus','color'=>'var(--success)','text'=>'New student enrolled: <strong>'.htmlspecialchars($s['full_name']).'</strong>','time'=>isset($s['created_at']) ? date('M d, H:i',strtotime($s['created_at'])) : 'Recently'];
    }
}
$rAtt = mysqli_query($conn,"SELECT a.attendance_date FROM attendance a JOIN students s ON a.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1) ORDER BY a.id DESC LIMIT 1");
if($rAtt && $a = mysqli_fetch_assoc($rAtt)) $activities[] = ['icon'=>'fa-calendar-check','color'=>'var(--primary)','text'=>'Attendance marked for <strong>'.htmlspecialchars($a['attendance_date']).'</strong>','time'=>'Latest'];
$rRes = mysqli_query($conn,"SELECT r.subject FROM results r JOIN students s ON r.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1) ORDER BY r.id DESC LIMIT 1");
if($rRes && $r = mysqli_fetch_assoc($rRes)) $activities[] = ['icon'=>'fa-square-poll-vertical','color'=>'var(--warning)','text'=>'Result published: <strong>'.htmlspecialchars($r['subject']).'</strong>','time'=>'Latest'];
if(empty($activities)) {
    $activities[] = ['icon'=>'fa-circle-check','color'=>'var(--success)','text'=>'Campus CRM System initialized and ready','time'=>'Just now'];
}

/* ─ Top Performers (by avg marks) ────────────── */
$topStudents = mysqli_query($conn,"SELECT s.full_name, s.course, ROUND(AVG(r.marks),1) as avg FROM results r JOIN students s ON r.student_id=s.id WHERE s.college_id = $userCollegeId OR (s.college_id IS NULL AND $userCollegeId = 1) GROUP BY r.student_id ORDER BY avg DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Smart Campus CRM</title>
<meta name="description" content="Smart Campus CRM — Admin dashboard with real-time analytics, student management, and academic insights.">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Admin Dashboard"; include "../includes/topbar.php"; ?>

<!-- Welcome Header -->
<div class="page-header">
    <h1>Welcome Back, <?= htmlspecialchars($_SESSION['user']) ?> 👋</h1>
    <p>Here's your institute overview for <?= date('l, F j, Y') ?></p>
    <div class="header-actions">
        <a href="add_student.php" class="btn-crm-primary btn-crm-sm"><i class="fa-solid fa-user-plus"></i> Add Student</a>
        <a href="announcements.php" class="btn-crm-warning btn-crm-sm"><i class="fa-solid fa-bullhorn"></i> Announce</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="d-flex justify-content-between">
            <div class="icon"><i class="fa-solid fa-user-graduate"></i></div>
            <span class="trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Active</span>
        </div>
        <h2 class="counter" data-target="<?= $studentCount ?>">0</h2>
        <p>Total Students</p>
    </div>
    <div class="stat-card">
        <div class="d-flex justify-content-between">
            <div class="icon"><i class="fa-solid fa-chalkboard-user"></i></div>
            <span class="trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Faculty</span>
        </div>
        <h2 class="counter" data-target="<?= $teacherCount ?>">0</h2>
        <p>Total Teachers</p>
    </div>
    <div class="stat-card">
        <div class="d-flex justify-content-between">
            <div class="icon"><i class="fa-solid fa-book-open"></i></div>
        </div>
        <h2 class="counter" data-target="<?= $courseCount ?>">0</h2>
        <p>Active Courses</p>
    </div>
    <div class="stat-card">
        <div class="d-flex justify-content-between">
            <div class="icon"><i class="fa-solid fa-calendar-check"></i></div>
            <span class="trend <?= $attendRate >= 75 ? 'trend-up' : 'trend-down' ?>"><?= $attendRate ?>%</span>
        </div>
        <h2 class="counter" data-target="<?= $attendCount ?>">0</h2>
        <p>Attendance Records</p>
    </div>
    <div class="stat-card">
        <div class="d-flex justify-content-between">
            <div class="icon"><i class="fa-solid fa-chart-bar"></i></div>
        </div>
        <h2 class="counter" data-target="<?= $resultCount ?>">0</h2>
        <p>Results Published</p>
    </div>
    <div class="stat-card">
        <div class="d-flex justify-content-between">
            <div class="icon"><i class="fa-solid fa-trophy"></i></div>
            <span class="trend trend-up"><i class="fa-solid fa-star"></i></span>
        </div>
        <h2 class="counter" data-target="<?= $achCount ?>">0</h2>
        <p>Achievements Awarded</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-section">
    <a href="add_student.php" class="action-card">
        <i class="fa-solid fa-user-plus"></i>
        <h5>Add Student</h5><p>Register a new student</p>
    </a>
    <a href="add_teacher.php" class="action-card">
        <i class="fa-solid fa-chalkboard-user"></i>
        <h5>Add Teacher</h5><p>Add new faculty member</p>
    </a>
    <a href="mark_attendance.php" class="action-card">
        <i class="fa-solid fa-calendar-check"></i>
        <h5>Attendance</h5><p>Mark daily attendance</p>
    </a>
    <a href="achievements.php" class="action-card">
        <i class="fa-solid fa-trophy"></i>
        <h5>Achievements</h5><p>Award student badges</p>
    </a>
    <a href="announcements.php" class="action-card">
        <i class="fa-solid fa-bullhorn"></i>
        <h5>Announce</h5><p>Publish a notice</p>
    </a>
    <a href="../teacher/marks_schedule.php" class="action-card">
        <i class="fa-solid fa-calendar-days"></i>
        <h5>Exam Schedule</h5><p>Manage exam dates</p>
    </a>
</div>

<!-- Charts Row + Side Panels -->
<div class="bento-grid">

    <!-- Overview Bar Chart -->
    <div class="crm-card bento-col-7">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-chart-bar"></i> Institute Overview</h4>
        </div>
        <div class="crm-card-body">
            <canvas id="overviewChart" height="200"></canvas>
        </div>
    </div>

    <!-- Attendance Ring + Growth -->
    <div class="crm-card bento-col-5">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-circle-half-stroke"></i> Attendance Rate</h4>
        </div>
        <div class="crm-card-body text-center">
            <div class="progress-ring">
                <svg width="100" height="100" viewBox="0 0 100 100">
                    <circle class="ring-bg" cx="50" cy="50" r="41"/>
                    <circle class="ring-fill" id="attendRingFill" cx="50" cy="50" r="41"
                        stroke-dasharray="258" stroke-dashoffset="<?= 258 - (258 * $attendRate / 100) ?>"/>
                </svg>
                <div class="progress-ring-text">
                    <div class="pct"><?= $attendRate ?>%</div>
                    <div class="lbl">Present</div>
                </div>
            </div>
            <canvas id="attendDoughnut" height="140" class="mt-3"></canvas>
        </div>
    </div>

    <!-- Subject Avg Marks Chart -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-chart-line"></i> Subject Avg Marks</h4>
        </div>
        <div class="crm-card-body">
            <?php if(!empty($subjectMarks)): ?>
            <canvas id="subjectChart" height="200"></canvas>
            <?php else: ?>
            <p class="text-muted text-center py-4">No result data yet. Upload results to see subject analytics.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Performers Leaderboard -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-ranking-star"></i> Top Performers</h4>
            <a href="results.php" class="btn-crm-primary btn-crm-sm">View All</a>
        </div>
        <div class="crm-card-body">
        <?php if(mysqli_num_rows($topStudents) > 0): $rank=1; while($s=mysqli_fetch_assoc($topStudents)): ?>
        <div class="leaderboard-item">
            <div class="leaderboard-rank <?= $rank<=3 ? 'rank-'.$rank : 'rank-other' ?>"><?= $rank ?></div>
            <div class="leaderboard-name">
                <?= htmlspecialchars($s['full_name']) ?>
                <span class="leaderboard-sub"><?= htmlspecialchars($s['course']) ?></span>
            </div>
            <div class="leaderboard-score"><?= $s['avg'] ?><small>/100</small></div>
        </div>
        <?php $rank++; endwhile; else: ?>
        <p class="text-muted text-center py-4">No results data available yet.</p>
        <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Exams -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-calendar-days"></i> Upcoming Exams</h4>
            <a href="../teacher/marks_schedule.php" class="btn-crm-primary btn-crm-sm">All Exams</a>
        </div>
        <div class="crm-card-body">
            <div class="exam-schedule-list">
            <?php if(mysqli_num_rows($upcomingExams) > 0): while($ex=mysqli_fetch_assoc($upcomingExams)):
                $dLeft = (int)((strtotime($ex['exam_date'])-time())/86400); ?>
            <div class="exam-item <?= $ex['exam_type']=='Final Exam' ? 'exam-urgent' : ($dLeft<=7 ? 'exam-soon' : '') ?>">
                <div class="exam-date-badge">
                    <div class="day"><?= date('d',strtotime($ex['exam_date'])) ?></div>
                    <div class="month"><?= date('M',strtotime($ex['exam_date'])) ?></div>
                </div>
                <div class="exam-info">
                    <div class="subject"><?= htmlspecialchars($ex['subject']) ?></div>
                    <div class="meta">
                        <span><?= htmlspecialchars($ex['exam_type']) ?></span>
                        <span>Max: <?= $ex['max_marks'] ?></span>
                    </div>
                </div>
                <span class="exam-countdown <?= $dLeft<=3 ? 'urgent' : '' ?>"><?= $dLeft<=0 ? 'Today!' : "In {$dLeft}d" ?></span>
            </div>
            <?php endwhile; else: ?>
            <p class="text-muted text-center py-3">No upcoming exams. <a href="../teacher/marks_schedule.php">Schedule one</a></p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-bullhorn"></i> Latest Announcements</h4>
            <a href="announcements.php" class="btn-crm-primary btn-crm-sm">All</a>
        </div>
        <div class="crm-card-body">
            <div class="announcement-list">
            <?php
            $typeIcons=['urgent'=>['fa-triangle-exclamation','notice-urgent'],'info'=>['fa-circle-info','notice-info'],'success'=>['fa-circle-check','notice-success'],'general'=>['fa-bullhorn','']];
            if(mysqli_num_rows($announcements)>0): while($ann=mysqli_fetch_assoc($announcements)):
                $ti=$typeIcons[$ann['notice_type']]??$typeIcons['general']; ?>
            <div class="announcement-item <?= $ti[1] ?>">
                <h6><i class="fa-solid <?= $ti[0] ?>" style="margin-right:5px;"></i><?= htmlspecialchars($ann['title']) ?></h6>
                <p style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= htmlspecialchars($ann['content']) ?></p>
                <div class="announcement-meta">
                    <span><i class="fa-solid fa-clock"></i><?= date('M d',strtotime($ann['created_at'])) ?></span>
                </div>
            </div>
            <?php endwhile; else: ?>
            <p class="text-muted py-2">No announcements. <a href="announcements.php">Publish one</a></p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="crm-card bento-col-4">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-bolt"></i> Recent Activity</h4>
        </div>
        <div class="crm-card-body">
            <div class="activity-box" style="background:none;padding:0;box-shadow:none;border:none;margin:0;">
            <?php foreach($activities as $act): ?>
            <div class="activity">
                <i class="fa-solid <?= $act['icon'] ?>" style="background:transparent;color:<?= $act['color'] ?>;width:auto;height:auto;font-size:16px;"></i>
                <div class="activity-text">
                    <?= $act['text'] ?>
                    <span class="activity-time"><?= $act['time'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

</div><!-- /bento-grid -->

<!-- Latest Students Table -->
<div class="table-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Latest Registered Students</h4>
        <div class="crm-table-actions">
            <button class="btn-crm-export" onclick="exportTableToCSV('latestStudentsTable','latest_students.csv')"><i class="fa-solid fa-file-csv"></i> CSV</button>
            <button class="btn-crm-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
            <a href="students.php" class="btn-crm-primary btn-crm-sm">View All</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="latestStudentsTable">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if($latestStudents && mysqli_num_rows($latestStudents)>0):
                while($s=mysqli_fetch_assoc($latestStudents)): ?>
            <tr>
                <td><strong>#<?= $s['id'] ?></strong></td>
                <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><span class="badge-crm-enrolled"><?= htmlspecialchars($s['course'] ?: 'Unassigned') ?></span></td>
                <td><span class="badge-crm-graduated">Active</span></td>
                <td>
                    <a href="edit_student.php?id=<?= $s['id'] ?>" class="icon-btn icon-btn-blue"><i class="fa-solid fa-pen"></i></a>
                    <a href="delete_student.php?id=<?= $s['id'] ?>" class="icon-btn icon-btn-red" onclick="return confirm('Delete this student?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No students registered yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /main -->
<div id="toastContainer"></div>

<script>
// Counter animation
document.querySelectorAll('.counter').forEach(c=>{
    const target=+c.getAttribute('data-target'),speed=Math.max(1,Math.ceil(target/40));
    let count=0;
    (function tick(){if(count<target){count+=speed;if(count>target)count=target;c.innerText=count;setTimeout(tick,25);}else c.innerText=target;})();
});

// Overview Chart
new Chart(document.getElementById('overviewChart').getContext('2d'),{
    type:'bar',
    data:{
        labels:['Students','Teachers','Courses','Results','Achievements'],
        datasets:[{
            label:'Count',
            data:[<?= $studentCount ?>,<?= $teacherCount ?>,<?= $courseCount ?>,<?= $resultCount ?>,<?= $achCount ?>],
            backgroundColor:['rgba(37,99,235,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(139,92,246,0.8)','rgba(236,72,153,0.8)'],
            borderRadius:10,
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
});

// Attendance Doughnut
new Chart(document.getElementById('attendDoughnut').getContext('2d'),{
    type:'doughnut',
    data:{
        labels:['Present','Absent/Late'],
        datasets:[{
            data:[<?= max(1,$presentCount) ?>,<?= max(0,$absentCount) ?>],
            backgroundColor:['#10B981','#EF4444'],
            borderWidth:0,
        }]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},cutout:'65%'}
});

// Subject Avg Marks Chart
<?php if(!empty($subjectMarks)): ?>
new Chart(document.getElementById('subjectChart').getContext('2d'),{
    type:'bar',
    data:{
        labels:<?= json_encode(array_column($subjectMarks,'subject')) ?>,
        datasets:[{
            label:'Avg Marks',
            data:<?= json_encode(array_column($subjectMarks,'avg_marks')) ?>,
            backgroundColor:['rgba(37,99,235,0.7)','rgba(16,185,129,0.7)','rgba(245,158,11,0.7)','rgba(139,92,246,0.7)','rgba(236,72,153,0.7)','rgba(6,182,212,0.7)'],
            borderRadius:8,
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:100,ticks:{precision:0}}}}
});
<?php endif; ?>

// CSV export utility
function exportTableToCSV(tableId,filename){
    const rows=[...document.getElementById(tableId).querySelectorAll('tr')];
    const csv=rows.map(r=>[...r.querySelectorAll('th,td')].map(c=>'"'+c.innerText.replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);a.download=filename;a.click();
}
// Mobile sidebar
document.getElementById('mobileSidebarToggle')?.addEventListener('click',()=>{
    document.getElementById('mainSidebar').classList.toggle('open');
});
</script>
</body>
</html>