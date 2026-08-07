<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



if(isset($_POST['add_student']))
{


$name = $_POST['full_name'];

$email = $_POST['email'];

$phone = $_POST['phone'];

$gender = $_POST['gender'];

$dob = $_POST['dob'];

$course = $_POST['course'];

$address = $_POST['address'];



$sql = "INSERT INTO students
(full_name,email,phone,gender,date_of_birth,course,address)

VALUES(?,?,?,?,?,?,?)";



$stmt = $conn->prepare($sql);


$stmt->bind_param(

"sssssss",

$name,

$email,

$phone,

$gender,

$dob,

$course,

$address

);



if($stmt->execute())
{

echo "

<script>

alert('Student Added Successfully');

window.location='students.php';

</script>

";

}

else
{

echo "

<script>

alert('Error Adding Student');

</script>

";

}


}

?>



<!DOCTYPE html>

<html>

<head>

<title>Add Student | Smart Campus CRM</title>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="../assets/css/dashboard.css">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<style>


.form-card{

background:white;

padding:35px;

border-radius:25px;

box-shadow:0 15px 35px rgba(0,0,0,0.08);

}



.form-card label{

font-weight:600;

margin-bottom:8px;

color:#0f172a;

}



.form-control,
.form-select{

padding:13px;

border-radius:12px;

border:1px solid #e2e8f0;

}



.form-control:focus,
.form-select:focus{

border-color:#2563eb;

box-shadow:0 0 0 .2rem rgba(37,99,235,.15);

}



.save-btn{

background:#2563eb;

color:white;

padding:13px 30px;

border:none;

border-radius:12px;

font-weight:600;

}



.save-btn:hover{

background:#1d4ed8;

}



</style>


</head>



<body>



<?php include "../includes/sidebar.php"; ?>



<div class="main">



<?php

$pageTitle="Add New Student";

include "../includes/topbar.php";

?>





<div class="header">


<h1>

<i class="fa-solid fa-user-plus"></i>

Add New Student

</h1>


<p>

Create a new student record in Smart Campus CRM.

</p>


</div>







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

placeholder="Enter student name"

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


<label>Gender</label>


<select name="gender" class="form-select">


<option value="Male">
Male
</option>


<option value="Female">
Female
</option>


<option value="Other">
Other
</option>


</select>


</div>







<div class="col-md-6 mb-3">


<label>Date of Birth</label>


<input

type="date"

name="dob"

class="form-control">


</div>







<div class="col-md-6 mb-3">


<label>Course</label>


<input

type="text"

name="course"

class="form-control"

placeholder="Enter course">


</div>







<div class="col-12 mb-3">


<label>Address</label>


<textarea

name="address"

class="form-control"

rows="3"

placeholder="Enter address"></textarea>


</div>



</div>





<button

type="submit"

name="add_student"

class="save-btn">


<i class="fa-solid fa-save"></i>

Save Student


</button>





<a href="students.php"

class="btn btn-secondary ms-2">

Back

</a>





</form>


</div>


</div>





</div>



</body>


</html>