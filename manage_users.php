<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies


require 'config.php';

// Add user
// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department = $_POST['department'];
    $role = $_POST['role']; // <--- Add this line

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, department, role) VALUES (?, ?, ?, ?, ?)"); // Include role
    $stmt->execute([$name, $email, $password, $department, $role]); // Pass role
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Update permissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $name = $_POST['name'];
    $department = $_POST['department'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $newPasswordInput = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "succlogin");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch the current password from the DB
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($existingPassword);
    $stmt->fetch();
    $stmt->close();

    // Hash only if the password was modified
    $finalPassword = ($newPasswordInput !== $existingPassword)
        ? password_hash($newPasswordInput, PASSWORD_DEFAULT)
        : $existingPassword;

    // Update user
    $stmt = $conn->prepare("UPDATE users SET username=?, department=?, role=?, email=?, password=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $department, $role, $email, $finalPassword, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all users
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR department LIKE ? OR role LIKE ?");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $users = $stmt->fetchAll();
} else {
    $users = $pdo->query("SELECT * FROM users")->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Minimal custom styles for sidebar scroll */
    #sidebar {
      height: 100vh;
      overflow-y: auto;
    }

    /* Sidebar styling - match auditlogs.php */
    #sidebar {
      background-color: #0f766e !important; /* Teal 700 */
      color: #fff !important;
      border-right: none !important;
    }
    #sidebar h2,
    #sidebar .sidebar-title {
      color: #fff !important;
    }
    #sidebar nav a,
    #sidebar .nav-link {
      color: #fff !important;
      background: transparent !important;
      transition: background 0.2s;
    }
    #sidebar nav a:hover,
    #sidebar .nav-link:hover {
      background-color: #0d9488 !important; /* Teal 600 */
      color: #fff !important;
    }
    #sidebar nav a.bg-teal-800,
    #sidebar .nav-link.active,
    #sidebar .nav-link.bg-teal-800 {
      background-color: #134e4a !important; /* Teal 900 */
      color: #fff !important;
    }
    #sidebar .fa-trash,
    #sidebar .fa-users,
    #sidebar .fa-chart-line,
    #sidebar .fa-envelope,
    #sidebar .fa-plus-circle {
      color: #fff !important;
    }
    .create-section-btn {
      color: #fff !important;
    }
    .create-section-btn:hover {
      background-color: #0d9488 !important;
    }
    #role {
    width: 60%;
    height: 40px; /* Increased height for better visibility */
    background: #e0dedec5;
    display: flex;
    justify-content: center;
    align-items: center; /* Align the text vertically */
    margin: 20px auto;
    padding: 0 12px; /* Adjusted padding for horizontal spacing */
    border: none;
    outline: none;
    border-radius: 5px;
    font-size: 14px; /* Make text readable */
    color: #333; /* Set a readable text color */
}

  </style>
</head>
<body class="bg-gray-50 font-sans">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div id="sidebar" class="w-64 flex-shrink-0 min-h-screen block" style="background-color: #0f766e; color: #fff;">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-6">Main sections</h2>
        <nav class="space-y-2">
          <a href="index.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Dashboard</a>
          <a href="auditlogs.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Audit Logs</a>
          <a href="manage_users.php" class="block px-3 py-2 rounded bg-teal-800">Manage Users</a>
          <a href="access_controle.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Access Control</a>
          <a href="profile.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Profile</a>
          <a href="#" id="sidebar-trash-link" class="block px-3 py-2 rounded hover:bg-teal-600 transition mt-2"> Trash</a>
          <form action="logout.php" method="post">
          <a href="logout.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Logout</a>
          </form>
        </nav>
      </div>
    </div>
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden md:hidden"></div>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="bg-white border-b p-4 header">
        <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
          <div class="flex items-center">
            <button id="mobile-sidebar-toggle" class="mr-4 md:hidden text-teal-600">
              <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-xl font-semibold text-teal-800">Manage Users</h1>
          </div>
          <div class="flex items-center space-x-2">
            <!-- Search Bar -->
            <div class="relative w-full md:w-64">
              <i class="fas fa-search absolute left-3 top-3 text-teal-400"></i>
              <form method="get" class="relative w-full md:w-64">
  <i class="fas fa-search absolute left-3 top-3 text-teal-400"></i>
  <input 
    type="text" 
    name="search"
    placeholder="Search users..." 
    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
    class="w-full pl-10 pr-10 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500"
  >
  <?php if (!empty($_GET['search'])): ?>
    <a href="manage_users.php" class="absolute right-3 top-3 text-teal-400 hover:text-teal-600">
      <i class="fas fa-times"></i>
    </a>
  <?php endif; ?>
</form>

              <button id="clear-search" class="absolute right-3 top-3 text-teal-400 hover:text-teal-600 hidden">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <button onclick="document.getElementById('modal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded ml-2">Add User</button>
          </div>
        </div>
      </div>
      <!-- Content Area -->
      <!-- User Table with Editable Fields -->
<div class="flex-1 overflow-auto p-4">
  <div class="bg-white border rounded-lg p-4 shadow-sm">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
          <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
          <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
          <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
          <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Password</th>
          <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        <?php foreach ($users as $user): ?>
        <tr class="hover:bg-gray-50 transition">
          <form method="post">
            <td class="py-2 px-4">
              <input type="text" name="name" value="<?= htmlspecialchars($user['username']) ?>" class="w-full border rounded px-2 py-1 text-sm">
            </td>
            <td class="py-2 px-4">
              <input type="text" name="department" value="<?= htmlspecialchars($user['department']) ?>" class="w-full border rounded px-2 py-1 text-sm">
            </td>
            <td class="py-2 px-4">
              <input type="text" name="role" value="<?= htmlspecialchars($user['role']) ?>" class="w-full border rounded px-2 py-1 text-sm">
            </td>
            <td class="py-2 px-4">
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border rounded px-2 py-1 text-sm">
            </td>
            <td class="py-2 px-4">
              <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>" class="w-full border rounded px-2 py-1 text-sm">
            </td>
            <td class="py-2 px-4 text-center flex gap-2 justify-center">
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
              <button type="submit" name="update_user" class="bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold shadow-sm">Save</button>
              <a href="?delete=<?= $user['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold shadow-sm" onclick="return confirm('Delete this user?')">Delete</a>
            </td>
          </form>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

  <!-- Modal -->
  <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded shadow-lg w-96">
      <h2 class="text-xl font-semibold mb-4">Add New User</h2>
      <form method="POST">
        <input type="hidden" name="add_user" value="1">
        <div class="mb-3">
          <label class="block text-sm font-medium">Name</label>
          <input type="text" name="name" required class="w-full border px-3 py-2 rounded mt-1">
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium">Email</label>
          <input type="email" name="email" required class="w-full border px-3 py-2 rounded mt-1">
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium">Password</label>
          <input type="password" name="password" required class="w-full border px-3 py-2 rounded mt-1">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium">Department</label>
          <input type="text" name="department" required class="w-full border px-3 py-2 rounded mt-1">
        </div>
        <div class="mb-4">
          <select id="role" name="role" required>
  <option value="">Select Role</option>
  <option value="admin">Admin</option>
  <option value="manager">Manager</option>
  <option value="employee">Employee</option>
</select>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" class="border px-4 py-2 rounded">Cancel</button>
          <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
