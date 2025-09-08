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

global $mysqli;
// Get current directory or page identifier, example by parsing URL path
$uriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePath = 'classroom/study';

if (strpos($uriPath, $basePath) === 0) {
    // Remove the basePath segment from the path
    $relativePath = trim(substr($uriPath, strlen($basePath)), '/');
} else {
    $relativePath = $uriPath;
}

$segments = explode('/', $relativePath);
$currentScreen = isset($segments[0]) && $segments[0] !== '' ? $segments[0] : 'menu';

if ($currentScreen == 'group') {
    $currentScreen = 'academy';
}

if (!isset($_SESSION['student_id'])) {
    header("Location: /classroom/study/login");
    exit();
}

// var_dump($_SESSION['student_id']);
$studentId = (int)$_SESSION['student_id'];
$sql = "SELECT `student_id`, comp_id , student_image_profile, IFNULL(student_firstname_en, student_firstname_th) AS student_name FROM `classroom_student` WHERE `student_id` = ?";

$stmt = $mysqli->prepare($sql);

// var_dump($studentId );

if ($stmt === false) {
    $error_message = "Database prepare error: " . $mysqli->error;
} else {
    // Bind parameter และ execute คำสั่ง
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_image_profile = GetUrl($row['student_image_profile']);
        $student_name = $row['student_name'];
    }
}

$hide_profile = ["profile", "edit_profile", "setting"]



// var_dump($result);
?>

<head>
    <link rel="stylesheet" href="/classroom/study/css/header.css?v=<?php echo time(); ?>">
</head>

<div class="orange-header">
    <?php
    if ($currentScreen == 'menu') {
    ?>

        <div class="container-topnav">
            <div class="header-topnav">
                <div class="title-group-topnav">
                    <span>
                        <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">



                        <!-- <div style="width: 20px; height: 20px; background-color: white; color: green; font-weight: bold; 
                                    font-size: 30px; width: 54px; height: 54px; display: flex; justify-content: center; 
                                    align-items: center; border-radius: 50%; user-select: none;">
                            G
                        </div> -->
                    </span>
                    <div class="">
                        <h1>Green Tech</h1>
                        <p>Hello ! <?php echo ($student_name) ? $student_name : "User"; ?></p>
                    </div>
                </div>
                <div class="icons">
                    <button class="bell-button" id="bellButton">
                        <span>
                            <i class="far fa-bell" style="font-size: 20px;"></i>
                        </span>
                    </button>
                    <a href="profile" class="" style="background-color: white; border-radius: 100%">

                        <img style=" border-radius: 100%;" width="25" id="avatar_h" name="avatar_h" title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'">
                    </a>


                    <!-- <div id="profile-right">
							<span class="profile-img" style="border:4px solid #FF9900">
								<img width="50" id="avatar_h" name="avatar_h" title="Admin" src="/images/default.png" onerror="this.src='/images/default.png'">
							</span>
                </div> -->

                </div>
            </div>

            <!-- <div class="balance-container">
                <p class="balance-label">Group Element:</p>
                <p class="balance-amount"> &nbsp; Fire 🔥</p>
            </div> -->
        </div>

    <?php
    } else {
    ?>
        <div class="header">
            <button class="back-button" onclick="window.history.back();">
                <span class="back-arrow">←</span>
            </button>
            <h1 class="header-title"><?php echo ucfirst($currentScreen); ?></h1>
            <?php 
            // var_dump($currentScreen);
            if(!in_array($currentScreen, $hide_profile)): ?>
            
            <a href="profile" class="" style="background-color: white; border-radius: 100%">

                <img style=" border-radius: 100%;" width="25" id="avatar_h" name="avatar_h" title="test" src="<?php echo $student_image_profile; ?>" onerror="this.src='/images/default.png'">
            </a>

            <?php endif; ?>
            <!-- <div class="header-spacer"></div> -->
        </div>
    <?php
    }
    ?>

    <div id="notificationModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Notification</h4>
                </div>
                <div class="modal-body">
                    <p>แจ้งเตือนเวอร์ชั่นปัจจุบัน คือ BETA 1.1</p>
                    <div class="" style="text-align: right;">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

</div>

<script>
    $('#bellButton').on('click', function() {
        $('#notificationModal').modal('show');
    });
</script>