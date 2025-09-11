<?php
    session_start();
    define('ROOT', str_replace("\\", '/', dirname(__FILE__)));
    define('PATH', ROOT == $_SERVER['DOCUMENT_ROOT'] ? '' : substr(ROOT, strlen($_SERVER['DOCUMENT_ROOT'])));
    $uploadDir  = '/uploads/';
    $uploadPath = ROOT . $uploadDir;
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileName = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg','jpeg','png','gif','pdf','doc','docx');
        if (!in_array($ext, $allowed)) {
            echo json_encode(array('error' => 'Invalid file type'));
            exit;
        }
        $newName = date("YmdHis") . '.' . $ext;
        $target  = $uploadPath . $newName;
        if (move_uploaded_file($fileTmp, $target)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $dirUrl   = dirname($_SERVER['REQUEST_URI']);
            if (substr($dirUrl, -1) != '/') {
                $dirUrl .= '/';
            }
            $response = new StdClass;
            $response->link = $protocol . "://" . $_SERVER['HTTP_HOST'] . $dirUrl . "uploads/" . $newName;
            echo json_encode($response);
        } else {
            echo json_encode(array('error' => 'Upload failed'));
        }
    } else {
        echo json_encode(array('error' => 'No file uploaded'));
    }