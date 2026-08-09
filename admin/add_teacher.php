<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "admin") {
    header("Location:../login.php");
    exit();
}

$error = "";
$msg = "";

// Fetch available colleges
$availableColleges = [];
$clgRes = mysqli_query($conn, "SELECT id, college_name, college_code FROM colleges ORDER BY id ASC");
if($clgRes) {
    while($clg = mysqli_fetch_assoc($clgRes)) {
        $availableColleges[] = $clg;
    }
}

if(isset($_POST['add_teacher'])) {
    $name          = trim($_POST['full_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $subject       = trim($_POST['subject'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $collegeId     = (int)($_POST['college_id'] ?? ($_SESSION['college_id'] ?? 1));

    // Resolve College Name
    $collegeName = "Smart Campus Main Institute";
    foreach($availableColleges as $cObj) {
        if((int)$cObj['id'] === $collegeId) {
            $collegeName = $cObj['college_name'];
            break;
        }
    }

    if(empty($name) || empty($email) || empty($subject)) {
        $error = "Name, Email, and Subject are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Extract First Name and Last Name
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0];
        $lastName  = count($nameParts) > 1 ? end($nameParts) : $nameParts[0];

        $sql = "INSERT INTO teachers (college_id, college_name, first_name, last_name, full_name, email, phone, subject, qualification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssss", $collegeId, $collegeName, $firstName, $lastName, $name, $email, $phone, $subject, $qualification);

        if($stmt->execute()) {
            // Check if user login already exists
            $userCheck = $conn->prepare("SELECT id FROM users WHERE email=?");
            $userCheck->bind_param("s", $email);
            $userCheck->execute();
            if($userCheck->get_result()->num_rows == 0) {
                $defaultPassword = password_hash("teacher123", PASSWORD_DEFAULT);
                $userInsert = $conn->prepare("INSERT INTO users (college_id, college_name, first_name, last_name, full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'teacher', 'Active')");
                $userInsert->bind_param("isssssss", $collegeId, $collegeName, $firstName, $lastName, $name, $email, $defaultPassword);
                $userInsert->execute();
            }

            echo "<script>alert('Teacher Added Successfully at " . json_encode($collegeName) . "!'); window.location='teachers.php';</script>";
            exit();
        } else {
            $error = "Error adding teacher: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Teacher | Smart Campus CRM</title>
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
<?php
$pageTitle = "Add New Teacher";
include "../includes/topbar.php";
?>

<div class="page-header mb-4">
    <h1><i class="fa-solid fa-chalkboard-user text-primary"></i> Add Faculty Member</h1>
    <p>Register a teacher and assign them to a university campus</p>
</div>

<?php if(!empty($error)): ?>
    <div class="alert alert-danger rounded-3 mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="form-card col-lg-9">
    <form method="POST" action="add_teacher.php">
        <div class="row g-3">

            <div class="col-md-12">
                <label><i class="fa-solid fa-building-columns text-primary me-1"></i> College / Institution Campus *</label>
                <select name="college_id" class="form-select" required>
                    <?php foreach($availableColleges as $clg): ?>
                        <option value="<?= $clg['id'] ?>" <?= (int)($_SESSION['college_id'] ?? 1) === (int)$clg['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($clg['college_name']) ?> (<?= htmlspecialchars($clg['college_code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-user text-primary me-1"></i> Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. Dr. Sarah Jenkins" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-envelope text-primary me-1"></i> Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="sarah.jenkins@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-phone text-primary me-1"></i> Phone Number</label>
                <input type="tel" name="phone" id="teacherPhone" class="form-control" placeholder="+1 555-0188" autocomplete="tel" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label><i class="fa-solid fa-book text-primary me-1"></i> Teaching Subject / Faculty *</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Data Structures & Algorithms" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
            </div>

            <div class="col-md-12">
                <label><i class="fa-solid fa-graduation-cap text-primary me-1"></i> Highest Qualification</label>
                <input type="text" name="qualification" class="form-control" placeholder="e.g. Ph.D. in Computer Science" value="<?= htmlspecialchars($_POST['qualification'] ?? '') ?>">
            </div>

        </div>

        <div class="mt-4 d-flex gap-3">
            <button type="submit" name="add_teacher" class="btn btn-primary px-4 py-2 rounded-pill">
                <i class="fa-solid fa-check me-1"></i> Register Faculty Member
            </button>
            <a href="teachers.php" class="btn btn-light border px-4 py-2 rounded-pill">Cancel</a>
        </div>
    </form>
</div>

</div>
</body>
</html>