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
    $student_id = (isset($_SESSION['student_id'])) ? $_SESSION['student_id'] : '';
    $id = (isset($_GET['id'])) ? $_GET['id'] : '';
    $cid = (isset($_GET['cid'])) ? base64_decode($_GET['cid']) : '';
    if(!$_SESSION && empty($student_id) && empty($id) && empty($cid)) {
        echo '<script language="javascript">window.location="/";</script>';
        exit;
    }
    $val = explode('_', $id);
    if (count($val) == 3) {
        $learning_id = $val[2];
        $learning_type = 'learning_map';
    } else {
        $learning_id = $val[1];
        $learning_type = 'course';
    }
    $employee = select_data(
        "emp_id, classroom_id, comp_id", "classroom_student_join", "where student_id = '{$student_id}' and classroom_id = '{$cid}'"
    );
    $emp_id = $employee[0]['emp_id'];
    $classroom_id = $employee[0]['classroom_id'];
    $comp_id = $employee[0]['comp_id'];
    if(empty($emp_id)) {
        $employees = select_data(
            "emp_id", "classroom_student_join", "where student_id = '{$student_id}' and comp_id = '{$comp_id}'"
        );
        $emp_id = $employees[0]['emp_id'];
        if(empty($emp_id)) {
            $students = select_data(
                "student_firstname_en, student_lastname_en, student_firstname_th, student_lastname_th, student_email", "classroom_student", "where student_id = '{$student_id}'"
            ); 
            $firstname_en = escape_string($students[0]['student_firstname_en']);
            $lastname_en = escape_string($students[0]['student_lastname_en']);
            $firstname_th = escape_string($students[0]['student_firstname_th']);
            $lastname_th = escape_string($students[0]['student_lastname_th']);
            $email = escape_string($students[0]['student_email']);
            $emp_id = insert_data(
                "m_employee", "(email, comp_id, emp_type, system_type)", "('{$email}', '{$comp_id}', 'student', 4)"
            );
            if($emp_id) {
                insert_data(
                    "m_employee_info", "(emp_id, firstname, lastname, firstname_th, lastname_th)", "('{$emp_id}', '{$firstname_en}', '{$lastname_en}', '{$firstname_th}', '{$lastname_th}')"
                );
                update_data(
                    "classroom_student_join", "emp_id = '{$emp_id}'", "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'"
                );
            }
        }
    }
    $classrooms = select_data(
        "date_format(classroom_start, '%Y-%m-%d') as classroom_start, date_format(classroom_end, '%Y-%m-%d') as classroom_end", "classroom_template", "where classroom_id = '{$classroom_id}'"
    );
    $classroom_start = $classrooms[0]['classroom_start'];
    $classroom_end = $classrooms[0]['classroom_end'];
    $courses = select_data(
        "course_ref_id, course_type", 
        "classroom_course",
        "where classroom_id = '{$classroom_id}' and status = 0"
    );
    foreach($courses as $course) {
        if($course['course_type'] == 'course') {
            $exist = select_data(
                "trainee_id",
                "ot_employee_training",
                "where emp_id = '{$emp_id}' and trn_id = '{$course['course_ref_id']}'"
            );
            if(!empty($exist)) {
                update_data(
                    "ot_employee_training",
                    "emp_trn_start_date_time = '{$classroom_start}', emp_trn_end_date_time = '{$classroom_end}', status = 0, emp_modify = '{$emp_id}', date_modify = NOW()",
                    "emp_id = '{$emp_id}' and trn_id = '{$course['course_ref_id']}'"
                );
            } else {
                insert_data(
                    "ot_employee_training", "(emp_id, trainee_type, trn_id, emp_trn_start_date_time, emp_trn_end_date_time, status, emp_create, date_create, emp_modify, date_modify)", "('{$emp_id}', 'student', '{$course['course_ref_id']}', '{$classroom_start}', '{$classroom_end}', 0, '{$emp_id}', NOW(), '{$emp_id}', NOW())"
                );
            }
        } else {
            $exist = select_data(
                "trainee_id",
                "ot_learning_map_emp",
                "where emp_id = '{$emp_id}' and learning_map_id = '{$course['course_ref_id']}'"
            );
            if(!empty($exist)) {
                update_data(
                    "ot_learning_map_emp",
                    "learning_map_emp_start = '{$classroom_start}', learning_map_emp_end = '{$classroom_end}', status = 0, emp_modify = '{$emp_id}', date_modify = NOW()",
                    "emp_id = '{$emp_id}' and learning_map_id = '{$course['course_ref_id']}'"
                );
            } else {
                insert_data(
                    "ot_learning_map_emp", "(learning_map_id, emp_id, trainee_type, learning_map_emp_start, learning_map_emp_end, comp_id, status, emp_create, date_create, emp_modify, date_modify)", "('{$course['course_ref_id']}', '{$emp_id}', 'student', '{$classroom_start}', '{$classroom_end}', '{$comp_id}', 0, '{$emp_id}', NOW(), '{$emp_id}', NOW())"
                );
            }
            
        }
    }
    $_SESSION['emp_id'] = $emp_id;
    $_SESSION['comp_id'] = $comp_id;
    $_SESSION['classroom_redirect'] = '/classroom/study/class';
    header('Location: /academy/redirect.php?id='.$id);
?>