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

$table = "SELECT 
        course.course_id,
        (
            CASE
                WHEN course.course_type = 'course' then c.trn_subject
                ELSE l.learning_map_name
            END
        ) as course_name,
        course.course_type,
        (
            CASE
                WHEN course.course_type = 'course' then c.picture_title
                ELSE l.learning_map_pic
            END
        ) as course_cover,
        date_format(course.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
        CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create,
        course.course_ref_id
    FROM 
        classroom_course course
    LEFT JOIN 
        ot_training_list c on c.trn_id = course.course_ref_id and course.course_type = 'course'
    LEFT JOIN 
        ot_learning_map_list l on l.learning_map_id = course.course_ref_id and course.course_type = 'learning_map'
    LEFT JOIN 
        m_employee_info i on i.emp_id = course.emp_create
    WHERE 
        course.classroom_id = '{$classroom_id}' and course.status = 0";


if($_POST && $_POST['action'] == 'fetch_history'){
    $user_id = $_SESSION['user_id'];
    $classroom_id = $_POST['classroom_id'];
    
    // TO DO;
    // GET HISTORY BASED ON USER ID AND CLASSROOM ID


    
    $data = array();
    $query = "SELECT * FROM study_history WHERE user_id = ? AND classroom_id = ? ORDER BY history_id DESC";
    $stmt = $db->prepare($query);
    $stmt->bind_param("ii", $user_id, $classroom_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(array('status' => 'success', 'data' => $data));
    exit;
}



?>