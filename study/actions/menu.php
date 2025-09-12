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

date_default_timezone_set('Asia/Bangkok');
$timeserver = date("Y-m-d H:i:s");
$dateSchedule = date('Y-m-d');


if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION['student_id'];
$dateSchedule = date('Y-m-d');

$student_classroom_id = select_data("classroom_id", "classroom_student_join", "WHERE student_id = '{$student_id}'");

if (!$student_classroom_id) {
    echo json_encode(['error' => 'Classroom not found']);
    exit();
}

$classroom_id = $student_classroom_id[0]['classroom_id'];

$scheduleItems = select_data(
    "course.trn_subject AS  schedule_name,
    course.trn_detail AS topic_name,
    course.trn_date AS date_start,
    course.trn_from_time AS time_start,
    course.trn_to_time AS time_end",
    "ot_training_list course 
        LEFT JOIN classroom_course cc ON course.trn_id = cc.course_ref_id
        JOIN ot_training_categories categories ON course.categories_id = categories.categories_id",
    "WHERE cc.classroom_id = {$classroom_id}
    AND cc.status = 0 
            AND course.status = 0 
            AND course.trn_date = '{$dateSchedule}' 
    ORDER BY time_start ASC LIMIT 5"
);

$now = new DateTime();

$soonClass = null;
$otherClasses = [];
$overdueClasses = [];

foreach ($scheduleItems as $item) {
    $classDateTime = new DateTime($item['date_start'] . ' ' . $item['time_start']);
    $minutesDiff = ($classDateTime->getTimestamp() - $now->getTimestamp()) / 60;

    if ($minutesDiff < 0) {
        // Class start time already passed - overdue class
        $item['overdue'] = true;
        $overdueClasses[] = $item;
    } else if ($minutesDiff <= 180 && $soonClass === null) {
        $item['starting_soon'] = true;
        $item['minutes_to_start'] = round($minutesDiff);
        $item['stamp_in_status'] = 'No Stamp'; // real logic
        $soonClass = $item;
    } else {
        $item['starting_soon'] = false;
        $otherClasses[] = $item;
    }
}

$response = [
    'soon_class' => $soonClass,
    'other_classes' => $otherClasses,
    'overdue_class' => $overdueClasses,
];


header('Content-Type: application/json');
echo json_encode($response);