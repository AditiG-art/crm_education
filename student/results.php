<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user']) || $_SESSION['role'] != "student") {
    header("Location:../login.php"); exit();
}

$email = $_SESSION['email'];
$studentQ = mysqli_query($conn, "SELECT * FROM students WHERE email='".mysqli_real_escape_string($conn,$email)."'");
$student  = mysqli_fetch_assoc($studentQ);
if(!$student) die("Student profile not found.");
$sid = $student['id'];

// Results
$resultQuery = mysqli_query($conn, "SELECT * FROM results WHERE student_id='$sid' ORDER BY id DESC");
$avgData     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT AVG(marks) AS avg, COUNT(*) as cnt, MAX(marks) AS top, MIN(marks) AS low FROM results WHERE student_id='$sid'"));
$average     = round($avgData['avg'] ?? 0, 1);
$subjCount   = $avgData['cnt'] ?? 0;
$topScore    = $avgData['top'] ?? 0;
$lowScore    = $avgData['low'] ?? 0;
$cgpa        = round($average/10, 2);

// Subject marks for charts
$subjects = []; $marks = []; $grades = [];
$chartQ = mysqli_query($conn,"SELECT subject, marks, grade FROM results WHERE student_id='$sid'");
while($r = mysqli_fetch_assoc($chartQ)) {
    $subjects[] = $r['subject'];
    $marks[]    = (int)$r['marks'];
    $grades[]   = $r['grade'];
}

// Grade distribution
$gradeDist = [];
$gradeQ = mysqli_query($conn,"SELECT grade, COUNT(*) as cnt FROM results WHERE student_id='$sid' GROUP BY grade");
while($r = mysqli_fetch_assoc($gradeQ)) {
    $gradeDist[$r['grade']] = $r['cnt'];
}

// Pass/Fail count
$passCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM results WHERE student_id='$sid' AND marks>=40"))['c'] ?? 0;
$failCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM results WHERE student_id='$sid' AND marks<40"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Results | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "My Academic Results"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-chart-bar"></i> Academic Results & Transcript</h1>
    <p>Subject-wise performance, grade analysis, and academic charts for <?= htmlspecialchars($student['full_name']) ?></p>
    <div class="header-actions">
        <button class="btn-crm-export" onclick="exportTableToCSV('resultsTable','transcript_<?= htmlspecialchars($student['full_name']) ?>.csv')">
            <i class="fa-solid fa-file-csv"></i> Export CSV
        </button>
        <button class="btn-crm-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-star"></i></div>
        <h2><?= $cgpa ?><small style="font-size:16px;color:var(--text-muted);">/10</small></h2>
        <p>Current CGPA</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-chart-line"></i></div>
        <h2><?= $average ?>%</h2>
        <p>Average Score</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-arrow-trend-up"></i></div>
        <h2><?= $topScore ?><small style="font-size:14px;color:var(--text-muted);">/100</small></h2>
        <p>Highest Score</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-book-open"></i></div>
        <h2><?= $subjCount ?></h2>
        <p>Subjects Evaluated</p>
    </div>
    <div class="stat-card">
        <div class="icon"><i class="fa-solid fa-circle-check"></i></div>
        <h2 style="color:var(--success);"><?= $passCount ?></h2>
        <p>Subjects Passed</p>
        <?php if($failCount > 0): ?>
        <span class="trend trend-down"><?= $failCount ?> Failed</span>
        <?php endif; ?>
    </div>
</div>

<!-- Charts Row -->
<div class="bento-grid">

    <!-- Marks Bar Chart -->
    <div class="crm-card bento-col-8">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-chart-bar"></i> Subject-wise Marks</h4>
            <span style="font-size:11px;color:var(--text-muted);">Pass line: 40 marks</span>
        </div>
        <div class="crm-card-body">
            <?php if(!empty($subjects)): ?>
            <canvas id="marksBarChart" height="220"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-chart-bar fa-3x mb-3 d-block" style="color:var(--border);"></i>
                No results uploaded yet.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grade Doughnut -->
    <div class="crm-card bento-col-4">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-circle-half-stroke"></i> Grade Distribution</h4>
        </div>
        <div class="crm-card-body text-center">
            <?php if(!empty($gradeDist)): ?>
            <canvas id="gradeChart" height="220"></canvas>
            <?php else: ?>
            <p class="text-muted py-4">No data yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Performance Trend -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-chart-line"></i> Performance Trend</h4>
        </div>
        <div class="crm-card-body">
            <?php if(!empty($subjects)): ?>
            <canvas id="trendChart" height="200"></canvas>
            <?php else: ?>
            <p class="text-muted text-center py-4">No data yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pass/Fail Doughnut -->
    <div class="crm-card bento-col-6">
        <div class="crm-card-header">
            <h4><i class="fa-solid fa-circle-check"></i> Pass / Fail Summary</h4>
        </div>
        <div class="crm-card-body text-center">
            <?php if($subjCount > 0): ?>
            <canvas id="passfailChart" height="200"></canvas>
            <div class="d-flex justify-content-center gap-3 mt-3" style="font-size:13px;">
                <span><strong style="color:var(--success);"><?= $passCount ?></strong> Passed</span>
                <span><strong style="color:var(--danger);"><?= $failCount ?></strong> Failed</span>
            </div>
            <?php else: ?>
            <p class="text-muted py-4">No data yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /bento-grid -->

