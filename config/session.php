<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}

function checkLogin()
{
    if(!isset($_SESSION['user']))
    {
        header("Location: ../login.php");
        exit();
    }
}

function checkRole($role)
{
    checkLogin();
    if(!isset($_SESSION['role']) || $_SESSION['role'] !== $role)
    {
        header("Location: ../login.php");
        exit();
    }
}

?>