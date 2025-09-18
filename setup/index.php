<?php
    session_start();
    $url = $_SERVER['REQUEST_URI'];
    $parts = explode('?', $url);
    $path = $parts[0];

    switch($path) {
        case '/classroom/setup/':
        case '/classroom/setup':
            require __DIR__.'/views/setup.php';
        break;
        default: 
            header('Location: /classroom/setup/');
    }
?>