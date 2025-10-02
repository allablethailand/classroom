<?php
// myphoto_process.php (เปลี่ยนเป็นการดึงจากตาราง detech แทนการรัน Python)

header('Content-Type: application/json');

// ------------------------------------------------------------------------------------------------------
// *** 1. นำเข้าโค้ด _config.php/ไฟล์ตั้งค่า ***
// ------------------------------------------------------------------------------------------------------
date_default_timezone_set('Asia/Bangkok');
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
$base_url = "http://" . $_SERVER['HTTP_HOST']; 

if ($_SERVER['HTTP_HOST'] == 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) { 
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    // ตรวจสอบว่าโปรเจกต์อยู่ใน Sub-directory หรือไม่
    if (isset($exl_path[1]) && !empty($exl_path[1]) && !file_exists($base_include . "/" . $exl_path[1] . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    // ปรับ $base_include ให้เป็น Path รากของโปรเจกต์ (เช่น C:\xampp_origami\htdocs\origami)
    $base_include = rtrim($_SERVER['DOCUMENT_ROOT'] . $base_path, '/');
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
// ต้องเปลี่ยน Path ให้ถูกต้องตามโครงสร้างโปรเจกต์ของคุณ
require_once(BASE_INCLUDE . "/lib/connect_sqli.php"); 
// ------------------------------------------------------------------------------------------------------

global $mysqli;

$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : ($_GET['student_id'] ? : null); 

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID ไม่ถูกต้อง']);
    exit;
}

// ----------------------------------------------------
// ✅ NEW: ดึงรูปภาพกลุ่มจากตาราง photo_face_detection ที่ตรวจจับไว้ล่วงหน้า
// ----------------------------------------------------
$result_db_paths = [];
$sql = "SELECT t2.`group_photo_path` 
        FROM `photo_face_detection` t1
        JOIN `photo_album_group` t2 ON t1.group_photo_id = t2.group_photo_id
        WHERE t1.`student_id` = ? 
        ORDER BY t2.date_create DESC"; // เรียงตามวันที่อัปโหลดล่าสุด
        
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $result_db_paths[] = $row['group_photo_path'];
    }
    $stmt->close();
}


if (!empty($result_db_paths)) {
    echo json_encode(['status' => 'success', 'images' => $result_db_paths, 'message' => 'ดึงรูปภาพที่ตรวจจับไว้ล่วงหน้าสำเร็จ']); 
} else {
    echo json_encode(['status' => 'success', 'images' => [], 'message' => 'ไม่พบรูปภาพที่คุณถูกตรวจจับ']);
}

// โค้ดส่วนที่เรียก Python ก่อนหน้านี้ถูกลบออกไปทั้งหมด
// myphoto_process.php นี้จะทำหน้าที่เป็น API ดึงผลลัพธ์ที่ Pre-processed ไว้เท่านั้น
?>