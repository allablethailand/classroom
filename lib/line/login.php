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
    include_once(__DIR__ . "/config.php");
    include_once(__DIR__ . "/LINEHelper.php");
    $control_page = "/control_role_alert.php";
    global $mysqli;
    $state = isset($_GET['state']) ? $_GET['state'] : '';
    if(!$state) {
        redirect($control_page);
        exit;
    }
    $decoded = base64_decode($state);
    $decoded = urldecode($decoded);
    parse_str($decoded, $params);
    $classroom_key = isset($params['cid']) ? $params['cid'] : '';
    $student_id = isset($params['stu']) ? $params['stu'] : '';
    $client_id = isset($params['lid']) ? $params['lid'] : '';
    $channel_id = isset($params['ch']) ? $params['ch'] : '';
    $classroom_key_safe = mysqli_real_escape_string($mysqli, $classroom_key);
    $classroom = select_data(
        "classroom_id", "classroom_template", "where classroom_key = '{$classroom_key_safe}' AND status = 0"
    );
    if (!$classroom) {
        redirect($control_page);
    }
    $classroom = $classroom[0];
    $classroom_id = (int) $classroom['classroom_id'];
    $login_url = LINEHelper::buildLoginUrl($classroom_id, $student_id, $client_id, $channel_id);
    if (!$login_url) {
        redirect($control_page);
    }
    redirect($login_url);
    function redirect($url) {
        header("Location: {$url}");
        exit;
    }
?>