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
    if(isset($_POST) && $_POST['action'] == 'buildChannel') {
        $classroom_id = $_POST['classroom_id'];
        $table = "SELECT 
            c.channel_id,
            date_format(c.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            c.channel_name,
            c.channel_description,
            c.channel_logo,
            t.classroom_key,
            count(stu.student_id) as channel_student,
            c.channel_type,
            l.line_token_name,
            l.line_client_id,
            '' as classroom_link
        FROM 
            classroom_channel c
        LEFT JOIN 
            m_employee_info i on i.emp_id = c.emp_create 
        LEFT JOIN 
            classroom_template t on t.classroom_id = c.classroom_id
        LEFT JOIN 
            classroom_student_join stu on stu.channel_id = c.channel_id and stu.status = 0
        LEFT JOIN 
            line_token l on l.line_token_id = c.line_oa
        WHERE 
            c.classroom_id = '{$classroom_id}' and c.status = 0
        GROUP BY 
            c.channel_id";
        $primaryKey = 'channel_id';
        $columns = array(
            array('db' => 'channel_id', 'dt' => 'channel_id'),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'channel_name', 'dt' => 'channel_name'),
            array('db' => 'channel_description', 'dt' => 'channel_description'),
            array('db' => 'classroom_key', 'dt' => 'classroom_key'),
            array('db' => 'channel_type', 'dt' => 'channel_type'),
            array('db' => 'line_token_name', 'dt' => 'line_token_name'),
            array('db' => 'line_client_id', 'dt' => 'line_client_id'),
            array('db' => 'channel_logo', 'dt' => 'channel_logo','formatter' => function ($d, $row) {
                $channel_type = $row['channel_type'];
                if($channel_type == 1) {
                    return '/images/line-oa.png';
                } else {
                    return ($d) ? GetUrl($d) : '';
                }
			}),
            array('db' => 'classroom_link', 'dt' => 'classroom_link','formatter' => function ($d, $row) {
                global $domain_name;
                $channel_id = $row['channel_id'];
                $classroom_key = $row['classroom_key'];
                $channel_type = $row['channel_type'];
                $line_client_id = $row['line_client_id'];
                if($channel_type == 1 && $line_client_id) {
                    return $domain_name.'classroom/register/'.$classroom_key.'/'.md5($channel_id).'/'.$line_client_id;
                } else {
                    return $domain_name.'classroom/register/'.$classroom_key.'/'.md5($channel_id);
                }
			}),
            array('db' => 'channel_student', 'dt' => 'channel_student','formatter' => function ($d, $row) {
				return number_format($d);
			}),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'delChannel') {
        $channel_id = $_POST['channel_id'];
        update_data(
            "classroom_channel",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "channel_id = '{$channel_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'channelData') {
        $channel_id = $_POST['channel_id'];
        $channels = select_data(
            "
                c.channel_name,
                c.channel_logo,
                c.channel_description
            ",
            "classroom_channel c",
            "where c.channel_id = '{$channel_id}'"
        );
        $channel = $channels[0];
        echo json_encode([
            'status' => true,
            'channel_data' => [
                'channel_name' => $channel['channel_name'],
                'channel_logo' => ($channel['channel_logo']) ? GetUrl($channel['channel_logo']) : '',
                'channel_description' => $channel['channel_description'],
            ]
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'saveChannel') {
        $classroom_id = $_POST['classroom_id'];
        $channel_id = $_POST['channel_id'];
        $channel_name = initVal($_POST['channel_name']);
        $channel_description = initVal($_POST['channel_description']);
        if($channel_id) {
            update_data(
                "classroom_channel",
                "channel_name = $channel_name, channel_description = $channel_description, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "channel_id = '{$channel_id}'"
            );
        } else {
            $channel_id = insert_data(
                "classroom_channel",
                "(
                    classroom_id,
                    channel_name,
                    channel_description,
                    comp_id,
                    status,
                    emp_create,
                    date_create,
                    emp_modify,
                    date_modify
                )",
                "(
                    '{$classroom_id}',
                    $channel_name,
                    $channel_description,
                    '{$_SESSION['comp_id']}',
                    0,
                    '{$_SESSION['emp_id']}',
                    NOW(),
                    '{$_SESSION['emp_id']}',
                    NOW()
                )"
            );
        }
        if($channel_id) {
            $channel_logo_name = $_FILES['channel_logo']['name'];
            $channel_logo_tmp = $_FILES['channel_logo']['tmp_name'];
            $channel_logo = null;
            $channel_logo_thumb = null;
            if ($channel_logo_name && $channel_logo_tmp) {
                $strname = md5($channel_id);
                $channel_logo_dir = "uploads/{$_SESSION['comp_id']}/classroom/channel/";
                $path_info = pathinfo($channel_logo_name);
                $channel_logo_ext = strtolower($path_info['extension']);
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($channel_logo_ext, $allowed_extensions)) {
                    echo json_encode([
                        'status' => false,
                        'message' => "Error: Invalid file type. Only JPG, PNG, GIF allowed."
                    ]);
                    exit;
                }
                $channel_logo = $channel_logo_dir . $strname . '.' . $channel_logo_ext;
                $channel_logo_thumb = $channel_logo_dir . $strname . '_thumbnail.' . $channel_logo_ext;
                $channel_logo_save = "'{$channel_logo}'";
                if (SaveFile($channel_logo_tmp, $channel_logo)) {
                    $thumb_local = sys_get_temp_dir() . '/' . uniqid('thumb_') . '.' . $channel_logo_ext;
                    if (createThumbnail($channel_logo_tmp, $thumb_local, 300, 300, 80)) {
                        SaveFile($thumb_local, $channel_logo_thumb);
                        unlink($thumb_local);
                        update_data(
                            "classroom_channel",
                            "channel_logo = $channel_logo_save",
                            "channel_id = '{$channel_id}'"
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
    if(isset($_POST) && $_POST['action'] == 'lineOAList') {
        $classroom_id = initVal($_POST['classroom_id']);
        $lines = select_data(
            "line_token_id, line_token_name",
            "line_token",
            "where status = 0 and comp_id = '{$_SESSION['comp_id']}' and line_token_id NOT IN (SELECT line_oa FROM classroom_channel WHERE classroom_id = $classroom_id AND channel_type = 1 AND status = 0) ORDER BY line_token_name ASC"
        );
        if(count($lines) > 0) {
            echo json_encode([
                'status' => true,
                'line_oa' => $lines
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'No Line OA found. Please add Line OA first.'
            ]);
        }
    }
    if(isset($_POST) && $_POST['action'] == 'addLineOAChannel') {
        $classroom_id = initVal($_POST['classroom_id']);
        $line_token_id = initVal($_POST['line_token_id']);
        $existing = select_data(
            "channel_id",
            "classroom_channel",
            "where classroom_id = $classroom_id and line_oa = $line_token_id and channel_type = 1"
        );
        if(count($existing) > 0) {
            update_data(
                "classroom_channel",
                "status = 0, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "channel_id = '{$existing[0]['channel_id']}'"
            );
            echo json_encode([
                'status' => true
            ]);
            exit();
        } else {
            $channel_id = insert_data(
                "classroom_channel",
                "(
                    classroom_id,
                    channel_name,
                    channel_description,
                    channel_type,
                    line_oa,
                    comp_id,
                    status,
                    emp_create,
                    date_create,
                    emp_modify,
                    date_modify
                )",
                "(
                    $classroom_id,
                    (SELECT line_token_name FROM line_token WHERE line_token_id = $line_token_id),
                    null,
                    1,
                    $line_token_id,
                    '{$_SESSION['comp_id']}',
                    0,
                    '{$_SESSION['emp_id']}',
                    NOW(),
                    '{$_SESSION['emp_id']}',
                    NOW()
                )"
            );
            if($channel_id) {
                echo json_encode([
                    'status' => true
                ]);
                exit();
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Error: Could not add Line OA channel.'
                ]);
                exit();
            }
        }
    }
?>