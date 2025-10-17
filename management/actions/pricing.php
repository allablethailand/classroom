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
            ticket_id,
            ticket_type,
            ticket_price,
            date_format(start_sale, '%Y/%m/%d') as start_sale,
            date_format(end_sale, '%Y/%m/%d') as end_sale,
            description,
            is_public,
            ticket_quota,
            ticket_default
        FROM 
            classroom_ticket
        WHERE 
            classroom_id = '{$classroom_id}' and status = 0";
        $primaryKey = 'ticket_id';
        $columns = array(
            array('db' => 'ticket_id', 'dt' => 'ticket_id'),
            array('db' => 'ticket_type', 'dt' => 'ticket_type'),
            array('db' => 'start_sale', 'dt' => 'start_sale'),
            array('db' => 'end_sale', 'dt' => 'end_sale'),
            array('db' => 'description', 'dt' => 'description'),
            array('db' => 'is_public', 'dt' => 'is_public'),
            array('db' => 'ticket_default', 'dt' => 'ticket_default'),
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
            "ticket_type, ticket_price, ticket_quota, date_format(start_sale, '%Y/%m/%d') as start_sale, date_format(end_sale, '%Y/%m/%d') as end_sale, description, ticket_quota", "classroom_ticket", "where ticket_id = '{$ticket_id}'"
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
            ]
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'savePrice') {
        $classroom_id = $_POST['classroom_id'];
        $ticket_id = $_POST['ticket_id'];
        $ticket_type = initVal($_POST['ticket_type']);
        $ticket_price = initVal($_POST['ticket_price']);
        $ticket_quota = initVal($_POST['ticket_quota']);
        $start_sale = initVal($_POST['start_sale']);
        $end_sale = initVal($_POST['end_sale']);
        $description = initVal($_POST['description']);
        if($ticket_id) {
            update_data(
                "classroom_ticket", "ticket_type = $ticket_type, ticket_price = $ticket_price, ticket_quota = $ticket_quota, start_sale = $start_sale, end_sale = $end_sale, description = $description",
                "ticket_id = '{$ticket_id}'"
            );
        } else {
            insert_data(
                "classroom_ticket",
                "(classroom_id, comp_id, ticket_type, ticket_price, ticket_quota, start_sale, end_sale, description, is_public, status, emp_create, date_create, emp_modify, date_modify)",
                "('{$classroom_id}', '{$_SESSION['comp_id']}', $ticket_type, $ticket_price, $ticket_quota, $start_sale, $end_sale, $description, 1, 0, '{$_SESSION['emp_id']}', NOW(), '{$_SESSION['emp_id']}', NOW())"
            );
        }
        echo json_encode([
            'status' => true
        ]);
    }
?>