<?php
$message = "";
$messageType = "";

if (isset($_GET['success'])) {
    $message = "Account created successfully.";
    $messageType = "success-message";
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === "empty") {
        $message = "Please fill all fields.";
    } elseif ($_GET['error'] === "email") {
        $message = "Invalid email format.";
    } elseif ($_GET['error'] === "match") {
        $message = "Passwords do not match.";
    } elseif ($_GET['error'] === "exists") {
        $message = "Email already registered.";
    } else {
        $message = "Something went wrong.";
    }
    $messageType = "error-message";
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
        <a href="home.php">Home</a>
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

          <form class="auth-form" method="POST" action="signup.php">
            <label for="full-name">Full Name</label>
            <input type="text" id="full-name" name="full_name" placeholder="Enter your full name">

            <label for="register-email">Email</label>
            <input type="email" id="register-email" name="email" placeholder="Enter your email">

            <label for="register-password">Password</label>
            <input type="password" id="register-password" name="password" placeholder="Create a password">

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password">

            <div class="auth-actions">
              <button type="submit" class="auth-btn auth-btn-customer" name="role" value="customer">Register as Customer</button>
              <button type="submit" class="auth-btn auth-btn-farmer" name="role" value="farmer">Register as Farmer</button>
            </div>

            <p class="form-message <?php echo $messageType; ?>">
              <?php echo htmlspecialchars($message); ?>
            </p>
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