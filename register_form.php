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

$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'student';
$allowedTypes = ['student', 'parent', 'teacher', 'institute'];
if (!in_array($type, $allowedTypes)) {
    $type = 'student';
}

$typeConfig = [
    'student' => [
        'title'    => 'Student Registration',
        'subtitle' => 'Create your student account to access course materials, grades, and attendance logs.',
        'icon'     => 'fa-user-graduate',
        'badge'    => 'Student Portal'
    ],
    'parent' => [
        'title'    => 'Parent Registration',
        'subtitle' => 'Sign up to monitor your child\'s class attendance health, test scores, and report cards.',
        'icon'     => 'fa-hands-holding-child',
        'badge'    => 'Parent Portal'
    ],
    'teacher' => [
        'title'    => 'Faculty / Teacher Registration',
        'subtitle' => 'Sign up as a teacher to manage subject timetables, mark attendance, and upload marks.',
        'icon'     => 'fa-chalkboard-user',
        'badge'    => 'Faculty Portal'
    ],
    'institute' => [
        'title'    => 'Register University / High School / Institution',
        'subtitle' => 'Create a brand-new campus ecosystem with 100% clean data and Administrator control tower.',
        'icon'     => 'fa-building-columns',
        'badge'    => 'Campus Administrator'
    ]
];

$currentConfig = $typeConfig[$type];
$errorMsg = "";

// Helper function to insert/fetch college reliably
function getOrCreateCollege($conn, $instName) {
    $instName = trim($instName);
    if(empty($instName)) return [1, 'Smart Campus Main Institute'];

    // Case-insensitive check
    $cChk = $conn->prepare("SELECT id, college_name FROM colleges WHERE LOWER(college_name) = LOWER(?)");
    $cChk->bind_param("s", $instName);
    $cChk->execute();
    $cRes = $cChk->get_result();

    if ($cRes && $cRow = $cRes->fetch_assoc()) {
        return [(int)$cRow['id'], $cRow['college_name']];
    }

    $cleanCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $instName), 0, 6));
    if(empty($cleanCode)) $cleanCode = 'UNI';
    $uniqueCode = $cleanCode . '_' . rand(1000, 9999);

    $insClg = $conn->prepare("INSERT INTO colleges (college_name, college_code, address) VALUES (?, ?, 'Main Campus')");
    $insClg->bind_param("ss", $instName, $uniqueCode);
    if ($insClg->execute()) {
        return [(int)$insClg->insert_id, $instName];
    }

    $altCode = $cleanCode . '_' . time();
    $insAlt = $conn->prepare("INSERT INTO colleges (college_name, college_code, address) VALUES (?, ?, 'Main Campus')");
    $insAlt->bind_param("ss", $instName, $altCode);
    if ($insAlt->execute()) {
        return [(int)$insAlt->insert_id, $instName];
    }

    return [1, 'Smart Campus Main Institute'];
}

// Function to fetch available colleges
function loadAvailableColleges($conn) {
    $list = [];
    $clgRes = mysqli_query($conn, "SELECT id, college_name, college_code FROM colleges ORDER BY id ASC");
    if ($clgRes) {
        while ($clg = mysqli_fetch_assoc($clgRes)) {
            $list[] = $clg;
        }
    }
    if (empty($list)) {
        $list = [
            ['id' => 1, 'college_name' => 'Smart Campus Main Institute', 'college_code' => 'SCMI'],
            ['id' => 2, 'college_name' => 'Apex Engineering College', 'college_code' => 'AEC'],
            ['id' => 3, 'college_name' => 'Global Science & Business Academy', 'college_code' => 'GSBA']
        ];
    }
    return $list;
}

