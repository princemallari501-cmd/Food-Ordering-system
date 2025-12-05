<?php
// food_read.php - READ/VIEW All Food Items with Search/Filter Functionality (Updated Design)
include 'db_connect.php'; 

// 1. Database Check
if (!isset($conn) || !$conn) {
    die("Database connection failed. Please check db_connect.php");
}

// ----------------------------------------------------
// 2. Search Logic
// ----------------------------------------------------
$search_term = $_GET['search'] ?? ''; 
$sql = "SELECT food_id, name, description, price, image_path FROM food_items"; 
$params = [];
$types = '';

if (!empty($search_term)) {
    $sql .= " WHERE name LIKE ? OR description LIKE ?";
    $search_param = "%" . $search_term . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss"; 
}

$sql .= " ORDER BY food_id DESC"; 

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("<div class='container error-message'>Error preparing statement: " . $conn->error . "</div>");
}

if (!empty($search_term)) {
    // Check if bind_param is called with correct parameters
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
}

// Execute and get the result
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIEW All Food Items</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Custom styles for buttons in the table */
        .action-btn {
            padding: 5px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            transition: background-color 0.3s;
        }
        .update-btn {
            background-color: #3498db; /* Blue */
            color: white;
        }
        .update-btn:hover {
            background-color: #2980b9;
        }
        .delete-btn {
            background-color: #e74c3c; /* Red */
            color: white;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🍽️ VIEW All Food Items (READ)</h2>
        <p>Select a Food Item to Update or Delete. Use the Search Filter to look up items.</p>
        
        <form method="get" action="food_read.php" class="filter-container" style="margin-bottom: 20px;">
            <label for="search" style="display: inline-block; font-weight: normal;">Search:</label>
            <input type="search" id="search" name="search" placeholder="Name or Description" 
                    value="<?php echo htmlspecialchars($search_term); ?>" style="width: auto; display: inline-block;">
            <button type="submit" class="action-btn update-btn" style="margin-right: 0;"><i class="fas fa-search"></i> Search</button>
            <?php if (!empty($search_term)): ?>
                <button type="button" onclick="location.href='food_read.php'" class="action-btn delete-btn">
                    <i class="fas fa-times"></i> Clear
                </button>
            <?php endif; ?>
        </form>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="message success-message" style="color: green; font-weight: bold; margin-bottom: 15px;">✅ Food Item successfully deleted!</div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAME</th>
                        <th>DESCRIPTION</th>
                        <th>PRICE</th>
                        <th>PICTURE</th> 
                        <th>ACTIONS</th> </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['food_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>Php <?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <?php if (!empty($row['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                                        alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <span style="color: #7f8c8d;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="food_update.php?id=<?php echo $row['food_id']; ?>" class="action-btn update-btn">
                                    <i class="fas fa-edit"></i> UPDATE
                                </a> 
                                <a href="food_delete.php?id=<?php echo $row['food_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this food item?');">
                                    <i class="fas fa-trash-alt"></i> DELETE
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style='color:orange; margin-top: 20px;'>No Food Items found <?php echo !empty($search_term) ? "matching '{$search_term}'" : "in the database."; ?></p>
        <?php endif; ?>

        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='food_create.php'" style="background-color: #27ae60;"><i class="fas fa-plus"></i> Add New Food Item</button>
            <button onclick="location.href='index.php'"><i class="fas fa-arrow-left"></i> Return to Dashboard</button>
        </div>
    </div>
</body>
</html>
<?php 
if (isset($stmt)) $stmt->close();
// $conn will be closed by the logic if it was opened
?>