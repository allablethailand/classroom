<?php
// myphoto_process.php
header('Content-Type: application/json');
require_once("../../lib/connect_sqli.php");
// ... (รวมไฟล์อื่นๆ ที่จำเป็น) ...
global $mysqli;

// สมมติว่า student_id มาจาก session
$student_id = $_SESSION['student_id'] ?? ($_GET['student_id'] ?? null); 

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID ไม่ถูกต้อง']);
    exit;
}

// ถ้าใช้ XAMPP (Windows): C:/xampp/htdocs/
$document_root = "/var/www/html/"; // <-- ***ตั้งค่านี้ให้ถูกต้อง***
$upload_dir = "uploads/"; // โฟลเดอร์ uploads ตาม Path ใน DB

// ฟังก์ชันแปลง Path DB เป็น Full Server Path สำหรับ Python
function get_full_server_path($db_path, $root_dir) {
    // $db_path คือ uploads/classroom/68ccd6420f18f.png
    // $root_dir คือ /var/www/html/
    // ผลลัพธ์: /var/www/html/uploads/classroom/68ccd6420f18f.png (ที่ Python อ่านได้)
    return rtrim($root_dir, '/') . '/' . ltrim($db_path, '/'); 
}

// **ต้องสร้างฟังก์ชันนี้เพื่อจัดการ Base64 (ถ้ามี)**
function save_base64_image_temporarily($base64_data, $student_id, $index) {
    // **ตัวอย่าง Logic: ต้องนำไปใช้งานจริง**
    // 1. ตรวจสอบ Base64 format
    // 2. decode base64
    // 3. บันทึกใน temp folder (เช่น /tmp/face_temp/{student_id}_{index}.jpg)
    // 4. คืนค่า Full Server Path ของไฟล์ที่บันทึก
    return false; // **ควรเปลี่ยนเป็น Path จริง**
}

// ----------------------------------------------------
// 1. ดึงรูปโปรไฟล์ (reference) 
// ----------------------------------------------------
$ref_db_paths = [];
$ref_server_paths = []; // Path สำหรับ Python

// A. ดึง student_image_profile
// ... (โค้ดดึง student_image_profile) ...
// **ถ้า student_image_profile เป็น Path**
// $ref_db_paths[] = $row['student_image_profile'];
// **ถ้า student_image_profile เป็น Base64**
// $temp_path = save_base64_image_temporarily($row['student_image_profile'], $student_id, 1);
// if ($temp_path) $ref_server_paths[] = $temp_path;


// B. ดึงรูปโปรไฟล์อื่นๆ จากตาราง classroom_student_company_photo (max 4 รูป, is_deleted != 1)
$sql_photos = "SELECT `file_path` FROM `classroom_student_company_photo` WHERE `student_id` = ? AND `is_deleted` != 1 LIMIT 4";
// ... (โค้ดดึงและเติม $ref_db_paths) ...
while ($row_photo = $result_photos->fetch_assoc()) {
    $ref_db_paths[] = $row_photo['file_path'];
}

// แปลง DB Path เป็น Server Path
foreach ($ref_db_paths as $path) {
    $ref_server_paths[] = get_full_server_path($path, $document_root);
}

if (empty($ref_server_paths)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบรูปโปรไฟล์ของผู้ใช้']);
    exit;
}

// ----------------------------------------------------
// 2. ดึงรูปกลุ่มทั้งหมดจากตาราง photo_album_group
// ----------------------------------------------------
$group_db_paths = [];
$group_server_paths = []; // Path สำหรับ Python
// ดึงรูปกลุ่มทั้งหมดที่ยังไม่ถูกลบ
$sql_groups = "SELECT `group_photo_path` FROM `photo_album_group` WHERE `is_deleted` = 0";
$result_groups = $mysqli->query($sql_groups); // ใช้ $mysqli ที่ต่อ DB แล้ว

while ($row_group = $result_groups->fetch_assoc()) {
    // เก็บ path ในรูปแบบ DB เพื่อส่งกลับไปแสดงผล
    $group_db_paths[] = $row_group['group_photo_path'];
    // แปลงเป็น Server Path สำหรับ Python
    $group_server_paths[] = get_full_server_path($row_group['group_photo_path'], $document_root);
}

if (empty($group_server_paths)) {
    echo json_encode(['status' => 'success', 'images' => [], 'message' => 'ไม่พบรูปภาพกลุ่ม']);
    exit;
}

// ----------------------------------------------------
// 3. เรียกใช้ Python Script (ใช้โค้ด myphoto.php ที่คุณให้มาแล้วเปลี่ยนชื่อ)
// ----------------------------------------------------

$python_script = 'myphoto.py'; // ใช้ชื่อไฟล์ Python ที่คุณให้มา
$python_interpreter = '/usr/bin/python3'; 
$temp_dir = sys_get_temp_dir() . '/face_temp/'; 
mkdir($temp_dir, 0777, true);

$data_for_python = [
    'ref_paths' => $ref_server_paths, // ส่ง Server Path
    'group_paths' => $group_server_paths, // ส่ง Server Path
    'output_dir' => $temp_dir,
    'student_id' => $student_id
];
$json_data_arg = escapeshellarg(json_encode($data_for_python));

// คำสั่งเรียก Python
$command = "{$python_interpreter} {$python_script} {$json_data_arg}";

// รันคำสั่ง
$output = shell_exec($command);

// ----------------------------------------------------
// 4. ประมวลผลผลลัพธ์จาก Python และส่งกลับ
// ----------------------------------------------------
$python_result = json_decode($output, true);

if (json_last_error() === JSON_ERROR_NONE && $python_result && isset($python_result['status']) && $python_result['status'] === 'success') {
    $found_server_paths = $python_result['found_images']; 
    $result_db_paths = [];

    // **แปลง Server Path กลับเป็น DB Path** สำหรับส่งให้หน้า myphoto.php แสดงผล
    foreach ($found_server_paths as $server_path) {
        $db_path = str_replace($document_root, '', $server_path);
        $result_db_paths[] = $db_path;

        // **ขั้นตอนสำคัญ: บันทึกผลลัพธ์ลงใน photo_face_detection**
        // คุณควรทำ:
        // 1. ดึง group_photo_id จากตาราง photo_album_group โดยใช้ $db_path
        // 2. ตรวจสอบว่ามีข้อมูลใน photo_face_detection สำหรับ student_id และ group_photo_id นี้หรือไม่
        // 3. ถ้าไม่มี -> INSERT
    }

    // ล้างไฟล์รูปโปรไฟล์ชั่วคราว (ถ้ามีการสร้าง)
    foreach ($ref_server_paths as $path) {
        // ... (โค้ดล้างไฟล์ชั่วคราว) ...
    }

    echo json_encode(['status' => 'success', 'images' => $result_db_paths]); // ส่ง DB Path กลับไป
} else {
    // ... (โค้ดจัดการ Error และล้างไฟล์) ...
    echo json_encode([
        'status' => 'error',
        'message' => 'Python script failed.',
        'debug_output' => $output 
    ]);
}
?>