<?php
    session_start();
    $url = $_SERVER['REQUEST_URI'];
    switch($url) {
        case '/classroom/student/':
        case '/classroom/student':
            require __DIR__.'/views/student.php';
        break;
        default: 
            header('Location: /classroom/student/');
    }
?>