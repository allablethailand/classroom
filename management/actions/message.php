<?php
	header('Content-Type: text/html; charset=UTF-8');
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
    DEFINE('base_path', $base_path);
    DEFINE('base_include', $base_include);
	require_once($base_include."/lib/connect_sqli.php");
    require_once($base_include."/classroom/actions/mailsend.php");
    if (!isset($_SESSION['emp_id'])) {
        echo json_encode([
            'status' => 'redirect',
            'message' => 'Session expired',
            'redirect_url' => '/control_role_alert.php'
        ]);
        exit;
    }
    if ($_POST['action'] == 'buildMessage') {
        $classroom_id = isset($_POST['classroom_id']) ? (int)$_POST['classroom_id'] : 0;
        $table = "SELECT 
                    t1.template_id,
                    t1.template_subject,
                    CONCAT(
                        IFNULL(i.firstname, i.firstname_th), ' ',
                        IFNULL(i.lastname, i.lastname_th)
                    ) AS emp_name,
                    DATE_FORMAT(IFNULL(t1.date_create, t1.date_modify), '%Y/%m/%d %H:%i:%s') AS date_create,
                    IFNULL(t1.master_id, 0) AS master_id,
                    t1.template_description
                FROM 
                    classroom_message_template t1
                LEFT JOIN 
                    m_employee_info i ON i.emp_id = t1.emp_modify
                WHERE 
                    t1.classroom_id = '{$classroom_id}' AND t1.status = 0
        ";
        $primaryKey = 'template_id';
        $columns = array(
            array('db' => 'template_id', 'dt' => 'template_id'),
            array('db' => 'template_subject', 'dt' => 'template_subject'),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'master_id', 'dt' => 'master_id'),
            array('db' => 'template_description', 'dt' => 'template_description'),
            array('db' => 'emp_name', 'dt' => 'emp_name'),
        );
        $sql_details = array(
            'user' => $db_username,
            'pass' => $db_pass_word,
            'db'   => $db_name,
            'host' => $db_host
        );
        require($base_include.'/lib/ssp-subquery.class.php');
        echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
        exit;
    }
    if(isset($_POST) && $_POST['action'] == 'delTemplate') {
        $template_id = $_POST['template_id'];
        update_data(
            "classroom_message_template", "status = 0, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", "template_id = '{$template_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'dataTemplate') {
        $template_id = $_POST['template_id'];
        $messages = select_data(
            "template_subject, template_body, master_id", "classroom_message_template", "where template_id = '{$template_id}'"
        );
        echo json_encode(['template_data' => $messages[0]]);
    }
    if(isset($_POST) && $_POST['action'] == 'previewTemplate') {
        $classroom_id = $_POST['classroom_id'];
        $template_id = $_POST['template_id'];
        $messages = select_data(
            "template_subject, template_body", "classroom_message_template", "where template_id = '{$template_id}'"
        );
        $html = previewTemplate($classroom_id, $messages[0]['template_body'], '');
        echo json_encode([
            'status' => true,
            'template_subject' => $messages[0]['template_subject'],
            'template_body' => $html,
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'restoreTemplate') {
        $template_id = $_POST['template_id'];
        $masterId = select_data(
            "master_id", "classroom_message_template", "where template_id = '{$template_id}'"
        );
        $master_id = $masterId[0]['master_id'];
        $messages = select_data(
            "master_subject, master_description, master_body", "classroom_message_master" ,"where master_id = '{$master_id}'"
        );
        $m = $messages[0];
        $master_subject = initVal($m['master_subject']);
        $master_description = initVal($m['master_description']);
        $master_body = initVal($m['master_body']);
        update_data(
            "classroom_message_template",
            "template_subject = $master_subject, template_description = $master_description, template_body = $master_body, emp_modify = '{$_SESSION['emp_modify']}', date_modify = NOW()",
            "template_id = '{$template_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if (isset($_POST) && $_POST['action'] == 'saveTemplate') {
        $classroom_id = (int) $_POST['classroom_id'];
        $template_id = (int) $_POST['template_id'];
        $template = escape_string(trim($_POST['template']));
        $emp_id = (int) $_SESSION['emp_id'];
        $comp_id = (int) $_SESSION['comp_id'];
        update_data(
            "classroom_message_template", 
            "template_body = '{$template}', emp_modify = '{$emp_id}', date_modify = NOW()",
            "template_id = '{$template_id}'"
        );
        echo json_encode(['status' => true]);
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