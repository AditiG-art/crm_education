<?php

session_start();


if(isset($_SESSION['user']) && isset($_SESSION['role']))
{

    if($_SESSION['role']=="admin")
    {
        header("Location:admin/dashboard.php");
        exit();
    }


    elseif($_SESSION['role']=="teacher")
    {
        header("Location:teacher/dashboard.php");
        exit();
    }


    elseif($_SESSION['role']=="student")
    {
        header("Location:student/dashboard.php");
        exit();
    }
    elseif($_SESSION['role']=="parent")
    {
        header("Location:parent/dashboard.php");
        exit();
    }

}


?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Campus | Login</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<link rel="stylesheet" href="assets/css/login.css">


</head>


<body>


<div class="main-container">


<!-- LEFT SECTION -->

<div class="brand-section">


<div class="brand-content">


<div class="brand-logo">

<i class="fa-solid fa-building-columns"></i>

</div>


<h1>
Smart Campus
</h1>


<p>

A complete digital solution to manage
students, teachers and institute operations.

</p>



<div class="features">


<div>
<i class="fa-solid fa-user-graduate"></i>
Student Management
</div>


<div>
<i class="fa-solid fa-calendar-check"></i>
Attendance Tracking
</div>


<div>
<i class="fa-solid fa-chart-line"></i>
Smart Analytics
</div>


<div>
<i class="fa-solid fa-wallet"></i>
Fee Management
</div>


</div>



</div>

</div>





<!-- LOGIN SECTION -->


<div class="login-section">


<div class="login-card">


<h2>
Welcome Back 👋
</h2>


<p>
Login to continue
</p>



<form action="config/auth.php" method="POST">



<div class="input-box">

<i class="fa-solid fa-envelope"></i>

<input 
type="email"
name="email"
placeholder="Enter Email"
required>

</div>




<div class="input-box">


<i class="fa-solid fa-lock"></i>


<input
type="password"
id="password"
name="password"
placeholder="Enter Password"
required>



<i class="fa-solid fa-eye-slash" id="togglePassword"></i>



</div>




<div class="options">


<label>

<input type="checkbox">

Remember me

</label>



<a href="#">
Forgot Password?
</a>


</div>



<button 
class="login-btn"
name="login">


Login

<i class="fa-solid fa-arrow-right"></i>


</button>



</form>



<?php if(isset($_SESSION['reg_success'])): ?>
<div class="alert alert-success" style="border-radius:12px; font-size:13px; margin-bottom:16px;">
    <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['reg_success']) ?>
</div>
<?php unset($_SESSION['reg_success']); endif; ?>

<div class="bottom-text">

Don't have an account?

<a href="register.php">
Register
</a>


</div>



</div>



</div>



</div>



<script src="assets/js/login.js"></script>


</body>

</html>