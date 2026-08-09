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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #2563EB;
    --primary-glow: #3B82F6;
    --accent: #F59E0B;
    --body-bg: #0B1120;
    --card-bg: #1E293B;
    --card-border: rgba(255, 255, 255, 0.1);
    --text-dark: #F8FAFC;
    --text-muted: #94A3B8;
    --badge-bg: rgba(30, 41, 59, 0.85);
    --badge-border: rgba(59, 130, 246, 0.35);
    --card-radius: 24px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

[data-theme="light"] {
    --body-bg: #F0F4FF;
    --card-bg: #FFFFFF;
    --card-border: #E2E8F0;
    --text-dark: #0F172A;
    --text-muted: #64748B;
    --badge-bg: #FFFFFF;
    --badge-border: rgba(37,99,235,0.2);
}

* { margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif; }

body {
    background-color: var(--body-bg);
    color: var(--text-dark);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
    position: relative;
    overflow-x: hidden;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Ambient Background Lights */
.glow-ambient {
    position: fixed;
    border-radius: 50%;
    filter: blur(140px);
    pointer-events: none;
    z-index: 0;
}
.glow-1 {
    width: 500px; height: 500px;
    background: rgba(37, 99, 235, 0.22);
    top: -100px; left: -100px;
}
.glow-2 {
    width: 450px; height: 450px;
    background: rgba(139, 92, 246, 0.18);
    bottom: -100px; right: -100px;
}

/* Theme Switcher Button */
.theme-toggle-wrapper {
    position: fixed;
    top: 24px; right: 24px;
    z-index: 100;
}
.theme-toggle-btn {
    background: var(--badge-bg);
    border: 1px solid var(--badge-border);
    color: var(--text-dark);
    font-weight: 600;
    font-size: 14px;
    padding: 10px 18px;
    border-radius: 50px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    transition: var(--transition);
}
.theme-toggle-btn:hover {
    transform: translateY(-2px);
    border-color: var(--primary-glow);
}

.reg-container {
    max-width: 980px;
    width: 100%;
    position: relative;
    z-index: 1;
}

.reg-header {
    text-align: center;
    margin-bottom: 44px;
}
.brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--badge-bg);
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 700;
    color: #60A5FA;
    margin-bottom: 20px;
    border: 1px solid var(--badge-border);
    backdrop-filter: blur(8px);
}
.brand-badge i { font-size: 16px; color: var(--accent); }

.reg-header h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 42px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}
.reg-header p {
    font-size: 16.5px;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.role-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.role-card {
    background: var(--card-bg);
    border-radius: var(--card-radius);
    padding: 34px;
    border: 1px solid var(--card-border);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
    transition: var(--transition);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.role-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 45px rgba(37, 99, 235, 0.25);
    border-color: rgba(96, 165, 250, 0.5);
}

.role-icon-wrapper {
    width: 64px; height: 64px;
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin-bottom: 24px;
    transition: var(--transition);
}

.role-student .role-icon-wrapper { background: rgba(37, 99, 235, 0.15); color: #60A5FA; }
.role-parent  .role-icon-wrapper { background: rgba(245, 158, 11, 0.15); color: #FBBF24; }
.role-teacher .role-icon-wrapper { background: rgba(16, 185, 129, 0.15); color: #34D399; }
.role-inst    .role-icon-wrapper { background: rgba(139, 92, 246, 0.15); color: #C084FC; }

.role-card h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 23px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 10px;
}
.role-card p {
    font-size: 14.5px;
    color: var(--text-muted);
    line-height: 1.65;
    margin-bottom: 28px;
    flex-grow: 1;
}

.role-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    font-size: 14.5px;
    color: #60A5FA;
}
.role-action i {
    transition: transform 0.2s ease;
}
.role-card:hover .role-action i {
    transform: translateX(5px);
}

.bottom-login-link {
    text-align: center;
    font-size: 15px;
    color: var(--text-muted);
}
.bottom-login-link a {
    color: #60A5FA;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.2s;
}
.bottom-login-link a:hover {
    color: white;
}

@media (max-width: 768px) {
    .role-grid { grid-template-columns: 1fr; }
    .reg-header h1 { font-size: 32px; }
}
</style>
</head>
<body data-theme="dark">

<!-- Ambient Glow Backgrounds -->
<div class="glow-ambient glow-1"></div>
<div class="glow-ambient glow-2"></div>

<!-- Theme Switcher Button -->
<div class="theme-toggle-wrapper">
    <button type="button" id="themeToggleBtn" class="theme-toggle-btn">
        <i class="fa-solid fa-sun text-warning" id="themeIcon"></i>
        <span id="themeText">Light Mode</span>
    </button>
</div>

<div class="reg-container">

    <!-- Header -->
    <div class="reg-header">
        <div class="brand-badge">
            <i class="fa-solid fa-sparkles"></i> Smart Campus Operating System
        </div>
        <h1>Choose Your Account Type ✨</h1>
        <p>Select how you will be using Smart Campus to access your dedicated role portal</p>
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

<script>
const themeToggleBtn = document.getElementById('themeToggleBtn');
const themeIcon      = document.getElementById('themeIcon');
const themeText      = document.getElementById('themeText');

// Check saved theme from localStorage
const savedTheme = localStorage.getItem('smart_campus_theme') || 'dark';
setTheme(savedTheme);

function setTheme(theme) {
    document.body.setAttribute('data-theme', theme);
    localStorage.setItem('smart_campus_theme', theme);
    if(theme === 'dark') {
        themeIcon.className = 'fa-solid fa-sun text-warning';
        themeText.innerText = 'Light Mode';
    } else {
        themeIcon.className = 'fa-solid fa-moon text-primary';
        themeText.innerText = 'Dark Mode';
    }
}

themeToggleBtn.addEventListener('click', () => {
    const currentTheme = document.body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    setTheme(newTheme);
});
</script>

</body>
</html>
