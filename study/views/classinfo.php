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

// condition to check
$isOnsite = true;
$isCheckIn = false;
?>


<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Class Info • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/classinfo.css?v=<?php echo time(); ?>">
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

    <?php
    echo "<script src='/dist/vue/dev.js'></script>" . PHP_EOL;
    // if(in_array($_SERVER['HTTP_HOST'], $dev_host)){
    //     echo "<script src='dist/vue/dev.js'></script>".PHP_EOL;
    // } else {
    //     echo "<script src='dist/vue/prod.js'></script>".PHP_EOL;
    // }
    ?>

<script src="/dist/ua-parser/ua-parser.min.js"></script>
<script src="/classroom/study/js/classinfo.js?v=<?php echo time(); ?>" type="text/javascript"></script>

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

    <div style="min-height:120vh;">
        <!-- CLASS CURRICULUM -->
        <div id="classinfo-container" class="app-container">
            <!-- Content Area -->
            <div class="content">
                <!-- Unit 1 Hello -->
                <div class="unit-section">

                </div>

                <div class="unit-header">
                    <div class="unit-dot"></div>

                    <h2 class="unit-title">Unit1 Hello</h2>
                </div>

                <button class="lesson-card">
                    <h3 class="lesson-title">Happy New Year</h3>
                    <div class="lesson-details">
                        <span>Class 1, grade 2</span>
                        <span>2019.12.05</span>
                    </div>
                </button>

                <div class="lesson-card">
                    <h3 class="lesson-title">Postman and policeman</h3>
                    <div class="lesson-details">
                        <span>Class 1, grade 2</span>
                        <span>2019.12.08</span>
                    </div>
                </div>
            </div>

            <!-- Unit 2 Colours -->
            <div class="unit-section">
                <div class="unit-header">
                    <div class="unit-dot"></div>
                    <h2 class="unit-title">Unit2 Colours</h2>
                </div>

                <div class="lesson-card">
                    <h3 class="lesson-title">Small animals</h3>
                    <div class="lesson-details">
                        <span>Class 1, grade 2</span>
                        <span>2019.12.18</span>
                    </div>
                </div>
            </div>
            <!-- <div class="unit-section">
                <button id="timestamp-button" class="center-box" style="cursor: pointer;">
                    <img id="timestamp-img" src="images/stamp_in_button.png" height="100" alt="timestamp button">
                </button>
                <input id="stamp_photo" type="file" accept="image/*" style="display: none;" capture="camera">
                <div id="time-server"></div>
            </div> -->
        </div>
    </div>
    <div class="container-fluid" style="margin-top: 1rem;">

    </div>
    <?php require_once("component/footer.php"); ?>

    </div>

</body>
<script>

</script>

</html>