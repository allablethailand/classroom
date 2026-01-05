<?php
// myphoto_process.php (เปลี่ยนเป็นการดึงจากตาราง detech แทนการรัน Python และดึง Description)

header('Content-Type: application/json');

// ------------------------------------------------------------------------------------------------------
// *** 1. นำเข้าโค้ด _config.php/ไฟล์ตั้งค่า ***
// ------------------------------------------------------------------------------------------------------
date_default_timezone_set('Asia/Bangkok');
session_start();
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if($_SERVER['HTTP_HOST'] == 'localhost'){
       $request_uri = $_SERVER['REQUEST_URI'];
       $exl_path = explode('/',$request_uri);
       if(!file_exists($base_include."/dashboard.php")){
           $base_path .= "/".$exl_path[1];
       }
       $base_include .= "/".$exl_path[1];
    }
    DEFINE('base_path', $base_path);
    DEFINE('base_include', $base_include);
	require_once($base_include."/lib/connect_sqli.php");
	require_once($base_include."/actions/func.php");
	require_once($base_include."/classroom/study/actions/student_func.php");

    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);
global $mysqli;

$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : ($_GET['student_id'] ? $_GET['student_id'] : null); 

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID ไม่ถูกต้อง']);
    exit;
}

// ----------------------------------------------------
// ✅ แก้ไข: เพิ่ม t1.detection_date และปรับ ORDER BY 
// ----------------------------------------------------
$result_data = [];
$sql = "SELECT 
            t2.`group_photo_path`, 
            t2.`description`, 
            t2.`date_create`,
            t1.`detection_date` 
        FROM `classroom_photo_face_detection` t1
        JOIN `classroom_photo_album_group` t2 ON t1.group_photo_id = t2.group_photo_id
        WHERE t1.`student_id` = ? 
        ORDER BY t2.date_create DESC, t1.detection_date DESC";
        
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // ✅ NEW: ตรวจสอบและกำหนดฟังก์ชัน GetUrl
    $use_get_url = function_exists('GetUrl');
    
    while ($row = $result->fetch_assoc()) {
        $path = $row['group_photo_path'];
        
        // ✅ NEW: เรียก GetUrl() เพื่อแปลง Path ใน DB เป็น Full URL
        if ($use_get_url) {
            $path = GetUrl($path);
        }
        
        $result_data[] = [
            'path' => $path, // <== ตอนนี้ 'path' คือ Full URL แล้ว
            'description' => $row['description'], 
            'date_create' => $row['date_create'], 
            'detection_date' => $row['detection_date']
        ];
    }
    $stmt->close();
}


if (!empty($result_data)) {
    // ส่งข้อมูลที่มีทั้ง Full URL และ Description กลับไปให้ JS
    echo json_encode(['status' => 'success', 'data' => $result_data, 'message' => 'ดึงรูปภาพที่ตรวจจับไว้ล่วงหน้าสำเร็จ']); 
} else {
    echo json_encode(['status' => 'success', 'data' => [], 'message' => 'ไม่พบรูปภาพที่คุณถูกตรวจจับ']);
}

// NEW FILE UPLOAD



?>