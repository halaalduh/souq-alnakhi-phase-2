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

$productName = "";
$dateType = "";
$price = "";
$quantity = "";
$description = "";

$farmQuery = mysqli_prepare($conn, "SELECT id, region FROM farms WHERE farmer_user_id = ?");
mysqli_stmt_bind_param($farmQuery, "i", $farmer_id);
mysqli_stmt_execute($farmQuery);
$farmResult = mysqli_stmt_get_result($farmQuery);
$farm = mysqli_fetch_assoc($farmResult);
mysqli_stmt_close($farmQuery);

if (!$farm) {
    header("Location: createfarm.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productName = trim($_POST["product_name"] ?? "");
    $dateType = trim($_POST["date_type"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $quantity = trim($_POST["quantity"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $allowedDateTypes = ["Ajwa", "Khalas", "Sukkari"];
    $imageName = "";

    if ($productName === "" || $dateType === "" || $price === "" || $quantity === "" || $description === "") {
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
                $imageName = time() . "_" . preg_replace("/[^A-Za-z0-9._-]/", "_", $originalName);
                $targetPath = $uploadDir . $imageName;

                if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetPath)) {
                    $error = "Failed to upload the image.";
                }
            }
        }

        if ($error === "") {
            $insertProduct = mysqli_prepare($conn, "INSERT INTO products (farm_id, product_name, date_type, price, quantity, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insertProduct, "issdiss", $farm["id"], $productName, $dateType, $price, $quantity, $description, $imageName);

            if (mysqli_stmt_execute($insertProduct)) {
                mysqli_stmt_close($insertProduct);
                header("Location: FarmerDashboard.php?product=added");
                exit();
            } else {
                $error = "Something went wrong while adding the product.";
            }

            mysqli_stmt_close($insertProduct);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - Souq Al-Nakhil</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="header-container">
    <a href="home.php" class="brand">
      <img src="logo.png" class="brand-logo">
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
  <h2>Add Product</h2>
  <p class="subtitle">Enter your product details so customers can view and purchase your dates.</p>
  <p id="addProductLocked" class="form-message error-message" hidden>Create the farm profile first before adding a product.</p>

  <form id="addProductForm" method="POST" action="" enctype="multipart/form-data">
    <label for="productName">Product Name</label>
    <input type="text" id="productName" name="product_name" placeholder="Enter product name" value="<?php echo htmlspecialchars($productName); ?>">

    <label for="dateType">Date Type</label>
    <select id="dateType" name="date_type">
      <option value="">Select date type</option>
      <option value="Ajwa" <?php if ($dateType === "Ajwa") echo "selected"; ?>>Ajwa</option>
      <option value="Khalas" <?php if ($dateType === "Khalas") echo "selected"; ?>>Khalas</option>
      <option value="Sukkari" <?php if ($dateType === "Sukkari") echo "selected"; ?>>Sukkari</option>
    </select>

    <label for="price">Price</label>
    <input type="number" step="0.01" id="price" name="price" placeholder="Enter price" value="<?php echo htmlspecialchars($price); ?>">

    <label for="quantity">Quantity</label>
    <input type="number" id="quantity" name="quantity" placeholder="Enter quantity" value="<?php echo htmlspecialchars($quantity); ?>">

    <label for="origin">Region</label>
    <select id="origin" disabled>
      <option value=""><?php echo htmlspecialchars($farm["region"]); ?></option>
    </select>

    <label for="description">Description</label>
    <textarea id="description" name="description" placeholder="Describe the product"><?php echo htmlspecialchars($description); ?></textarea>

    <label for="productImage">Product Image</label>
    <input type="file" id="productImage" name="product_image" accept="image/*">

    <div class="image-preview-box">
      <img id="imagePreview" src="" alt="Preview" style="display:none;">
    </div>

    <button type="submit">Add Product</button>

    <p id="addProductMessage" class="form-message"><?php echo htmlspecialchars($error !== "" ? $error : $success); ?></p>
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