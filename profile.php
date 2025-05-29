<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Connect to the database
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$sql = "SELECT username, email, role, department FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = htmlspecialchars($user['username']);
    $email = htmlspecialchars($user['email']);
    $role_id = $user['role'];
    $department = htmlspecialchars($user['department']);

    $roles = [
        1 => 'Admin',
        2 => 'Manager',
        3 => 'Employer'
    ];
    $role = $roles[$role_id] ?? 'Unknown';
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

            /* Sidebar colors from index.php */
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

        /* Sidebar styles (copied from index.php) */
        #sidebar {
            background-color: #0f766e !important;
            color: #fff !important;
            border-right: none !important;
            width: 260px;
            min-height: 100vh;
            flex-shrink: 0;
            display: block;
        }
        #sidebar h2 {
            color: #fff !important;
        }
        #sidebar nav a {
            color: #fff !important;
            background: transparent !important;
            transition: background 0.2s;
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
            text-decoration: none;
        }
        #sidebar nav a:hover {
            background-color: #0d9488 !important;
            color: #fff !important;
        }
        #sidebar nav a.bg-teal-800 {
            background-color: #134e4a !important;
            color: #fff !important;
        }
        #sidebar .fa-trash,
        #sidebar .fa-users,
        #sidebar .fa-chart-line,
        #sidebar .fa-envelope,
        #sidebar .fa-plus-circle {
            color: #fff !important;
        }

        /* Main content area */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-card {
            background-color: var(--white);
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-top: 5px solid var(--teal);
        }

        .profile-card h2 {
            color: var(--teal-dark);
            margin-bottom: 10px;
        }

        .profile-info {
            margin: 20px 0;
            text-align: left;
        }

        .profile-info p {
            font-size: 16px;
            margin-bottom: 12px;
            color: var(--text-light);
        }

        .profile-info p span {
            font-weight: bold;
            color: var(--teal);
        }

        .logout-btn {
            display: inline-block;
            background-color: var(--teal);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .logout-btn:hover {
            background-color: var(--teal-dark);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="p-6" style="padding: 1.5rem;">
            <h2 class="text-xl font-bold mb-6" style="font-size:1.25rem;font-weight:bold;margin-bottom:1.5rem;">Main sections</h2>
            <nav class="space-y-2">
                <a href="index.php" class="bg-teal-800">Dashboard</a>
                <a href="auditlogs.php">Audit Logs</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="access_controle.php">Access Control</a>
                <a href="profile.php">Profile</a>
                <a href="#" id="sidebar-trash-link" style="margin-top:0.5rem;">Trash</a>
                <form action="logout.php" method="post">
          <a href="logout.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Logout</a>
          </form>
            </nav>
        </div>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-card">
            <h2>Welcome, <?= $username ?> 👋</h2>
            <div class="profile-info">
                <p><span>Email:</span> <?= $email ?></p>
                <p><span>Role:</span> <?= $role ?></p>
                <p><span>Department:</span> <?= $department ?></p>
            </div>
            <form action="logout.php" method="post">
                <button class="logout-btn" type="submit">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>
