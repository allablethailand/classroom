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
    require_once $base_include.'/actions/func.php';
    require_once $base_include.'/classroom/actions/mailsend.php';
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);
    $action = (isset($_POST['action']) && $_POST['action']) ? $_POST['action'] : ''; 
    if ($action == 'verifyClassroom') {
        $classroomCode = (!empty($_POST['classroomCode'])) ? escape_string($_POST['classroomCode']) : ''; 
        if (!$classroomCode) {
            echo json_encode([
                'status' => false
            ]);
            exit;
        }
        $classrooms = select_data(
            "
                template.classroom_id, 
                template.classroom_name, 
                template.classroom_information, 
                template.classroom_poster, 
                template.classroom_bg, 
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
                where template.classroom_key = '{$classroomCode}' and template.status = 0 
            "
        );
        if (empty($classrooms)) {
            echo json_encode([
                'status' => false
            ]);
            exit;
        }
        $classroom = $classrooms[0];
        $comp_id = $classroom['comp_id'];
        $host = select_data(
            "tenant_key", "ogm_tenant", "where comp_id = '{$comp_id}' and status = 0"
        );
        $tenant_key = '';
        if(!empty($host)) {
            $tenant_key = $host[0]['tenant_key'];
        }
        $classroom_data = [
            'classroom_id' => $classroom['classroom_id'], 
            'classroom_name' => $classroom['classroom_name'], 
            'classroom_information' => $classroom['classroom_information'], 
            'classroom_poster' => ($classroom['classroom_poster']) ? GetUrl($classroom['classroom_poster']) : '', 
            'classroom_bg' => ($classroom['classroom_bg']) ? GetUrl($classroom['classroom_bg']) : '', 
            'classroom_start_date' => $classroom['classroom_start_date'], 
            'classroom_start_time' => $classroom['classroom_start_time'], 
            'classroom_end_date' => $classroom['classroom_end_date'], 
            'classroom_end_time' => $classroom['classroom_end_time'], 
            'classroom_type' => $classroom['classroom_type'], 
            'classroom_place' => $classroom['classroom_place'], 
            'classroom_source' => $classroom['classroom_source'], 
            'contact_us' => $classroom['contact_us'], 
            'tenant_key' => $tenant_key, 
            'comp_logo' => ($classroom['comp_logo']) ? ($classroom['comp_logo_target'] == 0) ? '/' . $classroom['comp_logo'] : GetUrl($classroom['comp_logo']) : '', 
            'classroom_allow_register' => $classroom['classroom_allow_register']
        ];
        $register_forms = select_data(
            "register_template, register_require",
            "classroom_template",
            "where classroom_id = '{$classroom['classroom_id']}'"
        );
        $register_template = explode(',', $register_forms[0]['register_template']);
        $register_require = explode(',', $register_forms[0]['register_require']);
        echo json_encode([
            'status' => true,
            'classroom_data' => $classroom_data,
            'register_template' => $register_template,
            'register_require' => $register_require
        ]);
        exit;
    }
    if($action == 'loadTerm') {
        $classroom_id = (!empty($_POST['classroom_id'])) ? escape_string($_POST['classroom_id']) : ''; 
        $Term = select_data(
            "consent_body as classroom_consent", "classroom_consent", "where classroom_id = '{$classroom_id}' and status = 0 and consent_use = 0"
        );
        echo json_encode([
            'status' => true,
            'classroom_consent' => $Term[0]['classroom_consent'],
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'saveRegister') {
        $classroom_id = $_POST['classroom_id'];
        $dial_code = isset($_POST['dialCode']) ? initVal(trim($_POST['dialCode'])) : '+66';
        $classroom = select_data(
            "comp_id",
            "classroom_template",
            "where classroom_id = '{$classroom_id}'"
        );
        $comp_id = $classroom[0]['comp_id'];
        $student_firstname_en  = isset($_POST['student_firstname_en']) ? initVal(trim($_POST['student_firstname_en'])) : '';
        $student_lastname_en = isset($_POST['student_lastname_en']) ? initVal(trim($_POST['student_lastname_en'])) : '';
        $student_nickname_en = isset($_POST['student_nickname_en']) ? initVal(trim($_POST['student_nickname_en'])) : '';
        $student_nickname_th = isset($_POST['student_nickname_th']) ? initVal(trim($_POST['student_nickname_th'])) : '';
        $student_firstname_th = isset($_POST['student_firstname_th']) ? initVal(trim($_POST['student_firstname_th'])) : '';
        $student_lastname_th = isset($_POST['student_lastname_th']) ? initVal(trim($_POST['student_lastname_th'])) : '';
        $student_gender = isset($_POST['student_gender']) ? initVal(trim($_POST['student_gender'])) : '';
        $student_email = isset($_POST['student_email']) ? initVal(trim($_POST['student_email'])) : '';
        if(isset($_POST['student_mobile'])) {
            $mobile = trim($_POST['student_mobile']);
            if (substr($mobile, 0, 1) === '0') {
                $mobile = substr($mobile, 1);
            }
            $student_mobile = "'{$mobile}'";
        } else {
            $student_mobile = "null";
        }
        $student_company = isset($_POST['student_company']) ? initVal(trim($_POST['student_company'])) : '';
        $student_position = isset($_POST['student_position']) ? initVal(trim($_POST['student_position'])) : '';
        $student_username = isset($_POST['student_username']) ? initVal(trim($_POST['student_username'])) : '';
        $student_birth_date = isset($_POST['student_birth_date']) ? initVal(trim(str_replace('/', '-', $_POST['student_birth_date']))) : '';
        $student_perfix = isset($_POST['student_perfix']) ? initVal(trim($_POST['student_perfix'])) : '';
        $student_idcard = isset($_POST['student_idcard']) ? initVal(trim($_POST['student_idcard'])) : '';
        $student_passport = isset($_POST['student_passport']) ? initVal(trim($_POST['student_passport'])) : '';
        $student_image_name = isset($_FILES['student_image_profile']['name']) ? $_FILES['student_image_profile']['name'] : '';
        $student_image_profile = "null";
        if($student_image_name) {
            $student_image_tmp = $_FILES['student_image_profile']['tmp_name'];
            $strname = md5($classroom_id . microtime(true) . rand(1000,9999));
            $student_image_dir = 'uploads/classroom/' . $comp_id . '/student/';
            $path_info = pathinfo($student_image_name);
            $student_image_ext = strtolower($path_info['extension']);
            $upload = $student_image_dir . $strname . '.' . $student_image_ext;
            SaveFile($student_image_tmp, $upload);
            $student_image_profile = "'{$upload}'";
        }
        if($student_email !== "null" || $student_mobile !== "null") {
            $exits_email = select_data(
                "student_id",
                "classroom_student",
                "where LOWER(student_email) = LOWER($student_email) and status = 0"
            );
            if(!empty($exits_email)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'This email is already registered. Please log in instead.'
                ]);
                exit;
            }
            $exits_mobile = select_data(
                "student_id",
                "classroom_student",
                "where student_mobile = $student_mobile and status = 0"
            );
            if(!empty($exits_mobile)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'This mobile is already registered. Please log in instead.'
                ]);
                exit;
            }
        }
        $student_password_key = "null";
        $student_password = "null";
        if(isset($_POST['student_password'])) {
            $password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $password = encryptToken($_POST['student_password'], $password_key);
            $student_password_key = "'{$password_key}'";
            $student_password = "'{$password}'";
        }
        $emp_id = ($_SESSION['emp_id']) ? "'{$_SESSION['emp_id']}'" : "null";
        $invite_status = ($_SESSION['emp_id']) ? 0 : 1;
        $student_id = insert_data(
            "classroom_student",
            "(
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
                student_password,
                student_password_key,
                student_birth_date,
                student_image_profile,
                student_perfix,
                student_idcard,
                student_passport,
                status,
                emp_create,
                date_create,
                emp_modify,
                date_modify
            )",
            "(
                $student_firstname_en,
                $student_lastname_en,
                $student_nickname_en,
                $student_nickname_th,
                $student_firstname_th,
                $student_lastname_th,
                $student_gender,
                $student_email,
                $dial_code,
                $student_mobile,
                $student_company,
                $student_position,
                $student_username,
                $student_password,
                $student_password_key,
                $student_birth_date,
                $student_image_profile,
                $student_perfix,
                $student_idcard,
                $student_passport,
                0,
                $emp_id,
                NOW(),
                $emp_id,
                NOW()
            )"
        );
        if($student_id) {
            insert_data(
                "classroom_student_join",
                "(
                    student_id,
                    classroom_id,
                    consent_accept,
                    register_date,
                    register_by,
                    comp_id,
                    status,
                    emp_create,
                    date_create,
                    emp_modify
                    date_modify,
                    invite_date,
                    invite_status,
                    invite_by
                )",
                "(
                    '{$student_id}',
                    '{$classroom_id}',
                    1,
                    NOW(),
                    0,
                    '{$comp_id}',
                    0,
                    $emp_id,
                    NOW(),
                    $emp_id,
                    NOW(),
                    NOW(),
                    '{$invite_status}',
                    $emp_id
                )"
            );
        }
        notiMail($classroom_id, $student_id, 'Register');
        echo json_encode([
            'status' => true
        ]);
        exit;
    }
    function initVal($val) {
        global $mysqli;
        if($val) {
            return "'" . mysqli_real_escape_string($mysqli, $val) . "'";
        } else {
            return "null";
        }
    }
?>