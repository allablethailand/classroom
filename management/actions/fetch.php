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
        default:
            $response = ['status' => 'error', 'message' => 'Unknown action.'];
            break;
    }
}

echo json_encode($response);
exit();

// ✨ เพิ่มฟังก์ชันใหม่สำหรับดึง Path ออกจาก URL
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

// ✨ ฟังก์ชันใหม่สำหรับทำให้ Path สะอาด (ไม่มี / นำหน้า)
function cleanPath($path) {
    return ltrim($path, '/');
}

function uploadFile($file, $name, $currentFile, $key = 0) {
    global $base_path;
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . $base_path . "/uploads/classroom/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // ตรวจสอบว่า $currentFile เป็น URL เต็มหรือไม่ ถ้าใช่ให้แปลงกลับมาเป็น path
    $currentFile = extractPathFromUrl($currentFile);
    
    $new_file_path = $currentFile;
    
    if (isset($file[$name]['tmp_name'])) {
        // Handle single file upload
        if (!is_array($file[$name]['tmp_name'])) {
            $tmp_name = $file[$name]['tmp_name'];
            $file_name = $file[$name]['name'];
            $file_error = $file[$name]['error'];
        } else {
            // Handle multi-file upload by key
            $tmp_name = $file[$name]['tmp_name'][$key];
            $file_name = $file[$name]['name'][$key];
            $file_error = $file[$name]['error'][$key];
        }

        if ($tmp_name && $file_error == UPLOAD_ERR_OK) {
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            // Delete old file if it exists and is not a default/system path
            // Note: We need to add the leading slash back to check if the file exists on the server's file system
            if ($currentFile && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $currentFile) && !strpos($currentFile, 'default')) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $currentFile);
            }
            
            if (move_uploaded_file($tmp_name, $target_file)) {
                // บันทึก path โดยไม่มี / นำหน้า
                $new_file_path = cleanPath("uploads/classroom/" . $new_file_name);
            } else {
                return null; // Handle upload error
            }
        }
    }
    return $new_file_path;
}

