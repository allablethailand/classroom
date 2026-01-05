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

// NOTIFICATION SCHEDULE
$student_id = getStudentId();
$class_id = getStudentClassroomId($student_id);

$course_data = select_data(
    "cc.course_type,
    c.trn_id AS course_id,
    c.trn_subject AS course_name,
    c.picture_title AS course_cover,
    c.trn_location AS course_location,
    c.trn_from_time AS course_timestart,
    c.trn_to_time AS course_timeend,
    c.trn_by AS course_instructor,
    c.trn_date AS course_date,
    LENGTH(REPLACE(trn_by, ' ', '')) - LENGTH(REPLACE(REPLACE(trn_by, ' ', ''), ',', '')) + 1 AS trn_count_by
    ",
    "classroom_course AS cc JOIN ot_training_list AS c on cc.course_ref_id = c.trn_id",
    "WHERE cc.classroom_id = '{$class_id}' 
        AND cc.status = 0"
    );

// FIX SOON.
// var_dump($course_data);

$notification_data = [
    [
        "header" => "Version alert",
        "message" => "แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1",
        "class" => "notification-item",
        "path" => "/classroom/study/menu",
        "img" => "https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png"
    ],
    [
        "header" => "Version alert",
        "message" => "เข้าเรียนได้แล้ว ภายในเวลา 50 นาที",
        "class" => "notification-item",
        "path" => "/classroom/study/classroominfo",
        "img" => "https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png"
    ],
];

