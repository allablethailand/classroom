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
    $uploadDir  = "uploads/{$_SESSION['comp_id']}/classroom/information/";
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileName = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg','jpeg','png','gif','pdf','doc','docx');
        if (!in_array($ext, $allowed)) {
            echo json_encode(array('error' => 'Invalid file type'));
            exit;
        };
        $target  = $uploadDir . $fileName;
        if(SaveFile($fileTmp, $target)) {
            $response = new StdClass;
            $response->link = GetPublicUrl($target);
            echo json_encode($response);
        } else {
            echo json_encode(array('error' => 'Upload failed'));
        }
    } else {
        echo json_encode(array('error' => 'No file uploaded'));
    }