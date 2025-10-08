<?php

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
require_once $base_include . '/actions/func.php';


$std_id = $_SESSION['student_id'];

$columnStudent  = "classroom_id, group_id";
$tableStudent = "classroom_student_join";
$whereStudent = "where student_id = '{$std_id}'";


$student_class = select_data($columnStudent, $tableStudent, $whereStudent);

$our_class = $student_class[0]["classroom_id"];
$our_group = $student_class[0]["group_id"];

$columnCourseGroup  = "classroom_id, classroom_name, classroom_information, classroom_poster";
$tableCourseGroup = "classroom_template";
$whereCourseGroup = "where classroom_id = '{$our_class}'";

$classroom_group =  select_data($columnCourseGroup, $tableCourseGroup, $whereCourseGroup);

// var_dump($classroom_group);

    $segments = ['complete', 'complete', 'complete', 'complete', 'complete', 'upcoming', 'upcoming', 'upcoming',];
    $segments_two = ['complete', 'complete', 'upcoming', 'upcoming', 'upcoming', 'upcoming', 'upcoming', 'upcoming',];
    $old_segment = '<div class="progress-container">
                                <div class="progress-bar-new">
                                    <?php foreach ($segments as $index => $segmentType): ?>
                                        <div class="progress-segment <?php echo htmlspecialchars($segmentType); ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>';
    
?>


<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Class • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <link rel="stylesheet" href="/dist/css/select2.min.css">
    <link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
    <link rel="stylesheet" href="/dist/css/jquery-ui.css">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/menu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/class.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/class.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>


<body>
    <?php require_once 'component/header.php'; ?>
    <div class="min-vh-100 bg-ori-gray">

        <div class="container-fluid px-4 py-2" style="margin-bottom: 20rem;">

            <?php foreach ($classroom_group as $item):  ?>

            <div class="g-4 justify-content-center mb-bs-3 " style="margin-top: 2rem;">
                <h1 class="heading-1">หลักสูตรชั้นเรียน</h1>
                    <div class="divider-1"> 
                        <span></span>
                    </div>
                <div class="col-12">
                    <a href="classinfo?classroom_id=<?php echo $item['classroom_id']; ?>" style="font-family: 'Kanit', sans-serif !important;">
                        <div class="card group-card h-100 bg-element-earth-two rounded-small" style="padding: 1.8rem">
                             <div class="flex-box-container">
                                <div class="header-menu">
                                    <div class="img-banner">
                                     <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="" style="width: 50px; height: 50px; border-radius: 100%;" >
                                    </div>
                                    <div class="class-menu">
                                    <span class="title-menu" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; overflow: hidden;"><?php echo $item['classroom_name']; ?></span>
                                    <div class="progress-section">
                                        <div class="progress-header">
                                        <span class="progress-text" style=" display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;"><?php echo $item['classroom_information']; ?></span>
                                        </div>
                                        <div class="progress-header-flex">
                                        <span class="progress-text-end"></span>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <div class="next-icon-box">
                                    <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach ?>
        </div>
    </div>
    <?php require_once 'component/footer.php'; ?>

</body>

</html>
