<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



$id=$_GET['id'];



$stmt=$conn->prepare("SELECT * FROM courses WHERE id=?");

$stmt->bind_param("i",$id);

$stmt->execute();


$result=$stmt->get_result();


$course=$result->fetch_assoc();





if(isset($_POST['update_course']))
{


$course_name=$_POST['course_name'];

$duration=$_POST['duration'];

$fees=$_POST['fees'];

$teacher=$_POST['teacher'];



$sql="UPDATE courses SET

course_name=?,
duration=?,
fees=?,
teacher=?

WHERE id=?";



$stmt=$conn->prepare($sql);


$stmt->bind_param(

"ssssi",

$course_name,

$duration,

$fees,

$teacher,

$id

);



if($stmt->execute())
{

echo "

<script>

alert('Course Updated Successfully');

window.location='courses.php';

</script>

";

}


}


?>



<!DOCTYPE html>
<html>
<head>
<title>Edit Course | Smart Campus CRM</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php
$pageTitle = "Edit Course";
include "../includes/topbar.php";
?>





<div class="panel mt-4">


<div class="card p-4 shadow">


<form method="POST">



<label>Course Name</label>

<input 
type="text"
name="course_name"
class="form-control mb-3"
value="<?php echo $course['course_name']; ?>">





<label>Duration</label>

<input 
type="text"
name="duration"
class="form-control mb-3"
value="<?php echo $course['duration']; ?>">





<label>Fees</label>

<input 
type="text"
name="fees"
class="form-control mb-3"
value="<?php echo $course['fees']; ?>">





<label>Teacher</label>

<input 
type="text"
name="teacher"
class="form-control mb-3"
value="<?php echo $course['teacher']; ?>">





<button 
name="update_course"
class="btn btn-primary">

Update Course

</button>



</form>


</div>


</div>


</div>


</body>

</html>