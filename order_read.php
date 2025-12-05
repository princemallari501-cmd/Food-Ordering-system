<?php
// order_read.php - READ/VIEW All Orders with Search/Filter Functionality (FINAL FIXED VERSION)
include 'db_connect.php'; 

if (!isset($conn) || !$conn) {
    die("<div class='container' style='color:red;'>Database connection failed. Please check db_connect.php</div>");
}

// ----------------------------------------------------
// 1. Search Logic
// ----------------------------------------------------
$search_term = $_GET['search'] ?? '';
$sql = "SELECT order_id, customer_name, order_date FROM orders";
$params = [];
$types = '';

if (!empty($search_term)) {
    // Added a WHERE clause to filter by Customer Name
    $sql .= " WHERE customer_name LIKE ?";
    $search_param = "%" . $search_term . "%";
    $params[] = $search_param;
    $types .= "s"; // String type for one parameter
}

$sql .= " ORDER BY order_id DESC"; 

// Prepare the query
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("<div class='container' style='color:red; margin-top: 20px;'>Error preparing statement: " . $conn->error . "</div>");
}

// Bind the parameters
if (!empty($search_term)) {
    $stmt->bind_param($types, ...$params);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
// ----------------------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEW All Orders (With Search)</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>🧾 READ ALL ORDER </h2>
        <p>Select an Order ID to Update or Delete. (Status/Total Amount Not Available)</p>
        
        <form method="get" action="order_read.php" style="margin-bottom: 20px; display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search by Customer Name..." style="flex-grow: 1; padding: 10px;" value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit" style="padding: 10px 15px; background-color: #3498db; color: white; border: none; cursor: pointer; border-radius: 4px;">
                <i class="fas fa-search"></i> Search
            </button>
            <button type="button" onclick="window.location.href='order_read.php'" style="padding: 10px 15px; background-color: #95a5a6; color: white; border: none; cursor: pointer; border-radius: 4px;">
                <i class="fas fa-redo"></i> Reset
            </button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <p>Number of Results: **<?php echo $result->num_rows; ?>**</p>
            <table border="1" style="width:100%; margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Action (UPDATE/DELETE)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['order_date'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="order_update.php?id=<?php echo $row['order_id']; ?>" class="action-link update-link">
                                    <i class="fas fa-edit"></i> UPDATE
                                </a> 
                                
                                <a href="order_delete.php?id=<?php echo $row['order_id']; ?>" class="action-link delete-link" 
                                    onclick="return confirm('Are you sure you want to delete Order #<?php echo $row['order_id']; ?>?');">
                                    <i class="fas fa-trash-alt"></i> DELETE
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style='color:orange;'>No Orders found <?php echo !empty($search_term) ? "matching '{$search_term}'" : "in the database."; ?></p>
        <?php endif; ?>

        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='index.php'"><i class="fas fa-arrow-left"></i> Return to Dashboard</button>
        </div>
    </div>
</body>
</html>
<?php 
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close(); 
?>