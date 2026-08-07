<?php

session_start();

include "../config/db.php";


if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}




$search = "";

$course = "";

$gender = "";


if(isset($_GET['search']))
{
    $search = $_GET['search'];
}


if(isset($_GET['course']))
{
    $course = $_GET['course'];
}


if(isset($_GET['gender']))
{
    $gender = $_GET['gender'];
}



$query = "SELECT * FROM students WHERE 1";



if($search != "")
{
    $query .= " AND 
    (full_name LIKE '%$search%' 
    OR email LIKE '%$search%' 
    OR phone LIKE '%$search%')";
}



if($course != "")
{
    $query .= " AND course='$course'";
}



if($gender != "")
{
    $query .= " AND gender='$gender'";
}



$query .= " ORDER BY id DESC";



$result=mysqli_query($conn,$query);



$countQuery=mysqli_query($conn,"SELECT COUNT(*) as total FROM students");

$total=mysqli_fetch_assoc($countQuery)['total'];

?>



?>


<!DOCTYPE html>

<html>

<head>

<title>Students | Smart Campus CRM</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="../assets/css/dashboard.css">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


</head>


<body>



<div class="main-content" style="margin-left:0;width:100%">



<div class="topbar">


<h3>

<i class="fa-solid fa-user-graduate"></i>

Students Management

</h3>


<a href="add_student.php" class="btn btn-primary">

<i class="fa-solid fa-plus"></i>

Add Student

</a>


</div>

<div class="panel mt-4">


<form method="GET">


<div class="row g-3">


<div class="col-md-4">


<input 
type="text"
name="search"
class="form-control"
placeholder="Search student..."
value="<?php echo $search; ?>">


</div>



<div class="col-md-3">


<select name="course" class="form-select">


<option value="">
All Courses
</option>


<option>
BCA
</option>


<option>
B.Tech AI
</option>


<option>
Data Science
</option>


<option>
Computer Science
</option>


</select>


</div>





<div class="col-md-3">


<select name="gender" class="form-select">


<option value="">
All Gender
</option>


<option>
Male
</option>


<option>
Female
</option>


<option>
Other
</option>


</select>


</div>




<div class="col-md-2">


<button class="btn btn-primary w-100">


<i class="fa-solid fa-search"></i>

Search


</button>


</div>



</div>


</form>


</div>

<div class="alert alert-primary">

<i class="fa-solid fa-users"></i>

Total Students:

<strong>
<?php echo $total; ?>
</strong>

</div>
<div class="panel mt-4">



<div class="table-responsive">


<table class="table align-middle">


<thead class="table-dark">


<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Phone</th>

<th>Course</th>

<th>Action</th>


</tr>


</thead>



<tbody>


<?php while($row=mysqli_fetch_assoc($result)){ ?>


<tr>


<td>
<?php echo $row['id']; ?>
</td>


<td>

<strong>

<?php echo $row['full_name']; ?>

</strong>

</td>



<td>

<?php echo $row['email']; ?>

</td>



<td>

<?php echo $row['phone']; ?>

</td>



<td>

<?php echo $row['course']; ?>

</td>



<td>


<a href="edit_student.php?id=<?php echo $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="fa-solid fa-pen"></i>

</a>



<a href="delete_student.php?id=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete Student?')">


<i class="fa-solid fa-trash"></i>


</a>



</td>


</tr>



<?php } ?>


</tbody>



</table>


</div>


</div>



</div>




</body>

</html>