<?php
// login.php - Simple Login Form (HIDDEN CREDENTIALS)
session_start(); // Simulan ang PHP session

// Tiyakin na hindi na pwedeng balikan ang login page kung naka-login na.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit;
}

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- ANG CREDENTIALS AY: user: prince, pass: prince123 ---
    $valid_username = "prince";
    $valid_password = "prince123"; 
    // -----------------------------------------------------------

    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';

    // Simple check ng credentials
    if ($input_username === $valid_username && $input_password === $valid_password) {
        // SUCCESS: I-set ang session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $valid_username;
        
        // Redirect sa main dashboard
        header("Location: index.php");
        exit;
    } else {
        // ERROR: Maling credentials
        $login_error = "Invalid Username or Password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login</title>
    <link rel="stylesheet" type="text/css" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #ecf0f1; /* Light background */
        }
        .login-container {
            width: 350px;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            background-color: #fff;
        }
        .login-container h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        .login-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        .login-container input[type="text"], 
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .login-container input[type="submit"] {
            width: 100%;
            background-color: #2ecc71;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }
        .login-container input[type="submit"]:hover {
            background-color: #27ae60;
        }
        .error {
            color: #e74c3c;
            background-color: #fceae8;
            border: 1px solid #e74c3c;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔒 Food System Login</h2>
        <?php if ($login_error): ?>
            <p class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $login_error; ?></p>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Log In">
        </form>
    </div>
</body>
</html>