<!-- Results Table -->
<div class="table-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Detailed Marks Breakdown</h4>
        <div class="crm-table-actions">
            <button class="btn-crm-export" onclick="exportTableToCSV('resultsTable','transcript.csv')"><i class="fa-solid fa-file-csv"></i> CSV</button>
            <button class="btn-crm-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="resultsTable">
            <thead>
                <tr><th>#</th><th>Subject</th><th>Marks / 100</th><th>Grade</th><th>Progress</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php
            if($resultQuery && mysqli_num_rows($resultQuery) > 0):
                $rowNum = 1;
                while($row = mysqli_fetch_assoc($resultQuery)):
                $pct = min(100, (int)$row['marks']);
                $barColor = $pct >= 75 ? 'var(--success)' : ($pct >= 40 ? 'var(--warning)' : 'var(--danger)');
            ?>
            <tr>
                <td><?= $rowNum++ ?></td>
                <td><strong><?= htmlspecialchars($row['subject']) ?></strong></td>
                <td>
                    <strong style="font-size:16px;"><?= htmlspecialchars($row['marks']) ?></strong>
                    <small class="text-muted">/ 100</small>
                </td>
                <td>
                    <span style="background:var(--primary);color:white;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">
                        <?= htmlspecialchars($row['grade']) ?>
                    </span>
                </td>
                <td style="min-width:120px;">
                    <div style="background:#F1F5F9;border-radius:20px;height:8px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:<?= $barColor ?>;border-radius:20px;transition:width 1s;"></div>
                    </div>
                </td>
                <td>
                    <?php if($row['grade']==='F' || $pct < 40): ?>
                    <span class="badge-crm-failed"><i class="fa-solid fa-xmark"></i> Failed</span>
                    <?php else: ?>
                    <span class="badge-crm-passed"><i class="fa-solid fa-check"></i> Passed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center text-muted py-5">
                <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                No results found. Your teacher will upload marks after each examination.
            </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div><!-- /main -->

<script>
<?php if(!empty($subjects)): ?>
const labels=<?= json_encode($subjects) ?>;
const marksData=<?= json_encode($marks) ?>;

// Marks Bar Chart
new Chart(document.getElementById('marksBarChart').getContext('2d'),{
    type:'bar',
    data:{
        labels,
        datasets:[
            {label:'Marks',data:marksData,backgroundColor:marksData.map(v=>v>=75?'rgba(16,185,129,0.8)':v>=40?'rgba(245,158,11,0.8)':'rgba(239,68,68,0.8)'),borderRadius:8},
            {type:'line',label:'Pass Line',data:Array(labels.length).fill(40),borderColor:'rgba(239,68,68,0.5)',borderDash:[6,4],borderWidth:2,pointRadius:0,fill:false}
        ]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,max:105,ticks:{precision:0}}}}
});

// Trend Line
new Chart(document.getElementById('trendChart').getContext('2d'),{
    type:'line',
    data:{
        labels,
        datasets:[{
            label:'Marks',data:marksData,
            borderColor:'#2563EB',backgroundColor:'rgba(37,99,235,0.1)',
            borderWidth:3,tension:0.4,fill:true,pointBackgroundColor:'#2563EB',pointRadius:5
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:100}}}
});
<?php endif; ?>

<?php if(!empty($gradeDist)): ?>
new Chart(document.getElementById('gradeChart').getContext('2d'),{
    type:'doughnut',
    data:{
        labels:<?= json_encode(array_keys($gradeDist)) ?>,
        datasets:[{data:<?= json_encode(array_values($gradeDist)) ?>,backgroundColor:['#2563EB','#10B981','#F59E0B','#8B5CF6','#EF4444','#06B6D4'],borderWidth:0}]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11}}}},cutout:'60%'}
});
<?php endif; ?>

<?php if($subjCount > 0): ?>
new Chart(document.getElementById('passfailChart').getContext('2d'),{
    type:'doughnut',
    data:{
        labels:['Passed','Failed'],
        datasets:[{data:[<?= $passCount ?>,<?= $failCount ?>],backgroundColor:['#10B981','#EF4444'],borderWidth:0}]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom'}},cutout:'65%'}
});
<?php endif; ?>

function exportTableToCSV(tableId,filename){
    const rows=[...document.getElementById(tableId).querySelectorAll('tr')];
    const csv=rows.map(r=>[...r.querySelectorAll('th,td')].map(c=>'"'+c.innerText.trim().replace(/"/g,'""')+'"').join(',')).join('\n');
    const a=document.createElement('a');a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);a.download=filename;a.click();
}
document.getElementById('mobileSidebarToggle')?.addEventListener('click',()=>{
    document.getElementById('mainSidebar').classList.toggle('open');
});
</script>
</body>
</html>