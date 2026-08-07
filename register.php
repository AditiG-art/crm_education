<?php
session_start();
include "config/db.php";

if(isset($_SESSION['user']) && isset($_SESSION['role'])) {
    if($_SESSION['role'] == "admin") {
        header("Location: admin/dashboard.php");
        exit();
    } elseif($_SESSION['role'] == "teacher") {
        header("Location: teacher/dashboard.php");
        exit();
    } elseif($_SESSION['role'] == "student") {
        header("Location: student/dashboard.php");
        exit();
    }
}

$error = "";
$success = "";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $role      = $_POST['role'] ?? 'student';

    if(empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists in users
        $chkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chkStmt->bind_param("s", $email);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result();

        if($chkRes && $chkRes->num_rows > 0) {
            $error = "An account with this email address already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users table
            $insUser = $conn->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, 'Active')");
            $insUser->bind_param("ssss", $full_name, $email, $hashedPassword, $role);

            if($insUser->execute()) {
                $user_id = $insUser->insert_id;

                // Create a clean new profile without data based on role
                if($role === 'teacher') {
                    // Check if teacher entry exists
                    $tchChk = $conn->prepare("SELECT id FROM teachers WHERE email = ?");
                    $tchChk->bind_param("s", $email);
                    $tchChk->execute();
                    if($tchChk->get_result()->num_rows === 0) {
                        $insTch = $conn->prepare("INSERT INTO teachers (full_name, email, phone, subject, qualification) VALUES (?, ?, '', '', '')");
                        $insTch->bind_param("ss", $full_name, $email);
                        $insTch->execute();
                    }
                } else {
                    // Default to student: Create clean student profile without extra data
                    $stdChk = $conn->prepare("SELECT id FROM students WHERE email = ?");
                    $stdChk->bind_param("s", $email);
                    $stdChk->execute();
                    if($stdChk->get_result()->num_rows === 0) {
                        $insStd = $conn->prepare("INSERT INTO students (full_name, email, phone, gender, date_of_birth, course, address) VALUES (?, ?, '', '', NULL, '', '')");
                        $insStd->bind_param("ss", $full_name, $email);
                        $insStd->execute();
                    }
                }

                echo "<script>
                    alert('Registration successful! Your clean profile has been created. Please login to continue.');
                    window.location.href = 'login.php';
                </script>";
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Campus CRM | Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/login.css">
<style>
.role-select-box {
    display: flex;
    gap: 15px;
    margin-bottom: 22px;
}
.role-option {
    flex: 1;
    border: 1px solid #CBD5E1;
    border-radius: 14px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    background: #F8FAFC;
    transition: 0.3s;
    font-size: 14px;
    font-weight: 500;
    color: #0F172A;
}
.role-option input {
    display: none;
}
.role-option:has(input:checked) {
    border-color: #2563EB;
    background: #EFF6FF;
    color: #2563EB;
    font-weight: 600;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
}
</style>
</head>
<body>

<div class="main-container">

    <!-- LEFT SECTION -->
    <div class="brand-section">
        <div class="brand-content">
            <div class="brand-logo">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h1>Smart Campus <span>CRM</span></h1>
            <p>Join Smart Campus CRM today. Create your account and access a seamless educational portal.</p>
            <div class="features">
                <div><i class="fa-solid fa-user-plus"></i> Clean Profile Setup</div>
                <div><i class="fa-solid fa-shield-halved"></i> Secure Account Creation</div>
                <div><i class="fa-solid fa-graduation-cap"></i> Student & Teacher Access</div>
            </div>
        </div>
    </div>

    <!-- REGISTER SECTION -->
    <div class="login-section">
        <div class="login-card" style="width:460px;">
            <h2>Create Account ✨</h2>
            <p>Register for a new account with a clean profile</p>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger py-2 px-3 mb-3 rounded-3" style="font-size:14px;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                
                <div class="input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>

                <div class="input-box">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <label class="form-label text-muted small mb-2 fw-medium">Select Account Role:</label>
                <div class="role-select-box">
                    <label class="role-option">
                        <input type="radio" name="role" value="student" <?= ($_POST['role'] ?? 'student') === 'student' ? 'checked' : '' ?>>
                        <i class="fa-solid fa-user-graduate me-1"></i> Student
                    </label>
                    <label class="role-option">
                        <input type="radio" name="role" value="teacher" <?= ($_POST['role'] ?? '') === 'teacher' ? 'checked' : '' ?>>
                        <i class="fa-solid fa-chalkboard-user me-1"></i> Teacher
                    </label>
                </div>

                <div class="input-box">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="reg_password" name="password" placeholder="Create Password" required>
                    <i class="fa-solid fa-eye-slash" id="toggleRegPassword"></i>
                </div>

                <div class="input-box">
                    <i class="fa-solid fa-lock-keyhole"></i>
                    <input type="password" id="reg_confirm" name="confirm_password" placeholder="Confirm Password" required>
                </div>

                <button class="login-btn" name="register" type="submit">
                    Register Account <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="bottom-text">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

</div>

<script>
const togglePass = document.getElementById('toggleRegPassword');
const passInput  = document.getElementById('reg_password');
if(togglePass && passInput) {
    togglePass.addEventListener('click', () => {
        const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passInput.setAttribute('type', type);
        togglePass.classList.toggle('fa-eye');
        togglePass.classList.toggle('fa-eye-slash');
    });
}
</script>

</body>
</html>
