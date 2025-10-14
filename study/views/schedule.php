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

// Program Name
$program_name = 'Green Tech Leadership (GTL) รุ่นที่ 1';
$program_slogan = '"CONNECT LOCAL TO GLOBAL"';

// Define date range as strings
date_default_timezone_set('Asia/Bangkok'); // or your timezone
?>


<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Schedule • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/schedule.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/schedule.js?v=<?php echo time(); ?>" type="text/javascript"></script>


</head>

<body>
    <?php require_once("component/header.php"); ?>

    <div class="main-transparent-content">
        <div class="container-fluid">
             <h1 class="heading-1" >กำหนดการประจำวัน</h1>
            <div class="divider-1">
                <span></span>
            </div>
            <div class="schedule-section">
                <!-- <div class="schedule-header">
                    <h3 class="schedule-title-card">Class Schedule</h3>
                </div> -->

                <div class="calendar-container">
                    <!-- change to next day -->
                    <button
                        id="prev-day"
                        class="day-nav-button prev"
                        aria-label="Previous day"
                        type="button">
                        ‹
                    </button>
                    <span id="current-date" style="margin: 0 1rem; font-weight: bold; font-size: 1.6rem; padding-bottom: 5px">
                        Tue, 9 Aug, 2025
                    </span>

                    <!-- change to next day -->
                    <button
                        id="next-day"
                        class="day-nav-button next"
                        aria-label="Next day"
                        type="button">
                        ›
                    </button>
                </div>
            </div>

            <div class="featured-class">
                <div class="featured-header">

                    <div>
                        <h2 class="featured-title"><?php echo $program_name; ?></h2>
                        <p class="featured-time"><?php echo $program_slogan; ?></p>
                        <p><?php echo "01/10/2025 - 15/10/2025"; ?></p>
                    </div>
                </div>
                <div class="featured-decoration-1"></div>
                <div class="featured-decoration-2"></div>
            </div>


            <div id="scheduleContainer"></div>
            
        </div>
        <?php require_once("component/footer.php"); ?>

    </div>

    <!-- One generic modal in your HTML -->
    <div id="scheduleModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content custom-modal-color">
                <div class="modal-header custom-header-color">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="scheduleModalLabel">Schedule Detail</h4>
                </div>
                <div class="modal-body">
                    <p id="modalTitle"><strong>หัวข้อ:</strong> ทดสอบ <span></span></p>
                    <p id="modalDetails"><strong>รายละเอียด:</strong> <span></span></p>
                    <p id="modalTime"><strong>ช่วงเวลาระหว่าง:</strong> <span></span></p>
                    <p id="modalSpeakers"><strong>วิทยากร:</strong> <span></span></p>
                    <div style="text-align: right;">
                        <button type="button" class="btn btn-primary open-new-modal">เข้าร่วม</button>
                        <button type="button" class="btn btn-secondary decline-modal" style="margin-left: 10px;">ปฎิเสธ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="newModal" class="modal fade" tabindex="-2" role="dialog" aria-labelledby="newModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content custom-modal-color-2">
                <div class="modal-header custom-header-color-2">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="newModalLabel">Join Event</h4>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <!-- Content of the second modal -->
                    <p>ต้องการไปยังหน้าเช็คอินเพื่อเข้าร่วมอีเว้นท์นี้เลยใช่มั้ย</p>
                    <div style="display: flex; margin:auto">
                        <p><b>ช่วงเวลาระหว่าง: </b></p>
                        <p id="modalTimeNew" style="margin-left: 10px;"> </p>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary accept-event" data-dismiss="modal">ตกลง</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

</html>