<?php
session_start();
include "config/db.php";

if (isset($_SESSION['user']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == "admin") {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == "teacher") {
        header("Location: teacher/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == "student") {
        header("Location: student/dashboard.php");
        exit();
    }
}

header("Location: login.php");
exit();
?>