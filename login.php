<?php
$message = "";
$messageType = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] === "empty") {
        $message = "Please enter email and password.";
    } elseif ($_GET['error'] === "email") {
        $message = "Invalid email format.";
    } elseif ($_GET['error'] === "invalid") {
        $message = "Invalid credentials.";
    } else {
        $message = "Something went wrong.";
    }
    $messageType = "error-message";
}

if (isset($_GET['success'])) {
    $message = "Login successful.";
    $messageType = "success-message";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Souq Al-Nakhil</title>
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
          <h1>Fresh Saudi Dates,<br>Direct from Local Farms</h1>
          <p>
            A trusted Saudi marketplace that connects customers with verified local farmers,
            making it easier to discover fresh, high-quality dates from different regions.
          </p>
        </div>

        <div class="hero-image-box">
          <img src="images/farmer-login.jpg" alt="Local Farmer" class="hero-image">
        </div>
      </section>

      <section class="auth-right">
        <div class="auth-card">
          <h2>Welcome Back</h2>
          <p class="auth-subtitle">Log in to continue</p>

          <form class="auth-form" method="POST" action="login_process.php">
            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="email" placeholder="Enter your email">

            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" placeholder="Enter your password">

            <div class="auth-actions">
              <button type="submit" class="auth-btn auth-btn-customer" name="role" value="customer">Log in as Customer</button>
              <button type="submit" class="auth-btn auth-btn-farmer" name="role" value="farmer">Log in as Farmer</button>
              <button type="submit" class="auth-btn auth-btn-admin" name="role" value="admin">Log in as Admin</button>
            </div>

            <p class="form-message <?php echo $messageType; ?>">
              <?php echo htmlspecialchars($message); ?>
            </p>
          </form>

          <p class="auth-link">
            New user? <a href="register.php">Create Account</a>
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