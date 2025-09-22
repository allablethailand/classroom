<?php
// $class_id = isset($_GET['id']) ? $_GET['id'] :  null;

// var_dump($class_id);
// // $classroom_group = $_POST['classroom_group'];

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



// var_dump($our_class);



// $class_generation_id = $_POST['class_gen_id'];
// $columnGroup  = "classroom_id, classroom_name, classroom_information, classroom_poster, classroom_student";
// $tableGroup = "classroom_template";
// $whereGroup = "where classroom_id = '{$our_class}'";
// $whereGroup = "where classroom_id = '1' AND status = 0";

$columnCourseGroup  = "group_id, group_name, group_logo, group_description, group_color";
$tableCourseGroup = "classroom_group";
$whereCourseGroup = "where classroom_id = '{$our_class}' AND group_id = '{$our_group}'";

$classroom_group =  select_data($columnCourseGroup, $tableCourseGroup, $whereCourseGroup);

// var_dump($our_class);


// $classroom_group = select_data($columnGroup, $tableGroup, $whereGroup);

// var_dump($classroom_group["classroom_id"]);
// $class_id = $classroom_group['classroom_id'];

$columnCount  = "COUNT(student_id) AS total_student";
$tableCount = "classroom_student_join";
$whereCount = "where classroom_id = '{$our_class}' AND group_id = '{$our_group}'";

$count_total = select_data($columnCount, $tableCount, $whereCount);
$count_student = $count_total[0]['total_student'];
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Group • ORIGAMI SYSTEM</title>
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
    <script src="/classroom/study/js/group.js?v=<?php echo time(); ?>" type="text/javascript"></script>

</head>
<body>

    <?php require_once 'component/header.php'; ?>

    <!-- work ON mobile screen ONLY -->
    <div class="main-content">
        <div class="container-fluid px-4 py-2">
            <div class="text-center mb-4">
                <h1 class="display-4 fw-bold text-dark mb-bs-5 text-center">
                    <!-- Elemental Group -->
                    <!-- Element Group -->
                </h1>
            </div>
            <div class="justify-content-center mb-bs-3" style="display: flex; direction: rtl;">
                <div class="" style="margin-left: 10px;">
                    <button class="btn btn-default" id="toggleStudent">
                       <i class="fas fa-address-book"></i>
                    </button>
                </div>
                <div class="dropdown">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-filter"></i><span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="#">1</a></li>
                        <li><a href="#">2</a></li>
                        <li><a href="#">3</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="#">4</a></li>
                        </ul>
                </div>
            </div>


            <?php
            if ($classroom_group === [] || count($classroom_group) === 0) {
                echo '<span class="display-4 fw-bold text-dark mb-bs-5 text-center">
                            ไม่พบข้อมูลกลุ่มที่คุณอยู่
                </span>';
            }
            foreach ($classroom_group as $item): {
            ?>
                    <div id="rowData" class="g-4 justify-content-center mb-bs-3 ">
                        <div class="col-12 col-md-6 col-lg-3">
                            <a href="student?<?php echo $item['group_id']; ?>" style="color: white; font-family: 'Kanit', sans-serif !important;">
                                <div class="card group-card h-100 bg-element-earth rounded-small" style="padding: 10px;">
                                    <div class="panel-heading border-0" style="padding:0;">
                                        <div class="d-flex-bs align-items-center gap-3">
                                            <div class="group-icon-large" style="color: #FFF;">
                                                <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                                <img src="<?php echo $item['group_logo']; ?>"  class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                                            </div>
                                            <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                                <div class="d-flex-bs align-items-center gap-2 mb-1">
                                                    <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
                                                </div>
                                                <p class="text-secondary mb-0 small text-truncate-2"> 
                                                    <?php echo "สมาชิกปัจจุบัน " . $count_student . " คน" ?>
                                                </p>
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

            <div id="menu"></div>

            <h1 class="heading-1" style="margin-top: 3rem;">คณะกรรมการ</h1>
            <div class="divider-1"> 
                <span></span>
            </div>
            <div class="g-4 justify-content-center mb-bs-3 ">
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="teacher?<?php echo $item['group_id']; ?>" style="color: white; font-family: 'Kanit', sans-serif !important;">
                        <div class="card group-card h-100 bg-element-water rounded-small" style="padding: 10px;">
                            <div class="panel-heading border-0" style="padding:0;">
                                <div class="d-flex-bs align-items-center gap-3">
                                    <div class="group-icon-large" style="color: #FFF;">
                                        <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                        <img src="" onerror="this.src='/images/online-1.png'" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                                    </div>
                                    <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                        <div class="d-flex-bs align-items-center gap-2 mb-1">
                                            <h4 class="panel-title mb-0 text-truncate d-flex-bs ">อาจารย์ผู้สอน</h4>
                                        </div>
                                        <p class="text-secondary mb-0 small text-truncate-2">
                                            สมาชิกปัจจุบัน 3 คน
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="g-4 justify-content-center mb-bs-3 ">
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="staff?<?php echo $item['group_id']; ?>" style="color: white; font-family: 'Kanit', sans-serif !important;">
                        <div class="card group-card h-100 bg-element-fire rounded-small" style="padding: 10px;">
                            <div class="panel-heading border-0" style="padding:0;">
                                <div class="d-flex-bs align-items-center gap-3">
                                    <div class="group-icon-large" style="color: #FFF;">
                                        <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                        <img src="" onerror="this.src='/images/offline-1.png'" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                                    </div>
                                    <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                        <div class="d-flex-bs align-items-center gap-2 mb-1">
                                            <h4 class="panel-title mb-0 text-truncate d-flex-bs ">Staff</h4>
                                        </div>
                                        <p class="text-secondary mb-0 small text-truncate-2">
                                            สมาชิกปัจจุบัน 3 คน
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>


                    <!-- <div class="g-4 justify-content-center bg-element-fire-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">
                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <div class="group-icon-large" style="color: #FFF;">
                                   
                                    <i class="fas fa-fire-alt" style="width: 50px;"></i>
                                </div>

                              
                                <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                    <div class="d-flex-bs align-items-center gap-2 mb-1">
                                        <h4 class="panel-title mb-0 text-truncate">Fire</h4>
                                    </div>
                                    <p class="text-secondary mb-0 small text-truncate-2">
                                        สมาชิกปัจจุบัน 39 คน
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="g-4 justify-content-center bg-element-earth-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">
                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <a href="student">

                                    
                                    <i class="fas fa-leaf" style="width: 50px;"></i>

                            </div>

                            
                            <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                <div class="d-flex-bs align-items-center gap-2 mb-1">
                                    <h4 class="panel-title mb-0 text-truncate">Fire</h4>
                                   
                                </div>
                                <p class="text-secondary mb-0 small text-truncate-2">
                                    สมาชิกปัจจุบัน 50 คน
                                </p>
                            </div>
                        </div>
                        <b>TESTT</b>
                        </a>

                    </div>
                </div>


            </div>
            <div class="g-4 justify-content-center bg-element-wind-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">
                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <div class="group-icon-large" style="color: #FFF;">
                                   
                                    <i class="fas fa-wind" style="width: 50px;"></i>
                                </div>

                                
                                <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                    <div class="d-flex-bs align-items-center gap-2 mb-1">
                                        <h4 class="panel-title mb-0 text-truncate">Fire</h4>
                                    </div>
                                    <p class="text-secondary mb-0 small text-truncate-2">
                                        สมาชิกปัจจุบัน 50 คน
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div> -->
                </div>
            </div>
            <?php require_once 'component/footer.php'; ?>


</body>

</html>