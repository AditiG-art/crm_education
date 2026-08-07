<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



if(isset($_POST['add_course']))
{


$course_name = $_POST['course_name'];

$duration = $_POST['duration'];

$fees = $_POST['fees'];

$teacher = $_POST['teacher'];



$sql="INSERT INTO courses
(course_name,duration,fees,teacher)

VALUES(?,?,?,?)";



$stmt=$conn->prepare($sql);


$stmt->bind_param(
"ssss",
$course_name,
$duration,
$fees,
$teacher
);



if($stmt->execute())
{

echo "

<script>

alert('Course Added Successfully');

window.location='courses.php';

</script>

";

}


}


?>



<!DOCTYPE html>
<html>
<head>
<title>Add Course | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Add New Course";
include "../includes/topbar.php";
?>





<div class="panel mt-4">



<div class="card p-4 shadow">



<form method="POST">



<div class="mb-3">


<label>Course Name</label>


<input 
type="text"
name="course_name"
class="form-control"
placeholder="Enter course name"
required>


</div>





<div class="mb-3">


<label>Duration</label>


<input 
type="text"
name="duration"
class="form-control"
placeholder="Example: 3 Years">


</div>





<div class="mb-3">


<label>Fees</label>


<input 
type="text"
name="fees"
class="form-control"
placeholder="Example: 50000">


</div>





<div class="mb-3">


<label>Assigned Teacher</label>


<input 
type="text"
name="teacher"
class="form-control"
placeholder="Teacher Name">


</div>





<button 
name="add_course"
class="btn btn-primary">


Save Course


</button>




</form>


</div>


</div>


</div>


</body>

</html>