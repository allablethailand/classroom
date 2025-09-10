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
            'classroom_start_date' => $classroom['classroom_start_date'], 
            'classroom_start_time' => $classroom['classroom_start_time'], 
            'classroom_end_date' => $classroom['classroom_end_date'], 
            'classroom_end_time' => $classroom['classroom_end_time'], 
            'classroom_type' => $classroom['classroom_type'], 
            'classroom_place' => $classroom['classroom_place'], 
            'classroom_source' => $classroom['classroom_source'], 
            'contact_us' => $classroom['contact_us'], 
            'tenant_key' => $tenant_key, 
            'comp_logo' => ($classroom['comp_logo']) ? ($classroom['comp_logo_target'] == 0) ? '/' . $classroom['contact_us'] : GetUrl($classroom['contact_us']) : '', 
            'classroom_allow_register' => $classroom['classroom_allow_register']
        ];
        echo json_encode([
            'status' => true,
            'classroom_data' => $classroom_data
        ]);
        exit;
    }
    if($action == 'loadTerm') {
        $classroom_id = (!empty($_POST['classroom_id'])) ? escape_string($_POST['classroom_id']) : ''; 
        $Term = select_data(
            "classroom_consent", "classroom_template", "where classroom_id = '{$classroom_id}'"
        );
        echo json_encode([
            'status' => true,
            'classroom_consent' => $Term[0]['classroom_consent']
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'saveRegister') {
        $classroom_id = $_POST['classroom_id'];
        $classroom = select_data(
            "comp_id, auto_approve",
            "classroom_template",
            "where classroom_id = '{$classroom_id}'"
        );
        $comp_id = $classroom[0]['comp_id'];
        $auto_approve = $classroom[0]['auto_approve'];
        $firstname_en = escape_string($_POST['firstName']);
        $lastname_en = escape_string($_POST['lastName']);
        $nickname = escape_string($_POST['nickname']);
        $gender = escape_string($_POST['gender']);
        $email = escape_string($_POST['email']);
        $mobile = escape_string($_POST['mobile']);
        $organization = escape_string($_POST['organization']);
        $position = escape_string($_POST['position']);
        $username = escape_string($_POST['username']);
        $password = escape_string($_POST['password']);
        $exits_email = select_data(
            "student_id",
            "classroom_student",
            "where LOWER(student_email) = LOWER('{$email}') and comp_id = '{$comp_id}' and status = 0"
        );
        if(!empty($exits_email)) {
            echo json_encode([
                'status' => false,
                'message' => 'This email is already registered. Please log in instead.'
            ]);
        }
        $exits_mobile = select_data(
            "student_id",
            "classroom_student",
            "where student_mobile = '{$mobile}' and comp_id = '{$comp_id}' and status = 0"
        );
        if(!empty($exits_mobile)) {
            echo json_encode([
                'status' => false,
                'message' => 'This mobile is already registered. Please log in instead.'
            ]);
        }
        $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
        $student_password = encryptToken($password, $student_password_key);
        $student_id = insert_data(
            "classroom_student",
            "(
                student_firstname_en,
                student_lastname_en,
                student_nickname_en,
                student_gender,
                student_email,
                student_mobile,
                student_company,
                student_position,
                student_username,
                student_password,
                student_password_key,
                comp_id,
                status,
                date_create,
                date_modify
            )",
            "(
                '{$firstname_en}',
                '{$lastname_en}',
                '{$nickname}',
                '{$gender}',
                '{$email}',
                '{$mobile}',
                '{$organization}',
                '{$position}',
                '{$username}',
                '{$student_password}',
                '{$student_password_key}',
                '{$comp_id}',
                0,
                NOW(),
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
                    date_create,
                    date_modify,
                    invite_date,
                    invite_status
                )",
                "(
                    '{$student_id}',
                    '{$classroom_id}',
                    1,
                    NOW(),
                    0,
                    '{$comp_id}',
                    0,
                    NOW(),
                    NOW(),
                    NOW(),
                    1
                )"
            );
            if($auto_approve == 0) {
                update_data(
                    "classroom_student_join",
                    "approve_status = 1, approve_date = NOW()",
                    "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'"
                );
            }
        }
        echo json_encode([
            'status' => true,
            'auto_approve' => $auto_approve
        ]);
    }
?>