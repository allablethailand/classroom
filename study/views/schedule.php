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
    
    <style>
        .sweet-alert h2 {
            font-family: 'Kanit', sans-serif !important;
        }

        .sweet-alert p {
            font-family: 'Kanit', sans-serif !important;
        }

        .sa-confirm-button-container button {
            font-family: 'Kanit', sans-serif !important;
        }
    </style>
</head>

<body>
    <?php require_once("component/header.php"); ?>

    <div style="min-height:140vh;">
        <div class="container-fluid" style="margin-top: 1rem;">
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
                    <span id="current-date" style="margin: 0 1rem; font-weight: bold; font-size: 2rem;">
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

</body>
<script>
    // Cancel modal on decline button click
    $(document).on('click', '.decline-modal', function() {
        // Find closest modal to this button and hide it
        $(this).closest('.modal').modal('hide');

        swal({
            type: 'error',
            title: 'ปฏิเสธ',
            text: 'คุณได้ปฏิเสธการเข้าร่วมอีเวนท์นี้',
        });
    });

    // Open second modal from first modal's "join" button
    $(document).on('click', '.open-new-modal', function() {
        const firstModal = $(this).closest('.modal');
        const index = firstModal.attr('id').split('-').pop(); // extract index

        // Hide first modal, then show second modal linked by index
        firstModal.modal('hide');
        firstModal.one('hidden.bs.modal', function() {
            const newModal = $('#newModal-' + index);

            newModal.modal('show');
        });
    });

    // Accept event on second modal
    $(document).on('click', '.accept-event', function() {
        $(this).closest('.modal').modal('hide');

        swal({
            type: 'success',
            title: 'เข้าร่วมสำเร็จ',
            text: 'คุณได้เข้าร่วมอีเว้นท์นี้เรียบร้อยแล้ว',
        });
    });
</script>
</html>