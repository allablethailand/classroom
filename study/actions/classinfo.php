<?php

date_default_timezone_set("Asia/Bangkok");
@session_start();
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
require_once $base_include . '/lib/notification.php';
require_once $base_include . '/actions/func.php';

$payload = json_decode(file_get_contents('php://input'), false, 512, JSON_BIGINT_AS_STRING);
$fsData = getBucketMaster();
$filesystem_user = $fsData['fs_access_user'];
$filesystem_pass = $fsData['fs_access_pass'];
$filesystem_host = $fsData['fs_host'];
$filesystem_path = $fsData['fs_access_path'];
$filesystem_type = $fsData['fs_type'];
$fs_id = $fsData['fs_id'];
setBucket($fsData);

if (!isset($payload->action)) {
    echo json_encode(["status" => "Error: 'action' is not set"]);
} else {
    $action = $payload->action;
}

// $emp_id = $_SESSION['student_id'];
$emp_id = 64;

$comp_id = 3;

$new_class = 2;

$course_id = 2;

function classSchedule($classroom_id)
{
    $classroom_id = 2;

    $columnData = "
        w.workshop_id,
        w.workshop_name, 
        DATE_FORMAT(w.date_start,'%Y/%m/%d') as date_start, 
        DATE_FORMAT(w.time_start,'%H:%i') as time_start, 
        DATE_FORMAT(w.time_end,'%H:%i') as time_end";

    $tableData = "ot_workshop w INNER JOIN ot_training_topic_setup tts 
        ON w.workshop_id = tts.workshop_id 
        INNER JOIN classroom_course cc 
        ON tts.trn_id = cc.course_ref_id";

    $whereData = "WHERE cc.classroom_id = {$classroom_id}
        AND cc.status = 0 
        AND tts.status = 0 
        AND tts.workshop_id IS NOT NULL 
        AND tts.workshop_id <> ''";

    $schedule = select_data($columnData, $tableData, $whereData);

    return $schedule;
}

function stampIn($student_id, $class_id, $course_id, $comp_id, $device, $os, $browser, $lat = null, $lng = null) {
    $timeserver = date("Y-m-d H:i:s");

    // Insert new stamp_in record
    $columns = "(stamp_in_datetime, status, emp_id, comp_id, classroom_id, course_id, stamp_device, stamp_os, stamp_browser, time_lat, time_lng)";
    $values = "('{$timeserver}', 'class_stamp_in', '{$student_id}', '{$comp_id}', '{$class_id}', '{$course_id}', '{$device}', '{$os}', '{$browser}', " . ($lat ? "'{$lat}'" : "NULL") . ", " . ($lng ? "'{$lng}'" : "NULL") . ")";
    $stamp_id = insert_data("classroom_attendance", $columns, $values);

    return [
        'status' => $stamp_id ? true : false,
        'message' => $stamp_id ? 'Stamp in successful' : 'Failed to stamp in',
        'stamp_id' => $stamp_id
    ];
}


function stampOut($student_id, $class_id, $course_id, $comp_id, $device, $os, $browser, $lat = null, $lng = null) {
    $timeserver = date("Y-m-d H:i:s");

    // Find existing stamp_in record without stamp_out to update
    $existing = select_data(
        "*",
        "classroom_attendance",
        "WHERE emp_id = '{$student_id}' AND classroom_id = '{$class_id}' AND course_id = '{$course_id}' AND comp_id = '{$comp_id}' AND status = 'class_stamp_in' AND stamp_out_datetime IS NULL"
    );

    if (empty($existing)) {
        // No stamp_in found to stamp out
        return [
            'status' => false,
            'message' => 'No existing stamp_in found to stamp out.'
        ];
    }

    $attendance_id = $existing[0]['id_time'];

    // Update record with stamp_out_datetime and change status
    $update_fields = "
        stamp_out_datetime = '{$timeserver}',
        status = 'class_stamp_out',
        stamp_device_out = '{$device}',
        stamp_os_out = '{$os}',
        stamp_browser_out = '{$browser}',
        time_lat_out = " . ($lat ? "'{$lat}'" : "NULL") . ",
        time_lng_out = " . ($lng ? "'{$lng}'" : "NULL") . "
    ";
    $updated = update_data("classroom_attendance", $update_fields, "WHERE id_time = {$attendance_id}");

    return [
        'status' => $updated !== false,
        'message' => $updated !== false ? 'Stamp out successful' : 'Failed to stamp out'
    ];
}


if ($action == 'stamptime') {
    $state = false;
    $stamp_data = $payload->data;
    $device = $payload->device;
    $os = $stamp_data->os;
    $browser = $stamp_data->browser;

    $stamp_device = $device;
    $stamp_os = $os;
    $stamp_browser = $browser;

    $stamp_photo = $stamp_data->stamp_photo;
    $time_lat = $stamp_data->lat;
    $time_lng = $stamp_data->lng;

      // Check if there is an existing stamp_in without stamp_out
    $existing = select_data(
    "*",
    "classroom_attendance",
    "WHERE emp_id = '{$emp_id}' AND classroom_id = '{$class_id}' AND course_id = '{$course_id}' AND comp_id = '{$comp_id}' AND status = 'class_stamp_in' AND stamp_out_datetime IS NULL");

    if (empty($existing)) {
        stampIn($student_id, $class_id, $course_id, $comp_id, $device, $os, $browser, $time_lat, $time_lng);
    } else {
        stampOut($student_id, $class_id, $course_id, $comp_id, $device, $os, $browser, $time_lat, $time_lng);
    }
}
