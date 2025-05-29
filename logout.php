<?php
session_start();

// Clear all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login page
header("Location: login2.php");
exit();
