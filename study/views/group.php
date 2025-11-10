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
require_once $base_include.'/actions/func.php';
  $fsData = getBucketMaster();
  $filesystem_user = $fsData['fs_access_user'];
  $filesystem_pass = $fsData['fs_access_pass'];
  $filesystem_host = $fsData['fs_host'];
  $filesystem_path = $fsData['fs_access_path'];
  $filesystem_type = $fsData['fs_type'];
  $fs_id = $fsData['fs_id'];
setBucket($fsData);

$std_id = $_SESSION['student_id'];

$class_id =  getStudentClassroomId($std_id);
$classroom_group = getStudentClassroomGroup($class_id);


// $classroom_group = select_data($columnGroup, $tableGroup, $whereGroup);
// var_dump($classroom_group["classroom_id"]);
// $class_id = $classroom_group['classroom_id'];

$columnCount  = "COUNT(student_id) AS total_student";
$tableCount = "classroom_student_join";
$whereCount = "where classroom_id = '{$our_class}' AND group_id = '{$our_group}'";

$count_total = select_data($columnCount, $tableCount, $whereCount);
$count_student = $count_total[0]['total_student'];


$columnTeacher  = "COUNT(teacher_id) AS total_teacher";
$tableTeacher = "classroom_teacher_join";
$whereTeacher = "where classroom_id = '{$our_class}'";

$count_total_teacher = select_data($columnTeacher, $tableTeacher, $whereTeacher);
$count_teacher = $count_total_teacher[0]['total_teacher'];


$all_role = getMemberRole();
$all_role_count = $all_role[0]['count_role'];
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
  <div class="main-content">
    <div class="container-fluid px-3 py-3">
      <!-- Header Section -->
      <div class="" style="display: flex;">
        <div class="">
          <h1 class="heading-1">สมาชิกกลุ่ม</h1>
          <div class="divider-1">
            <span></span>
          </div>
        </div>
        <div class="" style="display: flex; margin-left: auto;">
          <div class="header-section">
            <div class="header-actions">
              <div class="dropdown">
                <button type="button" class="icon-btn dropdown-toggle" data-toggle="dropdown">
                  <i class="fas fa-filter"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right modern-dropdown">
                  <li><a href="#" class="group-filter" data-group-id="all">ทั้งหมด</a></li>
                  <?php foreach ($classroom_group as $group): ?>
                    <li><a href="#" class="group-filter" data-group-id="<?= $group['group_id'] ?>"><?= htmlspecialchars($group['group_name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <button class="icon-btn" id="toggleViewList">
                <i class="fas fa-user-friends"></i>
              </button>
            </div>
          </div>
        </div>
      </div>


      <!-- Search Bar -->
      <div class="search-container" style="margin-bottom: 1.5rem;">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="group-searchbox" class="search-input" placeholder="Search for groups...">
        <button class="icon-btn search-btn">
          <i class="fas fa-search"></i>
        </button>
      </div>


      <!-- Groups Section -->
      <div class="content-wrapper">
        <?php
        if (empty($classroom_group)): ?>
          <div class="empty-state">
            <i class="fas fa-users empty-icon"></i>
            <h3>ไม่พบข้อมูลกลุ่มที่คุณอยู่</h3>
            <p>ลองสร้างกลุ่มใหม่หรือเข้าร่วมกลุ่มที่มีอยู่</p>
          </div>
        <?php endif; ?>

        <!-- Student Groups -->
        <div class="groups-container" id="rowData">
          <?php foreach ($classroom_group as $item): ?>
            <a href="student?group_id=<?= $item['group_id'] ?>" class="group-card-modern">
              <div class="group-card-content">
                <div class="group-header">
                  <div class="group-logo" style="background: linear-gradient(135deg, <?= $item['group_color'] ?>33, <?= $item['group_color'] ?>66);">
                    <img src="<?php echo GetUrl($item['group_logo']); ?>" alt="<?= $item['group_name'] ?>" onerror="this.src='../../../images/booth.png'">
                  </div>
                  <div class="group-head-name">
                    <h3 class="group-title"><?= $item["group_name"] ?></h3>
                  </div>
                </div>
              </div>
              
              <div class="group-action">
                <p class="group-description"><?= $item["group_description"] ?></p>
                <button class="arrow-btn">
                  <i class="fas fa-arrow-right"></i>
                </button>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

        <div id="menu" style="display: none;"></div>

        <h1 class="heading-1">คณะกรรมการ</h1>
        <div class="divider-1">
          <span></span>
        </div>
        <div class="groups-container" id="rowTeacher">
          <!-- Staff Cards -->
          <?php
          if (empty($all_role)): ?>
            <div class="empty-state">
              <i class="fas fa-users empty-icon"></i>
              <h3>ยังไม่พบข้อมูลคณะกรรมการในขณะนี้</h3>
              <p>กรุณาตรวจสอบใหม่อีกครั้ง</p>
            </div>
          <?php endif; ?>


          <?php foreach ($all_role as $roles): ?>
            <a href="teacher" class="group-card-modern staff-card">
              <div class="group-card-content">
                <div class="group-header">
                  <div class="group-logo staff-logo">
                    <i class="fas fa-user-tie"></i>
                  </div>
                  <div class="group-head-name">
                    <h3 class="group-title"><?= $roles['position_name_th'] ?></h3>
                  </div>
                </div>
                <div class="group-action">
                  <p class="group-description">สมาชิกทีมงาน</p>
                  <div class="" style="display: flex;">
                    <p>จำนวนสมาชิก:  <?= $roles['count_role'] ?> คน</p>
                    <!-- <button class="arrow-btn">
                      <i class="fas fa-arrow-right"></i>
                    </button> -->
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <div id="menuTeacher" style="display: none;"></div>

      </div>
    </div>
  </div>

  <?php require_once 'component/footer.php'; ?>


</body>

</html>