// Fetch available courses
$availableCourses = [];
$cRes = mysqli_query($conn, "SELECT course_name FROM courses ORDER BY course_name ASC");
if ($cRes) {
    while ($cr = mysqli_fetch_assoc($cRes)) {
        $availableCourses[] = $cr['course_name'];
    }
}
if (empty($availableCourses)) {
    $availableCourses = ['Computer Science', 'Data Science', 'Business Administration', 'Mechanical Engineering'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $fullName  = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone_number_tel'] ?? $_POST['phone'] ?? '');

    // Extract first and last name
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
    $lastName  = count($nameParts) > 1 ? end($nameParts) : $nameParts[0];

    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMsg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $errorMsg = "Password must be at least 6 characters long.";
    } else {
        // Check duplicate email
        $chkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chkStmt->bind_param("s", $email);
        $chkStmt->execute();
        if ($chkStmt->get_result()->num_rows > 0) {
            $errorMsg = "An account with this email address already exists.";
        } else {
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);

            if ($type === 'institute') {
                $instName = trim($_POST['institute_name'] ?? '');
                if (empty($instName)) {
                    $errorMsg = "Please enter the Institute / University / High School Name.";
                } else {
                    list($collegeId, $collegeName) = getOrCreateCollege($conn, $instName);

                    // Create Admin account for this new college
                    $uIns = $conn->prepare("INSERT INTO users (college_id, college_name, first_name, last_name, full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', 'Active')");
                    $uIns->bind_param("issssss", $collegeId, $collegeName, $firstName, $lastName, $fullName, $email, $hashedPass);
                    if ($uIns->execute()) {
                        echo "<script>
                            alert(" . json_encode("University / Institution '{$collegeName}' registered successfully! Your admin portal is ready with clean data. Please log in.") . ");
                            window.location.href = 'login.php';
                        </script>";
                        exit();
                    } else {
                        $errorMsg = "Failed to create campus admin account.";
                    }
                }
            } else {
                // Resolution of College for Student, Parent, Teacher
                $selCollegeId = $_POST['college_id'] ?? 1;
                $custCollege  = trim($_POST['custom_college'] ?? '');

                if ($selCollegeId === 'Other' && !empty($custCollege)) {
                    list($collegeId, $collegeName) = getOrCreateCollege($conn, $custCollege);
                } else {
                    $cid = (int)$selCollegeId;
                    $collegeId = 1;
                    $collegeName = "Smart Campus Main Institute";
                    $allColleges = loadAvailableColleges($conn);
                    foreach ($allColleges as $cObj) {
                        if ((int)$cObj['id'] === $cid) {
                            $collegeId = $cObj['id'];
                            $collegeName = $cObj['college_name'];
                            break;
                        }
                    }
                }

                // Insert into users
                $roleName = ($type === 'teacher') ? 'teacher' : (($type === 'parent') ? 'parent' : 'student');
                $uIns = $conn->prepare("INSERT INTO users (college_id, college_name, first_name, last_name, full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
                $uIns->bind_param("isssssss", $collegeId, $collegeName, $firstName, $lastName, $fullName, $email, $hashedPass, $roleName);

                if ($uIns->execute()) {
                    if ($type === 'student') {
                        $course = trim($_POST['course'] ?? '');
                        $sIns = $conn->prepare("INSERT INTO students (college_id, college_name, first_name, last_name, full_name, email, phone, course) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $sIns->bind_param("isssssss", $collegeId, $collegeName, $firstName, $lastName, $fullName, $email, $phone, $course);
                        $sIns->execute();
                    } elseif ($type === 'teacher') {
                        $subject = trim($_POST['subject'] ?? '');
                        $tIns = $conn->prepare("INSERT INTO teachers (college_id, college_name, first_name, last_name, full_name, email, phone, subject) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $tIns->bind_param("isssssss", $collegeId, $collegeName, $firstName, $lastName, $fullName, $email, $phone, $subject);
                        $tIns->execute();
                    } elseif ($type === 'parent') {
                        $pIns = $conn->prepare("INSERT INTO parents (college_id, college_name, first_name, last_name, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $pIns->bind_param("issssss", $collegeId, $collegeName, $firstName, $lastName, $fullName, $email, $phone);
                        $pIns->execute();
                    }

                    echo "<script>
                        alert(" . json_encode("Registration successful at {$collegeName}! Please login to continue.") . ");
                        window.location.href = 'login.php';
                    </script>";
                    exit();
                } else {
                    $errorMsg = "Registration failed. Please try again.";
                }
            }
        }
    }
}

// Load colleges for rendering
$availableColleges = loadAvailableColleges($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($currentConfig['title']) ?> | Smart Campus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #2563EB;
    --primary-dark: #1E40AF;
    --body-bg: #0B1120;
    --card-bg: #1E293B;
    --input-bg: #0F172A;
    --text-dark: #F8FAFC;
    --text-muted: #94A3B8;
    --border-color: rgba(255, 255, 255, 0.15);
    --radius: 20px;
}

[data-theme="light"] {
    --body-bg: #F0F4FF;
    --card-bg: #FFFFFF;
    --input-bg: #F8FAFC;
    --text-dark: #0F172A;
    --text-muted: #64748B;
    --border-color: #E2E8F0;
}

* { margin:0; padding:0; box-sizing:border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

body {
    background-color: var(--body-bg) !important;
    color: var(--text-dark) !important;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
    transition: background-color 0.3s ease, color 0.3s ease;
    position: relative;
    overflow-x: hidden;
}

/* Ambient Glow Backgrounds */
.glow-ambient {
    position: fixed;
    border-radius: 50%;
    filter: blur(140px);
    pointer-events: none;
    z-index: 0;
}
.glow-1 {
    width: 450px; height: 450px;
    background: rgba(37, 99, 235, 0.22);
    top: -100px; left: -100px;
}
.glow-2 {
    width: 400px; height: 400px;
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
    background: var(--card-bg);
    border: 1px solid var(--border-color);
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
    transition: all 0.3s ease;
}
.theme-toggle-btn:hover {
    transform: translateY(-2px);
    border-color: var(--primary);
}

.form-wrapper {
    max-width: 640px;
    width: 100%;
    background-color: var(--card-bg) !important;
    border-radius: 28px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    overflow: hidden;
    border: 1px solid var(--border-color) !important;
    position: relative;
    z-index: 1;
}

.form-header {
    background: linear-gradient(135deg, #1E293B, #0F172A) !important;
    color: white !important;
    padding: 36px 36px 28px;
    position: relative;
}

.back-btn {
    position: absolute;
    top: 24px; right: 24px;
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.15) !important;
    border-radius: 50%;
    color: white !important;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    transition: 0.2s;
}
.back-btn:hover { background: rgba(255,255,255,0.3) !important; color: white !important; }

.form-header-icon {
    width: 54px; height: 54px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary), #3B82F6);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
}

.form-header h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 6px;
    color: white !important;
}
.form-header p {
    color: #94A3B8 !important;
    font-size: 14px;
    margin: 0;
}

.form-body {
    padding: 36px;
    background-color: var(--card-bg) !important;
}

.field-group {
    margin-bottom: 20px;
}
.field-label {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-dark) !important;
    margin-bottom: 8px;
    display: block;
}

.input-with-icon {
    position: relative;
}
.input-with-icon i {
    position: absolute;
    left: 18px; top: 50%;
    transform: translateY(-50%);
    color: #3B82F6;
    font-size: 16px;
}

.form-control-crm {
    width: 100%;
    padding: 14px 18px 14px 50px;
    border-radius: 14px;
    border: 1px solid var(--border-color) !important;
    font-size: 14.5px;
    background-color: var(--input-bg) !important;
    color: var(--text-dark) !important;
    outline: none;
    transition: 0.3s;
}
.form-control-crm:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.2) !important;
}

