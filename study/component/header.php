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
$currentScreen = ucwords($currentScreen);

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

$notifications = select_data("*", "ogm_notification", "WHERE FIND_IN_SET('" . mysqli_real_escape_string($mysqli, $emp_id) . "', noti_emp_id) AND noti_comp_id = '" . mysqli_real_escape_string($mysqli, $comp_id) . "' AND noti_status = 0 AND noti_read is null limit 100");
$count_notification = count($notifications);


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
                    <!-- Dropdown wrapper -->
                    <div class="dropdown" style="display: inline-block;">
                        <button class="bell-button btn btn-default dropdown-toggle" type="button" id="bellDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: none; border: none; padding: 0;">
                            <i class="far fa-bell" style="font-size: 20px;"></i>
                            <span class="notification-badge">4</span>
                        </button>
                       <ul class="dropdown-menu centered" aria-labelledby="bellDropdown">
                            <li><a href="#" class="notification-item" data-message="แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1">Version alert: New!</a></li>
                            <li class="divider"></li>
                            <li><a href="#" class="notification-item" data-message="แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1">Version alert: New!</a></li>
                            <li class="divider"></li>
                            <li><a href="#" class="notification-item" data-message="แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1">Version alert: New!</a></li>
                            <li class="divider"></li>
                            <li><a href="#" class="notification-item" data-message="ข้อความแจ้งเตือนอื่น ๆ">Other notification</a></li>
                            
                        </ul>
                    </div>
                    <a href="profile" class="" style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?>;">
                        <img style=" border-radius: 100%; object-fit: cover;"height="30" width="30" id="avatar_h" name="avatar_h"  title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'" >
                    </a>
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

                    $(".notification").on("click", function () {
                        var isExpanded = $(this).attr("aria-expanded") === "true";
                        var $menu = $(this).next(".main-notification");
                        if (!isExpanded) {
                            if (!$menu.data("loaded")) {
                                $.ajax({
                                    url: "/actions/header.php",
                                    async: true,
                                    type: "POST",
                                    data: { 
                                        action: 'getNotification' 
                                    },
                                    dataType: "JSON",
                                    beforeSend: function() {
                                        $menu.html(`<li class="no-result text-center">
                                            <div class=text-grey" style="margin: 25px auto;">
                                                <i class="fas fa-spinner fa-pulse fa-3x"></i>
                                                <div>Loading</div>
                                            </div>
                                        </li>`);
                                    },
                                    success: function(result) {
                                        if (result.status) {
                                            if ((result.notification_data).length > 0) {
                                                let html = `
                                                    <li class="text-center">
                                                        <a onclick="readNotification();">
                                                            <i class="fa fa-check-circle"></i> Read all
                                                        </a>
                                                    </li>
                                                    <li class="divider"></li>
                                                `;
                                                $.each(result.notification_data, function(index, row) {
                                                    const decodedRequest = JSON.parse(row.noti_request);
                                                    const link = decodedRequest.link || '';
                                                    const id = decodedRequest.id || '';
                                                    const bgColor = row.color 
                                                        ? `background: linear-gradient(210deg, rgba(255, 255, 255, 0.15) 6.62%, ${row.color} 63.09%), #FFFFFF;`
                                                        : 'background: linear-gradient(210deg, rgba(255, 255, 255, 0.15) 6.62%, rgb(255 216 129 / 66%) 63.09%), #FFFFFF;';
                                                    const disabledClass = row.noti_read_datetime != null ? ' disabled ' : '';
                                                    const avatarHtml = `
                                                        <div class="img-circle" style="vertical-align: middle;padding:1px;display:inline-block; position: relative;  width: 50px;  height: 50px;  overflow: hidden !important;">
                                                            <img width="50" src="${row.emp_pic}" onerror="this.src='/images/default.png'">
                                                        </div>
                                                    `;
                                                    const elapsed = row.noti_datetime;
                                                    let notificationTxt = row.notification_txt;
                                                    let description = row.description;
                                                    const statusSvg = row.noti_read_datetime == null ? `
                                                        <div class="status-noti">
                                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                                                <circle cx="5" cy="5" r="5" fill="#FFA930"></circle>
                                                            </svg>
                                                        </div>
                                                    ` : '';
                                                    html += `
                                                        <li>
                                                            <div class="sub-noti-modal${disabledClass}" style="${bgColor}" onclick="SendLink('${link}', this, '${id}', '${row.noti_module_name}');">
                                                                <div style="padding: 10px;">
                                                                    <input type="hidden" name="noti-id" value="${row.noti_id}" />
                                                                    <div class="column1">
                                                                        ${avatarHtml}
                                                                        <span style="margin: -12px 36px; position: absolute; display: flex;">
                                                                            <svg width="21" height="21" viewBox="0 0 103 103" fill="none">
                                                                                <circle cx="51.5" cy="51.5" r="51.5" fill="#FFA930"/>
                                                                                <path d="M42.7216 27.9446C42.7216 22.8539 46.6518 18.7273 51.5 18.7273C56.3483 18.7273 60.2785 22.8539 60.2785 27.9446C60.2785 33.0351 56.3483 37.1619 51.5 37.1619C46.6518 37.1619 42.7216 33.0351 42.7216 27.9446ZM73.7664 28.1204C72.2428 26.5205 69.7724 26.5205 68.2489 28.1204L57.687 39.2102H45.313L34.7512 28.1204C33.2276 26.5205 30.7572 26.5205 29.2337 28.1204C27.71 29.7202 27.71 32.314 29.2337 33.9138L40.7709 46.0277V80.1761C40.7709 82.4386 42.5176 84.2727 44.6724 84.2727H46.6231C48.7779 84.2727 50.5247 82.4386 50.5247 80.1761V65.8381H52.4754V80.1761C52.4754 82.4386 54.2222 84.2727 56.3769 84.2727H58.3277C60.4824 84.2727 62.2292 82.4386 62.2292 80.1761V46.0277L73.7664 33.9137C75.29 32.3139 75.29 29.7202 73.7664 28.1204Z" fill="white"/>
                                                                            </svg>
                                                                        </span>
                                                                    </div>
                                                                    <div class="column2">
                                                                        <div class="text-action">
                                                                            ${row.noti_response} ${row.noti_module_name}
                                                                        </div>
                                                                        <div class="text-subject">
                                                                            ${notificationTxt}
                                                                        </div>
                                                                        <div class="text-descript">
                                                                            ${description}
                                                                        </div>
                                                                    </div>
                                                                    <div class="column3">
                                                                        <div class="time-noti" style="margin-bottom: 10px;">
                                                                            <i class="fa fa-clock-o"></i>&nbsp;${elapsed}
                                                                        </div>
                                                                        ${statusSvg}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    `;
                                                });
                                                html += `
                                                    <li class="divider"></li>
                                                    <li class="text-center view-all">
                                                        <a onclick="viewAllNotifications();">
                                                            <i class="fa fa-list"></i> View all
                                                        </a>
                                                    </li>
                                                `;
                                                $menu.html(html);
                                                $menu.data("loaded", true);
                                            } else {
                                                $menu.html(`
                                                    <li class="no-result text-center">
                                                        <div class=text-grey" style="margin: 25px auto;">
                                                            <i class="fas fa-bell fa-3x"></i>
                                                            <div>No results found</div>
                                                        </div>
                                                    </li>
                                                `);
                                            }
                                        } else {
                                            $menu.html(`
                                                <li class="no-result text-center">
                                                    <div class=text-grey" style="margin: 25px auto;">
                                                        <i class="fas fa-bell fa-3x"></i>
                                                        <div>No results found</div>
                                                    </div>
                                                </li>
                                            `);
                                        }
                                    },
                                    error: function() {
                                        $menu.html(`
                                            <li class="no-result text-center">
                                                <div class=text-red" style="margin: 25px auto;">
                                                    <i class="fas fa-exclamation fa-3x"></i>
                                                    <div>Failed to load.</div>
                                                </div>
                                            </li>
                                        `);
                                    }
                                });
                            }
                        }
                    });
                });

                function readNotification() {
                    $('.status-noti').addClass('hidden');
                    $('.sub-notifications').addClass('disabled');
                    $.ajax({
                        url: "/lib/notification_action.php", 
                        type: "POST",
                        data: {
                            action: "ReadDetailAll"
                        },
                        success: function(result) {}
                    });
                }
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
            <?php 
            $marginClass = in_array($currentScreen, $hide_profile) ? ' add-margin-right' : '';
            ?>
            <h1 class="header-title<?php echo $marginClass; ?>"><?php echo $currentScreen ?></h1>
            <?php
            if(!in_array($currentScreen, $hide_profile)): ?>
            <a href="profile" class="" style="background-color: white; border-radius: 100%; border: 2px solid <?php echo $profile_border_color; ?> ;">
                <img style=" border-radius: 100%;" width="30" id="avatar_h" name="avatar_h" title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'">
            </a>
            <?php endif; ?>
        </div>
        <script>
            const currentPage = window.location.pathname.split('/').pop();
            const backButton = document.getElementsByClassName('back-button'); // or get button by other selector
            console.log(currentPage);

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