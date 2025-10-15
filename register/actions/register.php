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
    $student_id = (isset($_SESSION['student_id']) && $_SESSION['student_id']) ? $_SESSION['student_id'] : '';
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
        $channel = (!empty($_POST['channel'])) ? escape_string($_POST['channel']) : ''; 
        if (!$classroomCode) {
            echo json_encode(array(
                'status' => false,
                'message' => 'Classroom code is required'
            ));
            exit;
        }
        $channel_id = '';
        if($channel) {
            $channels = select_data(
                "channel_id",
                "classroom_channel",
                "where md5(channel_id) = '" . escape_string($channel) . "'"
            );
            if(!empty($channels)) {
                $channel_id = $channels[0]['channel_id'];
            }
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
                template.classroom_allow_register,
                (
                    CASE
                        WHEN template.classroom_allow_register = 1 then 'Close Register'
                        WHEN template.classroom_allow_register = 0 and NOW() between template.classroom_open_register and template.classroom_close_register then 'open'
                        WHEN template.classroom_allow_register = 0 and NOW() < template.classroom_open_register then 'Not yet open for registration'
                        WHEN template.classroom_allow_register = 0 and NOW() > template.classroom_close_register then 'Close Register'
                        ELSE 'Close Register'
                    END
                ) as classroom_register,
                template.register_default_lang
            ",
            "classroom_template template",
            "
                left join data_meeting_platforms pf on pf.platforms_id = template.classroom_plateform
                left join m_company comp on comp.comp_id = template.comp_id
                where template.classroom_key = '" . escape_string($classroomCode) . "' and template.status = 0 
            "
        );
        if (empty($classrooms)) {
            echo json_encode(array(
                'status' => false,
                'message' => 'not found'
            ));
            exit;
        }
        $classroom = $classrooms[0];
        $comp_id = $classroom['comp_id'];
        $classroom_register = $classroom['classroom_register'];
        $tenant = select_data("tenant_key", "ogm_tenant", "where comp_id = '{$comp_id}' and status = 0");
        $tenant_url = $domain_name;
        if (!empty($tenant)) {
            $tenant_url .= $tenant[0]['tenant_key'];
        }
        if($classroom_register !== 'open' && !$student_id) {
            $messages = select_data(
                "template_body", "classroom_message_template", "where classroom_id = '{$classroom['classroom_id']}' and status = 0 and template_subject = '{$classroom_register}'"
            );
            echo json_encode(array(
                'status' => false,
                'message' => 'not register',
                'notification' => previewTemplate($classroom['classroom_id'], $messages[0]['template_body'], ''),
                'tenant_url' => $tenant_url,
                'classroom_register' => $classroom_register
            ));
            exit;
        }
        $host = select_data(
            "tenant_key", 
            "ogm_tenant", 
            "where comp_id = '" . escape_string($comp_id) . "' and status = 0"
        );
        $tenant_key = '';
        if(!empty($host)) {
            $tenant_key = $host[0]['tenant_key'];
        }
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
            'register_default_lang' => $classroom['register_default_lang'], 
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
        $consents = select_data(
            "consent_id",
            "classroom_consent",
            "where classroom_id = '" . escape_string($classroom['classroom_id']) . "' and consent_use = 0 and status = 0"
        );
        $consent_status = (!empty($consents)) ? 'Y' : 'N';
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
        $student_id = isset($_SESSION['student_id']) ? intval($_SESSION['student_id']) : 0;
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
            $consent_status = 'N';
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
            "WHERE student_id = " . intval($student_id) . " AND status = 0 AND classroom_id = " . intval($classroom['classroom_id'])
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
            'consent_status' => $consent_status,
            'form_data' => $form_data,
            'channel_id' => $channel_id ? $channel_id : '',
            'nationality' => $nationality,
            'student_data' => $student_data,
            'is_logged_in' => $student_id ? true : false,
        ));
        exit;
    }
    if($action == 'loadTerm') {
        $classroom_id = (!empty($_POST['classroom_id'])) ? escape_string($_POST['classroom_id']) : ''; 
        $currentLang = isset($_POST['currentLang']) ? $_POST['currentLang'] : 'th';
        $Term = select_data(
            "ifnull(consent_body,consent_body_en) as classroom_consent, ifnull(consent_body_en,consent_body) as classroom_consent_en", "classroom_consent", "where classroom_id = '{$classroom_id}' and status = 0 and consent_use = 0"
        );
        echo json_encode([
            'status' => true,
            'classroom_consent' => ($currentLang == 'th') ? $Term[0]['classroom_consent'] : $Term[0]['classroom_consent_en'],
        ]);
    }
    if(isset($_GET['action']) && $_GET['action'] == 'saveRegister') {
        $classroom_id = isset($_POST['classroom_id']) ? intval($_POST['classroom_id']) : 0;
        $currentLang = isset($_POST['currentLang']) ? $_POST['currentLang'] : 'th';
        if(!$classroom_id) {
            echo json_encode(array('status' => false, 'message' => ($currentLang == 'en') ? 'Invalid classroom ID' : 'รหัสห้องเรียนไม่ถูกต้อง'));
            exit;
        }
        $student_id = isset($_SESSION['student_id']) ? intval($_SESSION['student_id']) : 0;
        $classroom = select_data(
            "comp_id",
            "classroom_template",
            "where classroom_id = " . intval($classroom_id)
        );
        if(empty($classroom)) {
            echo json_encode(array('status' => false, 'message' => ($currentLang == 'en') ? 'Classroom not found' : 'ไม่พบห้องเรียน'));
            exit;
        }
        $comp_id = intval($classroom[0]['comp_id']);
        $tenant = select_data("tenant_key", "ogm_tenant", "where comp_id = '{$comp_id}' and status = 0");
        $tenant_url = $domain_name;
        if (!empty($tenant)) {
            $tenant_url .= $tenant[0]['tenant_key'];
        }
        $channel_id = isset($_POST['channel_id']) && trim($_POST['channel_id']) ? initVal(trim($_POST['channel_id'])) : "null";
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
                echo json_encode(array('status' => false, 'message' => ($currentLang == 'en') ? 'Invalid email format' : 'รูปแบบอีเมลไม่ถูกต้อง'));
                exit;
            }
            $student_email = initVal($email_raw);
        }
        $student_mobile = "null";
        if(isset($_POST['student_mobile']) && trim($_POST['student_mobile']) !== '') {
            $mobile = preg_replace('/[^0-9]/', '', trim($_POST['student_mobile']));
            if(strlen($mobile) < 9 || strlen($mobile) > 10) {
                echo json_encode(array('status' => false, 'message' => ($currentLang == 'en') ? 'Invalid mobile number' : 'หมายเลขโทรศัพท์มือถือไม่ถูกต้อง'));
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
        $payment_slip = uploadSecureFile('payment_slip', $comp_id, $classroom_id, 'student/payment_slip', array('jpg', 'jpeg', 'png'), 5242880);
        $duplicate_student_id = null;
        if($student_email !== "null" || $student_mobile !== "null" || $student_idcard !== "null") {
            $join = "LEFT JOIN classroom_student_join stu_join ON stu_join.student_id = stu.student_id AND stu_join.classroom_id = $classroom_id";
            $condition = "";
            if($student_id) {
                $condition .= "and stu.student_id != " . intval($student_id);
            }
            $condition_join = " AND (stu_join.join_id IS NULL OR stu_join.status = 0) ";
            if($student_email !== "null") {
                $where_email = "where LOWER(stu.student_email) = LOWER($student_email) and status = 0";
                $exits_email = select_data("stu.student_id", "classroom_student stu","$join $where_email $condition $condition_join");
                if(!empty($exits_email)) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => ($currentLang == 'en') ? 'This email is already registered. Please use another email.' : 'อีเมลนี้ถูกลงทะเบียนแล้ว กรุณาใช้เมลอื่น'
                    ));
                    exit;
                }
            }
            if($student_mobile !== "null") {
                $where_mobile = "where stu.student_mobile = $student_mobile and stu.status = 0";
                $exits_mobile = select_data("stu.student_id", "classroom_student stu","$join $where_mobile $condition $condition_join");
                if(!empty($exits_mobile)) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => ($currentLang == 'en') ? 'This mobile number is already registered. Please use another number.' : 'หมายเลขโทรศัพท์มือถือถูกลงทะเบียนแล้ว กรุณาใช้หมายเลขอื่น'
                    ));
                    exit;
                }
            }
            if($student_idcard !== "null") {
                $where_idcard = "where stu.student_idcard = $student_idcard and stu.status = 0";
                $exits_idcard = select_data("stu.student_id", "classroom_student stu","$join $where_idcard $condition $condition_join");
                if(!empty($exits_idcard)) {
                    echo json_encode(array(
                        'status' => false,
                        'message' => ($currentLang == 'en') ? 'This ID card number is already registered. Please use another number.' : 'หมายเลขบัตรประชาชนนี้ถูกลงทะเบียนแล้ว กรุณาใช้หมายเลขอื่น'
                    ));
                    exit;
                }
            }
            if($student_email !== "null") {
                $where_email = "where LOWER(stu.student_email) = LOWER($student_email) and status = 0";
                $exits_email = select_data("stu.student_id", "classroom_student stu","$join $where_email $condition");
                if(!empty($exits_email)) {
                    $duplicate_student_id = $exits_idcard[0]['student_id'];
                }
            }
            if($student_mobile !== "null") {
                $where_mobile = "where stu.student_mobile = $student_mobile and stu.status = 0";
                $exits_mobile = select_data("stu.student_id", "classroom_student stu","$join $where_mobile $condition");
                if(!empty($exits_mobile)) {
                    $duplicate_student_id = $exits_idcard[0]['student_id'];
                }
            }
            if($student_idcard !== "null") {
                $where_idcard = "where stu.student_idcard = $student_idcard and stu.status = 0";
                $exits_idcard = select_data("stu.student_id", "classroom_student stu","$join $where_idcard $condition");
                if(!empty($exits_idcard)) {
                    $duplicate_student_id = $exits_idcard[0]['student_id'];
                }
            }
        }
        mysqli_begin_transaction($mysqli);
        if(!$student_id) {
            if($duplicate_student_id) {
                $student_id = $duplicate_student_id;
            } 
        }
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
                update_data(
                    "classroom_student_join", "status = 0, date_modify = NOW()", "classroom_id = '{$classroom_id}' and student_id = '{$student_id}'"
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
                        invite_date, invite_status, channel_id
                    )",
                    "(
                        " . intval($student_id) . ", " . intval($classroom_id) . ",
                        1, NOW(), 0, " . intval($comp_id) . ", 0, NOW(), NOW(),
                        NOW(), 1, $channel_id
                    )"
                );
                notiMail($classroom_id, $student_id, 'Register');
            }
            update_data("classroom_student_join", "payment_attach_file = $payment_slip", "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'");
            $ex_payment_slip = $_POST['ex_payment_slip'];
            if($ex_payment_slip == '' && $payment_slip === "null") {
                update_data("classroom_student_join", "payment_attach_file = NULL, payment_date = NULL", "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'");
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
                                saveAnswer($classroom_id, $student_id, $question_id, 0, $answer, '');
                                break;
                            case 'checkbox':
                                if(isset($_POST['q_'.$question_id]) && is_array($_POST['q_'.$question_id])) {
                                    $answers = $_POST['q_'.$question_id];
                                    foreach($answers as $a) {
                                        saveAnswer($classroom_id, $student_id, $question_id, 1, $a, '');
                                    }
                                    if(isset($_POST['q_'.$question_id.'_other']) && $_POST['q_'.$question_id.'_other'] != '') {
                                        $last_answer = end($answers);
                                        $other_text = $_POST['q_'.$question_id.'_other'];
                                        saveAnswer($classroom_id, $student_id, $question_id, 2, $last_answer, $other_text);
                                    }
                                }
                                break;
                            case 'multiple_choice':
                            case 'radio':
                                $answer = isset($_POST['q_'.$question_id]) ? $_POST['q_'.$question_id] : '';
                                if(isset($_POST['q_'.$question_id.'_other']) && $_POST['q_'.$question_id.'_other'] != '') {
                                    $other_text = $_POST['q_'.$question_id.'_other'];
                                    saveAnswer($classroom_id, $student_id, $question_id, 2, $answer, $other_text);
                                } else {
                                    saveAnswer($classroom_id, $student_id, $question_id, 1, $answer, '');
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
            $message_success = previewTemplate($classroom_id, $messages[0]['template_body'], '');
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
            if($ex_student_image_profile == '' && $student_image_profile == "null") {
                update_data(
                    "classroom_student",
                    "student_image_profile = null",
                    "student_id = '{$student_id}'"
                );
            }
            mysqli_commit($mysqli);
            echo json_encode(array('status' => true, 'student_id' => $student_id, 'tenant_url' => $tenant_url, 'message_success' => $message_success));
            
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
    function saveAnswer($classroom_id, $student_id, $question_id, $answer_type, $answer, $other_text = '') {
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
    if(isset($_GET) && $_GET['action'] == 'buildNationality') {
        $keyword = trim($_GET['term']);
		$search = ($keyword) ? " where nationality_name like '%{$keyword}%' " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                nationality_id as data_code,
                nationality_name as data_desc 
            from 
                m_nationality 
            $search
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
    if(isset($_POST['action']) && $_POST['action'] == 'verifyDuplicateData') {
        $classroom_id = isset($_POST['classroom_id']) ? intval($_POST['classroom_id']) : 0;
        $currentLang = isset($_POST['currentLang']) ? trim($_POST['currentLang']) : 'th';
        $verify_val = isset($_POST['verify_val']) ? trim($_POST['verify_val']) : '';
        $verify_type = isset($_POST['verify_type']) ? trim($_POST['verify_type']) : '';
        $condition = $student_id ? "AND stu.student_id <> $student_id" : "";
        $join = "LEFT JOIN classroom_student_join stu_join 
                ON stu_join.student_id = stu.student_id 
                AND stu_join.classroom_id = $classroom_id";
        $condition .= " AND (stu_join.join_id IS NULL OR stu_join.status = 0) ";
        $duplicate_message = '';
        $where = '';
        switch($verify_type) {
            case 'idcard':
                $where = "WHERE stu.student_idcard = '" . mysqli_real_escape_string($mysqli, $verify_val) . "' AND stu.status = 0 $condition";
                $duplicate_message = ($currentLang == 'th') ? 
                    'หมายเลขบัตรประชาชนนี้ถูกลงทะเบียนแล้ว กรุณาใช้หมายเลขอื่น' : 
                    'This ID card number is already registered. Please use another number.';
                break;
            case 'email':
                $where = "WHERE LOWER(stu.student_email) = LOWER('" . mysqli_real_escape_string($mysqli, $verify_val) . "') AND stu.status = 0 $condition";
                $duplicate_message = ($currentLang == 'th') ? 
                    'อีเมลนี้ถูกลงทะเบียนแล้ว กรุณาใช้เมลอื่น' : 
                    'This email is already registered. Please use another email.';
                break;
            case 'mobile':
                $mobile = preg_replace('/[^0-9]/', '', $verify_val);
                if(substr($mobile, 0, 1) === '0') $mobile = substr($mobile, 1);
                $where = "WHERE stu.student_mobile = '" . mysqli_real_escape_string($mysqli, $mobile) . "' AND stu.status = 0 $condition";
                $duplicate_message = ($currentLang == 'th') ? 
                    'หมายเลขโทรศัพท์มือถือถูกลงทะเบียนแล้ว กรุณาใช้หมายเลขอื่น' : 
                    'This mobile number is already registered. Please use another number.';
                break;
        }
        $exits = select_data(
            "stu.student_id", "classroom_student stu", "$join $where LIMIT 1"
        );
        if(!empty($exits)) {
            echo json_encode(['status' => false, 'message' => $duplicate_message]);
        } else {
            echo json_encode(['status' => true, 'message' => '']);
        }
    }
?>