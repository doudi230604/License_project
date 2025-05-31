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



ini_set('display_errors', 1);

$upload_dir = "uploads/";
$message = "";

// Create the uploads folder if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["document"])) {
    $file = $_FILES["document"];
    $filename = basename($file["name"]);
    $target_file = $upload_dir . $filename;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $file_size = $file["size"];

    if ($file["size"] > 5 * 1024 * 1024) {
        $message = "<span class='text-red-600'>❌ File is too large (max 5MB).</span>";
        echo $message; exit;
    } elseif (move_uploaded_file($file["tmp_name"], $target_file)) {
        $conn = new mysqli("localhost", "root", "", "succlogin");
        if ($conn->connect_error) {
            $message = "Database connection failed: " . $conn->connect_error;
            echo $message; exit;
        }
        $stmt = $conn->prepare("INSERT INTO uploaded_files (filename, filetype, filesize) VALUES (?, ?, ?)");
        if (!$stmt) {
            $message = "Prepare failed: " . $conn->error;
            echo $message; exit;
        }
        $stmt->bind_param("ssi", $filename, $file_type, $file_size);
        if (!$stmt->execute()) {
            $message = "Execute failed: " . $stmt->error;
            echo $message; exit;
        }
        $stmt->close();
        $conn->close();
        $message = "<span class='text-green-600'>✅ File uploaded successfully.</span>";
        echo $message; exit;
    } else {
        $message = "<span class='text-red-600'>❌ Error uploading file.</span>";
        echo $message; exit;
    }
}

