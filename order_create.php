<?php
// order_create.php - Para sa pag-CREATE ng bagong Order na may Visual Menu (MAY PICTURE)
include 'db_connect.php'; // Siguraduhin na konektado ito at nagre-return ng $conn object

$message = '';
$customer_name = '';
$food_items = [];

// 1. Fetch ALL Food Items for the Visual Menu Cards (NAGDAGDAG NG image_path)
// MODIFIED SQL QUERY: Isinama ang image_path
$sql_fetch_food = "SELECT food_id, name, price, description, image_path FROM food_items ORDER BY name ASC";
$result_food = $conn->query($sql_fetch_food);

if ($result_food === false) {
    // Siguraduhin na ang error ay ma-handle
    $message .= "<p style='color:red;'>❌ Error fetching food items: " . htmlspecialchars($conn->error) . "</p>";
}

if ($result_food && $result_food->num_rows > 0) {
    while($row = $result_food->fetch_assoc()) {
        $food_items[] = $row;
    }
} else {
    $message .= "<p style='color:orange;'>⚠️ Walang Food Item na available. Kailangan mo munang magdagdag ng Food Item bago makapag-order.</p>";
}

// Tiyakin na naka-close ang connection
if (isset($conn) && $conn) $conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CREATE New Order</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS para sa menu cards */
        .food-item-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .food-card {
            border: 2px solid transparent; /* Ginawang transparent ang default border */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease-in-out;
            background-color: #fff;
        }
        .food-card input[type="radio"] {
            display: none;
        }
        .food-card input[type="radio"]:checked + label .food-card-content { /* I-target ang content sa loob ng label */
            /* Walang pagbabago sa radio button, i-target ang card mismo */
        }
        /* I-apply ang style sa food-card element kapag ang radio button nito ay napili */
        .food-card input[type="radio"]:checked + label .food-card-content { 
            border-color: #007bff; 
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5), 0 5px 15px rgba(0,0,0,0.1);
        }
        .food-card label {
            display: block;
            cursor: pointer;
            padding: 0; /* Alisin ang padding sa label */
            text-align: center;
        }
        .food-card-content { /* Ito ang gagamitin sa border effect */
            display: block;
            border: 2px solid #ddd; /* Dito ang visual border */
            border-radius: 6px;
            padding-bottom: 10px;
            height: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .food-card-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        .food-content h4 {
            margin-top: 5px;
            margin-bottom: 5px;
            color: #333;
        }
        .food-content .price {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛒 CREATE New Order (Visual Menu)</h2>
        <?php echo $message; ?>

        <form action="transaction.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            
            <h3>Customer Details</h3>
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required><br>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" required><br>
            
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required><br>

            <h3 style="margin-top: 25px;">Menu Selection (Pumili ng isa):</h3>
            
            <?php if (!empty($food_items)): ?>
                <div class="food-item-container">
                    <?php foreach ($food_items as $item): ?>
                        <div class="food-card">
                            <input type="radio" id="food_<?php echo $item['food_id']; ?>" name="food_item_id" value="<?php echo htmlspecialchars($item['food_id']); ?>">
                            
                            <label for="food_<?php echo $item['food_id']; ?>">
                                <div class="food-card-content">
                                    <?php 
                                    $image_path = htmlspecialchars($item['image_path']);
                                    // I-check kung may path at kung existing ang file
                                    if (!empty($image_path) && file_exists($image_path)): 
                                    ?>
                                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="food-card-img">
                                    <?php else: ?>
                                        <div style="height: 150px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #eee;">
                                            <i class="fas fa-utensils" style="font-size: 24px; color: #adb5bd;"></i>
                                            <p style="margin-left: 10px; color: #adb5bd;">No Image</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="food-content">
                                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p class="price">Php <?php echo number_format($item['price'], 2); ?></p>
                                        <p style="font-size: 0.9em; color: #6c757d; padding: 0 10px;"><?php echo htmlspecialchars($item['description'] ?? 'Walang description.'); ?></p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="food_error_message" style="color: red; margin-top: 10px; font-weight: bold; display: none;">
                    ⚠️ Kailangan mong pumili ng isang food item.
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #e67e22; font-weight: bold; padding: 20px; border: 1px dashed #e67e22; border-radius: 5px;">
                    Walang Food Items na makikita. Paki-dagdag muna sa Food Management.
                </p>
            <?php endif; ?>
            
            <h3 style="margin-top: 35px;">Order Details</h3>
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" value="1" required><br>

            <label for="customer_picture" style="margin-top: 15px;">Customer Picture (Optional):</label>
            <input type="file" id="customer_picture" name="customer_picture" accept="image/*"><br>

            <input type="submit" value="Submit New Order">
        </form>

        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='index.php'"><i class="fas fa-arrow-left"></i> Bumalik sa Dashboard</button>
        </div>
    </div>

    <script>
        function validateForm() {
            // I-check kung may napiling radio button (food_item_id)
            const selectedFood = document.querySelector('input[name="food_item_id"]:checked');
            const errorMessage = document.getElementById('food_error_message');

            if (!selectedFood) {
                // Kung walang napili
                errorMessage.style.display = 'block';
                // I-scroll sa error message
                errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false; // Pigilan ang form submission
            } else {
                // Kung may napili
                errorMessage.style.display = 'none';
                return true; // Payagan ang form submission
            }
        }
    </script>
</body>
</html>