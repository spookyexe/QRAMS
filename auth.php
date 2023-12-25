<?php
session_start();
include 'db_conn.php';

if (isset($_POST['email']) && isset($_POST['password'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email)) {
        header("Location: login.php?error=Email is Required.");
    } else if (empty($password)) {
        header("Location: login.php?error=Password is Required.&email=$email");
    } else {
        $stmt = $connect->prepare("SELECT * FROM advisers WHERE email=?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch();

            $user_id = $user['id'];
            $user_email = $user['email'];
            $user_password = $user['password'];
            $user_full_name = $user['full_name'];
            $user_gradeLevel = $user['gradeLevel'];
            $user_section = $user['section'];

            if ($email === $user_email) {
                if (password_verify($password, $user_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_email'] = $user_email;
                    $_SESSION['user_full_name'] = $user_full_name;
                    $_SESSION['user_gradeLevel'] = $user_gradeLevel;
                    $_SESSION['user_section'] = $user_section;

                    // Check if the user is the admin
                    if ($email === 'admin@qrams.com') {
                        header("Location: admin.php");
                    } else {
                        header("Location: index.php");
                    }
                } else {
                    header("Location: login.php?error=Incorrect Email or Password.&email=$email");
                }
            } else {
                header("Location: login.php?error=Incorrect Email or Password.&email=$email");
            }
        } else {
            header("Location: login.php?error=Incorrect Email or Password.&email=$email");
        }
    }
}
