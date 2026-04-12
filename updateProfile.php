<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);

    if (empty($full_name) || empty($email)) {
        header("Location: profile.php?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: profile.php?error=email");
        exit();
    }

    $full_name_safe = mysqli_real_escape_string($conn, $full_name);
    $email_safe = mysqli_real_escape_string($conn, $email);

    $check = "SELECT * FROM users WHERE email = '$email_safe' AND id != '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $check);

    if ($result && mysqli_num_rows($result) > 0) {
        header("Location: profile.php?error=exists");
        exit();
    }

    $update = "UPDATE users
               SET full_name = '$full_name_safe', email = '$email_safe'
               WHERE id = '$user_id'";

    if (mysqli_query($conn, $update)) {
        $_SESSION["full_name"] = $full_name;
        $_SESSION["email"] = $email;

        header("Location: profile.php?success=1");
        exit();
    } else {
        header("Location: profile.php?error=server");
        exit();
    }
}
?>