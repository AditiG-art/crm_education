<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role'] != "admin")
{
    header("Location:../login.php");
    exit();
}

if(isset($_GET['id']))
{

$id=$_GET['id'];



$stmt=$conn->prepare(
"DELETE FROM courses WHERE id=?"
);



$stmt->bind_param("i",$id);



$stmt->execute();


echo "

<script>

alert('Course Deleted Successfully');

window.location='courses.php';

</script>

";


}


?>