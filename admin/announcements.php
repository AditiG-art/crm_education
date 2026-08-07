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
        $title   = mysqli_real_escape_string($conn, $_POST['title']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $type    = mysqli_real_escape_string($conn, $_POST['notice_type']);
        $by      = mysqli_real_escape_string($conn, $_SESSION['user']);
        $exp     = $_POST['expires_date'] ? "'".mysqli_real_escape_string($conn,$_POST['expires_date'])."'" : 'NULL';
        $ok = mysqli_query($conn, "INSERT INTO announcements (title,content,notice_type,created_by,expires_date) VALUES ('$title','$content','$type','$by',$exp)");
        echo json_encode(['success'=>$ok]);
    } elseif($_POST['action']=='delete') {
        $id=(int)$_POST['id'];
        echo json_encode(['success'=>mysqli_query($conn,"DELETE FROM announcements WHERE id=$id")]);
    }
    exit();
}

// Fetch all announcements
$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM announcements"))['c'] ?? 0;
$urgent = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM announcements WHERE notice_type='urgent'"))['c'] ?? 0;

$typeIcons = [
    'urgent'  => ['icon'=>'fa-triangle-exclamation','cls'=>'notice-urgent','label'=>'Urgent'],
    'info'    => ['icon'=>'fa-circle-info','cls'=>'notice-info','label'=>'Info'],
    'success' => ['icon'=>'fa-circle-check','cls'=>'notice-success','label'=>'Notice'],
    'general' => ['icon'=>'fa-bullhorn','cls'=>'','label'=>'General'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Announcements | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Announcements"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-bullhorn"></i> Announcements & Notices</h1>
    <p>Publish and manage circulars, notices and important communications for the institute</p>
    <div class="header-actions">
        <button class="btn-crm-primary" onclick="document.getElementById('addAnnModal').classList.add('open')">
            <i class="fa-solid fa-plus"></i> New Notice
        </button>
    </div>
</div>

<!-- Stats -->
<div class="summary-row mb-4">
    <div class="summary-item">
        <div class="val" style="color:var(--primary);"><?= $total ?></div>
        <div class="lbl">Total Notices</div>
    </div>
    <div class="summary-item">
        <div class="val" style="color:var(--danger);"><?= $urgent ?></div>
        <div class="lbl">Urgent</div>
    </div>
    <?php $active = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM announcements WHERE expires_date >= CURDATE() OR expires_date IS NULL"))['c'] ?? 0; ?>
    <div class="summary-item">
        <div class="val" style="color:var(--success);"><?= $active ?></div>
        <div class="lbl">Active</div>
    </div>
    <?php $thisMonth = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM announcements WHERE MONTH(created_at)=MONTH(CURDATE())"))['c'] ?? 0; ?>
    <div class="summary-item">
        <div class="val" style="color:var(--purple);"><?= $thisMonth ?></div>
        <div class="lbl">This Month</div>
    </div>
</div>

<!-- Announcement List -->
<div class="section-title">
    <i class="fa-solid fa-newspaper"></i> Notice Board
</div>

<div class="announcement-list">
<?php if(mysqli_num_rows($announcements) > 0): while($ann = mysqli_fetch_assoc($announcements)): ?>
<?php $t = $typeIcons[$ann['notice_type']] ?? $typeIcons['general']; ?>
<div class="announcement-item <?= $t['cls'] ?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
        <div style="flex:1;">
            <h6>
                <i class="fa-solid <?= $t['icon'] ?>" style="margin-right:6px;"></i>
                <?= htmlspecialchars($ann['title']) ?>
                <span class="badge-crm-enrolled" style="margin-left:8px;font-size:10px;"><?= $t['label'] ?></span>
            </h6>
            <p><?= htmlspecialchars($ann['content']) ?></p>
            <div class="announcement-meta">
                <span><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($ann['created_by']) ?></span>
                <span><i class="fa-solid fa-clock"></i> <?= date('M d, Y H:i', strtotime($ann['created_at'])) ?></span>
                <?php if($ann['expires_date']): ?>
                <span><i class="fa-solid fa-hourglass-end"></i> Expires: <?= date('M d, Y', strtotime($ann['expires_date'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <button class="icon-btn icon-btn-red" onclick="deleteAnn(<?= $ann['id'] ?>)" title="Delete">
            <i class="fa-solid fa-trash"></i>
        </button>
    </div>
</div>
<?php endwhile; else: ?>
<div class="text-center text-muted py-5">
    <i class="fa-solid fa-newspaper fa-3x mb-3 d-block" style="color:var(--border);"></i>
    No announcements yet. Click <strong>New Notice</strong> to publish one.
</div>
<?php endif; ?>
</div>
</div>

<!-- Add Modal -->
<div class="crm-modal-overlay" id="addAnnModal">
    <div class="crm-modal">
        <div class="crm-modal-header">
            <h5><i class="fa-solid fa-bullhorn"></i> Publish New Notice</h5>
            <button class="crm-modal-close" onclick="document.getElementById('addAnnModal').classList.remove('open')">✕</button>
        </div>
        <div class="crm-modal-body">
            <form id="addAnnForm">
                <div class="form-group">
                    <label class="form-label">Notice Title</label>
                    <input type="text" class="form-control-crm" name="title" placeholder="Enter notice heading..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Content</label>
                    <textarea class="form-control-crm" name="content" rows="4" placeholder="Write the full announcement..." required></textarea>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Notice Type</label>
                        <select class="form-control-crm" name="notice_type">
                            <option value="general">General</option>
                            <option value="urgent">Urgent</option>
                            <option value="info">Info</option>
                            <option value="success">Notice</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expiry Date (optional)</label>
                        <input type="date" class="form-control-crm" name="expires_date">
                    </div>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button class="btn-crm-print" onclick="document.getElementById('addAnnModal').classList.remove('open')">Cancel</button>
            <button class="btn-crm-primary" onclick="submitAnn()"><i class="fa-solid fa-paper-plane"></i> Publish</button>
        </div>
    </div>
</div>
<div id="toastContainer"></div>
<script>
function submitAnn(){
    const data=new FormData(document.getElementById('addAnnForm'));
    data.append('action','add');
    fetch('announcements.php',{method:'POST',body:data}).then(r=>r.json()).then(res=>{
        if(res.success){showToast('Notice published!','success');setTimeout(()=>location.reload(),800);}
        else showToast('Failed to publish','error');
    });
}
function deleteAnn(id){
    if(!confirm('Delete this announcement?')) return;
    fetch('announcements.php',{method:'POST',body:new URLSearchParams({action:'delete',id})}).then(r=>r.json()).then(res=>{
        if(res.success){showToast('Deleted','success');setTimeout(()=>location.reload(),800);}
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
