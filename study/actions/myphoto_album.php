<?php
    header('Content-Type: application/json');
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
    DEFINE('base_path', $base_path);
    DEFINE('base_include', $base_include);
    require_once $base_include.'/lib/connect_sqli.php';

    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
    setBucket($fsData);

    if(isset($_POST['action']) && $_POST['action'] == 'getPermission') {
        $classroom_id = $_POST['classroom_id'];

        $events = select_data(
            "cv.classroom_name as event_name,
            (
                case 
                    when date(cv.classroom_start) = date(cv.classroom_end) then date_format(cv.classroom_start,'%d %b %y')
                    else concat(date_format(cv.classroom_start,'%d %b %y'),' - ',date_format(cv.classroom_end,'%d %b %y'))
                end
            ) as event_date,
            concat(date_format(cv.classroom_start,'%H:%i %p'),' - ',date_format(cv.classroom_end,'%H:%i %p')) as event_time, cv.classroom_id as classroom_id", 
            "classroom_template cv", 
            "where cv.classroom_id = '{$classroom_id}' and cv.status = 0"
        );

        if(empty($events)) {
            echo json_encode([
                'status' => false
            ]);
            exit;
        }
        $event = $events[0];
        $images = select_data(
            "photo_id",
            "event_photo",
            "WHERE classroom_id = '{$event['classroom_id']}' AND status = 0 and public = 0"
        );
        $classroom_data = [
            'classroom_id' => $event['classroom_id'],
            'event_name' => $event['event_name'],
            'event_date' => $event['event_date'],
            'event_time' => $event['event_time'],
        ];
        echo json_encode([
            'status' => true, 
            'classroom_data' => $classroom_data,
            'image_count' => number_format(count($images))
        ]);
    }

    if (isset($_GET['action']) && $_GET['action'] === 'loadImage') {
        $classroom_id = isset($_GET['classroom_id']) ? intval($_GET['classroom_id']) : 0;
        $page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit  = 24;
        $offset = ($page - 1) * $limit;
        $photos = array();
        $images = select_data(
            "photo_id,photo_path,public, DATE_FORMAT(date_create, '%Y/%m/%d %H:%i:%s') AS date_create",
            "classroom_photo",
            "WHERE classroom_id = '{$classroom_id}' AND status = 0 and public = 0 ORDER BY date_create DESC LIMIT {$limit} OFFSET {$offset}"
        );
        if (!empty($images)) {
            foreach ($images as $row) {
                $full_url = GetPublicUrl($row['photo_path'], $row['public']);
                $pathinfo = pathinfo($row['photo_path']);
                $thumb_path = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_thumbnail.' . $pathinfo['extension'];
                $thumb_url = GetPublicUrl($thumb_path, $row['public']);
                $photos[] = array(
                    "id"      => (int)$row['photo_id'],
                    "url"     => $full_url,
                    "thumb"   => $thumb_url,
                    "caption" => "Mission Photo #" . $row['photo_id'],
                    "date"    => $row['date_create']
                );
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($photos);
        exit;
    }
    if(isset($_POST['action']) && $_POST['action'] == 'closeGallery') {
        $classroom_id = $_POST['classroom_id'];
        $columnEvent = "classroom_id";
        $tableEvent = "classroom_template";
        $whereEvent = "where classroom_id = '{$classroom_id}'";
        $Event = select_data($columnEvent,$tableEvent,$whereEvent);
        $event_code = $Event[0]['classroom_id'];
        
        echo json_encode([
            'status' => true,
            'event_code' => $event_code,
        ]);
    }
?>