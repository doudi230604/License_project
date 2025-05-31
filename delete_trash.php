<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$conn = new mysqli("localhost", "root", "", "succlogin");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}
$upload_dir = "uploads/";

if (isset($data['delete_all']) && $data['delete_all']) {
    // Delete all files in trash
    $res = $conn->query("SELECT filename FROM trash");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $filepath = $upload_dir . $row['filename'];
            if (file_exists($filepath)) unlink($filepath);
        }
    }
    $conn->query("DELETE FROM trash");
    echo json_encode(['success' => true]);
    $conn->close();
    exit;
}

if (isset($data['id'])) {
    $id = intval($data['id']);
    $res = $conn->query("SELECT filename FROM trash WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        $filepath = $upload_dir . $row['filename'];
        if (file_exists($filepath)) unlink($filepath);
        $conn->query("DELETE FROM trash WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'File not found in trash.']);
    }
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'No file specified.']);
$conn->close();
?>