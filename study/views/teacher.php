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
    $selected_position_id = isset($_GET['position_id']) ? intval($_GET['position_id']) : 0;
    $teacher_list = getTeacherByPosition($selected_position_id);
    $teacher_count = count($teacher_list);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Teacher • ORIGAMI SYSTEM</title>
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
<style>
    .search-container {
        margin-bottom: 20px;
    }
    .search-container input {
        width: 100%;
        padding: 12px 20px;
        border-radius: 25px;
        border: 1px solid #ddd;
        font-size: 1em;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .search-container input:focus {
        border-color: #ff8c00;
        box-shadow: 0 0 10px rgba(255, 140, 0, 0.2);
        outline: none;
    }
</style>
<body>
<?php require_once("component/header.php"); ?>
<div class="main-transparent-content">
    <div class="container-fluid">
        <h1 class="heading-1">รายชื่ออาจารย์</h1>
        <div class="divider-1"><span></span></div>
        <div class="search-container">
            <input type="text" id="studentSearch" onkeyup="searchStudents()" placeholder="ค้นหาอาจารย์...">
        </div>
        <div class="group-list">
<?php
            if ($teacher_count > 0) {
                foreach ($teacher_list as $row) {
                    $teacher_pic = !empty($row['teacher_image_profile']) ? GetUrl($row['teacher_image_profile']) : '/images/default.png';
                    $border_color = '#ff8c00';
?>
                    <a href="teacherinfo?teacher_id=<?= htmlspecialchars($row['teacher_id']); ?>" class="teacher-card">
                        <div class="teacher-avatar" style="border-color: <?= $border_color; ?>;">
                            <img src="<?= htmlspecialchars($teacher_pic); ?>" alt="Teacher Avatar" style="width:100%; height:100%;" onerror="this.src='/images/default.png'">
                        </div>
                        <div class="teacher-info">
                            <h5 class="teacher-name" style="margin-bottom: 15px;"><?= htmlspecialchars($row['teacher_firstname_th'] . " " . $row['teacher_lastname_th']); ?></h5>
                            <div class="teacher-email">
                                <i class="fas fa-envelope" style="margin-right:10px"></i>
                                <?= !empty($row['teacher_email']) ? htmlspecialchars($row['teacher_email']) : "-"; ?>
                            </div>
                            <div class="teacher-mobile">
                                <i class="fas fa-phone" style="margin-right:10px"></i>
                                <?= !empty($row['teacher_mobile']) ? htmlspecialchars($row['teacher_mobile']) : "-"; ?>
                            </div>
                        </div>
                    </a>
<?php
                }
            } else {
                echo "<p style='text-align: center; color: #888;'>ไม่พบข้อมูลอาจารย์ผู้สอน</p>";
            }
?>
        </div>
    </div>
    <?php require_once("component/footer.php"); ?>
</div>
</body>
</html>