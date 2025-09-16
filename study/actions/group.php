<?php
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';

// if($_POST['action'] == 'view_group') {
//     // $page_project_id = $_POST['page_project_id'];
//     $class_generation_id = $_POST['class_gen_id'];
//     $columnGroup  = "group_id, classroom_id, group_name,group_logo,group_description";
//     $tableGroup = "classroom_group";
//     $whereGroup = "where classroom_id = {$class_generation_id} AND status = 0";
//     $userGroup = select_data($columnGroup, $tableGroup, $whereGroup);

//     echo json_encode($userGroup);
// }

if ($_GET['action'] == 'toggle_student') {

    $student_id = $_SESSION['student_id'];

    $columnStudent = "classroom_id";
    $tableStudent = "classroom_student_join";
    $whereStudent = "where student_id = '{$student_id}'";

    $student_class = select_data($columnStudent, $tableStudent, $whereStudent);
    $cur_class = $student_class[0]["classroom_id"];

    // var_dump($cur_class);

    $columnCourseGroup = "cs.student_id, cs.student_firstname_th, cs.student_lastname_th, cs.student_nickname_th, cs.student_gender, cs.student_image_profile, cs.student_email, cs.student_mobile";
    $tableCourseGroup = "classroom_student_join std_join
    LEFT JOIN classroom_student cs ON std_join.student_id = cs.student_id
    LEFT JOIN classroom_template template ON std_join.classroom_id = template.classroom_id";
    $whereCourseGroup = "where std_join.classroom_id = '{$cur_class}'";

    $student_list = select_data($columnCourseGroup, $tableCourseGroup, $whereCourseGroup);

    echo json_encode($student_list);
}
