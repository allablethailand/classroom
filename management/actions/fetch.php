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

function uploadFile($file, $name, $currentFile, $key = 0) {
    global $base_path;
    // Set target directory for uploads relative to the document root
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . $base_path . "/uploads/classroom/";
    // Ensure the directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $new_file_path = $currentFile;
    
    // Check if a new file was uploaded
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
            if ($currentFile && file_exists($_SERVER['DOCUMENT_ROOT'] . $currentFile) && !strpos($currentFile, 'default')) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $currentFile);
            }
            
            if (move_uploaded_file($tmp_name, $target_file)) {
                $new_file_path = $base_path . "/uploads/classroom/" . $new_file_name;
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

    // Use LEFT JOIN to get classroom_id from the join table
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
        // Decode JSON for attached documents and add full path
        if (isset($data[$type . '_attach_document'])) {
            $docs = json_decode($data[$type . '_attach_document'], true);
            if(is_array($docs)) {
                $data[$type . '_attach_document'] = array_map('GetUrl', $docs);
            }
        }
        // Add full path for image fields using GetUrl()
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
    
    // Check for required data
    if ($comp_id === null || $classroom_id === null) {
        return ['status' => 'error', 'message' => 'Required data (comp_id or classroom_id) not found.'];
    }

    $mysqli->begin_transaction();

    try {
        // Handle file uploads
        $image_profile = uploadFile($_FILES, $type . '_image_profile', isset($post[$type . '_image_profile_current']) ? $post[$type . '_image_profile_current'] : '');
        $card_front = uploadFile($_FILES, $type . '_card_front', isset($post[$type . '_card_front_current']) ? $post[$type . '_card_front_current'] : '');
        $card_back = uploadFile($_FILES, $type . '_card_back', isset($post[$type . '_card_back_current']) ? $post[$type . '_card_back_current'] : '');

        // Handle attached documents
        $attached_docs = [];
        if (isset($_FILES[$type . '_attach_document']['tmp_name']) && is_array($_FILES[$type . '_attach_document']['tmp_name'])) {
            foreach ($_FILES[$type . '_attach_document']['tmp_name'] as $key => $tmp_name) {
                if ($tmp_name) {
                    $new_file_name = uploadFile($_FILES, $type . '_attach_document', '', $key);
                    if ($new_file_name) {
                        $attached_docs[] = $new_file_name;
                    }
                }
            }
        }

        // Merge existing and new documents
        if (isset($post[$type . '_attach_document_current'])) {
            $existing_docs = json_decode($post[$type . '_attach_document_current'], true);
            if (is_array($existing_docs)) {
                $attached_docs = array_merge($attached_docs, $existing_docs);
            }
        }
        $attached_docs_json = json_encode(array_values(array_filter($attached_docs)));
        
        // Prepare data for insertion/update
        $data = [
            $type . '_perfix' => isset($post[$type . '_perfix']) ? $post[$type . '_perfix'] : null,
            $type . '_firstname_th' => isset($post[$type . '_firstname_th']) ? $post[$type . '_firstname_th'] : null,
            $type . '_lastname_th' => isset($post[$type . '_lastname_th']) ? $post[$type . '_lastname_th'] : null,
            $type . '_firstname_en' => isset($post[$type . '_firstname_en']) ? $post[$type . '_firstname_en'] : null,
            $type . '_lastname_en' => isset($post[$type . '_lastname_en']) ? $post[$type . '_lastname_en'] : null,
            $type . '_nickname_th' => isset($post[$type . '_nickname_th']) ? $post[$type . '_nickname_th'] : null,
            $type . '_nickname_en' => isset($post[$type . '_nickname_en']) ? $post[$type . '_nickname_en'] : null,
            $type . '_gender' => isset($post[$type . '_gender']) ? $post[$type . '_gender'] : null,
            $type . '_idcard' => isset($post[$type . '_idcard']) ? $post[$type . '_idcard'] : null,
            $type . '_passport' => isset($post[$type . '_passport']) ? $post[$type . '_passport'] : null,
            $type . '_birth_date' => isset($post[$type . '_birth_date']) ? $post[$type . '_birth_date'] : null,
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
            $type . '_attach_document' => $attached_docs_json,
        ];
        // Add specific fields for teachers
        if ($type === 'teacher') {
            $data['position_id'] = isset($post['position_id']) ? $post['position_id'] : null;
            $data['teacher_ref_type'] = isset($post['teacher_ref_type']) ? $post['teacher_ref_type'] : null;
            $data['teacher_company'] = isset($post['teacher_company']) ? $post['teacher_company'] : null;
            $data['teacher_position'] = isset($post['teacher_position']) ? $post['teacher_position'] : null;
        }

        // Handle password encryption
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

            // Check if join record exists
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

            if ($check_result->num_rows > 0) {
                // Update existing join record
                $update_join_sql = "UPDATE `$join_table` SET `comp_id` = ?, `status` = 0, `emp_modify` = ?, `date_modify` = NOW() WHERE `$join_id_col` = ? AND `classroom_id` = ?";
                $update_join_stmt = $mysqli->prepare($update_join_sql);
                if (!$update_join_stmt) {
                    throw new Exception("Update join prepare statement failed: " . $mysqli->error);
                }
                $update_join_stmt->bind_param('siss', $comp_id, $emp_id, $id, $classroom_id);
                $update_join_stmt->execute();
            } else {
                // Insert new join record
                $insert_join_sql = "INSERT INTO `$join_table` (`classroom_id`, `$join_id_col`, `comp_id`, `status`, `emp_create`, `date_create`) VALUES (?, ?, ?, 0, ?, NOW())";
                $insert_join_stmt = $mysqli->prepare($insert_join_sql);
                if (!$insert_join_stmt) {
                    throw new Exception("Insert join prepare statement failed: " . $mysqli->error);
                }
                $insert_join_stmt->bind_param('sisi', $classroom_id, $id, $comp_id, $emp_id);
                $insert_join_stmt->execute();
            }

        } else {
            // **INSERT**
            $data['comp_id'] = $comp_id;
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
            
            // Insert into join table
            $join_table = "classroom_" . $type . "_join";
            $join_id_col = $type . "_id";
            $insert_join_sql = "INSERT INTO `$join_table` (`classroom_id`, `$join_id_col`, `comp_id`, `status`, `emp_create`, `date_create`) VALUES (?, ?, ?, 0, ?, NOW())";
            $insert_join_stmt = $mysqli->prepare($insert_join_sql);
            if (!$insert_join_stmt) {
                throw new Exception("Insert join prepare statement failed: " . $mysqli->error);
            }
            $insert_join_stmt->bind_param('sisi', $classroom_id, $new_id, $comp_id, $emp_id);
            $insert_join_stmt->execute();
        }

        $mysqli->commit();
        return ['status' => 'success', 'message' => 'Data saved successfully.'];

    } catch (Exception $e) {
        $mysqli->rollback();
        return ['status' => 'error', 'message' => $e->getMessage()];
    } finally {
        $mysqli->close();
    }
}
?>