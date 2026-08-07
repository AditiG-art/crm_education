<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != "parent") {
    header("Location:../login.php");
    exit();
}

$email = $_SESSION['email'];
$msg = "";
$error = "";

// Fetch Parent info
$parentRes = mysqli_query($conn, "SELECT * FROM parents WHERE email='".mysqli_real_escape_string($conn, $email)."'");
$parent = mysqli_fetch_assoc($parentRes);

if(!$parent) {
    $uRes = mysqli_query($conn, "SELECT * FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."'");
    $uData = mysqli_fetch_assoc($uRes);
    if($uData) {
        $parent = [
            'first_name' => $uData['first_name'] ?: 'Parent',
            'last_name'  => $uData['last_name'] ?: '',
            'full_name'  => $uData['full_name'],
            'email'      => $uData['email'],
            'phone'      => '',
            'address'    => ''
        ];
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if($parent && isset($parent['id'])) {
        $upd = $conn->prepare("UPDATE parents SET phone = ?, address = ? WHERE email = ?");
        $upd->bind_param("sss", $phone, $address, $email);
        if($upd->execute()) {
            $msg = "Profile contact information updated successfully!";
            $parent['phone'] = $phone;
            $parent['address'] = $address;
        } else {
            $error = "Failed to update profile.";
        }
    } else {
        // Insert into parents table if entry wasn't present
        $fName = $parent['first_name'] ?? 'Parent';
        $lName = $parent['last_name'] ?? '';
        $fullName = $parent['full_name'] ?? 'Parent';
        $ins = $conn->prepare("INSERT INTO parents (first_name, last_name, full_name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("ssssss", $fName, $lName, $fullName, $email, $phone, $address);
        if($ins->execute()) {
            $msg = "Profile updated successfully!";
            $parent['phone'] = $phone;
            $parent['address'] = $address;
        }
    }
}

$surname = $parent['last_name'] ?? end(explode(' ', trim($parent['full_name'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | Parent Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/dashboard.css?v=5.0">
</head>
<body>
<?php include "../includes/sidebar.php"; ?>
<div class="main">
<?php $pageTitle = "Parent Profile Settings"; include "../includes/topbar.php"; ?>

<div class="page-header">
    <h1>My Account & Profile Settings ⚙️</h1>
    <p>View your profile details and update your contact information</p>
</div>

<?php if(!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="crm-card text-center p-4">
            <div class="mb-3">
                <i class="fa-solid fa-circle-user fa-5x text-primary"></i>
            </div>
            <h4 class="mb-1 fw-bold"><?= htmlspecialchars($parent['full_name']) ?></h4>
            <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">Parent Account</span>
            <hr>
            <div class="text-start">
                <p class="mb-2"><strong><i class="fa-solid fa-envelope me-2 text-primary"></i> Email:</strong> <br><span class="text-muted ms-4"><?= htmlspecialchars($parent['email']) ?></span></p>
                <p class="mb-2"><strong><i class="fa-solid fa-signature me-2 text-primary"></i> Surname (Last Name):</strong> <br><span class="badge bg-info-subtle text-info-emphasis ms-4">"<?= htmlspecialchars($surname) ?>"</span></p>
                <p class="mb-0"><strong><i class="fa-solid fa-users me-2 text-primary"></i> Linked Child Criteria:</strong> <br><small class="text-muted ms-4">All students matching surname "<?= htmlspecialchars($surname) ?>"</small></p>
            </div>
        </div>
    </div>

    <!-- Edit Contact Info -->
    <div class="col-md-8">
        <div class="crm-card">
            <div class="crm-card-header">
                <h4><i class="fa-solid fa-user-pen"></i> Update Contact Info</h4>
            </div>
            <div class="crm-card-body">
                <form action="profile.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">First Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($parent['first_name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Last Name (Surname)</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($parent['last_name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($parent['email'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 555-0199" value="<?= htmlspecialchars($parent['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Home Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter home address..."><?= htmlspecialchars($parent['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn-crm-primary mt-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>
