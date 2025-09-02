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
    if(isset($_POST) && $_POST['action'] == 'buildGroup') {
        $classroom_id = $_POST['classroom_id'];
        $table = "SELECT 
            g.group_id,
            g.group_name,
            g.group_logo,
            g.group_description,
            date_format(g.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            g.group_color
        FROM 
            classroom_group g 
        LEFT JOIN 
            m_employee_info i on i.emp_id = g.emp_create
        WHERE 
            g.classroom_id = '{$classroom_id}' and g.status = 0";
        $primaryKey = 'group_id';
        $columns = array(
            array('db' => 'group_id', 'dt' => 'group_id'),
            array('db' => 'group_name', 'dt' => 'group_name'),
            array('db' => 'group_description', 'dt' => 'group_description'),
            array('db' => 'group_logo', 'dt' => 'group_logo','formatter' => function ($d, $row) {
				if (!empty($d)) {
                    return GetUrl($d);
                } else {
                    return '/images/noimage.jpg';
                }
			}),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'group_color', 'dt' => 'group_color'),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'delGroup') {
        $group_id = $_POST['group_id'];
        update_data(
            "classroom_group",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "group_id = '{$group_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'groupData') {
        $group_id = $_POST['group_id'];
        $groups = select_data(
            "
                g.group_name,
                g.group_logo,
                g.group_description,
                g.group_color
            ",
            "classroom_group g",
            "where g.group_id = '{$group_id}'"
        );
        $group = $groups[0];
        echo json_encode([
            'status' => true,
            'group_data' => [
                'group_name' => $group['group_name'],
                'group_logo' => ($group['group_logo']) ? GetUrl($group['group_logo']) : '',
                'group_description' => $group['group_description'],
                'group_color' => $group['group_color'],
            ]
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'saveGroup') {
        $classroom_id = $_POST['classroom_id'];
        $group_id = $_POST['group_id'];
        $group_name = initVal($_POST['group_name']);
        $group_description = initVal($_POST['group_description']);
        $group_color = initVal($_POST['group_color']);
        if($group_id) {
            update_data(
                "classroom_group",
                "group_name = $group_name, group_description = $group_description, group_color = $group_color, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "group_id = '{$group_id}'"
            );
        } else {
            $group_id = insert_data(
                "classroom_group",
                "(
                    classroom_id,
                    group_name,
                    group_description,
                    group_color,
                    comp_id,
                    status,
                    emp_create,
                    date_create,
                    emp_modify,
                    date_modify
                )",
                "(
                    '{$classroom_id}',
                    $group_name,
                    $group_description,
                    $group_color,
                    '{$_SESSION['comp_id']}',
                    0,
                    '{$_SESSION['emp_id']}',
                    NOW(),
                    '{$_SESSION['emp_id']}',
                    NOW()
                )"
            );
        }
        if($group_id) {
            $group_logo_name = $_FILES['group_logo']['name'];
            $group_logo_tmp = $_FILES['group_logo']['tmp_name'];
            $group_logo = null;
            $group_logo_thumb = null;
            if ($group_logo_name && $group_logo_tmp) {
                $strname = md5($classroom_id.'||'.$group_id);
                $group_logo_dir = 'uploads/classroom/' . $_SESSION['comp_id'] . '/group/';
                $path_info = pathinfo($group_logo_name);
                $group_logo_ext = strtolower($path_info['extension']);
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($group_logo_ext, $allowed_extensions)) {
                    echo json_encode([
                        'status' => false,
                        'message' => "Error: Invalid file type. Only JPG, PNG, GIF allowed."
                    ]);
                    exit;
                }
                $group_logo = $group_logo_dir . $strname . '.' . $group_logo_ext;
                $group_logo_thumb = $group_logo_dir . $strname . '_thumbnail.' . $group_logo_ext;
                $group_logo_save = "{$group_logo}";
                if (SaveFile($group_logo_tmp, $group_logo)) {
                    if (!createThumbnail($group_logo, $group_logo_thumb, 300, 300, 80)) {
                        echo json_encode([
                            'status' => false,
                            'message' => "Warning: Could not create thumbnail"
                        ]);
                        exit;
                    }
                } else {
                    echo "Error: Could not save original file";
                    echo json_encode([
                        'status' => false,
                        'message' => "Error: Could not save original file"
                    ]);
                    exit;
                    $group_logo = null;
                    $group_logo_thumb = null;
                    $group_logo_save = "null";
                }
            }
        } 
        update_data(
            "classroom_group",
            "group_logo = $group_logo_save",
            "group_id = '{$group_id}'"
        );
        echo json_encode(['status' => true]);
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