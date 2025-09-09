<?php
    session_start();
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    switch($url) {
        case '/classroom/study/':
        case '/classroom/study':
            require __DIR__.'/views/menu.php';
        break;
        case '/classroom/study/schedule/':
        case '/classroom/study/schedule':
            require __DIR__.'/views/schedule.php';
        break;
        case '/classroom/study/calendar/':
        case '/classroom/study/calendar':
            require __DIR__.'/views/calendar.php';
        break;
        case '/classroom/study/myphoto/':
        case '/classroom/study/myphoto':
            require __DIR__.'/views/myphoto.php';
        break;
        case '/classroom/study/document/':
        case '/classroom/study/document':
            require __DIR__.'/views/document.php';
        break;
        case '/classroom/study/alarmni/':
        case '/classroom/study/alarmni':
            require __DIR__.'/views/alarmni.php';
        break;
        case '/classroom/study/group/':
        case '/classroom/study/group':
            require __DIR__.'/views/group.php';
        break;
        case '/classroom/study/student/':
        case '/classroom/study/student':
            require __DIR__.'/views/student.php';
        break;
        case '/classroom/study/studentinfo/':
        case '/classroom/study/studentinfo':
            require __DIR__.'/views/studentinfo.php';
        break;
        case '/classroom/study/profile/':
        case '/classroom/study/profile':
            require __DIR__.'/views/profile.php';
        break;
         case '/classroom/study/edit_profile/':
        case '/classroom/study/edit_profile':
            require __DIR__.'/views/edit_profile.php';
        break;
        case '/classroom/study/privacy_settings/':
        case '/classroom/study/privacy_settings':
            require __DIR__.'/views/privacy_settings.php';
        break;
        case '/classroom/study/setting/':
        case '/classroom/study/setting':
            require __DIR__.'/views/setting.php';
        break;
        case '/classroom/study/register/':
        case '/classroom/study/register':
            require __DIR__.'/views/register.php';
        break;

        case '/classroom/study/login/':
        case '/classroom/study/login':
            require __DIR__.'/views/login.php';
        break;
        case '/classroom/study/logout/':
        case '/classroom/study/logout':
            require __DIR__.'/views/logout.php';
        break;
        case '/classroom/study/chat/':
        case '/classroom/study/chat':
            require __DIR__.'/views/chat.php';
        break;
        case '/classroom/study/class/':
        case '/classroom/study/class':
            require __DIR__.'/views/class.php';
        break;
        case '/classroom/study/classinfo/':
        case '/classroom/study/classinfo':
            require __DIR__.'/views/classinfo.php';
        break;
        case '/classroom/study/history/':
        case '/classroom/study/history':
            require __DIR__.'/views/history.php';
        break;
        default: 
           header('Location: /classroom/study/');
    }
?> 