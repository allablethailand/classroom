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
require_once $base_include . '/classroom/study/actions/student_func.php';

// if($_POST['action'] == 'view_group') {
//     // $page_project_id = $_POST['page_project_id'];
//     $class_generation_id = $_POST['class_gen_id'];
//     $columnGroup  = "group_id, classroom_id, group_name,group_logo,group_description";
//     $tableGroup = "classroom_group";
//     $whereGroup = "where classroom_id = {$class_generation_id} AND status = 0";
//     $userGroup = select_data($columnGroup, $tableGroup, $whereGroup);

//     echo json_encode($userGroup);
// }

if ($_GET['action'] == 'toggleMember') {

    $student_id = $_SESSION['student_id'];
    $alumni_id = getStudentClassroomId($student_id);

    $columnCourseGroup = "cs.student_id, cs.student_firstname_th, cs.student_lastname_th, cs.student_firstname_en, cs.student_lastname_en, cs.student_nickname_th, cs.student_gender, cs.student_image_profile, cs.student_email, cs.student_mobile";
    $tableCourseGroup = "classroom_student_join std_join";
    $whereCourseGroup = "LEFT JOIN classroom_student cs ON std_join.student_id = cs.student_id
        LEFT JOIN classroom_template template ON std_join.classroom_id = template.classroom_id
        WHERE std_join.classroom_id = '{$alumni_id}' AND std_join.status = 0 AND std_join.approve_status = 1";
    $student_list = select_data($columnCourseGroup, $tableCourseGroup, $whereCourseGroup);

    $columnTeacherGroup = "ct.teacher_id,
        ct.teacher_ref_id,
        ct.teacher_ref_type,
        ct.teacher_perfix,
        ct.teacher_firstname_en,
        ct.teacher_lastname_en,
        ct.teacher_firstname_th,
        ct.teacher_lastname_th,
        ct.teacher_gender,
        ct.teacher_nickname_en,
        ct.teacher_nickname_th,
        ct.teacher_image_profile,
        ct.teacher_email,
        ct.teacher_facebook,
        ct.teacher_line,
        ct.teacher_ig,
        ct.teacher_mobile,
        ct.teacher_address,
        ct.teacher_birth_date,
        ct.teacher_education,
        ct.teacher_experience,
        ct.teacher_music,
        ct.teacher_drink,
        ct.teacher_movie,
        ct.teacher_goal,
        ct.teacher_company,
        ct.teacher_company_detail,
        ct.teacher_company_url,
        ct.teacher_company_logo,
        ct.teacher_position,
        ct.position_id,
        ct.comp_id,
        ct.status,
        ct.emp_create,
        ct.date_create,
        ct.emp_modify,
        ct.date_modify";
    $tableTeacherGroup = "classroom_teacher ct
        LEFT JOIN classroom_teacher_join ctj ON ct.teacher_id = ctj.teacher_id
        LEFT JOIN classroom_position cp ON ct.position_id = cp.position_id";
    $whereTeacherGroup = "where ctj.classroom_id = '{$cur_class}' AND ctj.status = 0 AND cp.is_active = 0";
    $teacher_list = select_data($columnTeacherGroup, $tableTeacherGroup, $whereTeacherGroup);

//     function remove_numeric_keys($array) {
//     return array_map(function($row) {
//         return array_filter($row, function($key) {
//             return is_string($key);
//         }, ARRAY_FILTER_USE_KEY);
//     }, $array);
// }

//     $teacher_list_clean = remove_numeric_keys($teacher_list);
//     $student_list_clean = remove_numeric_keys($student_list);

    $teachers_assoc = [];
    foreach ($teacher_list as $t) {
        $teachers_assoc[$t['teacher_id']] = $t;
    }

    $students_assoc = [];
    foreach ($student_list as $s) {
        $students_assoc[$s['student_id']] = $s;
    }

    // var_dump($teachers_assoc);
    // var_dump($students_assoc);

    echo json_encode([
        'teacher_data' => $teachers_assoc,
        'student_data' => $students_assoc
    ]);
}
