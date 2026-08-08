<?php
session_start();
include "config/db.php";

$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'student';
$allowedTypes = ['student', 'teacher', 'parent', 'institute'];
if (!in_array($type, $allowedTypes)) {
    $type = 'student';
}

$roleConfig = [
    'student' => [
        'title' => 'Student Registration',
        'subtitle' => 'Create your student portal account',
        'icon' => 'fa-user-graduate',
        'color' => '#2563EB'
    ],
    'teacher' => [
        'title' => 'Teacher / Faculty Registration',
        'subtitle' => 'Join your institute academic team',
        'icon' => 'fa-chalkboard-user',
        'color' => '#059669'
    ],
    'parent' => [
        'title' => 'Parent / Guardian Registration',
        'subtitle' => 'Track and monitor your child\'s education',
        'icon' => 'fa-people-roof',
        'color' => '#D97706'
    ],
    'institute' => [
        'title' => 'New Institute Onboarding',
        'subtitle' => 'Set up your school, college, or university CRM',
        'icon' => 'fa-building-columns',
        'color' => '#7C3AED'
    ]
];

$currentConfig = $roleConfig[$type];
$errorMsg = '';
$successMsg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');

    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMsg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } else {
        // Check duplicate email
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $errorMsg = "An account with this email address already exists. Please login instead.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $dbRole = ($type === 'teacher') ? 'teacher' : (($type === 'institute') ? 'admin' : 'student');

            // Insert into users
            $insertUser = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, 'Active')");
            mysqli_stmt_bind_param($insertUser, "ssss", $fullName, $email, $hashedPassword, $dbRole);

            if (mysqli_stmt_execute($insertUser)) {
                $userId = mysqli_insert_id($conn);

                // Insert into role specific tables
                if ($type === 'student') {
                    $course = trim($_POST['course'] ?? 'Computer Science');
                    $gender = $_POST['gender'] ?? 'Male';
                    $dob    = $_POST['date_of_birth'] ?? date('Y-m-d');
                    $insStud = mysqli_prepare($conn, "INSERT INTO students (full_name, email, phone, gender, date_of_birth, course) VALUES (?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($insStud, "ssssss", $fullName, $email, $phone, $gender, $dob, $course);
                    mysqli_stmt_execute($insStud);
                } elseif ($type === 'teacher') {
                    $subject = trim($_POST['subject'] ?? 'Computer Science');
                    $qual    = trim($_POST['qualification'] ?? 'Master of Science');
                    $insTeach = mysqli_prepare($conn, "INSERT INTO teachers (full_name, email, phone, subject, qualification) VALUES (?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($insTeach, "sssss", $fullName, $email, $phone, $subject, $qual);
                    mysqli_stmt_execute($insTeach);
                }

                $_SESSION['reg_success'] = "Registration successful as " . ucfirst($type) . "! You can now log in.";
                header("Location: login.php?registered=1");
                exit();
            } else {
                $errorMsg = "Something went wrong during registration. Please try again.";
            }
        }
    }
}

