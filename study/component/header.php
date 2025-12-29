<?php
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
	require_once($base_include."/lib/connect_sqli.php");
	require_once($base_include."/actions/func.php");
	require_once($base_include."/classroom/study/actions/student_func.php");

    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);

global $mysqli;
// Get current directory or page identifier, example by parsing URL path
$uriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePath = 'classroom/study';

if (strpos($uriPath, $basePath) === 0) {
    // Remove the basePath segment from the path
    $relativePath = trim(substr($uriPath, strlen($basePath)), '/');
} else {
    $relativePath = $uriPath;
}

$segments = explode('/', $relativePath);
$currentScreen = isset($segments[0]) && $segments[0] !== '' ? $segments[0] : 'menu';
$currentScreen = str_replace('_', ' ', $currentScreen);
// Insert space before 'info' if attached directly to other words
$currentScreen = preg_replace('/([a-z])info/i', '$1 info', $currentScreen);

// Convert to first letter to uppercase
$currentScreen = ucwords($currentScreen);
// if ($currentScreen = "Lang Setting"){
//     $currentScreen = "Language";
// }

if (!isset($_SESSION['student_id'])) {
    header("Location: /classroom/study/login");
    exit();
}

$studentId = (int)$_SESSION['student_id'];

// *** ส่วนที่แก้ไข: ดึงรูปภาพจาก classroom_file_student และดึง group_color ***
$student_image_profile = '/images/default.png'; // ตั้งค่ารูปเริ่มต้น
$profile_border_color = '#ff8c00'; // ตั้งค่าสีเริ่มต้น

// 1. ดึงข้อมูล group_color
$sql_color = "
    SELECT 
        cg.group_color
    FROM `classroom_student_join` csj
    LEFT JOIN `classroom_group` cg ON csj.group_id = cg.group_id
    WHERE csj.student_id = ?
";
$stmt_color = $mysqli->prepare($sql_color);
$stmt_color->bind_param("i", $studentId);
$stmt_color->execute();
$result_color = $stmt_color->get_result();
$row_color = $result_color->fetch_assoc();
if ($row_color && !empty($row_color['group_color'])) {
    $profile_border_color = htmlspecialchars($row_color['group_color']);
}
$stmt_color->close();

// 2. ดึงรูปโปรไฟล์หลักจาก classroom_file_student
$sql_image = "
    SELECT file_path
    FROM `classroom_file_student`
    WHERE student_id = ? AND file_type = 'profile_image' AND file_status = 1 AND is_deleted = 0
    ORDER BY file_order ASC
    LIMIT 1
";
$stmt_image = $mysqli->prepare($sql_image);
$stmt_image->bind_param("i", $studentId);
$stmt_image->execute();
$result_image = $stmt_image->get_result();
$row_image = $result_image->fetch_assoc();

if ($row_image && !empty($row_image['file_path'])) {
    $student_image_profile = GetUrl($row_image['file_path']);
} else {
    // ถ้าไม่พบรูปในตาราง classroom_file_student ให้ใช้รูปจากตาราง classroom_student แทน
    $sql_fallback = "SELECT student_image_profile FROM `classroom_student` WHERE student_id = ?";
    $stmt_fallback = $mysqli->prepare($sql_fallback);
    $stmt_fallback->bind_param("i", $studentId);
    $stmt_fallback->execute();
    $result_fallback = $stmt_fallback->get_result();
    $row_fallback = $result_fallback->fetch_assoc();
    if ($row_fallback && !empty($row_fallback['student_image_profile'])) {
        $student_image_profile = GetUrl($row_fallback['student_image_profile']);
    }
    $stmt_fallback->close();
}
$stmt_image->close();


// 3. ดึงชื่อนักเรียนเพื่อแสดงผล
$sql_name = "
    SELECT CONCAT(
    IFNULL(student_firstname_en, student_firstname_th),
    ' ',
    IFNULL(student_lastname_en, student_firstname_th)) AS student_name
    FROM `classroom_student`
    WHERE student_id = ?
";
$stmt_name = $mysqli->prepare($sql_name);
$stmt_name->bind_param("i", $studentId);
$stmt_name->execute();
$result_name = $stmt_name->get_result();
$row_name = $result_name->fetch_assoc();
$student_name = $row_name['student_name'] ? $row_name['student_name'] : "User";
$stmt_name->close();

$hide_profile = ["Profile", "Edit Profile", "Setting"];

