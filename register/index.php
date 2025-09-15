<?php
    session_start();
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($url, '/'));
    $classroomCode = isset($parts[2]) ? $parts[2] : null;
    $channel = isset($parts[3]) ? $parts[3] : '';
    if(empty($classroomCode)) {
        header('Location: /');
        exit;
    }
    switch("/" . $parts[0] . "/" . $parts[1]) {
        case '/classroom/register/':
        case '/classroom/register':
            require __DIR__.'/views/register.php';
            break;
        default:
            header('Location: /classroom/register/');
            exit;
    }
?> 