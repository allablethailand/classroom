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
    if(isset($_POST) && $_POST['action'] == 'buildConsent') {
        $classroom_id = $_POST['classroom_id'];
        $table = "SELECT 
            c.consent_id,
            date_format(c.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            c.consent_use,
            c.consent_body
        FROM 
            classroom_consent c
        LEFT JOIN 
            m_employee_info i on i.emp_id = c.emp_create 
        WHERE 
            c.classroom_id = '{$classroom_id}'";
        $primaryKey = 'consent_id';
        $columns = array(
            array('db' => 'consent_id', 'dt' => 'consent_id'),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'consent_use', 'dt' => 'consent_use'),
            array('db' => 'consent_body', 'dt' => 'consent_body'),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'buildConsentData') {
        $consent_id = $_POST['consent_id'];
        $consents = select_data(
            "consent_body",
            "classroom_consent",
            "where consent_id = '{$consent_id}'"
        );
        echo json_encode([
            'status' => true,
            'consent_body' => $consents[0]['consent_body']
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'delConsent') {
        $consent_id = $_POST['consent_id'];
        update_data(
            "classroom_consent",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "consent_id = '{$consent_id}'"
        );
        echo json_encode([
            'status' => true,
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'saveConsent') {
        $classroom_id = $_POST['classroom_id'];
        $consent_id = $_POST['consent_id'];
        $classroom_consent = initVal($_POST['classroom_consent']);
        if($consent_id) {
            update_data(
                "classroom_consent",
                "consent_body = $classroom_consent, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "consent_id = '{$consent_id}'"
            );
        } else { 
            insert_data(
                "classroom_consent",
                "(
                    classroom_id,
                    comp_id,
                    consent_body,
                    status,
                    emp_create,
                    date_create,
                    emp_modify,
                    date_modify
                )",
                "(
                    '{$classroom_id}',
                    '{$_SESSION['comp_id']}',
                    $classroom_consent,
                    0,
                    '{$_SESSION['emp_id']}',
                    NOW(),
                    '{$_SESSION['emp_id']}',
                    NOW()
                )"
            );
        }
        echo json_encode([
            'status' => true
        ]);
    }
    function initVal($val) {
        global $mysqli;
        if($val) {
            return "'" . mysqli_real_escape_string($mysqli, $val) . "'";
        } else {
            return "null";
        }
    }
    if(isset($_POST) && $_POST['action'] == 'switchConsent') {
        $classroom_id = $_POST['classroom_id'];
        $consent_id = $_POST['consent_id'];
        $option = $_POST['option'];
        $status = ($option == 0) ? 1 : 0;
        if($status == 0) {
            update_data(
                "classroom_consent",
                "consent_use = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "classroom_id = '{$classroom_id}'"
            );
        }
        update_data(
            "classroom_consent",
            "consent_use = $status, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "consent_id = '{$consent_id}'"
        );
        echo json_encode([
            'status' => true,
        ]);
    }
?>