if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $conn = new mysqli("localhost", "root", "", "succlogin");
    if (!$conn->connect_error) {
        // Move file info to trash table
        $conn->query("INSERT INTO trash (filename, filetype, filesize, deleted_at)
                      SELECT filename, filetype, filesize, NOW() FROM uploaded_files WHERE id = $delete_id");
        // Optionally, delete the file from uploads directory:
        $res = $conn->query("SELECT filename FROM uploaded_files WHERE id = $delete_id");
        if ($res && $row = $res->fetch_assoc()) {
            $filepath = $upload_dir . $row['filename'];
            if (file_exists($filepath)) unlink($filepath);
        }
        // Remove from uploaded_files
        $conn->query("DELETE FROM uploaded_files WHERE id = $delete_id");
        $conn->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle multiple file deletion (Delete Selected)

if (isset($_POST['file_ids']) && is_array($_POST['file_ids'])) {
    $conn = new mysqli("localhost", "root", "", "succlogin");
    $upload_dir = "uploads/";
    $userId = intval($_SESSION['user_id']);
    $ip = $_SERVER['REMOTE_ADDR'];

    if (!$conn->connect_error) {
        foreach ($_POST['file_ids'] as $delete_id) {
            $delete_id = intval($delete_id);

            // Get file info before deleting
            $result = $conn->query("SELECT filename FROM uploaded_files WHERE id = $delete_id");
            $filename = '';
            if ($row = $result->fetch_assoc()) {
                $filename = $row['filename'];
            }

            // Move file metadata to trash table
            $conn->query("INSERT INTO trash (filename, filetype, filesize, deleted_at)
                          SELECT filename, filetype, filesize, NOW() FROM uploaded_files WHERE id = $delete_id");

            // Remove from uploaded_files
            $conn->query("DELETE FROM uploaded_files WHERE id = $delete_id");

            // Insert audit log for deletion
            if (!empty($filename)) {
                $action = "Deleted file: $filename";
                $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $userId, $action, $ip);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->close();
    }

    // Redirect to avoid form resubmission warning
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}




if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['toggle_approve_id'])) {
    $id = intval($_POST['toggle_approve_id']);
    $conn = new mysqli("localhost", "root", "", "succlogin");
    if (!$conn->connect_error) {
        // Get current state
        $result = $conn->query("SELECT approved FROM uploaded_files WHERE id = $id");
        if ($row = $result->fetch_assoc()) {
            $newStatus = $row['approved'] ? 0 : 1;
            $conn->query("UPDATE uploaded_files SET approved = $newStatus WHERE id = $id");
        }
        $conn->close();
    }
    // Avoid resubmission message
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$upload_dir = "uploads/";

$conn = new mysqli("localhost", "root", "", "succlogin");
if (!$conn->connect_error) {

    // Restore logic
   

if (isset($_POST['restore_id'])) {
    $id = intval($_POST['restore_id']);
    $res = $conn->query("SELECT * FROM trash WHERE id = $id");
    
    if ($res && $row = $res->fetch_assoc()) {
        // Move back to uploaded_files
        $stmt = $conn->prepare("INSERT INTO uploaded_files (filename, filetype, filesize) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $row['filename'], $row['filetype'], $row['filesize']);
        $stmt->execute();

        // Delete from trash
        $conn->query("DELETE FROM trash WHERE id = $id");

        // ✅ Add audit log for restore action only
        if (isset($_SESSION['user_id'])) {
            $userId = intval($_SESSION['user_id']);
            $ip = $_SERVER['REMOTE_ADDR'];
            $action = "Restored file: " . $row['filename'];

            $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
            $logStmt->bind_param("iss", $userId, $action, $ip);
            $logStmt->execute();
            $logStmt->close();
        }
    }

    $conn->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}



    // Permanent delete logic
    if (isset($_POST['delete_perm_id'])) {
        $id = intval($_POST['delete_perm_id']);
        $res = $conn->query("SELECT filename FROM trash WHERE id = $id");
        if ($res && $row = $res->fetch_assoc()) {
            $file = $upload_dir . $row['filename'];
            if (file_exists($file)) unlink($file);
        }
        $conn->query("DELETE FROM trash WHERE id = $id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Delete all from trash
    if (isset($_POST['delete_all_trash'])) {
        $res = $conn->query("SELECT filename FROM trash");
        while ($res && $row = $res->fetch_assoc()) {
            $file = $upload_dir . $row['filename'];
            if (file_exists($file)) unlink($file);
        }
        $conn->query("DELETE FROM trash");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $conn->close();
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Custom styles for the Document Management System */

:root {
  --primary: #0d9488; /* Teal 600 */
  --primary-light: #14b8a6; /* Teal 500 */
  --primary-dark: #0f766e; /* Teal 700 */
  --primary-bg: #f0fdfa; /* Teal 50 */
  --primary-hover: #ccfbf1; /* Teal 100 */
  --accent: #0891b2; /* Cyan 600 */
  --accent-light: #06b6d4; /* Cyan 500 */
  --text-primary: #134e4a; /* Teal 900 */
  --text-secondary: #5eead4; /* Teal 300 */
  --border-color: #99f6e4; /* Teal 200 */
}

/* Ensure the page takes up the full height */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

/* Main background color */
body {
  background-color: #f8fafc;
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

/* Ensure the page takes up the full height */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

/* Main background color */
body {
  background-color: #f8fafc;
}

/* Sidebar styling */
#sidebar {
  background-color: white;
  border-right-color: var(--border-color);
}

#sidebar h2 {
  color: var(--primary-dark);
}

#sidebar .folder-item.active a {
  background-color: var(--primary-bg);
}

/* Folder icon rotation for expanded folders */
.folder-icon.expanded {
  transform: rotate(90deg);
}

/* Folder icon color change when folder is open */
.folder-item.active .fa-folder,
.folder-item.active .fa-folder-open {
  color: var(--primary) !important;
}

/* Navigation icons */
#sidebar .fa-home,
#sidebar .fa-star,
#sidebar .fa-clock,
#sidebar .fa-share-alt,
#sidebar .fa-trash,
#sidebar .fa-cog {
  color: var(--primary) !important;
}

/* Document row hover and selected states */
.document-row:hover {
  background-color: var(--primary-bg);
}

.document-row.selected {
  background-color: var(--primary-bg);
}

/* Status badge styles */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-approved {
  background-color: #d1fae5;
  color: #065f46;
}

.status-pending {
  background-color: #ccfbf1;
  color: #0f766e;
}

.status-review {
  background-color: #cffafe;
  color: #155e75;
}

/* Document type icons */
.doc-icon {
  width: 20px;
  height: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.5rem;
}

.doc-icon.word {
  color: var(--accent);
}

.doc-icon.excel {
  color: var(--primary);
}

.doc-icon.powerpoint {
  color: var(--accent-light);
}

.doc-icon.pdf {
  color: var(--primary-dark);
}

.doc-icon.image {
  color: var(--primary-light);
}

.doc-icon.video {
  color: var(--accent);
}

.doc-icon.archive {
  color: var(--text-primary);
}

.doc-icon.text {
  color: var(--primary);
}

/* Mobile sidebar styles */
@media (max-width: 768px) {
  #sidebar {
    position: fixed;
    left: -100%;
    top: 0;
    bottom: 0;
    z-index: 30;
    transition: left 0.3s ease;
    width: 80%;
    max-width: 300px;
  }
  
  #sidebar.open {
    left: 0;
  }
  
  #sidebar-overlay.open {
    display: block;
  }
}

/* Sort icons */
th[data-sort] .fa-sort-up,
th[data-sort] .fa-sort-down {
  display: none;
}

th[data-sort].sort-asc .fa-sort {
  display: none;
}

th[data-sort].sort-asc .fa-sort-up {
  display: inline-block;
}

th[data-sort].sort-desc .fa-sort {
  display: none;
}

th[data-sort].sort-desc .fa-sort-down {
  display: inline-block;
}

/* Disabled mobile action items */
.mobile-action-item.disabled {
  opacity: 0.5;
  pointer-events: none;
}

/* Notification styles */
.notification {
  animation: slideIn 0.3s ease forwards;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Disabled button styles */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Button styling */
button {
  transition: all 0.2s ease;
}

button:not(:disabled):hover {
  background-color: var(--primary-bg);
  border-color: var(--primary);
}

/* Header styling */
.header {
  border-bottom-color: var(--border-color);
}

/* Table header styling */
thead {
  background-color: var(--primary-bg) !important;
}

th {
  color: var(--primary-dark) !important;
}

/* Checkbox styling */
input[type="checkbox"]:checked {
  background-color: var(--primary);
  border-color: var(--primary);
}

/* Search input styling */
#search-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(13, 148, 136, 0.2);
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
          <a href="index.php" class="block px-3 py-2 rounded bg-teal-800">Dashboard</a>
          <a href="auditlogs.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Audit Logs</a>
          <a href="manage_users.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Manage Users</a>
          <a href="access_controle.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Access Control</a>
          <a href="profile.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Profile</a>
          <a href="#" id="sidebar-trash-link" class="block px-3 py-2 rounded hover:bg-teal-600 transition mt-2"> Trash</a>
          <form action="logout.php" method="post">
          <a href="logout.php" class="block px-3 py-2 rounded hover:bg-teal-600 transition">Logout</a>
          </form>
        </nav>
      </div>
    </div>
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="bg-white border-b p-4 header">
        <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
          <div class="flex items-center">
            <button id="mobile-sidebar-toggle" class="mr-4 md:hidden text-teal-600">
              <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-xl font-semibold text-teal-800">Documents</h1>
          </div>
          <div class="flex items-center space-x-2">
            
            
              <form method="GET" class="">
    <input 
      type="text" 
      name="search" 
      placeholder="Search by filename..." 
      value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
      class="px-3 py-2 border border-gray-300 rounded "
    />
    <button type="submit" style="background-color: #0f766e;" class="ml-2 px-4 py-2 text-white rounded hover:bg-teal-700">
     Search
   </button>

    </form>
          
            
            <!-- Download Selected Button in Header -->
            
            <!-- Action Buttons -->
             <!-- Search Bar -->
             
            <div class="flex items-center space-x-2">
              <form method="POST" action="download_files.php" id="download-form" target="_blank" style="display:inline;">
              <input type="hidden" name="file_ids[]" id="download-file-ids">
              <button type="button" class="hidden md:flex items-center px-3 py-2 border border-teal-500 rounded-md text-sm text-teal-700 hover:bg-teal-50" onclick="submitDownload()"><i class="fas fa-download mr-2"></i>Download </button>
              </form>
            <form method="POST" id="delete-selected-form" onsubmit="return confirm('Are you sure you want to delete selected files?');">
              <input type="hidden" name="file_ids[]" id="delete-selected-ids">
              <button type="submit" class="hidden md:flex items-center px-3 py-2 border border-teal-500 rounded-md text-sm text-teal-700 hover:bg-teal-50" ><i class="fas fa-trash mr-2"></i>Delete </button>
            </form>
              
              <!-- Upload Button -->
              <button type="button" id="upload-btn" class="md:flex items-center px-3 py-2 border border-teal-500 rounded-md text-sm text-teal-700 hover:bg-teal-50">
                <i class="fas fa-upload mr-2"></i>
                Upload
              </button>
              <button id="approve-btn" class="hidden md:flex items-center px-3 py-2 border border-teal-500 rounded-md text-sm text-teal-700 hover:bg-teal-50 disabled:opacity-50" >
                <i class="fas fa-check-circle mr-2"></i>
                Approve
              </button>
          
            </div>
          </div>
        </div>
      </div>
      
      <!-- Document Table (restored to show uploaded files) -->
      <!-- 🔍 SEARCH FORM -->
  
        <div class="flex-1 overflow-auto p-4">
          <div class="bg-white border rounded-lg overflow-hidden">
            <table id="document-table" class="min-w-full divide-y divide-teal-100">
              <thead class="bg-teal-50">
                <tr>
                  <th><input type="checkbox" id="select-all"></th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Type</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Date Modified</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Size</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Action</th>


                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-teal-100" id="document-list">
                <?php
                $conn = new mysqli("localhost", "root", "", "succlogin");
                if ($conn->connect_error) {
                    echo '<tr><td colspan="5" class="px-4 py-3 text-center text-red-600">Database error.</td></tr>';
                } else {
                    $search = $_GET['search'] ?? '';
$search = $conn->real_escape_string($search);

if (!empty($search)) {
    $result = $conn->query("SELECT * FROM uploaded_files WHERE filename LIKE '%$search%' ORDER BY uploaded_at DESC");
} else {
    $result = $conn->query("SELECT * FROM uploaded_files ORDER BY uploaded_at DESC");
}

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td class="px-4 py-3 text-center">';
    echo '<input type="checkbox" class="file-checkbox" name="file_ids[]" value="' . htmlspecialchars($row['id']) . '">';
    echo '</td>';
    $fileUrl = 'uploads/' . rawurlencode($row['filename']);
    echo '<td class="px-4 py-3">';
    echo '<a href="' . $fileUrl . '" target="_blank" class="text-teal-600 hover:underline">';
    echo htmlspecialchars($row['filename']);
    echo '</a>';
    echo '</td>';
    echo '<td class="px-4 py-3">' . strtoupper(htmlspecialchars($row['filetype'])) . '</td>';
    echo '<td class="px-4 py-3">' . htmlspecialchars($row['uploaded_at']) . '</td>';
    echo '<td class="px-4 py-3">' . round($row['filesize'] / 1024, 2) . ' KB</td>';
    echo '<td class="px-4 py-3 text-center">';
    echo $row['approved']
        ? '<span class="text-green-600 font-semibold">Approved</span>'
        : '<span class="text-gray-500">Pending</span>';
    echo '</td>';
    // ✅ This is a stand-alone form for each row
    echo '<td class="px-4 py-3 text-center">';
    echo '<form method="POST" action="" style="display:inline;">';
    echo '<input type="hidden" name="toggle_approve_id" value="' . $row['id'] . '">';
    echo '<button type="submit" class="px-3 py-1 ' . ($row['approved'] ? 'bg-gray-600' : 'bg-green-600') . ' text-white rounded hover:bg-green-700 text-sm font-bold">';
    echo $row['approved'] ? 'Unapprove' : 'Approve';
    echo '</button>';
    echo '</form>';
    echo '</td>';

    echo '</tr>';
}
                    } else {
                        echo '<tr><td colspan="7" class="px-4 py-3 text-center text-gray-500">No matching files found.</td></tr>';
                    }
                    $conn->close();
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm">
        <h2 class="text-lg font-semibold mb-4">Upload Document</h2>
        <form method="POST" enctype="multipart/form-data" id="modalUploadForm" action="">
          <input type="file" name="document" id="modalFileInput" required class="mb-4 block w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-700">
          <div class="flex justify-end space-x-2">
            <button type="button" onclick="closeUploadModal()" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-sm font-bold">Cancel</button>
            <button type="submit" class="px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800 text-sm font-bold flex items-center">
              <i class="fas fa-upload mr-1"></i>Upload
            </button>
          </div>
        </form>
      </div>
    </div>
<div id="trashModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[80vh] flex flex-col">
    <div class="flex justify-between items-center p-4 border-b">
      <h2 class="text-lg font-semibold">Trashed Files</h2>
      <button onclick="closeTrashModal()" class="text-gray-500 hover:text-red-600 text-xl font-bold">&times;</button>
    </div>
  

    <div class="overflow-y-auto flex-1">
      <table class="min-w-full divide-y divide-teal-100 mb-2">
        <thead class="bg-teal-50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Name</th>
            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Type</th>
            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Deleted At</th>
            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-teal-700">Size</th>
            <th class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider text-teal-700">Restore</th>
            <th class="px-4 py-2 text-center text-xs font-medium uppercase tracking-wider text-teal-700">Delete</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-teal-100">
          <?php
            $conn = new mysqli("localhost", "root", "", "succlogin");
            $upload_dir = "uploads/";

           if (!$conn->connect_error) {
  $search = $_GET['search'] ?? '';
  $search = $conn->real_escape_string($search);

  if ($search !== '') {
    $result = $conn->query("SELECT * FROM trash WHERE filename LIKE '%$search%' ORDER BY deleted_at DESC");
  } else {
    $result = $conn->query("SELECT * FROM trash ORDER BY deleted_at DESC");
  }

  while ($row = $result->fetch_assoc()) {

                echo "<tr>";
                echo "<td class='px-4 py-2'>" . htmlspecialchars($row['filename']) . "</td>";
                echo "<td class='px-4 py-2'>" . strtoupper(htmlspecialchars($row['filetype'])) . "</td>";
                echo "<td class='px-4 py-2'>" . htmlspecialchars($row['deleted_at']) . "</td>";
                echo "<td class='px-4 py-2'>" . round($row['filesize'] / 1024, 2) . " KB</td>";
                echo "<td class='px-4 py-2 text-center'>
                        <form method='POST'>
                          <input type='hidden' name='restore_id' value='" . $row['id'] . "'>
                          <button type='submit' class='bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm'>Restore</button>
                        </form>
                      </td>";
                echo "<td class='px-4 py-2 text-center'>
                        <form method='POST'>
                          <input type='hidden' name='delete_perm_id' value='" . $row['id'] . "'>
                          <button type='submit' class='bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm'>Delete</button>
                        </form>
                      </td>";
                echo "</tr>";
              }
              if ($result->num_rows === 0) {
  echo "<tr><td colspan='6' class='px-4 py-2 text-center text-gray-500'>No files found.</td></tr>";
}

            }
          ?>
        </tbody>
      </table>
    </div>
    <div class="flex justify-end gap-2 p-4 border-t">
      <button onclick="closeTrashModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm font-bold">Cancel</button>
      <form method="POST">
        <input type="hidden" name="delete_all_trash" value="1">
        <button type="submit" onclick="return confirm('Delete all permanently?')" class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 text-sm font-bold">Delete All Permanently</button>
      </form>
    </div>
  </div>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $conn = new mysqli("localhost", "root", "", "succlogin");
  $upload_dir = "uploads/";

  if (!$conn->connect_error) {
    if (isset($_POST['restore_id'])) {
      $id = intval($_POST['restore_id']);
      $res = $conn->query("SELECT * FROM trash WHERE id = $id");
      if ($res && $row = $res->fetch_assoc()) {
        $stmt = $conn->prepare("INSERT INTO uploaded_files (filename, filetype, filesize) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $row['filename'], $row['filetype'], $row['filesize']);
        $stmt->execute();
        $conn->query("DELETE FROM trash WHERE id = $id");
      }
    }

    if (isset($_POST['delete_perm_id'])) {
      $id = intval($_POST['delete_perm_id']);
      $res = $conn->query("SELECT filename FROM trash WHERE id = $id");
      if ($res && $row = $res->fetch_assoc()) {
        $filepath = $upload_dir . $row['filename'];
        if (file_exists($filepath)) unlink($filepath);
      }
      $conn->query("DELETE FROM trash WHERE id = $id");
    }

    if (isset($_POST['delete_all_trash'])) {
      $res = $conn->query("SELECT filename FROM trash");
      while ($res && $row = $res->fetch_assoc()) {
        $filepath = $upload_dir . $row['filename'];
        if (file_exists($filepath)) unlink($filepath);
      }
      $conn->query("DELETE FROM trash");
    }

    $conn->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  }
}
?>


    <div id="uploadNotification" class="fixed top-4 right-4 z-50 hidden px-4 py-2 bg-green-600 text-white rounded shadow"></div>

    <script>
document.getElementById('select-all').addEventListener('change', function () {
  const checkboxes = document.querySelectorAll('.file-checkbox');
  checkboxes.forEach(cb => cb.checked = this.checked);
      });

    
// Show modal when Upload button is clicked
document.getElementById('upload-btn').addEventListener('click', function() {
  document.getElementById('uploadModal').classList.remove('hidden');
});

// Hide modal function
function closeUploadModal() {
  document.getElementById('uploadModal').classList.add('hidden');
}

// Modal upload: AJAX submit, close modal and reload page to show new file
document.getElementById('modalUploadForm').addEventListener('submit', function(e) {
  e.preventDefault();
  var form = e.target;
  var formData = new FormData(form);

  fetch('', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then((text) => {
    closeUploadModal();
    // Show notification if upload was successful
    if (text.includes('✅')) {
      showUploadNotification('File uploaded successfully!');
      setTimeout(() => window.location.reload(), 1200);
    } else {
      showUploadNotification('❌ ' + text, true);
    }
  })
  .catch(() => {
    showUploadNotification('❌ Error uploading file.', true);
  });
});

// Notification function
function showUploadNotification(msg, isError) {
  var el = document.getElementById('uploadNotification');
  el.textContent = msg;
  el.className = 'fixed top-4 right-4 z-50 px-4 py-2 rounded shadow ' + (isError ? 'bg-red-600' : 'bg-green-600') + ' text-white';
  el.style.display = 'block';
  el.classList.remove('hidden');
  setTimeout(() => {
    el.classList.add('hidden');
    el.style.display = 'none';
  }, 2000);
}

// Download, Approve, Delete functions
function downloadFile(filename) {
  window.location.href = 'uploads/' + filename;
}

function approveFile(id) {
  // AJAX request to approve the file
  fetch('approve.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: id })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showUploadNotification('File approved successfully!');
      setTimeout(() => window.location.reload(), 1200);
    } else {
      showUploadNotification('❌ ' + data.message, true);
    }
  })
  .catch(() => {
    showUploadNotification('❌ Error approving file.', true);
  });
}

function deleteFile(id, filename) {
  if (confirm('Are you sure you want to delete this file?')) {
    // AJAX request to delete the file
    fetch('delete.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: id, filename: filename })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showUploadNotification('File deleted successfully!');
        setTimeout(() => window.location.reload(), 1200);
      } else {
        showUploadNotification('❌ ' + data.message, true);
      }
    })
    .catch(() => {
      showUploadNotification('❌ Error deleting file.', true);
    });
  }
}

