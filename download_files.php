<?php

session_start();
// Connect to the database
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}






// Ensure file_ids were sent
if (!isset($_POST['file_ids']) || !is_array($_POST['file_ids']) || count($_POST['file_ids']) === 0) {
    die("No files selected.");
}

$file_ids = array_map('intval', $_POST['file_ids']);
$id_list = implode(',', $file_ids);

// Query to get file details
$sql = "SELECT * FROM uploaded_files WHERE id IN ($id_list)";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("No files found.");
}

// Create a ZIP file
$zip = new ZipArchive();
$zipName = 'download_' . time() . '.zip';
$tmpZipPath = sys_get_temp_dir() . '/' . $zipName;

if ($zip->open($tmpZipPath, ZipArchive::CREATE) !== true) {
    die("Failed to create ZIP archive.");
}

// Add files to ZIP
while ($row = $result->fetch_assoc()) {
    $filePath = 'uploads/' . $row['filename']; // Adjust this path to your actual upload directory
    if (file_exists($filePath)) {
        $zip->addFile($filePath, $row['filename']);
    }
}
$zip->close();

// Force download
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . basename($zipName));
header('Content-Length: ' . filesize($tmpZipPath));
readfile($tmpZipPath);

// Delete the temp file
unlink($tmpZipPath);

exit;
?>
