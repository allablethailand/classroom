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

// Get the student ID from the URL.
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id > 0) {
    // Modified SQL query to join with `classroom_student_join` and `classroom_group`
    // เพื่อดึงข้อมูล group_color ตามที่ต้องการ
    $stmt = $mysqli->prepare("SELECT cs.*, cg.group_color 
                              FROM classroom_student cs 
                              LEFT JOIN classroom_student_join csj ON cs.student_id = csj.student_id
                              LEFT JOIN classroom_group cg ON csj.group_id = cg.group_id
                              WHERE cs.student_id = ? AND cs.status = 0");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Use the variable $row_all as in profile.php
    if ($result->num_rows > 0) {
        $row_all = $result->fetch_assoc();
    } else {
        $row_all = null;
    }
    $stmt->close();
} else {
    $row_all = null;
}

// Redirect if no valid student is found
if ($row_all === null) {
    header("Location: /classroom/study/student/");
    exit();
}

// Check for contact information
$has_contact = !empty($row_all['student_mobile']) || !empty($row_all['student_email']) || !empty($row_all['student_line']) || !empty($row_all['student_ig']) || !empty($row_all['student_facebook']);

// --- ส่วนที่แก้ไขเพิ่มเข้ามา ---
    // 1. ดึง classroom_id จาก classroom_student_join
    $sql_join = "SELECT classroom_id FROM `classroom_student_join` WHERE student_id = ?";
    $stmt_join = $mysqli->prepare($sql_join);
    $stmt_join->bind_param("i", $student_id);
    $stmt_join->execute();
    $result_join = $stmt_join->get_result();
    $join_data = $result_join->fetch_assoc();
    $stmt_join->close();

    $classroom_name = ""; // กำหนดค่าเริ่มต้นเป็นค่าว่าง
    if ($join_data && $join_data['classroom_id']) {
        $classroom_id = $join_data['classroom_id'];
        
        // 2. ใช้ classroom_id ดึง classroom_name จาก classroom_template
        $sql_template = "SELECT classroom_name FROM `classroom_template` WHERE classroom_id = ?";
        $stmt_template = $mysqli->prepare($sql_template);
        $stmt_template->bind_param("i", $classroom_id);
        $stmt_template->execute();
        $result_template = $stmt_template->get_result();
        $template_data = $result_template->fetch_assoc();
        $stmt_template->close();

        if ($template_data) {
            $classroom_name = $template_data['classroom_name'];
        }
    }
// กำหนดสีขอบรูปภาพเริ่มต้นเป็นสีส้ม ถ้าไม่มี group_color
$profile_border_color = !empty($row_all['group_color']) ? htmlspecialchars($row_all['group_color']) : '#ff8c00';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Profile • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/profile.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/profile.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <style>
        /* 🎨 UI/UX Enhancements to match the image */
        body {
            background-color: #f0f2f5;
            font-family: 'Kanit', sans-serif;
            color: #333;
            min-height: auto;
            overflow-y: auto;
        }
        
        .profile-header-container {
            background: url('https://www.trandar.com//public/news_img/photo_2025-09-03_17-51-32.jpg') no-repeat center center;
            background-size: cover;
            height: 300px;
            position: relative;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        
        .profile-avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #fff;
            overflow: hidden;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: border-color 0.3s ease;
        }
        
        .profile-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-name {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0;
        }
        
        .profile-location {
            font-size: 1.2em;
            margin: 0;
            font-weight: 300;
        }
        
        /* แก้ไข Bio: ลบ overflow และ line-clamp เพื่อให้แสดงผลเต็มที่ */
        .profile-bio {
            display: block;
            white-space: normal;
            text-overflow: unset;
            overflow: unset;
            margin: 5px 0 15px 0;
            text-align: center;
            padding: 0 20px;
            max-width: 80%;
        }

        /* เพิ่มเพื่อจัดกึ่งกลางและปรับขนาดกล่องหลักสูตร */
        .profile-card {
            display: flex;
            justify-content: center;
            margin: 0 auto;
            padding: 20px;
           /* max-width: fit-content;*/
            max-width: 600px; 
            text-align: center;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .profile-course-container {
            display: flex;
            flex-direction: column; /* เปลี่ยนจาก flex-direction: row เป็น column เพื่อให้ข้อมูลลงมาเป็นบรรทัดใหม่ */
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .profile-course, .profile-company, .profile-position {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .profile-stats-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-around;
            padding: 20px;
            position: relative;
            top: -50px;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .profile-stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8em;
            font-weight: 700;
            color: #333;
        }
        
        .stat-label {
            font-size: 1em;
            color: #7f8c8d;
        }
        
        .main-content-container {
            padding: 40px 20px;
            position: relative;
            top: -30px;
        }
        
        .section-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .activity-list-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .activity-details {
            flex-grow: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        
        .activity-date {
            font-size: 0.9em;
            color: #999;
            margin: 0;
        }
        
        .activity-button {
            background-color: #3498db;
            color: #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5em;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        
        /* Original styles - keeping for functionality */
        .profile-card, .contact-section-card, .info-grid-section {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .settings-button-container {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }
        
        .settings-button {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .settings-button:hover {
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .info-item-box {
            background-color: #f7f9fc;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease;
        }
        
        .info-item-box:hover {
            transform: translateY(-5px);
        }
        
        .info-item-box i {
            font-size: 22px;
            color: #ff8c00;
            margin-right: 15px;
        }
        /* ปรับขนาด icon ใน contact-grid */
        .contact-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .contact-item {
            text-align: center;
            flex-basis: 100px;
            flex-grow: 1;
        }
        .contact-item a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #7f8c8d;
            font-size: 1.1em;
            transition: transform 0.2s ease;
        }
        .contact-item a span {
            font-size: 1.1em;
        }
        .contact-icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
            font-size: 32px;
            color: #fff;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }
        @media (max-width: 768px) {
            .top-nav {
                gap: 10px;
                justify-content: space-around;
            }
            .top-nav li a {
                width: 50px;
                height: 50px;
            }
            .top-nav li a i {
                font-size: 20px;
            }
            .contact-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px 5px; /* ปรับลดระยะห่างระหว่างแถวและคอลัมน์ */
                padding: 0 10px; /* เพิ่ม padding ด้านข้างเล็กน้อย */
            }
            .contact-item {
                margin: 0;
                flex-grow: unset;
                flex-basis: auto;
            }
            .contact-icon-circle {
                width: 60px; /* ลดขนาดวงกลมไอคอน */
                height: 60px;
                font-size: 28px; /* ลดขนาดไอคอนภายในวงกลม */
                margin-bottom: 5px; /* ลดระยะห่างระหว่างไอคอนกับข้อความ */
            }
            .contact-item a span {
                font-size: 0.9em; /* ลดขนาดตัวอักษรของชื่อไอคอน */
            }
            
        }
        .contact-section-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        .contact-icon-circle.phone { background-color: #2ecc71; }
        .contact-icon-circle.mail { background-color: #D44638; }
        .contact-icon-circle.line { background-color: #00B900; }
        .contact-icon-circle.ig { background-color: #e4405f; }
        .contact-icon-circle.fb { background-color: #3b5998; }
        .info-grid-section {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        .section-header-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
             color: #ff9900;
        }
        .section-header-icon i {
            font-size: 2em;
            color: #ff6600;
            margin-right: 15px;
        }
        .section-title {
            font-weight: 700;
            color: #333;
            margin: 0;
        }
    </style>
</head>
<body>
    <?php require_once("component/header.php") ?>
    
    <div class="profile-header-container" style="gap: 5px;">
        <div class="profile-avatar-circle" style="border-color: <?= $profile_border_color; ?>;">
            <img src="<?= GetUrl($row_all["student_image_profile"]); ?>" 
                onerror="this.src='../../../images/default.png'" 
                alt="Profile Picture">
        </div>
        <h2 class="profile-name" style="
     background-color: rgba(0, 0, 0, 0.1); 
    
    backdrop-filter: blur(5px); /* เพิ่มเอฟเฟกต์เบลอ */
    padding: 8px;
    border-radius: 15px; /* มุมโค้งมน */
    text-align: center;
    ">
            <?= $row_all["student_firstname_th"] . " " . $row_all["student_lastname_th"]; ?>
        </h2>
        <?php if (!empty($row_all["student_address"])) : ?>
        <p class="profile-location" style="
     background-color: rgba(0, 0, 0, 0.1); 
    
    backdrop-filter: blur(5px); /* เพิ่มเอฟเฟกต์เบลอ */
    padding: 2px;
    border-radius: 15px; /* มุมโค้งมน */
    text-align: center;
    ">
            <i class="fas fa-map-marker-alt"></i>
            <span><?= $row_all["student_address"]; ?></span>
        </p>
        <?php endif; ?>
        <p class="profile-bio" style="
     background-color: rgba(0, 0, 0, 0.1); 
    
    backdrop-filter: blur(5px); /* เพิ่มเอฟเฟกต์เบลอ */
    padding: 4px;
    border-radius: 15px; /* มุมโค้งมน */
    text-align: center;
    ">
            <?= !empty($row_all["student_bio"]) ? $row_all["student_bio"] : "ยังไม่ได้เขียน Bio"; ?>
        </p>
    </div>
    
    <div class="page-container main-content-container">
        
        <div class="profile-card" style="padding: 10px;">
            <div class="profile-course-container">
                    <?php if (!empty($classroom_name)) : ?>
            <p class="profile-company" style="font-size: 14px;">
                <i class="fas fa-graduation-cap"  style="color: #0089ff;"></i>
                <span  style="font-size: 16px; font-weight: bold; padding-right: .3em;">หลักสูตร:</span> <span><?= $classroom_name; ?></span>
            </p>
        <?php endif; ?>
                <?php if (!empty($row_all["student_company"])) : ?>
                <p class="profile-company" style="font-size: 14px;">
                    <i class="fas fa-building" style="color: #0089ff;"></i>
                   <span  style="font-size: 16px; font-weight: bold; padding-right: .3em;">บริษัท:</span> <span><?= $row_all["student_company"]; ?></span>
                </p>
                <?php endif; ?>
                <?php if (!empty($row_all["student_position"])) : ?>
                <p class="profile-position" style="font-size: 14px;">
                    <i class="fas fa-briefcase" style="color: #0089ff;"></i>
                    <span  style="font-size: 16px ;font-weight: bold; padding-right: .3em;">ตำแหน่ง:</span> <span><?= $row_all["student_position"]; ?></span>
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($has_contact) : ?>
        <div class="contact-section-card">
            <div class="section-header-icon">
                <i class="fas fa-address-book" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">ช่องทางการติดต่อ</h3>
            </div>
            <div class="contact-grid">
                <?php if (!empty($row_all['student_mobile'])) : ?>
                <div class="contact-item">
                    <a href="tel:<?= $row_all['student_mobile']; ?>">
                        <div class="contact-icon-circle phone"><i class="fas fa-phone"></i></div>
                        <span>โทรศัพท์</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($row_all['student_email'])) : ?>
                <div class="contact-item">
                    <a href="mailto:<?= $row_all['student_email']; ?>">
                        <div class="contact-icon-circle mail"><i class="fas fa-envelope"></i></div>
                        <span>อีเมล</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($row_all['student_line'])) : ?>
                <div class="contact-item">
                    <a href="https://line.me/ti/p/~<?= $row_all['student_line']; ?>" target="_blank">
                        <div class="contact-icon-circle line"><i class="fab fa-line"></i></div>
                        <span>Line</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($row_all['student_ig'])) : ?>
                <div class="contact-item">
                    <a href="https://www.instagram.com/<?= $row_all['student_ig']; ?>" target="_blank">
                        <div class="contact-icon-circle ig"><i class="fab fa-instagram"></i></div>
                        <span>Instagram</span>
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($row_all['student_facebook'])) : ?>
                <div class="contact-item">
                    <a href="https://www.facebook.com/<?= $row_all['student_facebook']; ?>" target="_blank">
                        <div class="contact-icon-circle fb"><i class="fab fa-facebook-f"></i></div>
                        <span>Facebook</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="info-grid-section">
            <div class="section-header-icon">
                <i class="fas fa-user-circle" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">ข้อมูลส่วนตัว</h3>
            </div>
            <div class="info-grid">
                <div class="info-item-box">
                    <i class="fas fa-birthday-cake" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">วันเกิด</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_birth_date"]) ? date("j F Y", strtotime($row_all["student_birth_date"])) : "-"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-church" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">ศาสนา</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_religion"]) ? $row_all["student_religion"] : "-"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-tint" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">กรุ๊ปเลือด</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_bloodgroup"]) ? $row_all["student_bloodgroup"] : "-"; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-grid-section">
            <div class="section-header-icon">
                <i class="fas fa-heartbeat" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">ไลฟ์สไตล์</h3>
            </div>
            <div class="info-grid">
                <div class="info-item-box">
                    <i class="fas fa-star" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">งานอดิเรก</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_hobby"]) ? $row_all["student_hobby"] : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-music" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">ดนตรีที่ชอบ</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_music"]) ? $row_all["student_music"] : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-film" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">หนังที่ชอบ</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_movie"]) ? $row_all["student_movie"] : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-bullseye" style="font-size: 18px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">เป้าหมาย</strong>
                        <span style="padding-left:10px;"><?= !empty($row_all["student_goal"]) ? $row_all["student_goal"] : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once("component/footer.php") ?>
</body>
</html>