// Trash modal functions
function openTrashModal() {
  document.getElementById('trashModal').classList.remove('hidden');
  // Fetch trashed files via AJAX
  fetch('trash_list.php')
    .then(response => response.text())
    .then(html => {
      document.getElementById('trash-list').innerHTML = html;
    });
}

function closeTrashModal() {
  document.getElementById('trashModal').classList.add('hidden');
}

// Open Trash modal when Trash link is clicked
document.getElementById('sidebar-trash-link').addEventListener('click', function(e) {
  e.preventDefault();
  openTrashModal();
});

function restoreFile(id) {
  fetch('restore.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showUploadNotification('File restored successfully!');
      openTrashModal(); // reload trash list
      setTimeout(() => window.location.reload(), 1200);
    } else {
      showUploadNotification('❌ ' + data.message, true);
    }
  })
  .catch(() => {
    showUploadNotification('❌ Error restoring file.', true);
    showUploadNotification('❌ Error restoring file.', true);
  });
}

function deleteTrashFile(id, filename) {
  if (confirm('Permanently delete "' + filename + '"? This cannot be undone.')) {
    fetch('delete_trash.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showUploadNotification('File permanently deleted!');
        openTrashModal(); // reload trash list
      } else {
        showUploadNotification('❌ ' + data.message, true);
      }
    })
    .catch(() => {
      showUploadNotification('❌ Error deleting file.', true);
    });
  }
}

