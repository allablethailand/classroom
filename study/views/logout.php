<?php
// logout.php
session_start();

// ล้างค่า Session ทั้งหมด
$_SESSION = array();

// ลบ Session Cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย Session
session_destroy();

// Redirect ไปหน้า login หรือหน้าหลัก
header("Location: login");
exit();
?>