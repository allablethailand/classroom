<?php
// --- PHP CODE (actions/fetch.php) ---

session_start();
date_default_timezone_set('Asia/Bangkok');
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
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include.'/lib/connect_sqli.php';
require_once $base_include.'/lib/config.php';
global $mysqli;
$fsData = getBucketMaster();
$filesystem_user = $fsData['fs_access_user'];
$filesystem_pass = $fsData['fs_access_pass'];
$filesystem_host = $fsData['fs_host'];
$filesystem_path = $fsData['fs_access_path'];
$filesystem_type = $fsData['fs_type'];
$fs_id = $fsData['fs_id'];
setBucket($fsData);
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid action.'];

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'fetchData':
            $response = fetchData($_POST['type'], $_POST['id']);
            break;
        case 'saveData':
            $response = saveData($_POST);
            break;
        case 'deleteFile':
            $response = deleteFile($_POST['type'], $_POST['file_id']);
            break;
        default:
            $response = ['status' => 'error', 'message' => 'Unknown action.'];
            break;
    }
}

echo json_encode($response);
exit();

function extractPathFromUrl($url) {
    if (strpos($url, '://') === false) {
        return cleanPath($url);
    }
    $parsed_url = parse_url($url);
    if (isset($parsed_url['path'])) {
        $path = $parsed_url['path'];
        $path = strtok($path, '?');
        return cleanPath($path);
    }
    return '';
}

function cleanPath($path) {
    return ltrim($path, '/');
}

function uploadFile($file, $name, $currentFile = '', $key = null) {
    global $base_path;
    
    // --- โค้ดเดิม: ไม่ได้ใช้ $target_dir สำหรับ move_uploaded_file อีกต่อไป แต่เก็บไว้สำหรับกำหนด path
    $target_dir = "uploads/classroom/"; 
    
    if (!isset($file[$name]['tmp_name']) || (is_array($file[$name]['tmp_name']) && !isset($file[$name]['tmp_name'][$key])) || (!is_array($file[$name]['tmp_name']) && empty($file[$name]['tmp_name']))) {
        return extractPathFromUrl($currentFile);
    }
    
    if ($key !== null) {
        $tmp_name = $file[$name]['tmp_name'][$key];
        $file_name = $file[$name]['name'][$key];
        $file_error = $file[$name]['error'][$key];
    } else {
        $tmp_name = $file[$name]['tmp_name'];
        $file_name = $file[$name]['name'];
        $file_error = $file[$name]['error'];
    }

    if ($tmp_name && $file_error == UPLOAD_ERR_OK) {
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_id = uniqid(); // ใช้ ID แทนชื่อไฟล์
        $new_file_name = $new_file_id . '.' . $file_extension;
        
        // 1. กำหนด Path สำหรับไฟล์จริงและไฟล์ Thumbnail (ตามรูปแบบที่ต้องการ)
        $new_file_path = $target_dir . $new_file_name;
        $thumb_file_path = $target_dir . $new_file_id . '_thumb.' . $file_extension;
        
        // 2. ลบไฟล์เก่า (ยังคงไว้)
        $currentPath = extractPathFromUrl($currentFile);
        if ($currentPath && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $currentPath) && !strpos($currentPath, 'default')) {
            // โค้ดนี้อาจต้องเปลี่ยนเป็นการเรียกฟังก์ชันลบไฟล์เฉพาะทาง หากไฟล์เก่าถูกเก็บในที่อื่นที่ไม่ใช่ระบบไฟล์ปกติ
            // unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $currentPath); 
            // หากต้องการลบไฟล์เก่าจริง ๆ ควรตรวจสอบว่าไฟล์ thumbnail เก่าก็ถูกลบด้วย
        }

        // 3. ✨ เริ่มกระบวนการ SaveFile และสร้าง Thumbnail (ตามที่ร้องขอ)
        
        // **คำเตือน:** ฟังก์ชัน SaveFile() และ createThumbnail() จะต้องถูกกำหนดไว้
        // มิฉะนั้นโค้ดส่วนนี้จะเกิด Fatal Error
        
        if (function_exists('SaveFile') && function_exists('createThumbnail')) {
            // A. Save ไฟล์ต้นฉบับ
            if (SaveFile($tmp_name, $new_file_path)) {
                
                // B. สร้าง Thumbnail ใน Temp Folder ก่อน
                $thumb_local = sys_get_temp_dir() . '/' . uniqid('thumb_') . '.' . $file_extension;
                
                if (createThumbnail($tmp_name, $thumb_local, 300, 300, 80)) {
                    // C. Save ไฟล์ Thumbnail
                    if (SaveFile($thumb_local, $thumb_file_path)) {
                        unlink($thumb_local);
                        // D. คืนค่าเป็น Path ของไฟล์จริง (ไม่รวม Thumbnail)
                        return cleanPath($new_file_path);
                    } else {
                        // ไม่สามารถ Save Thumbnail ได้ แต่ไฟล์จริงถูก Save แล้ว
                        // อาจจะต้องพิจารณาว่าจะลบไฟล์จริงหรือไม่
                        return cleanPath($new_file_path); 
                    }
                } else {
                    // ไม่สามารถสร้าง Thumbnail ได้, ต้องส่ง Error กลับไป (ตามตัวอย่างที่ให้มา)
                    // **NOTE:** ฟังก์ชัน uploadFile ควรจะคืนค่า Path หรือ null เท่านั้น การ echo JSON จะทำให้โค้ดหลักพัง
                    // ดังนั้นควรพิจารณาแก้ไขกระบวนการจัดการ Error ในฟังก์ชัน saveData
                    return null; // หรือ Path ของไฟล์จริงถ้าต้องการเก็บไฟล์จริงไว้
                }
            } else {
                // ไม่สามารถ Save ไฟล์ต้นฉบับได้ (ตามตัวอย่างที่ให้มา)
                return null;
            }
        } 
    }
    return extractPathFromUrl($currentFile);
}