function deleteAllTrash() {
  if (confirm('Permanently delete ALL trashed files? This cannot be undone.')) {
    fetch('delete_trash.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ delete_all: true })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showUploadNotification('All trashed files permanently deleted!');
        openTrashModal(); // reload trash list
      } else {
        showUploadNotification('❌ ' + data.message, true);
      }
    })
    .catch(() => {
      showUploadNotification('❌ Error deleting files.', true);
    });
  }
}

// Download selected files as ZIP
function submitDownload() {
  const checked = Array.from(document.querySelectorAll('input[name="file_ids[]"]:checked')).map(cb => cb.value);
  if (checked.length === 0) {
    alert('Please select at least one file to download.');
    return;
  }
  // Create a hidden form and submit selected IDs
  let form = document.getElementById('download-form');
  // Remove any previous hidden inputs
  form.querySelectorAll('input[name="file_ids[]"]').forEach(e => e.remove());
  // Add selected file_ids[]
  checked.forEach(id => {
    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'file_ids[]';
    input.value = id;
    form.appendChild(input);
  });
  form.submit();
  // Remove the added inputs after submit
  setTimeout(() => {
    form.querySelectorAll('input[name="file_ids[]"]').forEach(e => e.remove());
  }, 1000);
}

// Handle Delete Selected button in header
document.getElementById('delete-selected-form').addEventListener('submit', function(e) {
  // Collect checked file IDs from the table
  const checked = Array.from(document.querySelectorAll('input[name="file_ids[]"]:checked')).map(cb => cb.value);
  if (checked.length === 0) {
    alert('Please select at least one file to delete.');
    e.preventDefault();
    return false;
  }
  // Set hidden input value(s)
  const idsInput = document.getElementById('delete-selected-ids');
  // Remove previous values
  while (idsInput.nextSibling) idsInput.parentNode.removeChild(idsInput.nextSibling);
  // Add hidden inputs for each selected file
  checked.forEach((id, idx) => {
    if (idx === 0) {
      idsInput.value = id;
    } else {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'file_ids[]';
      input.value = id;
      idsInput.parentNode.appendChild(input);
    }
  });
});

