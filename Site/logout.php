<?php
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. Kill the session cookie itself (Best Practice)
// This ensures the browser forgets the session ID entirely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the server-side storage
session_destroy();

// 4. Redirect to the login gateway
// Adding a URL parameter can help you show a "Logged out successfully" message
header("Location: admin.php?logout=success");
exit;