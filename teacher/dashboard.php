<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user']) || $_SESSION['role'] != "teacher") {
    header("Location:../login.php"); exit();
}

/* ─ Stats ─────────────────────────────────────── */
$studentCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM students"))['t'] ?? 0;
$courseCount  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM courses"))['t']  ?? 0;
$attendCount  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM attendance"))['t'] ?? 0;
$resultCount  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM results"))['t']   ?? 0;
$presentCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS t FROM attendance WHERE status='Present'"))['t'] ?? 0;
$attendRate   = $attendCount > 0 ? round(($presentCount/$attendCount)*100) : 0;

/* ─ Today's Timetable ────────────────────────── */
$today     = date('l');
$todaySlots = mysqli_query($conn,"SELECT * FROM timetable WHERE day_of_week='$today' ORDER BY period_number");
$todayCount = mysqli_num_rows($todaySlots);

/* ─ Upcoming Exams ───────────────────────────── */
$upcomingExams = mysqli_query($conn,"SELECT * FROM marks_schedule WHERE exam_date >= CURDATE() ORDER BY exam_date ASC LIMIT 3");

/* ─ Recent Students ──────────────────────────── */
$recentStudents = mysqli_query($conn,"SELECT * FROM students ORDER BY id DESC LIMIT 5");

/* ─ Subject Avg Marks (for chart) ─────────────── */
$subjectAvg = [];
$sqr = mysqli_query($conn,"SELECT subject, ROUND(AVG(marks),1) as avg FROM results GROUP BY subject ORDER BY avg DESC LIMIT 5");
while($r = mysqli_fetch_assoc($sqr)) $subjectAvg[] = $r;