// Handle Download Selected button in header
document.getElementById('download-selected-form').addEventListener('submit', function(e) {
  // Collect checked file IDs from the table
  const checked = Array.from(document.querySelectorAll('input[name="file_ids[]"]:checked')).map(cb => cb.value);
  if (checked.length === 0) {
    alert('Please select at least one file to download.');
    e.preventDefault();
    return false;
  }
  // Set hidden input value(s)
  const idsInput = document.getElementById('download-selected-ids');
  // Remove previous values
  while (idsInput.nextSibling) idsInput.parentNode.removeChild(idsInput.nextSibling);
  // Add hidden inputs for each selected file
  checked.forEach((id, idx) => {
    if (idx === 0) {
      idsInput.value = id;
    } else {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'file_ids[]';
      input.value = id;
      idsInput.parentNode.appendChild(input);
    }
  });
});

function submitDownload() {
    const selected = Array.from(document.querySelectorAll('.file-checkbox:checked'))
      .map(cb => cb.value);
    if (selected.length === 0) {
      alert("Please select at least one file to download.");
      return;
    }
    const hiddenInput = document.getElementById('download-file-ids');
    hiddenInput.name = "file_ids[]"; // Ensure PHP recognizes it as an array
    hiddenInput.value = ""; // Clear existing
    // Create hidden inputs dynamically
    const form = document.getElementById('download-form');
    // Clear any previous hidden inputs
    const existing = form.querySelectorAll('input[name="file_ids[]"]:not(#download-file-ids)');
    existing.forEach(e => e.remove());
    selected.forEach(id => {
      const input = document.createElement('input');
      input.type = "hidden";
      input.name = "file_ids[]";
      input.value = id;
      form.appendChild(input);
    });
    form.submit();
  }
  </script>
</body>
</html>