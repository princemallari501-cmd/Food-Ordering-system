<?php
// logout.php
session_start(); // Simulan ang session

// I-unset ang lahat ng session variables
$_SESSION = array();

// I-destroy ang session
session_destroy();

// Redirect pabalik sa login page
header("Location: login.php");
exit;
?>