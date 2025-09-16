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

// if ($currentScreen == 'group') {
//     $currentScreen = 'academy';
// }

if (!isset($_SESSION['student_id'])) {
    header("Location: /classroom/study/login");
    exit();
}

// *** ส่วนที่แก้ไข: ดึง group_color เฉพาะสำหรับนักเรียนที่ล็อกอินอยู่ ***
$profile_border_color = '#ff8c00'; // ตั้งค่าสีเริ่มต้น

$studentId = (int)$_SESSION['student_id'];
$sql = "
    SELECT 
        cs.student_id, 
        cs.comp_id, 
        cs.student_image_profile, 
        IFNULL(cs.student_firstname_en, cs.student_firstname_th) AS student_name,
        cg.group_color
    FROM `classroom_student` cs
    LEFT JOIN `classroom_student_join` csj ON cs.student_id = csj.student_id
    LEFT JOIN `classroom_group` cg ON csj.group_id = cg.group_id
    WHERE cs.student_id = ?
";

$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    // จัดการข้อผิดพลาดในการเตรียมคำสั่ง SQL
    // สามารถ log ข้อผิดพลาดหรือแสดงข้อความที่เหมาะสมได้
} else {
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_image_profile = GetUrl($row['student_image_profile']);
        $student_name = $row['student_name'];
        // กำหนดสีขอบรูปภาพจากฐานข้อมูล ถ้าไม่มีให้ใช้สีเริ่มต้น #ff8c00
        $profile_border_color = !empty($row['group_color']) ? htmlspecialchars($row['group_color']) : '#ff8c00';
    } else {
        // หากไม่พบข้อมูล ให้ใช้สีเริ่มต้นตามที่ตั้งไว้
        $profile_border_color = '#ff8c00';
    }
    $stmt->close();
}
$hide_profile = ["profile", "edit_profile", "setting"];
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
    <?php if ($currentScreen == 'menu') { ?>
        <div class="container-topnav">
            <div class="header-topnav">
                <div class="title-group-topnav">
                    <span>
                        <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                    </span>
                    <div class="">
                        <h1>Green Tech</h1>
                        <p>Hello ! <?php echo ($student_name) ? $student_name : "User"; ?></p>
                    </div>
                </div>
                <div class="icons">
                    <button class="bell-button" id="bellButton">
                        <span>
                            <i class="far fa-bell" style="font-size: 20px;"></i>
                        </span>
                    </button>
                    <a href="profile" class="" style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?>;">
                        <img style=" border-radius: 100%;" width="30" id="avatar_h" name="avatar_h" title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'">
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
                <!-- <span class="back-arrow">←</span> -->
                <span>
                    <i class="fas fa-long-arrow-alt-left"></i>
                </span>
            </button>
            <h1 class="header-title"><?php echo ucfirst($currentScreen); ?></h1>
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

<!-- <script>
    $('#bellButton').on('click', function() {
        $('#notificationModal').modal('show');
    });
</script> -->