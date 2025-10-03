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
    include_once(__DIR__ . "/function.php");
    $code = isset($_GET['code']) ? trim($_GET['code']) : '';
    $state = isset($_GET['state']) ? trim($_GET['state']) : '';
    $control_page = "/control_role_alert.php";
    if (!$code || !$state) {
        redirect($control_page);
    }
    list($classroom_id, $line_client_id, $student_id, $channel_id) = parseState($state);
    $register_url = LINEHelper::getRegisterUrl($classroom_id, $channel_id);
    $token_data = LINEHelper::getLineToken($line_client_id);
    if (!$token_data) {
        redirectTo($register_url);
    }
    $result = LINEHelper::getAccessToken($code, $token_data['line_client_id'], $token_data['line_client_secret']);
    $access_token = isset($result['access_token']) ? $result['access_token'] : '';
    if (!$access_token) {
        redirectTo($register_url);
    }
    $profile = LINEHelper::getLineProfile($access_token);
    if (!isset($profile['userId'])) {
        redirectTo($register_url);
    }
    $userId = escape_string($profile['userId']);
    $displayName = isset($profile['displayName']) ? escape_string($profile['displayName']) : '';
    $pictureUrl = isset($profile['pictureUrl']) ? escape_string($profile['pictureUrl']) : '';
    $statusMessage = isset($profile['statusMessage']) ? escape_string($profile['statusMessage']) : '';
    createConnectionIfNotExist($profile, $student_id);
    $classrooms = select_data("classroom_key", "classroom_template", "where classroom_id = '{$classroom_id}'");
    $classroom_key = $classrooms[0]['classroom_key'];
    $hash_student_id = md5($student_id);
    $_SESSION['is_result'] = true;
    header("Location: /classroom/register/{$classroom_key}");
    exit;
?>