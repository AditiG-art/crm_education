<?php
session_start();
include "config/db.php";

if (isset($_SESSION['user']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == "admin") {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == "teacher") {
        header("Location: teacher/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == "student") {
        header("Location: student/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == "parent") {
        header("Location: parent/dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Campus | Integrated Educational Ecosystem</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #2563EB;
  --primary-dark: #1E40AF;
  --accent: #F59E0B;
  --dark-bg: #0F172A;
  --card-bg: #FFFFFF;
}

body {
  font-family: 'Inter', sans-serif;
  background-color: #F8FAFC;
  color: #0F172A;
  overflow-x: hidden;
}

/* Navbar */
.landing-nav {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #E2E8F0;
  padding: 16px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}
.brand-logo {
  font-family: 'Outfit', sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: var(--dark-bg);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
}
.brand-logo i {
  width: 40px; height: 40px;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

/* Hero Section */
.hero-section {
  padding: 90px 0 70px;
  background: radial-gradient(100% 100% at 50% 0%, #EFF6FF 0%, #F8FAFC 100%);
  position: relative;
  overflow: hidden;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  color: var(--primary);
  font-size: 13px;
  font-weight: 600;
  padding: 6px 16px;
  border-radius: 50px;
  margin-bottom: 24px;
}
.hero-title {
  font-family: 'Outfit', sans-serif;
  font-size: 54px;
  font-weight: 800;
  line-height: 1.15;
  color: var(--dark-bg);
  margin-bottom: 20px;
}
.hero-title span {
  background: linear-gradient(135deg, var(--primary), #3B82F6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.hero-subtitle {
  font-size: 18px;
  color: #475569;
  max-width: 640px;
  margin: 0 auto 36px;
  line-height: 1.6;
}
.btn-primary-hero {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  font-weight: 600;
  padding: 14px 32px;
  border-radius: 14px;
  border: none;
  box-shadow: 0 8px 24px rgba(37,99,235,0.3);
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}
.btn-primary-hero:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(37,99,235,0.4);
  color: white;
}
.btn-outline-hero {
  background: white;
  color: #334155;
  font-weight: 600;
  padding: 14px 32px;
  border-radius: 14px;
  border: 1px solid #CBD5E1;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}
.btn-outline-hero:hover {
  background: #F1F5F9;
  color: var(--dark-bg);
}

/* Role Quick Cards */
.role-cards-section {
  margin-top: -40px;
  position: relative;
  z-index: 10;
}
.role-card {
  background: white;
  border-radius: 20px;
  padding: 28px 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 8px 30px rgba(0,0,0,0.05);
  transition: all 0.3s;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.role-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(37,99,235,0.12);
  border-color: #BFDBFE;
}
.role-icon {
  width: 56px; height: 56px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px;
  margin-bottom: 20px;
}
.role-student .role-icon { background: #EFF6FF; color: #2563EB; }
.role-parent  .role-icon { background: #FEF3C7; color: #D97706; }
.role-teacher .role-icon { background: #ECFDF5; color: #059669; }
.role-admin   .role-icon { background: #F3E8FF; color: #7C3AED; }

.role-card h4 {
  font-family: 'Outfit', sans-serif;
  font-weight: 700;
  font-size: 20px;
  margin-bottom: 8px;
}
.role-card p {
  color: #64748B;
  font-size: 14px;
  margin-bottom: 20px;
  flex-grow: 1;
}
.role-link {
  font-weight: 600;
  font-size: 14px;
  color: var(--primary);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Feature Grid */
.features-section {
  padding: 90px 0;
}
.section-header {
  text-align: center;
  max-width: 600px;
  margin: 0 auto 50px;
}
.section-header h2 {
  font-family: 'Outfit', sans-serif;
  font-weight: 800;
  font-size: 36px;
}
.feature-box {
  background: white;
  border-radius: 20px;
  padding: 32px;
  border: 1px solid #E2E8F0;
  height: 100%;
  transition: all 0.3s;
}
.feature-box:hover {
  border-color: #BFDBFE;
  box-shadow: 0 12px 32px rgba(37,99,235,0.08);
}
.feature-icon-wrapper {
  width: 48px; height: 48px;
  border-radius: 14px;
  background: #EFF6FF;
  color: var(--primary);
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  margin-bottom: 20px;
}

/* Footer */
footer {
  background: var(--dark-bg);
  color: rgba(255,255,255,0.7);
  padding: 40px 0 30px;
  border-top: 1px solid rgba(255,255,255,0.1);
}
footer a { color: white; text-decoration: none; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="landing-nav">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Smart Campus</span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="login.php" class="btn btn-link text-dark fw-semibold text-decoration-none px-3">Sign In</a>
            <a href="register.php" class="btn-primary-hero py-2 px-4" style="font-size:14px;">Register Now</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <div class="hero-badge mx-auto">
            <i class="fa-solid fa-sparkles text-warning"></i> Next-Gen Educational Portal
        </div>
        <h1 class="hero-title">Welcome to <span>Smart Campus</span></h1>
        <p class="hero-subtitle">
            An integrated digital ecosystem connecting Students, Parents, Teachers, and Administrators seamlessly in real-time.
        </p>
        <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
            <a href="login.php" class="btn-primary-hero">
                Portal Sign In <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="register.php" class="btn-outline-hero">
                Create Account <i class="fa-solid fa-user-plus"></i>
            </a>
        </div>
    </div>
</section>

<!-- Role Quick Access Cards -->
<section class="role-cards-section">
    <div class="container">
        <div class="row g-4">
            <!-- Student -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-student">
                    <div class="role-icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <h4>Student Portal</h4>
                    <p>Track attendance, view exam grades, monitor CGPA, and receive institute announcements.</p>
                    <a href="login.php" class="role-link">Login as Student <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <!-- Parent -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-parent">
                    <div class="role-icon"><i class="fa-solid fa-hands-holding-child"></i></div>
                    <h4>Parent Portal</h4>
                    <p>Automatic surname-based student linking to review child attendance, test scores, and reports.</p>
                    <a href="login.php" class="role-link">Login as Parent <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <!-- Teacher -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-teacher">
                    <div class="role-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <h4>Teacher Portal</h4>
                    <p>Manage subject timetables, schedule examinations, mark attendance, and upload student grades.</p>
                    <a href="login.php" class="role-link">Login as Teacher <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <!-- Admin -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-admin">
                    <div class="role-icon"><i class="fa-solid fa-user-gear"></i></div>
                    <h4>Admin Panel</h4>
                    <p>Complete institutional control, student/teacher rosters, course creation, and overall analytics.</p>
                    <a href="login.php" class="role-link">Login as Admin <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2>Designed for Excellence</h2>
            <p class="text-muted">Built with precision to make campus operations simple, transparent, and efficient.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper"><i class="fa-solid fa-users-viewfinder"></i></div>
                    <h5 class="fw-bold">Surname-Based Parent Link</h5>
                    <p class="text-muted small">Parents automatically access their children's report cards and attendance records based on matching surname.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper"><i class="fa-solid fa-calendar-check"></i></div>
                    <h5 class="fw-bold">Real-time Attendance Tracking</h5>
                    <p class="text-muted small">Comprehensive daily attendance logs with instant metrics, present/absent counts, and progress health rings.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon-wrapper"><i class="fa-solid fa-square-poll-vertical"></i></div>
                    <h5 class="fw-bold">Academic Performance Analytics</h5>
                    <p class="text-muted small">Subject-by-subject score breakdown, Chart.js performance graphs, CGPA calculation, and official transcripts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container text-center">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <i class="fa-solid fa-graduation-cap text-primary fs-4"></i>
            <span class="font-bold text-white fs-5" style="font-family:'Outfit',sans-serif;">Smart Campus</span>
        </div>
        <p class="small mb-0">&copy; <?= date('Y') ?> Smart Campus. All rights reserved.</p>
    </div>
</footer>

</body>
</html>