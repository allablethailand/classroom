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
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
    setBucket($fsData);
    if(isset($_POST) && $_POST['action'] == 'buildStudent') {
        $classroom_id = $_POST['classroom_id'];
        $group_id = (isset($_POST['group_id'])) ? $_POST['group_id'] : '';
        $build_type = (isset($_POST['build_type'])) ? $_POST['build_type'] : '';
        $filter = "";
        if($build_type == 'join') {
            $group_selected = (isset($_POST['group_selected'])) ? $_POST['group_selected'] : '';
            if($group_selected) {
                $filter .= ($group_id) ? " and g.group_id = '{$group_selected}' " : "";
            } else {
                $filter .= " and cjoin.group_id is null or cjoin.group_id = '' ";
            }
        } else {
            $filter .= ($group_id) ? " and g.group_id = '{$group_id}' " : "";
        }
        $table = "SELECT 
            cjoin.join_id,
            cjoin.student_id,
            date_format(cjoin.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
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
            CASE WHEN stu.student_birth_date IS NULL OR stu.student_birth_date = '' THEN ''
            ELSE CONCAT(TIMESTAMPDIFF(YEAR, stu.student_birth_date, CURDATE()), ' Yrs.') END as student_age,
            stu.student_username,
            stu.student_password,
            stu.student_password_key,
            stu.student_company,
            stu.student_position,
            g.group_id,
            g.group_name,
            date_format(cjoin.register_date, '%Y/%m/%d %H:%i:%s') as register_date
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
            array('db' => 'date_create', 'dt' => 'date_create'),
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
            array('db' => 'register_date', 'dt' => 'register_date'),
            array('db' => 'student_password', 'dt' => 'student_password','formatter' => function ($d, $row) {
                return ($d) ? decryptToken($d, $row['student_password_key']) : '';
            }),
        );
        $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db' => $db_name,'host' => $db_host);
        require($base_include.'/lib/ssp-subquery.class.php');
        echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
        exit();
    }
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
        $upload_dir = "uploads/{$comp_id}/classroom/student/";
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
        if (!empty($_POST['student_password'])) {
            $plain_password = $_POST['student_password'];
            $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $data['student_password'] = encryptToken($plain_password, $student_password_key);
            $data['student_password_key'] = $student_password_key;
        }
        if (empty($student_id)) {
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
            if ($data && !empty($data['student_image_profile'])) {
                $data['student_image_profile'] = GetUrl($data['student_image_profile']);
            }
            if ($data && !empty($data['student_card_front'])) {
                $data['student_card_front'] = GetUrl($data['student_card_front']);
            }
            if ($data && !empty($data['student_card_back'])) {
                $data['student_card_back'] = GetUrl($data['student_card_back']);
            }
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
    if(isset($_GET['action']) && $_GET['action'] == 'buildGroupSelect') {
		$keyword = trim($_GET['term']);
		$classroom_id = trim($_GET['classroom_id']);
		$search = ($keyword) ? " and group_name like '%{$keyword}%' " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                group_id as data_code,
                group_name as data_desc 
            from 
                classroom_group 
            where 
                classroom_id = '{$classroom_id}' and status = 0 $search
        ) data_table";
        $whereData = (($_GET['page']) ? "LIMIT ".$end.",".$start : "")."";
        $Data = select_data($columnData,$tableData,$whereData);
		$count_data = count($Data);
		$i = 0;
		while($i < $count_data) {
			$data[] = ['id' => $Data[$i]['data_code'],'col' => $Data[$i]['data_desc'],'total_count' => $count_data,'code' => $Data[$i]['data_code'],'desc' => $Data[$i]['data_desc'],];
			++$i;
		}
		if (empty($data)) {
			$data[] = ['id' => '','col' => '', 'total_count' => ''];
		}
        echo json_encode($data);
	}
    if(isset($_POST) && $_POST['action'] == 'addToGroup') {
        $classroom_id = $_POST['classroom_id'];
        $group_id = $_POST['group_id'];
        $student_id = $_POST['student_id'];
        update_data(
            "classroom_student_join", "group_id = '{$group_id}'", "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'"
        );
        echo json_encode(['status' => true]);
    }
    if(isset($_POST) && $_POST['action'] == 'removeFromGroup') {
        $classroom_id = $_POST['classroom_id'];
        $student_id = $_POST['student_id'];
        update_data(
            "classroom_student_join", "group_id = null", "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'"
        );
        echo json_encode(['status' => true]);
    }

