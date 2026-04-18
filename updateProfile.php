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
    $current_password = trim($_POST["current_password"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_new_password = trim($_POST["confirm_new_password"]);

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

    $user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
    $user_result = mysqli_query($conn, $user_query);

    if (!$user_result || mysqli_num_rows($user_result) == 0) {
        header("Location: profile.php?error=server");
        exit();
    }

    $user = mysqli_fetch_assoc($user_result);

    $update_profile = "UPDATE users
                       SET full_name = '$full_name_safe', email = '$email_safe'
                       WHERE id = '$user_id'";

    if (!mysqli_query($conn, $update_profile)) {
        header("Location: profile.php?error=server");
        exit();
    }

    $_SESSION["full_name"] = $full_name;
    $_SESSION["email"] = $email;

    $wants_password_change =
        !empty($current_password) ||
        !empty($new_password) ||
        !empty($confirm_new_password);

    if ($wants_password_change) {

        if (empty($current_password)) {
            header("Location: profile.php?error=oldpassword");
            exit();
        }

        if (!password_verify($current_password, $user["password"])) {
            header("Location: profile.php?error=oldpassword");
            exit();
        }

        if (empty($new_password) || empty($confirm_new_password)) {
            header("Location: profile.php?error=newpasswordempty");
            exit();
        }

        if ($new_password !== $confirm_new_password) {
            header("Location: profile.php?error=passwordmatch");
            exit();
        }

        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_password = "UPDATE users
                            SET password = '$hashed_new_password'
                            WHERE id = '$user_id'";

        if (!mysqli_query($conn, $update_password)) {
            header("Location: profile.php?error=server");
            exit();
        }

        header("Location: profile.php?success=password");
        exit();
    }

    header("Location: profile.php?success=profile");
    exit();
}
?>