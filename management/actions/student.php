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
global $mysqli;
$fsData = getBucketMaster();
$filesystem_user = $fsData['fs_access_user'];
$filesystem_pass = $fsData['fs_access_pass'];
$filesystem_host = $fsData['fs_host'];
$filesystem_path = $fsData['fs_access_path'];
$filesystem_type = $fsData['fs_type'];
$fs_id = $fsData['fs_id'];
setBucket($fsData);



if(isset($_POST) && $_POST['action'] == 'buildStudent') {
    $classroom_id = $_POST['classroom_id'];
    $table = "SELECT 
        cjoin.join_id,
        cjoin.student_id,
        date_format(cjoin.register_date, '%Y/%m/%d %H:%i:%s') as register_date,
        stu.student_firstname_en,
        stu.student_lastname_en,
        stu.student_firstname_th,
        stu.student_lastname_th,
        stu.student_nickname_en,
        stu.student_nickname_th,
        stu.student_gender,
        stu.student_idcard,
        stu.student_passport,
        stu.student_image_profile,
        stu.student_email,
        stu.student_mobile,
        date_format(stu.student_birth_date, '%Y/%m/%d') as student_birth_date,
        CASE  WHEN stu.student_birth_date IS NULL OR stu.student_birth_date = '' THEN ''
        ELSE CONCAT(TIMESTAMPDIFF(YEAR, stu.student_birth_date, CURDATE()), ' Yrs.') END as student_age,
        stu.student_username,
        stu.student_password,
        stu.student_password_key,
        stu.student_company,
        stu.student_position,
        g.group_id,
        g.group_name
    FROM 
        classroom_student_join cjoin
    LEFT JOIN 
        classroom_student stu on stu.student_id = cjoin.student_id 
    LEFT JOIN 
        classroom_group g on g.group_id = cjoin.group_id
    WHERE 
        cjoin.classroom_id = '{$classroom_id}' and cjoin.status = 0 and cjoin.payment_status = 1 $filter";
    $primaryKey = 'join_id';
    $columns = array(
        array('db' => 'join_id', 'dt' => 'join_id'),
        array('db' => 'student_id', 'dt' => 'student_id'),
        array('db' => 'register_date', 'dt' => 'register_date'),
        array('db' => 'student_firstname_en', 'dt' => 'student_firstname_en'),
        array('db' => 'student_lastname_en', 'dt' => 'student_lastname_en'),
        array('db' => 'student_firstname_th', 'dt' => 'student_firstname_th'),
        array('db' => 'student_lastname_th', 'dt' => 'student_lastname_th'),
        array('db' => 'student_nickname_en', 'dt' => 'student_nickname_en'),
        array('db' => 'student_nickname_th', 'dt' => 'student_nickname_th'),
        array('db' => 'student_gender', 'dt' => 'student_gender'),
        array('db' => 'student_idcard', 'dt' => 'student_idcard'),
        array('db' => 'student_passport', 'dt' => 'student_passport'),
        array('db' => 'student_image_profile', 'dt' => 'student_image_profile','formatter' => function ($d, $row) {
            return GetUrl($d);
        }),
        array('db' => 'student_email', 'dt' => 'student_email'),
        array('db' => 'student_mobile', 'dt' => 'student_mobile'),
        array('db' => 'student_birth_date', 'dt' => 'student_birth_date'),
        array('db' => 'student_age', 'dt' => 'student_age'),
        array('db' => 'student_company', 'dt' => 'student_company'),
        array('db' => 'student_position', 'dt' => 'student_position'),
        array('db' => 'student_username', 'dt' => 'student_username'),
        array('db' => 'student_password_key', 'dt' => 'student_password_key'),
        array('db' => 'group_id', 'dt' => 'group_id'),
        array('db' => 'group_name', 'dt' => 'group_name'),
        array('db' => 'student_password', 'dt' => 'student_password','formatter' => function ($d, $row) {
            return ($d) ? decryptToken($d, $row['student_password_key']) : '';
        }),
    );
    $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
    require($base_include.'/lib/ssp-subquery.class.php');
    echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
    exit();
}
// Action: บันทึก/อัปเดตข้อมูลนักเรียน (saveStudent)
if(isset($_POST['action']) && $_POST['action'] == 'saveStudent') {
    global $mysqli;
    
    $comp_id = isset($_SESSION['comp_id']) ? $_SESSION['comp_id'] : null;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : '';
    $classroom_id = isset($_POST['classroom_id']) ? $_POST['classroom_id'] : null;

    if ($comp_id === null || $classroom_id === null) {
        echo json_encode(array('status' => 'error', 'message' => 'Required data (comp_id or classroom_id) not found.'));
        exit();
    }

    $upload_dir = 'uploads/students/';
    $file_fields = array('student_image_profile', 'student_card_front', 'student_card_back');
    $file_paths = array();

    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            $file_name = uniqid() . '_' . basename($_FILES[$field]['name']);
            $target_file = $upload_dir . $file_name;
            if (saveFile($_FILES[$field]['tmp_name'], $target_file)) {
                $file_paths[$field] = $target_file;
            }
        } else if (isset($_POST[$field . '_current'])) {
            $file_paths[$field] = $_POST[$field . '_current'];
        }
    }

    $document_paths = array();
    if (isset($_FILES['student_attach_document']) && count($_FILES['student_attach_document']['name']) > 0) {
        $existing_documents = isset($_POST['student_attach_document_current']) ? $_POST['student_attach_document_current'] : '';
        $document_paths = array_filter(explode('|', $existing_documents));
        foreach ($_FILES['student_attach_document']['name'] as $key => $name) {
            if ($_FILES['student_attach_document']['error'][$key] == UPLOAD_ERR_OK) {
                $file_name = uniqid() . '_' . basename($name);
                $target_file = $upload_dir . $file_name;
                if (saveFile($_FILES['student_attach_document']['tmp_name'][$key], $target_file)) {
                    $document_paths[] = $target_file;
                }
            }
        }
    } else {
        $existing_documents = isset($_POST['student_attach_document_current']) ? $_POST['student_attach_document_current'] : '';
        $document_paths = array_filter(explode('|', $existing_documents));
    }

    $perfix_map = array(
        'นาย' => 0,
        'นาง' => 1,
        'นางสาว' => 2
    );
    $student_perfix = isset($perfix_map[$_POST['student_perfix']]) ? $perfix_map[$_POST['student_perfix']] : null;

    $data = array(
        'student_perfix' => $student_perfix,
        'student_firstname_en' => $_POST['student_firstname_en'] ? $_POST['student_firstname_en'] : null,
        'student_lastname_en' => $_POST['student_lastname_en'] ? $_POST['student_lastname_en'] : null,
        'student_firstname_th' => $_POST['student_firstname_th'] ? $_POST['student_firstname_th'] : null,
        'student_lastname_th' => $_POST['student_lastname_th'] ? $_POST['student_lastname_th'] : null,
        'student_nickname_en' => $_POST['student_nickname_en'] ? $_POST['student_nickname_en'] : null,
        'student_nickname_th' => $_POST['student_nickname_th'] ? $_POST['student_nickname_th'] : null,
        'student_idcard' => $_POST['student_idcard'] ? $_POST['student_idcard'] : null,
        'student_passport' => $_POST['student_passport'] ? $_POST['student_passport'] : null,
        'student_email' => $_POST['student_email'] ? $_POST['student_email'] : null,
        'student_mobile' => $_POST['student_mobile'] ? $_POST['student_mobile'] : null,
        'student_address' => $_POST['student_address'] ? $_POST['student_address'] : null,
        'student_birth_date' => $_POST['student_birth_date'] ? $_POST['student_birth_date'] : null,
        'student_bio' => $_POST['student_bio'] ? $_POST['student_bio'] : null,
        'student_education' => $_POST['student_education'] ? $_POST['student_education'] : null,
        'student_experience' => $_POST['student_experience'] ? $_POST['student_experience'] : null,
        'student_company' => $_POST['student_company'] ? $_POST['student_company'] : null,
        'student_position' => $_POST['student_position'] ? $_POST['student_position'] : null,
        'student_username' => $_POST['student_username'] ? $_POST['student_username'] : null,
    );

    foreach ($file_paths as $key => $path) {
        $data[$key] = $path;
    }
    $data['student_attach_document'] = implode('|', $document_paths);

    // If there's a password, encrypt it
    if (!empty($_POST['student_password'])) {
        $plain_password = $_POST['student_password'];
        $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
        $data['student_password'] = encryptToken($plain_password, $student_password_key);
        $data['student_password_key'] = $student_password_key;
    }

    if (empty($student_id)) {
        // New student
        $data['comp_id'] = $comp_id;
        $data['emp_create'] = $user_id;
        $data['date_create'] = date('Y-m-d H:i:s');
        $data['status'] = 1;

        $fields = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        $sql = "INSERT INTO classroom_student ($fields) VALUES ($placeholders)";
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
        }

        $types = str_repeat('s', count($data));
        $values = array_values($data);
        $bind_values = array($types);
        for ($i = 0; $i < count($values); $i++) {
            $bind_values[] = &$values[$i];
        }

        call_user_func_array(array($stmt, 'bind_param'), $bind_values);

        if ($stmt->execute()) {
            $new_student_id = $mysqli->insert_id;
            $join_sql = "INSERT INTO classroom_student_join (classroom_id, student_id, comp_id, status, payment_status, emp_create, date_create) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $join_stmt = $mysqli->prepare($join_sql);
            if ($join_stmt === false) {
                die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
            }
            $status = 0;
            $payment_status = 1;
            $date_create = date('Y-m-d H:i:s');
            $join_stmt->bind_param('sssssss', $classroom_id, $new_student_id, $comp_id, $status, $payment_status, $user_id, $date_create);
            if ($join_stmt->execute()) {
                echo json_encode(array('status' => 'success', 'message' => 'New student and join data added successfully.'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => "Error inserting into join table: " . $join_stmt->error));
            }
            $join_stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => "Error inserting into student table: " . $stmt->error));
        }
        $stmt->close();
    } else {
        // Update student
        $data['emp_modify'] = $user_id;
        $data['date_modify'] = date('Y-m-d H:i:s');

        $set_clause = array();
        foreach ($data as $key => $value) {
            $set_clause[] = "$key = ?";
        }
        $set_clause = implode(", ", $set_clause);
        $sql = "UPDATE classroom_student SET $set_clause WHERE student_id = ?";
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
        }

        $values = array_values($data);
        $values[] = $student_id;
        $types = str_repeat('s', count($values));
        $bind_values = array($types);
        for ($i = 0; $i < count($values); $i++) {
            $bind_values[] = &$values[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_values);

        if ($stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Student updated successfully.'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => "Error updating student table: " . $stmt->error));
        }
        $stmt->close();
    }
    $mysqli->close();
    exit();
}

