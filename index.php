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
<title>Smart Campus | Next-Gen Educational Management Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #2563EB;
  --primary-glow: #3B82F6;
  --dark-navy: #0F172A;
  --navy-card: #1E293B;
  --accent-gold: #F59E0B;
  --accent-emerald: #10B981;
  --accent-purple: #8B5CF6;
  --text-main: #F8FAFC;
  --text-sub: #94A3B8;
  --glass-bg: rgba(30, 41, 59, 0.7);
  --glass-border: rgba(255, 255, 255, 0.1);
}

* {
  margin: 0; padding: 0; box-sizing: border-box;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

body {
  background-color: var(--dark-navy);
  color: var(--text-main);
  overflow-x: hidden;
}

/* Ambient Background Lights */
.glow-ambient {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  pointer-events: none;
  z-index: 0;
}
.glow-1 {
  width: 500px; height: 500px;
  background: rgba(37, 99, 235, 0.25);
  top: -100px; left: -100px;
}
.glow-2 {
  width: 450px; height: 450px;
  background: rgba(139, 92, 246, 0.2);
  top: 300px; right: -100px;
}
.glow-3 {
  width: 400px; height: 400px;
  background: rgba(245, 158, 11, 0.15);
  bottom: 100px; left: 20%;
}

/* Navbar */
.landing-nav {
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--glass-border);
  padding: 18px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}
.brand-logo {
  font-family: 'Outfit', sans-serif;
  font-size: 24px;
  font-weight: 800;
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 12px;
}
.brand-logo i {
  width: 42px; height: 42px;
  background: linear-gradient(135deg, var(--primary), var(--accent-purple));
  color: white;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
}
.nav-link-custom {
  color: var(--text-sub);
  font-weight: 500;
  font-size: 15px;
  text-decoration: none;
  transition: all 0.3s;
}
.nav-link-custom:hover {
  color: white;
}
.btn-nav-login {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid var(--glass-border);
  color: white;
  font-weight: 600;
  padding: 10px 22px;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.3s;
}
.btn-nav-login:hover {
  background: rgba(255, 255, 255, 0.16);
  color: white;
  transform: translateY(-1px);
}
.btn-nav-register {
  background: linear-gradient(135deg, var(--primary), #3B82F6);
  color: white;
  font-weight: 600;
  padding: 10px 24px;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
  transition: all 0.3s;
}
.btn-nav-register:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.5);
  color: white;
}

/* Hero Section */
.hero-section {
  padding: 100px 0 80px;
  position: relative;
  z-index: 1;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: rgba(37, 99, 235, 0.12);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #60A5FA;
  font-size: 14px;
  font-weight: 600;
  padding: 8px 20px;
  border-radius: 50px;
  margin-bottom: 28px;
  backdrop-filter: blur(8px);
}
.hero-title {
  font-family: 'Outfit', sans-serif;
  font-size: 62px;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 24px;
  letter-spacing: -1px;
}
.hero-title span {
  background: linear-gradient(135deg, #60A5FA 0%, #A78BFA 50%, #F59E0B 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.hero-subtitle {
  font-size: 19px;
  color: var(--text-sub);
  max-width: 680px;
  margin: 0 auto 40px;
  line-height: 1.6;
}
.btn-hero-main {
  background: linear-gradient(135deg, var(--primary), var(--primary-glow));
  color: white;
  font-weight: 700;
  font-size: 16px;
  padding: 16px 36px;
  border-radius: 16px;
  text-decoration: none;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 12px;
}
.btn-hero-main:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 40px rgba(37, 99, 235, 0.55);
  color: white;
}
.btn-hero-outline {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--glass-border);
  color: white;
  font-weight: 600;
  font-size: 16px;
  padding: 16px 36px;
  border-radius: 16px;
  text-decoration: none;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  backdrop-filter: blur(8px);
}
.btn-hero-outline:hover {
  background: rgba(255, 255, 255, 0.12);
  color: white;
  transform: translateY(-2px);
}

