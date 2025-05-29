<?php
function logAction($conn, $userId, $username, $action) {
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("INSERT INTO audit_logs (user, action, ip_address, timestamp, user_id) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("sssi", $username, $action, $ip, $userId);
    $stmt->execute();
    $stmt->close();
}
?>
