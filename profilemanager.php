<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}



// Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Connect to the database
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = htmlspecialchars($user['username']);
    $email = htmlspecialchars($user['email']);
    $role = ucfirst($user['role']);
    $department = htmlspecialchars($user['department']);
    $can_upload = $user['can_upload'] ? 'Yes' : 'No';
    $can_modify = $user['can_modify'] ? 'Yes' : 'No';
    $can_delete = $user['can_delete'] ? 'Yes' : 'No';
    $can_share = $user['can_share'] ? 'Yes' : 'No';
    $created_at = date("F j, Y, g:i a", strtotime($user['created_at']));
} else {
    echo "User not found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
        :root {
            --teal-dark: #004d4d;
            --teal: #008080;
            --teal-light: #20b2aa;
            --background: #f0fdfa;
            --white: #ffffff;
            --text-dark: #1a1a1a;
            --text-light: #444;

            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-bg: #f0fdfa;
            --primary-hover: #ccfbf1;
            --border-color: #99f6e4;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text-dark);
            height: 100vh;
            display: flex;
        }

        #sidebar {
            background-color: var(--primary-dark);
            color: #fff;
            width: 260px;
            min-height: 100vh;
            padding: 1.5rem;
        }

        #sidebar h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        #sidebar nav a {
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: background 0.2s;
        }

        #sidebar nav a:hover {
            background-color: var(--primary);
        }

        .main-content {
            flex: 1;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-card {
            background-color: var(--white);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 600px;
        }

        .profile-card h2 {
            color: var(--teal-dark);
            font-size: 26px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--teal-light);
            padding-bottom: 10px;
        }

        .profile-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            font-size: 16px;
            color: var(--text-light);
        }

        .info-item span {
            display: block;
            font-weight: bold;
            color: var(--teal-dark);
            margin-bottom: 5px;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 25px;
            background-color: var(--teal);
            color: #fff;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: var(--teal-dark);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <h2>Main Section</h2>
       <nav class="space-y-2">
          <a href="indexmanager.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Dashboard</a>
          <a href="auditlogsmanager.php" class="block px-3 py-2 rounded bg-teal-800">Audit Logs</a>
          <a href="profilemanager.php" class="block px-3 py-2 rounded bg-teal-800">Profile</a>
          <a href="#" id="sidebar-trash-link" class="block px-3 py-2 rounded hover:bg-teal-600 transition mt-2"> Trash</a>
          <form action="logout.php" method="post">
          <a href="logout.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Logout</a>
          </form>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-card">
            <h2>Welcome, <?= $username ?> 👋</h2>
            <div class="profile-info">
                <div class="info-item">
                    <span>Email</span>
                    <?= $email ?>
                </div>
                <div class="info-item">
                    <span>Role</span>
                    <?= $role ?>
                </div>
                <div class="info-item">
                    <span>Department</span>
                    <?= $department ?>
                </div>
                <div class="info-item">
                    <span>Can Upload</span>
                    <?= $can_upload ?>
                </div>
                <div class="info-item">
                    <span>Can Modify</span>
                    <?= $can_modify ?>
                </div>
                <div class="info-item">
                    <span>Can Delete</span>
                    <?= $can_delete ?>
                </div>
                <div class="info-item">
                    <span>Can Share</span>
                    <?= $can_share ?>
                </div>
                <div class="info-item">
                    <span>Account Created</span>
                    <?= $created_at ?>
                </div>
            </div>
            <form action="logout.php" method="post">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

</body>
</html>
