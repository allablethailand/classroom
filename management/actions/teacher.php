<?php
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
    if(isset($_POST) && $_POST['action'] == 'buildTeacher') {
        $classroom_id = $_POST['classroom_id'] ? $_POST['classroom_id'] : null;
        if ($classroom_id === null) {
            echo json_encode(array('status' => 'error', 'message' => 'Classroom ID not found.'));
            exit();
        }
        $table = "SELECT
            t.teacher_id,
            CASE t.teacher_perfix
                WHEN 0 THEN 'นาย'
                WHEN 1 THEN 'นาง'
                WHEN 2 THEN 'นางสาว'
                ELSE ''
            END AS teacher_perfix,
            CONCAT(
                CASE 
                    WHEN COALESCE(t.teacher_firstname_th, '') = '' OR COALESCE(t.teacher_lastname_th, '') = '' THEN
                        CASE t.teacher_perfix
                            WHEN 0 THEN 'Mr.'
                            WHEN 1 THEN 'Mrs.'
                            WHEN 2 THEN 'Ms.'
                            ELSE ''
                        END
                    ELSE
                        CASE t.teacher_perfix
                            WHEN 0 THEN 'นาย'
                            WHEN 1 THEN 'นาง'
                            WHEN 2 THEN 'นางสาว'
                            ELSE ''
                        END
                END,
                COALESCE(t.teacher_firstname_th, t.teacher_firstname_en), 
                ' ', 
                COALESCE(t.teacher_lastname_th, t.teacher_lastname_en)
            ) AS teacher_name,
            p.position_name_en AS teacher_job_position,
            t.teacher_company,
            t.teacher_position,
            date_format(t.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            date_format(t.date_modify, '%Y/%m/%d %H:%i:%s') as date_modify,
            CONCAT(IFNULL(i2.firstname,i2.firstname_th),' ',IFNULL(i2.lastname,i2.lastname_th)) AS emp_modify
        FROM
            classroom_teacher t
        LEFT JOIN
            classroom_teacher_join j ON j.teacher_id = t.teacher_id
        LEFT JOIN
            m_employee_info i on i.emp_id = t.emp_create
        LEFT JOIN 
            m_employee_info i2 on i2.emp_id = t.emp_modify
        LEFT JOIN
            classroom_position p ON p.position_id = t.position_id
        WHERE
            j.status = 0 AND j.classroom_id = '{$classroom_id}'";  
        $primaryKey = 'teacher_id';
        $columns = array(
            array('db' => 'teacher_id', 'dt' => 'teacher_id'),
            array('db' => 'teacher_name', 'dt' => 'teacher_name'),
            array('db' => 'teacher_job_position', 'dt' => 'teacher_job_position'),
            array('db' => 'teacher_company', 'dt' => 'teacher_company'),
            array('db' => 'teacher_position', 'dt' => 'teacher_position'),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'date_modify', 'dt' => 'date_modify'),
            array('db' => 'emp_modify', 'dt' => 'emp_modify'),
        );
        $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db' => $db_name,'host' => $db_host);
        require($base_include.'/lib/ssp-subquery.class.php');
        echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
        exit();
    }
    if(isset($_POST['action']) && $_POST['action'] == 'saveTeacher') {
        global $mysqli;
        $comp_id = isset($_SESSION['comp_id']) ? $_SESSION['comp_id'] : null;
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
        $classroom_id = $_POST['classroom_id'] ? $_POST['classroom_id'] : null;
        $teacher_id = $_POST['teacher_id'] ? $_POST['teacher_id'] : '';
        if ($comp_id === null || $classroom_id === null) {
            echo json_encode(array('status' => 'error', 'message' => 'Required data (comp_id or classroom_id) not found.'));
            exit();
        }
        $upload_dir = 'uploads/teachers/';
        $file_fields = array('teacher_image_profile', 'teacher_card_front', 'teacher_card_back');
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
        if (isset($_FILES['teacher_attach_document']) && count($_FILES['teacher_attach_document']['name']) > 0) {
            $existing_documents = isset($_POST['teacher_attach_document_current']) ? $_POST['teacher_attach_document_current'] : '';
            $document_paths = array_filter(explode('|', $existing_documents));
            foreach ($_FILES['teacher_attach_document']['name'] as $key => $name) {
                if ($_FILES['teacher_attach_document']['error'][$key] == UPLOAD_ERR_OK) {
                    $file_name = uniqid() . '_' . basename($name);
                    $target_file = $upload_dir . $file_name;
                    if (saveFile($_FILES['teacher_attach_document']['tmp_name'][$key], $target_file)) {
                        $document_paths[] = $target_file;
                    }
                }
            }
        } else {
            $existing_documents = isset($_POST['teacher_attach_document_current']) ? $_POST['teacher_attach_document_current'] : '';
            $document_paths = array_filter(explode('|', $existing_documents));
        }
        $perfix_map = array(
            'นาย' => 0,
            'นาง' => 1,
            'นางสาว' => 2
        );
        $teacher_perfix = isset($perfix_map[$_POST['teacher_perfix']]) ? $perfix_map[$_POST['teacher_perfix']] : null;
        $data = array(
            'teacher_perfix' => $teacher_perfix, 
            'teacher_firstname_en' => $_POST['teacher_firstname_en'] ? $_POST['teacher_firstname_en'] : null,
            'teacher_lastname_en' => $_POST['teacher_lastname_en'] ? $_POST['teacher_lastname_en'] : null,
            'teacher_firstname_th' => $_POST['teacher_firstname_th'] ? $_POST['teacher_firstname_th'] : null,
            'teacher_lastname_th' => $_POST['teacher_lastname_th'] ? $_POST['teacher_lastname_th'] : null,
            'teacher_nickname_en' => $_POST['teacher_nickname_en'] ? $_POST['teacher_nickname_en'] : null,
            'teacher_nickname_th' => $_POST['teacher_nickname_th'] ? $_POST['teacher_nickname_th'] : null,
            'teacher_idcard' => $_POST['teacher_idcard'] ? $_POST['teacher_idcard'] : null,
            'teacher_passport' => $_POST['teacher_passport'] ? $_POST['teacher_passport'] : null,
            'teacher_email' => $_POST['teacher_email'] ? $_POST['teacher_email'] : null,
            'teacher_mobile' => $_POST['teacher_mobile'] ? $_POST['teacher_mobile'] : null,
            'teacher_address' => $_POST['teacher_address'] ? $_POST['teacher_address'] : null,
            'teacher_birth_date' => $_POST['teacher_birth_date'] ? $_POST['teacher_birth_date'] : null,
            'teacher_bio' => $_POST['teacher_bio'] ? $_POST['teacher_bio'] : null,
            'teacher_education' => $_POST['teacher_education'] ? $_POST['teacher_education'] : null,
            'teacher_experience' => $_POST['teacher_experience'] ? $_POST['teacher_experience'] : null,
            'teacher_company' => $_POST['teacher_company'] ? $_POST['teacher_company'] : null,
            'teacher_position' => $_POST['teacher_position'] ? $_POST['teacher_position'] : null,
            'teacher_username' => $_POST['teacher_username'] ? $_POST['teacher_username'] : null,
            'position_id' => $_POST['position_id'] ? $_POST['position_id'] : null,
            'teacher_ref_type' => $_POST['teacher_ref_type'] ? $_POST['teacher_ref_type'] : null,
        );
        foreach ($file_paths as $key => $path) {
            $data[$key] = $path;
        }
        $data['teacher_attach_document'] = implode('|', $document_paths);
        if (empty($teacher_id)) {
            $data['comp_id'] = $comp_id;
            $data['emp_create'] = $user_id;
            $data['date_create'] = date('Y-m-d H:i:s'); 
            if (!empty($_POST['teacher_password'])) {
                $plain_password = $_POST['teacher_password'];
                $teacher_password_key = bin2hex(openssl_random_pseudo_bytes(16));
                $data['teacher_password'] = encryptToken($plain_password, $teacher_password_key);
                $data['teacher_password_key'] = $teacher_password_key;
            }
            $fields = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), '?'));
            $sql = "INSERT INTO classroom_teacher ($fields) VALUES ($placeholders)";
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
                $new_teacher_id = $mysqli->insert_id;
                $join_sql = "INSERT INTO classroom_teacher_join (classroom_id, teacher_id, comp_id, status, emp_create, date_create) VALUES (?, ?, ?, ?, ?, ?)";
                $join_stmt = $mysqli->prepare($join_sql);
                if ($join_stmt === false) {
                    die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
                }
                $status = 0;
                $date_create = date('Y-m-d H:i:s');
                $join_stmt->bind_param('ssssss', $classroom_id, $new_teacher_id, $comp_id, $status, $user_id, $date_create);
                if ($join_stmt->execute()) {
                    echo json_encode(array('status' => 'success', 'message' => 'New teacher and join data added successfully.'));
                } else {
                    echo json_encode(array('status' => 'error', 'message' => "Error inserting into join table: " . $join_stmt->error));
                }
                $join_stmt->close();
            } else {
                echo json_encode(array('status' => 'error', 'message' => "Error inserting into teacher table: " . $stmt->error));
            }
            $stmt->close();
        } else {
            $data['emp_modify'] = $user_id;
            $data['date_modify'] = date('Y-m-d H:i:s');
            if (!empty($_POST['teacher_password'])) {
                $plain_password = $_POST['teacher_password'];
                $teacher_password_key = bin2hex(openssl_random_pseudo_bytes(16));
                $data['teacher_password'] = encryptToken($plain_password, $teacher_password_key);
                $data['teacher_password_key'] = $teacher_password_key;
            }
            $set_clause = array();
            foreach ($data as $key => $value) {
                $set_clause[] = "$key = ?";
            }
            $set_clause = implode(", ", $set_clause);
            $sql = "UPDATE classroom_teacher SET $set_clause WHERE teacher_id = ?";
            $stmt = $mysqli->prepare($sql);
            if ($stmt === false) {
                die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
            }
            $values = array_values($data);
            $values[] = $teacher_id;
            $types = str_repeat('s', count($values));
            $bind_values = array($types);
            for ($i = 0; $i < count($values); $i++) {
                $bind_values[] = &$values[$i];
            }
            call_user_func_array(array($stmt, 'bind_param'), $bind_values);
            if ($stmt->execute()) {
                $check_sql = "SELECT * FROM classroom_teacher_join WHERE teacher_id = ? AND classroom_id = ?";
                $check_stmt = $mysqli->prepare($check_sql);
                if ($check_stmt === false) {
                    die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
                }
                $check_stmt->bind_param('ss', $teacher_id, $classroom_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows > 0) {
                    $update_join_sql = "UPDATE classroom_teacher_join SET comp_id = ?, status = 0, emp_modify = ?, date_modify = ? WHERE teacher_id = ? AND classroom_id = ?";
                    $update_join_stmt = $mysqli->prepare($update_join_sql);
                    if ($update_join_stmt === false) {
                        die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
                    }
                    $date_modify = date('Y-m-d H:i:s');
                    $status = 0;
                    $update_join_stmt->bind_param('sssss', $comp_id, $user_id, $date_modify, $teacher_id, $classroom_id);
                    $update_join_stmt->execute();
                    $update_join_stmt->close();
                } else {
                    $join_sql = "INSERT INTO classroom_teacher_join (classroom_id, teacher_id, comp_id, status, emp_create, date_create) VALUES (?, ?, ?, ?, ?, ?)";
                    $join_stmt = $mysqli->prepare($join_sql);
                    if ($join_stmt === false) {
                        die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
                    }
                    $date_create = date('Y-m-d H:i:s');
                    $status = 0;
                    $join_stmt->bind_param('ssssss', $classroom_id, $teacher_id, $comp_id, $status, $user_id, $date_create);
                    $join_stmt->execute();
                    $join_stmt->close();
                }
                echo json_encode(array('status' => 'success', 'message' => 'Teacher updated successfully.'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => "Error updating teacher table: " . $stmt->error));
            }
            $stmt->close();
        }
        $mysqli->close();
        exit();
    }
    if(isset($_POST['action']) && $_POST['action'] == 'getTeacherData') {
    global $mysqli;
    $teacher_id = $_POST['teacher_id'] ? $_POST['teacher_id'] : null;
    if ($teacher_id === null) {
        echo json_encode(array('status' => 'error', 'message' => 'Teacher ID not found.'));
        exit();
    }
    
    $sql = "SELECT t.*, j.classroom_id FROM classroom_teacher t LEFT JOIN classroom_teacher_join j ON t.teacher_id = j.teacher_id WHERE t.teacher_id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
    }
    
    $stmt->bind_param('s', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacherData = $result->fetch_assoc();
    
    // **ส่วนที่เพิ่มเข้ามา: เรียกใช้ GetUrl() เพื่อจัดการ URL รูปภาพ**
    if ($teacherData && !empty($teacherData['teacher_image_profile'])) {
        $teacherData['teacher_image_profile'] = GetUrl($teacherData['teacher_image_profile']);
    }
    if ($teacherData && !empty($teacherData['teacher_card_front'])) {
        $teacherData['teacher_card_front'] = GetUrl($teacherData['teacher_card_front']);
    }
    if ($teacherData && !empty($teacherData['teacher_card_back'])) {
        $teacherData['teacher_card_back'] = GetUrl($teacherData['teacher_card_back']);
    }
    
    unset($teacherData['teacher_password']);
    unset($teacherData['teacher_password_key']);
    
    echo json_encode($teacherData);
    $stmt->close();
    $mysqli->close();
    exit();
}

    if(isset($_POST['action']) && $_POST['action'] == 'deleteTeacher') {
        global $mysqli;
        $teacher_id = $_POST['teacher_id'] ? $_POST['teacher_id'] : null;
        if ($teacher_id === null) {
            echo json_encode(array('status' => 'error', 'message' => 'Teacher ID not found.'));
            exit();
        }
        $sql = "UPDATE classroom_teacher_join SET status = 1, date_modify = ? WHERE teacher_id = ?";
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
        }
        $date_modify = date('Y-m-d H:i:s');
        $stmt->bind_param('ss', $date_modify, $teacher_id);
        if ($stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Teacher deleted successfully.'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => "Error deleting teacher: " . $stmt->error));
        }
        $stmt->close();
        $mysqli->close();
        exit();
    }
    if(isset($_POST['action']) && $_POST['action'] == 'getPositions') {
        global $mysqli;
        $sql = "SELECT position_id, position_name_en FROM classroom_position WHERE status = 0 ORDER BY position_name_en ASC";
        $result = $mysqli->query($sql);
        $positions = array();
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $positions[] = $row;
            }
        }
        echo json_encode(array('status' => 'success', 'data' => $positions));
        $mysqli->close();
        exit();
    }
?>