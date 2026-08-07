<?php

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}

include "db.php";

if(isset($_POST['login']))
{
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0)
    {
        $user = $result->fetch_assoc();

        // Check password via password_verify or legacy plaintext fallback
        $passwordMatches = false;
        if(password_verify($password, $user['password']))
        {
            $passwordMatches = true;
        }
        elseif($password === $user['password'])
        {
            $passwordMatches = true;
            // Transparently re-hash legacy plaintext password to secure hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if($updateStmt)
            {
                $updateStmt->bind_param("si", $newHash, $user['id']);
                $updateStmt->execute();
            }
        }

        if($passwordMatches)
        {
            if(isset($user['status']) && $user['status'] === 'Inactive')
            {
                echo "
                <script>
                alert('Your account is inactive. Please contact the administrator.');
                window.location='../login.php';
                </script>
                ";
                exit();
            }

            $_SESSION['user'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];

            if($user['role'] == "admin")
            {
                header("Location: ../admin/dashboard.php");
            }
            elseif($user['role'] == "teacher")
            {
                header("Location: ../teacher/dashboard.php");
            }
            elseif($user['role'] == "student")
            {
                header("Location: ../student/dashboard.php");
            }
            elseif($user['role'] == "parent")
            {
                header("Location: ../parent/dashboard.php");
            }
            else
            {
                header("Location: ../login.php");
            }
            exit();
        }
    }

    echo "
    <script>
    alert('Invalid Email or Password');
    window.location='../login.php';
    </script>
    ";
}
?>