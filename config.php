
<?php
$conn = new mysqli("localhost", "root", "", "souq_alnakhil_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
