<?php
    session_start();
    $url = $_SERVER['REQUEST_URI'];
    $parts = explode('?', $url);
    $path = $parts[0];
    switch($path) {
        case '/classroom/management/':
        case '/classroom/management':
            require __DIR__.'/views/management.php';
        break;
        case '/classroom/management/detail/':
        case '/classroom/management/detail':
            require __DIR__.'/views/detail.php';
        break;
        case '/classroom/management/form/':
        case '/classroom/management/form':
            require __DIR__.'/views/form.php'; 
        break;
        case '/classroom/management/group/':
        case '/classroom/management/group':
            require __DIR__.'/views/group.php'; 
        break;
        case '/classroom/management/photo/':
        case '/classroom/management/photo':
            require __DIR__.'/views/photo.php'; 
        break;
        default: 
            header('Location: /classroom/management/');
    }
?>