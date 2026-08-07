<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}



if(isset($_GET['id']))
{

$id = $_GET['id'];



$sql = "DELETE FROM students WHERE id=?";


$stmt = $conn->prepare($sql);


$stmt->bind_param("i",$id);



if($stmt->execute())
{

echo "

<script>

alert('Student Deleted Successfully');

window.location='students.php';

</script>

";

}


else
{

echo "

<script>

alert('Error deleting student');

window.location='students.php';

</script>

";

}


}


?>