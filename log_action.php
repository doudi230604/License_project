<?
function log_action($conn, $username, $action) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO audit_logs (user, action, ip_address, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $action, $ip);
    $stmt->execute();
    $stmt->close();
}
?>