// Action: ดึงข้อมูลนักเรียนเพื่อนำไปกรอกในฟอร์ม (getStudentData)
// Action: ดึงข้อมูลนักเรียนเพื่อนำไปกรอกในฟอร์ม (getStudentData)
if(isset($_POST) && $_POST['action'] == 'getStudentData') {
    global $mysqli;
    $student_id = $_POST['student_id'];
    $sql = "SELECT * FROM classroom_student WHERE student_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data) {
        // **ส่วนที่เพิ่มเข้ามา: เรียกใช้ GetUrl() เพื่อจัดการ URL รูปภาพ**
        if ($data && !empty($data['student_image_profile'])) {
            $data['student_image_profile'] = GetUrl($data['student_image_profile']);
        }
        if ($data && !empty($data['student_card_front'])) {
            $data['student_card_front'] = GetUrl($data['student_card_front']);
        }
        if ($data && !empty($data['student_card_back'])) {
            $data['student_card_back'] = GetUrl($data['student_card_back']);
        }

        // จัดการเอกสารแนบ
        if ($data && !empty($data['student_attach_document'])) {
            $documents = explode('|', $data['student_attach_document']);
            $url_documents = array();
            foreach ($documents as $doc_path) {
                if (!empty($doc_path)) {
                    $url_documents[] = GetUrl($doc_path);
                }
            }
            $data['student_attach_document'] = $url_documents;
        } else {
            $data['student_attach_document'] = [];
        }

        echo json_encode($data);
    } else {
        echo json_encode(null);
    }
    $stmt->close();
    exit();
}


// Action: ลบนักเรียน (deleteStudent)
if(isset($_POST) && $_POST['action'] == 'deleteStudent') {
    global $mysqli;
    $student_id = $_POST['student_id'] ? $_POST['student_id'] : null;
    if ($student_id === null) {
        echo json_encode(array('status' => 'error', 'message' => 'Student ID not found.'));
        exit();
    }
    $sql = "UPDATE classroom_student SET status = 0, date_modify = ? WHERE student_id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
    }
    $date_modify = date('Y-m-d H:i:s');
    $stmt->bind_param('ss', $date_modify, $student_id);
    if ($stmt->execute()) {
        echo json_encode(array('status' => 'success', 'message' => 'Student deleted successfully.'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => "Error deleting student: " . $stmt->error));
    }
    $stmt->close();
    $mysqli->close();
    exit();
}
?>