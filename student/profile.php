<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="student")
{
    header("Location:../login.php");
    exit();
}



$email = $_SESSION['email'];



// Fetch Student Data

$query = mysqli_query(
$conn,
"SELECT * FROM students WHERE email='$email'"
);



$student = mysqli_fetch_assoc($query);



if(!$student)
{
    die("Student profile not found");
}


?>


<!DOCTYPE html>

<html>

<head>

<title>My Profile | Student Panel</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<link rel="stylesheet" href="../assets/css/dashboard.css">



<style>


.profile-card{

background:white;

padding:35px;

border-radius:25px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

}


.profile-header{

display:flex;

align-items:center;

gap:25px;

margin-bottom:30px;

}



.profile-icon{

height:100px;

width:100px;

border-radius:50%;

background:linear-gradient(135deg,#2563eb,#7c3aed);

display:flex;

align-items:center;

justify-content:center;

color:white;

font-size:45px;

}



.info-box{

display:grid;

grid-template-columns:repeat(2,1fr);

gap:20px;

}



.info-item{

background:#f8fafc;

padding:18px;

border-radius:15px;

}


.info-item i{

color:#2563eb;

margin-right:10px;

}



@media(max-width:700px){

.info-box{

grid-template-columns:1fr;

}

}


</style>


</head>


<body>


<div class="wrapper">


<?php include "../includes/student_sidebar.php"; ?>



<div class="main-content">



<div class="topbar">


<h3>
My Profile
</h3>



<div class="profile">


<i class="fa-solid fa-user-circle fa-2x"></i>


<div>

<strong>
<?php echo $student['full_name']; ?>
</strong>

<br>

<small>
Student
</small>


</div>


</div>


</div>





<div class="profile-card">


<div class="profile-header">


<div class="profile-icon">

<i class="fa-solid fa-user"></i>

</div>



<div>

<h2>

<?php echo $student['full_name']; ?>

</h2>


<p>

Student Profile

</p>


</div>


</div>





<div class="info-box">



<div class="info-item">

<i class="fa-solid fa-envelope"></i>

<strong>Email:</strong>

<br>

<?php echo $student['email']; ?>

</div>





<div class="info-item">

<i class="fa-solid fa-phone"></i>

<strong>Phone:</strong>

<br>

<?php echo $student['phone']; ?>

</div>




<div class="info-item">

<i class="fa-solid fa-calendar"></i>

<strong>Date of Birth:</strong>

<br>

<?php echo !empty($student['date_of_birth']) ? htmlspecialchars($student['date_of_birth']) : 'Not Added'; ?>

</div>




<div class="info-item">

<i class="fa-solid fa-graduation-cap"></i>

<strong>Course:</strong>

<br>

<?php echo $student['course']; ?>

</div>





<div class="info-item">

<i class="fa-solid fa-venus-mars"></i>

<strong>Gender:</strong>

<br>

<?php echo $student['gender']; ?>

</div>





<div class="info-item">

<i class="fa-solid fa-location-dot"></i>

<strong>Address:</strong>

<br>

<?php echo $student['address']; ?>

</div>



</div>


</div>



</div>


</div>


</body>

</html>