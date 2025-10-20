<?php
    session_start();
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    $classroomCode = isset($parts[2]) ? $parts[2] : null;
    $channel = isset($parts[3]) ? $parts[3] : '';
    $line_client_id = isset($parts[4]) ? $parts[4] : '';
    if($line_client_id) {
        $state = "cid={$classroomCode}&lid={$line_client_id}";
        if($channel) {
            $state .= "&ch={$channel}";
        }
        header('Location: /classroom/lib/line/login.php?state=' . base64_encode($state));
        exit;
    }
    if(empty($classroomCode)) {
        header('Location: /');
        exit;
    }
    $data = $_SERVER['REQUEST_URI'];
    $fragment = 'register';
    if (strpos($data, '?') !== false) {
        $data_parts = explode('?', $data);
        $data = $data_parts[0];
        parse_str($data_parts[1], $params); 
    }
    if (!empty($params)) {
        $fragment = key($params);
    }
    if($fragment == 'payment') {
        if(!isset($_SESSION['student_id'])) {
            $fragment = 'register';
        }
    }
    switch("/" . $parts[0] . "/" . $parts[1]) {
        case '/classroom/register/':
        case '/classroom/register':
            require __DIR__.'/views/register.php';
            break;
        default:
            header('Location: /');
            exit;
    }
?> 