<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'admin')) {
    header("Location:../login.php"); exit();
}

// Handle AJAX Add/Delete
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if($_POST['action'] == 'add') {
        $day    = mysqli_real_escape_string($conn, $_POST['day_of_week']);
        $period = (int)$_POST['period_number'];
        $start  = mysqli_real_escape_string($conn, $_POST['start_time']);
        $end    = mysqli_real_escape_string($conn, $_POST['end_time']);
        $subj   = mysqli_real_escape_string($conn, $_POST['subject']);
        $course = mysqli_real_escape_string($conn, $_POST['course']);
        $room   = mysqli_real_escape_string($conn, $_POST['room']);
        $color  = mysqli_real_escape_string($conn, $_POST['color_class']);
        $ok = mysqli_query($conn, "INSERT INTO timetable (day_of_week,period_number,start_time,end_time,subject,course,room,color_class) VALUES ('$day',$period,'$start','$end','$subj','$course','$room','$color')");
        echo json_encode(['success' => $ok, 'id' => mysqli_insert_id($conn)]);
    } elseif($_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        $ok = mysqli_query($conn, "DELETE FROM timetable WHERE id=$id");
        echo json_encode(['success' => $ok]);
    }
    exit();
}

// Fetch all timetable entries
$ttQuery = mysqli_query($conn, "SELECT * FROM timetable ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), period_number");
$timetable = [];
while($row = mysqli_fetch_assoc($ttQuery)) {
    $timetable[$row['day_of_week']][$row['period_number']] = $row;
}

$days    = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$periods = [
    1 => ['label'=>'Period 1','time'=>'09:00–10:00'],
    2 => ['label'=>'Period 2','time'=>'10:00–11:00'],
    3 => ['label'=>'Period 3','time'=>'11:30–12:30'],
    4 => ['label'=>'Period 4','time'=>'12:30–13:30'],
    5 => ['label'=>'Period 5','time'=>'14:00–15:00'],
    6 => ['label'=>'Period 6','time'=>'15:00–16:00'],
];