/* Glass Dashboard Preview Card */
.hero-preview-wrapper {
  margin-top: 65px;
  position: relative;
}
.hero-preview-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: 28px;
  padding: 30px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(20px);
}
.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--glass-border);
}
.dots {
  display: flex; gap: 8px;
}
.dot { width: 12px; height: 12px; border-radius: 50%; }
.dot-red { background: #EF4444; }
.dot-yellow { background: #F59E0B; }
.dot-green { background: #10B981; }

.preview-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
.preview-stat {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid var(--glass-border);
  padding: 20px;
  border-radius: 18px;
  text-align: left;
}
.preview-stat i {
  font-size: 22px;
  margin-bottom: 12px;
}
.preview-stat h3 {
  font-family: 'Outfit', sans-serif;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 4px;
}
.preview-stat p {
  color: var(--text-sub);
  font-size: 13px;
  margin: 0;
}

/* Role Portals Grid */
.portals-section {
  padding: 100px 0;
  position: relative;
  z-index: 1;
}
.section-title-badge {
  color: var(--primary-glow);
  font-weight: 700;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 12px;
}
.section-heading {
  font-family: 'Outfit', sans-serif;
  font-size: 40px;
  font-weight: 800;
  margin-bottom: 16px;
}
.portal-card {
  background: var(--navy-card);
  border: 1px solid var(--glass-border);
  border-radius: 24px;
  padding: 36px 28px;
  transition: all 0.35s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}
.portal-card:hover {
  transform: translateY(-8px);
  border-color: rgba(96, 165, 250, 0.4);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
}
.portal-icon {
  width: 64px; height: 64px;
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  font-size: 28px;
  margin-bottom: 24px;
}
.portal-student .portal-icon { background: rgba(37, 99, 235, 0.15); color: #60A5FA; }
.portal-parent  .portal-icon { background: rgba(245, 158, 11, 0.15); color: #FBBF24; }
.portal-teacher .portal-icon { background: rgba(16, 185, 129, 0.15); color: #34D399; }
.portal-admin   .portal-icon { background: rgba(139, 92, 246, 0.15); color: #C084FC; }

.portal-card h3 {
  font-family: 'Outfit', sans-serif;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 12px;
}
.portal-card p {
  color: var(--text-sub);
  font-size: 14.5px;
  line-height: 1.6;
  margin-bottom: 28px;
  flex-grow: 1;
}
.portal-btn {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid var(--glass-border);
  color: white;
  font-weight: 600;
  font-size: 14px;
  padding: 12px 20px;
  border-radius: 14px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  transition: all 0.3s;
}
.portal-card:hover .portal-btn {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

/* Bento Feature Section */
.features-section {
  padding: 90px 0 110px;
  position: relative;
  z-index: 1;
}
.bento-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: 24px;
  padding: 36px;
  height: 100%;
  backdrop-filter: blur(12px);
}
.bento-icon {
  width: 52px; height: 52px;
  border-radius: 16px;
  background: rgba(37, 99, 235, 0.15);
  color: #60A5FA;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px;
  margin-bottom: 20px;
}

/* FAQ Accordion */
.faq-section {
  padding: 90px 0;
}
.accordion-item {
  background: var(--navy-card);
  border: 1px solid var(--glass-border);
  border-radius: 18px !important;
  margin-bottom: 16px;
  overflow: hidden;
}
.accordion-button {
  background: var(--navy-card);
  color: white;
  font-weight: 700;
  font-size: 17px;
  padding: 20px 24px;
  box-shadow: none !important;
}
.accordion-button:not(.collapsed) {
  background: rgba(37, 99, 235, 0.15);
  color: #60A5FA;
}
.accordion-body {
  color: var(--text-sub);
  font-size: 15px;
  line-height: 1.7;
  padding: 0 24px 24px;
}

/* Footer */
footer {
  background: #0B1120;
  border-top: 1px solid var(--glass-border);
  padding: 60px 0 30px;
  color: var(--text-sub);
}
footer a { color: var(--text-sub); text-decoration: none; transition: color 0.3s; }
footer a:hover { color: white; }

@media (max-width: 992px) {
  .hero-title { font-size: 42px; }
  .preview-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
  .hero-title { font-size: 34px; }
  .preview-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- Ambient Glow Backgrounds -->
<div class="glow-ambient glow-1"></div>
<div class="glow-ambient glow-2"></div>
<div class="glow-ambient glow-3"></div>

<!-- Navbar -->
<nav class="landing-nav">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Smart Campus</span>
        </a>
        <div class="d-none d-md-flex align-items-center gap-4">
            <a href="#portals" class="nav-link-custom">Portals</a>
            <a href="#features" class="nav-link-custom">Features</a>
            <a href="#faq" class="nav-link-custom">FAQ</a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="login.php" class="btn-nav-login">Sign In</a>
            <a href="register.php" class="btn-nav-register">Get Started</a>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <div class="hero-badge mx-auto">
            <i class="fa-solid fa-sparkles text-warning"></i> Next-Gen Campus Platform
        </div>
        <h1 class="hero-title">
            Empowering Education with <br><span>Smart Campus</span>
        </h1>
        <p class="hero-subtitle">
            An integrated, intelligent digital ecosystem connecting Students, Parents, Teachers, and Institute Administrators in real-time.
        </p>
        <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
            <a href="register.php" class="btn-hero-main">
                Create Account <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="login.php" class="btn-hero-outline">
                Sign In to Portal <i class="fa-solid fa-right-to-bracket"></i>
            </a>
        </div>

        <!-- Hero Preview Mockup Card -->
        <div class="hero-preview-wrapper col-lg-10 mx-auto">
            <div class="hero-preview-card">
                <div class="preview-header">
                    <div class="dots">
                        <div class="dot dot-red"></div>
                        <div class="dot dot-yellow"></div>
                        <div class="dot dot-green"></div>
                    </div>
                    <small class="text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> Smart Campus Operating System v5.0</small>
                </div>
                <div class="preview-grid">
                    <div class="preview-stat">
                        <i class="fa-solid fa-user-graduate text-primary"></i>
                        <h3>2,450+</h3>
                        <p>Enrolled Students</p>
                    </div>
                    <div class="preview-stat">
                        <i class="fa-solid fa-calendar-check text-success"></i>
                        <h3>99.4%</h3>
                        <p>Attendance Health</p>
                    </div>
                    <div class="preview-stat">
                        <i class="fa-solid fa-users text-warning"></i>
                        <h3>100%</h3>
                        <p>Surname Parent Link</p>
                    </div>
                    <div class="preview-stat">
                        <i class="fa-solid fa-star text-info"></i>
                        <h3>3.85 / 4.0</h3>
                        <p>Average CGPA</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Portals Grid -->
<section class="portals-section" id="portals">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-title-badge">Unified Role Portals</div>
            <h2 class="section-heading">Dedicated Ecosystems for Everyone</h2>
            <p class="text-muted max-w-lg mx-auto">Tailored portals providing instant access to attendance, transcripts, timetables, and campus management.</p>
        </div>

        <div class="row g-4">
            <!-- Student -->
            <div class="col-md-6 col-lg-3">
                <div class="portal-card portal-student">
                    <div class="portal-icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <h3>Student Portal</h3>
                    <p>Track your daily class attendance, view published exam grades, calculate CGPA, and view achievements.</p>
                    <a href="login.php" class="portal-btn">
                        <span>Student Login</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Parent -->
            <div class="col-md-6 col-lg-3">
                <div class="portal-card portal-parent">
                    <div class="portal-icon"><i class="fa-solid fa-hands-holding-child"></i></div>
                    <h3>Parent Portal</h3>
                    <p>Automatic surname-based student linking. Monitor your child's attendance health, test scores, and reports.</p>
                    <a href="login.php" class="portal-btn">
                        <span>Parent Login</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Teacher -->
            <div class="col-md-6 col-lg-3">
                <div class="portal-card portal-teacher">
                    <div class="portal-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                    <h3>Teacher Portal</h3>
                    <p>Manage daily timetables, schedule unit tests, mark student attendance, and upload subject marks easily.</p>
                    <a href="login.php" class="portal-btn">
                        <span>Teacher Login</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Admin -->
            <div class="col-md-6 col-lg-3">
                <div class="portal-card portal-admin">
                    <div class="portal-icon"><i class="fa-solid fa-user-gear"></i></div>
                    <h3>Admin Panel</h3>
                    <p>Complete control tower: manage student & teacher rosters, course catalogs, system announcements, and fee logs.</p>
                    <a href="login.php" class="portal-btn">
                        <span>Admin Login</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Bento Section -->
<section class="features-section" id="features">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-title-badge">Platform Highlights</div>
            <h2 class="section-heading">Built for Seamless Operations</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="bento-card">
                    <div class="bento-icon"><i class="fa-solid fa-users"></i></div>
                    <h4 class="fw-bold mb-2">Surname-Based Parent Linking</h4>
                    <p class="text-muted small">Zero manual linking code needed. Parents automatically view report cards and attendance logs for students matching their surname.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="bento-card">
                    <div class="bento-icon"><i class="fa-solid fa-chart-pie"></i></div>
                    <h4 class="fw-bold mb-2">Interactive Attendance Rings</h4>
                    <p class="text-muted small">Real-time attendance percentage visualization with SVG health rings and metric breakdowns for present, absent, and late entries.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="bento-card">
                    <div class="bento-icon"><i class="fa-solid fa-square-poll-vertical"></i></div>
                    <h4 class="fw-bold mb-2">Performance Analytics & Transcripts</h4>
                    <p class="text-muted small">Automated GPA calculation, subject performance charts using Chart.js, and exportable academic transcript view.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Accordion Section -->
<section class="faq-section" id="faq">
    <div class="container col-lg-8">
        <div class="text-center mb-5">
            <div class="section-title-badge">Got Questions?</div>
            <h2 class="section-heading">Frequently Asked Questions</h2>
        </div>

        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        How does parent-child linking work?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        When a parent registers with their Last Name (surname), Smart Campus automatically links their account with all students carrying that matching surname. Parents can seamlessly monitor reports, attendance, and exam grades.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Which user roles are supported in Smart Campus?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Smart Campus supports 4 distinct user roles: Student, Parent, Teacher, and Administrator—each equipped with dedicated dashboards and privacy controls.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        How do teachers submit timetable schedules & marks?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Teachers have access to a Teacher Portal where they can view class timetables, schedule unit tests, mark attendance logs, and upload student scores directly into the database.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row g-4 align-items-center justify-content-between pb-4">
            <div class="col-md-6 text-center text-md-start">
                <a href="index.php" class="brand-logo mb-2">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Smart Campus</span>
                </a>
                <p class="small text-muted mb-0">Empowering Students, Parents, Teachers, and Institutions worldwide.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-3">
                    <a href="login.php">Login</a>
                    <span>•</span>
                    <a href="register.php">Register</a>
                    <span>•</span>
                    <a href="#portals">Portals</a>
                </div>
            </div>
        </div>
        <hr style="border-color:var(--glass-border);">
        <div class="text-center small text-muted pt-2">
            &copy; <?= date('Y') ?> Smart Campus. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>