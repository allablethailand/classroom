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
    if(isset($_POST) && $_POST['action'] == 'buildConsentData') {
        $classroom_id = $_POST['classroom_id'];
        $consents = select_data(
            "classroom_consent",
            "classroom_template",
            "where classroom_id = '{$classroom_id}'"
        );
        echo json_encode([
            'status' => true,
            'classroom_consent' => $consents[0]['classroom_consent']
        ]);
    }
    if(isset($_GET) && $_GET['action'] == 'saveConsent') {
        $classroom_id = $_POST['classroom_id'];
        $classroom_consent = initVal($_POST['classroom_consent']);
        update_data(
            "classroom_template",
            "classroom_consent = $classroom_consent",
            "classroom_id = '{$classroom_id}'"
        );
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