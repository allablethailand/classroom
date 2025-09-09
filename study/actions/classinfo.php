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
    return json_encode(["status" => "Error: 'action' is not set"]);
} else {
    $action = $payload->action;
}

// $emp_id = $_SESSION['student_id'];
$emp_id = 5;

// $comp_id = $_SESSION['comp_id'];
$comp_id = 3;

$new_class = 2;

function classSchedule($classroom_id) {

    $classroom_id = 2;

    $columnData = "w.workshop_name, 
        DATE_FORMAT(w.date_start,'%Y/%m/%d') as date_start, 
        DATE_FORMAT(w.time_start,'%H:%i') as time_start, 
        DATE_FORMAT(w.time_end,'%H:%i') as time_end";

    $tableData = "ot_workshop w INNER JOIN ot_training_topic_setup tts 
        ON w.workshop_id = tts.workshop_id 
        INNER JOIN classroom_course cc 
        ON tts.trn_id = cc.course_ref_id";

    $whereData = " cc.classroom_id = {$classroom_id}
        AND cc.status = 0 
        AND tts.status = 0 
        AND tts.workshop_id IS NOT NULL 
        AND tts.workshop_id <> ''";


    $base_sql = "SELECT w.workshop_name, DATE_FORMAT(w.date_start,'%Y/%m/%d') as date_start, DATE_FORMAT(w.time_start,'%H:%i') as time_start, DATE_FORMAT(w.time_end,'%H:%i') as time_end FROM ot_workshop w INNER JOIN ot_training_topic_setup tts ON w.workshop_id = tts.workshop_id INNER JOIN classroom_course cc ON tts.trn_id = cc.course_ref_id WHERE cc.classroom_id = 2 AND cc.status = 0 AND tts.status = 0 AND tts.workshop_id IS NOT NULL AND tts.workshop_id <> ''";

    $schedule = select_data($columnData, $tableData, $whereData);
    return $schedule;
}


