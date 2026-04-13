<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];
$farmer_name = $_SESSION["full_name"] ?? "Farmer";

if (isset($_GET["delete"])) {
    $product_id = (int)$_GET["delete"];

    if ($product_id > 0) {
        $deleteStmt = mysqli_prepare($conn, "
            DELETE p FROM products p
            INNER JOIN farms f ON p.farm_id = f.id
            WHERE p.id = ? AND f.farmer_user_id = ?
        ");
        mysqli_stmt_bind_param($deleteStmt, "ii", $product_id, $farmer_id);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);
    }

    header("Location: FarmerDashboard.php?product=deleted");
    exit();
}

$farmStmt = mysqli_prepare($conn, "SELECT * FROM farms WHERE farmer_user_id = ? LIMIT 1");
mysqli_stmt_bind_param($farmStmt, "i", $farmer_id);
mysqli_stmt_execute($farmStmt);
$farmResult = mysqli_stmt_get_result($farmStmt);
$farm = mysqli_fetch_assoc($farmResult);
mysqli_stmt_close($farmStmt);

$products = [];
$totalProducts = 0;

if ($farm) {
    $productStmt = mysqli_prepare($conn, "SELECT * FROM products WHERE farm_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($productStmt, "i", $farm["id"]);
    mysqli_stmt_execute($productStmt);
    $productsResult = mysqli_stmt_get_result($productStmt);

    while ($row = mysqli_fetch_assoc($productsResult)) {
        $products[] = $row;
    }

    mysqli_stmt_close($productStmt);
    $totalProducts = count($products);
}

function getImagePath($image) {
    if (empty($image)) {
        return "logo.png";
    }
    if (file_exists("uploads/" . $image)) {
        return "uploads/" . $image;
    }
    return $image;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmer Dashboard - Souq Al-Nakhil</title>
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
        <a href="logout.php" class="nav-btn nav-btn-outline">Logout</a>
        <a href="profile.php" class="nav-btn nav-btn-outline">Edit Profile</a>
      </nav>
    </div>
  </header>

  <div class="back-wrap">
    <button type="button" class="back-btn" onclick="history.back()">← Back</button>
  </div>

  <main class="page-shell">
    <section class="dashboard-hero">
      <div class="dashboard-hero-top">
        <div>
          <h1>Farmer Dashboard</h1>
        </div>
        <div class="welcome-pill">Welcome, <span id="farmerWelcomeName"><?php echo htmlspecialchars($farmer_name); ?></span></div>
      </div>
    </section>

    <?php if (!$farm): ?>
    <section class="panel pending-setup" id="beforeFarmSection">
      <div class="pending-setup-box">
        <div class="pending-setup-copy">
          <span class="status-badge pending">Before Farm Creation</span>
          <h3>Required First Step</h3>
          <p>Complete the farm profile to continue with product and farm management.</p>
        </div>

        <div class="state-grid single-state-grid">
          <div class="state-card single-state-card">
            <h4>Create Farm Profile</h4>
            <a class="action-link secondary" href="createfarm.php">Open Form</a>
          </div>
        </div>
      </div>
    </section>
    <?php else: ?>

    <section class="panel" id="afterFarmSection">
      <div class="pending-setup-box">
        <div class="pending-setup-copy">
          <span class="status-badge active">After Farm Creation</span>
          <h3>Farm Profile Completed</h3>
          <ul class="pending-points">
            <li><strong>Available now:</strong> Add Product</li>
            <li><strong>Manage products:</strong> Edit Product and Delete Product from the products list</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="stats-row" id="farmStatsSection">
      <div class="dashboard-stat">
        <strong id="totalProducts"><?php echo $totalProducts; ?></strong>
        <span>Total Products</span>
      </div>

      <div class="dashboard-stat">
        <strong id="dashboardFarmRegion"><?php echo htmlspecialchars($farm["region"]); ?></strong>
        <span>Farm Region</span>
      </div>

      <div class="dashboard-stat">
        <strong id="dashboardFarmStatus"><?php echo $farm["is_verified"] ? "Verified" : "Pending"; ?></strong>
        <span>Farm Status</span>
      </div>
    </section>

    <section class="panel" id="farmProfileSection">
      <div class="section-head">
        <div>
          <span class="status-badge active">Farmer Profile</span>
          <h3>Welcome Section</h3>
          <p>The farmer account is active and connected to the farm profile.</p>
        </div>
      </div>

      <div class="info-grid">
        <div class="info-card">
          <h4>Farm Information</h4>
          <ul class="meta-list">
            <li><strong>Farm Name:</strong> <span id="dashboardFarmName"><?php echo htmlspecialchars($farm["farm_name"]); ?></span></li>
            <li><strong>Region:</strong> <span id="dashboardFarmRegionText"><?php echo htmlspecialchars($farm["region"]); ?></span></li>
            <li><strong>Date Types:</strong> <span id="dashboardFarmDateTypes">Managed through products</span></li>
            <li><strong>Description:</strong> <span id="dashboardFarmDescription"><?php echo htmlspecialchars($farm["farm_description"]); ?></span></li>
          </ul>
        </div>

        <div class="info-card">
          <h4>Current Logic</h4>
          <div class="notice">
            The farm profile has already been created. The dashboard now shows the farm name and enables the product actions.
          </div>
        </div>
      </div>
    </section>

    <section class="panel" id="productsSection">
      <div class="section-head">
        <div>
          <h3>My Products</h3>
          <p id="productsSubtext">Current products listed under your farm.</p>
        </div>

        <div class="inline-actions">
          <a class="action-link soft-add" href="add-product.php">+ Add Product</a>
        </div>
      </div>

      <div class="dashboard-products-list">
        <div class="dashboard-products-head">
          <span>Product Image</span>
          <span>Product Name</span>
          <span>Date Type</span>
          <span>Price</span>
          <span>Quantity</span>
          <span>Region</span>
          <span>Description</span>
          <span>Actions</span>
        </div>

        <div id="dashboardProductsBody" class="dashboard-products-body">
          <?php if (empty($products)): ?>
            <div class="product-row">
              <span>No image</span>
              <span>No products yet</span>
              <span>-</span>
              <span>-</span>
              <span>-</span>
              <span><?php echo htmlspecialchars($farm["region"]); ?></span>
              <span>Add your first product.</span>
              <span>-</span>
            </div>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <div class="product-row">
                <span>
                  <img src="<?php echo htmlspecialchars(getImagePath($product["image"])); ?>" alt="Product" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">
                </span>
                <span><?php echo htmlspecialchars($product["product_name"]); ?></span>
                <span><?php echo htmlspecialchars($product["date_type"]); ?></span>
                <span><?php echo htmlspecialchars($product["price"]); ?> SAR</span>
                <span><?php echo htmlspecialchars($product["quantity"]); ?></span>
                <span><?php echo htmlspecialchars($farm["region"]); ?></span>
                <span><?php echo htmlspecialchars($product["description"]); ?></span>
                <span>
                  <a href="editProduct.php?id=<?php echo $product["id"]; ?>">Edit</a> |
                  <a href="FarmerDashboard.php?delete=<?php echo $product["id"]; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
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