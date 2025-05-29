<?php
$login_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create connection
    $conn = new mysqli("localhost", "root", "", "login");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get the username and password from the form
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM useress WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_password);
        $stmt->fetch();

        // Verify the password (plain comparison here)
        if ($pass === $stored_password) {
            $login_message = '<div style="color: green; font-weight: bold; text-align: center;">Successfully logged in!</div>';
        } else {
            $login_message = '<div style="color: red; font-weight: bold; text-align: center;">Invalid password. Please try again.</div>';
        }
    } else {
        $login_message = '<div style="color: red; font-weight: bold; text-align: center;">User not found. Please check your username.</div>';
    }
    $stmt->close();
    $conn->close();
    if (!empty($login_message)) echo $login_message;
}
?>