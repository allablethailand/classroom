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
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);
    if(isset($_POST) && $_POST['action'] == 'buildRegistration') {
        $classroom_id = $_POST['classroom_id'];
        $filter_status = $_POST['filter_status'];
        $filter_date = $_POST['filter_date'];
        $filter = "";
        if($filter_date) {
            $date = explode('-',$filter_date);
            $date_st = trim($date[0]);
            $date_ed = (trim($date[1])) ? trim($date[1]) : trim($date[0]);
            $data_st = substr($date_st,-4).'-'.substr($date_st,3,2).'-'.substr($date_st,0,2);
            $data_ed = substr($date_ed,-4).'-'.substr($date_ed,3,2).'-'.substr($date_ed,0,2);
            $filter .= " and date(cjoin.register_date) between date('{$data_st}') and date('{$data_ed}') ";
        }
        switch($filter_status) {
            case 'lead':
                $filter .= " and cjoin.invite_status = 0 ";
            break;
            case 'register':
                $filter .= "";
            break;
            case 'waiting':
                $filter .= " and cjoin.invite_status = 1 and cjoin.approve_status = 0 ";
            break;
            case 'approve':
                $filter .= " and cjoin.invite_status = 1 and cjoin.approve_status = 1 and cjoin.payment_status = 0 ";
            break;
            case 'payment':
                $filter .= " and cjoin.invite_status = 1 and cjoin.approve_status = 1 and cjoin.payment_status = 1 ";
            break;
            case 'notapprove':
                $filter .= " and cjoin.invite_status = 1 and cjoin.approve_status = 2 ";
            break;
            case 'notpayment':
                $filter .= " and cjoin.invite_status = 1 and cjoin.approve_status = 1 and cjoin.payment_status = 2 ";
            break;
            case 'cancel':
                $filter .= " and cjoin.invite_status = 2 ";
            break;
        }
        $table = "SELECT 
            cjoin.join_id,
            date_format(cjoin.register_date, '%Y/%m/%d %H:%i:%s') as register_date,
            cjoin.register_by,
            cjoin.invite_status,
            date_format(cjoin.invite_date, '%Y/%m/%d %H:%i:%s') as invite_date,
            CONCAT(IFNULL(i_invite.firstname,i_invite.firstname_th),' ',IFNULL(i_invite.lastname,i_invite.lastname_th)) AS invite_by,
            ifnull(cjoin.approve_status, 0) as approve_status,
            date_format(cjoin.approve_date, '%Y/%m/%d %H:%i:%s') as approve_date,
            CONCAT(IFNULL(i_approve.firstname,i_approve.firstname_th),' ',IFNULL(i_approve.lastname,i_approve.lastname_th)) AS approve_by,
            ifnull(cjoin.payment_status, 0) as payment_status,
            date_format(cjoin.payment_status_date, '%Y/%m/%d %H:%i:%s') as payment_status_date,
            CONCAT(IFNULL(i_payment.firstname,i_payment.firstname_th),' ',IFNULL(i_payment.lastname,i_payment.lastname_th)) AS payment_status_by,
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
            stu.student_position
        FROM 
            classroom_student_join cjoin
        LEFT JOIN 
            classroom_student stu on stu.student_id = cjoin.student_id 
        LEFT JOIN 
            m_employee_info i_invite on i_invite.emp_id = cjoin.invite_by
        LEFT JOIN 
            m_employee_info i_approve on i_approve.emp_id = cjoin.approve_by
        LEFT JOIN 
            m_employee_info i_payment on i_payment.emp_id = cjoin.payment_status_by
        WHERE 
            cjoin.classroom_id = '{$classroom_id}' and cjoin.status = 0 $filter";
        $primaryKey = 'join_id';
        $columns = array(
            array('db' => 'join_id', 'dt' => 'join_id'),
            array('db' => 'register_date', 'dt' => 'register_date'),
            array('db' => 'register_by', 'dt' => 'register_by'),
            array('db' => 'invite_status', 'dt' => 'invite_status'),
            array('db' => 'invite_date', 'dt' => 'invite_date'),
            array('db' => 'invite_by', 'dt' => 'invite_by'),
            array('db' => 'approve_status', 'dt' => 'approve_status'),
            array('db' => 'approve_date', 'dt' => 'approve_date'),
            array('db' => 'approve_by', 'dt' => 'approve_by'),
            array('db' => 'payment_status', 'dt' => 'payment_status'),
            array('db' => 'payment_status_date', 'dt' => 'payment_status_date'),
            array('db' => 'payment_status_by', 'dt' => 'payment_status_by'),
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
            array('db' => 'student_password', 'dt' => 'student_password','formatter' => function ($d, $row) {
                return ($d) ? decryptToken($d, $row['student_password_key']) : '';
			}),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_GET) && $_GET['action'] == 'saveImport') {
        $classroom_id = $_POST['classroom_id'];
        require_once $base_include."/lib/excel/PHPExcel.php";
        $excel_name = $_FILES['excel_file']['name'];
        $excel_tmp = $_FILES['excel_file']['tmp_name'];
        $excelReader = PHPExcel_IOFactory::createReaderForFile($excel_tmp);
        $excelObj = $excelReader->load($excel_tmp);
        $worksheet = $excelObj->getSheet(0);
        $lastRow = $worksheet->getHighestRow(); 
        for ($row = 3; $row <= $lastRow; $row++) {
            if($worksheet->getCell('A'.$row)->getValue()) {
                $idcard = escape_string($worksheet->getCell('B'.$row)->getValue());
                $idcard_val = str_replace('-','',$idcard);
                $passport = escape_string($worksheet->getCell('C'.$row)->getValue());
                if($idcard_val || $passport) {
                    $firstname_en = escape_string($worksheet->getCell('D'.$row)->getValue());
                    $lastname_en = escape_string($worksheet->getCell('E'.$row)->getValue());
                    $firstname_th = escape_string($worksheet->getCell('F'.$row)->getValue());
                    $lastname_th = escape_string($worksheet->getCell('G'.$row)->getValue());
                    $gender = escape_string($worksheet->getCell('H'.$row)->getValue());
                    $gender_val = '';
                    switch($gender) {
                        case 'ชาย':
                        case 'Male':
                            $gender_val = 'M';
                        break;
                        case 'หญิง':
                        case 'Female':
                            $gender_val = 'F';
                        break;
                        default:
                            $gender_val = 'O';
                    }
                    $birthday = escape_string($worksheet->getCell('I'.$row)->getValue());
                    $birthday_val = convertToDate($birthday);
                    $company = escape_string($worksheet->getCell('J'.$row)->getValue());
                    $position = escape_string($worksheet->getCell('K'.$row)->getValue());
                    $mobile = escape_string($worksheet->getCell('L'.$row)->getValue());
                    $mobile_val = str_replace('-','',$mobile);
                    $email = escape_string($worksheet->getCell('M'.$row)->getValue());
                    $username = escape_string($worksheet->getCell('M'.$row)->getValue());
                    $password = escape_string($worksheet->getCell('N'.$row)->getValue());
                    $information = [
                        'idcard' => trim($idcard_val),
                        'passport' => trim($passport),
                        'firstname_en' => trim($firstname_en),
                        'lastname_en' => trim($lastname_en),
                        'firstname_th' => trim($firstname_th),
                        'lastname_th' => trim($lastname_th),
                        'gender' => trim($gender_val),
                        'birthday' => trim($birthday_val),
                        'mobile' => trim($mobile_val),
                        'email' => trim($email),
                        'company' => trim($company),
                        'position' => trim($position),
                        'username' => trim($username),
                        'password' => trim($password)
                    ];
                    $result = createStudent($classroom_id, $information);
                }
            }
            ++$i;
        }
        echo json_encode(['status' => true]);
    }
    function createStudent($classroom_id, $information) {
        $conditions = [];
        if (!empty($information['idcard'])) {
            $idcard = addslashes($information['idcard']);
            $conditions[] = "student_idcard = '$idcard'";
        }
        if (!empty($information['passport'])) {
            $passport = addslashes($information['passport']);
            $conditions[] = "student_idcard = '$passport'";
        }
        $exits_condition = '';
        if (count($conditions) > 0) {
            $exits_condition = " WHERE " . implode(" OR ", $conditions);
        }
        $exits = select_data(
            "student_id, student_password_key",
            "classroom_student",
            $exits_condition
        );
        $student_id = '';
        $student_password_key = '';
        if (!empty($exits)) {
            $student_id = $exits[0]['student_id'];
            $student_password_key = $exits[0]['student_password_key'];
        }
        if(!$student_password_key) {
            $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
        }
        if($information['password']) {
            $pwd = encryptToken($information['password'], $student_password_key);
            $password = "'{$pwd}'";
        } else {
            $password = "null";
        }
        if($student_id) {
            update_data(
                "classroom_student",
                "
                    student_firstname_en = '{$information['firstname_en']}',
                    student_lastname_en = '{$information['lastname_en']}',
                    student_firstname_th = '{$information['firstname_th']}',
                    student_lastname_th = '{$information['lastname_th']}',
                    student_gender = '{$information['gender']}',
                    student_idcard = '{$information['idcard']}',
                    student_passport = '{$information['passport']}',
                    student_birth_date = '{$information['birthday']}',
                    student_mobile = '{$information['mobile']}',
                    student_email = '{$information['email']}',
                    student_username = '{$information['username']}',
                    student_company = '{$information['company']}',
                    student_position = '{$information['position']}',
                    student_password = $password,
                    emp_modify = '{$_SESSION['emp_id']}',
                    status = 0,
                    date_modify = NOW()
                ",
                "student_id = '{$student_id}'"
            );
        } else {
            $student_id = insert_data(
                "classroom_student",
                "(
                    student_firstname_en,
                    student_lastname_en,
                    student_firstname_th,
                    student_lastname_th,
                    student_gender,
                    student_idcard,
                    student_passport,
                    student_email,
                    student_mobile,
                    student_birth_date,
                    student_company,
                    student_position,
                    student_username,
                    student_password,
                    student_password_key,
                    comp_id,
                    status,
                    emp_create,
                    date_create,
                    emp_modify,
                    date_modify
                )",
                "(
                    '{$information['firstname_en']}',
                    '{$information['lastname_en']}',
                    '{$information['firstname_th']}',
                    '{$information['lastname_th']}',
                    '{$information['gender']}',
                    '{$information['idcard']}',
                    '{$information['passport']}',
                    '{$information['email']}',
                    '{$information['mobile']}',
                    '{$information['birthday']}',
                    '{$information['company']}',
                    '{$information['position']}',
                    '{$information['username']}',
                    $password,
                    '{$student_password_key}',
                    '{$_SESSION['comp_id']}',
                    0,
                    '{$_SESSION['emp_id']}',
                    NOW(),
                    '{$_SESSION['emp_id']}',
                    NOW()
                )"
            );
        }
        if($student_id) {
            $exitsClass = select_data(
                "*",
                "classroom_student_join",
                "where student_id = '{$student_id}' and classroom_id = '{$classroom_id}'"
            );
            if (empty($exitsClass)) {
                insert_data(
                    "classroom_student_join",
                    "(
                        student_id,
                        classroom_id,
                        register_date,
                        register_by,
                        register_by_emp,
                        invite_date,
                        invite_by,
                        invite_status,
                        comp_id,
                        status,
                        emp_create,
                        date_create,
                        emp_modify,
                        date_modify
                    )",
                    "(
                        '{$student_id}',
                        '{$classroom_id}',
                        NOW(),
                        1,
                        '{$_SESSION['emp_id']}',
                        NOW(),
                        '{$_SESSION['emp_id']}',
                        0,
                        '{$_SESSION['comp_id']}',
                        0,
                        '{$_SESSION['emp_id']}',
                        NOW(),
                        '{$_SESSION['emp_id']}',
                        NOW()
                    )"
                );
            }
        }
        return $student_id;
    }
    function convertToDate($date) {
        $date = trim($date);
        if (!is_string($date)) {
            $date = (string) $date;
        }
        $dateTime = DateTime::createFromFormat('!d/m/Y', $date) ?: DateTime::createFromFormat('!m/d/Y', $date) ?: DateTime::createFromFormat('!Y-m-d', $date);
        if ($dateTime === false) {
            return false;
        }
        return $dateTime->format('Y-m-d');
    }
    if(isset($_POST) && $_POST['action'] == 'confirmRegistration') {
        $join_id = $_POST['join_id'];
        $option = $_POST['option'];
        $status = 0;
        switch($option) {
            case 'W':
                $status = 0;
            break;
            case 'Y':
                $status = 1;
            break;
            case 'N':
                $status = 2;
            break;
        }
        update_data(
            "classroom_student_join",
            "invite_status = $status, invite_date = NOW(), invite_by = '{$_SESSION['emp_id']}'",
            "join_id = '{$join_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'approveRegistration') {
        $join_id = $_POST['join_id'];
        $option = $_POST['option'];
        $status = 0;
        switch($option) {
            case 'W':
                $status = 0;
            break;
            case 'Y':
                $status = 1;
            break;
            case 'N':
                $status = 2;
            break;
        }
        update_data(
            "classroom_student_join",
            "approve_status = $status, approve_date = NOW(), approve_by = '{$_SESSION['emp_id']}'",
            "join_id = '{$join_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'paymentRegistration') {
        $join_id = $_POST['join_id'];
        $option = $_POST['option'];
        $status = 0;
        switch($option) {
            case 'W':
                $status = 0;
            break;
            case 'Y':
                $status = 1;
            break;
            case 'N':
                $status = 2;
            break;
        }
        update_data(
            "classroom_student_join",
            "payment_status = $status, payment_status_date = NOW(), payment_status_by = '{$_SESSION['emp_id']}'",
            "join_id = '{$join_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'delRegistration') {
        $join_id = $_POST['join_id'];
        update_data(
            "classroom_student_join",
            "status = 1, date_modify = NOW(), emp_modify = '{$_SESSION['emp_id']}'",
            "join_id = '{$join_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if (isset($_POST['action']) && $_POST['action'] === 'buildSummaryRegistration') {
        $classroom_id = $_POST['classroom_id'];
        $filter_date  = !empty($_POST['filter_date']) ? $_POST['filter_date'] : '';
        $filter = '';
        if ($filter_date) {
            $date     = explode('-', $filter_date);
            $date_st  = trim($date[0]);
            $date_ed  = !empty($date[1]) ? trim($date[1]) : $date_st;
            $data_st  = substr($date_st, -4) . '-' . substr($date_st, 3, 2) . '-' . substr($date_st, 0, 2);
            $data_ed  = substr($date_ed, -4) . '-' . substr($date_ed, 3, 2) . '-' . substr($date_ed, 0, 2);
            $filter   = " AND DATE(cjoin.register_date) BETWEEN DATE('{$data_st}') AND DATE('{$data_ed}')";
        }
        $queries = [
            'lead'       => "invite_status = 0",
            'register'   => "1=1",
            'waiting'    => "invite_status = 1 AND approve_status = 0",
            'approve'    => "invite_status = 1 AND approve_status = 1 AND payment_status = 0",
            'payment'    => "invite_status = 1 AND approve_status = 1 AND payment_status = 1",
            'notapprove' => "invite_status = 1 AND approve_status = 2",
            'notpayment' => "invite_status = 1 AND approve_status = 1 AND payment_status = 2",
            'cancel'     => "invite_status = 2",
        ];
        $summary = [];
        foreach ($queries as $key => $condition) {
            $result = select_data(
                "join_id",
                "classroom_student_join cjoin",
                "WHERE classroom_id = '{$classroom_id}' AND status = 0 {$filter} AND {$condition}"
            );
            $summary[$key] = number_format(count($result));
        }
        echo json_encode([
            'status'       => true,
            'summary_data' => $summary
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'getClassroomKey') {
        $classroom_id = $_POST['classroom_id'];
        $classroom = select_data(
            "classroom_key",
            "classroom_template",
            "where classroom_id = '{$classroom_id}'"
        );
        echo json_encode([
            'status'       => true,
            'classroom_key' => $classroom[0]['classroom_key']
        ]);
    }
?>