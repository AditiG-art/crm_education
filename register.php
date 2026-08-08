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
    --card-radius: 20px;
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
    max-width: 960px;
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
    font-size: 36px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 10px;
    letter-spacing: -0.5px;
}
.reg-header p {
    font-size: 15px;
    color: var(--text-muted);
    max-width: 540px;
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
    padding: 30px 24px;
    box-shadow: 0 6px 30px rgba(37,99,235,0.06);
    border: 2px solid transparent;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}

.role-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 45px rgba(37,99,235,0.15);
    border-color: var(--primary);
}

.role-card.selected {
    border-color: var(--primary);
    background: linear-gradient(180deg, #FFFFFF 0%, #F4F7FF 100%);
    box-shadow: 0 12px 40px rgba(37,99,235,0.18);
}

.role-icon-box {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 20px;
    transition: var(--transition);
}

.role-card:nth-child(1) .role-icon-box { background: #DBEAFE; color: #1D4ED8; }
.role-card:nth-child(2) .role-icon-box { background: #D1FAE5; color: #059669; }
.role-card:nth-child(3) .role-icon-box { background: #FEF3C7; color: #D97706; }
.role-card:nth-child(4) .role-icon-box { background: #EDE9FE; color: #7C3AED; }

.role-card:hover .role-icon-box {
    transform: scale(1.1) rotate(-4deg);
}

.role-title {
    font-family: 'Outfit', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.role-desc {
    font-size: 13.5px;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 20px;
    flex-grow: 1;
}

.role-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.badge-popular { background: #DBEAFE; color: #1E40AF; }
.badge-enterprise { background: #EDE9FE; color: #6D28D9; }

.role-footer {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--primary);
    transition: var(--transition);
}
.role-card:hover .role-footer i {
    transform: translateX(5px);
}

.radio-check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #CBD5E1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}
.role-card.selected .radio-check {
    border-color: var(--primary);
    background: var(--primary);
}
.role-card.selected .radio-check::after {
    content: '';
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

.action-bar {
    text-align: center;
}

.btn-crm-continue {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    border-radius: 14px;
    padding: 14px 40px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 25px rgba(37,99,235,0.3);
    transition: var(--transition);
    text-decoration: none;
}
.btn-crm-continue:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(37,99,235,0.4);
    color: white;
}

.login-link {
    margin-top: 24px;
    font-size: 14px;
    color: var(--text-muted);
}
.login-link a {
    color: var(--primary);
    font-weight: 700;
    text-decoration: none;
}
.login-link a:hover {
    text-decoration: underline;
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
            <i class="fa-solid fa-building-columns"></i> Smart Campus CRM
        </div>
        <h1>Create Your Account</h1>
        <p>Select your account type below to customize your registration form.</p>
    </div>

    <!-- Role Selection Form -->
    <form action="register_form.php" method="GET" id="roleForm">
        <input type="hidden" name="type" id="selectedRole" value="student">

        <div class="role-grid">

            <!-- Student Card -->
            <div class="role-card selected" onclick="selectRole('student', this)">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <span class="role-badge badge-popular">Popular</span>
                </div>
                <div class="role-title">
                    Student
                    <div class="radio-check"></div>
                </div>
                <div class="role-desc">
                    Join as a student to track your attendance, check subject marks, view timetables, and view academic achievements.
                </div>
                <div class="role-footer">
                    Register as Student <i class="fa-solid fa-arrow-right ms-auto"></i>
                </div>
            </div>

            <!-- Teacher Card -->
            <div class="role-card" onclick="selectRole('teacher', this)">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
                <div class="role-title">
                    Teacher / Faculty
                    <div class="radio-check"></div>
                </div>
                <div class="role-desc">
                    Register as an educator to manage class timetables, log student attendance, post exam marks, and issue achievements.
                </div>
                <div class="role-footer">
                    Register as Teacher <i class="fa-solid fa-arrow-right ms-auto"></i>
                </div>
            </div>

            <!-- Parent Card -->
            <div class="role-card" onclick="selectRole('parent', this)">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-people-roof"></i>
                    </div>
                </div>
                <div class="role-title">
                    Parent / Guardian
                    <div class="radio-check"></div>
                </div>
                <div class="role-desc">
                    Register as a parent to monitor your child's daily attendance, academic scorecard, and receive official campus notices.
                </div>
                <div class="role-footer">
                    Register as Parent <i class="fa-solid fa-arrow-right ms-auto"></i>
                </div>
            </div>

            <!-- New Institute Card -->
            <div class="role-card" onclick="selectRole('institute', this)">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <span class="role-badge badge-enterprise">Enterprise</span>
                </div>
                <div class="role-title">
                    New Institute
                    <div class="radio-check"></div>
                </div>
                <div class="role-desc">
                    Set up a new school, college, or coaching institute on Smart Campus CRM to manage students, teachers, and operations.
                </div>
                <div class="role-footer">
                    Register New Institute <i class="fa-solid fa-arrow-right ms-auto"></i>
                </div>
            </div>

        </div>

        <div class="action-bar">
            <button type="submit" class="btn-crm-continue">
                Continue to Registration <i class="fa-solid fa-arrow-right"></i>
            </button>
            <div class="login-link">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </div>

    </form>

</div>

<script>
function selectRole(role, cardElement) {
    document.getElementById('selectedRole').value = role;
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    cardElement.classList.add('selected');
}
</script>
</body>
</html>
