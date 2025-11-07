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

$notifications = select_data("*", "ogm_notification", "WHERE FIND_IN_SET('" . mysqli_real_escape_string($mysqli, $emp_id) . "', noti_emp_id) AND noti_comp_id = '" . mysqli_real_escape_string($mysqli, $comp_id) . "' AND noti_status = 0 AND noti_read is null limit 100");
$company = select_data(
    "comp_logo, comp_logo_target",
    "m_company",
    "where comp_id = '{$comp_id}'"
);

if(isset($_POST) && $_POST['action'] == 'getNotification') {
    $notifications = select_data(
        "noti.noti_id,
        noti.noti_read_datetime,
        res.color,
        noti.noti_request,
        DATE_FORMAT(noti.noti_datetime, '%Y/%m/%d %H:%i:%s') AS noti_datetime,
        md.noti_module_name,
        res.noti_response,
        emp.firstname_th,
        emp.emp_pic,
        emp.gender,
        emp.emp_id,
        CASE
            WHEN md.noti_module_name = 'Activity' THEN act.activity_project_name
            WHEN md.noti_module_name = 'Project' THEN pro.project_name
            ELSE ''
        END AS notification_txt,
        CASE
            WHEN md.noti_module_name = 'Activity' THEN act.activity_description
            WHEN md.noti_module_name = 'Project' THEN pro.project_description
            ELSE ''
        END AS description",
        "ogm_notification AS noti",
        "LEFT JOIN ogm_notification_module AS md 
            ON noti.noti_module_id = md.noti_module_id
        LEFT JOIN ogm_notification_response AS res 
            ON noti.noti_response = res.noti_response
        LEFT JOIN m_employee_info AS emp 
            ON noti.emp_create = emp.emp_id
        LEFT JOIN stk_activity AS act 
            ON SUBSTRING_INDEX(SUBSTRING_INDEX(noti.noti_request, '\"id\":\"', -1), '\"', 1) = act.activity_id
        AND md.noti_module_name = 'Activity'
        LEFT JOIN stk_project AS pro 
            ON SUBSTRING_INDEX(SUBSTRING_INDEX(noti.noti_request, '\"id\":\"', -1), '\"', 1) = pro.project_id
        AND md.noti_module_name = 'Project'
        WHERE FIND_IN_SET('" . mysqli_real_escape_string($mysqli, $emp_id) . "', noti.noti_emp_id)
        AND noti.noti_comp_id = '" . mysqli_real_escape_string($mysqli, $comp_id) . "'
        AND noti.noti_status = 0
        ORDER BY noti.noti_datetime DESC limit 15"
    );
    $notification_data = [];
    foreach($notifications as $notification) {
        $notification_data[] = [
            'noti_id' => $notification['noti_id'],
            'noti_read_datetime' => $notification['noti_read_datetime'],
            'color' => $notification['color'],
            'noti_request' => $notification['noti_request'],
            'noti_datetime' => $notification['noti_datetime'],
            'noti_module_name' => $notification['noti_module_name'],
            'noti_response' => $notification['noti_response'],
            'firstname_th' => $notification['firstname_th'],
            'emp_pic' => ($notification['emp_pic']) ? GetMemberAvatar($notification['emp_pic']) : '',
            'gender' => $notification['gender'],
            'emp_id' => $notification['emp_id'],
            'notification_txt' => $notification['notification_txt'],
            'description' => $notification['description'],
        ];
    }
    echo json_encode([
        'status' => true,
        'notification_data' => $notification_data
    ]);
}


?>