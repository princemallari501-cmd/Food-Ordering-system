<?php
// order_delete.php - Direct Deletion using GET ID from order_read.php
include 'db_connect.php';

// Tiyakin na may Order ID sa URL. Kung wala, ibalik sa listahan.
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: order_read.php");
    exit();
}

$order_id = $_GET['id'];

// Gumamit ng prepared statement para sa deletion
$sql = "DELETE FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("<div class='container' style='color:red;'>Error preparing statement: " . $conn->error . "</div>");
}

$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    $message = "<p style='color:green; font-weight: bold;'>Order #{$order_id} deleted successfully!</p>";
    
    // Matagumpay na na-delete, ibalik sa listahan para makita ang pagbabago
    header("Location: order_read.php?status=deleted");
    exit();
} else {
    // Error sa pag-delete
    $message = "<p style='color:red;'>Error deleting record: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Order Status</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>🗑️ Delete Status</h2>
        <?php echo $message; ?>
        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='order_read.php'">Back to Order List</button>
        </div>
    </div>
</body>
</html>