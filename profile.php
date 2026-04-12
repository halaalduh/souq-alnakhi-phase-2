<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$messageType = "";

$query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("User not found.");
}

$user = mysqli_fetch_assoc($result);

if (isset($_GET["success"])) {
    $message = "Profile updated successfully.";
    $messageType = "success-message";

    $query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
}

if (isset($_GET["error"])) {
    if ($_GET["error"] === "empty") {
        $message = "Please fill all fields.";
    } elseif ($_GET["error"] === "email") {
        $message = "Invalid email format.";
    } elseif ($_GET["error"] === "exists") {
        $message = "This email is already used.";
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
  <title>Manage Profile - Souq Al-Nakhil</title>
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
        <?php if ($_SESSION["role"] === "admin") { ?>
          <a href="AdminDashboard.php">Dashboard</a>
        <?php } elseif ($_SESSION["role"] === "farmer") { ?>
          <a href="Farmerdashboard.php">Dashboard</a>
        <?php } else { ?>
          <a href="home.php">Home</a>
        <?php } ?>

        <a href="profile.php" class="nav-btn nav-btn-outline">Profile</a>
        <a href="logout.php" class="nav-btn nav-btn-solid">Logout</a>
      </nav>

    </div>
  </header>

  <main class="auth-wrapper">
    <div class="profile-page-wrap">
      <div class="auth-card profile-card-wide">
        <h2>Manage Profile</h2>
        <p class="auth-subtitle">View and update your account information</p>

        <form class="auth-form" method="POST" action="updateProfile.php">
          <label for="full-name">Full Name</label>
          <input
            type="text"
            id="full-name"
            name="full_name"
            value="<?php echo htmlspecialchars($user["full_name"]); ?>"
            placeholder="Enter your full name"
          >

          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            value="<?php echo htmlspecialchars($user["email"]); ?>"
            placeholder="Enter your email"
          >

          <label for="role">Role</label>
          <input
            type="text"
            id="role"
            value="<?php echo htmlspecialchars(ucfirst($user["role"])); ?>"
            readonly
            class="readonly-field"
          >

          <div class="auth-actions">
            <button type="submit" class="auth-btn auth-btn-customer">Save Changes</button>
          </div>

          <p class="form-message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
          </p>
        </form>
      </div>
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
