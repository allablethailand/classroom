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
    if(isset($_POST) && $_POST['action'] == 'buildTeacher') {
        $table = "SELECT 
            teacher.teacher_id,
            teacher.email,
            teacher.mobile,
            teacher.is_available,
            teacher.teacher_type
        FROM 
            classroom_teachers teacher 
        WHERE 
            teacher.status = 0 and teacher.comp_id = '{$_SESSION['comp_id']}'";
        $primaryKey = 'teacher_id';
        $columns = array(
			array('db' => 'teacher_id', 'dt' => 'teacher_id'),
			array('db' => 'email', 'dt' => 'email'),
			array('db' => 'mobile', 'dt' => 'mobile'),
			array('db' => 'is_available', 'dt' => 'is_available'),
			array('db' => 'teacher_type', 'dt' => 'teacher_type'),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
?>