$colorOptions = ['tt-blue','tt-green','tt-amber','tt-purple','tt-pink','tt-teal'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Timetable | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Class Timetable"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-table-cells"></i> Weekly Class Timetable</h1>
    <p>Manage and view your complete teaching schedule for the week</p>
    <div class="header-actions">
        <button class="btn-crm-primary" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Add Class
        </button>
        <button class="btn-crm-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print
        </button>
    </div>
</div>

<!-- Legend -->
<div class="d-flex gap-2 flex-wrap mb-4">
    <?php foreach(['tt-blue'=>'Computer Science','tt-green'=>'Mathematics','tt-amber'=>'Web Dev','tt-purple'=>'Database','tt-pink'=>'Statistics','tt-teal'=>'Data Science'] as $cls=>$lbl): ?>
    <span class="<?= $cls ?> tt-slot" style="min-height:auto;padding:5px 12px;border-radius:20px;font-size:11px;font-weight:700;"><?= $lbl ?></span>
    <?php endforeach; ?>
</div>

<!-- Timetable Grid -->
<div class="crm-card">
    <div class="crm-card-header">
        <h4><i class="fa-solid fa-calendar-week"></i> Weekly Schedule</h4>
        <small class="text-muted">Click any empty slot to add a class</small>
    </div>
    <div class="crm-card-body">
        <div class="timetable-wrapper">
            <div class="timetable-grid">
                <!-- Headers -->
                <div class="timetable-header" style="background:#1E293B;">Period</div>
                <?php foreach($days as $i=>$day): ?>
                <div class="timetable-header tt-<?= strtolower(substr($day,0,3)) ?>" style="background:transparent;color:var(--text-primary);border:1px solid var(--border);">
                    <div style="font-size:11px;font-weight:800;color:var(--primary);"><?= strtoupper(substr($day,0,3)) ?></div>
                    <div style="font-size:10px;color:var(--text-muted);font-weight:400;"><?= $day ?></div>
                </div>
                <?php endforeach; ?>

                <?php foreach($periods as $pnum => $pinfo): ?>
                <!-- Period label -->
                <div class="timetable-period">
                    <strong style="font-size:12px;"><?= $pinfo['label'] ?></strong>
                    <small><?= $pinfo['time'] ?></small>
                </div>
                <!-- Day slots -->
                <?php foreach($days as $day): ?>
                <?php if(isset($timetable[$day][$pnum])): $slot = $timetable[$day][$pnum]; ?>
                <div class="tt-slot <?= htmlspecialchars($slot['color_class']) ?>" onclick="confirmDelete(<?= $slot['id'] ?>)">
                    <span class="subj"><?= htmlspecialchars($slot['subject']) ?></span>
                    <span class="room"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($slot['room']) ?></span>
                    <span style="font-size:9px;opacity:0.7;"><?= htmlspecialchars($slot['course']) ?></span>
                    <span class="position-absolute" style="top:6px;right:6px;font-size:10px;opacity:0.5;" title="Click to remove">✕</span>
                </div>
                <?php else: ?>
                <div class="tt-slot-empty" onclick="openAddModal('<?= $day ?>',<?= $pnum ?>)">
                    <i class="fa-solid fa-plus" style="font-size:14px;"></i>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Classes Today -->
<?php
$today = date('l');
$todaySlots = isset($timetable[$today]) ? $timetable[$today] : [];
?>
<?php if(!empty($todaySlots)): ?>
<div class="crm-card mt-4">
    <div class="crm-card-header">
        <h4><i class="fa-solid fa-clock"></i> Today's Classes — <?= $today ?></h4>
    </div>
    <div class="crm-card-body">
        <div class="exam-schedule-list">
        <?php foreach($todaySlots as $pnum => $slot): ?>
        <div class="exam-item">
            <div class="exam-date-badge">
                <div class="day" style="font-size:14px;"><?= 'P'.$pnum ?></div>
                <div class="month"><?= $periods[$pnum]['time'] ?? '' ?></div>
            </div>
            <div class="exam-info">
                <div class="subject"><?= htmlspecialchars($slot['subject']) ?></div>
                <div class="meta">
                    <span><i class="fa-solid fa-book-open"></i> <?= htmlspecialchars($slot['course']) ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($slot['room']) ?></span>
                </div>
            </div>
            <span class="exam-countdown"><i class="fa-solid fa-play"></i> Active</span>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /main -->

<!-- Add Class Modal -->
<div class="crm-modal-overlay" id="addModal">
    <div class="crm-modal">
        <div class="crm-modal-header">
            <h5><i class="fa-solid fa-plus-circle"></i> Add Class to Timetable</h5>
            <button class="crm-modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="crm-modal-body">
            <form id="addClassForm">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Day of Week</label>
                        <select class="form-control-crm" id="modal_day" name="day_of_week" required>
                            <?php foreach($days as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Period</label>
                        <select class="form-control-crm" id="modal_period" name="period_number" required>
                            <?php foreach($periods as $pnum => $pinfo): ?>
                            <option value="<?= $pnum ?>"><?= $pinfo['label'] ?> (<?= $pinfo['time'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control-crm" name="start_time" value="09:00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control-crm" name="end_time" value="10:00" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject Name</label>
                    <input type="text" class="form-control-crm" name="subject" placeholder="e.g. Data Structures" required>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Course / Class</label>
                        <input type="text" class="form-control-crm" name="course" placeholder="e.g. Computer Science">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Room / Location</label>
                        <input type="text" class="form-control-crm" name="room" placeholder="e.g. Lab A-101">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Slot Colour</label>
                    <select class="form-control-crm" name="color_class">
                        <?php foreach($colorOptions as $c): ?>
                        <option value="<?= $c ?>"><?= ucfirst(str_replace('tt-','',$c)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="crm-modal-footer">
            <button class="btn-crm-print" onclick="closeModal()">Cancel</button>
            <button class="btn-crm-primary" onclick="submitAddClass()"><i class="fa-solid fa-save"></i> Save Class</button>
        </div>
    </div>
</div>
<div id="toastContainer"></div>

<script>
function openAddModal(day='', period=1) {
    document.getElementById('addModal').classList.add('open');
    if(day) document.getElementById('modal_day').value = day;
    if(period) document.getElementById('modal_period').value = period;
}
function closeModal() { document.getElementById('addModal').classList.remove('open'); }
function submitAddClass() {
    const form = document.getElementById('addClassForm');
    const data = new FormData(form);
    data.append('action','add');
    fetch('timetable.php', {method:'POST', body:data})
        .then(r=>r.json()).then(res=>{
            if(res.success) { showToast('Class added!','success'); setTimeout(()=>location.reload(),800); }
            else showToast('Failed to add class','error');
        });
}
function confirmDelete(id) {
    if(!confirm('Remove this class from the timetable?')) return;
    fetch('timetable.php', {method:'POST', body: new URLSearchParams({action:'delete',id})})
        .then(r=>r.json()).then(res=>{
            if(res.success) { showToast('Class removed','success'); setTimeout(()=>location.reload(),800); }
        });
}
function showToast(msg, type='info') {
    const c = document.getElementById('toastContainer');
    const t = document.createElement('div');
    const icons = {success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};
    t.className = `crm-toast toast-${type}`;
    t.innerHTML = `<i class="fa-solid ${icons[type]||'fa-bell'}" style="color:var(--${type=='success'?'success':type=='error'?'danger':'primary'})"></i><span class="toast-text">${msg}</span>`;
    c.appendChild(t);
    setTimeout(()=>t.remove(), 3000);
}
// Mobile sidebar toggle
document.getElementById('mobileSidebarToggle')?.addEventListener('click',()=>{
    document.getElementById('mainSidebar').classList.toggle('open');
});
</script>
</body>
</html>
