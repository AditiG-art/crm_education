<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user']) || ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin')) {
    header("Location:../login.php"); exit();
}

// Handle Add / Delete
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if($_POST['action']=='add') {
        $subj  = mysqli_real_escape_string($conn, $_POST['subject']);
        $type  = mysqli_real_escape_string($conn, $_POST['exam_type']);
        $date  = mysqli_real_escape_string($conn, $_POST['exam_date']);
        $max   = (int)$_POST['max_marks'];
        $course = mysqli_real_escape_string($conn, $_POST['course']);
        $desc  = mysqli_real_escape_string($conn, $_POST['description']);
        $ok = mysqli_query($conn, "INSERT INTO marks_schedule (subject,exam_type,exam_date,max_marks,course,description) VALUES ('$subj','$type','$date',$max,'$course','$desc')");
        echo json_encode(['success'=>$ok]);
    } elseif($_POST['action']=='delete') {
        $id = (int)$_POST['id'];
        echo json_encode(['success'=>mysqli_query($conn, "DELETE FROM marks_schedule WHERE id=$id")]);
    }
    exit();
}

// Fetch schedules
$upcoming = mysqli_query($conn, "SELECT * FROM marks_schedule WHERE exam_date >= CURDATE() ORDER BY exam_date ASC");
$past     = mysqli_query($conn, "SELECT * FROM marks_schedule WHERE exam_date < CURDATE() ORDER BY exam_date DESC LIMIT 5");

