<?php
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
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include.'/lib/connect_sqli.php';
require_once $base_include.'/lib/filesystem.php'; // Assume filesystem functions are here

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

$action = $_POST['action'];

switch ($action) {
    case 'saveData':
        global $mysqli;
        $type = $_POST['type'];
        $id = $_POST[$type . '_id'];
        $tableName = ($type === 'teacher') ? 'classroom_teacher' : 'classroom_student';
        $idColumn = ($type === 'teacher') ? 'teacher_id' : 'student_id';

        $data = [];
        $allowedFields = [
            'perfix', 'firstname_en', 'lastname_en', 'firstname_th', 'lastname_th', 'nickname_en', 'nickname_th',
            'idcard', 'passport', 'image_profile', 'card_front', 'card_back', 'email', 'mobile', 'address',
            'birth_date', 'bio', 'attach_document', 'education', 'experience', 'company', 'position',
            'username', 'password', 'gender', 'facebook', 'line', 'ig', 'hobby', 'music', 'movie', 'goal',
            'religion', 'bloodgroup', 'status', 'comp_id'
        ];
        
        // Handle file uploads first
        $filePaths = [];
        foreach (['image_profile', 'card_front', 'card_back'] as $field) {
            $columnName = $type . '_' . $field;
            // Check if a new file was uploaded
            if (isset($_FILES[$columnName]) && $_FILES[$columnName]['error'] === UPLOAD_ERR_OK) {
                // Upload the new file (you'll need to implement a function for this)
                $newPath = uploadFile($_FILES[$columnName], $type);
                if ($newPath) {
                    $filePaths[$columnName] = $newPath;
                } else {
                    echo json_encode(['status' => 'error', 'message' => "Failed to upload $columnName"]);
                    exit();
                }
            } else {
                // If no new file, use the existing hidden value
                $hiddenField = 'hidden_' . $field;
                if (isset($_POST[$hiddenField]) && !empty($_POST[$hiddenField])) {
                    $filePaths[$columnName] = $_POST[$hiddenField];
                }
            }
        }
        
        // Handle attached documents
        $existingDocs = json_decode($_POST['existing_attach_document'], true);
        $newDocs = [];
        if (isset($_FILES[$type . '_attach_document'])) {
            $files = $_FILES[$type . '_attach_document'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $newPath = uploadFile([
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ], $type);
                    if ($newPath) {
                        $newDocs[] = $newPath;
                    }
                }
            }
        }
        $allDocs = array_merge($existingDocs, $newDocs);
        
        // Prepare data array for SQL query
        foreach ($allowedFields as $field) {
            $postKey = $type . '_' . $field;
            if (isset($_POST[$postKey])) {
                $data[$field] = $_POST[$postKey];
            }
        }
        
        // Add file paths to data array
        foreach ($filePaths as $column => $path) {
            $data[str_replace($type . '_', '', $column)] = $path;
        }

        // Add document paths to data array
        $data['attach_document'] = json_encode($allDocs);

        // Handle password hashing if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $passwordKey = generateRandomString(16);
            $hashedPassword = md5($data['password'] . $passwordKey);
            $data['password'] = $hashedPassword;
            $data['password_key'] = $passwordKey;
        } else {
            unset($data['password']); // Don't update password if it's empty
        }

        // Prepare SQL query
        $columns = [];
        $placeholders = [];
        $values = [];
        $types = '';

        foreach ($data as $column => $value) {
            $columns[] = "`" . $type . "_" . $column . "` = ?";
            $values[] = $value;
            $types .= 's';
        }
        
        // Set update and modify dates
        $data['date_modify'] = date('Y-m-d H:i:s');
        $columns[] = "`date_modify` = ?";
        $values[] = $data['date_modify'];
        $types .= 's';

        if ($id) {
            // Update an existing record
            $sql = "UPDATE `$tableName` SET " . implode(', ', $columns) . " WHERE `$idColumn` = ?";
            $values[] = $id;
            $types .= 'i';
        } else {
            // Insert a new record
            $data['date_create'] = date('Y-m-d H:i:s');
            $data['emp_create'] = $_SESSION['user_id'] ? $_SESSION['user_id'] : 0; // assuming you have user id
            $data['status'] = 1;
            
            $insertColumns = [];
            $insertPlaceholders = [];
            $insertValues = [];
            $insertTypes = '';

            foreach ($data as $column => $value) {
                $insertColumns[] = "`" . $type . "_" . $column . "`";
                $insertPlaceholders[] = "?";
                $insertValues[] = $value;
                $insertTypes .= 's';
            }
            
            $sql = "INSERT INTO `$tableName` (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertPlaceholders) . ")";
            $values = $insertValues;
            $types = $insertTypes;
        }
        
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Data saved successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save data: ' . $mysqli->error]);
        }

        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
        break;
}

function uploadFile($file, $type) {
    // This function should be in your `filesystem.php` to handle file uploads
    // and return the path on your filesystem/bucket.
    // Example implementation:
    // $uploadDir = BASE_INCLUDE . '/uploads/' . $type . '/';
    // if (!is_dir($uploadDir)) {
    //     mkdir($uploadDir, 0777, true);
    // }
    // $fileName = uniqid() . '_' . basename($file['name']);
    // $uploadPath = $uploadDir . $fileName;
    // if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    //     return '/uploads/' . $type . '/' . $fileName;
    // }
    return false; // placeholder for failure
}

function generateRandomString($length = 16) {
    // A function to generate a random string for the password key
    return bin2hex(random_bytes($length / 2));
}

?>