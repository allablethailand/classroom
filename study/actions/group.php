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

    // if($_POST['action'] == 'view_group') {
    //     // $page_project_id = $_POST['page_project_id'];
    //     $class_generation_id = $_POST['class_gen_id'];
    //     $columnGroup  = "group_id, classroom_id, group_name,group_logo,group_description";
    //     $tableGroup = "classroom_group";
    //     $whereGroup = "where classroom_id = {$class_generation_id} AND status = 0";
    //     $userGroup = select_data($columnGroup, $tableGroup, $whereGroup);

    //     echo json_encode($userGroup);
    // }


    
?>