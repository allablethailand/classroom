<?php
    session_start();
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if($_SERVER['HTTP_HOST'] == 'localhost') {
        $request_uri = $_SERVER['REQUEST_URI'];
        $exl_path = explode('/',$request_uri);
        if(!file_exists($base_include."/dashboard.php")) {
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
        $filter_channel = $_POST['filter_channel'];
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
        if($filter_channel) {
            $filter .= " and cjoin.channel_id = '{$filter_channel}' ";
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
            case
                when stu.student_perfix = 'Other' then stu.student_perfix_other
                else stu.student_perfix
            end as student_perfix,
            case
                when stu.student_perfix = 'Other' then stu.student_perfix_other
                when stu.student_perfix = 'Mr.' then 'นาย'
                when stu.student_perfix = 'Mrs.' then 'นาง'
                when stu.student_perfix = 'Miss' then 'นางสาว'
            end as student_perfix_th,
            stu.student_firstname_en,
            stu.student_lastname_en,
            stu.student_firstname_th,
            stu.student_lastname_th,
            stu.student_nickname_en,
            stu.student_nickname_th,
            stu.student_gender,
            stu.student_idcard,
            stu.student_passport,
            stu.student_passport_expire,
            stu.student_image_profile,
            stu.student_email,
            concat(stu.dial_code,'',stu.student_mobile) as student_mobile,
            stu.student_company,
            stu.student_position,
            stu.student_username,
            stu.student_password,
            n.nationality_name,
            stu.student_reference,
            date_format(stu.student_birth_date, '%Y/%m/%d') as student_birth_date,
            CASE  WHEN stu.student_birth_date IS NULL OR stu.student_birth_date = '' THEN ''
            ELSE CONCAT(TIMESTAMPDIFF(YEAR, stu.student_birth_date, CURDATE()), ' Yrs.') END as student_age,
            stu.student_password_key,
            c.channel_name,
            cjoin.student_id,
            stu.copy_of_idcard,
            stu.copy_of_passport,
            stu.work_certificate,
            stu.company_certificate
        FROM 
            classroom_student_join cjoin
        LEFT JOIN 
            classroom_student stu on stu.student_id = cjoin.student_id 
        LEFT JOIN 
            m_nationality n on n.nationality_id = stu.student_nationality
        LEFT JOIN 
            m_employee_info i_invite on i_invite.emp_id = cjoin.invite_by
        LEFT JOIN 
            m_employee_info i_approve on i_approve.emp_id = cjoin.approve_by
        LEFT JOIN 
            m_employee_info i_payment on i_payment.emp_id = cjoin.payment_status_by
        LEFT JOIN 
            classroom_channel c on c.channel_id = cjoin.channel_id
        WHERE 
            cjoin.classroom_id = '{$classroom_id}' and cjoin.status = 0 $filter";
        $primaryKey = 'join_id';
        $columns = array(
            array('db' => 'join_id', 'dt' => 'join_id'),
            array('db' => 'student_id', 'dt' => 'student_id'),
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
            array('db' => 'student_perfix', 'dt' => 'student_perfix'),
            array('db' => 'student_perfix_th', 'dt' => 'student_perfix_th'),
            array('db' => 'student_firstname_en', 'dt' => 'student_firstname_en'),
            array('db' => 'student_lastname_en', 'dt' => 'student_lastname_en'),
            array('db' => 'student_firstname_th', 'dt' => 'student_firstname_th'),
            array('db' => 'student_lastname_th', 'dt' => 'student_lastname_th'),
            array('db' => 'student_nickname_en', 'dt' => 'student_nickname_en'),
            array('db' => 'student_nickname_th', 'dt' => 'student_nickname_th'),
            array('db' => 'student_gender', 'dt' => 'student_gender'),
            array('db' => 'student_idcard', 'dt' => 'student_idcard'),
            array('db' => 'student_passport', 'dt' => 'student_passport'),
            array('db' => 'student_passport_expire', 'dt' => 'student_passport_expire'),
            array('db' => 'student_image_profile','dt' => 'student_image_profile','formatter' => function ($d, $row) {
                return ($d) ? GetPublicUrl($d) : '';
            }),
            array('db' => 'copy_of_idcard','dt' => 'copy_of_idcard','formatter' => function ($d, $row) {
                return ($d) ? GetPublicUrl($d) : '';
            }),
            array('db' => 'copy_of_passport','dt' => 'copy_of_passport','formatter' => function ($d, $row) {
                return ($d) ? GetPublicUrl($d) : '';
            }),
            array('db' => 'work_certificate','dt' => 'work_certificate','formatter' => function ($d, $row) {
                return ($d) ? GetPublicUrl($d) : '';
            }),
            array('db' => 'company_certificate','dt' => 'company_certificate','formatter' => function ($d, $row) {
                return ($d) ? GetPublicUrl($d) : '';
            }),
            array('db' => 'student_email', 'dt' => 'student_email'),
            array('db' => 'student_mobile', 'dt' => 'student_mobile'),
            array('db' => 'student_company', 'dt' => 'student_company'),
            array('db' => 'student_position', 'dt' => 'student_position'),
            array('db' => 'student_username', 'dt' => 'student_username'),
            array('db' => 'student_password_key', 'dt' => 'student_password_key'),
            array('db' => 'student_password','dt' => 'student_password','formatter' => function ($d, $row) {
                return ($d) ? decryptToken($d, $row['student_password_key']) : '';
            }),
            array('db' => 'student_reference', 'dt' => 'student_reference'),
            array('db' => 'student_birth_date', 'dt' => 'student_birth_date'),
            array('db' => 'student_age', 'dt' => 'student_age'),
            array('db' => 'channel_name', 'dt' => 'channel_name')
        );
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if (isset($_GET) && $_GET['action'] == 'saveImport') {
        try {
            if (!isset($_POST['classroom_id']) || empty($_POST['classroom_id']) || !is_numeric($_POST['classroom_id'])) {
                throw new Exception('Invalid classroom ID');
            }
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }
            $classroom_id = intval($_POST['classroom_id']);
            $allowed_types = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            $file_type = $_FILES['excel_file']['type'];
            $file_size = $_FILES['excel_file']['size'];
            $max_size = 10 * 1024 * 1024; 
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Invalid file type. Only Excel files are allowed.');
            }
            if ($file_size > $max_size) {
                throw new Exception('File size too large. Maximum 10MB allowed.');
            }
            require_once $base_include . "/lib/excel/PHPExcel.php";
            $excel_tmp = $_FILES['excel_file']['tmp_name'];
            if (!file_exists($excel_tmp)) {
                throw new Exception('Uploaded file not found');
            }
            $excelReader = PHPExcel_IOFactory::createReaderForFile($excel_tmp);
            $excelReader->setReadDataOnly(true);
            $excelObj = $excelReader->load($excel_tmp);
            $worksheet = $excelObj->getSheet(0);
            $lastRow = $worksheet->getHighestRow();
            if ($lastRow < 6) {
                throw new Exception('Excel file must have data starting from row 6');
            }
            $registerTemplate = select_data(
                "register_template", "classroom_template", "WHERE classroom_id = '{$classroom_id}'"
            );
            if (empty($registerTemplate)) {
                throw new Exception('Classroom template not found');
            }
            $register_template = $registerTemplate[0]['register_template'] ? explode(',', $registerTemplate[0]['register_template']) : [];
            if (empty($register_template)) {
                throw new Exception('No register template fields found');
            }
            $columnInsert = [];
            foreach ($register_template as $row) {
                $columns = select_data(
                    "templace_column", 
                    "classroom_register_template", 
                    "WHERE template_id = '{$row}'"
                );
                if ($columns) {
                    $columnInsert[] = $columns[0]['templace_column'];
                }
            }
            $form = select_data("form_id", "classroom_forms", "WHERE classroom_id = '{$classroom_id}'");
            $form_id = !empty($form) ? $form[0]['form_id'] : null;
            $questions = [];
            if ($form_id) {
                $questions = select_data(
                    "question_id, question_text, question_type, has_options, has_required, has_other_option",
                    "classroom_form_questions",
                    "WHERE form_id = '{$form_id}' ORDER BY question_id ASC"
                );
            }
            $colChar = range('A', 'Z');
            $doubleColChar = [];
            foreach ($colChar as $c1) {
                foreach ($colChar as $c2) {
                    $doubleColChar[] = $c1 . $c2;
                }
            }
            $allColumns = array_merge($colChar, $doubleColChar);
            $map_columns = [];
            foreach ($columnInsert as $i => $field) {
                if (isset($allColumns[$i])) {
                    $map_columns[$field] = $allColumns[$i];
                }
            }
            $offset = count($map_columns);
            foreach ($questions as $i => $q) {
                if (isset($allColumns[$offset + $i])) {
                    $map_columns["form_q_" . $q['question_id']] = $allColumns[$offset + $i];
                }
            }
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            mysqli_autocommit($mysqli, false);
            for ($row = 6; $row <= $lastRow; $row++) {
                try {
                    $has_data = false;
                    foreach ($map_columns as $field => $col_char) {
                        $cell_value = $worksheet->getCell($col_char . $row)->getValue();
                        if (!empty(trim($cell_value))) {
                            $has_data = true;
                            break;
                        }
                    }
                    if (!$has_data) {
                        continue;
                    }
                    $studentData = [];
                    $formData = [];
                    foreach ($map_columns as $field => $col_char) {
                        $cell_value = $worksheet->getCell($col_char . $row)->getValue();
                        if ($cell_value instanceof PHPExcel_RichText) {
                            $cell_value = $cell_value->getPlainText();
                        } elseif (PHPExcel_Shared_Date::isDateTime($worksheet->getCell($col_char . $row))) {
                            $cell_value = PHPExcel_Shared_Date::ExcelToPHP($cell_value);
                            $cell_value = date('Y-m-d', $cell_value);
                        }
                        $cell_value = trim($cell_value);
                        $cell_value = escape_string($cell_value);
                        if (strpos($field, 'form_q_') === 0) {
                            $qid = str_replace('form_q_', '', $field);
                            $formData[$qid] = $cell_value;
                        } else {
                            $studentData[$field] = $cell_value;
                        }
                    }
                    $student_id = createStudent($classroom_id, $studentData);
                    if ($student_id && !empty($formData)) {
                        saveFormAnswers($classroom_id, $student_id, $form_id, $formData);
                    }
                    if ($student_id) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = "Row {$row}: Failed to create student record";
                    }
                } catch (Exception $e) {
                    $error_count++;
                    $errors[] = "Row {$row}: " . $e->getMessage();
                    error_log("Import error at row {$row}: " . $e->getMessage());
                }
            }
            if ($error_count == 0) {
                mysqli_commit($mysqli);
                echo json_encode([
                    'status' => true,
                    'message' => "Import successful! {$success_count} records imported.",
                    'success_count' => $success_count,
                    'error_count' => $error_count
                ]);
            } else {
                mysqli_rollback($mysqli);
                echo json_encode([
                    'status' => false,
                    'message' => "Import completed with errors. {$success_count} successful, {$error_count} failed.",
                    'success_count' => $success_count,
                    'error_count' => $error_count,
                    'errors' => array_slice($errors, 0, 10) 
                ]);
            }
        } catch (Exception $e) {
            if (isset($mysqli)) {
                mysqli_rollback($mysqli);
            }
            error_log("Import Excel Error: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage(),
                'success_count' => 0,
                'error_count' => 0
            ]);
        } finally {
            if (isset($mysqli)) {
                mysqli_autocommit($mysqli, true);
            }
        }
    }
    function saveFormAnswers($classroom_id, $student_id, $form_id, $formData) {
        if (!$form_id || !$student_id) return false;
        try {
            $exists = select_data(
                "*",
                "classroom_form_question_users",
                "WHERE user_id = '{$student_id}' AND form_id = '{$form_id}'"
            );
            if (empty($exists)) {
                insert_data(
                    "classroom_form_question_users",
                    "(user_id, form_id, question_list, date_create)",
                    "('{$student_id}', '{$form_id}', null, NOW())"
                );
            }
            $q_list = [];
            foreach ($formData as $question_id => $answer) {
                if (empty(trim($answer))) continue;
                $q_list[] = $question_id;
                $qInfo = select_data(
                    "question_type, has_other_option",
                    "classroom_form_questions",
                    "WHERE question_id = '{$question_id}' AND form_id = '{$form_id}'"
                );
                if (!$qInfo) continue;
                $type = $qInfo[0]['question_type'];
                $has_other = $qInfo[0]['has_other_option'];
                delete_data(
                    "classroom_form_answer_users",
                    "student_id = '{$student_id}' AND question_id = '{$question_id}'"
                );
                switch ($type) {
                    case 'short_answer':
                    case 'paragraph':
                        saveAnswer($classroom_id, $student_id, $question_id, 0, $answer, '');
                        break;
                    case 'checkbox':
                        $answers = array_map('trim', explode(',', $answer));
                        foreach ($answers as $a) {
                            if ($a !== '') {
                                if ($has_other && strpos($a, 'other:') === 0) {
                                    $other_text = trim(substr($a, 6));
                                    saveAnswer($classroom_id, $student_id, $question_id, 2, '', $other_text);
                                } else {
                                    saveAnswer($classroom_id, $student_id, $question_id, 1, $a, '');
                                }
                            }
                        }
                        break;
                    case 'multiple_choice':
                    case 'radio':
                        if ($has_other && strpos($answer, 'other:') === 0) {
                            $other_text = trim(substr($answer, 6));
                            saveAnswer($classroom_id, $student_id, $question_id, 2, '', $other_text);
                        } else {
                            saveAnswer($classroom_id, $student_id, $question_id, 1, $answer, '');
                        }
                        break;
                    case 'dropdown':
                        saveAnswer($classroom_id, $student_id, $question_id, 1, $answer, '');
                        break;
                    default:
                        saveAnswer($classroom_id, $student_id, $question_id, 0, $answer, '');
                        break;
                }
            }
            if (!empty($q_list)) {
                $q_no = implode(',', $q_list);
                update_data(
                    "classroom_form_question_users",
                    "question_list = '{$q_no}'",
                    "user_id = '{$student_id}' AND form_id = '{$form_id}'"
                );
            }
            return true;
        } catch (Exception $e) {
            error_log("saveFormAnswers Error: " . $e->getMessage());
            return false;
        }
    }
    function saveAnswer($classroom_id, $student_id, $question_id, $answer_type, $answer, $other_text) {
        try {
            $answer = escape_string($answer);
            $other_text = escape_string($other_text);
            $answer_text = ($answer_type == 0 && $answer !== '') ? "'{$answer}'" : "null";
            $choice_id   = ($answer_type > 0 && $answer !== '') ? "'{$answer}'" : "null";
            $other       = ($other_text !== '') ? "'{$other_text}'" : "null";
            $is_other    = ($other_text !== '') ? 1 : 0;
            $result = insert_data(
                "classroom_form_answer_users",
                "(
                    answer_text,
                    question_id,
                    answer_type,
                    choice_id,
                    other_text,
                    is_other,
                    classroom_id,
                    student_id,
                    create_date,
                    date_update,
                    status
                )",
                "(
                    $answer_text,
                    '{$question_id}',
                    '{$answer_type}',
                    $choice_id,
                    $other,
                    '{$is_other}',
                    '{$classroom_id}',
                    '{$student_id}',
                    NOW(),
                    NOW(),
                    0
                )"
            );
            return $result !== false;
        } catch (Exception $e) {
            error_log("saveAnswer Error: " . $e->getMessage());
            return false;
        }
    }
    function createStudent($classroom_id, $information) {
        try {
            $student = [
                'student_idcard' => !empty($information['idcard']) ? str_replace(['-', ' '], '', escape_string($information['idcard'])) : '',
                'student_passport' => !empty($information['passport']) ? escape_string($information['passport']) : '',
                'student_perfix' => !empty($information['perfix']) ? escape_string($information['perfix']) : '',
                'student_firstname_en' => !empty($information['firstname_en']) ? escape_string($information['firstname_en']) : '',
                'student_lastname_en' => !empty($information['lastname_en']) ? escape_string($information['lastname_en']) : '',
                'student_firstname_th' => !empty($information['firstname_th']) ? escape_string($information['firstname_th']) : '',
                'student_lastname_th' => !empty($information['lastname_th']) ? escape_string($information['lastname_th']) : '',
                'student_nickname_en' => !empty($information['nickname_en']) ? escape_string($information['nickname_en']) : '',
                'student_nickname_th' => !empty($information['nickname_th']) ? escape_string($information['nickname_th']) : '',
                'student_gender' => !empty($information['gender']) ? escape_string($information['gender']) : 'O',
                'student_birth_date' => !empty($information['birthday']) ? convertToDate($information['birthday']) : null,
                'student_nationality' => !empty($information['nationality']) ? escape_string($information['nationality']) : '',
                'student_image_profile'=> !empty($information['image_profile']) ? escape_string($information['image_profile']) : '',
                'student_email' => !empty($information['email']) ? strtolower(escape_string($information['email'])) : '',
                'student_mobile' => !empty($information['mobile']) ? str_replace(['-', ' ', '(', ')'], '', escape_string($information['mobile'])) : '',
                'student_company' => !empty($information['company']) ? scape_string($information['company']) : '',
                'student_position' => !empty($information['position']) ? escape_string($information['position']) : '',
                'student_username' => !empty($information['username']) ? escape_string($information['username']) : '',
                'student_password' => !empty($information['password']) ? $information['password'] : null,
                'copy_of_idcard' => !empty($information['copy_of_idcard']) ? escape_string($information['copy_of_idcard']) : '',
                'copy_of_passport' => !empty($information['copy_of_passport']) ? escape_string($information['copy_of_passport']) : '',
                'work_certificate' => !empty($information['work_certificate']) ? escape_string($information['work_certificate']) : '',
                'company_certificate' => !empty($information['company_certificate']) ? escape_string($information['company_certificate']) : ''
            ];
            $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $student_password = $student['student_password'] ? encryptToken($student['student_password'], $student_password_key) : null;
            $conditions = [];
            if ($student['student_idcard']) {
                $conditions[] = "student_idcard = '{$student['student_idcard']}'";
            }
            if ($student['student_passport']) {
                $conditions[] = "student_passport = '{$student['student_passport']}'";
            }
            if ($student['student_email']) {
                $conditions[] = "student_email = '{$student['student_email']}'";
            }
            if (!empty($student['student_mobile'])) {
                $mobile = preg_replace('/^0/', '', $student['student_mobile']); 
                $conditions[] = "student_mobile = '{$mobile}'";
            }
            $exists_condition = count($conditions) ? " WHERE " . implode(" OR ", $conditions) : '';
            $exists = select_data("student_id, student_password_key", "classroom_student", $exists_condition);
            if (!empty($exists)) {
                $student_id = $exists[0]['student_id'];
                $student_password_key = $exists[0]['student_password_key'] ?: $student_password_key;
                $update_sql = [];
                foreach ($student as $key => $val) {
                    if ($key == 'student_password') {
                        if ($val) {
                            $encrypted_pwd = encryptToken($val, $student_password_key);
                            $update_sql[] = "$key='$encrypted_pwd'";
                        }
                    } else {
                        $update_sql[] = "$key='{$val}'";
                    }
                }
                if (!empty($_SESSION['emp_id'])) {
                    $update_sql[] = "emp_modify='{$_SESSION['emp_id']}'";
                }
                $update_sql[] = "date_modify=NOW()";
                update_data("classroom_student", implode(',', $update_sql), "student_id='{$student_id}'");
            } else {
                $fields = array_keys($student);
                $values = [];
                foreach ($student as $key => $val) {
                    if ($key == 'student_password') {
                        $values[] = $val ? "'$student_password'" : "NULL";
                    } else {
                        $values[] = "'$val'";
                    }
                }
                $fields[] = 'student_password_key';
                $fields[] = 'status';
                $fields[] = 'emp_create';
                $fields[] = 'date_create';
                $fields[] = 'emp_modify';
                $fields[] = 'date_modify';
                $values[] = "'$student_password_key'";
                $values[] = 0;
                $values[] = !empty($_SESSION['emp_id']) ? "'{$_SESSION['emp_id']}'" : "NULL";
                $values[] = "NOW()";
                $values[] = !empty($_SESSION['emp_id']) ? "'{$_SESSION['emp_id']}'" : "NULL";
                $values[] = "NOW()";
                $student_id = insert_data(
                    "classroom_student",
                    "(" . implode(',', $fields) . ")",
                    "(" . implode(',', $values) . ")"
                );
            }
            if ($student_id) {
                $existsClass = select_data(
                    "*", 
                    "classroom_student_join", 
                    "WHERE student_id='{$student_id}' AND classroom_id='{$classroom_id}'"
                );
                if (empty($existsClass)) {
                    insert_data(
                        "classroom_student_join",
                        "(student_id,classroom_id,register_date,register_by,register_by_emp,invite_date,invite_by,invite_status,comp_id,status,emp_create,date_create,emp_modify,date_modify)",
                        "('{$student_id}','{$classroom_id}',NOW(),1,'" . $_SESSION['emp_id'] . "',NOW(),'" . $_SESSION['emp_id'] . "',0,'" . $_SESSION['comp_id'] . "',0,'" . $_SESSION['emp_id'] . "',NOW(),'" . $_SESSION['emp_id'] . "',NOW())"
                    );
                }
            }
            return $student_id;
        } catch (Exception $e) {
            error_log("createStudent Error: " . $e->getMessage());
            return false;
        }
    }
    function convertToDate($date_string) {
        if (empty($date_string)) return null;
        try {
            if (is_numeric($date_string)) {
                $unix_date = ($date_string - 25569) * 86400;
                return date('Y-m-d', $unix_date);
            }
            $formats = [
                'Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y',
                'Y-m-d H:i:s', 'Y/m/d H:i:s',
                'd/m/Y H:i:s', 'd-m-Y H:i:s'
            ];
            foreach ($formats as $format) {
                $date = DateTime::createFromFormat($format, $date_string);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            }
            $timestamp = strtotime($date_string);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
            return null;
        } catch (Exception $e) {
            error_log("Date conversion error: " . $e->getMessage());
            return null;
        }
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
        $filter_channel = $_POST['filter_channel'];
        $filter_date  = !empty($_POST['filter_date']) ? $_POST['filter_date'] : '';
        $filter = '';
        if ($filter_date) {
            $date     = explode('-', $filter_date);
            $date_st  = trim($date[0]);
            $date_ed  = !empty($date[1]) ? trim($date[1]) : $date_st;
            $data_st  = substr($date_st, -4) . '-' . substr($date_st, 3, 2) . '-' . substr($date_st, 0, 2);
            $data_ed  = substr($date_ed, -4) . '-' . substr($date_ed, 3, 2) . '-' . substr($date_ed, 0, 2);
            $filter .= " AND DATE(cjoin.register_date) BETWEEN DATE('{$data_st}') AND DATE('{$data_ed}')";
        }
        if($filter_channel) {
            $filter .= " and cjoin.channel_id = '{$filter_channel}' ";
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
            'status'        => true,
            'classroom_key' => $classroom[0]['classroom_key']
        ]);
    }
    if(isset($_GET['action']) && $_GET['action'] == 'buildChannel') {
		$classroom_id = trim($_GET['classroom_id']);
		$keyword = trim($_GET['term']);
		$search = ($keyword) ? " and channel_name like '%{$keyword}%' " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                channel_id as data_code,
                channel_name as data_desc 
            from 
                classroom_channel 
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
    if (isset($_POST) && $_POST['action'] == 'dataRegistration') {
        $classroom_id = (!empty($_POST['classroom_id'])) ? escape_string($_POST['classroom_id']) : ''; 
        $classrooms = select_data(
            "
                template.classroom_id, 
                template.classroom_name, 
                template.classroom_information, 
                template.classroom_poster, 
                date_format(template.classroom_start, '%d %b %Y') as classroom_start_date, 
                date_format(template.classroom_start, '%H:%i') as classroom_start_time, 
                date_format(template.classroom_end, '%d %b %Y') as classroom_end_date, 
                date_format(template.classroom_end, '%H:%i') as classroom_end_time, 
                template.classroom_type,
                (
                    case
                        when template.classroom_type = 'online' then pf.platforms_name
                        else template.classroom_plateform
                    end
                ) as classroom_place,
                template.classroom_source,
                template.contact_us,
                comp.comp_id,
                comp.comp_logo,
                comp.comp_logo_target,
                template.classroom_allow_register
            ",
            "classroom_template template",
            "
                left join data_meeting_platforms pf on pf.platforms_id = template.classroom_plateform
                left join m_company comp on comp.comp_id = template.comp_id
                where template.classroom_id = '" . escape_string($classroom_id) . "' and template.status = 0 
            "
        );
        if (empty($classrooms)) {
            echo json_encode(array(
                'status' => false,
                'message' => 'Classroom not found'
            ));
            exit;
        }
        $classroom = $classrooms[0];
        $classroom_data = array(
            'classroom_id' => $classroom['classroom_id'], 
            'classroom_name' => $classroom['classroom_name'], 
            'classroom_information' => $classroom['classroom_information'], 
            'classroom_poster' => ($classroom['classroom_poster']) ? GetPublicUrl($classroom['classroom_poster']) : '', 
            'classroom_start_date' => $classroom['classroom_start_date'], 
            'classroom_start_time' => $classroom['classroom_start_time'], 
            'classroom_end_date' => $classroom['classroom_end_date'], 
            'classroom_end_time' => $classroom['classroom_end_time'], 
            'classroom_type' => $classroom['classroom_type'], 
            'classroom_place' => $classroom['classroom_place'], 
            'classroom_source' => $classroom['classroom_source'], 
            'contact_us' => $classroom['contact_us'], 
            'tenant_key' => $tenant_key, 
            'comp_logo' => ($classroom['comp_logo']) ? (($classroom['comp_logo_target'] == 0) ? '/' . $classroom['comp_logo'] : GetPublicUrl($classroom['comp_logo'])) : '', 
            'classroom_allow_register' => $classroom['classroom_allow_register']
        );
        $register_forms = select_data(
            "register_template, register_require, shortcut_status, shortcut_field, shortcut_require",
            "classroom_template",
            "where classroom_id = '" . escape_string($classroom['classroom_id']) . "'"
        );
        $shortcut_status = $register_forms[0]['shortcut_status'];
        $shortcut_field = explode(',', $register_forms[0]['shortcut_field']);
        $shortcut_require = explode(',', $register_forms[0]['shortcut_require']);
        $register_template = explode(',', $register_forms[0]['register_template']);
        $register_require = explode(',', $register_forms[0]['register_require']);
        $forms = select_data(
            "form_id",
            "classroom_forms",
            "where classroom_id = '" . escape_string($classroom['classroom_id']) . "'"
        );
        $form_data = array();
        if(!empty($forms)) {
            $form = $forms[0];
            $form_id = $form['form_id'];
            $questions = select_data(
                "question_id, question_text, question_type, has_other_option, has_options, has_required, `order`",
                "classroom_form_questions",
                "where form_id = '" . escape_string($form_id) . "' and status = 0 order by `order` asc"
            );
            foreach($questions as $q) {
                $question_id = $q['question_id'];
                $question_text = $q['question_text'];
                $question_type = $q['question_type'];
                $has_other_option = $q['has_other_option'];
                $has_options = $q['has_options'];
                $has_required = $q['has_required'];
                $option_item = array();
                if($has_options == 1) {
                    $items = select_data(
                        "choice_id, choice_text",
                        "classroom_form_choices",
                        "where question_id = '" . escape_string($question_id) . "' and status = 0 order by choice_id asc"
                    );
                    foreach($items as $i) {
                        $option_item[] = array(
                            'choice_id' => $i['choice_id'],
                            'choice_text' => $i['choice_text']
                        );
                    }
                }
                $form_data[] = array(
                    'question_id' => $question_id,
                    'question_text' => $question_text,
                    'question_type' => $question_type,
                    'has_other_option' => $has_other_option,
                    'has_options' => $has_options,
                    'has_required' => $has_required,
                    'option_item' => $option_item
                );
            }
        }
        $nationals = select_data(
            "nationality_id, nationality_name", 
            "m_nationality", 
            "where LOWER(nationality_name) = LOWER('Thai')"
        );
        $nationality = array(
            'nationality_id' => isset($nationals[0]['nationality_id']) ? $nationals[0]['nationality_id'] : '',
            'nationality_name' => isset($nationals[0]['nationality_name']) ? $nationals[0]['nationality_name'] : 'Thai'
        );
        $student_data = null;
        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
        if($student_id) {
            $students = select_data(
                "
                    student_id,
                    student_firstname_en,
                    student_lastname_en,
                    student_nickname_en,
                    student_nickname_th,
                    student_firstname_th,
                    student_lastname_th,
                    student_gender,
                    student_email,
                    dial_code,
                    student_mobile,
                    student_company,
                    student_position,
                    student_username,
                    date_format(student_birth_date, '%Y/%m/%d') as student_birth_date,
                    student_image_profile,
                    student_perfix,
                    student_perfix_other,
                    student_idcard,
                    student_passport,
                    date_format(student_passport_expire, '%Y/%m/%d') as student_passport_expire,
                    copy_of_idcard,
                    copy_of_passport,
                    work_certificate,
                    company_certificate,
                    student_nationality,
                    student_password,
                    student_password_key,
                    student_reference
                ",
                "classroom_student",
                "where student_id = " . intval($student_id) . " and status = 0"
            );
            if(!empty($students)) {
                $student = $students[0];
                $student_data = array(
                    'student_id' => $student['student_id'],
                    'date_create' => $student['date_create'],
                    'date_modify' => $student['date_modify'],
                    'student_firstname_en' => $student['student_firstname_en'],
                    'student_lastname_en' => $student['student_lastname_en'],
                    'student_nickname_en' => $student['student_nickname_en'],
                    'student_nickname_th' => $student['student_nickname_th'],
                    'student_firstname_th' => $student['student_firstname_th'],
                    'student_lastname_th' => $student['student_lastname_th'],
                    'student_gender' => $student['student_gender'],
                    'student_email' => $student['student_email'],
                    'dial_code' => $student['dial_code'] ? $student['dial_code'] : '+66',
                    'student_mobile' => $student['student_mobile'],
                    'student_company' => $student['student_company'],
                    'student_position' => $student['student_position'],
                    'student_username' => $student['student_username'],
                    'student_birth_date' => $student['student_birth_date'],
                    'student_passport_expire' => $student['student_passport_expire'],
                    'student_image_profile' => $student['student_image_profile'] ? GetPublicUrl($student['student_image_profile']) : '',
                    'student_perfix' => $student['student_perfix'],
                    'student_perfix_other' => $student['student_perfix_other'],
                    'student_idcard' => $student['student_idcard'],
                    'student_passport' => $student['student_passport'],
                    'student_reference' => $student['student_reference'],
                    'copy_of_idcard' => $student['copy_of_idcard'] ? GetPublicUrl($student['copy_of_idcard']) : '',
                    'copy_of_passport' => $student['copy_of_passport'] ? GetPublicUrl($student['copy_of_passport']) : '',
                    'work_certificate' => $student['work_certificate'] ? GetPublicUrl($student['work_certificate']) : '',
                    'company_certificate' => $student['company_certificate'] ? GetPublicUrl($student['company_certificate']) : '',
                    'student_nationality' => $student['student_nationality'],
                    'student_password' => ($student['student_password']) ? decryptToken($student['student_password'], $student['student_password_key']) : '',
                );
            }
            $register_template = $register_template;
            $register_require = $register_require;
            $form_data = $form_data;
        } else {
            if($shortcut_status == 0) {
                $register_template = $shortcut_field;
                $register_require = $shortcut_require;
                $form_data = array();
            } else {
                $register_template = $register_template;
                $register_require = $register_require;
                $form_data = $form_data;
            }
        }
        $answerData = select_data(
            "question_id, choice_id, answer_type, answer_text, other_text, is_other",
            "classroom_form_answer_users",
            "WHERE student_id = " . intval($student_id) . " 
            AND status = 0 
            AND classroom_id = " . intval($classroom['classroom_id'])
        );
        foreach($answerData as $ans) {
            foreach($form_data as $k => $f) {
                if($f['question_id'] == $ans['question_id']) {
                    if($f['has_options'] == 1) {
                        if(!isset($form_data[$k]['answer_choice_id'])) {
                            $form_data[$k]['answer_choice_id'] = [];
                        }
                        if($ans['choice_id']) {
                            $form_data[$k]['answer_choice_id'][] = $ans['choice_id'];
                        }
                    }
                    if($f['has_other_option'] == 1 && $ans['other_text']) {
                        $form_data[$k]['answer_other_text'] = $ans['other_text'];
                    }
                    if($f['has_options'] == 0 && $f['has_other_option'] == 0) {
                        $form_data[$k]['answer_text'] = $ans['answer_text'];
                    }
                }
            }
        }
        echo json_encode(array(
            'status' => true,
            'classroom_data' => $classroom_data,
            'register_template' => $register_template,
            'register_require' => $register_require,
            'form_data' => $form_data,
            'nationality' => $nationality,
            'student_data' => $student_data,
            'is_logged_in' => $student_id ? true : false,
        ));
        exit;
    }
    if(isset($_GET['action']) && $_GET['action'] == 'saveRegister') {
        ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
        $classroom_id = isset($_POST['classroom_id']) ? intval($_POST['classroom_id']) : 0;
        if(!$classroom_id) {
            echo json_encode(array('status' => false, 'message' => 'Invalid classroom ID'));
            exit;
        }
        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
        $classroom = select_data(
            "comp_id",
            "classroom_template",
            "where classroom_id = " . intval($classroom_id)
        );
        if(empty($classroom)) {
            echo json_encode(array('status' => false, 'message' => 'Classroom not found'));
            exit;
        }
        $comp_id = $classroom[0]['comp_id'];
        $dial_code = isset($_POST['dialCode']) && trim($_POST['dialCode']) ? initVal(trim($_POST['dialCode'])) : "'+66'";
        $student_firstname_en = isset($_POST['student_firstname_en']) ? initVal(trim($_POST['student_firstname_en'])) : "null";
        $student_lastname_en = isset($_POST['student_lastname_en']) ? initVal(trim($_POST['student_lastname_en'])) : "null";
        $student_nickname_en = isset($_POST['student_nickname_en']) ? initVal(trim($_POST['student_nickname_en'])) : "null";
        $student_nickname_th = isset($_POST['student_nickname_th']) ? initVal(trim($_POST['student_nickname_th'])) : "null";
        $student_firstname_th = isset($_POST['student_firstname_th']) ? initVal(trim($_POST['student_firstname_th'])) : "null";
        $student_lastname_th = isset($_POST['student_lastname_th']) ? initVal(trim($_POST['student_lastname_th'])) : "null";
        $student_gender = isset($_POST['student_gender']) ? initVal(trim($_POST['student_gender'])) : "null";
        $student_email = "null";
        if(isset($_POST['student_email']) && trim($_POST['student_email']) !== '') {
            $email_raw = trim($_POST['student_email']);
            if(!filter_var($email_raw, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(array('status' => false, 'message' => 'Invalid email format'));
                exit;
            }
            $student_email = initVal($email_raw);
        }
        $student_mobile = "null";
        if(isset($_POST['student_mobile']) && trim($_POST['student_mobile']) !== '') {
            $mobile = preg_replace('/[^0-9]/', '', trim($_POST['student_mobile']));
            if(strlen($mobile) < 9 || strlen($mobile) > 10) {
                echo json_encode(array('status' => false, 'message' => 'Invalid mobile number'));
                exit;
            }
            if(substr($mobile, 0, 1) === '0') {
                $mobile = substr($mobile, 1);
            }
            $student_mobile = "'" . mysqli_real_escape_string($mysqli, $mobile) . "'";
        }
        $ex_student_image_profile = isset($_POST['ex_student_image_profile']) ? $_POST['ex_student_image_profile'] : '';
        $student_company = isset($_POST['student_company']) ? initVal(trim($_POST['student_company'])) : "null";
        $student_position = isset($_POST['student_position']) ? initVal(trim($_POST['student_position'])) : "null";
        $student_username = isset($_POST['student_username']) ? initVal(trim($_POST['student_username'])) : "null";
        $student_reference = isset($_POST['student_reference']) ? initVal(trim($_POST['student_reference'])) : "null";
        $student_birth_date = "null";
        if(isset($_POST['student_birth_date']) && trim($_POST['student_birth_date']) !== '') {
            $student_birth_date = initVal(str_replace('/', '-', trim($_POST['student_birth_date'])));
        }
        $student_passport_expire = "null";
        if(isset($_POST['student_passport_expire']) && trim($_POST['student_passport_expire']) !== '') {
            $student_passport_expire = initVal(str_replace('/', '-', trim($_POST['student_passport_expire'])));
        }
        $student_perfix = isset($_POST['student_perfix']) ? initVal(trim($_POST['student_perfix'])) : "null";
        $student_perfix_other = isset($_POST['student_perfix_other']) ? initVal(trim($_POST['student_perfix_other'])) : "null";
        $student_idcard = isset($_POST['student_idcard']) ? initVal(trim($_POST['student_idcard'])) : "null";
        $student_passport = isset($_POST['student_passport']) ? initVal(trim($_POST['student_passport'])) : "null";
        $student_nationality = isset($_POST['student_nationality']) ? initVal(trim($_POST['student_nationality'])) : "null";
        $student_image_profile = uploadSecureFile('student_image_profile', $comp_id, $classroom_id, 'student', array('jpg', 'jpeg', 'png', 'gif'), 5242880);
        $copy_of_idcard = uploadSecureFile('copy_of_idcard', $comp_id, $classroom_id, 'student/idcard', array('pdf', 'jpg', 'jpeg', 'png'), 5242880);
        $copy_of_passport = uploadSecureFile('copy_of_passport', $comp_id, $classroom_id, 'student/passport', array('pdf', 'jpg', 'jpeg', 'png'), 5242880);
        $work_certificate = uploadSecureFile('work_certificate', $comp_id, $classroom_id, 'student/work_certificate', array('pdf', 'jpg', 'jpeg', 'png'), 5242880);
        $company_certificate = uploadSecureFile('company_certificate', $comp_id, $classroom_id, 'student/company_certificate', array('pdf', 'jpg', 'jpeg', 'png'), 5242880);
        if($student_email !== "null" || $student_mobile !== "null") {
            if($student_email !== "null") {
                $where_email = "where LOWER(student_email) = LOWER($student_email) and status = 0";
                if($student_id) {
                    $where_email .= " and student_id != " . intval($student_id);
                }
                $exits_email = select_data("student_id", "classroom_student", $where_email);
                if(!empty($exits_email)) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => 'This email is already registered. Please use another email.'
                    ));
                    exit;
                }
            }
            if($student_mobile !== "null") {
                $where_mobile = "where student_mobile = $student_mobile and status = 0";
                if($student_id) {
                    $where_mobile .= " and student_id != " . intval($student_id);
                }
                $exits_mobile = select_data("student_id", "classroom_student", $where_mobile);
                if(!empty($exits_mobile)) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => 'This mobile number is already registered. Please use another number.'
                    ));
                    exit;
                }
            }
        }
        mysqli_begin_transaction($mysqli);
        try {
            if($student_id) {
                $student_password_key_update = "";
                $student_password_update = "";
                if(isset($_POST['student_password']) && trim($_POST['student_password']) !== '') {
                    $password_key = bin2hex(openssl_random_pseudo_bytes(16));
                    $password = encryptToken($_POST['student_password'], $password_key);
                    $student_password_update = ", student_password = '" . mysqli_real_escape_string($mysqli, $password) . "'";
                    $student_password_key_update = ", student_password_key = '" . mysqli_real_escape_string($mysqli, $password_key) . "'";
                }
                $student_username_update = "";
                if($student_username !== "null") {
                    $student_username_update = ", student_username = $student_username";
                }
                $image_update = ($student_image_profile !== "null") ? ", student_image_profile = $student_image_profile" : "";
                $idcard_update = ($copy_of_idcard !== "null") ? ", copy_of_idcard = $copy_of_idcard" : "";
                $passport_update = ($copy_of_passport !== "null") ? ", copy_of_passport = $copy_of_passport" : "";
                $work_cert_update = ($work_certificate !== "null") ? ", work_certificate = $work_certificate" : "";
                $company_cert_update = ($company_certificate !== "null") ? ", company_certificate = $company_certificate" : "";
                update_data(
                    "classroom_student",
                    "   
                        student_firstname_en = $student_firstname_en,
                        student_lastname_en = $student_lastname_en,
                        student_nickname_en = $student_nickname_en,
                        student_nickname_th = $student_nickname_th,
                        student_firstname_th = $student_firstname_th,
                        student_lastname_th = $student_lastname_th,
                        student_gender = $student_gender,
                        student_email = $student_email,
                        dial_code = $dial_code,
                        student_mobile = $student_mobile,
                        student_company = $student_company,
                        student_position = $student_position
                        $student_username_update
                        $student_password_update
                        $student_password_key_update
                        $image_update
                        $idcard_update
                        $passport_update
                        $work_cert_update
                        $company_cert_update,
                        student_birth_date = $student_birth_date,
                        student_perfix = $student_perfix,
                        student_perfix_other = $student_perfix_other,
                        student_idcard = $student_idcard,
                        student_passport = $student_passport,
                        student_passport_expire = $student_passport_expire,
                        student_nationality = $student_nationality,
                        date_modify = NOW(),
                        student_reference = $student_reference
                    ",
                    "student_id = " . intval($student_id)
                );
            } else {
                $student_password_key = "null";
                $student_password = "null";
                if(isset($_POST['student_password']) && trim($_POST['student_password']) !== '') {
                    $password_key = bin2hex(openssl_random_pseudo_bytes(16));
                    $password = encryptToken($_POST['student_password'], $password_key);
                    $student_password_key = "'" . mysqli_real_escape_string($mysqli, $password_key) . "'";
                    $student_password = "'" . mysqli_real_escape_string($mysqli, $password) . "'";
                }
                $student_id = insert_data(
                    "classroom_student",
                    "(
                        student_firstname_en, student_lastname_en, student_nickname_en,
                        student_nickname_th, student_firstname_th, student_lastname_th,
                        student_gender, student_email, dial_code, student_mobile,
                        student_company, student_position, student_username,
                        student_password, student_password_key, student_birth_date,
                        student_image_profile, student_perfix, student_perfix_other,
                        student_idcard, student_passport, student_passport_expire, copy_of_idcard,
                        copy_of_passport, work_certificate, company_certificate,
                        student_nationality, status, date_create, date_modify, student_reference
                    )",
                    "(
                        $student_firstname_en, $student_lastname_en, $student_nickname_en,
                        $student_nickname_th, $student_firstname_th, $student_lastname_th,
                        $student_gender, $student_email, $dial_code, $student_mobile,
                        $student_company, $student_position, $student_username,
                        $student_password, $student_password_key, $student_birth_date,
                        $student_image_profile, $student_perfix, $student_perfix_other,
                        $student_idcard, $student_passport, $student_passport_expire, $copy_of_idcard,
                        $copy_of_passport, $work_certificate, $company_certificate,
                        $student_nationality, 0, NOW(), NOW(), $student_reference
                    )"
                );
                insert_data(
                    "classroom_student_join",
                    "(
                        student_id, classroom_id, consent_accept, register_date,
                        register_by, comp_id, status, date_create, date_modify,
                        invite_date, invite_status
                    )",
                    "(
                        " . intval($student_id) . ", " . intval($classroom_id) . ",
                        1, NOW(), 0, " . intval($comp_id) . ", 0, NOW(), NOW(),
                        NOW(), 1
                    )"
                );
            }
            $existing_copy_of_idcard = $_POST['existing_copy_of_idcard'];
            $existing_copy_of_passport = $_POST['existing_copy_of_passport'];
            $existing_work_certificate = $_POST['existing_work_certificate'];
            $existing_company_certificate = $_POST['existing_company_certificate'];
            if($existing_copy_of_idcard == '' && $copy_of_idcard === "null") {
                update_data(
                    "classroom_student",
                    "copy_of_idcard = null",
                    "student_id = " . intval($student_id)
                );
            }
            if($existing_copy_of_passport == '' && $copy_of_passport === "null") {
                update_data(
                    "classroom_student",
                    "copy_of_passport = null",
                    "student_id = " . intval($student_id)
                );
            }
            if($existing_work_certificate == '' && $work_certificate === "null") {
                update_data(
                    "classroom_student",
                    "work_certificate = null",
                    "student_id = " . intval($student_id)
                );
            }
            if($existing_company_certificate == '' && $company_certificate === "null") {
                update_data(
                    "classroom_student",
                    "company_certificate = null",
                    "student_id = " . intval($student_id)
                );
            }
            if(isset($_POST['question_id']) && is_array($_POST['question_id'])) {
                delete_data(
                    "classroom_form_answer_users",
                    "student_id = " . intval($student_id) . " and classroom_id = " . intval($classroom_id)
                );
                $forms = select_data(
                    "form_id",
                    "classroom_forms",
                    "where classroom_id = " . intval($classroom_id)
                );
                if(!empty($forms)) {
                    $form_id = intval($forms[0]['form_id']);
                    $exists_form_user = select_data(
                        "user_id",
                        "classroom_form_question_users",
                        "where user_id = " . intval($student_id) . " and form_id = " . intval($form_id)
                    );
                    if(empty($exists_form_user)) {
                        insert_data(
                            "classroom_form_question_users",
                            "(user_id, form_id, question_list, date_create)",
                            "(" . intval($student_id) . ", " . intval($form_id) . ", null, NOW())"
                        );
                    }
                    $type = isset($_POST['question_type']) ? $_POST['question_type'] : array();
                    $item = 0;
                    $q_list = array();
                    foreach($_POST['question_id'] as $question_id) {
                        $question_id = intval($question_id);
                        $q_list[] = $question_id;
                        $question_type = isset($type[$item]) ? $type[$item] : '';
                        switch($question_type) {
                            case 'short_answer':
                                $answer = isset($_POST['q_'.$question_id]) ? $_POST['q_'.$question_id] : '';
                                saveAnswer2($classroom_id, $student_id, $question_id, 0, $answer, '');
                                break;
                            case 'checkbox':
                                if(isset($_POST['q_'.$question_id]) && is_array($_POST['q_'.$question_id])) {
                                    $answers = $_POST['q_'.$question_id];
                                    foreach($answers as $a) {
                                        saveAnswer2($classroom_id, $student_id, $question_id, 1, $a, '');
                                    }
                                    if(isset($_POST['q_'.$question_id.'_other']) && $_POST['q_'.$question_id.'_other'] != '') {
                                        $last_answer = end($answers);
                                        $other_text = $_POST['q_'.$question_id.'_other'];
                                        saveAnswer2($classroom_id, $student_id, $question_id, 2, $last_answer, $other_text);
                                    }
                                }
                                break;
                            case 'multiple_choice':
                            case 'radio':
                                $answer = isset($_POST['q_'.$question_id]) ? $_POST['q_'.$question_id] : '';
                                if(isset($_POST['q_'.$question_id.'_other']) && $_POST['q_'.$question_id.'_other'] != '') {
                                    $other_text = $_POST['q_'.$question_id.'_other'];
                                    saveAnswer2($classroom_id, $student_id, $question_id, 2, $answer, $other_text);
                                } else {
                                    saveAnswer2($classroom_id, $student_id, $question_id, 1, $answer, '');
                                }
                                break;
                        }
                        $item++;
                    }
                    $q_no = implode(',', $q_list);
                    update_data(
                        "classroom_form_question_users",
                        "question_list = '" . mysqli_real_escape_string($mysqli, $q_no) . "'",
                        "user_id = " . intval($student_id) . " and form_id = " . intval($form_id)
                    );
                }
            }
            $messages = select_data(
                "template_body", "classroom_message_template", "where classroom_id = '{$classroom_id}' and status = 0 and template_subject = 'Register'"
            );
            if(isset($_SESSION['userId'])) {
                $userId = initVal($_SESSION['userId']);
                $exits = select_data(
                    "connect_id", "classroom_line_connect", "where userId = $userId"
                );
                if(!empty($exits)) {
                    $connect_id = $exits[0]['connect_id'];
                    update_data(
                        "classroom_line_connect", "student_id = '{$student_id}', update_date = NOW()", "connect_id = '{$connect_id}'"
                    );
                }
            }
            if($ex_student_image_profile == '') {
                update_data(
                    "classroom_student",
                    "student_image_profile = null",
                    "student_id = '{$student_id}'"
                );
            }
            mysqli_commit($mysqli);
            echo json_encode(array('status' => true, 'student_id' => $student_id));
            
        } catch(Exception $e) {
            mysqli_rollback($mysqli);
            echo json_encode(array('status' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
        exit;
    }
    function uploadSecureFile($file_key, $comp_id, $classroom_id, $subfolder, $allowed_types, $max_size) {
        global $mysqli;
        if(!isset($_FILES[$file_key]['name']) || $_FILES[$file_key]['name'] === '') {
            return "null";
        }
        $file_name = $_FILES[$file_key]['name'];
        $file_tmp = $_FILES[$file_key]['tmp_name'];
        $file_size = $_FILES[$file_key]['size'];
        $file_error = $_FILES[$file_key]['error'];
        if($file_error !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error for " . $file_key);
        }
        if($file_size > $max_size) {
            throw new Exception("File too large. Maximum size is " . ($max_size/1024/1024) . "MB");
        }
        $path_info = pathinfo($file_name);
        $file_ext = strtolower($path_info['extension']);
        if(!in_array($file_ext, $allowed_types)) {
            throw new Exception("Invalid file type for " . $file_key . ". Allowed: " . implode(', ', $allowed_types));
        }
        $strname = md5($classroom_id . microtime(true) . rand(1000,9999) . $file_key);
        $upload_dir = 'uploads/' . intval($comp_id) . '/classroom/' . $subfolder . '/';
        $upload_path = $upload_dir . $strname . '.' . $file_ext;
        if(!SaveFile($file_tmp, $upload_path)) {
            throw new Exception("Failed to save file: " . $file_key);
        }
        return "'" . mysqli_real_escape_string($mysqli, $upload_path) . "'";
    }
    function saveAnswer2($classroom_id, $student_id, $question_id, $answer_type, $answer, $other_text = '') {
        global $mysqli;
        $classroom_id = intval($classroom_id);
        $student_id   = intval($student_id);
        $question_id  = intval($question_id);
        $answer_type  = intval($answer_type);
        $answer_text = ($answer && $answer_type == 0) ? "'" . mysqli_real_escape_string($mysqli, $answer) . "'" : "null";
        $choice_id   = ($answer_type != 0 && !$other_text) ? "'" . mysqli_real_escape_string($mysqli, $answer) . "'" : "null";
        $other       = ($other_text) ? "'" . mysqli_real_escape_string($mysqli, $other_text) . "'" : "null";
        $is_other    = ($other_text) ? 1 : 0; 
        insert_data(
            "classroom_form_answer_users",
            "(
                answer_text, question_id, answer_type, choice_id,
                other_text, is_other, classroom_id, student_id,
                create_date, date_update, status
            )",
            "(
                $answer_text, $question_id, $answer_type, $choice_id,
                $other, $is_other, $classroom_id, $student_id,
                NOW(), NOW(), 0
            )"
        );
    }
    function initVal($val) {
        global $mysqli;
        if($val && trim($val) !== '') {
            return "'" . mysqli_real_escape_string($mysqli, $val) . "'";
        } else {
            return "null";
        }
    }
?>