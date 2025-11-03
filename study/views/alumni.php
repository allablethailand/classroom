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
require_once $base_include . '/classroom/study/actions/student_func.php';

$std_id = $_SESSION['student_id'];

$classroom_group = getAlumniClassroom($std_id);

// var_dump($classroom_group);

// $columnStudent  = "classroom_id";
// $tableStudent = "classroom_student_join";
// $whereStudent = "where student_id = '{$std_id}'";

// $student_class = select_data($columnStudent, $tableStudent, $whereStudent);

// $our_class = $student_class[0]["classroom_id"];

// $columnCourseGroup  = "COUNT(cg.group_id) AS group_count, ctp.classroom_id, ctp.classroom_name, ctp.classroom_information, ctp.classroom_poster, ctp.classroom_student, count(student.join_id) as classroom_register,";
// $tableCourseGroup = "classroom_template ctp";
// $whereCourseGroup = "LEFT JOIN classroom_group cg ON ctp.classroom_id = cg.classroom_id WHERE ctp.classroom_id = '{$our_class}'";

// $classroom_group =  select_data($columnCourseGroup, $tableCourseGroup, $whereCourseGroup);

// var_dump($course);


// $classroom_group = select_data($columnGroup, $tableGroup, $whereGroup);

// var_dump($classroom_group["classroom_id"]);

// $class_id = $classroom_group['classroom_id'];
// var_dump($class_id);


// var_dump($userGroup);
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Alumni • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/group.css?v=<?php echo time(); ?>">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/dist/js/jquery.redirect.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/alumni.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <!-- <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script> -->
</head>

<body>

    <?php require_once 'component/header.php'; ?>

    <!-- work ON mobile screen ONLY -->
    <div class="main-transparent-content" >
        <div class="container-fluid px-4 py-2" >
            <h1 class="heading-1" >รุ่นหลักสูตร</h1>
            <div class="divider-1">
                <span></span>
            </div>
            <!-- <div class="text-center mb-4">
                <h1 class="display-4 fw-bold text-dark mb-bs-5 text-center">
                </h1>
            </div> -->
            <?php
            if ($classroom_group === [] || count($classroom_group) === 0) {
                echo '<span class="display-4 fw-bold text-dark mb-bs-5 text-center">
                            ไม่พบข้อมูลรุ่น academy ปัจจุบันที่คุณอยู่
                </span>';
            }
            foreach ($classroom_group as $item): {
            ?>
                <div class="g-4 justify-content-center mb-bs-3 ">
                    <div class="col-12 col-md-6 col-lg-4 ">
                        <a href="group?<?php echo $item['group_id']; ?>" style="color: white; font-family: 'Kanit', sans-serif !important;">
                        <div class="card group-card h-100 bg-element-earth-two rounded-small">
                            <div class="panel-heading border-0" style="padding:0;">
                                <div class="d-flex-bs align-items-center gap-3">
                                        <div class="group-icon-large" style="color: #FFF;">
                                            <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                            <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png"  alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                                        </div>
                                        <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                            <div class="d-flex-bs align-items-center gap-2 mb-1">
                                                <h4 class="panel-title mb-0 text-truncate-2 d-flex-bs " style="margin-right:40px"> <?= $item["classroom_name"] ?></h4>
                                            </div>
                                            <p class="text-secondary mb-0 small text-truncate-2" >
                                                สมาชิกปัจจุบัน <?php echo $item["classroom_register"]. " / ". $item['classroom_student']; ?> คน
                                            </p>
                                            
                                        </div>
                                        <div class="flex-right-alum" style="align-content: center; text-align:center;">
                                            <!-- <p style="font-size: 9px; text-align:center;">
                                                มีทั้งหมด <?php echo $item['group_count']; ?> <br>
                                                กลุ่ม
                                            </p> -->
                                            <div class="" style="text-align:center; margin-top: 2rem;">
                                               <i style="font-size: 20px;" class="fas fa-chevron-circle-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php
                }
            endforeach; ?>

        </div>
    </div>
    <?php require_once 'component/footer.php'; ?>


</body>

</html>