$total_noti = count($notification_data);

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
            <img class="margin-behind" src="/images/menu/DASHBOARD.svg" alt="" onerror="this.style.display='none'">
            <!-- <i class="fas fa-home"  style="margin-right: 5px;"></i> -->
            <p class="menu-title" data-lang="dashboard"> Dashboard</p>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="schedule">
            <img width="32px" height="32px" style="padding: 4px;" class="margin-behind" src="data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2048%2048%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20y%3D%220.00012207%22%20width%3D%2248%22%20height%3D%2248%22%20rx%3D%229.99999%22%20fill%3D%22url(%23paint0_linear_204_460)%22%20/%3E%3Cpath%20d%3D%22M38.6863%2020.0417L39.6554%2036.2856C39.6882%2036.7291%2039.4747%2037.0247%2039.3598%2037.1561C39.2284%2037.3039%2038.9491%2037.5339%2038.4893%2037.5339H34.0053L37.5531%2020.0417H38.6863ZM40.493%2013.7183L40.4766%2013.7511C40.5095%2014.1453%2040.4766%2014.5559%2040.3781%2014.9665L28.2732%2037.189C27.879%2038.8478%2026.4008%2039.9976%2024.6926%2039.9976H38.4893C40.608%2039.9976%2042.2833%2038.2073%2042.1191%2036.0885L40.493%2013.7183Z%22%20fill%3D%22white%22%20/%3E%3Cpath%20d%3D%22M23.1642%207.54466C23.3284%206.88768%2022.9178%206.21427%2022.2608%206.05003C21.6038%205.90221%2020.9304%206.2964%2020.7662%206.95338L19.9449%2010.3533H22.4743L23.1642%207.54466Z%22%20fill%3D%22white%22%20/%3E%3Cpath%20d%3D%22M34.0052%207.49103C34.153%206.81763%2033.726%206.17707%2033.0526%206.02925C32.3956%205.88143%2031.7386%206.30847%2031.5908%206.98187L30.8517%2010.3818H33.3811L34.0052%207.49103Z%22%20fill%3D%22white%22%20/%3E%3Cpath%20d%3D%22M40.1954%2012.6202C39.6534%2011.3062%2038.3723%2010.37%2036.7955%2010.37H33.3792L32.4594%2014.624C32.328%2015.1988%2031.8189%2015.593%2031.2604%2015.593C31.1783%2015.593%2031.0798%2015.593%2030.9976%2015.5602C30.3407%2015.4123%2029.9136%2014.7554%2030.045%2014.0984L30.8498%2010.3536H22.4733L21.4386%2014.624C21.3072%2015.1824%2020.798%2015.5602%2020.2396%2015.5602C20.141%2015.5602%2020.0425%2015.5437%2019.9439%2015.5273C19.2869%2015.3631%2018.8763%2014.7061%2019.0406%2014.0327L19.9275%2010.3372H16.5933C14.9837%2010.3372%2013.5548%2011.3883%2013.0785%2012.9322L6.16372%2035.1875C5.44104%2037.5691%207.19847%2039.9999%209.67858%2039.9999H31.2604C32.9686%2039.9999%2034.4468%2038.8502%2034.841%2037.1913L40.3761%2014.9689C40.4746%2014.5583%2040.5075%2014.1477%2040.4746%2013.7535C40.4418%2013.3593%2040.3596%2012.9651%2040.1954%2012.6202ZM28.5011%2031.377H15.3615C14.6881%2031.377%2014.1296%2030.8186%2014.1296%2030.1452C14.1296%2029.4718%2014.6881%2028.9133%2015.3615%2028.9133H28.5011C29.1745%2028.9133%2029.733%2029.4718%2029.733%2030.1452C29.733%2030.8186%2029.1745%2031.377%2028.5011%2031.377ZM30.1436%2024.8072H17.0039C16.3305%2024.8072%2015.7721%2024.2488%2015.7721%2023.5754C15.7721%2022.9019%2016.3305%2022.3435%2017.0039%2022.3435H30.1436C30.817%2022.3435%2031.3754%2022.9019%2031.3754%2023.5754C31.3754%2024.2488%2030.817%2024.8072%2030.1436%2024.8072Z%22%20fill%3D%22white%22%20/%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22paint0_linear_204_460%22%20x1%3D%224%22%20y1%3D%223.00012%22%20x2%3D%2245%22%20y2%3D%2246.0001%22%20gradientUnits%3D%22userSpaceOnUse%22%3E%3Cstop%20stop-color%3D%22%231EC0FB%22%20/%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%23198FE9%22%20/%3E%3C%2FlinearGradient%3E%3C%2Fdefs%3E%3C%2Fsvg%3E" alt="" onerror="this.style.display='none'">
            <!-- <img class="margin-behind" src="/images/menu/SCHEDULE.svg" alt="" onerror="this.style.display='none'"> -->
            <!-- <i class="fas fa-book-open"  style="margin-right: 5px;"></i> -->
            <span class="menu-title"  data-lang="schedule"> Schedule</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="alumni">
            <img class="margin-behind" src="/images/menu/ACADEMY.svg" alt="" onerror="this.style.display='none'">
            <!-- <i class="fas fa-school" style="margin-right: 5px;"></i> -->
            <span class="menu-title" data-lang="alumni"> Alumni</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="chat">
            <!-- <i class="fas fa-robot" style="margin-right: 5px;"></i> -->
            <img class="margin-behind" src="/images/menu/Origami AI.svg" alt="" onerror="this.style.display='none'">   
            <span class="menu-title" data-lang="askai" > Ask AI</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="class">
                <img width="32px" height="32px" style="padding: 4px;" class="margin-behind" 
                    src="data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2048%2048%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Crect%20width%3D%2248%22%20height%3D%2248%22%20rx%3D%229.99999%22%20fill%3D%22url(%23paint0_linear_204_456)%22/%3E%3Cpath%20d%3D%22M41.7792%2011.8795V33.1379C41.7792%2034.8722%2040.3668%2036.4635%2038.6325%2036.678L38.0782%2036.7495C35.1461%2037.1429%2031.016%2038.3587%2027.6904%2039.7532C26.5283%2040.236%2025.241%2039.3599%2025.241%2038.0905V13.2205C25.241%2012.559%2025.6164%2011.9511%2026.2064%2011.6292C29.4783%209.85919%2034.4309%208.28582%2037.7922%207.99976H37.8995C40.045%207.99976%2041.7792%209.73404%2041.7792%2011.8795Z%22%20fill%3D%22white%22/%3E%3Cpath%20d%3D%22M21.5906%2011.6292C18.3187%209.85919%2013.3661%208.28582%2010.0048%207.99976H9.87967C7.73416%207.99976%205.99988%209.73404%205.99988%2011.8795V33.1379C5.99988%2034.8722%207.41234%2036.4635%209.14662%2036.678L9.70088%2036.7495C12.6331%2037.1429%2016.7632%2038.3587%2020.0887%2039.7532C21.2508%2040.236%2022.5382%2039.3599%2022.5382%2038.0905V13.2205C22.5382%2012.5411%2022.1806%2011.9511%2021.5906%2011.6292ZM11.3815%2017.0466H15.4043C16.1374%2017.0466%2016.7453%2017.6545%2016.7453%2018.3876C16.7453%2019.1385%2016.1374%2019.7285%2015.4043%2019.7285H11.3815C10.6485%2019.7285%2010.0406%2019.1385%2010.0406%2018.3876C10.0406%2017.6545%2010.6485%2017.0466%2011.3815%2017.0466ZM16.7453%2025.0923H11.3815C10.6485%2025.0923%2010.0406%2024.5023%2010.0406%2023.7513C10.0406%2023.0183%2010.6485%2022.4104%2011.3815%2022.4104H16.7453C17.4783%2022.4104%2018.0862%2023.0183%2018.0862%2023.7513C18.0862%2024.5023%2017.4783%2025.0923%2016.7453%2025.0923Z%22%20fill%3D%22white%22/%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22paint0_linear_204_456%22%20x1%3D%224%22%20y1%3D%223%22%20x2%3D%2245%22%20y2%3D%2246%22%20gradientUnits%3D%22userSpaceOnUse%22%3E%3Cstop%20stop-color%3D%22%23CC4CFF%22/%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%237F2EBD%22/%3E%3C/linearGradient%3E%3C/defs%3E%3C/svg%3E" 
                    alt="" onerror="this.style.display='none'">
                
            <!-- <i class="fas fa-chalkboard-teacher"  style="margin-right: 5px;"></i> -->
            <span class="menu-title"  data-lang="classroom"> Classroom</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="calendar">
            <img width="32px" height="32px" style="padding: 4px;" class="margin-behind" 
                src="data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2048%2048%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Crect%20y%3D%22-0.000244141%22%20width%3D%2248%22%20height%3D%2248%22%20rx%3D%229.99999%22%20fill%3D%22url(%23paint0_linear_204_467)%22/%3E%3Cpath%20d%3D%22M41.9999%2031.7766C41.9999%2037.5608%2037.2472%2042.2499%2031.3845%2042.2499H16.6153C10.7525%2042.2499%205.99988%2037.5608%205.99988%2031.7766V18.5713H41.9999V31.7766ZM13.846%2030.4106C13.0813%2030.4106%2012.4614%2031.0222%2012.4614%2031.7766C12.4614%2032.5311%2013.0813%2033.1427%2013.846%2033.1427H15.6922C16.4569%2033.1427%2017.0768%2032.5311%2017.0768%2031.7766C17.0768%2031.0222%2016.4569%2030.4106%2015.6922%2030.4106H13.846ZM23.0768%2030.4106C22.3121%2030.4106%2021.6922%2031.0222%2021.6922%2031.7766C21.6922%2032.5311%2022.3121%2033.1427%2023.0768%2033.1427H24.9229C25.6876%2033.1427%2026.3076%2032.5311%2026.3076%2031.7766C26.3076%2031.0222%2025.6876%2030.4106%2024.9229%2030.4106H23.0768ZM32.3076%2030.4106C31.5429%2030.4106%2030.9229%2031.0222%2030.9229%2031.7766C30.9229%2032.5311%2031.5429%2033.1427%2032.3076%2033.1427H34.1537C34.9184%2033.1427%2035.5383%2032.5311%2035.5383%2031.7766C35.5383%2031.0222%2034.9184%2030.4106%2034.1537%2030.4106H32.3076ZM13.846%2023.1249C13.0813%2023.1249%2012.4614%2023.7365%2012.4614%2024.4909C12.4614%2025.2454%2013.0813%2025.857%2013.846%2025.857H15.6922C16.4569%2025.857%2017.0768%2025.2454%2017.0768%2024.4909C17.0768%2023.7365%2016.4569%2023.1249%2015.6922%2023.1249H13.846ZM23.0768%2023.1249C22.3121%2023.1249%2021.6922%2023.7365%2021.6922%2024.4909C21.6922%2025.2454%2022.3121%2025.857%2023.0768%2025.857H24.9229C25.6876%2025.857%2026.3076%2025.2454%2026.3076%2024.4909C26.3076%2023.7365%2025.6876%2023.1249%2024.9229%2023.1249H23.0768ZM32.3076%2023.1249C31.5429%2023.1249%2030.9229%2023.7365%2030.9229%2024.4909C30.9229%2025.2454%2031.5429%2025.857%2032.3076%2025.857H34.1537C34.9184%2025.857%2035.5383%2025.2454%2035.5383%2024.4909C35.5383%2023.7365%2034.9184%2023.1249%2034.1537%2023.1249H32.3076ZM30.9229%2010.8302C30.9229%2011.5847%2031.5429%2012.1963%2032.3076%2012.1963C33.0723%2012.1963%2033.6922%2011.5847%2033.6922%2010.8302V6.98104C38.0046%207.92452%2041.329%2011.4558%2041.9079%2015.8392H6.09182C6.6707%2011.4558%209.99517%207.92452%2014.3076%206.98104V10.8302C14.3076%2011.5847%2014.9275%2012.1963%2015.6922%2012.1963C16.4569%2012.1963%2017.0768%2011.5847%2017.0768%2010.8302V6.73202H30.9229V10.8302ZM15.6922%203.99988C16.4569%203.99988%2017.0768%204.61149%2017.0768%205.36595V6.73202H16.6153C15.8226%206.73202%2015.0505%206.8185%2014.3076%206.98104V5.36595C14.3076%204.61149%2014.9275%203.99988%2015.6922%203.99988ZM32.3076%203.99988C33.0723%203.99988%2033.6922%204.61149%2033.6922%205.36595V6.98104C32.9492%206.8185%2032.1771%206.73202%2031.3845%206.73202H30.9229V5.36595C30.9229%204.61149%2031.5429%203.99988%2032.3076%203.99988Z%22%20fill%3D%22white%22/%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22paint0_linear_204_467%22%20x1%3D%224%22%20y1%3D%222.99975%22%20x2%3D%2245%22%20y2%3D%2245.9997%22%20gradientUnits%3D%22userSpaceOnUse%22%3E%3Cstop%20stop-color%3D%22%23FFD900%22/%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%23FF8000%22/%3E%3C/linearGradient%3E%3C/defs%3E%3C/svg%3E" 
                alt="" onerror="this.style.display='none'">

            <span class="menu-title"  data-lang="calendar_sm"> Calendar</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="history">
            <img width="32px" height="32px" style="padding: 4px;" class="margin-behind" src="data:image/svg+xml;utf8,
                <svg width='24' height='24' viewBox='0 0 48 48' fill='none' xmlns='http://www.w3.org/2000/svg'>
                <rect width='48' height='48' rx='9.99999' fill='url(%23paint0_linear_226_27)'/>
                <path d='M31 18C23.837 18 18 23.837 18 31C18 38.163 23.837 44 31 44C38.163 44 44 38.163 44 31C44 23.837 38.163 18 31 18ZM36.655 35.641C36.473 35.953 36.148 36.122 35.81 36.122C35.641 36.122 35.472 36.083 35.316 35.979L31.286 33.574C30.285 32.976 29.544 31.663 29.544 30.506V25.176C29.544 24.643 29.986 24.201 30.519 24.201C31.052 24.201 31.494 24.643 31.494 25.176V30.506C31.494 30.974 31.884 31.663 32.287 31.897L36.317 34.302C36.785 34.575 36.941 35.173 36.655 35.641Z' fill='white'/>
                <path d='M30.5547 5C37.1098 5 41.0179 8.90782 41 15.4629V19.8203C38.7322 17.7904 35.8433 16.4406 32.6533 16.0908C33.0742 15.8605 33.3641 15.4129 33.3643 14.9053C33.3643 14.167 32.752 13.5548 32.0137 13.5547H14.0049C13.2665 13.5547 12.6543 14.1669 12.6543 14.9053C12.6545 15.6435 13.2666 16.2559 14.0049 16.2559H28.2324C24.6214 16.9295 21.4661 18.8964 19.2646 21.6582H14.0049C13.2666 21.6582 12.6544 22.2705 12.6543 23.0088C12.6543 23.7472 13.2665 24.3594 14.0049 24.3594H17.5479C16.7307 26.0115 16.2082 27.8353 16.0508 29.7627H14.0049C13.2665 29.7627 12.6543 30.3749 12.6543 31.1133C12.6544 31.8515 13.2666 32.4639 14.0049 32.4639H16.0713C16.3883 35.7372 17.7571 38.7024 19.8359 41.0176H15.4629C8.90794 41.0174 5.00014 37.1101 5 30.5371V15.4629C5.00013 8.90793 8.90793 5.00013 15.4629 5H30.5547Z' fill='white'/>
                <defs><linearGradient id='paint0_linear_226_27' x1='4' y1='3' x2='45' y2='46' gradientUnits='userSpaceOnUse'><stop stop-color='%2357EA49'/><stop offset='1' stop-color='%23009F0B'/></linearGradient></defs>
                </svg>"
                alt="icon" width="24" height="24" />

            <span class="menu-title" data-lang="history_sm"> History</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="game">
            <img width="32px" height="32px" style="padding: 4px;" class="margin-behind" 
                src="data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2048%2048%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Crect%20width%3D%2248%22%20height%3D%2248%22%20rx%3D%229.99999%22%20fill%3D%22url(%23paint0_linear_307_19)%22/%3E%3Cpath%20d%3D%22M33.6%205H14.6C10.42%205%207%208.42%207%2012.6V35.4C7%2039.58%2010.42%2043%2014.6%2043H33.6C37.78%2043%2041.2%2039.58%2041.2%2035.4V12.6C41.2%208.42%2037.78%205%2033.6%205ZM21.934%2035.666C21.649%2035.951%2021.288%2036.084%2020.927%2036.084C20.566%2036.084%2020.205%2035.951%2019.92%2035.666L18.685%2034.431L17.507%2035.609C17.222%2035.894%2016.861%2036.027%2016.5%2036.027C16.139%2036.027%2015.778%2035.894%2015.493%2035.609C14.942%2035.058%2014.942%2034.146%2015.493%2033.595L16.671%2032.417L15.55%2031.296C14.999%2030.745%2014.999%2029.833%2015.55%2029.282C16.101%2028.731%2017.013%2028.731%2017.564%2029.282L18.685%2030.403L19.863%2029.225C20.414%2028.674%2021.326%2028.674%2021.877%2029.225C22.428%2029.776%2022.428%2030.688%2021.877%2031.239L20.699%2032.417L21.934%2033.652C22.485%2034.203%2022.485%2035.115%2021.934%2035.666ZM28.831%2036.331C27.786%2036.331%2026.931%2035.495%2026.931%2034.45V34.412C26.931%2033.367%2027.786%2032.512%2028.831%2032.512C29.876%2032.512%2030.731%2033.367%2030.731%2034.412C30.731%2035.457%2029.876%2036.331%2028.831%2036.331ZM32.669%2032.227C31.624%2032.227%2030.75%2031.372%2030.75%2030.327C30.75%2029.282%2031.586%2028.427%2032.631%2028.427H32.669C33.714%2028.427%2034.569%2029.282%2034.569%2030.327C34.569%2031.372%2033.714%2032.227%2032.669%2032.227ZM35.5%2018.775C35.5%2020.599%2033.999%2022.1%2032.175%2022.1H16.025C14.201%2022.1%2012.7%2020.599%2012.7%2018.775V14.025C12.7%2012.201%2014.201%2010.7%2016.025%2010.7H32.175C33.999%2010.7%2035.5%2012.201%2035.5%2014.025V18.775Z%22%20fill%3D%22white%22/%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22paint0_linear_307_19%22%20x1%3D%224%22%20y1%3D%223%22%20x2%3D%2245%22%20y2%3D%2246%22%20gradientUnits%3D%22userSpaceOnUse%22%3E%3Cstop%20stop-color%3D%22%23CC4CFF%22/%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%237F2EBD%22/%3E%3C/linearGradient%3E%3C/defs%3E%3C/svg%3E" 
                alt="" onerror="this.style.display='none'">
            <span class="menu-title" data-lang="minigame_sm"> Mini Game</span>
            <span class="submenu-toggle"><i class="bi bi-chevron-down"></i></span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="myphoto">
                <img width="32px" height="32px" style="padding: 4px;" class="margin-behind"
                    src="data:image/svg+xml;charset=utf-8,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2048%2048%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Crect%20width%3D%2248%22%20height%3D%2248%22%20rx%3D%229.99999%22%20fill%3D%22url(%23paint0_linear_307_34)%22/%3E%3Cpath%20d%3D%22M13.4444%205C8.781%205%205%208.78101%205%2013.4445V34.5556C5%2039.2191%208.781%2043.0001%2013.4444%2043.0001H34.5556C39.219%2043.0001%2043%2039.2191%2043%2034.5556V13.4445C43%208.78101%2039.219%205%2034.5556%205H13.4444ZM32.4444%2011.3333C34.7772%2011.3333%2036.6667%2013.2228%2036.6667%2015.5556C36.6667%2017.8884%2034.7772%2019.7778%2032.4444%2019.7778C30.1117%2019.7778%2028.2222%2017.8884%2028.2222%2015.5556C28.2222%2013.2228%2030.1117%2011.3333%2032.4444%2011.3333ZM16.6111%2021.8889C17.8398%2021.891%2019.706%2023.8037%2021.492%2027.1012C22.2014%2028.4059%2022.8368%2029.8436%2023.4068%2031.258C23.7488%2032.1025%2024.0127%2032.7485%2024.1309%2033.1032C24.7072%2034.828%2027.0463%2035.0728%2027.9583%2033.5C28.0491%2033.3438%2028.2138%2033.044%2028.4861%2032.6429C28.9421%2031.9674%2029.4614%2031.2834%2030.004%2030.6627C31.3593%2029.1089%2032.6682%2028.2223%2033.5%2028.2223C34.3423%2028.2434%2035.6428%2029.1279%2036.996%2030.6627C37.5449%2031.2855%2038.0516%2031.9674%2038.5139%2032.6429C38.6596%2032.854%2038.6807%2032.9533%2038.7778%2033.1032V34.5556C38.7778%2036.8884%2036.8883%2038.7778%2034.5556%2038.7778H13.4444C11.1117%2038.7778%209.22222%2036.8884%209.22222%2034.5556V32.7738C9.35522%2032.3981%209.53257%2031.9526%209.81546%2031.258C10.3918%2029.8436%2011.0166%2028.408%2011.7302%2027.1012C13.531%2023.7974%2015.3846%2021.8868%2016.6111%2021.8889Z%22%20fill%3D%22white%22/%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22paint0_linear_307_34%22%20x1%3D%224%22%20y1%3D%223%22%20x2%3D%2245%22%20y2%3D%2246%22%20gradientUnits%3D%22userSpaceOnUse%22%3E%3Cstop%20stop-color%3D%22%23F28B1F%22/%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%23FF6200%22/%3E%3C/linearGradient%3E%3C/defs%3E%3C/svg%3E"
                    alt="" onerror="this.style.display='none'">
            <span class="menu-title" data-lang="myphoto_sm"> My Photo</span>
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
                            <span class="notification-badge"><?php echo $total_noti ?></span>
                        </button>
                        <ul class="dropdown-menu centered" aria-labelledby="bellDropdown">
                            <a class="notification dropdown-toggle menu-readall text-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="update_notiStatus_read();">
                            <span><i class="fas fa-check-circle"></i></span>
                                &nbsp; Mark all as read
                            </a>
                            <?php foreach ($notification_data as $notification): ?>
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
                        <li><a href="logout">Logout</a></li>
                    </ul>
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

    </div>
</div>


<div class="modal fade" id="notificationModalContainer" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-mobile-full" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification</h4>
            </div>
            <div class="modal-body">
                <p id="modalMessage">แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1</p>
                <div class="" style="text-align: right;">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">รับทราบ</button>
                </div>
            </div>
        </div>
    </div>
</div>