// ส่วนที่ 1: ดึงข้อมูล Employee สำหรับแสดงใน Pop-up
if(isset($_POST['action']) && $_POST['action'] == 'getEmployees') {
    $table = "SELECT 
        e.emp_id, 
        ei.firstname_th, 
        ei.lastname_th,
        ei.tel_office,
        e.email,
        CONCAT(ei.firstname_th, ' ', ei.lastname_th) AS full_name
    FROM m_employee e
    LEFT JOIN m_employee_info ei ON ei.emp_id = e.emp_id
    WHERE e.emp_del = 0
    AND e.emp_id NOT IN (SELECT student_ref_id FROM classroom_student WHERE student_ref_type = 'employee')";
    
    $primaryKey = 'emp_id';
    $columns = array(
        array('db' => 'emp_id', 'dt' => 'emp_id'),
        array('db' => 'full_name', 'dt' => 'full_name'),
        array('db' => 'tel_office', 'dt' => 'tel_office'),
        array('db' => 'email', 'dt' => 'email'),
    );
    
    $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db' => $db_name,'host' => $db_host);
    require($base_include.'/lib/ssp-subquery.class.php');
    echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
    exit();
}

// ส่วนที่ 2: ดึงข้อมูล Customer สำหรับแสดงใน Pop-up
if(isset($_POST['action']) && $_POST['action'] == 'getCustomers') {
    $table = "SELECT
        cus_cont_id as cus_id,
        CONCAT(cus_cont_name, ' ', cus_cont_surname) AS cus_name_th,
        cus_cont_mob AS cus_tel_no,
        cus_cont_email AS cus_email
    FROM m_customer_contact c
    WHERE c.cus_cont_del = 0
    AND c.cus_cont_id NOT IN (SELECT student_ref_id FROM classroom_student WHERE student_ref_type = 'contact')";
    
    $primaryKey = 'cus_id';
    $columns = array(
        array('db' => 'cus_id', 'dt' => 'cus_id'),
        array('db' => 'cus_name_th', 'dt' => 'cus_name_th'),
        array('db' => 'cus_tel_no', 'dt' => 'cus_tel_no'),
        array('db' => 'cus_email', 'dt' => 'cus_email'),
    );
    
    $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db' => $db_name,'host' => $db_host);
    require($base_include.'/lib/ssp-subquery.class.php');
    echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
    exit();
}

