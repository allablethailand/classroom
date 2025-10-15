<?php

@session_start();
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

$std_id = $_SESSION['student_id'];

$classroom_group = getStudentClassroomGroup($std_id);

// $teacher_group = getTeacherMember($)

// $our_class = $student_class[0]["classroom_id"];
// $our_group = $student_class[0]["group_id"];

$our_class = $classroom_group[0]["classroom_id"];
$our_group = $classroom_group[0]["group_id"];

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
    <div class="row justify-content-center mb-bs-2-5">
      <div class="col-12" style="display: flex; justify-content: flex-end; margin-right:2rem;">
        <div class="dropdown" style="margin-right: 10px;">
          <button type="button" class="btn btn-default blur-shadow dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border:none;">
            <i class="fas fa-filter"></i><span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="#" class="group-filter" data-group-id="all">ทั้งหมด</a></li>
            <?php foreach ($classroom_group as $group): ?>
              <li><a href="#" class="group-filter" data-group-id="<?= $group['group_id'] ?>"><?= htmlspecialchars($group['group_name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <button class="btn btn-default blur-shadow" id="toggleStudent" style="border: none;">
          <i class="fas fa-address-book"></i>
        </button>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12 col-md-6">
        <h1 class="heading-1">สมาชิกกลุ่ม</h1>
        <div class="divider-1"><span></span></div>
        <?php if (empty($classroom_group)): ?>
          <span class="display-4 fw-bold text-dark mb-bs-5 text-center">
            ไม่พบข้อมูลกลุ่มที่คุณอยู่
          </span>
        <?php endif; ?>

        <?php foreach ($classroom_group as $item): ?>
          <div id="rowData" class="g-4 justify-content-center mb-bs-3">
            <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12" style="padding-bottom: 2rem;">
              <a href="student?<?= $item['group_id'] ?>" style="color: black; font-family: 'Kanit', sans-serif !important;">
                <div class="card group-card h-100 bg-white rounded-small" style=" border-left: 15px solid <?= $item['group_color'] ?> !important;">
                  <div class="panel-heading border-0" style="padding:0;">
                    <div class="d-flex-bs align-items-center gap-3" style="flex-wrap: wrap;">
                      <div class="group-icon-large" style="color: #FFF; flex-shrink: 0;">
                        <img src="<?= $item['group_logo'] ?>" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                      </div>
                      <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px;">
                        <h4 class="panel-title mb-0 text-truncate d-flex-bs"><?= $item["group_name"] ?></h4>
                        <p class="text-secondary mb-0 small text-truncate-2">สมาชิกปัจจุบัน <?= $count_student ?> คน</p>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="col-xs-12 col-md-6">
        <h1 class="heading-1">คณะกรรมการ</h1>
        <div class="divider-1"><span></span></div>
        <div class="g-4 justify-content-center mb-bs-3">
          <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12"  style="padding-bottom: 2rem;">
            <a href="teacher" style="color: #F39865; font-family: 'Kanit', sans-serif !important;">
              <div class="card group-card h-100 bg-teacher rounded-small">
                <div class="panel-heading border-0" style="padding:0;">
                  <div class="d-flex-bs align-items-center gap-3" style="flex-wrap: wrap;">
                    <div class="group-icon-large" style="color: #FFF; flex-shrink: 0;">
                      <div class="circle"><i class="fas fa-chalkboard-teacher" style="color:#EED8DA; font-size: 2rem; margin-left:15px; margin-top: 15px;"></i></div>
                    </div>
                    <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px;">
                      <h4 class="panel-title mb-0 text-truncate d-flex-bs">อาจารย์ผู้สอน</h4>
                      <p class="text-secondary mb-0 small text-truncate-2">สมาชิกปัจจุบัน 3 คน</p>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>

        <div class="g-4 justify-content-center mb-bs-3">
          <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
            <a href="staff?<?= $item['group_id'] ?>" class="blur-shadow" style="color: #F39865; font-family: 'Kanit', sans-serif !important;">
              <div class="card group-card h-100 bg-teacher rounded-small">
                <div class="panel-heading border-0" style="padding:0;">
                  <div class="d-flex-bs align-items-center gap-3" style="flex-wrap: wrap;">
                    <div class="group-icon-large" style="color: #FFF; flex-shrink: 0;">
                      <div class="circle"><i class="fas fa-user-tie" style="color:#EED8DA; font-size: 2.5rem; margin-left:15px; margin-top: 10px;"></i></div>
                    </div>
                    <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px;">
                      <h4 class="panel-title mb-0 text-truncate d-flex-bs">Staff Member</h4>
                      <p class="text-secondary mb-0 small text-truncate-2">สมาชิกปัจจุบัน 3 คน</p>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div id="menu"></div>
  </div>
</div>

    <?php require_once 'component/footer.php'; ?>


</body>

</html>