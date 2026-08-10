<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "admin") {
    header("Location:../login.php");
    exit();
}

$msg = "";
$error = "";

// Fetch available courses
$availableCourses = [];
$cRes = mysqli_query($conn, "SELECT course_name FROM courses ORDER BY course_name ASC");
if($cRes) {
    while($cr = mysqli_fetch_assoc($cRes)) {
        $availableCourses[] = $cr['course_name'];
    }
}
if(empty($availableCourses)) {
    $availableCourses = ['Computer Science', 'Data Science', 'Business Administration', 'Mechanical Engineering'];
}

// Fetch available colleges
$availableColleges = [];
$clgRes = mysqli_query($conn, "SELECT id, college_name, college_code FROM colleges ORDER BY id ASC");
if($clgRes) {
    while($clg = mysqli_fetch_assoc($clgRes)) {
        $availableColleges[] = $clg;
    }
}

if(isset($_POST['add_student'])) {
    $name      = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $gender    = trim($_POST['gender'] ?? '');
    $dob       = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
    $course    = trim($_POST['course'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $collegeId = (int)($_POST['college_id'] ?? ($_SESSION['college_id'] ?? 1));

    // Resolve College Name
    $collegeName = "Smart Campus Main Institute";
    foreach($availableColleges as $cObj) {
        if((int)$cObj['id'] === $collegeId) {
            $collegeName = $cObj['college_name'];
            break;
        }
    }

    if(empty($name) || empty($email) || empty($password)) {
        $error = "Name, Email, and Password are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Extract First Name and Last Name
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0];
        $lastName  = count($nameParts) > 1 ? end($nameParts) : $nameParts[0];

        // Insert into students table
        $sql = "INSERT INTO students (college_id, college_name, first_name, last_name, full_name, email, phone, gender, date_of_birth, course, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssss", $collegeId, $collegeName, $firstName, $lastName, $name, $email, $phone, $gender, $dob, $course, $address);

        if($stmt->execute()) {
            $studentId = $stmt->insert_id;

            // Check if user login already exists in users table
            $uChk = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $uChk->bind_param("s", $email);
            $uChk->execute();
            if($uChk->get_result()->num_rows == 0) {
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                $uIns = $conn->prepare("INSERT INTO users (college_id, college_name, first_name, last_name, full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'student', 'Active')");
                $uIns->bind_param("issssss", $collegeId, $collegeName, $firstName, $lastName, $name, $email, $hashedPass);
                $uIns->execute();
            }

            $alertMsg = "Student Added Successfully! Login Credentials:\\nEmail: {$email}\\nPassword: {$password}";
            echo "<script>
                alert(" . json_encode($alertMsg) . ");
                window.location='students.php';
            </script>";
            exit();
        } else {
            $error = "Error adding student: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Student | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.form-card {
    background: white;
    padding: 36px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
label { font-weight: 600; font-size: 14px; margin-bottom: 6px; }
.form-control, .form-select { padding: 12px 16px; border-radius: 12px; border: 1px solid #CBD5E1; }
.form-control:focus, .form-select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
</style>
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Add Student"; include "../includes/topbar.php"; ?>

<div class="page-header mb-4">
    <h1><i class="fa-solid fa-user-plus text-primary"></i> Add New Student</h1>
    <p>Register a student and generate their login credentials instantly</p>
</div>

<?php if(!empty($error)): ?>
    <div class="alert alert-danger rounded-3 mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card col-lg-9">
    <form method="POST" action="add_student.php">
        <div class="row g-3">
            
            <div class="col-md-12">
                <label><i class="fa-solid fa-building-columns text-primary me-1"></i> College / Institution</label>
                <select name="college_id" class="form-select" required>
                    <?php foreach($availableColleges as $clg): ?>
                        <option value="<?= $clg['id'] ?>" <?= (int)($_SESSION['college_id'] ?? 1) === (int)$clg['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($clg['college_name']) ?> (<?= htmlspecialchars($clg['college_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-user text-primary me-1"></i> Student Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. Alex Rivera" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-envelope text-primary me-1"></i> Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="alex.rivera@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-lock text-primary me-1"></i> Student Account Password *</label>
                <div class="input-group">
                    <input type="text" name="password" id="studentPassword" class="form-control" placeholder="Enter or generate password" value="Student@2026" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPassword()" title="Generate Random Password">
                        <i class="fa-solid fa-dice me-1"></i> Generate
                    </button>
                </div>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-phone text-primary me-1"></i> Phone Number</label>
                <input type="tel" name="phone" id="studentPhone" class="form-control" placeholder="+1 555-0199" autocomplete="tel" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-venus-mars text-primary me-1"></i> Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Select Gender</option>
                    <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= ($_POST['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-calendar text-primary me-1"></i> Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <label><i class="fa-solid fa-graduation-cap text-primary me-1"></i> Enrolled Course</label>
                <select name="course" class="form-select">
                    <option value="">-- Choose Course --</option>
                    <?php foreach($availableCourses as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= ($_POST['course'] ?? '') === $c ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12">
                <label><i class="fa-solid fa-location-dot text-primary me-1"></i> Residential Address</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Enter home address..."><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

        </div>

        <div class="mt-4 d-flex gap-3">
            <button type="submit" name="add_student" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="fa-solid fa-user-check me-1"></i> Create Student Account
            </button>
            <a href="students.php" class="btn btn-light border px-4 py-2 rounded-pill">Cancel</a>
        </div>
    </form>
</div>

</div>

<script>
function generateRandomPassword() {
    const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#$!";
    let pass = "Stud@";
    for(let i=0; i<6; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('studentPassword').value = pass;
}
</script>
</body>
</html>