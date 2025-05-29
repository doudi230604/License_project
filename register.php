<?php
$auth_message = '';
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $action = $_POST['action']; // "register" or "login"

    if ($action === "register") {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM useres2 WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $auth_message = '<div style="color: red; text-align: center;">❌ Username already taken.</div>';
        } else {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO useres2 (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $auth_message = '<div style="color: green; text-align: center;">✅ Registration successful. You can now log in.</div>';
            } else {
                $auth_message = '<div style="color: red; text-align: center;">❌ Registration failed. Try again.</div>';
            }
        }
        $stmt->close();
    }

    if ($action === "login") {
        $stmt = $conn->prepare("SELECT password FROM useres2 WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $auth_message = '<div style="color: green; font-weight: bold; text-align: center;">✅ Login successful! Welcome, <b>' . htmlspecialchars($username) . '</b>.</div>';
            } else {
                $auth_message = '<div style="color: red; text-align: center;">❌ Incorrect password.</div>';
            }
        } else {
            $auth_message = '<div style="color: red; text-align: center;">❌ Username not found.</div>';
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Auth Form</title>
  <style>
    /* Your CSS from earlier (unchanged) */
    :root {
        --background: #1a1a2e;
        --color: #ffffff;
        --primary-color: #0f3460;
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        margin: 0;
        font-family: "poppins";
        background: var(--background);
        color: var(--color);
        letter-spacing: 1px;
        transition: background 0.2s ease;
    }
    a { text-decoration: none; color: var(--color); }
    h1 { font-size: 2.5rem; text-align: center; }
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .login-container { position: relative; width: 22.2rem; }
    .form-container {
        border: 1px solid hsla(0, 0%, 65%, 0.158);
        box-shadow: 0 0 36px 1px rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        backdrop-filter: blur(20px);
        padding: 2rem;
        text-align: center;
    }
    form input, form select {
        display: block;
        padding: 14.5px;
        width: 100%;
        margin: 1rem 0;
        color: var(--color);
        background-color: #9191911f;
        border: none;
        border-radius: 5px;
        font-weight: 500;
        font-size: 15px;
        backdrop-filter: blur(15px);
    }
    form button {
        background-color: var(--primary-color);
        color: var(--color);
        padding: 13px;
        border-radius: 5px;
        font-size: 18px;
        font-weight: bold;
        width: 100%;
        cursor: pointer;
        margin-top: 1rem;
        border: none;
    }
    form button:hover {
        box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.15);
        transform: scale(1.02);
    }
    .switch {
        margin-top: 1rem;
    }
    .switch a {
        color: var(--primary-color);
        cursor: pointer;
        font-weight: bold;
    }
    .message {
        margin: 1rem 0;
    }
  </style>
</head>
<body>
  <section class="container">
    <div class="login-container">
      <div class="form-container">
        <h1>AUTH FORM</h1>
        <?php if (!empty($auth_message)) echo '<div class="message">'.$auth_message.'</div>'; ?>
        <form action="" method="POST">
          <input type="text" name="username" placeholder="Username" required />
          <input type="password" name="password" placeholder="Password" required />
          <select name="action">
              <option value="login">Login</option>
              <option value="register">Register</option>
          </select>
          <button type="submit">SUBMIT</button>
        </form>
      </div>
    </div>
  </section>
</body>
</html>
