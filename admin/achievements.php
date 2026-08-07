<?php
session_start();
include "../config/db.php";
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location:../login.php"); exit();
}

// Handle Add / Delete
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if($_POST['action']=='add') {
        $sid   = (int)$_POST['student_id'];
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $desc  = mysqli_real_escape_string($conn, $_POST['description']);
        $badge = mysqli_real_escape_string($conn, $_POST['badge_type']);
        $icon  = mysqli_real_escape_string($conn, $_POST['badge_icon']);
        $date  = date('Y-m-d');
        $ok = mysqli_query($conn, "INSERT INTO achievements (student_id,title,description,badge_type,badge_icon,awarded_date) VALUES ($sid,'$title','$desc','$badge','$icon','$date')");
        echo json_encode(['success'=>$ok]);
    } elseif($_POST['action']=='delete') {
        $id=(int)$_POST['id'];
        echo json_encode(['success'=>mysqli_query($conn,"DELETE FROM achievements WHERE id=$id")]);
    }
    exit();
}

// Fetch data
$achievements = mysqli_query($conn, "SELECT a.*, s.full_name, s.course FROM achievements a JOIN students s ON a.student_id=s.id ORDER BY a.awarded_date DESC");
$students     = mysqli_query($conn, "SELECT id,full_name FROM students ORDER BY full_name");

$badgeIcons = [
    'fa-trophy'          => '🏆 Trophy',
    'fa-star'            => '⭐ Star',
    'fa-medal'           => '🏅 Medal',
    'fa-calendar-check'  => '📅 Attendance',
    'fa-chart-line'      => '📈 Growth',
    'fa-award'           => '🎖 Award',
    'fa-fire'            => '🔥 Top Performer',
    'fa-graduation-cap'  => '🎓 Graduate',
];

// Stats
$totalAch = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM achievements"))['c'] ?? 0;
$goldCount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM achievements WHERE badge_type='gold'"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Achievements | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Academic Achievements"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-trophy"></i> Academic Achievements</h1>
    <p>Award and manage academic badges, honours and recognition for outstanding students</p>
    <div class="header-actions">
        <button class="btn-crm-primary" onclick="document.getElementById('addAchModal').classList.add('open')">
            <i class="fa-solid fa-plus"></i> Award Badge
        </button>
    </div>
</div>

<!-- Stats Row -->
<div class="summary-row mb-4">
    <div class="summary-item">
        <div class="val" style="color:var(--primary);"><?= $totalAch ?></div>
        <div class="lbl">Total Awarded</div>
    </div>
    <div class="summary-item">
        <div class="val" style="color:#B45309;"><?= $goldCount ?></div>
        <div class="lbl">Gold Badges</div>
    </div>
    <?php
    $stu_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT student_id) as c FROM achievements"))['c'] ?? 0;
    ?>
    <div class="summary-item">
        <div class="val" style="color:var(--success);"><?= $stu_count ?></div>
        <div class="lbl">Students Recognised</div>
    </div>
    <?php
    $thisMonth = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM achievements WHERE MONTH(awarded_date)=MONTH(CURDATE())"))['c'] ?? 0;
    ?>
    <div class="summary-item">
        <div class="val" style="color:var(--purple);"><?= $thisMonth ?></div>
        <div class="lbl">This Month</div>
    </div>
</div>

<!-- Achievement Cards Grid -->
<div class="section-title">
    <i class="fa-solid fa-star"></i> Achievement Showcase
    <span class="sub"><?= $totalAch ?> total badges awarded</span>
</div>

<div class="achievements-grid mb-4">
<?php if(mysqli_num_rows($achievements) > 0): mysqli_data_seek($achievements,0); while($ach = mysqli_fetch_assoc($achievements)): ?>
<div class="achievement-card">
    <div class="achievement-badge-icon badge-<?= htmlspecialchars($ach['badge_type']) ?>">
        <i class="fa-solid <?= htmlspecialchars($ach['badge_icon']) ?>"></i>
    </div>
    <h6><?= htmlspecialchars($ach['title']) ?></h6>
    <p><?= htmlspecialchars($ach['description']) ?></p>
    <div class="student-name">
        <i class="fa-solid fa-user-graduate"></i> <?= htmlspecialchars($ach['full_name']) ?>
    </div>
    <div style="margin-top:6px;font-size:10px;color:var(--text-muted);">
        <i class="fa-solid fa-book-open"></i> <?= htmlspecialchars($ach['course']) ?>
        &nbsp;·&nbsp;
        <i class="fa-solid fa-calendar"></i> <?= date('M d, Y',strtotime($ach['awarded_date'])) ?>
    </div>
    <button class="icon-btn icon-btn-red mt-2" onclick="deleteAch(<?= $ach['id'] ?>)">
        <i class="fa-solid fa-trash"></i>
    </button>
