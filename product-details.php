<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Product ID is missing.");
}

$product_id = (int) $_GET['id'];

$sql = "SELECT 
            p.id,
            p.product_name,
            p.date_type,
            p.price,
            p.quantity,
            p.description,
            f.id AS farm_id,
            f.farm_name,
            f.region,
            f.contact_phone,
            f.contact_email,
            f.is_verified
        FROM products p
        INNER JOIN farms f ON p.farm_id = f.id
        WHERE p.id = $product_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Product not found.");
}

$product = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Details - Souq Al-Nakhil</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

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
      <a href="login.html" class="nav-btn nav-btn-outline">Logout</a>
    </nav>
  </div>
</header>

<div class="back-wrap">
  <button type="button" class="back-btn" onclick="history.back()">← Back</button>
</div>

<div class="container details-container">
  <h2>Product Details</h2>
  <p class="subtitle">View full information about this product.</p>

  <div class="details-card">

    <?php if ((int)$product['is_verified'] === 1) { ?>
      <span class="trusted-badge">✔ Trusted Farmer</span>
    <?php } ?>

    <table class="details-table">
      <tr>
        <td><strong>Product Name</strong></td>
        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
      </tr>

      <tr>
        <td><strong>Date Type</strong></td>
        <td><?php echo htmlspecialchars($product['date_type']); ?></td>
      </tr>

      <tr>
        <td><strong>Price</strong></td>
        <td><?php echo htmlspecialchars($product['price']); ?> SAR</td>
      </tr>

      <tr>
        <td><strong>Quantity</strong></td>
        <td><?php echo htmlspecialchars($product['quantity']); ?> boxes</td>
      </tr>

      <tr>
        <td><strong>Region</strong></td>
        <td><?php echo htmlspecialchars($product['region']); ?></td>
      </tr>

      <tr>
        <td><strong>Description</strong></td>
        <td><?php echo htmlspecialchars($product['description']); ?></td>
      </tr>

      <tr>
        <td><strong>Farm Name</strong></td>
        <td>
          <a href="farm-details.php?id=<?php echo $product['farm_id']; ?>" class="farm-link">
            <?php echo htmlspecialchars($product['farm_name']); ?>
          </a>
        </td>
      </tr>

      <tr>
        <td><strong>Contact Information</strong></td>
        <td>
          <?php if (!empty($product['contact_phone']) || !empty($product['contact_email'])) { ?>
            <div class="contact-available">
              <?php if (!empty($product['contact_phone'])) { ?>
                Phone: <?php echo htmlspecialchars($product['contact_phone']); ?><br>
              <?php } ?>

              <?php if (!empty($product['contact_email'])) { ?>
                Email: <?php echo htmlspecialchars($product['contact_email']); ?>
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