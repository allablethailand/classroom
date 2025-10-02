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

date_default_timezone_set('Asia/Bangkok');
$dateSchedule = date('Y-m-d');

$student_id = getStudentId();
if (!isset($student_id)) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$class_id = getStudentClassroomId($student_id);
if (!$class_id) {
    echo json_encode(['error' => 'Classroom not found']);
    exit();
}

$classroom_id = $class_id[0]['classroom_id'];
$startingSoonClasses = [];
$otherClass = [];
$overdueClass = [];
$ongoingClass = [];

if (isset($_POST) && $_POST['action'] == 'getUpcomingClass') {

    $scheduleItems = select_data(
        "cc.course_ref_id AS id,
        course.trn_subject AS schedule_name,
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
                AND course.trn_date = '{$dateSchedule}' "
    );

    $now = new DateTime();

    foreach ($scheduleItems as $item) {
        $classStart = new DateTime($item['date_start'] . ' ' . $item['time_start']);
        $classEnd = new DateTime($item['date_start'] . ' ' . $item['time_end']);
        $minutesDiff = ($classStart->getTimestamp() - $now->getTimestamp()) / 60;

        if ($now >= $classStart && $now <= $classEnd) {
            // Current time is within the class duration - ongoing class
            $item['ongoing'] = true;
            $ongoingClass[] = $item;
        } elseif ($minutesDiff < 0) {
            // Overdue class (class already started and ended)
            $item['overdue'] = true;
            $overdueClass[] = $item;
        } elseif ($minutesDiff > 0 && $minutesDiff <= 360) {
            // Starting soon within 6 hour
            $item['starting_soon'] = true;
            $item['minutes_to_start'] = round($minutesDiff); // your logic here
            $startingSoonClasses[] = $item;
        } else {
            // Other upcoming classes beyond 1 hour
            $item['starting_soon'] = false;
            $otherClass[] = $item;
        }
    }

    $response = [
        'ongoing_class' => $ongoingClass,
        'overdue_class' => $overdueClass,
        'starting_soon_class' => $startingSoonClasses,
        'other_upcoming_class' => $otherClass,
    ];

    echo json_encode($response);
}