// Fetch Courses for Dropdown
$courseList = [];
$cRes = mysqli_query($conn, "SELECT course_name FROM courses ORDER BY course_name ASC");
if ($cRes) {
    while ($r = mysqli_fetch_assoc($cRes)) {
        $courseList[] = $r['course_name'];
    }
}
if (empty($courseList)) {
    $courseList = ['Computer Science', 'Data Science', 'Business Administration', 'Electrical Engineering'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($currentConfig['title']) ?> | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: <?= $currentConfig['color'] ?>;
    --primary-dark: #1E3A8A;
    --primary-light: #EFF6FF;
    --body-bg: #F0F4FF;
    --text-dark: #0F172A;
    --text-muted: #64748B;
    --card-radius: 20px;
}

* { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }

body {
    background: var(--body-bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.form-wrapper {
    max-width: 600px;
    width: 100%;
    background: white;
    border-radius: var(--card-radius);
    box-shadow: 0 10px 40px rgba(37,99,235,0.08);
    border: 1px solid rgba(226,232,240,0.8);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 32px 36px;
    position: relative;
}

.back-btn {
    position: absolute;
    top: 24px; right: 24px;
    color: rgba(255,255,255,0.8);
    background: rgba(255,255,255,0.15);
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    transition: all 0.2s ease;
}
.back-btn:hover { background: rgba(255,255,255,0.3); color: white; }

.form-header-icon {
    width: 54px; height: 54px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; margin-bottom: 16px;
}

.form-header h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 24px; font-weight: 700;
    margin-bottom: 6px;
}
.form-header p {
    font-size: 13.5px; opacity: 0.85; margin: 0;
}

.form-body {
    padding: 36px;
}

.field-group {
    margin-bottom: 20px;
}
.field-label {
    font-size: 13px; font-weight: 600;
    color: var(--text-dark); margin-bottom: 8px;
    display: block;
}

.input-with-icon {
    position: relative;
}
.input-with-icon i {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 14px;
}
.form-control-crm {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    font-size: 13.5px;
    color: var(--text-dark);
    transition: all 0.2s ease;
    outline: none;
}
.form-control-crm:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}

select.form-control-crm {
    appearance: none;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%64748B' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 14px center;
    background-color: white;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.btn-submit {
    width: 100%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    padding: 13px;
    border-radius: 12px;
    font-size: 14.5px;
    font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    gap: 8px;
    box-shadow: 0 6px 20px rgba(37,99,235,0.25);
    transition: all 0.2s ease;
    margin-top: 10px;
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(37,99,235,0.35);
}

.alert-danger {
    background: #FEE2E2;
    border: 1px solid #FCA5A5;
    color: #B91C1C;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px;
}

@media (max-width: 576px) {
    .form-grid-2 { grid-template-columns: 1fr; }
    .form-body { padding: 24px; }
}
</style>
</head>
<body>

<div class="form-wrapper">

    <!-- Form Header -->
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
        <div class="alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
        </div>
        <?php endif; ?>

        <form action="register_form.php?type=<?= urlencode($type) ?>" method="POST">

            <!-- Common Fields -->
            <?php if ($type === 'institute'): ?>
            <div class="field-group">
                <label class="field-label">Institute / Organization Name *</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-building"></i>
                    <input type="text" name="institute_name" class="form-control-crm" placeholder="e.g. Stanford Academy of Science" required>
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
                        <input type="email" name="email" class="form-control-crm" placeholder="name@domain.com" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" name="phone" class="form-control-crm" placeholder="+1 555-0199">
                    </div>
                </div>
            </div>

            <div class="field-group">
                <label class="field-label">Password *</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control-crm" placeholder="Create a secure password" required minlength="6">
                </div>
            </div>

            <!-- Role Specific Fields -->
            <?php if ($type === 'student'): ?>
            <div class="field-group">
                <label class="field-label">Course / Program *</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-book-open"></i>
                    <select name="course" class="form-control-crm" required>
                        <?php foreach ($courseList as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="field-group">
                    <label class="field-label">Gender</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-venus-mars"></i>
                        <select name="gender" class="form-control-crm">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Date of Birth</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-calendar"></i>
                        <input type="date" name="date_of_birth" class="form-control-crm" value="2003-01-01">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($type === 'teacher'): ?>
            <div class="form-grid-2">
                <div class="field-group">
                    <label class="field-label">Primary Subject / Faculty *</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-book"></i>
                        <input type="text" name="subject" class="form-control-crm" placeholder="e.g. Computer Science" required>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Highest Qualification</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-award"></i>
                        <input type="text" name="qualification" class="form-control-crm" placeholder="e.g. Ph.D. in Computer Science">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($type === 'parent'): ?>
            <div class="field-group">
                <label class="field-label">Child / Student's Registered Email *</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-child"></i>
                    <input type="email" name="student_email" class="form-control-crm" placeholder="Enter student's registered email" required>
                </div>
                <small class="text-muted mt-1 d-block" style="font-size:11.5px;">This connects your parent account to your child's student records.</small>
            </div>
            <?php endif; ?>

            <?php if ($type === 'institute'): ?>
            <div class="field-group">
                <label class="field-label">Institute Category</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <select name="institute_type" class="form-control-crm">
                        <option value="College / University">College / University</option>
                        <option value="High School">High School / K-12</option>
                        <option value="Coaching Institute">Coaching / Training Institute</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">
                Complete Registration <i class="fa-solid fa-arrow-right"></i>
            </button>

            <div class="text-center mt-3" style="font-size:13.5px; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight:700; text-decoration:none;">Log In</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>