// ส่วนที่ 3: บันทึกข้อมูลนักเรียนจาก Employee หรือ Customer (แก้ไขแล้ว)
if(isset($_POST['action']) && $_POST['action'] == 'addStudentFromRef') {
    global $mysqli;
    $ref_id = $_POST['ref_id'];
    $ref_type = $_POST['ref_type'];
    $classroom_id = $_POST['classroom_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $date_create = date('Y-m-d H:i:s');

    // ตรวจสอบว่ามีข้อมูลนักเรียนคนนี้อยู่ในตารางอยู่แล้วหรือไม่
    $check_sql = "SELECT student_id FROM classroom_student WHERE student_ref_id = ? AND student_ref_type = ?";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("ss", $ref_id, $ref_type);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $existing_student = $check_result->fetch_assoc();
        $student_id = $existing_student['student_id'];

        // ถ้ามีอยู่แล้วให้ตรวจสอบว่าได้มีการ join กับ classroom นี้แล้วหรือยัง
        $check_join_sql = "SELECT join_id FROM classroom_student_join WHERE classroom_id = ? AND student_id = ?";
        $check_join_stmt = $mysqli->prepare($check_join_sql);
        $check_join_stmt->bind_param("ss", $classroom_id, $student_id);
        $check_join_stmt->execute();
        $check_join_result = $check_join_stmt->get_result();

        if ($check_join_result->num_rows > 0) {
            echo json_encode(array('status' => 'error', 'message' => 'Student is already linked to this classroom.'));
            exit();
        }

        // ดึง comp_id จากตารางต้นทางเพื่อใช้ในการ join
        $comp_id = null;
        if ($ref_type === 'employee') {
            $sql_comp = "SELECT comp_id FROM m_employee WHERE emp_id = ?";
        } elseif ($ref_type === 'contact') {
            $sql_comp = "SELECT comp_id FROM m_customer WHERE cus_id = ?";
        }
        if (isset($sql_comp)) {
            $stmt_comp = $mysqli->prepare($sql_comp);
            $stmt_comp->bind_param("s", $ref_id);
            $stmt_comp->execute();
            $result_comp = $stmt_comp->get_result();
            $data_comp = $result_comp->fetch_assoc();
            $comp_id = $data_comp['comp_id'];
            $stmt_comp->close();
        }
        
        // ถ้ายังไม่มีการ join ก็ให้เพิ่มข้อมูลใน classroom_student_join
        $join_sql = "INSERT INTO classroom_student_join (classroom_id, student_id, comp_id, status, emp_create, date_create) 
                     VALUES (?, ?, ?, 0, ?, ?)";
        $join_stmt = $mysqli->prepare($join_sql);
        $join_stmt->bind_param("sssss", $classroom_id, $student_id, $comp_id, $user_id, $date_create);
        if ($join_stmt->execute()) {
            echo json_encode(array('status' => 'success', 'message' => 'Student successfully linked to this classroom.'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Error linking student: ' . $join_stmt->error));
        }
        $join_stmt->close();
        $check_stmt->close();
        exit();
    }

    // ถ้ายังไม่มีในตาราง classroom_student ให้ดึงข้อมูลมาสร้าง
    $data = null;
    if ($ref_type === 'employee') {
        $sql = "SELECT 
            ei.firstname_th AS student_firstname_th,
            ei.lastname_th AS student_lastname_th,
            ei.firstname AS student_firstname_en,
            ei.lastname AS student_lastname_en,
            ei.nickname AS student_nickname_th,
            ei.title AS student_perfix,
            ei.idcard AS student_idcard,
            e.email AS student_email,
            ei.tel_office AS student_mobile,
            ei.date_birth AS student_birth_date,
            ei.gender AS student_gender,
            e.comp_id,
            ei.emp_pic AS student_image_profile,
            e.emp_username AS student_username,
            '' AS student_position,
            '' AS student_company
        FROM m_employee e
        LEFT JOIN m_employee_info ei ON ei.emp_id = e.emp_id
        WHERE e.emp_id = ?";
     } elseif ($ref_type === 'contact') {
        $sql = "SELECT
            cus_cont_name AS student_firstname_th,
            cus_cont_surname AS student_lastname_th,
            cus_cont_email AS student_email,
            cus_cont_mob AS student_mobile,
            comp_id,
            '' AS student_company,
            '' AS student_position,
            -- cus_cont_gender AS student_gender,
            cus_cont_idcard AS student_idcard,
            cus_cont_date_birth AS student_birth_date,
            cus_cont_photo AS student_image_profile
        FROM m_customer_contact c
        WHERE c.cus_cont_id = ?";
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid reference type.'));
        exit();
    }

    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
    }
    $stmt->bind_param("s", $ref_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data) {
        $insert_data = array(
            'student_ref_id' => $ref_id,
            'student_ref_type' => $ref_type,
            'student_firstname_th' => isset($data['student_firstname_th']) ? $data['student_firstname_th'] : '',
            'student_lastname_th' => isset($data['student_lastname_th']) ? $data['student_lastname_th'] : '',
            'student_firstname_en' => isset($data['student_firstname_en']) ? $data['student_firstname_en'] : '',
            'student_lastname_en' => isset($data['student_lastname_en']) ? $data['student_lastname_en'] : '',
            'student_nickname_th' => isset($data['student_nickname_th']) ? $data['student_nickname_th'] : '',
            'student_gender' => isset($data['student_gender']) ? $data['student_gender'] : '',
            'student_idcard' => isset($data['student_idcard']) ? $data['student_idcard'] : null,
            'student_email' => isset($data['student_email']) ? $data['student_email'] : '',
            'student_mobile' => isset($data['student_mobile']) ? $data['student_mobile'] : '',
            'student_birth_date' => isset($data['student_birth_date']) ? $data['student_birth_date'] : '0000-00-00',
            'student_company' => isset($data['student_company']) ? $data['student_company'] : '',
            'student_position' => isset($data['student_position']) ? $data['student_position'] : '',
            'student_username' => isset($data['student_username']) ? $data['student_username'] : null,
            'student_image_profile' => isset($data['student_image_profile']) ? $data['student_image_profile'] : '',
            // 'comp_id' => isset($data['comp_id']) ? $data['comp_id'] : null, // ดึงจาก $data
            'emp_create' => $user_id,
            'date_create' => $date_create
        );

        $fields = implode(", ", array_keys($insert_data));
        $placeholders = implode(", ", array_fill(0, count($insert_data), '?'));
        $insert_sql = "INSERT INTO classroom_student ($fields) VALUES ($placeholders)";
        $insert_stmt = $mysqli->prepare($insert_sql);
        if ($insert_stmt === false) {
            die(json_encode(array('status' => 'error', 'message' => "Prepare failed: " . $mysqli->error)));
        }
        $types = str_repeat('s', count($insert_data));
        $values = array_values($insert_data);
        $bind_values = array($types);
        for ($i = 0; $i < count($values); $i++) {
            $bind_values[] = &$values[$i];
        }
        call_user_func_array(array($insert_stmt, 'bind_param'), $bind_values);

        if ($insert_stmt->execute()) {
            $new_student_id = $mysqli->insert_id;
            
            // เพิ่มข้อมูลลงใน classroom_student_join
            $join_sql = "INSERT INTO classroom_student_join (classroom_id, student_id, comp_id, status, emp_create, date_create) VALUES (?, ?, ?, 0, ?, ?)";
            $join_stmt = $mysqli->prepare($join_sql);
            $join_stmt->bind_param('sssss', $classroom_id, $new_student_id, $data['comp_id'], $user_id, $date_create); // ดึงจาก $data
            
            if ($join_stmt->execute()) {
                echo json_encode(array('status' => 'success', 'message' => 'New student added from ' . $ref_type . ' successfully.'));
            } else {
                echo json_encode(array('status' => 'error', 'message' => "Error inserting into join table: " . $join_stmt->error));
            }
            $join_stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => "Error inserting into student table: " . $insert_stmt->error));
        }
        $insert_stmt->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Data not found for the selected ' . $ref_type . '.'));
    }
    $mysqli->close();
    exit();
}
?>