</div>
<?php endwhile; else: ?>
<div class="text-center text-muted py-5" style="grid-column: 1/-1;">
    <i class="fa-solid fa-trophy fa-3x mb-3 d-block" style="color:var(--border);"></i>
    No achievements awarded yet. Click <strong>Award Badge</strong> to get started!
</div>
<?php endif; ?>
</div>

<!-- Leaderboard -->
<div class="crm-card">
    <div class="crm-card-header">
        <h4><i class="fa-solid fa-ranking-star"></i> Achievement Leaderboard</h4>
    </div>
    <div class="crm-card-body">
        <div class="leaderboard-list">
        <?php
        $lb = mysqli_query($conn,"SELECT s.full_name, s.course, COUNT(a.id) as badge_count FROM achievements a JOIN students s ON a.student_id=s.id GROUP BY a.student_id ORDER BY badge_count DESC LIMIT 10");
        $rank = 1;
        if(mysqli_num_rows($lb) > 0): while($r = mysqli_fetch_assoc($lb)): ?>
        <div class="leaderboard-item">
            <div class="leaderboard-rank <?= $rank<=3 ? 'rank-'.$rank : 'rank-other' ?>"><?= $rank ?></div>
            <div class="leaderboard-name">
                <?= htmlspecialchars($r['full_name']) ?>
                <span class="leaderboard-sub"><?= htmlspecialchars($r['course']) ?></span>
            </div>
            <div class="leaderboard-score">
                <?= $r['badge_count'] ?> <i class="fa-solid fa-trophy" style="font-size:11px;"></i>
            </div>
        </div>
        <?php $rank++; endwhile; else: ?>
        <p class="text-muted text-center py-3">No data yet.</p>
        <?php endif; ?>
        </div>
    </div>
</div>
</div>

<!-- Add Achievement Modal -->
<div class="crm-modal-overlay" id="addAchModal">
    <div class="crm-modal">
        <div class="crm-modal-header">
            <h5><i class="fa-solid fa-trophy"></i> Award Achievement Badge</h5>
            <button class="crm-modal-close" onclick="document.getElementById('addAchModal').classList.remove('open')">✕</button>
        </div>
        <div class="crm-modal-body">
            <form id="addAchForm">
                <div class="form-group">
                    <label class="form-label">Select Student</label>
                    <select class="form-control-crm" name="student_id" required>
                        <option value="">— Choose Student —</option>
                        <?php mysqli_data_seek($students,0); while($s=mysqli_fetch_assoc($students)): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Achievement Title</label>
                    <input type="text" class="form-control-crm" name="title" placeholder="e.g. Class Topper, Perfect Attendance" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control-crm" name="description" placeholder="Brief description of the achievement">
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Badge Colour</label>
                        <select class="form-control-crm" name="badge_type">
                            <option value="gold">Gold</option>
                            <option value="silver">Silver</option>
                            <option value="bronze">Bronze</option>
                            <option value="blue">Blue</option>
                            <option value="green">Green</option>
                            <option value="purple">Purple</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Badge Icon</label>
                        <select class="form-control-crm" name="badge_icon">
                            <?php foreach($badgeIcons as $cls=>$lbl): ?>
                            <option value="<?= $cls ?>"><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button class="btn-crm-print" onclick="document.getElementById('addAchModal').classList.remove('open')">Cancel</button>
            <button class="btn-crm-primary" onclick="submitAch()"><i class="fa-solid fa-award"></i> Award</button>
        </div>
    </div>
</div>
<div id="toastContainer"></div>
<script>
function submitAch(){
    const data=new FormData(document.getElementById('addAchForm'));
    data.append('action','add');
    fetch('achievements.php',{method:'POST',body:data}).then(r=>r.json()).then(res=>{
        if(res.success){showToast('Badge awarded!','success');setTimeout(()=>location.reload(),800);}
        else showToast('Failed','error');
    });
}
function deleteAch(id){
    if(!confirm('Remove this achievement?')) return;
    fetch('achievements.php',{method:'POST',body:new URLSearchParams({action:'delete',id})}).then(r=>r.json()).then(res=>{
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
