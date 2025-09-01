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
    if(isset($_POST) && $_POST['action'] == 'buildClassroom') {
        $filter_date = ($_POST['filter_date']) ? $_POST['filter_date'] : '';
        $filter_mode = ($_POST['filter_mode']) ? $_POST['filter_mode'] : '';
        $filter = "";
        if($filter_date) {
            $date = explode('-',$filter_date);
            $date_st = trim($date[0]);
            $date_ed = (trim($date[1])) ? trim($date[1]) : trim($date[0]);
            $data_st = substr($date_st,-4).'-'.substr($date_st,3,2).'-'.substr($date_st,0,2);
            $data_ed = substr($date_ed,-4).'-'.substr($date_ed,3,2).'-'.substr($date_ed,0,2);
            $filter .= " and (date(template.classroom_start) between date('{$data_st}') and date('{$data_ed}') or date(template.classroom_end) between date('{$data_st}') and date('{$data_ed}')) ";
        }
        if($filter_mode) {
            $filter .= " and template.classroom_type = '{$filter_mode}' ";
        }
        $table = "SELECT 
            template.classroom_id
        FROM 
            classroom_template template
        WHERE 
            template.comp_id = '{$_SESSION['comp_id']}' and template.status = 0 $filter";
        $primaryKey = 'classroom_id';
        $columns = array(
            array('db' => 'classroom_id', 'dt' => 'classroom_id'),
        );
        $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
?>