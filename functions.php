<?php
function logActivity($conn, $user, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $conn->prepare("INSERT INTO audit_logs (user, action, ip_address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $action, $ip);
    $stmt->execute();
}

?>
