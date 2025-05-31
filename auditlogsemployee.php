<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Prevent browser from caching this page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies

$currentUser = $_SESSION['username']; // for display
$currentUserId = intval($_SESSION['user_id']); // for filtering

// DB connection
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional filter for date
$dateFilter = isset($_GET['date']) ? trim($_GET['date']) : '';

// ✅ Modified: JOIN users to get the actual username
$sql = "
    SELECT a.*, u.username 
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.user_id = ?
";

$params = [$currentUserId];
$types = 'i';

if ($dateFilter !== '') {
    $sql .= " AND DATE(a.timestamp) = ?";
    $params[] = $dateFilter;
    $types .= 's';
}

$sql .= " ORDER BY a.timestamp DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$bind_names[] = $types;
foreach ($params as $i => $param) {
    $bind_name = 'bind' . $i;
    $$bind_name = $param;
    $bind_names[] = &$$bind_name;
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

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
          <a href="indexmanager.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Dashboard</a>
          <a href="auditlogsmanager.php" class="block px-3 py-2 rounded bg-teal-800">Audit Logs</a>
          <a href="profilemanager.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Profile</a>
          <a href="#" id="sidebar-trash-link" class="block px-3 py-2 rounded hover:bg-teal-600 transition mt-2">Trash</a>
          <form action="logout.php" method="post">
            <button type="submit" class="block w-full text-left px-3 py-2 rounded hover:bg-teal-600 transition">Logout</button>
          </form>
        </nav>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1">
      <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4 text-teal-700">My Audit Logs</h1>

        <!-- Filter Form -->
        <form method="GET" class="flex flex-wrap gap-4 mb-6 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700">User (readonly)</label>
            <input type="text" name="user" value="<?= htmlspecialchars($currentUser) ?>" readonly class="border rounded px-3 py-2 w-48 bg-gray-100" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>" class="border rounded px-3 py-2" />
          </div>
          <button type="submit" class="bg-[#0f766e] text-white px-4 py-2 rounded hover:bg-teal-700">Filter</button>
          <a href="auditlogsmanager.php" class="ml-2 text-sm text-gray-500 hover:text-teal-600">Reset</a>
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
                    <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
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
