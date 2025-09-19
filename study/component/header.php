<?php
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';

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
$currentScreen = ucfirst($currentScreen);

// if ($currentScreen == 'group') {
//     $currentScreen = 'academy';
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
    SELECT IFNULL(student_firstname_en, student_firstname_th) AS student_name
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
</head>

<div class="orange-header">
    <?php if ($currentScreen == 'Menu') { ?>
        <div class="container-topnav">
            <div class="header-topnav">
                <div class="title-group-topnav">
                    <span>
                        <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                    </span>
                    <div class="">
                        <h1>Green Tech</h1>
                        <p>Hello ! <?php echo $student_name; ?></p>
                    </div>
                </div>
                <div class="icons">
                    <button class="bell-button" id="bellButton">
                        <span>
                            <i class="far fa-bell" style="font-size: 20px;"></i>
                        </span>
                    </button>
                    <a href="profile" class="" style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?>;">
                        <img style=" border-radius: 100%; object-fit: cover;"height="30" width="30" id="avatar_h" name="avatar_h"  title="test" src="<?php echo $student_image_profile; ?>">
                    </a>
                </div>
            </div>
        </div>
        <script>
            $('#bellButton').on('click', function() {
                $('#notificationModal').modal('show');
            });
        </script>

    <?php
    } else {
    ?>
        <div class="header">
            <button class="back-button" onclick="window.history.back();">
                <span>
                    <i class="fas fa-long-arrow-alt-left"></i>
                </span>
            </button>
            <h1 class="header-title"><?php echo $currentScreen ?></h1>
            <?php 
            if(!in_array($currentScreen, $hide_profile)): ?>
            <a href="profile" class="" style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?>;">
                <img style=" border-radius: 100%;" width="30" id="avatar_h" name="avatar_h" title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'">
            </a>
            <?php endif; ?>
        </div>
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
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>