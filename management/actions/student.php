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
?>