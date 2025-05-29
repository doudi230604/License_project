<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'No file specified.']);
    exit;
}
$id = intval($data['id']);
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}
$res = $conn->query("SELECT filename, filetype, filesize FROM trash WHERE id = $id");
if ($res && $row = $res->fetch_assoc()) {
    // Insert back to uploaded_files
    $stmt = $conn->prepare("INSERT INTO uploaded_files (filename, filetype, filesize) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $row['filename'], $row['filetype'], $row['filesize']);
    $stmt->execute();
    $stmt->close();
    // Remove from trash
    $conn->query("DELETE FROM trash WHERE id = $id");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'File not found in trash.']);
}
$conn->close();
?>