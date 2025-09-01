<?php
    session_start();
    $url = $_SERVER['REQUEST_URI'];
    switch($url) {
        case '/classroom/management/':
        case '/classroom/management':
            require __DIR__.'/views/management.php';
        break;
        case '/classroom/management/detail/':
        case '/classroom/management/detail':
            require __DIR__.'/views/detail.php';
        break;
        default: 
            header('Location: /classroom/management/');
    }
?>