/* ─ Top Students ─────────────────────────────── */
$topStudents = mysqli_query($conn,"SELECT s.full_name, s.course, ROUND(AVG(r.marks),1) as avg FROM results r JOIN students s ON r.student_id=s.id GROUP BY r.student_id ORDER BY avg DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Teacher Dashboard"; include "../includes/topbar.php"; ?>

<!-- Welcome Header -->
<div class="page-header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']) ?> 👋</h1>
    <p>Manage your teaching schedule, student performance, and exam dates — <?= date('l, F j, Y') ?></p>
    <div class="header-actions">
        <a href="timetable.php" class="btn-crm-primary btn-crm-sm"><i class="fa-solid fa-table-cells"></i> View Timetable</a>
        <a href="marks_schedule.php" class="btn-crm-warning btn-crm-sm"><i class="fa-solid fa-calendar-days"></i> Exam Schedule</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-user-graduate"></i></div>
        <h2 class="counter" data-target="<?= $studentCount ?>">0</h2>
        <p>Total Students</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-book-open"></i></div>
        <h2 class="counter" data-target="<?= $courseCount ?>">0</h2>
        <p>Active Courses</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-calendar-check"></i></div>
        <h2 class="counter" data-target="<?= $attendCount ?>">0</h2>
        <p>Attendance Logs</p>
        <span class="trend <?= $attendRate >= 75 ? 'trend-up' : 'trend-down' ?>"><?= $attendRate ?>% Rate</span>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-chart-bar"></i></div>
        <h2 class="counter" data-target="<?= $resultCount ?>">0</h2>
        <p>Results Entered</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-table-cells"></i></div>
        <h2 class="counter" data-target="<?= $todayCount ?>">0</h2>
        <p>Classes Today</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-section">
    <a href="timetable.php" class="action-card">
        <i class="fa-solid fa-table-cells"></i>
        <h5>My Timetable</h5><p>View weekly class schedule</p>
    </a>
    <a href="marks_schedule.php" class="action-card">
        <i class="fa-solid fa-calendar-days"></i>
        <h5>Marks Schedule</h5><p>Manage exam dates</p>
    </a>
    <a href="../admin/mark_attendance.php" class="action-card">
        <i class="fa-solid fa-calendar-check"></i>
        <h5>Mark Attendance</h5><p>Record daily attendance</p>
    </a>
    <a href="../admin/results.php" class="action-card">
        <i class="fa-solid fa-upload"></i>
        <h5>Upload Results</h5><p>Enter student marks</p>
    </a>
    <a href="../admin/students.php" class="action-card">
        <i class="fa-solid fa-user-graduate"></i>
        <h5>Student Roster</h5><p>View all students</p>
    </a>
</div>

<!-- Bento Grid -->
<div class="bento-grid">

    <!-- Today's Timetable -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-clock"></i> Today's Schedule — <?= $today ?></h4>
            <a href="timetable.php" class="btn-crm-primary btn-crm-sm">Full Timetable</a>
        </div>
        <div class="crm-card-body">
            <?php if($todayCount > 0): ?>
            <div class="exam-schedule-list">
            <?php while($slot = mysqli_fetch_assoc($todaySlots)): ?>
            <div class="exam-item">
                <div class="exam-date-badge">
                    <div class="day" style="font-size:14px;">P<?= $slot['period_number'] ?></div>
                    <div class="month"><?= date('H:i', strtotime($slot['start_time'])) ?></div>
                </div>
                <div class="exam-info">
                    <div class="subject"><?= htmlspecialchars($slot['subject']) ?></div>
                    <div class="meta">
                        <span><i class="fa-solid fa-book-open"></i> <?= htmlspecialchars($slot['course']) ?></span>
                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($slot['room']) ?></span>
                    </div>
                </div>
                <span class="exam-countdown"><i class="fa-solid fa-play"></i> <?= date('H:i',strtotime($slot['start_time'])) ?>–<?= date('H:i',strtotime($slot['end_time'])) ?></span>
            </div>
            <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-moon fa-2x mb-2 d-block"></i>
                No classes scheduled for today.
                <br><a href="timetable.php" class="btn-crm-primary btn-crm-sm mt-2" style="display:inline-flex;">Add Classes</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Exams -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-calendar-days"></i> Upcoming Exams</h4>
            <a href="marks_schedule.php" class="btn-crm-primary btn-crm-sm">Manage</a>
        </div>
        <div class="crm-card-body">
            <div class="exam-schedule-list">
            <?php if(mysqli_num_rows($upcomingExams) > 0): while($ex = mysqli_fetch_assoc($upcomingExams)):
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
            <p class="text-muted text-center py-3">No upcoming exams. <a href="marks_schedule.php">Schedule one</a></p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Subject Performance Chart -->
    <div class="crm-card bento-col-7">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-chart-bar"></i> Subject Performance (Avg Marks)</h4>
        </div>
        <div class="crm-card-body">
            <?php if(!empty($subjectAvg)): ?>
            <canvas id="subjectChart" height="200"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-chart-bar fa-2x mb-2 d-block" style="color:var(--border);"></i>
                No marks data yet. Upload results to see subject performance.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Students Leaderboard -->
    <div class="crm-card bento-col-5">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-ranking-star"></i> Top Students</h4>
        </div>
        <div class="crm-card-body">
            <div class="leaderboard-list">
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
            <p class="text-muted text-center py-3">No results data yet.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Student Roster -->
    <div class="crm-card bento-col-12">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-users"></i> Student Roster</h4>
            <div class="crm-table-actions">
                <button class="btn-crm-export" onclick="exportTableToCSV('studentTable','students.csv')"><i class="fa-solid fa-file-csv"></i> CSV</button>
                <a href="../admin/students.php" class="btn-crm-primary btn-crm-sm">View All</a>
            </div>
        </div>
        <div class="crm-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="studentTable">
                    <thead><tr><th>#</th><th>Name</th><th>Course</th><th>Email</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php while($s = mysqli_fetch_assoc($recentStudents)): ?>
                    <tr>
                        <td><strong>#<?= $s['id'] ?></strong></td>
                        <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                        <td><span class="badge-crm-enrolled"><?= htmlspecialchars($s['course'] ?: 'Unassigned') ?></span></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><span class="badge-crm-graduated">Active</span></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div><!-- /bento-grid -->
</div><!-- /main -->
<div id="toastContainer"></div>

<script>
document.querySelectorAll('.counter').forEach(c=>{
    const target=+c.getAttribute('data-target'),speed=Math.max(1,Math.ceil(target/40));
    let count=0;
    (function tick(){if(count<target){count+=speed;if(count>target)count=target;c.innerText=count;setTimeout(tick,25);}else c.innerText=target;})();
});

<?php if(!empty($subjectAvg)): ?>
new Chart(document.getElementById('subjectChart').getContext('2d'),{
    type:'horizontalBar'||'bar',
    type:'bar',
    data:{
        labels:<?= json_encode(array_column($subjectAvg,'subject')) ?>,
        datasets:[{
            label:'Avg Marks',
            data:<?= json_encode(array_column($subjectAvg,'avg')) ?>,
            backgroundColor:['rgba(37,99,235,0.8)','rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(139,92,246,0.8)','rgba(236,72,153,0.8)'],
            borderRadius:10,
        }]
    },
    options:{
        indexAxis:'y',
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{x:{beginAtZero:true,max:100}}
    }
});
<?php endif; ?>

function exportTableToCSV(tableId,filename){
    const rows=[...document.getElementById(tableId).querySelectorAll('tr')];
    const csv=rows.map(r=>[...r.querySelectorAll('th,td')].map(c=>'"'+c.innerText.replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);a.download=filename;a.click();
}
document.getElementById('mobileSidebarToggle')?.addEventListener('click',()=>{
    document.getElementById('mainSidebar').classList.toggle('open');
});
</script>
</body>
</html>