<?php
// transaction.php - This is where the form submission from order_create.php will pass through
// to process the new order.

// *********************************************************************************
// !!! SECURITY WARNING !!!
// This file contains basic security checks, but deeper validation and prepared 
// statements (like mysqli or PDO) are needed against SQL Injection.
// Remember that 'db_connect.php' should use Prepared Statements.
// *********************************************************************************

include 'db_connect.php'; // Ensure this is included and connected to the database

// Use two variables: $file_message for the file upload result, and $main_message for the overall result
$file_message = ''; 
$main_message = '';
$upload_dir = "customer_uploads/"; // !!! IMPORTANT: Ensure you have this folder and it is writable !!!

// Verify that the data came from a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // If not POST, redirect back to order_create.php
    header("Location: order_create.php");
    exit();
}

// Get the data from the POST request and sanitize it (basic sanitization first)
$customer_name = trim($_POST['customer_name'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');

$food_id = $_POST['food_item_id'] ?? null;
$quantity = (int)($_POST['quantity'] ?? 1);
$customer_picture_path = null; // Default value

// 1. Basic Validation
if (empty($customer_name) || empty($contact_number) || empty($address) || empty($food_id) || $quantity < 1) {
    $main_message .= "<p class='error-message'>⚠️ Error: Incomplete form submission (Customer Name, Contact Number, Address, Food Item, and Quantity are required). Please re-enter the order.</p>";
} else {
    // 2. File Upload Handling (Customer Picture)
    if (isset($_FILES['customer_picture']) && $_FILES['customer_picture']['error'] === UPLOAD_ERR_OK) {
        
        // Check if the folder exists, if not, create the folder and verify
        if (!is_dir($upload_dir)) {
            // Create the folder. Recursive = true, so it can create parent folders if needed.
            if (!mkdir($upload_dir, 0777, true)) {
                $file_message .= "<p class='warning-message'>⚠️ Warning: Failed to create the upload folder. The picture will not be saved.</p>";
            }
        }
        
        // Set a max file size (example: 5MB)
        $max_file_size = 5 * 1024 * 1024; // 5MB
        if ($_FILES['customer_picture']['size'] > $max_file_size) {
            $file_message .= "<p class='error-message'>❌ Error: File is too large. The maximum size is 5MB.</p>";
        } else {
            // Check the file type (example: PNG, JPG, JPEG)
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $_FILES['customer_picture']['tmp_name']); 
            finfo_close($finfo);
            
            if ($file_type === false) {
                 $file_type = mime_content_type($_FILES['customer_picture']['tmp_name']); 
            }
            
            if (in_array($file_type, $allowed_types)) {
                // Create a unique filename
                $file_extension = pathinfo($_FILES['customer_picture']['name'], PATHINFO_EXTENSION);
                $new_file_name = uniqid('cust_') . '.' . $file_extension;
                $target_file = $upload_dir . $new_file_name;

                // Move the uploaded file
                if (move_uploaded_file($_FILES['customer_picture']['tmp_name'], $target_file)) {
                    $customer_picture_path = $target_file; // Save this path to the database
                    $file_message .= "<p class='success-message'>✅ Picture successfully uploaded.</p>";
                } else {
                    $file_message .= "<p class='error-message'>❌ Error: A problem occurred while uploading the file.</p>";
                }
            } else {
                $file_message .= "<p class='error-message'>❌ Error: File type is not allowed. Only JPG, JPEG, and PNG are accepted.</p>";
            }
        }

    } elseif (isset($_FILES['customer_picture']) && $_FILES['customer_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        // If there was an upload error but not because no file was provided
        $file_message .= "<p class='error-message'>❌ File Upload Error: Code {$_FILES['customer_picture']['error']}.</p>";
    }

    // 3. Database Insertion (Using MySQLi Prepared Statements for Security)
    $sql = "INSERT INTO orders (customer_name, contact_number, address, food_id, quantity, customer_picture_path, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())";

    // Use prepared statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters: 's' for string, 'i' for integer
        $stmt->bind_param("sssiis", 
            $customer_name, 
            $contact_number, 
            $address, 
            $food_id, 
            $quantity, 
            $customer_picture_path
        );

        if ($stmt->execute()) {
            $last_id = $stmt->insert_id;
            
            // Build the Main Success Message using $file_message
            $main_message .= "<div class='order-success-box'>";
            $main_message .= "<h3 class='success-header'>🎉 Order Successful!</h3>";
            $main_message .= "<p>Order # <strong>{$last_id}</strong> has been recorded.</p>";
            
            // Display the file upload message (if any) here
            $main_message .= $file_message;
            
            $main_message .= "<h4>Order Details:</h4><ul class='order-details-list'>
                <li><strong>Name:</strong> " . htmlspecialchars($customer_name) . "</li>
                <li><strong>Contact:</strong> " . htmlspecialchars($contact_number) . "</li>
                <li><strong>Address:</strong> " . htmlspecialchars($address) . "</li>
                <li><strong>Food ID:</strong> " . htmlspecialchars($food_id) . "</li>
                <li><strong>Quantity:</strong> " . htmlspecialchars($quantity) . "</li>
            </ul>";

            // ***** NEW CODE: Display the Customer Picture *****
            // Check if there is a path and if the file exists before displaying
            if ($customer_picture_path && file_exists($customer_picture_path)) {
                $main_message .= "<div class='customer-photo-container'>";
                $main_message .= "<h4>Customer Photo:</h4>";
                $main_message .= "<img src='" . htmlspecialchars($customer_picture_path) . "' alt='Customer Photo' class='customer-photo-display'>";
                $main_message .= "</div>";
            }
            // *******************************************************

            $main_message .= "</div>";
            
        } else {
            $main_message .= "<p class='error-message'>❌ Error: Order was not recorded. (" . htmlspecialchars($stmt->error) . ")</p>";
        }

        $stmt->close();
    } else {
        $main_message .= "<p class='error-message'>❌ Error: Could not prepare statement (" . htmlspecialchars($conn->error) . ")</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Result</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Base Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        /* Message Box Styling */
        .order-success-box {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .success-header {
            color: #155724;
            font-size: 1.8em;
            margin-top: 0;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .warning-message {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        /* Specific Success messages inside the box, especially the file message */
        .order-success-box .success-message, 
        .order-success-box .warning-message,
        .order-success-box .error-message {
            margin-top: 15px;
            padding: 10px;
            font-size: 0.9em;
            /* Override the full background for internal messages */
            background: none; 
            border: none;
            color: inherit;
        }

        /* Order Details List */
        .order-details-list {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }
        .order-details-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #c3e6cb;
        }
        .order-details-list li strong {
            display: inline-block;
            width: 120px; 
        }

        /* NEW CSS: For Customer Photo Display */
        .customer-photo-container {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #c3e6cb;
            text-align: center;
        }
        .customer-photo-container h4 {
            color: #155724;
            margin-bottom: 10px;
        }
        .customer-photo-display {
            max-width: 100%;
            height: auto;
            max-height: 250px;
            border-radius: 6px;
            border: 2px solid #c3e6cb;
            object-fit: contain;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Back Button */
        .back-link {
            display: block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.2s;
            font-weight: bold;
            text-align: center;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Order Processing Result</h1>

        <?php 
        echo $main_message; 
        ?>

        <a href="order_create.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Return and Create New Order
        </a>
    </div>

</body>
</html>

<?php
// Ensure the connection is closed after use
if (isset($conn)) {
    $conn->close();
}
?>