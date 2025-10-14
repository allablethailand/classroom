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

// $hist_table = select_data(
//     "olh_id, 
//     olh_course_id,
//     ott.trn_subject,
//     ott.trn_by,
//     ott.trn_date, 
//     ott.trn_from_time,
//     ott.trn_to_time,
//     ott.trn_location,
//     (CASE 
//         WHEN ott.trn_type = 'inhouse' THEN 'inhouse'
//         WHEN ott.trn_type = 'public' THEN 'public'
//         WHEN ott.trn_type = 'both' THEN 'both'
//         WHEN ott.trn_type IS NULL THEN 'ไม่ระบุ'
//         ELSE 'ไม่ระบุ'
//     END) AS trn_type_description,
//     olh_learning_map_id, 
//     olh_topic,
//     olh_datetime_in, 
//     olh_emp, 
//     olh_comp, 
//     oth.learning_device",
//     "ot_learning_history AS oth JOIN ot_training_list AS ott on ott.olh_course_id = oth.trn_id",
//     "WHERE oth.olh_emp = '{$student_id}'");

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
        LENGTH(REPLACE(trn_by, ' ', '')) - LENGTH(REPLACE(REPLACE(trn_by, ' ', ''), ',', '')) + 1 AS trn_count_by
        ",
        "classroom_course AS cc JOIN ot_training_list AS c on cc.course_ref_id = c.trn_id",
        "WHERE cc.classroom_id = '{$class_id}' 
            AND cc.status = 0"
        );

//  search by filter function
if($_POST['action'] = 'searchFilter')
{

}

$student_id = getStudentId();
$alumni_list = select_data("classroom_id", "classroom_student_join", "WHERE student_id = '{$student_id}'");

$student_attendant = select_data("*", "ot_workshop_emp", "workshop_id = '{$workshop_id}' AND emp_id = '{$student_id}' AND status = 0");

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

        $topic = select_data(
        "p.topic_id,p.topic_no,p.topic_option,p.topic_item,ifnull(p.learn_flag,'N') as learn_flag,p.topic_duration,p.topic_views,p.topic_percent,p.topic_percent_pass,p.topic_percent_value,p.learn_button_flag,p.topic_time,s.topic_name,s.topic_speed,s.topic_skip",
        "ot_learning_plan p",
        "left join ot_training_topic_setup s on s.topic_id = p.topic_id where p.trn_id = '{$course_id}' and p.emp_id = '{$emp_id}' and p.status = 0 group by p.topic_id order by p.topic_no asc"
    );


    $topic_type = 'Workshop';
    $workshop++;
    $percent = 0;
    $stamp_in = '';
    $stamp_out = '';
    $activity = select_data(
        "activity_id",
        "ot_workshop_emp",
        "WHERE emp_id = '{$emp_id}' AND workshop_id = '{$topic_item}' AND activity_id <> 0 AND status = 0"
    );
    if (!empty($activity)) {
        $activity_id = $activity[0]['activity_id'];
        $TimeIn = select_data(
            "DATE_FORMAT(date_time, '%Y/%m/%d %H:%i:%s') AS stamp_in",
            "temp_attendance",
            "WHERE emp_id = '{$emp_id}' AND status = 'in' AND IFNULL(activity_id, 'X') = '{$activity_id}'"
        );
        if (!empty($TimeIn)) {
            $stamp_in = $TimeIn[0]['stamp_in'];
            $percent += 50;
        }
        $TimeOut = select_data(
            "DATE_FORMAT(date_time, '%Y/%m/%d %H:%i:%s') AS stamp_out",
            "temp_attendance",
            "WHERE emp_id = '{$emp_id}' AND status = 'out' AND IFNULL(activity_id, 'X') = '{$activity_id}'"
        );
        if (!empty($TimeOut)) {
            $stamp_out = $TimeOut[0]['stamp_out'];
            $percent += 50;
        }
    }
    $topic_info    = 'Workshop ' . $workshop . ' ' . $topic_name;
    $description1  = 'Stamp In ' . $stamp_in;
    $description2  = 'Stamp Out ' . $stamp_out;
    $description3  = 'Progress ' . number_format($percent, 2);
    break;


if($_POST && $_POST['action'] == 'fetch_history'){

    $std_id = $student_id;
    $classroom_id = $_POST['classroom_id'];
    
    // TO DO;
    // GET HISTORY BASED ON USER ID AND CLASSROOM ID



    // $data = array();
    // $query = "SELECT * FROM study_history WHERE user_id = ? AND classroom_id = ? ORDER BY history_id DESC";
    // $stmt = $db->prepare($query);
    // $stmt->bind_param("ii", $user_id, $classroom_id);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // while ($row = $result->fetch_assoc()) {
    //     $data[] = $row;
    // }
    // echo json_encode(array('status' => 'success', 'data' => $data));
    // exit;
}



?>