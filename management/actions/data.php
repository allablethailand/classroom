<?php
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
setBucket($fsData);
header('Content-Type: application/json');

function saveFile($file, $uploadPath) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($file['name']);
        $targetFile = $uploadPath . $fileName;
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $targetFile;
        }
    }
    return null;
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = ['status' => 'error', 'message' => 'Invalid request.'];
    $db = $mysqli;

    switch ($action) {
        case 'fetchData':
            $type = $_POST['type'];
            $id = $_POST['id'];
            $table = "classroom_" . $type;
            $id_col = $type . "_id";

            if ($stmt = $db->prepare("SELECT * FROM `$table` WHERE `$id_col` = ?")) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                $stmt->close();
                
                if ($data) {
                    // Handle attached documents as a JSON array
                    if (!empty($data[$type . '_attach_document'])) {
                        $data[$type . '_attach_document'] = json_decode($data[$type . '_attach_document']);
                    } else {
                        $data[$type . '_attach_document'] = [];
                    }

                    $response['status'] = 'success';
                    $response['message'] = 'Data fetched successfully.';
                    $response['data'] = $data;
                } else {
                    $response['message'] = 'Data not found.';
                }
            } else {
                $response['message'] = 'Database query failed: ' . $db->error;
            }
            break;

        case 'saveData':
            $type = $_POST['type'];
            $id = isset($_POST[$type . '_id']) && !empty($_POST[$type . '_id']) ? $_POST[$type . '_id'] : null;
            $table = "classroom_" . $type;
            $id_col = $type . "_id";
            $upload_dir = "../uploads/" . $type . "/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Prepare data for saving
            $data = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, $type . '_') === 0 && $key != 'type' && $key != 'action') {
                    $data[$key] = $value;
                }
            }
            
            // Handle file uploads
            $image_profile_path = $data[$type . '_image_profile_current'] ? $data[$type . '_image_profile_current'] : null;
            if (isset($_FILES[$type . '_image_profile'])) {
                $image_profile_path = saveFile($_FILES[$type . '_image_profile'], $upload_dir);
            }
            $data[$type . '_image_profile'] = $image_profile_path;

            $card_front_path = $data[$type . '_card_front_current'] ? $data[$type . '_card_front_current'] : null;
            if (isset($_FILES[$type . '_card_front'])) {
                $card_front_path = saveFile($_FILES[$type . '_card_front'], $upload_dir);
            }
            $data[$type . '_card_front'] = $card_front_path;

            $card_back_path = $data[$type . '_card_back_current'] ? $data[$type . '_card_back_current'] : null;
            if (isset($_FILES[$type . '_card_back'])) {
                $card_back_path = saveFile($_FILES[$type . '_card_back'], $upload_dir);
            }
            $data[$type . '_card_back'] = $card_back_path;

            // Handle multiple document uploads
            $document_paths = json_decode($_POST[$type . '_attach_document_existing'] ? $_POST[$type . '_attach_document_existing'] : '[]');
            if (isset($_FILES[$type . '_attach_document_new'])) {
                foreach ($_FILES[$type . '_attach_document_new']['tmp_name'] as $key => $tmp_name) {
                    $new_file = [
                        'name' => $_FILES[$type . '_attach_document_new']['name'][$key],
                        'type' => $_FILES[$type . '_attach_document_new']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES[$type . '_attach_document_new']['error'][$key],
                        'size' => $_FILES[$type . '_attach_document_new']['size'][$key]
                    ];
                    $doc_path = saveFile($new_file, $upload_dir);
                    if ($doc_path) {
                        $document_paths[] = $doc_path;
                    }
                }
            }
            $data[$type . '_attach_document'] = json_encode($document_paths);

            // Sanitize and prepare SQL query
            $cols = [];
            $placeholders = [];
            $values = [];
            $types = '';
            
            // Map form fields to DB columns and their data types
            $column_map = [
                'perfix' => 's', 'firstname_en' => 's', 'lastname_en' => 's', 'firstname_th' => 's', 'lastname_th' => 's',
                'nickname_en' => 's', 'nickname_th' => 's', 'idcard' => 's', 'passport' => 's', 'image_profile' => 's',
                'card_front' => 's', 'card_back' => 's', 'email' => 's', 'mobile' => 's', 'address' => 's', 'birth_date' => 's',
                'bio' => 's', 'attach_document' => 's', 'education' => 's', 'experience' => 's', 'company' => 's',
                'position' => 's', 'username' => 's', 'password' => 's', 'gender' => 's', 'facebook' => 's', 'line' => 's',
                'ig' => 's', 'hobby' => 's', 'music' => 's', 'movie' => 's', 'goal' => 's', 'religion' => 's', 'bloodgroup' => 's',
            ];

            foreach ($column_map as $col => $type_char) {
                $full_col = $type . '_' . $col;
                if (isset($data[$full_col])) {
                    $cols[] = "`$full_col`";
                    $placeholders[] = '?';
                    $values[] = $data[$full_col];
                    $types .= $type_char;
                }
            }
            
            // Add user and timestamp
            $emp_field = ($id ? 'emp_modify' : 'emp_create');
            $date_field = ($id ? 'date_modify' : 'date_create');
            $status_field = 'status';
            
            $cols[] = "`$emp_field`";
            $placeholders[] = '?';
            $values[] = $_SESSION['user_id']; // Use a real user ID from session
            $types .= 'i';

            $cols[] = "`$date_field`";
            $placeholders[] = '?';
            $values[] = date('Y-m-d H:i:s');
            $types .= 's';
            
            if (!$id) {
                $cols[] = "`$status_field`";
                $placeholders[] = '?';
                $values[] = 1; // Default status for new entry
                $types .= 'i';
            }

            if ($id) {
                // UPDATE query
                $set_clause = implode(' = ?, ', $cols) . ' = ?';
                $sql = "UPDATE `$table` SET $set_clause WHERE `$id_col` = ?";
                $values[] = $id;
                $types .= 'i';
                
                if ($stmt = $db->prepare($sql)) {
                    $stmt->bind_param($types, ...$values);
                    if ($stmt->execute()) {
                        $response['status'] = 'success';
                        $response['message'] = 'Data updated successfully.';
                    } else {
                        $response['message'] = 'Update failed: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $response['message'] = 'Database query failed: ' . $db->error;
                }
            } else {
                // INSERT query
                $col_names = implode(', ', $cols);
                $placeholder_list = implode(', ', $placeholders);
                $sql = "INSERT INTO `$table` ($col_names) VALUES ($placeholder_list)";
                
                if ($stmt = $db->prepare($sql)) {
                    $stmt->bind_param($types, ...$values);
                    if ($stmt->execute()) {
                        $response['status'] = 'success';
                        $response['message'] = 'Data added successfully.';
                    } else {
                        $response['message'] = 'Insert failed: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $response['message'] = 'Database query failed: ' . $db->error;
                }
            }
            break;
    }

    echo json_encode($response);
}
?>