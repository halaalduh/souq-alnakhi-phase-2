<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];

    // تحقق من الفراغ
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: register.php?error=empty");
        exit();
    }

    // تحقق من الايميل
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=email");
        exit();
    }

    // تحقق من تطابق الباسورد
    if ($password !== $confirm_password) {
        header("Location: register.php?error=match");
        exit();
    }

    // تحقق إذا الايميل موجود
    $check = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        header("Location: register.php?error=exists");
        exit();
    }

    // 🔥 هنا أهم سطر (تشفير الباسورد)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // إدخال المستخدم
    $insert = "INSERT INTO users (full_name, email, password, role)
               VALUES ('$full_name', '$email', '$hashed_password', '$role')";

    if (mysqli_query($conn, $insert)) {
        header("Location: register.php?success=1");
        exit();
    } else {
        header("Location: register.php?error=server");
        exit();
    }
}
?>