<?php
    session_start();
    $url = $_SERVER['REQUEST_URI'];
    switch($url) {
        case '/classroom/teacher/':
        case '/classroom/teacher':
            require __DIR__.'/views/teacher.php';
        break;
        default: 
            header('Location: /classroom/teacher/');
    }
?>