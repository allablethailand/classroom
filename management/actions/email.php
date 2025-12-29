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
            'redirect_url' => '/index.php'
        ]);
        exit;
    }
    if ($_POST['action'] == 'restoreTemplate') {
        $template_id = (int)$_POST['template_id'];
        $classroom_id = (int)$_POST['classroom_id'];
        $columnRefId = "mail_reference as master_template_id";
        $tableRefId = "classroom_mail_template";
        $whereRefId = "WHERE mail_template_id = '{$template_id}' AND classroom_id = '{$classroom_id}'";
        $RefId = select_data($columnRefId, $tableRefId, $whereRefId);
        if (empty($RefId)) {
            echo json_encode(['status' => false, 'message' => 'Reference template not found']);
            exit;
        }
        $master_template_id = (int)$RefId[0]['master_template_id'];
        $columnTemplate = "mail_master_html";
        $tableTemplate = "classroom_mail_master";
        $whereTemplate = "WHERE mail_master_id = '{$master_template_id}'";
        $Template = select_data($columnTemplate, $tableTemplate, $whereTemplate);
        if (empty($Template)) {
            echo json_encode(['status' => false, 'message' => 'Master template not found']);
            exit;
        }
        $mail_master_html = escape_string($Template[0]['mail_master_html']);
        $tableUpdData = "classroom_mail_template";
        $valueUpdData = "mail_description = '{$mail_master_html}', emp_modify = '" . (int)$_SESSION['emp_id'] . "', date_modify = NOW()";
        $whereUpdData = "mail_template_id = '{$template_id}' AND classroom_id = '{$classroom_id}'";
        update_data($tableUpdData, $valueUpdData, $whereUpdData);
        echo json_encode(['status' => true]);
    }
    if ($_POST['action'] == 'buildEmail') {
        $classroom_id = isset($_POST['classroom_id']) ? (int)$_POST['classroom_id'] : 0;
        $table = "SELECT 
                    t1.mail_template_id,
                    t1.mail_subject,
                    CONCAT(
                        IFNULL(i.firstname, i.firstname_th), ' ',
                        IFNULL(i.lastname, i.lastname_th)
                    ) AS emp_name,
                    DATE_FORMAT(IFNULL(t1.date_create, t1.date_modify), '%Y/%m/%d %H:%i:%s') AS date_create,
                    IFNULL(t1.mail_reference, 0) AS mail_reference,
                    t1.mail_reason,
                    t1.mail_name,
                    t1.mail_sending
                FROM 
                    classroom_mail_template t1
                LEFT JOIN 
                    m_employee_info i ON i.emp_id = t1.emp_modify
                WHERE 
                    t1.classroom_id = '{$classroom_id}' and t1.comp_id = '{$_SESSION['comp_id']}' AND t1.status = 0
        ";
        $primaryKey = 'mail_template_id';
        $columns = array(
            array('db' => 'mail_template_id', 'dt' => 'mail_template_id'),
            array('db' => 'mail_subject', 'dt' => 'mail_subject'),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'mail_reference', 'dt' => 'mail_reference'),
            array('db' => 'mail_reason', 'dt' => 'mail_reason'),
            array('db' => 'emp_name', 'dt' => 'emp_name'),
            array('db' => 'mail_name', 'dt' => 'mail_name'),
            array('db' => 'mail_sending', 'dt' => 'mail_sending'),
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
    if ($_POST['action'] == 'saveTemplate') {
        $classroom_id = (int) $_POST['classroom_id'];
        $template_id = (int) $_POST['template_id'];
        $template_subject = escape_string(trim($_POST['template_subject']));
        $template = escape_string(trim($_POST['template']));
        $emp_id = (int) $_SESSION['emp_id'];
        $comp_id = (int) $_SESSION['comp_id'];
        if ($template_id) {
            $tableUpdData = "classroom_mail_template";
            $valueUpdData = "
                mail_subject = '{$template_subject}',
                mail_description = '{$template}',
                emp_modify = '{$emp_id}',
                date_modify = NOW()
            ";
            $whereUpdData = "mail_template_id = '{$template_id}'";
            update_data($tableUpdData, $valueUpdData, $whereUpdData);
        } else {
            $tableInsData = "classroom_mail_template";
            $columnInsData = "(
                classroom_id,
                comp_id,
                mail_subject,
                mail_description,
                status,
                emp_create,
                date_create,
                emp_modify,
                date_modify,
                mail_reference
            )";
            $valueInsData = "(
                '{$classroom_id}',
                '{$comp_id}',
                '{$template_subject}',
                '{$template}',
                0,
                '{$emp_id}',
                NOW(),
                '{$emp_id}',
                NOW(),
                NULL
            )";
            insert_data($tableInsData, $columnInsData, $valueInsData);
        }
        echo json_encode(['status' => true]);
        exit;
    }
    if($_POST['action'] == 'delTemplate') {
        $template_id = (int) $_POST['template_id'];
        update_data("classroom_mail_template","status = 1,emp_modify = '{$_SESSION['emp_id']}',date_modify = NOW()","mail_template_id = '{$template_id}'");
        echo json_encode(['status' => true]);
    }
    if($_POST['action'] == 'dataTemplate') {
        $template_id = $_POST['template_id'];
        $valueData = "mail_subject,mail_description,mail_reference,classroom_id";
        $tableData = "classroom_mail_template";
        $whereData = "where mail_template_id = '{$template_id}'";
        $Data = select_data($valueData,$tableData,$whereData);
        $Data[0]['mail_description'] = str_replace('http://origami.local/','https://www.origami.life/',$Data[0]['mail_description']);
        $classroom_id = $Data[0]['classroom_id'];
        echo json_encode(['template_data' => $Data[0]]);
    }
    if($_POST['action'] == 'previewTemplate') {
        $classroom_id = $_POST['classroom_id'];
        $template = $_POST['template'];
        $html = previewTemplate($classroom_id,$template,'');
        echo json_encode(['status' => true,'template' => htmlspecialchars_decode($html)]);
    }
    if($_POST['action'] == 'previewTemplated') {
        $classroom_id = $_POST['classroom_id'];
        $template_id = $_POST['template_id'];
        $columnData = "mail_description";
        $tableData = "classroom_mail_template";
        $whereData = "where mail_template_id = '{$template_id}'";
        $Data = select_data($columnData,$tableData,$whereData);
        $template = $Data[0]['mail_description'];
        $html = previewTemplate($classroom_id,$template,'','','');
        echo json_encode(['status' => true,'template' => htmlspecialchars_decode($html)]);
    }
    if(isset($_POST) && $_POST['action'] == 'switchEmail') {
        $classroom_id = $_POST['classroom_id'];
        $template_id = $_POST['template_id'];
        $option = $_POST['option'];
        $status = ($option == 0) ? 1 : 0;
        update_data(
            "classroom_mail_template", "mail_sending = $status", "mail_template_id = '{$template_id}' and classroom_id = '{$classroom_id}'"
        );
        echo json_encode(['status' => true]);
    }
?>