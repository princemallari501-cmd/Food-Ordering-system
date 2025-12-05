<?php
// index.php - Dashboard Menu (FINALIZED Card Design with Corrected CSS Classes)
session_start(); 

// Security Check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Ordering System - Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
    /* General Container & Heading */
    body {
        background-color: #f4f7f6; /* Light gray background */
    }
    .container {
        width: 90%;
        max-width: 950px; /* Ginawa pang mas malaki para sa 4 cards */
        margin: 40px auto;
        padding: 30px;
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
    }
    h1 {
        text-align: center;
        color: #34495e;
        margin-bottom: 5px;
    }
    .welcome-message {
        text-align: center;
        color: #27ae60;
        font-weight: bold;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ecf0f1;
    }

    /* Card/Grid Design for Menu */
    .menu-grid {
        display: grid;
        /* Ngayon 2 columns na lang sila para maging pantay at mas malaki */
        grid-template-columns: repeat(2, 1fr); 
        gap: 20px;
        margin-top: 30px;
    }
    .menu-card {
        border-radius: 10px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); /* Mas malalim na shadow */
        transition: transform 0.3s, opacity 0.3s;
        text-decoration: none;
        color: white; /* Default text color sa loob ng card */
        display: block; 
    }
    .menu-card:hover {
        transform: translateY(-5px);
        opacity: 0.95;
    }
    .menu-card i {
        font-size: 3em;
        color: white; 
        margin-bottom: 10px;
    }
    .menu-card h3 {
        margin: 0;
        font-size: 1.3em;
        color: white;
    }
    .menu-card p {
        font-size: 1em;
        color: #ecf0f1;
        margin-top: 8px;
    }
    
    /* Individual Card Colors - Magkakaibang kulay para sa mas magandang visual hierarchy */
    .view-edit-food {
        background-color: #e67e22; /* Orange for Food Management */
    }
    .create-food {
        background-color: #27ae60; /* Green for Food Creation */
    }
    .view-edit-order {
        background-color: #3498db; /* Blue for Order Management */
    }
    .create-order {
        background-color: #9b59b6; /* Purple for Order Creation */
    }

    /* Logout Button */
    .logout-button {
        background-color: #e74c3c; 
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.3s;
        width: 100%;
        margin-top: 25px;
    }
    .logout-button:hover {
        background-color: #c0392b; 
    }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍕 Food Ordering System Dashboard</h1>
        
        <p class="welcome-message">
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! You are successfully logged in.
        </p>
        
        
        <div class="menu-grid">
            <a href="food_read.php" class="menu-card view-edit-food">
                <i class="fas fa-utensils"></i>
                <h3>Food Item Management</h3>
                <p>Tingnan, I-update, at I-delete ang Food Items.</p>
            </a>
            
            <a href="order_read.php" class="menu-card view-edit-order">
                <i class="fas fa-clipboard-check"></i>
                <h3>Order Management</h3>
                <p>Subaybayan, I-edit, at I-delete ang mga Orders.</p>
            </a>
            
            <a href="food_create.php" class="menu-card create-food">
                <i class="fas fa-plus-circle"></i>
                <h3>CREATE New Food Item</h3>
                <p>Mabilis na magdagdag ng bagong putahe.</p>
            </a>

            <a href="order_create.php" class="menu-card create-order">
                <i class="fas fa-shopping-cart"></i>
                <h3>CREATE New Order</h3>
                <p>Mabilis na gumawa ng bagong order.</p>
            </a>

        </div>
        
        <div class="separator" style="margin-top: 40px; border-bottom: 1px solid #ddd;"></div>
        
        <p style="text-align:center; color: #7f8c8d; margin-top: 20px;">System Status: Database Connection Check</p>
        
        <?php
        // Include db_connect.php to check connection status
        include 'db_connect.php'; 
        if (isset($conn) && $conn) {
            echo "<p style='text-align:center; color:#27ae60; font-weight: bold;'>Database Connected Successfully! <i class='fas fa-check-circle'></i></p>";
            // Isara ang koneksyon pagkatapos gamitin
            $conn->close();
        } else {
            echo "<p style='text-align:center; color:#e74c3c; font-weight: bold;'>Connection failed or not established. <i class='fas fa-exclamation-triangle'></i></p>";
        }
        ?>

        <div class="action-buttons">
            <button onclick="location.href='logout.php'" class="logout-button">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </button>
        </div>
        
    </div>
</body>
</html>