// $notifications = select_data("*", "ogm_notification", "WHERE FIND_IN_SET('" . mysqli_real_escape_string($mysqli, $emp_id) . "', noti_emp_id) AND noti_comp_id = '" . mysqli_real_escape_string($mysqli, $comp_id) . "' AND noti_status = 0 AND noti_read is null limit 100");
// $count_notification = count($notifications);

$notification_data = [
    [
        "header" => "Version alert",
        "message" => "แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1",
        "class" => "notification-item",
        "path" => "/alerts/version",
        "img" => "https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png"
    ],
];

        function truncateMessage($text, $maxLength = 50) {
            if (mb_strlen($text) > $maxLength) {
                return mb_substr($text, 0, $maxLength) . '...';
            }
            return $text;
        }


?>

<head>
    <link rel="stylesheet" href="/classroom/study/css/header.css?v=<?php echo time(); ?>">
    
    <style>
        .profile-avatar-bordered {
            width: 54px;
            height: 54px;
            border-radius: 100%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2px;
            box-sizing: border-box;
            background-color: transparent;
        }
        /* แก้ไข: ใช้คลาสใหม่เพื่อแยกสไตล์ของรูปโปรไฟล์ */
        .profile-avatar-bordered img {
            width: 100%;
            height: 100%;
            border-radius: 100%;
            object-fit: cover;
            border: 2px solid transparent; /* สร้าง border ซ้อนกันเพื่อความเนียน */
        }
    </style>
    <script src="/classroom/study/js/header.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>


