<?php
session_start();

// Force logout if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Connect to database FIRST
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert audit log for "Deleted a file" if user is logged in


// Handle filters
$userFilter = isset($_GET['user']) ? trim($_GET['user']) : '';
$dateFilter = isset($_GET['date']) ? trim($_GET['date']) : '';

$sql = "SELECT audit_logs.*, users.username AS username
        FROM audit_logs
        LEFT JOIN users ON audit_logs.user_id = users.id
        WHERE 1=1";


$params = [];
$types = '';

if ($userFilter !== '') {
    $sql .= " AND users.username LIKE ?";
    $params[] = "%{$userFilter}%";
    $types .= 's';
}


if ($dateFilter !== '') {
    $sql .= " AND DATE(timestamp) = ?";
    $params[] = $dateFilter;
    $types .= 's';
}

$sql .= " ORDER BY timestamp DESC";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

if ($types !== '') {
    // bind_param requires references
    $bind_names[] = $types;
    for ($i=0; $i<count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name; // pass by reference
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Audit Logs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-teal-700 text-white flex-shrink-0 min-h-screen">
      <div class="p-6">
        <h2 class="text-xl font-bold mb-6">Main sections</h2>
        <nav class="space-y-2">
          <a href="index.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Dashboard</a>
          <a href="auditlogs.php" class="block px-3 py-2 rounded bg-teal-800">Audit Logs</a>
          <a href="manage_users.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Manage Users</a>
          <a href="access_controle.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Access Control</a>
          
          <a href="profile.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Profile</a>
          <a href="#" id="sidebar-trash-link" class="block px-3 py-2 rounded hover:bg-teal-600 transition>
          <i class="fas fa-trash mr-2 text-teal-600"></i>
          <span>Trash</span>
        </a>
          <a href="logout.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Logout</a>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1">
      <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4 text-teal-700">Audit Logs</h1>

        <!-- Filter Form -->
        <form method="GET" class="flex flex-wrap gap-4 mb-6 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700">User</label>
            <input type="text" name="user" value="<?= htmlspecialchars($userFilter) ?>" class="border rounded px-3 py-2 w-48" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>" class="border rounded px-3 py-2" />
          </div>
          <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700">Filter</button>
          <a href="auditlogs.php" class="ml-2 text-sm text-gray-500 hover:text-teal-600">Reset</a>
        </form>

        <!-- Audit Table -->
        <div class="bg-white shadow border rounded-lg overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-teal-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-teal-700 uppercase">User</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-teal-700 uppercase">Action</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-teal-700 uppercase">IP Address</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-teal-700 uppercase">Timestamp</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($row['username']) ?></td>
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($row['action']) ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($row['ip_address']) ?></td>
                    <td class="px-4 py-3 text-gray-500 text-sm"><?= htmlspecialchars($row['timestamp']) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="px-4 py-4 text-center text-gray-500">No logs found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
