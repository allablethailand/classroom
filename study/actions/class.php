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
    $student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null;
    $student_classroom_id = select_data("classroom_id", "classroom_student_join", "WHERE student_id = '{$student_id}'");
    $classroom_id = $student_classroom_id[0]['classroom_id'];
    if ($_GET['action'] === 'loadAlumni') {
        $student_alumni = select_data(
            "classroom_id, classroom_name, classroom_information", 
            "classroom_template", 
            "WHERE classroom_id = '{$classroom_id}'");
        echo json_encode($student_alumni);
    }
    if ($_POST['action'] === 'loadClass' && !empty($_POST['classroom_id'])) {
        $class_id = $_POST['classroom_id'];
        $course_data = select_data(
        "cc.course_type,
        c.trn_id AS course_id,
        c.trn_subject AS course_name,
        c.picture_title AS course_cover,
        c.trn_location AS course_location,
        c.trn_from_time AS course_timestart,
        c.trn_to_time AS course_timeend,
        c.trn_by AS course_instructor,
        DATE_FORMAT(c.trn_date, '%d/%m/%Y') AS course_date,
        LENGTH(REPLACE(trn_by, ' ', '')) - LENGTH(REPLACE(REPLACE(trn_by, ' ', ''), ',', '')) + 1 AS trn_count_by",
        "classroom_course AS cc JOIN ot_training_list AS c on cc.course_ref_id = c.trn_id",
        "WHERE cc.classroom_id = '{$class_id}' 
            AND cc.status = 0"
        );

        echo json_encode($course_data);

    }