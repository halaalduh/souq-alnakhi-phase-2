<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $role = trim($_POST["role"] ?? "");

    if ($full_name === "" || $email === "" || $password === "" || $confirm_password === "" || $role === "") {
        $message = "Please fill all fields.";
        $messageType = "error-message";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error-message";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error-message";
    } elseif ($role !== "customer" && $role !== "farmer") {
        $message = "Invalid role selected.";
        $messageType = "error-message";
    } else {
        $email_escaped = mysqli_real_escape_string($conn, $email);

        $check_sql = "SELECT id FROM users WHERE email = '$email_escaped' LIMIT 1";
        $check_result = mysqli_query($conn, $check_sql);

        if (!$check_result) {
            die("Check query failed: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($check_result) > 0) {
            $message = "Email already registered.";
            $messageType = "error-message";
        } else {
            $full_name_escaped = mysqli_real_escape_string($conn, $full_name);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role_escaped = mysqli_real_escape_string($conn, $role);

            $insert_sql = "INSERT INTO users (full_name, email, password, role)
                           VALUES ('$full_name_escaped', '$email_escaped', '$hashed_password', '$role_escaped')";

            $insert_result = mysqli_query($conn, $insert_sql);

            if ($insert_result) {
                header("Location: home.php");
                exit();
            } else {
                die("Insert failed: " . mysqli_error($conn));
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Souq Al-Nakhil</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-body">

  <header class="site-header">
    <div class="header-container">
      <a href="home.php" class="brand">
        <img src="logo.png" alt="Souq Al-Nakhil Logo" class="brand-logo">
        <div class="brand-text">
          <span class="brand-title">Souq Al-Nakhil</span>
          <span class="brand-subtitle">Fresh Saudi Dates Marketplace</span>
        </div>
      </a>

      <nav class="site-nav">
        <a href="home.php" class="nav-btn nav-btn-outline">Home</a>
        <a href="login.php" class="nav-btn nav-btn-outline">Login</a>
        <a href="register.php" class="nav-btn nav-btn-solid">Register</a>
      </nav>
    </div>
  </header>

  <main class="auth-wrapper">
    <div class="auth-layout">

      <section class="auth-left">
        <div class="hero-text">
          <h1>Join the Marketplace<br>of Trusted Saudi Farms</h1>
          <p>
            Register as a customer or farmer and become part of a platform
            that supports local agriculture and transparent shopping.
          </p>
        </div>

        <div class="hero-image-box">
          <img src="images/farmer-register.jpg" alt="Saudi Farm" class="hero-image">
        </div>
      </section>

      <section class="auth-right">
        <div class="auth-card">
          <h2>Create Account</h2>
          <p class="auth-subtitle">Sign up to continue</p>

          <form class="auth-form" method="POST" action="register.php">
            <label for="full-name">Full Name</label>
            <input type="text" id="full-name" name="full_name" placeholder="Enter your full name" required>

            <label for="register-email">Email</label>
            <input type="email" id="register-email" name="email" placeholder="Enter your email" required>

            <label for="register-password">Password</label>
            <input type="password" id="register-password" name="password" placeholder="Create a password" required>

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>

            <div class="auth-actions">
              <button type="submit" class="auth-btn auth-btn-customer" name="role" value="customer">Register as Customer</button>
              <button type="submit" class="auth-btn auth-btn-farmer" name="role" value="farmer">Register as Farmer</button>
            </div>

            <?php if ($message !== "") { ?>
              <p class="form-message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
              </p>
            <?php } ?>
          </form>

          <p class="auth-link">
            Already have an account? <a href="login.php">Login</a>
          </p>
        </div>
      </section>

    </div>
  </main>

  <footer class="site-footer">
    <div class="footer-container">
      <div class="footer-brand">
        <h3>Souq Al-Nakhil</h3>
        <p>
          A trusted Saudi marketplace connecting local farms with customers,
          making it easier to discover fresh, high-quality dates from different regions.
        </p>
      </div>

      <div class="footer-contact">
        <h4>Contact</h4>
        <p>Email: support@souqalnakhil.com</p>
        <p>Riyadh, Saudi Arabia</p>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2026 Souq Al-Nakhil. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>