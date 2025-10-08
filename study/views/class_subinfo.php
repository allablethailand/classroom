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
?>


<!doctype html>
<html>

<head>
    <script>
        var classroomId = <?php echo json_encode($class_id); ?>;
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Class Sub Info • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/game.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/menu.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/class_subinfo.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
    <?php require_once 'component/header.php'; ?>
    
    <div class="main-content">
            
        <div class="container-fluid" style="margin-bottom: 7rem;">
            <h1 class="heading-1">รายการลิสต์ห้องเรียน</h1>
            <div class="divider-1"> 
                <span></span>
            </div>
            <div class="row">
                 <div class="actions-grid">
                    <button class="action-card" id="btn-quiz-game">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M36.1167 32.918L36.8771 39.0799C37.0721 40.6983 35.3367 41.8293 33.9522 40.9908L27.2053 36.9739C26.7373 36.7009 26.6203 36.1159 26.8738 35.6479C27.8488 33.854 28.3753 31.826 28.3753 29.798C28.3753 22.6612 22.2524 16.8503 14.7255 16.8503C13.1851 16.8503 11.6836 17.0843 10.2796 17.5523C9.55814 17.7862 8.85615 17.1233 9.03165 16.3823C10.8061 9.2844 17.631 4 25.7818 4C35.2977 4 43 11.1954 43 20.0677C43 25.3326 40.2896 29.993 36.1167 32.918Z" fill="url(#paint0_linear_315_5)"/>
                            <path d="M25.4496 29.7977C25.4496 32.1182 24.5916 34.2631 23.1487 35.9596C21.2182 38.2996 18.1567 39.801 14.7248 39.801L9.6354 42.8235C8.77741 43.35 7.68543 42.6285 7.80243 41.634L8.28992 37.7926C5.67697 35.9791 4 33.0737 4 29.7977C4 26.3658 5.83297 23.3433 8.64092 21.5494C10.3764 20.4184 12.4628 19.7749 14.7248 19.7749C20.6527 19.7749 25.4496 24.2598 25.4496 29.7977Z" fill="#292D32"/>
                            <path d="M11.4735 25.1389C11.2016 24.7088 11.2839 24.1394 11.7042 23.8524C11.9636 23.6795 12.2416 23.5312 12.5381 23.4077C12.8345 23.278 13.1557 23.1791 13.5017 23.1112C13.8537 23.0371 14.2305 23 14.632 23C15.1818 23 15.6821 23.0772 16.133 23.2316C16.5901 23.3799 16.9793 23.5961 17.3005 23.8802C17.6217 24.1582 17.8718 24.4979 18.051 24.8994C18.2301 25.2947 18.3197 25.7395 18.3197 26.2336C18.3197 26.7154 18.2486 27.1324 18.1066 27.4844C17.9645 27.8365 17.7884 28.1423 17.5784 28.4017C17.3684 28.6612 17.1399 28.8866 16.8928 29.0781C16.6457 29.2634 16.411 29.4394 16.1886 29.6062C15.9662 29.773 15.7748 29.9367 15.6142 30.0973C15.4536 30.2579 15.3578 30.4401 15.3269 30.6439L15.2275 31.2652C15.1567 31.708 14.7747 32.0338 14.3262 32.0338C13.8579 32.0338 13.4656 31.6793 13.4181 31.2134L13.3441 30.4864C13.338 30.4555 13.3349 30.4308 13.3349 30.4123C13.3349 30.3876 13.3349 30.3567 13.3349 30.3197C13.3349 30.0417 13.4028 29.8008 13.5387 29.597C13.6808 29.3931 13.8568 29.2016 14.0668 29.0225C14.2769 28.8372 14.4992 28.6612 14.734 28.4944C14.9749 28.3214 15.2003 28.1361 15.4103 27.9385C15.6203 27.7408 15.7933 27.5153 15.9292 27.2621C16.0713 27.0088 16.1423 26.7092 16.1423 26.3633C16.1423 26.141 16.0991 25.9402 16.0126 25.7611C15.9261 25.5758 15.8056 25.4183 15.6512 25.2886C15.503 25.1588 15.3238 25.06 15.1138 24.9921C14.9038 24.9241 14.6753 24.8901 14.4282 24.8901C14.0638 24.8901 13.7549 24.9303 13.5017 25.0106C13.2546 25.0847 13.0446 25.1712 12.8716 25.27C12.6987 25.3689 12.5504 25.4584 12.4269 25.5387C12.3095 25.619 12.2045 25.6592 12.1118 25.6592C11.8833 25.6592 11.7196 25.5634 11.6208 25.3719L11.4735 25.1389ZM12.7975 35.5824C12.7975 35.3847 12.8315 35.1994 12.8994 35.0265C12.9735 34.8473 13.0754 34.696 13.2052 34.5725C13.3349 34.4428 13.4862 34.3408 13.6592 34.2667C13.8383 34.1864 14.0298 34.1463 14.2336 34.1463C14.4313 34.1463 14.6166 34.1864 14.7895 34.2667C14.9687 34.3408 15.12 34.4428 15.2435 34.5725C15.3733 34.696 15.4752 34.8473 15.5493 35.0265C15.6296 35.1994 15.6698 35.3847 15.6698 35.5824C15.6698 35.7862 15.6296 35.9746 15.5493 36.1476C15.4752 36.3205 15.3733 36.4719 15.2435 36.6016C15.12 36.7251 14.9687 36.8209 14.7895 36.8888C14.6166 36.9629 14.4313 37 14.2336 37C14.0298 37 13.8383 36.9629 13.6592 36.8888C13.4862 36.8209 13.3349 36.7251 13.2052 36.6016C13.0754 36.4719 12.9735 36.3205 12.8994 36.1476C12.8315 35.9746 12.7975 35.7862 12.7975 35.5824Z" fill="white"/>
                            <defs>
                            <linearGradient id="paint0_linear_315_5" x1="26.0022" y1="4" x2="26.0022" y2="41.2749" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F28A1E"/>
                            <stop offset="1" stop-color="#FE6502"/>
                            </linearGradient>
                            </defs>
                        </svg>
                        <h4 style="margin-top: 10px;">Certificate</h4>

                    </button>
                    <div class="action-card">
                        
                    </div>
                    <div class="action-card"></div>
                    <div class="action-card"></div>


                </div>
            </div>
            <div class="text-center mb-4 course-class-info" style="margin-top: 2rem; margin: 1rem">
            </div>
        </div>
    </div>
    
    <?php require_once 'component/footer.php'; ?>


</body>
<script>

</script>

</html>