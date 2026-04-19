<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");



if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search    = isset($_GET['search']) ? trim($_GET['search']) : '';
$region    = isset($_GET['region']) ? trim($_GET['region']) : '';
$date_type = isset($_GET['date_type']) ? trim($_GET['date_type']) : '';

$sql = "SELECT 
            p.id AS product_id,
            p.product_name,
            p.date_type,
            p.price,
            p.quantity,
            p.description,
            f.farm_name,
            f.region,
            f.is_verified
        FROM products p
        INNER JOIN farms f ON p.farm_id = f.id
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (p.product_name LIKE ? OR f.farm_name LIKE ?)";
    $search_like = "%" . $search . "%";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss";
}

if ($region !== '') {
    $sql .= " AND f.region = ?";
    $params[] = $region;
    $types .= "s";
}

if ($date_type !== '') {
    $sql .= " AND p.date_type = ?";
    $params[] = $date_type;
    $types .= "s";
}

$sql .= " ORDER BY p.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$has_filters = ($search !== '' || $region !== '' || $date_type !== '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - Souq Al-Nakhil</title>
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
        <h1>Discover Fresh Saudi Dates</h1>
        <p>
          Browse products from local farms, search by product or farm name, and filter by region or date type
          to find the dates that match your preference.
        </p>
      </div>
      <span class="welcome-pill">Fresh • Local • Trusted</span>
    </div>
  </section>

  <section class="panel">
    <div class="section-head">
      <div>
        <h3>Search & Filter</h3>
        <p>Use one search box for product name or farm name, then apply filters if needed.</p>
      </div>
    </div>

    <form class="filter-form" method="GET" action="home.php">
      <input
        type="text"
        id="searchInput"
        name="search"
        placeholder="Search by product or farm name"
        value="<?php echo htmlspecialchars($search); ?>"
      >

      <select id="regionFilter" name="region">
        <option value="">Filter by Region</option>
        <option value="Najd" <?php echo ($region === 'Najd') ? 'selected' : ''; ?>>Najd</option>
        <option value="Qassim" <?php echo ($region === 'Qassim') ? 'selected' : ''; ?>>Qassim</option>
        <option value="Al-Ahsa" <?php echo ($region === 'Al-Ahsa') ? 'selected' : ''; ?>>Al-Ahsa</option>
      </select>

      <select id="typeFilter" name="date_type">
        <option value="">Filter by Date Type</option>
        <option value="Ajwa" <?php echo ($date_type === 'Ajwa') ? 'selected' : ''; ?>>Ajwa</option>
        <option value="Sukkari" <?php echo ($date_type === 'Sukkari') ? 'selected' : ''; ?>>Sukkari</option>
        <option value="Khalas" <?php echo ($date_type === 'Khalas') ? 'selected' : ''; ?>>Khalas</option>
      </select>

      <button type="submit">Search</button>
    </form>
  </section>

  <section class="panel">
    <div class="section-head">
      <div>
        <h3>Available Products</h3>
        <p>A list of products available on the platform.</p>
      </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
      <div class="products-grid" id="productsGrid">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <?php
          $type = strtolower(trim($row['date_type']));
          $image = '';

          if ($type === 'ajwa') {
              $image = 'images/ajwa.png';
          } elseif ($type === 'sukkari') {
              $image = 'images/sukkari.png';
          } elseif ($type === 'khalas') {
              $image = 'images/khalas.png';
          }
          ?>

          <div class="market-card">
            <?php if ($image !== ''): ?>
              <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
            <?php endif; ?>

            <?php if ((int)$row['is_verified'] === 1): ?>
              <span class="trusted-badge">✔ Trusted Farmer</span>
            <?php endif; ?>

            <h4>
              <a href="product-details.php?id=<?php echo $row['product_id']; ?>" class="product-link">
                <?php echo htmlspecialchars($row['product_name']); ?>
              </a>
            </h4>

            <p><strong>Farm:</strong> <?php echo htmlspecialchars($row['farm_name']); ?></p>
            <p><strong>Region:</strong> <?php echo htmlspecialchars($row['region']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($row['date_type']); ?></p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($row['price']); ?> SAR</p>
            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($row['quantity']); ?> boxes</p>
            <p class="card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div id="noResultsMessage" class="notice" style="text-align:center; padding:30px;">
        <?php if ($has_filters): ?>
          <h4>No matching products found</h4>
          <p>Try changing the search text or filters and search again.</p>
        <?php else: ?>
          <h4>No products available yet</h4>
          <p>Products will appear here after farmers add them.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
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