if ($action == 'stamptime') {
    $state = false;
    $stamp_data = $payload->data;
    $device = $payload->device;
    $os = $stamp_data->os;
    $browser = $stamp_data->browser;
    // $stamp_device = json_encode($device);
    // $stamp_os = json_encode($os);
    // $stamp_browser = json_encode($browser);

    $stamp_device = $device;
    $stamp_os = $os;
    $stamp_browser = $browser;
    
    $stamp_photo = $stamp_data->stamp_photo;
    $time_lat = $stamp_data->lat;
    $time_lng = $stamp_data->lng;
    $stamp_status = stampStatus($emp_id, $comp_id);

    // echo json_encode($stamp_status);

    if ($stamp_status == 0 || $stamp_status == 1) {
        $stamp_status = ($stamp_status == 0) ? 'in' : 'out';
        $timeserver = date("Y-m-d H:i:s");
        $date_stamp_use = date("Y-m-d");
        $time_stamp_use = date("H:i:s");
        // $ShiftWork = ShiftWork($emp_id, $comp_id, $date_stamp_use);
        // $classSchedule = classSchedule($new_class);
        // $check_in = $ShiftWork['check_in'] . ':00';
        // $time_count = $ShiftWork['time_count'];


        // if ($time_count > 0) {
        //     $columnMinTime = "distinct id_time";
        //     $tableMinTime = "temp_attendance";
        //     $whereMinTime = "where date(date_time) = date('{$date_stamp_use}') and emp_id = '{$emp_id}' and comp_id = '{$comp_id}'";
        //     $MinTime = select_data($columnMinTime, $tableMinTime, $whereMinTime);
        //     $count_mintime = count($MinTime);
        //     if ($count_mintime == 0) {
        //         $time_shift_data = $date_stamp_use . ' ' . $check_in;
        //         $time_stamp_data = $date_stamp_use . ' ' . $time_stamp_use;
        //         $TimeDiff = select_data("
        //             GREATEST(
        //                 TIMESTAMPDIFF(MINUTE, '{$time_shift_data}', '{$time_stamp_data}'),
        //                 0
        //             ) AS minutes_late,
        //             SEC_TO_TIME(
        //                 GREATEST(
        //                     TIMESTAMPDIFF(MINUTE, '{$time_shift_data}', '{$time_stamp_data}'),
        //                     0
        //                 ) * 60
        //             ) AS time_late
        //         ", "DUAL", "");
        //         $minutes_late = (int) $TimeDiff[0]['minutes_late'];
        //         $time_late = $TimeDiff[0]['time_late'];
        //         $late = $time_late;
        //         $columnLate = "count(distinct id_time) as late_count";
        //         $tableLate = "temp_attendance";
        //         $whereLate = "where emp_id = '{$emp_id}' and comp_id = '{$comp_id}' and year(date_time) = year(NOW()) and ifnull(TIME_TO_SEC(time_late),0) >= 60";
        //         $Late = select_data($columnLate, $tableLate, $whereLate);
        //         $late_count = $Late[0]['late_count'];
        //         if ($minutes_late > 0) {
        //             $late_count = $late_count + 1;
        //         }
        //     } else {
        //         $late = '';
        //         $late_count = '';
        //     }
        //     $time_in = $check_in;
        // } else {
        //     $late = '';
        //     $time_in = '';
        //     $late_count = '';
        // }

        $stamp_status = "class_stamp_in";

        $tableInsStamp = "temp_attendance";
        $columnInsStamp = "(date_time, status, emp_id, comp_id, time_lat, time_lng, stamp_device, stamp_os, stamp_browser)";
        $valueInsStamp = "('{$timeserver}', '{$stamp_status}', '{$emp_id}', '{$comp_id}', '{$time_lat}', '{$time_lng}', '{$stamp_device}' ,'{$stamp_os}', '{$stamp_browser}')";
        $stamp_id = insert_data($tableInsStamp, $columnInsStamp, $valueInsStamp);

        if ($stamp_id && isset($stamp_photo)) {
            $photo = preg_replace("#^data:image/\w+;base64,#i", '', $stamp_photo->photo);
            $os_name = $os->name;
            $os_version = $os->version;
            if (strtolower($os_name) == 'ios' && $os_version >= 13) {
                $device_name = $stamp_os;
                $photo_exif = 0;
            } else {
                $photo_exif = $stamp_photo->photo_exif;
            }
            $photo_fullsize     = uploadBase64($photo, $photo_exif, $base_include, $base_path . "uploads/timestamp/{$comp_id}", NULL, '800');
            $photo_thumbnail = uploadBase64($photo, $photo_exif, $base_include, $base_path . "uploads/timestamp/{$comp_id}", '_thumbnail', '500');
            $photo_stamp = $stamp_photo->photo;
            $photo_stamp_base64 = 'data:image/png;base64,' . $photo_stamp;
            $photo_stamp_size = getBase64ImageSize($photo_stamp);
            $save_base = str_replace("'", "\'", htmlspecialchars(trim($photo_stamp_base64)));
            $tableInsStampPhoto = "temp_attendance_photo";
            $columnInsStampPhoto = "(temp_id, emp_id, comp_id, stamp_photo, stamp_photo_thumbnail, stamp_date,stamp_photo_tmp,stamp_photo_size)";
            $valueInsStampPhoto = "('$stamp_id', '{$emp_id}' ,'{$comp_id}', '{$photo_fullsize}', '{$photo_thumbnail}', '{$timeserver}','{$save_base}','{$photo_stamp_size}')";
            insert_data($tableInsStampPhoto, $columnInsStampPhoto, $valueInsStampPhoto);
            
            $photo_fullsize = $base_url . "/" . $photo_fullsize;
            $photo_thumbnail = $base_url . "/" . $photo_thumbnail;
            $device_name = $device->model;
            $os_name = $os->name;
            $os_version = $os->version;
            if (strpos(strtolower($stamp_os), 'android') !== false) {
                $device_name = $stamp_os;
            }
            $in_time = '';
            $total_time = '';
            if ($stamp_status == 'out') {
                $result = getAttendanceTime($timeserver, $emp_id, $comp_id, '');
                $in_time = $result['in_time'];
                $total_time = $result['total_time'];
            }
            generateMessage($emp_id, $comp_id, ucfirst($stamp_status), $device_name, $time_lat, $time_lng, $timeserver, $photo_fullsize, $photo_thumbnail, $late, $late_count, $in_time, $total_time);
        }
        $state = true;

    } else {
        $stamp_status = null;
        $state = false;
    }
    echo json_encode(["status" => $state, "stampstatus" => $stamp_status, "timeserver" => $timeserver]);
}
