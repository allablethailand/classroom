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
require_once $base_include . '/classroom/study/actions/student_func.php'; 
    
$student_id = getStudentId();

// Program Name
$program_name = 'Green Tech Leadership (GTL) รุ่นที่ 1';
// $program_slogan = '"CONNECT LOCAL TO GLOBAL"';

$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';

// Define date range as strings
date_default_timezone_set('Asia/Bangkok'); // or your timezone

$classroom_id = getStudentClassroomId($student_id);

$classroom_info = getStudentClassroomDetail($classroom_id);

$classStartPeriod = date_format(date_create($classroom_info['classroom_start']), "d/m/Y");
$classEndPeriod = date_format(date_create($classroom_info['classroom_end']), "d/m/Y");

$scheduleItems = select_data(
        "course.trn_subject AS course_name,
			course.trn_detail AS course_detail,
            cc.course_id AS course_id,
            DATE_FORMAT(course.trn_date,'%Y/%m/%d') AS date_start,
            course.trn_from_time AS time_start,
            course.trn_to_time AS time_end,
            course.trn_type AS course_type",
        "ot_training_list course 
            LEFT JOIN classroom_course cc 
            ON course.trn_id = cc.course_ref_id",
        "WHERE cc.classroom_id = '{$classroom_id}'
            AND cc.status = 0 
            ORDER BY time_start ASC");

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
    <link rel="stylesheet" href="/dist/daterangepicker/v2/daterangepicker.css">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/dist/moment/moment.min.js"></script>

    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/js/moment-with-locales.js"></script>

    <script src="/dist/daterangepicker/v2/daterangepicker.js"></script>
    <script>
        const dateRangeFromPHP = "<?php echo htmlspecialchars($dateRange, ENT_QUOTES); ?>";
    </script>
    <script src="/classroom/study/js/schedule.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>

</head>

<body sty>
    <?php require_once("component/header.php"); ?>

    <div class="main-transparent-content col-md-10">
        <div class="container-fluid">
            <h1 class="heading-1" data-lang="dailyschedule">กำหนดการประจำวัน</h1>
            <div class="divider-1">
                <span></span>
            </div>
            <div class="schedule-section">
                <!-- <div class="schedule-header">
                    <h3 class="schedule-title-card">Class Schedule</h3>
                </div> -->

                <!-- <div class="calendar-container">
                    <button
                        id="prev-day"
                        class="day-nav-button prev"
                        aria-label="Previous day"
                        type="button">
                        ‹
                    </button>
                    <div class="">
                         <span id="current-date" style="margin: 0 1rem; font-weight: bold; font-size: 1.6rem; padding-bottom: 5px">
                        Tue, 9 Aug, 2025
                        </span>
                        <button id="select-date-btn" type="button" class="btn minimal-nav-button"  style="font-size: 1rem; margin-bottom: 5px;  cursor: pointer; border-radius: 50%;">
                            <span><i class="fas fa-calendar-alt"></i></span>
                        </button>
                        <input type="text" id="hidden-date-input" style="display: none;" />
                    </div>
                   
                    <button
                        id="next-day"
                        class="day-nav-button next"
                        aria-label="Next day"
                        type="button">
                        ›
                    </button>
                </div> -->
            </div>

            <div class="featured-class">
                <div class="featured-header">
                    <div>
                        <h2 class="featured-title"><?php echo $classroom_info['classroom_name']; ?></h2>
                        <!-- <p class="featured-time"><?php echo $program_slogan['']; ?></p> -->
                        <p><?php echo $classStartPeriod . " - " . $classEndPeriod; ?></p>
                    </div>
                    <div>
                        <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                    </div>
                </div>
                <div class="featured-decoration-1"></div>
                <div class="featured-decoration-2"></div>
            </div>
            <div id="scheduleContainer"></div>


             <div class="clearfix mb-20">
                <div class="pull-left">
                    <div class="d-flex align-items-center">
                        <span class="label label-primary ml-10" style="font-size:0.85rem; font-weight: 800; padding: 10px 12px; border-radius: 50%;"> <?php echo count($scheduleItems); ?></span>
                    <div class="upcoming mr-10"></div>
                        <h3 class="h4 font-semibold text-primary mb-0">Class Schedule</h3>
                    </div>
                </div>
            </div>

             <!-- Timeline Container -->
            <div class="position-relative">
            <?php foreach ($scheduleItems as $item): ?>
              <div class="">
                <div class="schedule-container">
                <div class="schedule-item">
                    <div class="">
                        <div class="schedule-time">
                            <span class="schedule-time-text"><?php echo $item['date_start']; ?></span>
                        </div>
                        
                        <div class="schedule-timeline">
                            <div class="timeline-dot timeline-dot-blue" style="margin-left: 10px;"></div>

                            <div class="timeline-line" style="margin-left: 10px;"></div>
                        </div>
                    </div>
                    <div class="schedule-content schedule-content-card" style="margin-left: 25px;">
                        <div class="schedule-header">
                            <div>
                                <h3 class="schedule-title" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($item['course_name']) ?>
                                </h3>
                                <p class="schedule-duration"><?= htmlspecialchars($item['date_start']) ?> • <?php echo htmlspecialchars($item['time_start']) . " - " . htmlspecialchars($item['time_end']) ?> </p>
                            </div>
                            <span class="schedule-badge badge-class">
                            <?php 
                                if ($item['course_type'] === 'both') {
                                    $item['course_type'] = 'Hybrid';
                                } elseif ($item['course_type'] === 'inhouse') {
                                    $item['course_type'] = 'Onsite';
                                } echo htmlspecialchars(ucfirst($item['course_type']));
                                ?></span>
                        </div>
                        <div class="schedule-footer">
                            <div class="member-avatars">
                                <!-- ${instructorsHtml} -->
                            </div>
                            <button type="button" class="btn btn-new-primary" style="border-radius: 15px;"
                                data-toggle="modal"
                                data-target="#scheduleModal"
                                data-index="${key}">
                                ไปยังคลาสเรียน
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>

          
        </div>

            <!-- Appointment Cards -->
            <!-- <div class="space-y-24">
               
                 <?php foreach ($scheduleItems as $item): ?>
                    <div class="relative flex-start position-relative">
                        <div class="timeline-dot upcoming position-absolute" style="top: 1.5rem; left: 1rem; z-index: 10;"></div>

                        <div class="ml-40 w-full">
                            <div class="card shadow-hover p-20 rounded-lg">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <h4 class="text-lg font-semibold text-primary mb-10">
                                            <?= htmlspecialchars($item['course_name']) ?>
                                        </h4>
                                        <div class="row text-muted small">
                                            <div class="col-xs-6 d-flex align-items-center">
                                                <i class="fa fa-calendar mr-5 text-primary"></i>
                                                <?= htmlspecialchars($item['date_start']) ?>
                                            </div>
                                            <div class="col-xs-6 d-flex align-items-center">
                                                <i class="fa fa-clock-o mr-5 text-primary"></i>
                                                <?= htmlspecialchars($item['time_start']) ?> - <?= htmlspecialchars($item['time_end']) ?>
                                            </div>
                                        </div>
                                        <p class="text-muted small mt-15">
                                            <?= htmlspecialchars($item['course_detail']) ?>
                                        </p>
                                    </div>
                                    <div class="col-lg-4 text-right mt-20-lg mt-0">
                                        <button class="btn btn-primary btn-pay-now" onclick="location.href='course_detail.php?id=<?= urlencode($item['course_id']) ?>'">
                                            More detail <i class="fas fa-arrow-circle-right ml-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div> -->
               
            </div>
            </div>

            
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
                        <button type="button" class="btn btn-primary open-new-modal">ดูเพิ่มเติม</button>
                        <button type="button" class="btn btn-secondary decline-modal" style="margin-left: 10px;">ยกเลิก</button>
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
                    <p>ต้องการไปยังหน้าคลาสเพื่อเข้าร่วมอีเว้นท์นี้เลยใช่มั้ย</p>
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