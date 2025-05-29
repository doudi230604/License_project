<?php

include 'functions.php';
$upload_dir = "uploads/";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["filename"])) {
    $filename = basename($_POST["filename"]);
    $filepath = $upload_dir . $filename;

    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            echo "✅ File deleted.";
             logAction($conn, $id, $username, "Deleted file");
        } else {
            http_response_code(500);
            echo "❌ Error deleting file.";
        }
    } else {
        http_response_code(404);
        echo "❌ File not found.";
    }
} else {
    http_response_code(400);
    echo "❌ Invalid request.";
}