$examTypeColors = [
    'Unit Test'  => 'exam-item',
    'Mid Term'   => 'exam-item exam-soon',
    'Final Exam' => 'exam-item exam-urgent',
    'Assignment' => 'exam-item',
    'Quiz'       => 'exam-item',
];
$examTypeIcons = [
    'Unit Test'  => 'fa-file-pen',
    'Mid Term'   => 'fa-file-circle-check',
    'Final Exam' => 'fa-graduation-cap',
    'Assignment' => 'fa-folder-open',
    'Quiz'       => 'fa-question-circle',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Marks Schedule | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Marks & Exam Schedule"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-calendar-days"></i> Marks & Exam Schedule</h1>
    <p>View and manage upcoming examinations, unit tests, assignments and quizzes</p>
    <div class="header-actions">
        <button class="btn-crm-primary" onclick="document.getElementById('addExamModal').classList.add('open')">
            <i class="fa-solid fa-plus"></i> Schedule Exam
        </button>
        <button class="btn-crm-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print
        </button>
    </div>
</div>

<!-- Legend row -->
<div class="summary-row mb-4">
    <?php
    $counts = [];
    $allExams = mysqli_query($conn, "SELECT exam_type, COUNT(*) as cnt FROM marks_schedule GROUP BY exam_type");
    while($r = mysqli_fetch_assoc($allExams)) $counts[$r['exam_type']] = $r['cnt'];
    $upcomingCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM marks_schedule WHERE exam_date >= CURDATE()"))['c'] ?? 0;
    $pastCount     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM marks_schedule WHERE exam_date < CURDATE()"))['c'] ?? 0;
    ?>
    <div class="summary-item">
        <div class="val" style="color:var(--primary);"><?= $upcomingCount ?></div>
        <div class="lbl">Upcoming</div>
    </div>
    <div class="summary-item">
        <div class="val" style="color:var(--danger);"><?= $counts['Final Exam'] ?? 0 ?></div>
        <div class="lbl">Final Exams</div>
    </div>
    <div class="summary-item">
        <div class="val" style="color:var(--warning);"><?= $counts['Mid Term'] ?? 0 ?></div>
        <div class="lbl">Mid Terms</div>
    </div>
    <div class="summary-item">
        <div class="val" style="color:var(--success);"><?= $counts['Unit Test'] ?? 0 ?></div>
        <div class="lbl">Unit Tests</div>
    </div>
    <div class="summary-item">
        <div class="val" style="color:var(--text-muted);"><?= $pastCount ?></div>
        <div class="lbl">Completed</div>
    </div>
</div>

<!-- Upcoming Exams -->
<div class="crm-card mb-4">
    <div class="crm-card-header">
        <h4><i class="fa-solid fa-clock"></i> Upcoming Examinations</h4>
        <span class="badge bg-primary rounded-pill" style="font-size:11px;"><?= $upcomingCount ?> scheduled</span>
    </div>
    <div class="crm-card-body">
        <div class="exam-schedule-list">
        <?php if(mysqli_num_rows($upcoming) > 0): mysqli_data_seek($upcoming,0); while($ex = mysqli_fetch_assoc($upcoming)): ?>
        <?php
            $daysLeft = (int)((strtotime($ex['exam_date']) - time()) / 86400);
            $cls = $examTypeColors[$ex['exam_type']] ?? 'exam-item';
            if($daysLeft <= 3 && $ex['exam_type']=='Final Exam') $cls .= ' exam-urgent';
            $icon = $examTypeIcons[$ex['exam_type']] ?? 'fa-file';
        ?>
        <div class="<?= $cls ?>">
            <div class="exam-date-badge">
                <div class="day"><?= date('d', strtotime($ex['exam_date'])) ?></div>
                <div class="month"><?= date('M', strtotime($ex['exam_date'])) ?></div>
            </div>
            <div class="exam-info">
                <div class="subject"><i class="fa-solid <?= $icon ?>"></i> <?= htmlspecialchars($ex['subject']) ?></div>
                <div class="meta">
                    <span><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($ex['exam_type']) ?></span>
                    <span><i class="fa-solid fa-book-open"></i> <?= htmlspecialchars($ex['course']) ?></span>
                    <span><i class="fa-solid fa-star"></i> Max: <?= (int)$ex['max_marks'] ?> marks</span>
                </div>
                <?php if($ex['description']): ?>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:4px;"><?= htmlspecialchars($ex['description']) ?></div>
                <?php endif; ?>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                <span class="exam-countdown <?= $daysLeft <= 3 ? 'urgent' : '' ?>">
                    <?= $daysLeft <= 0 ? 'Today!' : "In {$daysLeft}d" ?>
                </span>
                <button class="icon-btn icon-btn-red" onclick="deleteExam(<?= $ex['id'] ?>)" title="Remove">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="text-center text-muted py-4">
            <i class="fa-solid fa-calendar-xmark fa-2x mb-2 d-block"></i>
            No upcoming exams scheduled.
        </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<!-- Past Exams -->
<div class="crm-card">
    <div class="crm-card-header">
        <h4><i class="fa-solid fa-history"></i> Recently Completed</h4>
    </div>
    <div class="crm-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr>
                    <th>Subject</th><th>Type</th><th>Date</th><th>Max Marks</th><th>Course</th>
                </tr></thead>
                <tbody>
                <?php if(mysqli_num_rows($past) > 0): while($ex = mysqli_fetch_assoc($past)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ex['subject']) ?></strong></td>
                    <td><span class="badge-crm-enrolled"><?= htmlspecialchars($ex['exam_type']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($ex['exam_date'])) ?></td>
                    <td><?= (int)$ex['max_marks'] ?></td>
                    <td><?= htmlspecialchars($ex['course']) ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" class="text-center text-muted">No past exams found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- Add Exam Modal -->
<div class="crm-modal-overlay" id="addExamModal">
    <div class="crm-modal">
        <div class="crm-modal-header">
            <h5><i class="fa-solid fa-calendar-plus"></i> Schedule New Exam</h5>
            <button class="crm-modal-close" onclick="document.getElementById('addExamModal').classList.remove('open')">✕</button>
        </div>
        <div class="crm-modal-body">
            <form id="addExamForm">
                <div class="form-group">
                    <label class="form-label">Subject Name</label>
                    <input type="text" class="form-control-crm" name="subject" placeholder="e.g. Data Structures" required>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Exam Type</label>
                        <select class="form-control-crm" name="exam_type">
                            <option>Unit Test</option>
                            <option>Mid Term</option>
                            <option>Final Exam</option>
                            <option>Assignment</option>
                            <option>Quiz</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Exam Date</label>
                        <input type="date" class="form-control-crm" name="exam_date" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Marks</label>
                        <input type="number" class="form-control-crm" name="max_marks" value="100" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Course</label>
                        <input type="text" class="form-control-crm" name="course" placeholder="e.g. Computer Science">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description / Syllabus</label>
                    <textarea class="form-control-crm" name="description" rows="2" placeholder="Topics covered..."></textarea>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button class="btn-crm-print" onclick="document.getElementById('addExamModal').classList.remove('open')">Cancel</button>
            <button class="btn-crm-primary" onclick="submitExam()"><i class="fa-solid fa-save"></i> Save</button>
        </div>
    </div>
</div>
<div id="toastContainer"></div>
<script>
function submitExam() {
    const data = new FormData(document.getElementById('addExamForm'));
    data.append('action','add');
    fetch('marks_schedule.php',{method:'POST',body:data}).then(r=>r.json()).then(res=>{
        if(res.success){showToast('Exam scheduled!','success');setTimeout(()=>location.reload(),800);}
        else showToast('Failed to save','error');
    });
}
function deleteExam(id) {
    if(!confirm('Remove this exam from the schedule?')) return;
    fetch('marks_schedule.php',{method:'POST',body:new URLSearchParams({action:'delete',id})}).then(r=>r.json()).then(res=>{
        if(res.success){showToast('Removed','success');setTimeout(()=>location.reload(),800);}
    });
}
function showToast(msg,type='info'){
    const c=document.getElementById('toastContainer');
    const t=document.createElement('div');
    const icons={success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};
    t.className=`crm-toast toast-${type}`;
    t.innerHTML=`<i class="fa-solid ${icons[type]}" style="color:var(--${type=='success'?'success':type=='error'?'danger':'primary'})"></i><span class="toast-text">${msg}</span>`;
    c.appendChild(t);setTimeout(()=>t.remove(),3000);
}
document.getElementById('mobileSidebarToggle')?.addEventListener('click',()=>{
    document.getElementById('mainSidebar').classList.toggle('open');
});
</script>
</body>
</html>
