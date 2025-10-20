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
    if(isset($_POST) && $_POST['action'] == 'buildPricing') {
        $classroom_id = $_POST['classroom_id'];
        $table = "SELECT 
            t.ticket_id,
            t.ticket_type,
            t.ticket_price,
            date_format(t.start_sale, '%Y/%m/%d') as start_sale,
            date_format(t.end_sale, '%Y/%m/%d') as end_sale,
            description,
            t.is_public,
            t.ticket_quota,
            t.ticket_default,
            concat(c.currency_name, ' (', c.currency_code, ')') as currency_name
        FROM 
            classroom_ticket t
        left join 
            m_currency_master c on c.currency_id = t.ticket_value
        WHERE 
            t.classroom_id = '{$classroom_id}' and t.status = 0";
        $primaryKey = 'ticket_id';
        $columns = array(
            array('db' => 'ticket_id', 'dt' => 'ticket_id'),
            array('db' => 'ticket_type', 'dt' => 'ticket_type'),
            array('db' => 'start_sale', 'dt' => 'start_sale'),
            array('db' => 'end_sale', 'dt' => 'end_sale'),
            array('db' => 'description', 'dt' => 'description'),
            array('db' => 'is_public', 'dt' => 'is_public'),
            array('db' => 'ticket_default', 'dt' => 'ticket_default'),
            array('db' => 'currency_name', 'dt' => 'currency_name'),
            array('db' => 'ticket_price', 'dt' => 'ticket_price','formatter' => function ($d, $row) {
                return number_format($d, 2);
			}),
            array('db' => 'ticket_quota', 'dt' => 'ticket_quota','formatter' => function ($d, $row) {
                return number_format($d);
			}),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'switchPrice') {
        $classroom_id = $_POST['classroom_id'];
        $ticket_id = $_POST['ticket_id'];
        $option = $_POST['option'];
        $status = ($option == 0) ? 1 : 0;
        update_data(
            "classroom_ticket",
            "is_public = $status, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "ticket_id = '{$ticket_id}'"
        );
        echo json_encode([
            'status' => true,
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'switchDefault') {
        $classroom_id = $_POST['classroom_id'];
        $ticket_id = $_POST['ticket_id'];
        $option = $_POST['option'];
        $status = ($option == 0) ? 1 : 0;
        if($status == 0) {
            update_data(
                "classroom_ticket", "ticket_default = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", "classroom_id = '{$classroom_id}' and ticket_default = 0"
            );
        }
        update_data(
            "classroom_ticket",
            "ticket_default = $status, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "ticket_id = '{$ticket_id}'"
        );
        echo json_encode([
            'status' => true,
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'delPrice') {
        $ticket_id = $_POST['ticket_id'];
        update_data(
            "classroom_ticket",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "ticket_id = '{$ticket_id}'"
        );
        echo json_encode([
            'status' => true,
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
    if(isset($_POST) && $_POST['action'] == 'priceData') {
        $ticket_id = $_POST['ticket_id'];
        $price = select_data(
            "t.ticket_type, t.ticket_price, t.ticket_quota, date_format(t.start_sale, '%Y/%m/%d') as start_sale, date_format(t.end_sale, '%Y/%m/%d') as end_sale, t.description, t.ticket_quota, c.currency_id,concat(c.currency_name, ' (', c.currency_code, ')') as currency_name", "classroom_ticket t", "left join m_currency_master c on c.currency_id = t.ticket_value where t.ticket_id = '{$ticket_id}'"
        );
        echo json_encode([
            'status' => true,
            'price_data' => [
                'ticket_type' => $price[0]['ticket_type'],
                'ticket_price' => $price[0]['ticket_price'],
                'start_sale' => $price[0]['start_sale'],
                'end_sale' => $price[0]['end_sale'],
                'description' => $price[0]['description'],
                'ticket_quota' => $price[0]['ticket_quota'],
                'currency_id' => $price[0]['currency_id'],
                'currency_name' => $price[0]['currency_name'],
            ]
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'savePrice') {
        $classroom_id = $_POST['classroom_id'];
        $ticket_id = $_POST['ticket_id'];
        $ticket_type = initVal($_POST['ticket_type']);
        $ticket_value = initVal($_POST['ticket_value']);
        $ticket_price = initVal($_POST['ticket_price']);
        $ticket_quota = initVal($_POST['ticket_quota']);
        $start_sale = initVal($_POST['start_sale']);
        $end_sale = initVal($_POST['end_sale']);
        $description = initVal($_POST['description']);
        if($ticket_id) {
            update_data(
                "classroom_ticket", "ticket_value = $ticket_value, ticket_type = $ticket_type, ticket_price = $ticket_price, ticket_quota = $ticket_quota, start_sale = $start_sale, end_sale = $end_sale, description = $description",
                "ticket_id = '{$ticket_id}'"
            );
        } else {
            insert_data(
                "classroom_ticket",
                "(classroom_id, comp_id, ticket_type, ticket_value, ticket_price, ticket_quota, start_sale, end_sale, description, is_public, status, emp_create, date_create, emp_modify, date_modify)",
                "('{$classroom_id}', '{$_SESSION['comp_id']}', $ticket_value, $ticket_type, $ticket_price, $ticket_quota, $start_sale, $end_sale, $description, 1, 0, '{$_SESSION['emp_id']}', NOW(), '{$_SESSION['emp_id']}', NOW())"
            );
        }
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'buildCurrency') {
        $keyword = trim($_GET['term']);
		$search = ($keyword) ? " where (currency_code like '%{$keyword}%' or currency_name like '%{$keyword}%) " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                currency_id as data_code,
                concat(currency_name, ' (', currency_code, ')') as data_desc
            from 
                m_currency_master 
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
?>