function fetchData($type, $id) {
    global $mysqli;
    $table = "classroom_" . $type;
    $id_col = $type . "_id";
    $file_table = "classroom_file_" . $type;
    $company_photo_table = "classroom_" . $type . "_company_photo"; // ตารางรูปภาพบริษัท
    $join_table = "classroom_" . $type . "_join";
    
    $sql = "SELECT t.*, j.classroom_id FROM `$table` AS t LEFT JOIN `$join_table` AS j ON t.`$id_col` = j.`$id_col` WHERE t.`$id_col` = ? LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['status' => 'error', 'message' => "Prepare statement failed: " . $mysqli->error];
    }
    
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // --- ดึงรูปโปรไฟล์ทั้งหมดจากตารางใหม่ ---
        $profile_sql = "SELECT `file_id`, `file_path`, `file_order`, `file_status` FROM `$file_table` WHERE `$id_col` = ? AND `file_type` = 'profile_image' AND `is_deleted` = 0 ORDER BY `file_order` ASC, `file_status` DESC";
        $profile_stmt = $mysqli->prepare($profile_sql);
        $data['all_profile_images'] = [];
        if ($profile_stmt) {
            $profile_stmt->bind_param("s", $id);
            $profile_stmt->execute();
            $profile_result = $profile_stmt->get_result();
            while ($row = $profile_result->fetch_assoc()) {
                $row['file_path'] = GetUrl($row['file_path']);
                $data['all_profile_images'][] = $row;
            }
        }
        
        // --- ดึงไฟล์แนบจากตารางใหม่ทั้งหมด ---
        $attach_sql = "SELECT `file_id`, `file_path` FROM `$file_table` WHERE `$id_col` = ? AND `file_type` = 'attached_document' AND `is_deleted` = 0 ORDER BY `date_create` DESC";
        $attach_stmt = $mysqli->prepare($attach_sql);
        $data['attached_documents'] = []; 
        if ($attach_stmt) {
            $attach_stmt->bind_param("s", $id);
            $attach_stmt->execute();
            $attach_result = $attach_stmt->get_result();
            while ($row = $attach_result->fetch_assoc()) {
                $data['attached_documents'][] = [
                    'file_id' => $row['file_id'],
                    'file_path' => GetUrl($row['file_path'])
                ];
            }
        }

        // --- เพิ่มส่วนดึงรูปภาพบริษัทใหม่ ---
        $company_photo_sql = "SELECT `file_id`, `file_path` FROM `$company_photo_table` WHERE `" . $type . "_id` = ? AND `is_deleted` = 0 ORDER BY `file_id` ASC";
        $company_photo_stmt = $mysqli->prepare($company_photo_sql);
        $data['company_photos'] = [];
        if ($company_photo_stmt) {
            $company_photo_stmt->bind_param("s", $id);
            $company_photo_stmt->execute();
            $company_photo_result = $company_photo_stmt->get_result();
            while ($row = $company_photo_result->fetch_assoc()) {
                $data['company_photos'][] = [
                    'file_id' => $row['file_id'],
                    'file_path' => GetUrl($row['file_path'])
                ];
            }
        }
        
        // Add full path for other image fields
        if (isset($data[$type . '_card_front'])) {
            $data[$type . '_card_front'] = GetUrl($data[$type . '_card_front']);
        }
        if (isset($data[$type . '_card_back'])) {
            $data[$type . '_card_back'] = GetUrl($data[$type . '_card_back']);
        }
        if (isset($data[$type . '_company_logo'])) {
            $data[$type . '_company_logo'] = GetUrl($data[$type . '_company_logo']);
        }
        
        return ['status' => 'success', 'data' => $data];
    } else {
        return ['status' => 'error', 'message' => 'Data not found.'];
    }
}

