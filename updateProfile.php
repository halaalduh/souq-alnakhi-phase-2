<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $contact_phone = trim($_POST["contact_phone"] ?? "");
    $contact_email = trim($_POST["contact_email"] ?? "");

    $current_password = trim($_POST["current_password"] ?? "");
    $new_password = trim($_POST["new_password"] ?? "");
    $confirm_new_password = trim($_POST["confirm_new_password"] ?? "");

    if ($full_name === "" || $email === "") {
        header("Location: profile.php?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: profile.php?error=email");
        exit();
    }

    $user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
    $user_result = mysqli_query($conn, $user_query);

    if (!$user_result || mysqli_num_rows($user_result) == 0) {
        header("Location: login.php");
        exit();
    }

    $user = mysqli_fetch_assoc($user_result);

    if ($user["role"] === "farmer") {
        if ($contact_phone !== "") {
            if (!ctype_digit($contact_phone) || strlen($contact_phone) != 10) {
                header("Location: profile.php?error=phone");
                exit();
            }
        }

        if ($contact_email !== "" && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            header("Location: profile.php?error=contactemail");
            exit();
        }
    }

    $full_name_safe = mysqli_real_escape_string($conn, $full_name);
    $email_safe = mysqli_real_escape_string($conn, $email);

    $check_email = "SELECT id FROM users WHERE email = '$email_safe' AND id != '$user_id' LIMIT 1";
    $check_result = mysqli_query($conn, $check_email);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        header("Location: profile.php?error=exists");
        exit();
    }

    $update_user = "UPDATE users 
                    SET full_name = '$full_name_safe',
                        email = '$email_safe'
                    WHERE id = '$user_id'";

    if (!mysqli_query($conn, $update_user)) {
        die("Update user failed: " . mysqli_error($conn));
    }

    if ($user["role"] === "farmer") {
        $contact_phone_safe = mysqli_real_escape_string($conn, $contact_phone);
        $contact_email_safe = mysqli_real_escape_string($conn, $contact_email);

        $farm_check = "SELECT id FROM farms WHERE farmer_user_id = '$user_id' LIMIT 1";
        $farm_result = mysqli_query($conn, $farm_check);

        if (!$farm_result) {
            die("Farm check failed: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($farm_result) > 0) {
            $update_farm = "UPDATE farms
                            SET contact_phone = '$contact_phone_safe',
                                contact_email = '$contact_email_safe'
                            WHERE farmer_user_id = '$user_id'";

            if (!mysqli_query($conn, $update_farm)) {
                die("Update farm failed: " . mysqli_error($conn));
            }
        }
    }

    $_SESSION["full_name"] = $full_name;
    $_SESSION["email"] = $email;

    $wants_password_change =
        $current_password !== "" ||
        $new_password !== "" ||
        $confirm_new_password !== "";

    if ($wants_password_change) {

        if ($current_password === "") {
            header("Location: profile.php?error=oldpassword");
            exit();
        }

        if (!password_verify($current_password, $user["password"])) {
            header("Location: profile.php?error=oldpassword");
            exit();
        }

        if ($new_password === "" || $confirm_new_password === "") {
            header("Location: profile.php?error=newpasswordempty");
            exit();
        }

        if (strlen($new_password) < 4) {
            header("Location: profile.php?error=passwordlength");
            exit();
        }

        if ($new_password !== $confirm_new_password) {
            header("Location: profile.php?error=passwordmatch");
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $hashed_password_safe = mysqli_real_escape_string($conn, $hashed_password);

        $update_password = "UPDATE users 
                            SET password = '$hashed_password_safe'
                            WHERE id = '$user_id'";

        if (!mysqli_query($conn, $update_password)) {
            die("Update password failed: " . mysqli_error($conn));
        }

        header("Location: profile.php?success=password");
        exit();
    }

    header("Location: profile.php?success=profile");
    exit();
}
?>
