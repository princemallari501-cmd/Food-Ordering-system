<?php
// order_update.php - FIXED: Tinanggal ang 'status'
include 'db_connect.php';

$order_id = $_GET['id'] ?? null;
$item_details = [];
$message = '';

// Step 1: Handle POST request (Form Submission for Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $customer_name = $_POST['customer_name'];
    
    // BINAGO: 'status' removed from SQL query
    $sql = "UPDATE orders SET customer_name = ? WHERE order_id = ?"; // Linya 20 (Dito nagkaka-error dati)
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Mag-print ng error kung nag-fail ang prepare (para makita ang tunay na MySQL error)
        $message = "<p style='color:red;'>Error preparing update statement: " . $conn->error . "</p>";
    } else {
        // BINAGO: 'status' removed. Changed from "ssi" to "si"
        $stmt->bind_param("si", $customer_name, $order_id); // Linya 25 (Dito nagkaka-fatal error dati)
        if ($stmt->execute()) {
            $message = "<p style='color:green; font-weight: bold;'>Order updated successfully!</p>";
            header("Location: order_read.php");
            exit();
        } else {
            $message = "<p style='color:red;'>Error updating record: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Step 2: Fetch current data (for displaying in the form)
if ($order_id) {
    // BINAGO: 'status' removed from SELECT query
    $sql_fetch = "SELECT order_id, customer_name FROM orders WHERE order_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    
    if ($stmt_fetch === false) {
        die("Error preparing fetch statement: " . $conn->error);
    }

    $stmt_fetch->bind_param("i", $order_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    if ($result->num_rows === 1) {
        $item_details = $result->fetch_assoc();
    } else {
        die("<div class='container'>Order ID not found.</div>");
    }
    $stmt_fetch->close();
} else {
    die("<div class='container'>No Order ID provided for update.</div>");
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>UPDATE Specific Order</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>✏️ UPDATE Order #<?php echo htmlspecialchars($item_details['order_id'] ?? ''); ?></h2>
        
        <?php echo $message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($item_details['order_id'] ?? ''); ?>">

            <label for="customer_name">Customer Name:</label> 
            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($item_details['customer_name'] ?? ''); ?>" required><br>

            <input type="submit" value="Update Order Details">
        </form>

        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='order_read.php'">Cancel/Back to List</button>
        </div>
    </div>
</body>
</html>