<?php

session_start();

include "../config/db.php";

if(!isset($_SESSION['user']) || $_SESSION['role']!="admin")
{
    header("Location:../login.php");
    exit();
}

if(!isset($_GET['id']))
{
    header("Location:students.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows==0)
{
    header("Location:students.php");
    exit();
}

$student = $result->fetch_assoc();



if(isset($_POST['update_student']))
{

$name=$_POST['full_name'];

$email=$_POST['email'];

$phone=$_POST['phone'];

$gender=$_POST['gender'];

$dob=$_POST['dob'];

$course=$_POST['course'];

$address=$_POST['address'];


$update=$conn->prepare("UPDATE students
SET
full_name=?,
email=?,
phone=?,
gender=?,
date_of_birth=?,
course=?,
address=?
WHERE id=?");

$update->bind_param(
"sssssssi",
$name,
$email,
$phone,
$gender,
$dob,
$course,
$address,
$id
);

if($update->execute())
{

echo "<script>
alert('Student Updated Successfully');
window.location='students.php';
</script>";

exit();

}

else
{

echo "<script>
alert('Update Failed');
</script>";

}

}

?>

<!DOCTYPE html>

<html>

<head>

<title>Edit Student | Smart Campus CRM</title>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/dashboard.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.form-card{

background:white;

padding:35px;

border-radius:20px;

box-shadow:0 15px 35px rgba(0,0,0,.08);

}

.form-card label{

font-weight:600;

margin-bottom:8px;

}

.form-control,
.form-select{

padding:13px;

border-radius:12px;

}

.update-btn{

background:#2563EB;

color:white;

border:none;

padding:12px 28px;

border-radius:12px;

font-weight:600;

}

.update-btn:hover{

background:#1D4ED8;

color:white;

}

</style>

</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php

$pageTitle="Edit Student";

include "../includes/topbar.php";

?>

<div class="header">

<h1>

<i class="fa-solid fa-user-pen"></i>

Edit Student

</h1>

<p>

Update student information.

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
value="<?php echo htmlspecialchars($student['full_name']); ?>"
required>

</div>



<div class="col-md-6 mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo htmlspecialchars($student['email']); ?>"
required>

</div>



<div class="col-md-6 mb-3">

<label>Phone</label>

<input
type="text"
name="phone"
class="form-control"
value="<?php echo htmlspecialchars($student['phone']); ?>">

</div>



<div class="col-md-6 mb-3">

<label>Gender</label>

<select
name="gender"
class="form-select">

<option value="Male"
<?php if($student['gender']=="Male") echo "selected"; ?>>
Male
</option>

<option value="Female"
<?php if($student['gender']=="Female") echo "selected"; ?>>
Female
</option>

<option value="Other"
<?php if($student['gender']=="Other") echo "selected"; ?>>
Other
</option>

</select>

</div>



<div class="col-md-6 mb-3">

<label>Date of Birth</label>

<input
type="date"
name="dob"
class="form-control"
value="<?php echo $student['date_of_birth']; ?>">

</div>



<div class="col-md-6 mb-3">

<label>Course</label>

<input
type="text"
name="course"
class="form-control"
value="<?php echo htmlspecialchars($student['course']); ?>">

</div>



<div class="col-12 mb-3">

<label>Address</label>

<textarea
name="address"
class="form-control"
rows="4"><?php echo htmlspecialchars($student['address']); ?></textarea>

</div>

</div>

<div class="mt-4">

<button
type="submit"
name="update_student"
class="update-btn">

<i class="fa-solid fa-floppy-disk"></i>

Update Student

</button>



<a
href="students.php"
class="btn btn-secondary ms-2">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</form>

</div>

</div>

</div>

</body>

</html>