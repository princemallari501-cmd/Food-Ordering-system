<?php
// food_create.php
include 'db_connect.php';

// Define the directory where uploaded files will be stored
// IMPORTANT: Make sure this directory exists and is writable by the web server
$target_dir = "uploads/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_path = NULL; // Initialize image path to NULL

    // --- File Upload Handling ---
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // File is an image
            $uploadOk = 1;
        } else {
            echo "<div class='container' style='text-align:center;'>File is not an image.</div>";
            $uploadOk = 0;
        }

        // Check file size (e.g., limit to 500KB)
        if ($_FILES["image"]["size"] > 500000) {
            echo "<div class='container' style='text-align:center;'>Sorry, your file is too large. (Max 500KB)</div>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            echo "<div class='container' style='text-align:center;'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "<div class='container' style='text-align:center;'>Sorry, your file was not uploaded.</div>";
        } else {
            // Generate a unique filename to prevent overwriting and security issues
            $new_filename = uniqid('img_', true) . '.' . $imageFileType;
            $final_target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $final_target_file)) {
                $image_path = $final_target_file; // This is the path we'll save to the database
            } else {
                echo "<div class='container' style='text-align:center;'>Sorry, there was an error uploading your file.</div>";
                // Optionally stop execution or set a default path
            }
        }
    }
    // --- End File Upload Handling ---


    // Only proceed with DB insert if there were no critical errors or if no image was provided
    if ($uploadOk !== 0) {
        // Updated SQL to include the image_path column
        $sql = "INSERT INTO food_items (name, description, price, image_path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        // Updated bind_param to include the image_path (string 's')
        $stmt->bind_param("ssds", $name, $description, $price, $image_path);

        if ($stmt->execute()) {
            echo "<div class='container' style='text-align:center;'>New food item added successfully. <button onclick=\"location.href='food_read.php'\">View List</button></div>";
        } else {
            echo "<div class='container' style='text-align:center;'>Error: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}
// Huwag isara ang connection dito para sa HTML form
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Food Item</title>
    <link rel="stylesheet" type="text/css" href="style.css"> 
</head>
<body>
    <div class="container">
        <h2>➕ Add New Food Item</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <label for="name">Name:</label> <input type="text" id="name" name="name" required><br>
            <label for="description">Description:</label> <textarea id="description" name="description"></textarea><br>
            <label for="price">Price (Php):</label> <input type="number" id="price" step="0.01" name="price" required><br>
            <label for="image">Food Image:</label> <input type="file" id="image" name="image" accept="image/*"><br>
            <input type="submit" value="Add Food Item">
        </form>
        <div class="action-buttons" style="margin-top: 20px;">
            <button onclick="location.href='index.php'">Exit to Index</button>
        </div>
    </div>
</body>
</html>