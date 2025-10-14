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

$teacher_list = getTeacherList();
$teacher_count = count($teacher_list);

?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Staff • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/teacher.css?v=<?php echo time(); ?>">
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
</head>
<body>
    <?php
        require_once ("component/header.php")
    ?>
    <div class="main-content">
        <div class="container-fluid" style="margin: 0 1rem;">
            <div class="row">
                <h1 class="heading-1">รายชื่อบุคลากร</h1>
                <div class="divider-1">
                    <span></span>
                </div>
            </div>

            <div class="search-container">
                <input type="text" id="studentSearch" onkeyup="searchStudents()" placeholder="ค้นหาบุลากรในรุ่น...">
            </div>

            <div class="group-list">
                <?php
                if ($teacher_count > 0) {
                    foreach ($teacher_list as $row) {
                        $teacher_pic = !empty($row['teacher_image_profile']) ? GetUrl($row['teacher_image_profile']) : '../../../images/default.png';
                        
                        $border_color = '#ff8c00';
                ?>
                        <a href="staffinfo?id=<?= htmlspecialchars($row['teacher_id']); ?>" class="teacher-card">
                            <p class="teacher-id-display">
                                ID: <?= htmlspecialchars($row['teacher_id']); ?>
                            </p>

                            <div class="teacher-avatar" style="border-color: <?= $border_color; ?>;">
                                <img src="<?= htmlspecialchars($teacher_pic); ?>" alt="Teacher Avatar"
                                style="width:100%; height:100%;" onerror="this.src='../../../images/default.png'">
                            </div>

                            <div class="teacher-info">
                                <h4 class="teacher-name">
                                    <i class="fas fa-chalkboard-teacher" style="margin-right:10px"></i>
                                    <?= "ชื่อ: ". htmlspecialchars($row['teacher_firstname_th'] . " " . $row['teacher_lastname_th']); ?>
                                </h4>
                                <p class="teacher-nickname">
                                    <i class="fas fa-user" style="margin-right:10px"></i>ชื่อเล่น:
                                    <?= !empty($row['teacher_nickname_th']) ? htmlspecialchars($row['teacher_nickname_th']) : "-"; ?>
                                </p>
                                <p class="teacher-email">
                                    <i class="fas fa-envelope" style="margin-right:10px"></i>
                                    <?= !empty($row['teacher_email']) ? htmlspecialchars($row['teacher_email']) : "-"; ?>
                                </p>
                                <p class="teacher-mobile">
                                    <i class="fas fa-phone" style="margin-right:10px"></i>
                                    <?= !empty($row['teacher_mobile']) ? htmlspecialchars($row['teacher_mobile']) : "-"; ?>
                                </p>
                                <p class="teacher-address">
                                    <i class="fas fa-map-marker-alt" style="margin-right:10px"></i>
                                    <?= !empty($row['teacher_address']) ? htmlspecialchars($row['teacher_address']) : "-"; ?>
                                </p>
                            </div>
                        </a>
                <?php
                    }
                } else {
                    echo "<p style='text-align: center; color: #888;'>ไม่พบข้อมูลอาจารย์ผู้สอน</p>";
                }
                ?>
            </div>

            <div class="row" style="margin-top: 1rem; ">
                <?php foreach ($groups as $group): ?>
                    <a href="?group_id=<?= htmlspecialchars($group['group_id']); ?>" class="group-item-dropdown">
                        <div class="group-logo-container"
                            style="border-color: <?= htmlspecialchars($group['group_color']); ?>;">
                            <?php
                            $group_logo = !empty($group['group_logo']) ? GetUrl($group['group_logo']) : '';
                            ?>
                            <img src="<?= htmlspecialchars($group_logo); ?>" alt="Group Logo" class="group-logo">
                        </div>
                        <?= htmlspecialchars($group['group_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php require_once 'component/footer.php'; ?>
</body>