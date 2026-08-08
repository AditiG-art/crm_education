<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "student") {
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];
$msg = "";
$error = "";

// Fetch Student Data
$query = mysqli_query($conn, "SELECT * FROM students WHERE email='".mysqli_real_escape_string($conn, $email)."'");
$student = mysqli_fetch_assoc($query);

if(!$student) {
    $name_esc = mysqli_real_escape_string($conn, $_SESSION['user'] ?? 'Student');
    $email_esc = mysqli_real_escape_string($conn, $email);
    mysqli_query($conn, "INSERT INTO students (full_name, email, phone, gender, date_of_birth, course, address) VALUES ('$name_esc', '$email_esc', '', '', NULL, '', '')");
    $query = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");
    $student = mysqli_fetch_assoc($query);
}

// Handle Profile Update POST
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : NULL;
    $address = trim($_POST['address'] ?? '');

    $upd = $conn->prepare("UPDATE students SET phone = ?, gender = ?, date_of_birth = ?, address = ? WHERE email = ?");
    $upd->bind_param("sssss", $phone, $gender, $dob, $address, $email);
    if($upd->execute()) {
        $msg = "Profile updated successfully!";
        $student['phone'] = $phone;
        $student['gender'] = $gender;
        $student['date_of_birth'] = $dob;
        $student['address'] = $address;
    } else {
        $error = "Failed to update profile. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | Student Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "My Student Profile"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1>My Account Profile ⚙️</h1>
    <p>View your academic information and update your contact details</p>
</div>

<?php if(!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Summary Card -->
    <div class="col-md-4">
        <div class="crm-card text-center p-4">
            <div class="mb-3">
                <i class="fa-solid fa-circle-user fa-5x text-primary"></i>
            </div>
            <h4 class="mb-1 fw-bold"><?= htmlspecialchars($student['full_name']) ?></h4>
            <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">Enrolled Student</span>
            <hr>
            <div class="text-start">
                <p class="mb-2"><strong><i class="fa-solid fa-envelope me-2 text-primary"></i> Email:</strong> <br><span class="text-muted ms-4"><?= htmlspecialchars($student['email']) ?></span></p>
                <p class="mb-2"><strong><i class="fa-solid fa-graduation-cap me-2 text-primary"></i> Enrolled Course:</strong> <br><span class="badge bg-info-subtle text-info-emphasis ms-4"><?= htmlspecialchars($student['course'] ?: 'Not Enrolled') ?></span></p>
                <p class="mb-0"><strong><i class="fa-solid fa-calendar me-2 text-primary"></i> Registration:</strong> <br><small class="text-muted ms-4"><?= isset($student['created_at']) ? date('M d, Y', strtotime($student['created_at'])) : 'Active' ?></small></p>
            </div>
        </div>
    </div>

    <!-- Right Profile Edit Form -->
    <div class="col-md-8">
        <div class="crm-card">
            <div class="crm-card-header">
                <h4><i class="fa-solid fa-user-pen"></i> Personal Information</h4>
            </div>
            <div class="crm-card-body">
                <form action="profile.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['full_name']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 555-0144" value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="" <?= empty($student['gender']) ? 'selected' : '' ?>>-- Select Gender --</option>
                                <option value="Male" <?= ($student['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($student['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($student['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Enrolled Course</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['course'] ?: 'Not Enrolled') ?>" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Residential Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter home address..."><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn-crm-primary mt-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Profile Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>