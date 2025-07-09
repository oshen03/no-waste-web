<?php
session_start();

header('Content-Type: application/json');


$_SESSION = array();


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


setcookie('username', '', time() - 3600, '/');
setcookie('email', '', time() - 3600, '/');


session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>