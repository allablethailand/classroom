<?php
// --- PHP CODE (actions/fetch.php) ---

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
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . "/uploads/";
    $new_file_path = $currentFile;
    
    // Check if a new file was uploaded
    if (isset($file[$name]['tmp_name']) && !empty($file[$name]['tmp_name'])) {
        $tmp_name = is_array($file[$name]['tmp_name']) ? $file[$name]['tmp_name'][$key] : $file[$name]['tmp_name'];
        if ($tmp_name) {
            $file_extension = pathinfo($file[$name]['name'][$key] ? $file[$name]['name'][$key] : $file[$name]['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            // Delete old file if it exists and is a new upload
            if ($currentFile && file_exists($target_dir . basename($currentFile))) {
                unlink($target_dir . basename($currentFile));
            }
            
            if (move_uploaded_file($tmp_name, $target_file)) {
                $new_file_path = BASE_PATH . "/uploads/" . $new_file_name;
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

    $stmt = $mysqli->prepare("SELECT * FROM `$table` WHERE `$id_col` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        // Decode JSON for attached documents
        if (isset($data[$type . '_attach_document'])) {
            $data[$type . '_attach_document'] = json_decode($data[$type . '_attach_document'], true);
        }
        return ['status' => 'success', 'data' => $data];
    } else {
        return ['status' => 'error', 'message' => 'Data not found.'];
    }
}

function saveData($post) {
    global $mysqli;
    $type = $post['type'];
    $id = $post['id'] ? $post['id'] : null;
    $table = "classroom_" . $type;
    $id_col = $type . "_id";

    // Start a transaction to ensure all operations are successful
    $mysqli->begin_transaction();

    try {
        // Handle file uploads
        $image_profile = uploadFile($_FILES, $type . '_image_profile', $post[$type . '_image_profile_current'] ? $post[$type . '_image_profile_current'] : '');
        $card_front = uploadFile($_FILES, $type . '_card_front', $post[$type . '_card_front_current'] ? $post[$type . '_card_front_current'] : '');
        $card_back = uploadFile($_FILES, $type . '_card_back', $post[$type . '_card_back_current'] ? $post[$type . '_card_back_current'] : '');

        // Handle attached documents
        $attached_docs = [];
        if (isset($_FILES[$type . '_attach_document'])) {
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
            $attached_docs = array_merge($attached_docs, (array)$post[$type . '_attach_document_current']);
        }
        $attached_docs_json = json_encode(array_values(array_filter($attached_docs)));
        
        // Prepare data for insertion/update
        $data = [
            $type . '_perfix' => $post[$type . '_perfix'] ? $post[$type . '_perfix'] : null,
            $type . '_firstname_th' => $post[$type . '_firstname_th'] ? $post[$type . '_firstname_th'] : null,
            $type . '_lastname_th' => $post[$type . '_lastname_th'] ? $post[$type . '_lastname_th'] : null,
            $type . '_firstname_en' => $post[$type . '_firstname_en'] ? $post[$type . '_firstname_en'] : null,
            $type . '_lastname_en' => $post[$type . '_lastname_en'] ? $post[$type . '_lastname_en'] : null,
            $type . '_nickname_th' => $post[$type . '_nickname_th'] ? $post[$type . '_nickname_th'] : null,
            $type . '_nickname_en' => $post[$type . '_nickname_en'] ? $post[$type . '_nickname_en'] : null,
            $type . '_gender' => $post[$type . '_gender'] ? $post[$type . '_gender'] : null,
            $type . '_idcard' => $post[$type . '_idcard'] ? $post[$type . '_idcard'] : null,
            $type . '_passport' => $post[$type . '_passport'] ? $post[$type . '_passport'] : null,
            $type . '_birth_date' => $post[$type . '_birth_date'] ? $post[$type . '_birth_date'] : null,
            $type . '_email' => $post[$type . '_email'] ? $post[$type . '_email'] : null,
            $type . '_mobile' => $post[$type . '_mobile'] ? $post[$type . '_mobile'] : null,
            $type . '_facebook' => $post[$type . '_facebook'] ? $post[$type . '_facebook'] : null,
            $type . '_line' => $post[$type . '_line'] ? $post[$type . '_line'] : null,
            $type . '_ig' => $post[$type . '_ig'] ? $post[$type . '_ig'] : null,
            $type . '_address' => $post[$type . '_address'] ? $post[$type . '_address'] : null,
            $type . '_bio' => $post[$type . '_bio'] ? $post[$type . '_bio'] : null,
            $type . '_education' => $post[$type . '_education'] ? $post[$type . '_education'] : null,
            $type . '_experience' => $post[$type . '_experience'] ? $post[$type . '_experience'] : null,
            $type . '_username' => $post[$type . '_username'] ? $post[$type . '_username'] : null,
            $type . '_image_profile' => $image_profile,
            $type . '_card_front' => $card_front,
            $type . '_card_back' => $card_back,
            $type . '_attach_document' => $attached_docs_json,
        ];
        
        if (!empty($post[$type . '_password'])) {
            $data[$type . '_password'] = password_hash($post[$type . '_password'], PASSWORD_DEFAULT);
        }

        if ($id) {
            // UPDATE
            $set_clause = [];
            $params = [];
            $types = '';
            
            foreach ($data as $key => $value) {
                $set_clause[] = "`" . $key . "` = ?";
                $params[] = $value;
                $types .= 's';
            }
            $set_clause[] = "`emp_modify` = ?";
            $params[] = 'admin'; // Replace with actual employee ID
            $types .= 's';
            
            $set_clause[] = "`date_modify` = NOW()";

            $params[] = $id;
            $types .= 'i';

            $stmt_update = $mysqli->prepare("UPDATE `$table` SET " . implode(', ', $set_clause) . " WHERE `$id_col` = ?");
            if (!$stmt_update) {
                throw new Exception("Prepare statement failed: " . $mysqli->error);
            }
            $stmt_update->bind_param($types, ...$params);
            $stmt_update->execute();

            $response = ['status' => 'success', 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'];

        } else {
            // INSERT
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            $types = str_repeat('s', count($columns));
            
            $columns[] = 'emp_create';
            $placeholders[] = '?';
            $types .= 's';

            $columns[] = 'date_create';
            $placeholders[] = 'NOW()';

            $params = array_values($data);
            $params[] = 'admin'; // Replace with actual employee ID

            $stmt_insert = $mysqli->prepare("INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")");
            if (!$stmt_insert) {
                throw new Exception("Prepare statement failed: " . $mysqli->error);
            }
            $stmt_insert->bind_param($types, ...$params);
            $stmt_insert->execute();
            
            $id = $mysqli->insert_id; // Get the newly created ID
            $response = ['status' => 'success', 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว', 'id' => $id];
            
            // Insert into join table
            if ($type === 'teacher') {
                $join_table = 'classroom_teacher_join';
                $join_id_col = 'teacher_id';
            } else {
                $join_table = 'classroom_student_join';
                $join_id_col = 'student_id';
            }
            $join_stmt = $mysqli->prepare("INSERT INTO `$join_table` (`$join_id_col`, `emp_create`, `date_create`) VALUES (?, ?, NOW())");
            if (!$join_stmt) {
                throw new Exception("Prepare join table statement failed: " . $mysqli->error);
            }
            $emp_create = 'admin'; // Replace with actual employee ID
            $join_stmt->bind_param("is", $id, $emp_create);
            $join_stmt->execute();
        }

        $mysqli->commit();
        return $response;

    } catch (Exception $e) {
        $mysqli->rollback();
        error_log($e->getMessage());
        return ['status' => 'error', 'message' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $e->getMessage()];
    }
}
?>