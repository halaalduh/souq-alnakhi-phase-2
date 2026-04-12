<?php
session_start();
include "config.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit();
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function redirect_with_message($type, $message) {
    header("Location: AdminDashboard.php?type=" . urlencode($type) . "&message=" . urlencode($message));
    exit();
}

function count_rows($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int)($row["total"] ?? 0);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "verify_farm" || $action === "unverify_farm") {
        $farm_id = (int)($_POST["farm_id"] ?? 0);
        $is_verified = ($action === "verify_farm") ? 1 : 0;

        if ($farm_id <= 0) {
            redirect_with_message("error", "Invalid farm selected.");
        }

        $stmt = mysqli_prepare($conn, "UPDATE farms SET is_verified = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $is_verified, $farm_id);

        if (mysqli_stmt_execute($stmt)) {
            redirect_with_message("success", "Farm verification status updated.");
        }

        redirect_with_message("error", "Could not update farm verification.");
    }

    if ($action === "update_user") {
        $user_id = (int)($_POST["user_id"] ?? 0);
        $full_name = trim($_POST["full_name"] ?? "");
        $email = trim($_POST["email"] ?? "");

        if ($user_id <= 0 || $full_name === "" || $email === "") {
            redirect_with_message("error", "Please fill all user fields.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirect_with_message("error", "Invalid email format.");
        }

        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($check_stmt, "si", $email, $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            redirect_with_message("error", "This email is already used.");
        }

        $stmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, email = ? WHERE id = ? AND role IN ('customer', 'farmer')");
        mysqli_stmt_bind_param($stmt, "ssi", $full_name, $email, $user_id);

        if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0) {
            redirect_with_message("success", "User information updated.");
        }

        redirect_with_message("error", "Could not update user information.");
    }

    if ($action === "delete_user") {
        $user_id = (int)($_POST["user_id"] ?? 0);

        if ($user_id <= 0) {
            redirect_with_message("error", "Invalid user selected.");
        }

        mysqli_begin_transaction($conn);

        try {
            $stmt = mysqli_prepare($conn, "DELETE p FROM products p INNER JOIN farms f ON p.farm_id = f.id WHERE f.farmer_user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Could not delete products.");
            }

            $stmt = mysqli_prepare($conn, "DELETE FROM farms WHERE farmer_user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Could not delete farms.");
            }

            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role IN ('customer', 'farmer')");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Could not delete user.");
            }

            mysqli_commit($conn);
            redirect_with_message("success", "User information removed.");
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            redirect_with_message("error", "Could not remove user information.");
        }
    }

    if ($action === "delete_farm") {
        $farm_id = (int)($_POST["farm_id"] ?? 0);

        if ($farm_id <= 0) {
            redirect_with_message("error", "Invalid farm selected.");
        }

        mysqli_begin_transaction($conn);

        try {
            $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE farm_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $farm_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Could not delete products.");
            }

            $stmt = mysqli_prepare($conn, "DELETE FROM farms WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $farm_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Could not delete farm.");
            }

            mysqli_commit($conn);
            redirect_with_message("success", "Farm information removed.");
        } catch (Throwable $e) {
            mysqli_rollback($conn);
            redirect_with_message("error", "Could not remove farm information.");
        }
    }
}

