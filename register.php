<?php
session_start();
include "config/db.php";

if (isset($_SESSION['user']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == "admin") {
        header("Location: admin/dashboard.php"); exit();
    } elseif ($_SESSION['role'] == "teacher") {
        header("Location: teacher/dashboard.php"); exit();
    } elseif ($_SESSION['role'] == "student") {
        header("Location: student/dashboard.php"); exit();
    } elseif ($_SESSION['role'] == "parent") {
        header("Location: parent/dashboard.php"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Account Type | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #2563EB;
    --primary-dark: #1E40AF;
    --primary-light: #EFF6FF;
    --accent: #F59E0B;
    --body-bg: #F0F4FF;
    --text-dark: #0F172A;
    --text-muted: #64748B;
    --card-radius: 24px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }

body {
    background: var(--body-bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content:'';
    position: fixed;
    top: -150px; left: -150px;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    z-index: 0;
}
body::after {
    content:'';
    position: fixed;
    bottom: -150px; right: -150px;
    width: 550px; height: 550px;
    background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    z-index: 0;
}

.reg-container {
    max-width: 980px;
    width: 100%;
    position: relative;
    z-index: 1;
}

.reg-header {
    text-align: center;
    margin-bottom: 40px;
}
.brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 8px 18px;
    border-radius: 30px;
    box-shadow: 0 4px 20px rgba(37,99,235,0.08);
    font-size: 13px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 16px;
    border: 1px solid rgba(37,99,235,0.15);
}
.brand-badge i { font-size: 16px; color: var(--accent); }

.reg-header h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 38px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 10px;
    letter-spacing: -0.5px;
}
.reg-header p {
    font-size: 16px;
    color: var(--text-muted);
    max-width: 580px;
    margin: 0 auto;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 36px;
}

.role-card {
    background: white;
    border-radius: var(--card-radius);
    padding: 32px;
    border: 2px solid transparent;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: var(--transition);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.role-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(37,99,235,0.15);
    border-color: var(--primary);
}

.role-icon-wrapper {
    width: 64px; height: 64px;
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 20px;
    transition: var(--transition);
}

.role-student .role-icon-wrapper { background: #EFF6FF; color: #2563EB; }
.role-parent  .role-icon-wrapper { background: #FEF3C7; color: #D97706; }
.role-teacher .role-icon-wrapper { background: #ECFDF5; color: #059669; }
.role-inst    .role-icon-wrapper { background: #F3E8FF; color: #7C3AED; }

.role-card h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
}
.role-card p {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 24px;
    flex-grow: 1;
}

.role-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    font-size: 14px;
    color: var(--primary);
}
.role-action i {
    transition: transform 0.2s ease;
}
.role-card:hover .role-action i {
    transform: translateX(4px);
}

.bottom-login-link {
    text-align: center;
    font-size: 14px;
    color: var(--text-muted);
}
.bottom-login-link a {
    color: var(--primary);
    font-weight: 700;
    text-decoration: none;
}

@media (max-width: 768px) {
    .role-grid { grid-template-columns: 1fr; }
    .reg-header h1 { font-size: 28px; }
}
</style>
</head>
<body>

<div class="reg-container">

    <!-- Header -->
    <div class="reg-header">
        <div class="brand-badge">
            <i class="fa-solid fa-sparkles"></i> Smart Campus Operating System
        </div>
        <h1>Choose Your Account Type ✨</h1>
        <p>Select how you will be using Smart Campus to get started with your customized portal</p>
    </div>

    <!-- Role Selection Cards Grid -->
    <div class="role-grid">

        <!-- Student -->
        <a href="register_form.php?type=student" class="role-card role-student">
            <div class="role-icon-wrapper">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <h3>Student Account</h3>
            <p>Enroll in courses, view your attendance records, track CGPA & exam transcripts, and access learning schedules.</p>
            <div class="role-action">
                <span>Sign Up as Student</span>
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <!-- Parent -->
        <a href="register_form.php?type=parent" class="role-card role-parent">
            <div class="role-icon-wrapper">
                <i class="fa-solid fa-hands-holding-child"></i>
            </div>
            <h3>Parent Account</h3>
            <p>Monitor your child's academic health, check daily class attendance, view test reports, and stay connected.</p>
            <div class="role-action">
                <span>Sign Up as Parent</span>
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <!-- Teacher -->
        <a href="register_form.php?type=teacher" class="role-card role-teacher">
            <div class="role-icon-wrapper">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <h3>Teacher / Faculty</h3>
            <p>Manage assigned subjects, mark daily class attendance, upload student test scores, and organize timetables.</p>
            <div class="role-action">
                <span>Sign Up as Teacher</span>
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

        <!-- Register University / College -->
        <a href="register_form.php?type=institute" class="role-card role-inst">
            <div class="role-icon-wrapper">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h3>Register University / College</h3>
            <p>Create a brand-new institution campus portal with 100% clean data, administrative control tower, and campus settings.</p>
            <div class="role-action">
                <span>Register New Institution</span>
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </a>

    </div>

    <div class="bottom-login-link">
        Already registered on Smart Campus? <a href="login.php">Sign In to Your Account</a>
    </div>

</div>

</body>
</html>
