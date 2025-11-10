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
    if(isset($_POST) && $_POST['action'] == 'buildPayment') {
        $table = "SELECT 
            p.id,
            p.method_name,
            p.method_type,
            p.method_cover,
            date_format(p.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
            p.account_name,
            p.account_number,
            p.api_url,
            p.api_key,
            p.api_secret,
            p.is_active,
            b.bank_code,
            ifnull(b.bank_name_en,b.bank_name) as bank_name
        FROM 
            payment_methods p 
        LEFT JOIN 
            m_employee_info i on i.emp_id = p.emp_create
        LEFT JOIN 
            m_bank b on b.bank_id = p.bank_id
        WHERE 
            p.comp_id = '{$_SESSION['comp_id']}' and p.status = 0 
        GROUP BY 
            p.id";
        $primaryKey = 'id';
        $columns = array(
            array('db' => 'id', 'dt' => 'id'),
            array('db' => 'method_name', 'dt' => 'method_name'),
            array('db' => 'method_type', 'dt' => 'method_type'),
            array('db' => 'method_cover', 'dt' => 'method_cover','formatter' => function ($d, $row) {
				if (!empty($d)) {
                    return GetUrl($d);
                } else {
                    return '/images/noimage.jpg';
                }
			}),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
            array('db' => 'account_name', 'dt' => 'account_name'),
            array('db' => 'account_number', 'dt' => 'account_number'),
            array('db' => 'api_url', 'dt' => 'api_url'),
            array('db' => 'api_key', 'dt' => 'api_key'),
            array('db' => 'api_secret', 'dt' => 'api_secret'),
            array('db' => 'is_active', 'dt' => 'is_active'),
            array('db' => 'bank_code', 'dt' => 'bank_code'),
            array('db' => 'bank_name', 'dt' => 'bank_name'),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'delPayment') {
        $id = $_POST['id'];
        update_data(
            "payment_methods",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "id = '{$id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'switchPayment') {
        $id = $_POST['id'];
        $option = $_POST['option'];
        $status = ($option == 0) ? 1 : 0;
        update_data(
            "payment_methods",
            "is_active = $status, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "id = '{$id}'"
        );
        echo json_encode([
            'status' => true,
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'paymentData') {
        $id = $_POST['id'];
        $payments = select_data(
            "p.method_cover, p.method_name, p.method_type, p.account_name, p.account_number, b.bank_id, ifnull(b.bank_name_en,b.bank_name) as bank_name, p.api_url, p.api_key, p.api_secret",
            "payment_methods p",
            "left join m_bank b on b.bank_id = p.bank_id where p.id = '{$id}'"
        );
        $payment = $payments[0];
        echo json_encode([
            'status' => true,
            'payment_data' => [
                'method_cover' => ($payment['method_cover']) ? GetUrl($payment['method_cover']) : '',
                'method_name' => $payment['method_name'],
                'method_type' => $payment['method_type'],
                'account_name' => $payment['account_name'],
                'account_number' => $payment['account_number'],
                'bank_id' => $payment['bank_id'],
                'bank_name' => $payment['bank_name'],
                'api_url' => $payment['api_url'],
                'api_key' => $payment['api_key'],
                'api_secret' => $payment['api_secret'],
            ]
        ]);
    }
    if(isset($_GET['action']) && $_GET['action'] == 'buildBank') {
		$keyword = trim($_GET['term']);
		$search = ($keyword) ? " where (bank_name like '%{$keyword}%' or bank_name_en like '%{$keyword}%') " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                bank_id as data_code,
                ifnull(bank_name_en,bank_name) as data_desc 
            from 
                m_bank 
            $search
        ) data_table";
        $whereData = (($_GET['page']) ? "LIMIT ".$end.",".$start : "")."";
        $Data = select_data($columnData,$tableData,$whereData);
		$count_data = count($Data);
		$i = 0;
		while($i < $count_data) {
			$data[] = ['id' => $Data[$i]['data_code'],'col' => $Data[$i]['data_desc'],'total_count' => $count_data,'code' => $Data[$i]['data_code'],'desc' => $Data[$i]['data_desc'],];
			++$i;
		}
		if (empty($data)) {
			$data[] = ['id' => '','col' => '', 'total_count' => ''];
		}
        echo json_encode($data);
	}
    if(isset($_GET) && $_GET['action'] == 'savePayment') {
        $id = $_POST['id'];
        $method_name = initVal($_POST['method_name']);
        $method_type = initVal($_POST['method_type']);
        $account_number = initVal($_POST['account_number']);
        $account_name = initVal($_POST['account_name']);
        $bank_id = initVal($_POST['bank_id']);
        $api_url = initVal($_POST['api_url']);
        $api_key = initVal($_POST['api_key']);
        $api_secret = initVal($_POST['api_secret']);
        if($id) {
            update_data(
                "payment_methods",
                "method_name = $method_name, method_type = $method_type, account_name = $account_name, account_number = $account_number, bank_id = $bank_id, api_url = $api_url, api_key = $api_key, api_secret = $api_secret, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                "id = '{$id}'"
            );
        } else {
            $id = insert_data(
                "payment_methods", 
                "(method_name, method_type, account_name, account_number, bank_id, api_url, api_key, api_secret, comp_id, status, emp_create, date_create, emp_modify, date_modify)",
                "($method_name, $method_type, $account_name, $account_number, $bank_id, $api_url, $api_key, $api_secret, '{$_SESSION['comp_id']}', 0, '{$_SESSION['emp_id']}', NOW(), '{$_SESSION['emp_id']}', NOW())"
            );
        }
        if($id) {
            $method_cover_name = $_FILES['method_cover']['name'];
            $method_cover_tmp = $_FILES['method_cover']['tmp_name'];
            $method_cover = null;
            $method_cover_thumb = null;
            if ($method_cover_name && $method_cover_tmp) {
                $strname = md5($_SESSION['comp_id'].'||'.$id);
                $method_cover_dir = 'uploads/' . $_SESSION['comp_id'] . '/classroom/payment/';
                $path_info = pathinfo($method_cover_name);
                $method_cover_ext = strtolower($path_info['extension']);
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($method_cover_ext, $allowed_extensions)) {
                    echo json_encode([
                        'status' => false,
                        'message' => "Error: Invalid file type. Only JPG, PNG, GIF allowed."
                    ]);
                    exit;
                }
                $method_cover = $method_cover_dir . $strname . '.' . $method_cover_ext;
                $method_cover_thumb = $method_cover_dir . $strname . '_thumbnail.' . $method_cover_ext;
                $method_cover_save = "'{$method_cover}'";
                if (SaveFile($method_cover_tmp, $method_cover)) {
                    $thumb_local = sys_get_temp_dir() . '/' . uniqid('thumb_') . '.' . $method_cover_ext;
                    if (createThumbnail($method_cover_tmp, $thumb_local, 300, 300, 80)) {
                        SaveFile($thumb_local, $method_cover_thumb);
                        unlink($thumb_local);
                        update_data(
                            "payment_methods",
                            "method_cover = $method_cover_save",
                            "id = '{$id}'"
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