$customer_count = count_rows($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
$farmer_count = count_rows($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'farmer'");
$trusted_count = count_rows($conn, "SELECT COUNT(*) AS total FROM farms WHERE is_verified = 1");

$users_sql = "SELECT
                u.id,
                u.full_name,
                u.email,
                u.role,
                f.farm_name,
                f.region,
                f.is_verified
              FROM users u
              LEFT JOIN farms f ON f.farmer_user_id = u.id
              WHERE u.role IN ('customer', 'farmer')
              ORDER BY u.role, u.full_name";
$users_result = mysqli_query($conn, $users_sql);

$farms_sql = "SELECT
                f.id,
                f.farm_name,
                f.region,
                f.contact_phone,
                f.contact_email,
                f.is_verified,
                u.full_name AS farmer_name,
                u.email AS farmer_email
              FROM farms f
              INNER JOIN users u ON u.id = f.farmer_user_id
              ORDER BY f.is_verified ASC, f.farm_name";
$farms_result = mysqli_query($conn, $farms_sql);

$message = trim($_GET["message"] ?? "");
$message_type = ($_GET["type"] ?? "") === "error" ? "error-message" : "success-message";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Souq Al-Nakhil</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header class="site-header">
    <div class="header-container">
      <a href="home.php" class="brand">
        <img src="logo.png" alt="Souq Al-Nakhil Logo" class="brand-logo">
        <div class="brand-text">
          <span class="brand-title">Souq Al-Nakhil</span>
          <span class="brand-subtitle">Admin Panel</span>
        </div>
      </a>

      <nav class="site-nav">
        <a href="profile.php" class="nav-btn nav-btn-outline">Edit Profile</a>
        <a href="logout.php" class="nav-btn nav-btn-solid">Logout</a>
      </nav>
    </div>
  </header>

  <div class="back-wrap">
    <a class="back-btn" href="home.php">Back</a>
  </div>

  <main class="page-shell">
    <section class="dashboard-hero">
      <div class="dashboard-hero-top">
        <div>
          <span class="status-badge active">Admin Controls</span>
          <h1>Admin Dashboard</h1>
          <p>Verify farms, manage trusted badges, and update customer or farmer records.</p>
        </div>
        <div class="welcome-panel">
          <div class="welcome-pill">Welcome, <?php echo h($_SESSION["full_name"] ?? "Admin"); ?></div>
        </div>
      </div>
    </section>

    <?php if ($message !== "") { ?>
      <section class="panel">
        <p class="form-message <?php echo h($message_type); ?>"><?php echo h($message); ?></p>
      </section>
    <?php } ?>

    <section class="stats-row">
      <div class="dashboard-stat">
        <strong><?php echo $customer_count; ?></strong>
        <span>Total Customers</span>
        <p>Customer accounts currently visible in the platform.</p>
      </div>
      <div class="dashboard-stat">
        <strong><?php echo $farmer_count; ?></strong>
        <span>Total Farmers</span>
        <p>Farmer accounts registered in the system.</p>
      </div>
      <div class="dashboard-stat">
        <strong><?php echo $trusted_count; ?></strong>
        <span>Trusted Farmers</span>
        <p>Verified farms with the Trusted Farmer Badge.</p>
      </div>
    </section>

    <section class="panel">
      <div class="section-head">
        <div>
          <h3>Manage Customer And Farmer Information</h3>
          <p>Edit account information directly in the database or remove records from the system.</p>
        </div>
      </div>

      <div class="table-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Email</th>
              <th>Submitted Information</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($users_result && mysqli_num_rows($users_result) > 0) { ?>
              <?php while ($user = mysqli_fetch_assoc($users_result)) { ?>
                <?php $form_id = "user-form-" . (int)$user["id"]; ?>
                <tr>
                  <td>
                    <input class="admin-inline-input" form="<?php echo h($form_id); ?>" type="text" name="full_name" value="<?php echo h($user["full_name"]); ?>" required>
                  </td>
                  <td><?php echo h(ucfirst($user["role"])); ?></td>
                  <td>
                    <input class="admin-inline-input" form="<?php echo h($form_id); ?>" type="email" name="email" value="<?php echo h($user["email"]); ?>" required>
                  </td>
                  <td>
                    <?php if ($user["role"] === "farmer" && $user["farm_name"]) { ?>
                      Farm: <?php echo h($user["farm_name"]); ?>,
                      <?php echo h($user["region"]); ?>
                      <?php if ((int)$user["is_verified"] === 1) { ?>
                        <span class="trusted-badge">Trusted Farmer</span>
                      <?php } ?>
                    <?php } else { ?>
                      Account details
                    <?php } ?>
                  </td>
                  <td>
                    <form id="<?php echo h($form_id); ?>" method="POST" action="AdminDashboard.php">
                      <div class="table-actions">
                        <input type="hidden" name="user_id" value="<?php echo (int)$user["id"]; ?>">
                        <button class="mini-btn edit" type="submit" name="action" value="update_user">Save</button>
                        <button class="mini-btn delete" type="submit" name="action" value="delete_user" onclick="return confirm('Remove this user and related farmer information?');">Remove</button>
                      </div>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="5">No customer or farmer accounts found.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <div class="section-head">
        <div>
          <h3>Verify Farms And Assign Trusted Farmer Badge</h3>
          <p>Verified farms receive the Trusted Farmer Badge shown to customers on product cards.</p>
        </div>
      </div>

      <div class="table-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Farm Name</th>
              <th>Farmer</th>
              <th>Region</th>
              <th>Status</th>
              <th>Badge</th>
              <th>Contact</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($farms_result && mysqli_num_rows($farms_result) > 0) { ?>
              <?php while ($farm = mysqli_fetch_assoc($farms_result)) { ?>
                <?php $verified = (int)$farm["is_verified"] === 1; ?>
                <tr>
                  <td><?php echo h($farm["farm_name"]); ?></td>
                  <td><?php echo h($farm["farmer_name"]); ?><br><?php echo h($farm["farmer_email"]); ?></td>
                  <td><?php echo h($farm["region"]); ?></td>
                  <td>
                    <span class="pill <?php echo $verified ? "approved" : "review"; ?>">
                      <?php echo $verified ? "Verified" : "Pending"; ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($verified) { ?>
                      <span class="trusted-badge">Trusted Farmer</span>
                    <?php } else { ?>
                      <span class="badge-text">Not Assigned</span>
                    <?php } ?>
                  </td>
                  <td>
                    <?php echo h($farm["contact_phone"] ?: "No phone"); ?><br>
                    <?php echo h($farm["contact_email"] ?: "No email"); ?>
                  </td>
                  <td>
                    <div class="table-actions">
                      <form method="POST" action="AdminDashboard.php">
                        <input type="hidden" name="farm_id" value="<?php echo (int)$farm["id"]; ?>">
                        <?php if ($verified) { ?>
                          <button class="mini-btn plain" type="submit" name="action" value="unverify_farm">Remove Badge</button>
                        <?php } else { ?>
                          <button class="mini-btn edit" type="submit" name="action" value="verify_farm">Verify</button>
                        <?php } ?>
                      </form>
                      <form method="POST" action="AdminDashboard.php">
                        <input type="hidden" name="farm_id" value="<?php echo (int)$farm["id"]; ?>">
                        <button class="mini-btn delete" type="submit" name="action" value="delete_farm" onclick="return confirm('Remove this farm and its products?');">Remove Farm</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="7">No farms found.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
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
