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
            template.classroom_id,
            template.classroom_name,
            concat(date_format(template.classroom_start, '%Y/%m/%d %H:%i'),' - ',date_format(template.classroom_end, '%Y/%m/%d %H:%i')) as classroom_date,
            classroom_student,
            classroom_type as classroom_mode,
            count(student.join_id) as classroom_register,
            date_format(template.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            template.classroom_poster,
            template.classroom_key,
            '' as classroom_link,
            template.classroom_promote
        FROM 
            classroom_template template
        LEFT JOIN 
            classroom_student_join student on student.classroom_id = template.classroom_id and student.status = 0
        LEFT JOIN 
            m_employee_info i on i.emp_id = template.emp_create
        WHERE 
            template.comp_id = '{$_SESSION['comp_id']}' and template.status = 0 $filter
        GROUP BY 
            template.classroom_id";
        $primaryKey = 'classroom_id';
        $columns = array(
            array('db' => 'classroom_id', 'dt' => 'classroom_id'),
            array('db' => 'classroom_name', 'dt' => 'classroom_name'),
            array('db' => 'classroom_date', 'dt' => 'classroom_date'),
            array('db' => 'classroom_promote', 'dt' => 'classroom_promote'),
            array('db' => 'classroom_student', 'dt' => 'classroom_student','formatter' => function ($d, $row) {
                return number_format($d);
            }),
            array('db' => 'classroom_mode', 'dt' => 'classroom_mode'),
            array('db' => 'classroom_register', 'dt' => 'classroom_register','formatter' => function ($d, $row) {
                return number_format($d);
            }),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'classroom_key', 'dt' => 'classroom_key'),
            array(
                'db' => 'classroom_poster',
                'dt' => 'classroom_poster',
                'formatter' => function ($d, $row) {
                    if ($d) {
                        $info = pathinfo($d);
                        return GetUrl($info['dirname'] . '/' . $info['filename'] . '_thumbnail.' . $info['extension']);
                    }
                    return GetUrl('/images/training.jpg');
                }
            ),
            array(
                'db' => 'classroom_link',
                'dt' => 'classroom_link',
                'formatter' => function ($d, $row) {
                    global $domain_name;
                    $classroom_key = $row['classroom_key'];
                    $register_url = $domain_name . 'classroom/register/' .$classroom_key;
                    if ($register_url) {
                        return $register_url;
                    } else {
                        return '';
                    }
                }
            ),
        );
        $sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'delClassroom') {
        $classroom_id = $_POST['classroom_id'];
        update_data(
            "classroom_template",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "classroom_id = '{$classroom_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'viewLink') {
        global $domain_name;
        $classroom_id = $_POST['classroom_id'];
        $classrooms = select_data(
            "classroom_key, classroom_name, comp_id",
            "classroom_template",
            "where classroom_id = '{$classroom_id}'"
        );
        $classroom = $classrooms[0];
        $classroom_key = $classroom['classroom_key'];
        $classroom_name = $classroom['classroom_name'];
        $comp_id = $classroom['comp_id'];
        $tenant = select_data(
            "tenant_key",
            "ogm_tenant",
            "where comp_id = '{$comp_id}' and status = 0"
        );
        $tenant_key = '';
        if(!empty($tenant)) {
            $tenant_key = $tenant[0]['tenant_key'];
        }
        $login_url = $domain_name . $tenant_key;
        $register_url = $domain_name . 'classroom/register/' .$classroom_key;
        echo json_encode([
            'status' => true,
            'classroom_name' => $classroom_name,
            'login_url' => $login_url,
            'register_url' => $register_url
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'switchClassroom') {
        $classroom_id = $_POST['classroom_id'];
        $classroom_promote = $_POST['classroom_promote'];
        $new_promote = ((int) $classroom_promote == 0) ? 1 : 0;
        update_data(
            "classroom_template",
            "classroom_promote = {$new_promote}, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "classroom_id = '{$classroom_id}'"
        );
        echo json_encode([
            'status' => true,
            'new_promote' => $new_promote
        ]);

    }
?>