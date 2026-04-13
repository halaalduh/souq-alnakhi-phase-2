<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "farmer") {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION["user_id"];
$error = "";
$success = "";

$product_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($product_id <= 0) {
    header("Location: FarmerDashboard.php");
    exit();
}

$getProduct = mysqli_prepare($conn, "
    SELECT p.*, f.region
    FROM products p
    INNER JOIN farms f ON p.farm_id = f.id
    WHERE p.id = ? AND f.farmer_user_id = ?
");
mysqli_stmt_bind_param($getProduct, "ii", $product_id, $farmer_id);
mysqli_stmt_execute($getProduct);
$productResult = mysqli_stmt_get_result($getProduct);
$product = mysqli_fetch_assoc($productResult);
mysqli_stmt_close($getProduct);

if (!$product) {
    header("Location: FarmerDashboard.php");
    exit();
}

$productName = $product["product_name"];
$dateType = $product["date_type"];
$price = $product["price"];
$quantity = $product["quantity"];
$originRegion = $product["region"];
$productDescription = $product["description"];
$currentImage = $product["image"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productName = trim($_POST["product_name"] ?? "");
    $dateType = trim($_POST["date_type"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $productDescription = trim($_POST["product_description"] ?? "");

    $allowedDateTypes = ["Ajwa", "Khalas", "Sukkari"];
    $newImageName = $currentImage;

    if ($productName === "" || $dateType === "" || $price === "" || $quantity === "" || $productDescription === "") {
        $error = "Please fill in all required fields.";
    } elseif (!in_array($dateType, $allowedDateTypes)) {
        $error = "Please select a valid date type.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Please enter a valid price.";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error = "Please enter a valid quantity.";
    } else {
        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] === 0) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = basename($_FILES["product_image"]["name"]);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

            if (!in_array($extension, $allowedExtensions)) {
                $error = "Only image files are allowed.";
            } else {
                $newImageName = time() . "_" . preg_replace("/[^A-Za-z0-9._-]/", "_", $originalName);
                $targetPath = $uploadDir . $newImageName;

                if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetPath)) {
                    $error = "Failed to upload the image.";
                }
            }
        }

        if ($error === "") {
            $updateProduct = mysqli_prepare($conn, "
                UPDATE products p
                INNER JOIN farms f ON p.farm_id = f.id
                SET p.product_name = ?, p.date_type = ?, p.price = ?, p.quantity = ?, p.description = ?, p.image = ?
                WHERE p.id = ? AND f.farmer_user_id = ?
            ");
            mysqli_stmt_bind_param($updateProduct, "ssdissii", $productName, $dateType, $price, $quantity, $productDescription, $newImageName, $product_id, $farmer_id);

            if (mysqli_stmt_execute($updateProduct)) {
                mysqli_stmt_close($updateProduct);
                header("Location: FarmerDashboard.php?product=updated");
                exit();
            } else {
                $error = "Something went wrong while updating the product.";
            }

            mysqli_stmt_close($updateProduct);
        }
    }
}

$imageSrc = "";
if (!empty($currentImage)) {
    if (file_exists("uploads/" . $currentImage)) {
        $imageSrc = "uploads/" . $currentImage;
    } else {
        $imageSrc = $currentImage;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product - Souq Al-Nakhil</title>
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
        <a href="FarmerDashboard.php" class="nav-btn nav-btn-outline">Dashboard</a>
        <a href="logout.php" class="nav-btn nav-btn-outline">Logout</a>
      </nav>
    </div>
  </header>

  <div class="back-wrap">
    <button type="button" class="back-btn" onclick="history.back()">← Back</button>
  </div>

  <div class="container">
    <h2>Edit Product</h2>
    <p class="subtitle">Update the product information below.</p>

    <form id="editProductForm" method="POST" action="" enctype="multipart/form-data">
      <label for="productName">Product Name</label>
      <input type="text" id="productName" name="product_name" placeholder="Enter product name" value="<?php echo htmlspecialchars($productName); ?>">

      <label for="dateType">Date Type</label>
      <select id="dateType" name="date_type">
        <option value="">Select date type</option>
        <option value="Ajwa" <?php if ($dateType === "Ajwa") echo "selected"; ?>>Ajwa</option>
        <option value="Khalas" <?php if ($dateType === "Khalas") echo "selected"; ?>>Khalas</option>
        <option value="Sukkari" <?php if ($dateType === "Sukkari") echo "selected"; ?>>Sukkari</option>
      </select>

      <label for="price">Price (SAR)</label>
      <input type="number" step="0.01" id="price" name="price" placeholder="Enter price" value="<?php echo htmlspecialchars($price); ?>">

      <label for="quantity">Quantity</label>
      <input type="number" id="quantity" name="quantity" placeholder="Enter quantity" value="<?php echo htmlspecialchars($quantity); ?>">

      <label for="originRegion">Region</label>
      <select id="originRegion" disabled>
        <option value=""><?php echo htmlspecialchars($originRegion); ?></option>
      </select>

      <label for="productDescription">Product Description</label>
      <textarea id="productDescription" name="product_description" placeholder="Describe the product"><?php echo htmlspecialchars($productDescription); ?></textarea>

      <label for="productImage">Product Image</label>
      <input type="file" id="productImage" name="product_image" accept="image/*">

      <div class="image-preview-box">
        <?php if ($imageSrc !== ""): ?>
          <img id="imagePreview" src="<?php echo htmlspecialchars($imageSrc); ?>" alt="Preview">
        <?php else: ?>
          <img id="imagePreview" src="" alt="Preview" style="display:none;">
        <?php endif; ?>
      </div>

      <p id="productError" class="error-message"><?php echo htmlspecialchars($error); ?></p>
      <p id="productSuccess" class="success-message"><?php echo htmlspecialchars($success); ?></p>
      <p id="productFormMessage" class="form-message"></p>

      <button type="submit">Save Changes</button>
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
</html>