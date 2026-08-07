<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "teacher") {
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];
$query = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email'");
$teacher = mysqli_fetch_assoc($query);

if(!$teacher) {
    $name_esc = mysqli_real_escape_string($conn, $_SESSION['user'] ?? 'Teacher');
    $email_esc = mysqli_real_escape_string($conn, $email);
    mysqli_query($conn, "INSERT INTO teachers (full_name, email, phone, subject, qualification) VALUES ('$name_esc', '$email_esc', '', '', '')");
    $query = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email'");
    $teacher = mysqli_fetch_assoc($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Profile | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
.profile-card{ background:white; padding:35px; border-radius:25px; box-shadow:0 10px 25px rgba(0,0,0,.08); }
.profile-header{ display:flex; align-items:center; gap:25px; margin-bottom:30px; }
.profile-icon{ height:100px; width:100px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#6f42c1); display:flex; align-items:center; justify-content:center; color:white; font-size:45px; }
.info-box{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px; }
.info-item{ background:#f8fafc; padding:18px; border-radius:15px; }
.info-item i{ color:#2563eb; margin-right:10px; }
@media(max-width:700px){ .info-box{ grid-template-columns:1fr; } }
</style>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Teacher Profile"; include "../includes/topbar.php"; ?>

<div class="profile-card mt-4">
    <div class="profile-header">
        <div class="profile-icon">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <h2><?= htmlspecialchars($teacher['full_name']) ?></h2>
            <p class="text-muted">Teacher Profile</p>
        </div>
    </div>

    <div class="info-box">
        <div class="info-item">
            <i class="fa-solid fa-envelope"></i> <strong>Email:</strong><br>
            <?= htmlspecialchars($teacher['email']) ?>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-phone"></i> <strong>Phone:</strong><br>
            <?= !empty($teacher['phone']) ? htmlspecialchars($teacher['phone']) : '<span class="text-muted">Not Provided</span>' ?>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-book"></i> <strong>Subject:</strong><br>
            <?= !empty($teacher['subject']) ? htmlspecialchars($teacher['subject']) : '<span class="text-muted">Not Assigned</span>' ?>
        </div>
        <div class="info-item">
            <i class="fa-solid fa-graduation-cap"></i> <strong>Qualification:</strong><br>
            <?= !empty($teacher['qualification']) ? htmlspecialchars($teacher['qualification']) : '<span class="text-muted">Not Provided</span>' ?>
        </div>
    </div>
</div>
</div>
</body>
</html>
