<?php
// food_delete.php - Final fix for Foreign Key Constraint
include 'db_connect.php';

// Tiyakin na may Food ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: food_read.php");
    exit();
}

$food_id = $_GET['id'];

// 1. DELETE ALL DEPENDENT ORDERS FIRST (Foreign Key Fix)
$sql_delete_orders = "DELETE FROM orders WHERE food_id = ?";
$stmt_orders = $conn->prepare($sql_delete_orders);
if ($stmt_orders === false) {
    die("Error preparing order deletion statement: " . $conn->error);
}
$stmt_orders->bind_param("i", $food_id);
$stmt_orders->execute();
$stmt_orders->close();

// 2. DELETE THE FOOD ITEM
$sql = "DELETE FROM food_items WHERE food_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing food deletion statement: " . $conn->error);
}

$stmt->bind_param("i", $food_id);

if ($stmt->execute()) {
    // SUCCESS: Redirect back to the list
    header("Location: food_read.php?deleted=true");
    exit();
} else {
    echo "Error deleting food record: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>