<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = trim($_POST["role"]);

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        header("Location: register.php?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=email");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: register.php?error=match");
        exit();
    }

    if ($role !== "customer" && $role !== "farmer") {
        header("Location: register.php?error=server");
        exit();
    }

    $full_name_safe = mysqli_real_escape_string($conn, $full_name);
    $email_safe = mysqli_real_escape_string($conn, $email);
    $role_safe = mysqli_real_escape_string($conn, $role);

    $check = "SELECT * FROM users WHERE email='$email_safe' LIMIT 1";
    $result = mysqli_query($conn, $check);

    if (!$result) {
        header("Location: register.php?error=server");
        exit();
    }

    if (mysqli_num_rows($result) > 0) {
        header("Location: register.php?error=exists");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert = "INSERT INTO users (full_name, email, password, role)
               VALUES ('$full_name_safe', '$email_safe', '$hashed_password', '$role_safe')";

    if (mysqli_query($conn, $insert)) {

        $user_id = mysqli_insert_id($conn);

        $_SESSION["user_id"] = $user_id;
        $_SESSION["full_name"] = $full_name;
        $_SESSION["email"] = $email;
        $_SESSION["role"] = $role;

        if ($role === "farmer") {
            header("Location: Farmerdashboard.php");
        } else {
            header("Location: home.php");
        }
        exit();

    } else {
        header("Location: register.php?error=server");
        exit();
    }
}
?>