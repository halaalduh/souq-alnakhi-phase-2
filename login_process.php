<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

  
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }

  
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=email");
        exit();
    }


    $query = "SELECT * FROM users WHERE email='$email' AND role='$role'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

   
        if (password_verify($password, $user['password'])) {

     
            if ($role == "admin") {
                header("Location: AdminDashboard.php");
            } elseif ($role == "farmer") {
                header("Location: Farmerdashboard.php");
            } else {
                header("Location: home.php");
            }

            exit();

        } else {
            header("Location: login.php?error=invalid");
            exit();
        }

    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}
?>