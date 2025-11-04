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

$class_id = $_GET['classroom_id'];
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
    <title>Class Info • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/classinfo.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/classinfo.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>
<body>
    <?php require_once 'component/header.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid" style="margin-bottom: 7rem;">
            <h1 class="heading-1" data-lang="classroom">ห้องเรียน</h1>
            <div class="divider-1"> 
                <span></span>
            </div>
            <div class="text-center mb-4 course-class-info" style="margin-top: 2rem; margin: 1rem">
                <div class="row"></div>
            </div>
        </div>
        <!-- <span lang="en">Instructors</span> -->
    </div>
    
    <?php require_once 'component/footer.php'; ?>


</body>
<script>

</script>

</html>