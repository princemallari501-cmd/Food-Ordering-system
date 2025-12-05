<?php
// food_update.php - Para sa pag-UPDATE ng Food Item, kasama ang Picture
include 'db_connect.php';

// I-define ang directory kung saan naka-save ang mga files (Dapat tugma ito sa food_create.php)
$target_dir = "uploads/";

$food_id = $_GET['id'] ?? null;
$item_details = [];
$message = '';

// Step 1: Handle POST request (Form Submission for Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $food_id = $_POST['food_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $current_image_path = $_POST['current_image_path'] ?? NULL; // Kunin ang dating image path
    $new_image_path = $current_image_path; // Default: Panatilihin ang luma

    $uploadOk = 1;

    // --- File Upload Handling (Check if a new file was uploaded) ---
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic image checks (same as in food_create.php)
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) { $message .= "<p style='color:red;'>File is not an image.</p>"; $uploadOk = 0; }
        if ($_FILES["image"]["size"] > 500000) { $message .= "<p style='color:red;'>Sorry, your file is too large. (Max 500KB)</p>"; $uploadOk = 0; }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message .= "<p style='color:red;'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>"; $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            // Generate a unique filename
            $new_filename = uniqid('img_', true) . '.' . $imageFileType;
            $final_target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $final_target_file)) {
                // Berified na-upload, ngayon i-delete ang lumang file
                if (!empty($current_image_path) && file_exists($current_image_path)) {
                    unlink($current_image_path); // Delete the old file
                }
                $new_image_path = $final_target_file; // Set ang bagong path
            } else {
                $message .= "<p style='color:red;'>Sorry, there was an error uploading your new file.</p>";
                $uploadOk = 0;
            }
        }
    }
    // --- End File Upload Handling ---


    // Only proceed with DB insert if there were no critical errors
    if ($uploadOk !== 0) {
        // Updated SQL to include name, description, price, and image_path
        $sql = "UPDATE food_items SET name = ?, description = ?, price = ?, image_path = ? WHERE food_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            $message .= "<p style='color:red;'>Error preparing update statement: " . $conn->error . "</p>";
        } else {
            // Updated bind_param: name(s), description(s), price(d), image_path(s), food_id(i) -> "ssdsi"
            $stmt->bind_param("ssdsi", $name, $description, $price, $new_image_path, $food_id); 
            
            if ($stmt->execute()) {
                $message = "<p style='color:green; font-weight: bold;'>Food Item updated successfully!</p>";
                // Maaari mong alisin ang header() redirect para makita ang updated image agad
                // header("Location: food_read.php");
                // exit();
            } else {
                $message .= "<p style='color:red;'>Error updating record: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
    }
}

// Step 2: Fetch current data (for displaying in the form, and re-fetching after POST)
if ($food_id) {
    // BINAGO: 'image_path' added to SELECT query
    $sql_fetch = "SELECT food_id, name, description, price, image_path FROM food_items WHERE food_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    
    if ($stmt_fetch === false) {
        die("Error preparing fetch statement: " . $conn->error);
    }

    $stmt_fetch->bind_param("i", $food_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    if ($result->num_rows === 1) {
        $item_details = $result->fetch_assoc();
    } else {
        die("<div class='container'>Food Item ID not found.</div>");
    }
    $stmt_fetch->close();
} else {
    die("<div class='container'>No Food Item ID provided for update.</div>");
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>UPDATE Specific Food Item</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>✏️ UPDATE Food Item #<?php echo htmlspecialchars($item_details['food_id'] ?? ''); ?></h2>
        
        <?php echo $message; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <input type="hidden" name="food_id" value="<?php echo htmlspecialchars($item_details['food_id'] ?? ''); ?>">
            <input type="hidden" name="current_image_path" value="<?php echo htmlspecialchars($item_details['image_path'] ?? ''); ?>">

            <label for="name">Name:</label> 
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item_details['name'] ?? ''); ?>" required><br>

            <label for="description">Description:</label> 
            <textarea id="description" name="description"><?php echo htmlspecialchars($item_details['description'] ?? ''); ?></textarea><br>
            
            <label for="price">Price (Php):</label> 
            <input type="number" id="price" step="0.01" name="price" value="<?php echo htmlspecialchars($item_details['price'] ?? ''); ?>" required><br>

            <label>Current Picture:</label>
            <?php if (!empty($item_details['image_path'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?php echo htmlspecialchars($item_details['image_path']); ?>" 
                         alt="Current Food Image" 
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc;">
                </div>
            <?php else: ?>
                <p>No current picture uploaded.</p>
            <?php endif; ?>

            <label for="image">Upload New Picture (Max 500KB):</label> 
            <input type="file" id="image" name="image" accept="image/*"><br>

            <input type="submit" value="Update Food Item">
        </form>

        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='food_read.php'">Cancel/Back to List</button>
        </div>
    </div>
</body>
</html>