<div class="desktop-navbar">
  <aside id="sidenav-store1" class="sidenav">
    <div>
        <ul id="menuListContainerAdmin1" style="padding: 0px !important;" class="menu-list">
            <li class="has-submenu">
            <a href="menu">
            <i class="fas fa-home"  style="margin-right: 5px;"></i>
            <span class="menu-title" data-lang="home"> Home</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="schedule">
            <i class="fas fa-book-open"  style="margin-right: 5px;"></i>
            <span class="menu-title"  data-lang="schedule"> Schedule</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="alumni">
            <i class="fas fa-school" style="margin-right: 5px;"></i>
            <span class="menu-title" data-lang="alumni"> Alumni</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="chat">
            <i class="fas fa-robot" style="margin-right: 5px;"></i>
            <span class="menu-title" data-lang="askai" > Ask AI</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="class">
            <i class="fas fa-chalkboard-teacher"  style="margin-right: 5px;"></i>
            <span class="menu-title"  data-lang="classroom"> Classroom</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="calendar">
            <i class="fas fa-calendar-week"  style="margin-right: 5px;"></i>
            <span class="menu-title"  data-lang="calendar"> Calendar</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="history">
            <i class="fas fa-history"  style="margin-right: 5px;"></i>
            <span class="menu-title"  data-lang="history"> History</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="game">
            <i class="fas fa-gamepad"  style="margin-right: 5px;"></i>
            <span class="menu-title" data-lang="minigame"> Mini Game</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="myphoto">
            <i class="fas fa-images"  style="margin-right: 5px;"></i>
            <span class="menu-title" data-lang="myphoto"> My Photo</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        </ul>
    </div>
    </aside>
  
    <div class="origami-header-dashboard">
        <div class="container-topnav">
            <div class="header-topnav">
                <div class="somebox-flex" style="display: flex;">
                    <a class="navbar-brand-test menu-btn" id="menu-left" style="pointer-events: auto;">
                        <img id="menu-icon" src="/images/menu/Hamberger Icon.svg" alt="Menu">
                    </a>
                    <div class="title-group-topnav">
                        <a href="menu">
                            <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                        </a>
                        <div class="dissappear-text">
                            <h1 style="color:black !important;">Green Tech</h1>
                            <p style="color:black !important;">Hello ! <?php echo $student_name; ?></p>
                        </div>
                    </div>
                </div>
                <div class="icons">
                    <!-- Dropdown wrapper -->
                    <div class="dropdown" style="display: inline-block; vertical-align: middle;">
                        <button class="bell-button btn btn-default dropdown-toggle" type="button" id="bellDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: none; border: none; padding: 0;">
                            <img id="menu-icon" src="/images/menu/Bell.svg" alt="Noti" style="padding: 5px;">
                            <span class="notification-badge">1</span>
                        </button>
                        <ul class="dropdown-menu centered" aria-labelledby="bellDropdown">
                            <a class="notification dropdown-toggle menu-readall text-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="update_notiStatus_read();">
                            <span><i class="fas fa-check-circle"></i></span>
                                &nbsp; Mark all as read
                            </a>
                            <?php foreach ($notification_data as $notification): ?>
                                <!-- class="<?= htmlspecialchars($notification['class']) ?>" -->

                                    <li>
                                        <a 
                                        href="<?php echo htmlspecialchars($notification['path']) ?>" 
                                        class="<?= htmlspecialchars($notification['class']) ?>" 
                                        data-message="<?= htmlspecialchars($notification['message']) ?>"
                                        style="display:flex; align-items: center;"
                                        >
                                            <img 
                                                src="<?= htmlspecialchars($notification['img']) ?>" 
                                                alt="error" 
                                                style="width: 30px; height: 30px; border-radius: 100%; margin-right: 10px;"
                                            >
                                            <span style="font-weight: bolder;"><?= htmlspecialchars($notification['header']) ?>: New!</span>
                                            <p style="margin-left: 10%;"><?= htmlspecialchars(truncateMessage($notification['message'], 50)) ?></p>
                                        </a>
                                    </li>
                                    <!-- <li class="divider"></li> -->
                                <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="dropdown" style="display: inline-block; vertical-align: middle;">
                    <button id="profileDropdown" 
                        class="btn dropdown-toggle" 
                        type="button" 
                        data-toggle="dropdown" 
                        aria-haspopup="true" 
                        aria-expanded="false" 
                        style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?>; padding: 0; width: 34px; height: 34px;"
                        >
                        <img height="30" width="30" id="avatar_h" name="avatar_h"  title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'" alt="Profile" style="width: 30px; height: 30px; border-radius: 100%;">
                    </button>
                    <ul class="dropdown-menu lefted" aria-labelledby="profileDropdown">
                        <li><a href="profile">Profile</a></li>
                        <li class="divider"></li>
                        <li><a href="/profile">Logout</a></li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>
            <script>
                // $('#bellButton').on('click', function() {
                //     $('#notificationModal').modal('show');
                // });
                $(document).ready(function() {
                    // When dropdown item clicked, set modal message and show modal
                    $('.notification-item').on('click', function(e) {
                        e.preventDefault();
                        var message = $(this).data('message');
                        $('#modalMessage').text(message);
                        $('#notificationModal').modal('show');
                        // Optionally, close dropdown after click
                        $('.dropdown.open .dropdown-toggle').dropdown('toggle');
                    });
                    
                    $('#menu-left').on('click', function() {
                        $('#sidenav-store1, #sidenav-store2').toggleClass('open');
                    });

                    // Close sidebar and all submenus on window resize below 1024
                    function handleResize() {
                        if ($(window).width() < 1024) {
                            $('#sidenav-store1, #sidenav-store2').removeClass('open');

                            // Close all submenus
                            $('.submenu-list').slideUp(200);
                            $('.submenu-toggle i').css('transform', 'rotate(0deg)');
                        }
                    }

                    // Run on initial load and on resize
                    handleResize();
                    $(window).on('resize', handleResize);
                });
            </script>

        <div id="notificationModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Notification</h4>
                    </div>
                    <div class="modal-body">
                        <p>แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1</p>
                        <div class="" style="text-align: right;">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">รับทราบ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mobile-navbar">
    <div class="origami-header-dashboard">
        <?php if ($currentScreen == 'Menu') { ?>
            <div class="container-topnav">
                <div class="header-topnav">
                    <div class="somebox">
                        <a class="navbar-brand-test menu-btn" id="menu-left" style="pointer-events: auto;">
                            <img id="menu-icon" src="/images/menu/Hamberger Icon.svg" alt="Menu">
                        </a>
                        <div class="title-group-topnav">
                            <div>
                                <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                            </div>
                            <div class="dissappear-text">
                                <h1 style="color:black !important;">Green Tech</h1>
                                <p style="color:black !important;">Hello ! <?php echo $student_name; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="icons">
                        <!-- Dropdown wrapper -->
                        <div class="dropdown" style="display: inline-block; vertical-align: middle;">
                            <button class="bell-button btn btn-default dropdown-toggle" type="button" id="bellDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: none; border: none; padding: 0;">
                                <img id="menu-icon" src="/images/menu/Bell.svg" alt="Noti" style="padding: 5px;">
                                <span class="notification-badge">1</span>
                            </button>

                            <ul class="dropdown-menu centered" aria-labelledby="bellDropdown">
                                <a class="notification dropdown-toggle menu-readall text-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="update_notiStatus_read();">
                                <span><i class="fas fa-check-circle"></i></span>
                                    &nbsp; Mark all as read
                                </a>
                                <?php foreach ($notification_data as $notification): ?>
                                    <!-- class="<?= htmlspecialchars($notification['class']) ?>" -->

                                        <li>
                                            <a 
                                            href="<?php echo htmlspecialchars($notification['path']) ?>" 
                                            class="<?= htmlspecialchars($notification['class']) ?>" 
                                            data-message="<?= htmlspecialchars($notification['message']) ?>"
                                            style="display:flex; align-items: center;"
                                            >
                                                <img 
                                                    src="<?= htmlspecialchars($notification['img']) ?>" 
                                                    alt="error" 
                                                    style="width: 30px; height: 30px; border-radius: 100%; margin-right: 10px;"
                                                >
                                                <span style="font-weight: bolder;"><?= htmlspecialchars($notification['header']) ?>: New!</span>
                                                <p style="margin-left: 10%;"><?= htmlspecialchars(truncateMessage($notification['message'], 50)) ?></p>
                                            </a>
                                        </li>
                                        <li class="divider"></li>
                                    <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="dropdown" style="display: inline-block; vertical-align: middle;">
                        <button id="profileDropdown" 
                            class="btn dropdown-toggle" 
                            type="button" 
                            data-toggle="dropdown" 
                            aria-haspopup="true" 
                            aria-expanded="false" 
                            style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?>; padding: 0; width: 34px; height: 34px;"
                            >
                            <img height="30" width="30" id="avatar_h" name="avatar_h"  title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'" alt="Profile" style="width: 30px; height: 30px; border-radius: 100%;">
                        </button>
                        <ul class="dropdown-menu lefted" aria-labelledby="profileDropdown">
                            <li><a href="profile">Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="logout">Logout</a></li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                // $('#bellButton').on('click', function() {
                //     $('#notificationModal').modal('show');
                // });
                $(document).ready(function() {
                    // When dropdown item clicked, set modal message and show modal
                    $('.notification-item').on('click', function(e) {
                        e.preventDefault();
                        var message = $(this).data('message');
                        $('#modalMessage').text(message);
                        $('#notificationModal').modal('show');
                        // Optionally, close dropdown after click
                        $('.dropdown.open .dropdown-toggle').dropdown('toggle');
                    });

                    // Close sidebar and all submenus on window resize below 1024
                    function handleResize() {
                        if ($(window).width() < 1024) {
                            $('#sidenav-store1, #sidenav-store2').removeClass('open');

                            // Close all submenus
                            $('.submenu-list').slideUp(200);
                            $('.submenu-toggle i').css('transform', 'rotate(0deg)');
                        }
                    }

                    // Run on initial load and on resize
                    handleResize();
                    $(window).on('resize', handleResize);
                });
            </script>

        <?php
        
        } else {
        ?>
            <!-- Mobile centered title -->
            <div class="header">
                <button class="back-button" onclick="history.go(-1);">
                    <span>
                        <i class="fas fa-long-arrow-alt-left"></i>
                    </span>
                </button>
                <?php 
                $marginClass = in_array($currentScreen, $hide_profile) ? ' add-margin-right' : '';
                ?>
                <h1 class="header-title<?php echo $marginClass; ?>"><?php echo $currentScreen ?></h1>
                <?php
                if(!in_array($currentScreen, $hide_profile)): ?>
                <a href="profile" class="" style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?> ;">
                    <img style="height: 30px; border-radius: 100%;" width="30" id="avatar_h" name="avatar_h" title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'">
                </a>
                <?php endif; ?>
            </div>
            <script>
                const currentPage = window.location.pathname.split('/').pop();
                const backButton = document.getElementsByClassName('back-button'); // or get button by other selector
                // console.log(currentPage);

                backButton.onclick = function() {
                    if (currentPage === 'classroominfo') {
                        // Redirect to class.php when on classroominfo.php
                        window.location.href = 'class';
                    } else {
                        // Otherwise, go back in history
                        window.history.back();
                    }
                };
            </script>
        <?php
        }
        ?>

        <div id="notificationModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Notification</h4>
                    </div>
                    <div class="modal-body">
                        <p>แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1</p>
                        <div class="" style="text-align: right;">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">รับทราบ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


