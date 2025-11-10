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
    if(isset($_POST) && $_POST['action'] == 'buildPosition') {
        $table = "SELECT 
            p.position_id,
            p.position_name_en,
            p.position_name_th,
            p.position_cover,
            date_format(p.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            p.position_description,
            p.is_active
        FROM 
            classroom_position p 
        LEFT JOIN 
            m_employee_info i on i.emp_id = p.emp_create
        WHERE 
            p.comp_id = '{$_SESSION['comp_id']}' and p.status = 0 
        GROUP BY 
            p.position_id";
        $primaryKey = 'position_id';
        $columns = array(
            array('db' => 'position_id', 'dt' => 'position_id'),
            array('db' => 'position_name_en', 'dt' => 'position_name_en'),
            array('db' => 'position_name_th', 'dt' => 'position_name_th'),
            array('db' => 'position_cover', 'dt' => 'position_cover','formatter' => function ($d, $row) {
				if (!empty($d)) {
                    return GetUrl($d);
                } else {
                    return '/images/noimage.jpg';
                }
			}),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'position_description', 'dt' => 'position_description'),
            array('db' => 'is_active', 'dt' => 'is_active'),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'delPosition') {
        $id = $_POST['id'];
        update_data(
            "classroom_position",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "position_id = '{$id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'switchPosition') {
        $id = $_POST['id'];
        $option = $_POST['option'];
        $status = ($option == 0) ? 1 : 0;
        update_data(
            "classroom_position",
            "is_active = $status, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "position_id = '{$id}'"
        );
        echo json_encode([
            'status' => true,
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'positionData') {
        $id = $_POST['id'];
        $positions = select_data(
            "p.position_cover, p.position_name_en, p.position_name_th, p.position_description",
            "classroom_position p",
            "where p.position_id = '{$id}'"
        );
        $position = $positions[0];
        echo json_encode([
            'status' => true,
            'position_data' => [
                'position_cover' => ($position['position_cover']) ? GetUrl($position['position_cover']) : '',
                'position_name_en' => $position['position_name_en'],
                'position_name_th' => $position['position_name_th'],
                'position_description' => $position['position_description'],
            ]
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'savePosition') {
        $position_id = $_POST['position_id'];
        $position_name_en = initVal($_POST['position_name_en']);
        $position_name_th = initVal($_POST['position_name_th']);
        $position_description = initVal($_POST['position_description']);
        if($position_id) {
            update_data(
                "classroom_position",
                "position_name_en = $position_name_en, position_name_th = $position_name_th, position_description = $position_description, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "position_id = '{$position_id}'"
            );
        } else {
            $position_id = insert_data(
                "classroom_position", 
                "(position_name_en, position_name_th, position_description, comp_id, status, emp_create, date_create, emp_modify, date_modify)",
                "($position_name_en, $position_name_th, $position_description, '{$_SESSION['comp_id']}', 0, '{$_SESSION['emp_id']}', NOW(), '{$_SESSION['emp_id']}', NOW())"
            );
        }
        if($position_id) {
            $position_cover_name = $_FILES['position_cover']['name'];
            $position_cover_tmp = $_FILES['position_cover']['tmp_name'];
            $position_cover = null;
            $position_cover_thumb = null;
            if ($position_cover_name && $position_cover_tmp) {
                $strname = md5($_SESSION['comp_id'].'||'.$position_id);
                $position_cover_dir = 'uploads/' . $_SESSION['comp_id'] . '/classroom/position/';
                $path_info = pathinfo($position_cover_name);
                $position_cover_ext = strtolower($path_info['extension']);
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($position_cover_ext, $allowed_extensions)) {
                    echo json_encode([
                        'status' => false,
                        'message' => "Error: Invalid file type. Only JPG, PNG, GIF allowed."
                    ]);
                    exit;
                }
                $position_cover = $position_cover_dir . $strname . '.' . $position_cover_ext;
                $position_cover_thumb = $position_cover_dir . $strname . '_thumbnail.' . $position_cover_ext;
                $position_cover_save = "'{$position_cover}'";
                if (SaveFile($position_cover_tmp, $position_cover)) {
                    $thumb_local = sys_get_temp_dir() . '/' . uniqid('thumb_') . '.' . $position_cover_ext;
                    if (createThumbnail($position_cover_tmp, $thumb_local, 300, 300, 80)) {
                        SaveFile($thumb_local, $position_cover_thumb);
                        unlink($thumb_local);
                        update_data(
                            "classroom_position",
                            "position_cover = $position_cover_save",
                            "position_id = '{$position_id}'"
                        );
                    } else {
                        echo json_encode([
                            'status' => false,
                            'message' => "Warning: Could not create thumbnail"
                        ]);
                        exit;
                    }
                } else {
                    echo json_encode([
                        'status' => false,
                        'message' => "Error: Could not save original file"
                    ]);
                    exit;
                }
            }
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
?>