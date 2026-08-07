<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



$id = $_GET['id'];



$query = "SELECT * FROM teachers WHERE id=?";


$stmt = $conn->prepare($query);

$stmt->bind_param("i",$id);

$stmt->execute();


$result = $stmt->get_result();


$teacher = $result->fetch_assoc();





if(isset($_POST['update_teacher']))
{


$name = $_POST['full_name'];

$email = $_POST['email'];

$phone = $_POST['phone'];

$subject = $_POST['subject'];

$qualification = $_POST['qualification'];

$experience = $_POST['experience'];





$sql = "UPDATE teachers SET

full_name=?,
email=?,
phone=?,
subject=?,
qualification=?,
experience=?

WHERE id=?";



$stmt = $conn->prepare($sql);



$stmt->bind_param(

"ssssssi",

$name,

$email,

$phone,

$subject,

$qualification,

$experience,

$id

);



if($stmt->execute())
{

echo "

<script>

alert('Teacher Updated Successfully');

window.location='teachers.php';

</script>

";

}


}


?>



<!DOCTYPE html>

<html>

<head>


<title>Edit Teacher | Smart Campus CRM</title>


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


label{

font-weight:600;

}


.form-control{

padding:13px;

border-radius:12px;

}


</style>


</head>


<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Edit Teacher";
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
value="<?php echo $teacher['full_name']; ?>"
required>

</div>



<div class="col-md-6 mb-3">

<label>Email</label>

<input 
type="email"
name="email"
class="form-control"
value="<?php echo $teacher['email']; ?>"
required>

</div>



<div class="col-md-6 mb-3">

<label>Phone</label>

<input 
type="text"
name="phone"
class="form-control"
value="<?php echo $teacher['phone']; ?>">

</div>




<div class="col-md-6 mb-3">

<label>Subject</label>

<input 
type="text"
name="subject"
class="form-control"
value="<?php echo $teacher['subject']; ?>">

</div>




<div class="col-md-6 mb-3">

<label>Qualification</label>

<input 
type="text"
name="qualification"
class="form-control"
value="<?php echo $teacher['qualification']; ?>">

</div>




<div class="col-md-6 mb-3">

<label>Experience</label>

<input 
type="text"
name="experience"
class="form-control"
value="<?php echo $teacher['experience']; ?>">

</div>



</div>



<button 
name="update_teacher"
class="btn btn-primary">

<i class="fa-solid fa-save"></i>

Update Teacher

</button>



</form>


</div>


</div>


</div>


</body>

</html>