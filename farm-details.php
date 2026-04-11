<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Farm ID is missing.");
}

$farm_id = (int) $_GET['id'];

$farm_sql = "SELECT 
                id,
                farm_name,
                region,
                farm_description,
                contact_phone,
                contact_email,
                is_verified
             FROM farms
             WHERE id = $farm_id
             LIMIT 1";

$farm_result = mysqli_query($conn, $farm_sql);

if (!$farm_result) {
    die("Farm query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($farm_result) == 0) {
    die("Farm not found.");
}

$farm = mysqli_fetch_assoc($farm_result);

$products_sql = "SELECT 
                    id,
                    product_name,
                    date_type,
                    price,
                    quantity,
                    description
                 FROM products
                 WHERE farm_id = $farm_id
                 ORDER BY id DESC";

$products_result = mysqli_query($conn, $products_sql);

if (!$products_result) {
    die("Products query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farm Details - Souq Al-Nakhil</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="header-container">
    <a href="home.php" class="brand">
      <img src="logo.png" class="brand-logo" alt="Souq Al-Nakhil Logo">
      <div class="brand-text">
        <span class="brand-title">Souq Al-Nakhil</span>
        <span class="brand-subtitle">Fresh Saudi Dates Marketplace</span>
      </div>
    </a>

    <nav class="site-nav">
      <a href="home.php" class="nav-btn nav-btn-outline">Home</a>
      <a href="login.html" class="nav-btn nav-btn-outline">Logout</a>
    </nav>
  </div>
</header>

<div class="back-wrap">
  <button type="button" class="back-btn" onclick="history.back()">← Back</button>
</div>

<div class="container details-container">

  <h2>Farm Details</h2>
  <p class="subtitle">Learn more about the farm.</p>

  <div class="details-card">

    <?php if ((int)$farm['is_verified'] === 1) { ?>
      <span class="trusted-badge">✔ Trusted Farmer</span>
    <?php } ?>

    <table class="details-table">
      <tr>
        <td><strong>Farm Name</strong></td>
        <td><?php echo htmlspecialchars($farm['farm_name']); ?></td>
      </tr>

      <tr>
        <td><strong>Region</strong></td>
        <td><?php echo htmlspecialchars($farm['region']); ?></td>
      </tr>

      <tr>
        <td><strong>Farm Description</strong></td>
        <td><?php echo htmlspecialchars($farm['farm_description']); ?></td>
      </tr>

      <tr>
        <td><strong>Contact Information</strong></td>
        <td>
          <?php if (!empty($farm['contact_phone']) || !empty($farm['contact_email'])) { ?>
            <div class="contact-available">
              <?php if (!empty($farm['contact_phone'])) { ?>
                Phone: <?php echo htmlspecialchars($farm['contact_phone']); ?><br>
              <?php } ?>

              <?php if (!empty($farm['contact_email'])) { ?>
                Email: <?php echo htmlspecialchars($farm['contact_email']); ?>
              <?php } ?>
            </div>
          <?php } else { ?>
            <div class="no-contact">
              Contact information is not available.
            </div>
          <?php } ?>
        </td>
      </tr>
    </table>

  </div>

  <h3 class="section-title">Products</h3>

  <div class="product-cards">
    <?php if (mysqli_num_rows($products_result) > 0) { ?>
      <?php while ($product = mysqli_fetch_assoc($products_result)) { ?>
        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="product-card-link">
          <div class="product-card">
            <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
            <p><?php echo htmlspecialchars($product['price']); ?> SAR</p>
          </div>
        </a>
      <?php } ?>
    <?php } else { ?>
      <p class="notice">No products available for this farm.</p>
    <?php } ?>
  </div>

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
</html>