function saveData($post) {
    global $mysqli;
    $type = $post['type'];
    $id = isset($post['id']) && $post['id'] != '' ? $post['id'] : null;
    $table = "classroom_" . $type;
    $id_col = $type . "_id";
    $file_table = "classroom_file_" . $type;
    $company_photo_table = "classroom_" . $type . "_company_photo"; // ตารางรูปภาพบริษัท
    $classroom_id = isset($post['classroom_id']) ? $post['classroom_id'] : null;
    $comp_id = isset($_SESSION['comp_id']) ? $_SESSION['comp_id'] : null;
    $emp_id = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 1;
    
    if ($comp_id === null || $classroom_id === null) {
        return ['status' => 'error', 'message' => 'Required data (comp_id or classroom_id) not found.'];
    }

    $mysqli->begin_transaction();

    try {
        // Handle card front and back uploads
        $current_card_front = isset($post[$type . '_card_front_current']) ? extractPathFromUrl($post[$type . '_card_front_current']) : '';
        $current_card_back = isset($post[$type . '_card_back_current']) ? extractPathFromUrl($post[$type . '_card_back_current']) : '';
        $current_company_logo = isset($post[$type . '_company_logo_current']) ? extractPathFromUrl($post[$type . '_company_logo_current']) : '';
        
        // 🚨 ส่วนที่แก้ไข: ตรวจสอบและจัดการการตั้งค่า NULL สำหรับบัตรประชาชน
        // ถ้ามีการอัปโหลดไฟล์ใหม่ จะใช้ค่าจาก uploadFile()
        // ถ้าไม่มีการอัปโหลดไฟล์ใหม่ แต่มีค่า current เดิม จะใช้ค่า current เดิม
        // ถ้าไม่มีทั้งไฟล์ใหม่และค่า current (หมายถึงถูกลบ) ให้เป็น NULL

        $card_front_uploaded = isset($_FILES[$type . '_card_front']) && $_FILES[$type . '_card_front']['error'] === 0;
        $card_back_uploaded = isset($_FILES[$type . '_card_back']) && $_FILES[$type . '_card_back']['error'] === 0;

        // 1. จัดการรูปด้านหน้า
        if ($card_front_uploaded) {
            // มีการอัปโหลดไฟล์ใหม่
            $card_front = uploadFile($_FILES, $type . '_card_front', $current_card_front);
        } else if (!empty($current_card_front) && isset($post[$type . '_card_front_current'])) {
            // ไม่มีไฟล์ใหม่ แต่มีค่า current_card_front เดิม (ไม่ได้ถูกลบ)
            $card_front = $current_card_front;
        } else {
            // ไม่มีไฟล์ใหม่ และไม่มีค่า current_card_front (ถูกลบโดยฟังก์ชัน removeImage บนหน้าเว็บ)
            $card_front = null; // ⭐ ตั้งค่าเป็น NULL เพื่อบันทึกใน DB
        }

        // 2. จัดการรูปด้านหลัง
        if ($card_back_uploaded) {
            // มีการอัปโหลดไฟล์ใหม่
            $card_back = uploadFile($_FILES, $type . '_card_back', $current_card_back);
        } else if (!empty($current_card_back) && isset($post[$type . '_card_back_current'])) {
            // ไม่มีไฟล์ใหม่ แต่มีค่า current_card_back เดิม (ไม่ได้ถูกลบ)
            $card_back = $current_card_back;
        } else {
            // ไม่มีไฟล์ใหม่ และไม่มีค่า current_card_back (ถูกลบโดยฟังก์ชัน removeImage บนหน้าเว็บ)
            $card_back = null; // ⭐ ตั้งค่าเป็น NULL เพื่อบันทึกใน DB
        }
        
        // จัดการ Company Logo (ทำแบบเดียวกันหากต้องการให้สามารถลบ Company Logo ได้)
        $company_logo_uploaded = isset($_FILES[$type . '_company_logo']) && $_FILES[$type . '_company_logo']['error'] === 0;
        if ($company_logo_uploaded) {
             $company_logo = uploadFile($_FILES, $type . '_company_logo', $current_company_logo);
        } else if (!empty($current_company_logo) && isset($post[$type . '_company_logo_current'])) {
            $company_logo = $current_company_logo;
        } else {
            $company_logo = null;
        }

        $gender_for_db = isset($post[$type . '_gender']) ? $post[$type . '_gender'] : 'N';
        
        $data = [
            $type . '_perfix' => isset($post[$type . '_perfix']) ? $post[$type . '_perfix'] : null,
            $type . '_firstname_th' => isset($post[$type . '_firstname_th']) ? $post[$type . '_firstname_th'] : null,
            $type . '_lastname_th' => isset($post[$type . '_lastname_th']) ? $post[$type . '_lastname_th'] : null,
            $type . '_firstname_en' => isset($post[$type . '_firstname_en']) ? $post[$type . '_firstname_en'] : null,
            $type . '_lastname_en' => isset($post[$type . '_lastname_en']) ? $post[$type . '_lastname_en'] : null,
            $type . '_nickname_th' => isset($post[$type . '_nickname_th']) ? $post[$type . '_nickname_th'] : null,
            $type . '_nickname_en' => isset($post[$type . '_nickname_en']) ? $post[$type . '_nickname_en'] : null,
            $type . '_gender' => $gender_for_db,
            $type . '_idcard' => isset($post[$type . '_idcard']) ? $post[$type . '_idcard'] : null,
            $type . '_passport' => isset($post[$type . '_passport']) ? $post[$type . '_passport'] : null,
            
            $type . '_birth_date' => isset($post[$type . '_birth_date']) && !empty($post[$type . '_birth_date']) ? date('Y-m-d', strtotime($post[$type . '_birth_date'])) : null,
            
            $type . '_email' => isset($post[$type . '_email']) ? $post[$type . '_email'] : null,
            $type . '_mobile' => isset($post[$type . '_mobile']) ? $post[$type . '_mobile'] : null,
            $type . '_facebook' => isset($post[$type . '_facebook']) ? $post[$type . '_facebook'] : null,
            $type . '_line' => isset($post[$type . '_line']) ? $post[$type . '_line'] : null,
            $type . '_ig' => isset($post[$type . '_ig']) ? $post[$type . '_ig'] : null,
            $type . '_address' => isset($post[$type . '_address']) ? $post[$type . '_address'] : null,
            $type . '_bio' => isset($post[$type . '_bio']) ? $post[$type . '_bio'] : null,
            $type . '_education' => isset($post[$type . '_education']) ? $post[$type . '_education'] : null,
            $type . '_experience' => isset($post[$type . '_experience']) ? $post[$type . '_experience'] : null,
            $type . '_username' => isset($post[$type . '_username']) ? $post[$type . '_username'] : null,
            $type . '_card_front' => $card_front,
            $type . '_card_back' => $card_back,
            $type . '_company' => isset($post[$type . '_company']) ? $post[$type . '_company'] : null,
            $type . '_position' => isset($post[$type . '_position']) ? $post[$type . '_position'] : null,
            $type . '_hobby' => isset($post[$type . '_hobby']) ? $post[$type . '_hobby'] : null,
            $type . '_music' => isset($post[$type . '_music']) ? $post[$type . '_music'] : null,
            $type . '_drink' => isset($post[$type . '_drink']) ? $post[$type . '_drink'] : null,
            $type . '_movie' => isset($post[$type . '_movie']) ? $post[$type . '_movie'] : null,
            $type . '_goal' => isset($post[$type . '_goal']) ? $post[$type . '_goal'] : null,
            // $type . '_religion' => isset($post[$type . '_religion']) ? $post[$type . '_religion'] : null,
            // $type . '_bloodgroup' => isset($post[$type . '_bloodgroup']) ? $post[$type . '_bloodgroup'] : null,
            $type . '_allergy' => isset($post[$type . '_allergy']) ? $post[$type . '_allergy'] : null,
            
            // --- เพิ่มคอลัมน์ใหม่สำหรับข้อมูลบริษัท ---
            $type . '_company_detail' => isset($post[$type . '_company_detail']) ? $post[$type . '_company_detail'] : null,
            $type . '_company_url' => isset($post[$type . '_company_url']) ? $post[$type . '_company_url'] : null,
            $type . '_company_logo' => $company_logo,
        ];
        
        if ($type === 'teacher') {
            $data['position_id'] = isset($post['position_id']) ? $post['position_id'] : null;
            $data['teacher_ref_type'] = isset($post['teacher_ref_type']) ? $post['teacher_ref_type'] : null;
            $data['teacher_company'] = isset($post['teacher_company']) ? $post['teacher_company'] : null;
            $data['teacher_position'] = isset($post['teacher_position']) ? $post['teacher_position'] : null;
        }

        // **ส่วนที่เพิ่มเข้ามาเพื่อตรวจสอบ Username และ Password **
        $username_input = isset($post[$type . '_username']) ? $post[$type . '_username'] : null;
        $plain_password = isset($post[$type . '_password']) ? $post[$type . '_password'] : null;
        $confirm_password = isset($post[$type . '_confirm_password']) ? $post[$type . '_confirm_password'] : null;

        // **Step 1: ตรวจสอบ username ห้ามซ้ำ**
        if ($username_input) {
            $check_username_sql = "SELECT `".$id_col."` FROM `".$table."` WHERE `".$type."_username` = ? AND `".$id_col."` != ?";
            $check_username_stmt = $mysqli->prepare($check_username_sql);
            if (!$check_username_stmt) {
                throw new Exception("Check username prepare statement failed: " . $mysqli->error);
            }
            // ใช้ "s" สำหรับ string เสมอสำหรับ username และใช้ "i" หรือ "s" สำหรับ id ตามประเภทข้อมูลจริง (สมมติว่าเป็น i - integer)
            $check_username_stmt->bind_param('si', $username_input, $id); 
            $check_username_stmt->execute();
            $check_username_result = $check_username_stmt->get_result();

            if ($check_username_result->num_rows > 0) {
                return ['status' => 'error', 'message' => 'Username already exists. Please choose a different one.'];
            }
        }
        
        // **Step 2: ตรวจสอบ Password ที่กรอก 2 รอบ**
        // ถ้ามีการกรอกรหัสผ่านใหม่
        if (isset($post[$type . '_password']) && !empty($post[$type . '_password'])) {
            if ($plain_password !== $confirm_password) {
                return ['status' => 'error', 'message' => 'Passwords do not match. Please try again.'];
            }
            
            $password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $data[$type . '_password'] = encryptToken($plain_password, $password_key);
            $data[$type . '_password_key'] = $password_key;
        }


        $new_id = $id;

        if ($id) {
            // **UPDATE**
            $set_clause = array_map(function($key) { return "`$key` = ?"; }, array_keys($data));
            $set_clause[] = "`emp_modify` = ?";
            $set_clause[] = "`date_modify` = NOW()";
            $sql = "UPDATE `$table` SET " . implode(', ', $set_clause) . " WHERE `$id_col` = ?";
            $params = array_values($data);
            $params[] = $emp_id;
            $params[] = $id;
            // ใช้ "s" สำหรับ string (ข้อมูลใน $data) และ 'ii' สำหรับ emp_modify (int) และ id (int) - สมมติ emp_id, id เป็น int
            $types = str_repeat('s', count($data)) . 'ii'; 

            $stmt_update = $mysqli->prepare($sql);
            if (!$stmt_update) {
                throw new Exception("Update prepare statement failed: " . $mysqli->error);
            }
            
            // ใช้ call_user_func_array เพื่อส่ง parameter ให้ bind_param ได้อย่างถูกต้อง
            $bind_params = array_merge([$types], $params);
            $refs = [];
            foreach ($bind_params as $key => $value) {
                $refs[$key] = &$bind_params[$key];
            }
            if (!call_user_func_array([$stmt_update, 'bind_param'], $refs)) {
                throw new Exception("Update bind_param failed: " . $stmt_update->error);
            }
            
            if (!$stmt_update->execute()) {
                throw new Exception("Update execution failed: " . $stmt_update->error);
            }
            
            // --- จัดการตาราง Join ---
            $join_table = "classroom_" . $type . "_join";
            $join_id_col = $type . "_id";
            $check_sql = "SELECT * FROM `$join_table` WHERE `$join_id_col` = ? AND `classroom_id` = ?";
            $check_stmt = $mysqli->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Join check prepare statement failed: " . $mysqli->error);
            }
            $check_stmt->bind_param('is', $id, $classroom_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $current_datetime = date('Y-m-d H:i:s');
            
            if ($check_result->num_rows > 0) {
                if ($type === 'teacher') {
                    $update_join_sql = "UPDATE `$join_table` SET `comp_id` = ?, `status` = 0, `emp_modify` = ?, `date_modify` = NOW() WHERE `$join_id_col` = ? AND `classroom_id` = ?";
                    $params_join = [$comp_id, $emp_id, $id, $classroom_id];
                    $types_join = 'siis';
                } else {
                    $update_join_sql = "UPDATE `$join_table` SET `status` = 0, `emp_modify` = ?, `date_modify` = NOW() WHERE `$join_id_col` = ? AND `classroom_id` = ?";
                    $params_join = [$emp_id, $id, $classroom_id];
                    $types_join = 'iis';
                }
                $update_join_stmt = $mysqli->prepare($update_join_sql);
                if (!$update_join_stmt) {
                    throw new Exception("Update join prepare statement failed: " . $mysqli->error);
                }
                
                $bind_join_params = array_merge([$types_join], $params_join);
                $refs_join = [];
                foreach ($bind_join_params as $key => $value) {
                    $refs_join[$key] = &$bind_join_params[$key];
                }
                if (!call_user_func_array([$update_join_stmt, 'bind_param'], $refs_join)) {
                    throw new Exception("Update join bind_param failed: " . $update_join_stmt->error);
                }
                
                if (!$update_join_stmt->execute()) {
                    throw new Exception("Update join execution failed: " . $update_join_stmt->error);
                }
                
            } else {
                $insert_join_sql = "INSERT INTO `$join_table` (`classroom_id`, `$join_id_col`, `comp_id`, `status`, `emp_create`, `date_create`";
                $join_placeholders = "?, ?, ?, 0, ?, NOW()";
                $join_params = [$classroom_id, $id, $comp_id, $emp_id];
                $join_types = "sisi";

                if ($type === 'student') {
                    $current_datetime = date('Y-m-d H:i:s');
                    $insert_join_sql .= ", `register_date`, `register_by_emp`, `invite_date`, `approve_date`, `approve_by`, `payment_status`, `payment_status_by`, `payment_status_date`";
                    $join_placeholders .= ", ?, ?, ?, ?, ?, ?, ?, ?";
                    $join_params = array_merge($join_params, [$current_datetime, $emp_id, $current_datetime, $current_datetime, $emp_id, 1, $emp_id, $current_datetime]);
                    $join_types .= "sisissis";
                }

                $insert_join_sql .= ") VALUES ($join_placeholders)";
                $insert_join_stmt = $mysqli->prepare($insert_join_sql);
                if (!$insert_join_stmt) {
                    throw new Exception("Insert join prepare statement failed: " . $mysqli->error);
                }
                
                $bind_insert_join_params = array_merge([$join_types], $join_params);
                $refs_insert_join = [];
                foreach ($bind_insert_join_params as $key => $value) {
                    $refs_insert_join[$key] = &$bind_insert_join_params[$key];
                }
                if (!call_user_func_array([$insert_join_stmt, 'bind_param'], $refs_insert_join)) {
                    throw new Exception("Insert join bind_param failed: " . $insert_join_stmt->error);
                }
                
                if (!$insert_join_stmt->execute()) {
                    throw new Exception("Insert join execution failed: " . $insert_join_stmt->error);
                }
            }

        } else {
            // **INSERT**
            if ($type === 'teacher') {
                $data['comp_id'] = $comp_id;
            }
            $data['emp_create'] = $emp_id;
            $data['date_create'] = date('Y-m-d H:i:s');
            
            $fields = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), '?'));
            $sql = "INSERT INTO `$table` ($fields) VALUES ($placeholders)";
            
            $stmt_insert = $mysqli->prepare($sql);
            if (!$stmt_insert) {
                throw new Exception("Insert prepare statement failed: " . $mysqli->error);
            }
            
            $types = str_repeat('s', count($data)); // ใช้ 's' เป็น default แต่ต้องระมัดระวังประเภทข้อมูลจริง
            $values = array_values($data);
            
            // --- แก้ไข: การใช้ bind_param กับ ...$values ต้องใช้วิธีเรียกผ่าน array reference
            $bind_insert_params = array_merge([$types], $values);
            $refs_insert = [];
            foreach ($bind_insert_params as $key => $value) {
                $refs_insert[$key] = &$bind_insert_params[$key];
            }
            if (!call_user_func_array([$stmt_insert, 'bind_param'], $refs_insert)) {
                throw new Exception("Insert bind_param failed: " . $stmt_insert->error);
            }
            
            // --- แก้ไข: ตรวจสอบการ execute
            if (!$stmt_insert->execute()) {
                throw new Exception("Insert execution failed: " . $stmt_insert->error);
            }

            // ⭐ การแก้ไขสำหรับ Error: Failed to get last inserted ID. (MySQLi Error: )
            $new_id = $mysqli->insert_id;
            if (!$new_id) { // ตรวจสอบว่า $new_id เป็น 0 หรือ false หรือไม่
                // เพิ่ม $mysqli->error เข้าไปในข้อความ Error
                throw new Exception("Failed to get last inserted ID. (MySQLi Error: " . $mysqli->error . ")"); 
            }
            
            // --- จัดการตาราง Join ---
            $join_table = "classroom_" . $type . "_join";
            $join_id_col = $type . "_id";
            $insert_join_sql = "INSERT INTO `$join_table` (`classroom_id`, `$join_id_col`, `comp_id`, `status`, `emp_create`, `date_create`";
            $join_placeholders = "?, ?, ?, 0, ?, NOW()";
            $join_params = [$classroom_id, $new_id, $comp_id, $emp_id];
            $join_types = "sisi";

            if ($type === 'student') {
                $current_datetime = date('Y-m-d H:i:s');
                $insert_join_sql .= ", `register_date`, `register_by_emp`, `invite_date`, `approve_date`, `approve_by`, `payment_status`, `payment_status_by`, `payment_status_date`";
                $join_placeholders .= ", ?, ?, ?, ?, ?, ?, ?, ?";
                $join_params = array_merge($join_params, [$current_datetime, $emp_id, $current_datetime, $current_datetime, $emp_id, 1, $emp_id, $current_datetime]);
                $join_types .= "sisissis";
            }

            $insert_join_sql .= ") VALUES ($join_placeholders)";
            $insert_join_stmt = $mysqli->prepare($insert_join_sql);
            if (!$insert_join_stmt) {
                throw new Exception("Insert join prepare statement failed: " . $mysqli->error);
            }
            
            $bind_insert_join_params = array_merge([$join_types], $join_params);
            $refs_insert_join = [];
            foreach ($bind_insert_join_params as $key => $value) {
                $refs_insert_join[$key] = &$bind_insert_join_params[$key];
            }
            if (!call_user_func_array([$insert_join_stmt, 'bind_param'], $refs_insert_join)) {
                throw new Exception("Insert join bind_param failed: " . $insert_join_stmt->error);
            }
            
            if (!$insert_join_stmt->execute()) {
                throw new Exception("Insert join execution failed: " . $insert_join_stmt->error);
            }
        }

        // 1. จัดการรูปโปรไฟล์
        $profile_file_name = $type . '_image_profile';
        if (isset($_FILES[$profile_file_name]) && $_FILES[$profile_file_name]['tmp_name']) {
            $current_file_path = isset($post[$profile_file_name . '_current']) ? $post[$profile_file_name . '_current'] : '';
            
            $delete_old_profile_sql = "UPDATE `$file_table` SET `is_deleted` = 1, `emp_modify` = ?, `date_modify` = NOW() WHERE `$id_col` = ? AND `file_type` = 'profile_image' AND `is_deleted` = 0";
            $delete_old_stmt = $mysqli->prepare($delete_old_profile_sql);
            if (!$delete_old_stmt) {
                 throw new Exception("Delete old profile file prepare statement failed: " . $mysqli->error);
            }
            $delete_old_stmt->bind_param("ii", $emp_id, $new_id); // สมมติว่า emp_id และ new_id เป็น integer
            $delete_old_stmt->execute();
            
            $file_path = uploadFile($_FILES, $profile_file_name, $current_file_path);
            if ($file_path) {
                $insert_file_sql = "INSERT INTO `$file_table` (`$id_col`, `file_path`, `file_type`, `file_status`, `file_order`, `emp_create`, `date_create`) VALUES (?, ?, 'profile_image', 1, 1, ?, NOW())";
                $insert_file_stmt = $mysqli->prepare($insert_file_sql);
                if (!$insert_file_stmt) {
                    throw new Exception("Insert profile file prepare statement failed: " . $mysqli->error);
                }
                $insert_file_stmt->bind_param("isi", $new_id, $file_path, $emp_id); // สมมติ new_id, emp_id เป็น integer
                $insert_file_stmt->execute();
            }
        }
        
        // 2. จัดการไฟล์แนบ
        if (isset($_FILES[$type . '_attach_document']['tmp_name']) && is_array($_FILES[$type . '_attach_document']['tmp_name'])) {
            foreach ($_FILES[$type . '_attach_document']['tmp_name'] as $key => $tmp_name) {
                if ($tmp_name) {
                    $file_path = uploadFile($_FILES, $type . '_attach_document', '', $key);
                    if ($file_path) {
                        $insert_file_sql = "INSERT INTO `$file_table` (`$id_col`, `file_path`, `file_type`, `file_status`, `emp_create`, `date_create`) VALUES (?, ?, 'attached_document', 0, ?, NOW())";
                        $insert_file_stmt = $mysqli->prepare($insert_file_sql);
                        if (!$insert_file_stmt) {
                            throw new Exception("Insert attached document prepare statement failed: " . $mysqli->error);
                        }
                        $insert_file_stmt->bind_param("isi", $new_id, $file_path, $emp_id); // สมมติ new_id, emp_id เป็น integer
                        $insert_file_stmt->execute();
                    }
                }
            }
        }

        // 3. เพิ่มการจัดการรูปภาพบริษัท
        // ตรวจสอบว่ามีไฟล์รูปภาพบริษัทใหม่ถูกอัปโหลดหรือไม่
        if (isset($_FILES[$type . '_company_photos']) && is_array($_FILES[$type . '_company_photos']['tmp_name'])) {
            // ลูปผ่านไฟล์ทั้งหมดที่ถูกอัปโหลด
            foreach ($_FILES[$type . '_company_photos']['tmp_name'] as $key => $tmp_name) {
                // ตรวจสอบว่ามีไฟล์ถูกเลือกจริง
                if ($tmp_name) {
                    $file_path = uploadFile($_FILES, $type . '_company_photos', '', $key);
                    if ($file_path) {
                        $insert_file_sql = "INSERT INTO `$company_photo_table` (`" . $type . "_id`, `file_path`, `emp_create`, `date_create`) VALUES (?, ?, ?, NOW())";
                        $insert_file_stmt = $mysqli->prepare($insert_file_sql);
                        if (!$insert_file_stmt) {
                            throw new Exception("Insert company photo prepare statement failed: " . $mysqli->error);
                        }
                        $insert_file_stmt->bind_param("isi", $new_id, $file_path, $emp_id); // สมมติ new_id, emp_id เป็น integer
                        $insert_file_stmt->execute();
                    }
                }
            }
        }
        
        $mysqli->commit();
        return ['status' => 'success', 'message' => 'Data saved successfully.', 'id' => $new_id];

    } catch (Exception $e) {
        $mysqli->rollback();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function deleteFile($type, $file_id) {
    global $mysqli;
    $file_table = "";
    
    // ✨ แก้ไขส่วนนี้: ระบุตารางให้ถูกต้องตามประเภทของไฟล์ที่ส่งมา
    if (isset($_POST['file_type']) && $_POST['file_type'] === 'company_photos') {
        $file_table = "classroom_" . $type . "_company_photo";
    } else {
        $file_table = "classroom_file_" . $type;
    }

    if (empty($file_table)) {
        return ['status' => 'error', 'message' => 'Invalid file type specified.'];
    }
    
    $emp_id = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 1;

    $mysqli->begin_transaction();
    try {
        // อัปเดต is_deleted = 1 และบันทึกวันที่/ผู้แก้ไข
        $sql = "UPDATE `$file_table` SET `is_deleted` = 1, `emp_modify` = ?, `date_modify` = NOW() WHERE `file_id` = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Delete file prepare statement failed: " . $mysqli->error);
        }
        $stmt->bind_param("ii", $emp_id, $file_id); // สมมติ emp_id และ file_id เป็น integer
        $stmt->execute();

        $mysqli->commit();
        return ['status' => 'success', 'message' => 'File deleted successfully.'];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
?>