<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "admin")
{
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
$stmt->bind_param("s", $email);
$stmt->execute();
$adminUser = $stmt->get_result()->fetch_assoc();

if(!$adminUser)
{
    $adminUser = [
        'full_name' => $_SESSION['user'],
        'email' => $_SESSION['email'],
        'phone' => 'N/A',
        'role' => 'Administrator',
        'status' => 'Active'
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Profile | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
.profile-container{
    background:white;
    padding:35px;
    border-radius:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.profile-header{
    text-align:center;
    margin-bottom:30px;
}
.profile-icon{
    height:120px;
    width:120px;
    border-radius:50%;
    background:linear-gradient(135deg,#2563eb,#7c3aed);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:55px;
    margin:auto;
}
.profile-header h2{
    margin-top:20px;
    font-weight:700;
}
.info-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}
.info-card{
    background:#f8fafc;
    padding:20px;
    border-radius:15px;
}
.info-card span{
    color:#64748b;
    font-size:14px;
}
.info-card h5{
    margin-top:8px;
    font-weight:600;
}
@media(max-width:768px){
    .info-grid{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Admin Profile";
include "../includes/topbar.php";
?>

<div class="profile-container mt-4">
    <div class="profile-header">
        <div class="profile-icon">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <h2><?php echo htmlspecialchars($adminUser['full_name']); ?></h2>
        <p class="text-primary font-weight-bold">System Administrator</p>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <span>Full Name</span>
            <h5><?php echo htmlspecialchars($adminUser['full_name']); ?></h5>
        </div>
        <div class="info-card">
            <span>Email Address</span>
            <h5><?php echo htmlspecialchars($adminUser['email']); ?></h5>
        </div>
        <div class="info-card">
            <span>Phone Number</span>
            <h5><?php echo htmlspecialchars($adminUser['phone'] ?? 'N/A'); ?></h5>
        </div>
        <div class="info-card">
            <span>Role</span>
            <h5><span class="badge bg-success">Administrator</span></h5>
        </div>
        <div class="info-card">
            <span>Account Status</span>
            <h5><span class="badge bg-info text-dark"><?php echo htmlspecialchars($adminUser['status'] ?? 'Active'); ?></span></h5>
        </div>
        <div class="info-card">
            <span>Joined Date</span>
            <h5><?php echo htmlspecialchars($adminUser['created_at'] ?? 'N/A'); ?></h5>
        </div>
    </div>
</div>

</div>

</body>
</html>