function fetchData($type, $id) {
    global $mysqli;
    $table = "classroom_" . $type;
    $id_col = $type . "_id";

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
        
        $prefix_map = ['1' => 'นาย', '2' => 'นาง', '3' => 'นางสาว', '4' => 'ด.ช.', '5' => 'ด.ญ.'];
        $data[$type . '_perfix_text'] = isset($prefix_map[$data[$type . '_perfix']]) ? $prefix_map[$data[$type . '_perfix']] : $data[$type . '_perfix'];
        
        $gender_map = ['M' => 'ชาย', 'F' => 'หญิง'];
        $data[$type . '_gender_text'] = isset($gender_map[$data[$type . '_gender']]) ? $gender_map[$data[$type . '_gender']] : $data[$type . '_gender'];

        // Decode JSON for attached documents and add full path
        if (isset($data[$type . '_attach_document']) && !empty($data[$type . '_attach_document'])) {
            $docs_str = $data[$type . '_attach_document'];
            $docs_arr = explode('|', $docs_str);
            
            // Now convert each relative path to a full URL
            $full_paths = array_map('GetUrl', $docs_arr); 
            
            // Re-join the array into a string with '|' for JS to split easily
            $data[$type . '_attach_document'] = implode('|', array_filter($full_paths));
        }

        // Add full path for other image fields
        if (isset($data[$type . '_image_profile'])) {
            $data[$type . '_image_profile'] = GetUrl($data[$type . '_image_profile']);
        }
        if (isset($data[$type . '_card_front'])) {
            $data[$type . '_card_front'] = GetUrl($data[$type . '_card_front']);
        }
        if (isset($data[$type . '_card_back'])) {
            $data[$type . '_card_back'] = GetUrl($data[$type . '_card_back']);
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
    $classroom_id = isset($post['classroom_id']) ? $post['classroom_id'] : null;
    $comp_id = isset($_SESSION['comp_id']) ? $_SESSION['comp_id'] : null;
    $emp_id = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 1;
    
    if ($comp_id === null || $classroom_id === null) {
        return ['status' => 'error', 'message' => 'Required data (comp_id or classroom_id) not found.'];
    }

    $mysqli->begin_transaction();

    try {
        $current_image_profile = isset($post[$type . '_image_profile_current']) ? extractPathFromUrl($post[$type . '_image_profile_current']) : '';
        $current_card_front = isset($post[$type . '_card_front_current']) ? extractPathFromUrl($post[$type . '_card_front_current']) : '';
        $current_card_back = isset($post[$type . '_card_back_current']) ? extractPathFromUrl($post[$type . '_card_back_current']) : '';
        
        $image_profile = uploadFile($_FILES, $type . '_image_profile', $current_image_profile);
        $card_front = uploadFile($_FILES, $type . '_card_front', $current_card_front);
        $card_back = uploadFile($_FILES, $type . '_card_back', $current_card_back);

        // Handle attached documents
        $attached_docs = [];

        // 1. Add new uploaded documents
        if (isset($_FILES[$type . '_attach_document']['tmp_name']) && is_array($_FILES[$type . '_attach_document']['tmp_name'])) {
            foreach ($_FILES[$type . '_attach_document']['tmp_name'] as $key => $tmp_name) {
                if ($tmp_name) {
                    // Your upload function should handle this correctly
                    $new_file_path = uploadFile($_FILES, $type . '_attach_document', '', $key);
                    if ($new_file_path) {
                        $attached_docs[] = $new_file_path;
                    }
                }
            }
        }

        // 2. Add existing documents that were not removed
        if (isset($post[$type . '_attach_document_current']) && is_array($post[$type . '_attach_document_current'])) {
            foreach ($post[$type . '_attach_document_current'] as $doc_url) {
                if (!empty(trim($doc_url))) {
                    // You need a function to convert the full URL back to the relative path
                    $path_to_save = extractPathFromUrl($doc_url); 
                    if (!empty($path_to_save)) {
                        $attached_docs[] = $path_to_save;
                    }
                }
            }
        }

        // 3. Combine and save to database
        // Use array_unique to remove duplicates (if any)
        $attached_docs_unique = array_unique(array_filter($attached_docs));
        $attached_docs_str = implode('|', $attached_docs_unique);

        $gender_for_db = isset($post[$type . '_gender']) ? $post[$type . '_gender'] : 'N'; // รับค่า M, F, N ที่แปลงมาจาก JS แล้ว

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
            
            // ✨ แก้ไขใหม่: ตรวจสอบและแปลง format วันที่
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
            $type . '_image_profile' => $image_profile,
            $type . '_card_front' => $card_front,
            $type . '_card_back' => $card_back,
            $type . '_attach_document' => $attached_docs_str,
            $type . '_company' => isset($post[$type . '_company']) ? $post[$type . '_company'] : null,
            $type . '_position' => isset($post[$type . '_position']) ? $post[$type . '_position'] : null,
            $type . '_hobby' => isset($post[$type . '_hobby']) ? $post[$type . '_hobby'] : null,
            $type . '_music' => isset($post[$type . '_music']) ? $post[$type . '_music'] : null,
            $type . '_movie' => isset($post[$type . '_movie']) ? $post[$type . '_movie'] : null,
            $type . '_goal' => isset($post[$type . '_goal']) ? $post[$type . '_goal'] : null,
            $type . '_religion' => isset($post[$type . '_religion']) ? $post[$type . '_religion'] : null,
            $type . '_bloodgroup' => isset($post[$type . '_bloodgroup']) ? $post[$type . '_bloodgroup'] : null,
        ];

        if ($type === 'teacher') {
            $data['position_id'] = isset($post['position_id']) ? $post['position_id'] : null;
            $data['teacher_ref_type'] = isset($post['teacher_ref_type']) ? $post['teacher_ref_type'] : null;
            $data['teacher_company'] = isset($post['teacher_company']) ? $post['teacher_company'] : null;
            $data['teacher_position'] = isset($post['teacher_position']) ? $post['teacher_position'] : null;
        }

        if (isset($post[$type . '_password']) && !empty($post[$type . '_password'])) {
            $plain_password = $post[$type . '_password'];
            $password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $data[$type . '_password'] = encryptToken($plain_password, $password_key);
            $data[$type . '_password_key'] = $password_key;
        }

        if ($id) {
            // **UPDATE**
            $set_clause = array_map(function($key) { return "`$key` = ?"; }, array_keys($data));
            $set_clause[] = "`emp_modify` = ?";
            $set_clause[] = "`date_modify` = NOW()";
            $sql = "UPDATE `$table` SET " . implode(', ', $set_clause) . " WHERE `$id_col` = ?";
            $params = array_values($data);
            $params[] = $emp_id;
            $params[] = $id;
            $types = str_repeat('s', count($data)) . 'si';

            $stmt_update = $mysqli->prepare($sql);
            if (!$stmt_update) {
                throw new Exception("Update prepare statement failed: " . $mysqli->error);
            }
            $stmt_update->bind_param($types, ...$params);
            $stmt_update->execute();

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
                // สำหรับ Teacher ให้ update comp_id
                if ($type === 'teacher') {
                    $update_join_sql = "UPDATE `$join_table` SET `comp_id` = ?, `status` = 0, `emp_modify` = ?, `date_modify` = NOW() WHERE `$join_id_col` = ? AND `classroom_id` = ?";
                    $params_join = [$comp_id, $emp_id, $id, $classroom_id];
                    $types_join = 'siss';
                } else {
                    // สำหรับ Student ไม่ต้อง update comp_id
                    $update_join_sql = "UPDATE `$join_table` SET `status` = 0, `emp_modify` = ?, `date_modify` = NOW() WHERE `$join_id_col` = ? AND `classroom_id` = ?";
                    $params_join = [$emp_id, $id, $classroom_id];
                    $types_join = 'sis';
                }
                 if ($type === 'student') {
                     $update_join_sql .= ", `approve_status` = 1, `approve_by` = ?, `approve_date` = ?";
                     $params_join = [$emp_id, $current_datetime, $id, $classroom_id];
                     $types_join = 'sissis';
                }
                
                $update_join_stmt = $mysqli->prepare($update_join_sql);
                if (!$update_join_stmt) {
                    throw new Exception("Update join prepare statement failed: " . $mysqli->error);
                }
                $update_join_stmt->bind_param($types_join, ...$params_join);
                $update_join_stmt->execute();
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
                    $join_types .= "sisisisi";
                }

                $insert_join_sql .= ") VALUES ($join_placeholders)";
                $insert_join_stmt = $mysqli->prepare($insert_join_sql);
                if (!$insert_join_stmt) {
                    throw new Exception("Insert join prepare statement failed: " . $mysqli->error);
                }
                $insert_join_stmt->bind_param($join_types, ...$join_params);
                $insert_join_stmt->execute();
            }

        } else {
            // **INSERT**
            
            // เพิ่ม comp_id สำหรับ teacher เท่านั้น
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
            $types = str_repeat('s', count($data));
            $values = array_values($data);
            $stmt_insert->bind_param($types, ...$values);
            $stmt_insert->execute();

            $new_id = $mysqli->insert_id;
            if ($new_id === 0) {
                throw new Exception("Failed to get last inserted ID.");
            }
            
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
                $join_types .= "sisisisi";
            }

            $insert_join_sql .= ") VALUES ($join_placeholders)";
            $insert_join_stmt = $mysqli->prepare($insert_join_sql);
            if (!$insert_join_stmt) {
                throw new Exception("Insert join prepare statement failed: " . $mysqli->error);
            }
            $insert_join_stmt->bind_param($join_types, ...$join_params);
            $insert_join_stmt->execute();
        }

        $mysqli->commit();
        return ['status' => 'success', 'message' => 'Data saved successfully.', 'id' => $id];

    } catch (Exception $e) {
        $mysqli->rollback();
        return ['status' => 'error', 'message' => $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}
?>