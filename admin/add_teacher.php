<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



if(isset($_POST['add_teacher']))
{
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $qualification = trim($_POST['qualification']);
    $experience = trim($_POST['experience']);

    $sql = "INSERT INTO teachers (full_name,email,phone,subject,qualification,experience) VALUES(?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $qualification, $experience);

    if($stmt->execute())
    {
        // Check if user login already exists
        $userCheck = $conn->prepare("SELECT id FROM users WHERE email=?");
        $userCheck->bind_param("s", $email);
        $userCheck->execute();
        if($userCheck->get_result()->num_rows == 0) {
            $defaultPassword = password_hash("teacher123", PASSWORD_DEFAULT);
            $userInsert = $conn->prepare("INSERT INTO users (full_name, email, password, role, phone) VALUES (?, ?, ?, 'teacher', ?)");
            $userInsert->bind_param("ssss", $name, $email, $defaultPassword, $phone);
            $userInsert->execute();
        }

        echo "<script>alert('Teacher Added Successfully'); window.location='teachers.php';</script>";
        exit();
    }
    else
    {
        echo "<script>alert('Error Adding Teacher');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Teacher | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
.form-card{
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}
label{ font-weight:600; }
.form-control{ padding:13px; border-radius:12px; }
</style>
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Add New Teacher";
include "../includes/topbar.php";
?>





<div class="panel mt-4">


<div class="form-card">



<form method="POST">



<div class="row">


<div class="col-md-6 mb-3">


<label>Full Name</label>


<input 
type="text"
name="full_name"
class="form-control"
placeholder="Enter teacher name"
required>


</div>





<div class="col-md-6 mb-3">


<label>Email</label>


<input 
type="email"
name="email"
class="form-control"
placeholder="Enter email"
required>


</div>





<div class="col-md-6 mb-3">


<label>Phone</label>


<input 
type="text"
name="phone"
class="form-control"
placeholder="Phone number">


</div>





<div class="col-md-6 mb-3">


<label>Subject</label>


<input 
type="text"
name="subject"
class="form-control"
placeholder="Subject taught">


</div>





<div class="col-md-6 mb-3">


<label>Qualification</label>


<input 
type="text"
name="qualification"
class="form-control"
placeholder="Qualification">


</div>





<div class="col-md-6 mb-3">


<label>Experience</label>


<input 
type="text"
name="experience"
class="form-control"
placeholder="Years of experience">


</div>



</div>





<button 
name="add_teacher"
class="btn btn-primary">


<i class="fa-solid fa-save"></i>

Save Teacher


</button>



</form>


</div>


</div>



</div>


</body>

</html>