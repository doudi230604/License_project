<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include log function
include 'functions.php';

if (!function_exists('logAction')) {
    die("Error: logAction() function not found.");
}

function logAction($conn, $user, $action, $ip_address = null) {
    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    $stmt = $conn->prepare("INSERT INTO audit_logs (user, action, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("sss", $user, $action, $ip_address);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("logAction() prepare failed: " . $conn->error);
    }
}


// SIGN UP logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['txt']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $department = trim($_POST['department']);
    $password = password_hash($_POST['pswd'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already exists.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, role, department, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $role, $department, $password);
        
        if ($stmt->execute()) {
            echo "<script>alert('Registered successfully! Please login.');</script>";
        } else {
            echo "<script>alert('Registration failed. Try again.');</script>";
        }

        $stmt->close();
    }

    $check->close();
}

// LOGIN logic
// LOGIN logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['pswd'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashedPassword, $role);

    if ($stmt->num_rows > 0 && $stmt->fetch() && password_verify($password, $hashedPassword)) {
        session_regenerate_id(true); // Secure session
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        // ✅ Use same logging logic as logout.php
        $ip = $_SERVER['REMOTE_ADDR'];
        $action = "Logged in";

        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
        $logStmt->bind_param("iss", $id, $action, $ip);
        $logStmt->execute();
        $logStmt->close();

        // Redirect by role
        if ($role === 'admin') {
            header("Location: index.php");
        } elseif ($role === 'manager') {
            header("Location: indexmanager.php");
        } elseif ($role === 'employee') {
            header("Location: indexemploye.php");
        } else {
            echo "<script>alert('Unknown role.');</script>";
        }

        exit();
    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }

    $stmt->close();
}


$conn->close();
?> 