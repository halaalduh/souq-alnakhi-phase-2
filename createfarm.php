<?php

session_start();
include "config.php";


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "config.php";
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];
$error = "";
$success = "";

$farmName = "";
$region = "";
$farmDescription = "";
$contactPhone = "";
$contactEmail = "";

$checkFarm = mysqli_prepare($conn, "SELECT id FROM farms WHERE farmer_user_id = ?");
mysqli_stmt_bind_param($checkFarm, "i", $farmer_id);
mysqli_stmt_execute($checkFarm);
$existingFarmResult = mysqli_stmt_get_result($checkFarm);
$existingFarm = mysqli_fetch_assoc($existingFarmResult);
mysqli_stmt_close($checkFarm);

if ($existingFarm) {
    header("Location: FarmerDashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $farmName = trim($_POST["farm_name"] ?? "");
    $region = trim($_POST["region"] ?? "");
    $farmDescription = trim($_POST["farm_description"] ?? "");
    $contactPhone = trim($_POST["contact_phone"] ?? "");
    $contactEmail = trim($_POST["contact_email"] ?? "");

    $allowedRegions = ["Najd", "Qassim", "Al-Ahsa"];

    if ($farmName === "" || $region === "" || $farmDescription === "") {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($region, $allowedRegions)) {
        $error = "Please select a valid region.";
    } elseif ($contactEmail !== "" && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
       $insertFarm = mysqli_prepare($conn, "INSERT INTO farms (farmer_user_id, farm_name, region, farm_description, contact_phone, contact_email) VALUES (?, ?, ?, ?, ?, ?)");

if (!$insertFarm) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($insertFarm, "isssss", $farmer_id, $farmName, $region, $farmDescription, $contactPhone, $contactEmail);

if (mysqli_stmt_execute($insertFarm)) {
    mysqli_stmt_close($insertFarm);
    header("Location: Farmerdashboard.php?farm=created");
    exit();
} else {
    die("Execute failed: " . mysqli_stmt_error($insertFarm));
}
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Farm - Souq Al-Nakhil</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header class="site-header">
    <div class="header-container">
      <a href="home.php" class="brand">
        <img src="logo.png" alt="Souq Al-Nakhil Logo" class="brand-logo">
        <div class="brand-text">
          <span class="brand-title">Souq Al-Nakhil</span>
          <span class="brand-subtitle">Farmer Panel</span>
        </div>
      </a>

      <nav class="site-nav">
        <a href="home.php" class="nav-btn nav-btn-outline">Home</a>
        <a href="FarmerDashboard.php" class="nav-btn nav-btn-outline">Dashboard</a>
        <a href="logout.php" class="nav-btn nav-btn-outline">Logout</a>
      </nav>
    </div>
  </header>

  <div class="back-wrap">
    <button type="button" class="back-btn" onclick="history.back()">← Back</button>
  </div>

  <div class="container">
    <h2>Create Your Farm Profile</h2>
    <p class="subtitle">
      Share your farm details so customers can discover your products.
    </p>

    <form id="createFarmForm" method="POST" action="">
      <label for="farmName">Farm Name</label>
      <input type="text" id="farmName" name="farm_name" placeholder="Enter farm name" value="<?php echo htmlspecialchars($farmName); ?>">

      <label for="region">Region</label>
      <select id="region" name="region">
        <option value="">Select Region</option>
        <option value="Najd" <?php if ($region === "Najd") echo "selected"; ?>>Najd</option>
        <option value="Qassim" <?php if ($region === "Qassim") echo "selected"; ?>>Qassim</option>
        <option value="Al-Ahsa" <?php if ($region === "Al-Ahsa") echo "selected"; ?>>Al-Ahsa</option>
      </select>

      <label for="contactPhone">Contact Phone</label>
      <input type="text" id="contactPhone" name="contact_phone" placeholder="Enter phone number" value="<?php echo htmlspecialchars($contactPhone); ?>">

      <label for="contactEmail">Contact Email</label>
      <input type="email" id="contactEmail" name="contact_email" placeholder="Enter email" value="<?php echo htmlspecialchars($contactEmail); ?>">

      <label for="farmDescription">Farm Description</label>
      <textarea id="farmDescription" name="farm_description" placeholder="Describe your farm and products"><?php echo htmlspecialchars($farmDescription); ?></textarea>

      <p id="farmError" class="error-message"><?php echo htmlspecialchars($error); ?></p>
      <p id="farmSuccess" class="success-message"><?php echo htmlspecialchars($success); ?></p>

      <button type="submit">Create Farm Profile</button>
    </form>
  </div>

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
</html>s