<?php
session_start();


$conn = new mysqli("localhost", "root", "", "succlogin");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

include 'functions.php';

if (function_exists('logAction')) {
    echo "Function logAction() is loaded.<br>";
} else {
    echo "Function logAction() is NOT loaded.<br>";
}



// Sign up logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = $_POST['txt'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $department = $_POST['department'];
    $password = password_hash($_POST['pswd'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, role, department, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $username, $email, $role, $department, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Registered successfully! Please login.');</script>";
    } else {
        echo "<script>alert('Email already exists.');</script>";
    }
    $stmt->close();
}

// Login logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['pswd'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashedPassword);

    if ($stmt->num_rows > 0 && $stmt->fetch() && password_verify($password, $hashedPassword)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;

        logAction($conn, $id, $username, "Logged in");

        header("Location: index.php");  // Redirect here
        exit();
    } else {
        echo "<script>alert('Invalid email or password');</script>";
    }

    $stmt->close();
}


$conn->close();
?>
