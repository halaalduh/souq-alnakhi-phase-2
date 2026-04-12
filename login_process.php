<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=email");
        exit();
    }

    $email_safe = mysqli_real_escape_string($conn, $email);

    $query = "SELECT * FROM users WHERE email='$email_safe' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        header("Location: login.php?error=server");
        exit();
    }

    if (mysqli_num_rows($result) == 0) {
        header("Location: login.php?error=notfound");
        exit();
    }

    $user = mysqli_fetch_assoc($result);

    if (!password_verify($password, $user["password"])) {
        header("Location: login.php?error=wrongpassword");
        exit();
    }

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["email"] = $user["email"];
    $_SESSION["role"] = $user["role"];

    if ($user["role"] === "admin") {
        header("Location: AdminDashboard.php");
    } elseif ($user["role"] === "farmer") {
        header("Location: Farmerdashboard.php");
    } else {
        header("Location: home.php");
    }
    exit();
}
?>
