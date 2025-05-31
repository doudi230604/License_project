<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
    $ip = $_SERVER['REMOTE_ADDR'];
    $action = "Logged out";

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "succlogin");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Log the logout action
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $userId, $action, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Now destroy the session
session_unset();
session_destroy();

// Redirect to login
header("Location: login2.php");
exit();
?>