/* Ensure dropdown options are crisp and dark in Dark Mode */
select.form-control-crm option {
    background-color: var(--card-bg) !important;
    color: var(--text-dark) !important;
    padding: 10px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.btn-submit {
    width: 100%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white !important;
    font-weight: 700;
    font-size: 16px;
    padding: 16px;
    border-radius: 14px;
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    gap: 10px;
    box-shadow: 0 8px 24px rgba(37,99,235,0.3);
    transition: 0.3s;
    margin-top: 10px;
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(37,99,235,0.4);
}

.alert-danger-custom {
    background: #FEE2E2;
    border: 1px solid #FCA5A5;
    color: #B91C1C;
    border-radius: 14px;
    padding: 14px 18px;
    font-size: 14px;
    margin-bottom: 24px;
    display: flex; align-items: center; gap: 10px;
}

@media (max-width: 576px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .form-body { padding: 24px; }
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

<div class="form-wrapper">

    <!-- Header -->
    <div class="form-header">
        <a href="register.php" class="back-btn" title="Back to Account Selection">
            <i class="fa-solid fa-xmark"></i>
        </a>
        <div class="form-header-icon">
            <i class="fa-solid <?= $currentConfig['icon'] ?>"></i>
        </div>
        <h2><?= htmlspecialchars($currentConfig['title']) ?></h2>
        <p><?= htmlspecialchars($currentConfig['subtitle']) ?></p>
    </div>

    <!-- Form Body -->
    <div class="form-body">

        <?php if (!empty($errorMsg)): ?>
            <div class="alert-danger-custom">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php endif; ?>

        <form action="register_form.php?type=<?= urlencode($type) ?>" method="POST" autocomplete="off">

            <?php if ($type === 'institute'): ?>
                <div class="field-group">
                    <label class="field-label">University / High School / Institution Name *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-building-columns"></i>
                        <input type="text" name="institute_name" class="form-control-crm" placeholder="e.g. Oxford High School & College" required>
                    </div>
                </div>
            <?php else: ?>
                <!-- Select College / Campus for Student, Parent, Teacher -->
                <div class="field-group">
                    <label class="field-label">Select University / High School / College Campus *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-building-columns"></i>
                        <select name="college_id" id="collegeSelect" class="form-control-crm" required>
                            <option value="">-- Choose University / High School / College --</option>
                            <?php foreach ($availableColleges as $clg): ?>
                                <option value="<?= $clg['id'] ?>">
                                    <?= htmlspecialchars($clg['college_name']) ?> (<?= htmlspecialchars($clg['college_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                            <option value="Other">Other / Register New Campus Name</option>
                        </select>
                    </div>
                </div>

                <div class="field-group" id="customCollegeBox" style="display: none;">
                    <label class="field-label">Specify New Campus / Institution Name *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-hotel"></i>
                        <input type="text" name="custom_college" id="customCollegeInput" class="form-control-crm" placeholder="Enter New Campus / High School Name">
                    </div>
                </div>
            <?php endif; ?>

            <div class="field-group">
                <label class="field-label"><?= $type === 'institute' ? 'Administrator Full Name *' : 'Full Name *' ?></label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="full_name" class="form-control-crm" placeholder="Enter your full name" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="field-group">
                    <label class="field-label">Email Address *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" class="form-control-crm" placeholder="name@domain.com" autocomplete="email" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-phone"></i>
                        <input type="tel" name="phone_number_tel" id="userPhoneTel" class="form-control-crm" placeholder="+1 555-0199" autocomplete="tel">
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label">Password *</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control-crm" placeholder="Create a secure password" autocomplete="new-password" required minlength="6">
                </div>
            </div>

            <!-- Role Specific Inputs -->
            <?php if ($type === 'student'): ?>
                <div class="field-group">
                    <label class="field-label">Enrolling Course / Subject *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <select name="course" class="form-control-crm" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($availableCourses as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($type === 'teacher'): ?>
                <div class="field-group">
                    <label class="field-label">Teaching Subject / Faculty *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-book"></i>
                        <input type="text" name="subject" class="form-control-crm" placeholder="e.g. Computer Science & AI" required>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">
                Complete Registration <i class="fa-solid fa-arrow-right"></i>
            </button>

            <div class="text-center mt-4" style="font-size:14px; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: #60A5FA; font-weight:700; text-decoration:none;">Log In</a>
            </div>

        </form>

    </div>

</div>

<script>
const collegeSelect     = document.getElementById('collegeSelect');
const customCollegeBox  = document.getElementById('customCollegeBox');
const customCollegeInput= document.getElementById('customCollegeInput');
const themeToggleBtn    = document.getElementById('themeToggleBtn');
const themeIcon         = document.getElementById('themeIcon');
const themeText         = document.getElementById('themeText');

if (collegeSelect) {
    collegeSelect.addEventListener('change', () => {
        if (collegeSelect.value === 'Other') {
            customCollegeBox.style.display = 'block';
            if (customCollegeInput) customCollegeInput.required = true;
        } else {
            customCollegeBox.style.display = 'none';
            if (customCollegeInput) customCollegeInput.required = false;
        }
    });
}

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

if(themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.body.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    });
}
</script>

</body>
</html>
