<?php
//  print_r("Info"); exit;
    session_start();
    require_once("../../../lib/connect_sqli.php");
    // require_once("../../component/header.php");
    include_once("../../login_history.php");
    global $mysqli;

    // Get the student ID from the URL. Using query parameter is more reliable if URL rewriting is not configured.
    // Example URL: http://origami.local/classroom/study/studentinfo.php?id=1
    // or if URL rewriting is setup: http://origami.local/classroom/study/studentinfo/1
    
    // Method 1: Get ID from URL path (requires server routing/htaccess)
    $url = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $url);
    $student_id = end($parts);

    if (empty($student_id) || !is_numeric($student_id)) {
        // Fallback to query parameter if URL path doesn't have an ID
        $student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    }

    // Check if a valid ID is provided
    if ($student_id > 0) {
        // Use a prepared statement to prevent SQL injection
        $stmt = $mysqli->prepare("SELECT * FROM classroom_student WHERE student_id = ? AND status = 1");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row_student = $result->fetch_assoc();
        } else {
            $row_student = null;
        }
        $stmt->close();
    } else {
        $row_student = null;
    }
    
    // Redirect if no valid student is found
    if ($row_student === null) {
        header("Location: /classroom/study/student/");
        exit();
    }
    
    // Now, map the database fields to the variables used in your HTML, using the correct column names
    $has_contact = !empty($row_student['student_mobile']) || !empty($row_student['student_email']) || !empty($row_student['student_line']) || !empty($row_student['student_ig']) || !empty($row_student['student_facebook']);
    
    // Assume friendship status for demonstration (in a real app, this would be dynamic)
    $friendship_status = 1; // Example: 1=Not friends, 2=Friends, 3=Pending request
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
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript" ></script>
<script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
<script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
<script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
<script src="/classroom/study/js/profile.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<style>
/* --- New Style for a modern, flat UI --- */
body {
    background-color: #f0f2f5; /* Light gray background */
    font-family: 'Helvetica Neue', Arial, sans-serif;
    color: #333;
    min-height: auto;
    overflow-y: auto;
}
.top-nav-container {
    background: linear-gradient(135deg, #ff8c00, #ffbc90); /* Gradient orange */
    padding: 12px 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-radius: 0 0 30px 30px;
}
.top-nav {
    list-style: none;
    display: flex;
    justify-content: center;
    gap: 30px;
    margin: 0;
    padding: 0;
}
.top-nav li a {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #ffffff;
    color: #ff8c00;
    font-size: 9px;
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-decoration: none;
}
.top-nav li a i {
    font-size: 24px;
    margin-bottom: 4px;
}
.top-nav li a:hover {
    background: #ff6600;
    color: #fff;
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(255,102,0,0.35);
}
.top-nav li a.active, .top-nav li a.active:hover {
    background: #ff6600;
    color: #fff;
    transform: scale(1.1);
    box-shadow: 0 8px 20px rgba(255,102,0,0.4);
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
        grid-template-columns: repeat(3, 1fr);
    }
    .contact-icon-circle {
        width: 55px;
        height: 55px;
        /* font-size: 50px; */
    }
    .contact-item a span {
        font-size: 1em;
    }
}
/* The rest of your styles from the original code */
.main-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 20px;
}
.profile-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 40px;
    text-align: center;
    position: relative;
    top: -50px;
}
.profile-avatar-square {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid #ff8c00;
    overflow: hidden;
    margin: 0 auto 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.profile-avatar-square img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-name {
    font-size: 2.2em;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 8px;
}
.profile-bio {
    font-size: 1.1em;
    color: #7f8c8d;
    margin-bottom: 25px;
}
.divider {
    height: 2px;
    width: 80px;
    background-color: #ff8c00;
    margin: 20px auto;
}
.contact-section-card {
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
.info-text strong {
    display: block;
    font-size: 1.1em;
    font-weight: 700;
    color: #555;
    margin-bottom: 4px;
}
.info-text span {
    font-size: 1em;
    color: #888;
}
.btn-save-changes {
    padding: 15px 40px;
    background-color: #ff6600;
    color: #fff;
    border: none;
    border-radius: 50px;
    font-weight: bold;
    font-size: 1.2em;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(255,102,0,0.4);
    transition: all 0.3s ease;
    display: block;
    margin: 40px auto;
}
.btn-save-changes:hover {
    background-color: #e55c00;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255,102,0,0.5);
}
.profile-course-container {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #f0f7ff;
    border-radius: 10px;
    display: inline-block;
}
.settings-button-container {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 10;
}

.settings-button {
    background-color: #ff6600;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 20px;
    box-shadow: 0 4px 10px rgba(255,102,0,0.2);
    transition: background-color 0.3s ease;
    text-decoration: none;
}
.settings-button:hover {
    background-color: #e55c00;
}
/* เพิ่มสไตล์สำหรับปุ่มแชท */
.message-button {
    background-color: #2ecc71; /* สีเขียวสำหรับปุ่มแชท */
}
.message-button:hover {
    background-color: #27ae60;
}
.pending-button {
    background-color: #f39c12; /* สีส้มสำหรับสถานะรอการตอบรับ */
}
.pending-button:hover {
    background-color: #e67e22;
}

.page-container {
    padding-top: 100px;
}
/* เพิ่ม Media Query สำหรับอุปกรณ์มือถือโดยเฉพาะ */
@media (max-width: 380px) {
    .page-container {
    /* padding-top: 140px; */
}
}
</style>
</head>
<body>
      <?php
    require_once ("../component/header.php")
    ?>
    <div class="page-container main-container" style="margin-bottom: 110px;">
        
        
        <div class="profile-card">
           
            <div class="settings-button-container">
            <?php if ($friendship_status === 1) : ?>
                <a href="#" class="settings-button add-friend-button" title="เพิ่มเพื่อน">
                    <i class="fas fa-user-plus"></i>
                </a>
            <?php elseif ($friendship_status === 2) : ?>
                <a href="#" class="settings-button message-button" title="ส่งข้อความ">
                    <i class="fas fa-comment-dots"></i>
                </a>
            <?php elseif ($friendship_status === 3) : ?>
                <a href="#" class="settings-button pending-button" title="รอการตอบรับ">
                    <i class="fas fa-hourglass-half"></i>
                </a>
            <?php endif; ?>
        </div>
            <div class="profile-avatar-square">
                <img src="<?= htmlspecialchars($row_student["student_image_profile"]); ?>" 
                    onerror="this.src='../../../images/default.png'" 
                    alt="Profile Picture">
            </div>
            <h2 class="profile-name">
                <?= htmlspecialchars($row_student["student_firstname_th"] . " " . $row_student["student_lastname_th"]); ?>
            </h2>
            <p class="profile-bio">
                <?= !empty($row_student["student_bio"]) ? htmlspecialchars($row_student["student_bio"]) : "ยังไม่ได้เขียน Bio"; ?>
            </p>
            <div class="profile-course-container">
                <p class="profile-course" style="margin: 0px;">
                    <i class="fas fa-graduation-cap"></i>
                    หลักสูตร: <span><?= !empty($row_student["student_education"]) ? htmlspecialchars($row_student["student_education"]) : "ยังไม่ได้ระบุ"; ?></span>
                </p>
            </div>
        </div>
        
        <?php if ($has_contact) : ?>
        <div class="contact-section-card">
            <div class="section-header-icon">
                <i class="fas fa-address-book" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">ช่องทางการติดต่อ</h3>
            </div>
            <div class="contact-grid">
    <?php if (!empty($row_student['student_mobile'])) : ?>
    <div class="contact-item">
        <a href="tel:<?= htmlspecialchars($row_student['student_mobile']); ?>">
            <div class="contact-icon-circle phone"><i class="fas fa-phone"></i></div>
            <span>โทรศัพท์</span>
        </a>
    </div>
    <?php endif; ?>
    <?php if (!empty($row_student['student_email'])) : ?>
    <div class="contact-item">
        <a href="mailto:<?= htmlspecialchars($row_student['student_email']); ?>">
            <div class="contact-icon-circle mail"><i class="fas fa-envelope"></i></div>
            <span>อีเมล</span>
        </a>
    </div>
    <?php endif; ?>
    <?php if (!empty($row_student['student_line'])) : ?>
    <div class="contact-item">
        <a href="https://line.me/ti/p/~<?= htmlspecialchars($row_student['student_line']); ?>" target="_blank">
            <div class="contact-icon-circle line"><i class="fab fa-line"></i></div>
            <span>Line</span>
        </a>
    </div>
    <?php endif; ?>
    <?php if (!empty($row_student['student_ig'])) : ?>
    <div class="contact-item">
        <a href="https://www.instagram.com/<?= htmlspecialchars($row_student['student_ig']); ?>" target="_blank">
            <div class="contact-icon-circle ig"><i class="fab fa-instagram"></i></div>
            <span>Instagram</span>
        </a>
    </div>
    <?php endif; ?>
    <?php if (!empty($row_student['student_facebook'])) : ?>
    <div class="contact-item">
        <a href="https://www.facebook.com/<?= htmlspecialchars($row_student['student_facebook']); ?>" target="_blank">
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
                    <i class="fas fa-birthday-cake" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">วันเกิด</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_birth_date"]) ? date("j F Y", strtotime($row_student["student_birth_date"])) : "-"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-church" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">ศาสนา</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_religion"]) ? htmlspecialchars($row_student["student_religion"]) : "-"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-tint" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">กรุ๊ปเลือด</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_bloodgroup"]) ? htmlspecialchars($row_student["student_bloodgroup"]) : "-"; ?></span>
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
                    <i class="fas fa-star" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">งานอดิเรก</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_hobby"]) ? htmlspecialchars($row_student["student_hobby"]) : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-music" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">ดนตรีที่ชอบ</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_music"]) ? htmlspecialchars($row_student["student_music"]) : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-film" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">หนังที่ชอบ</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_movie"]) ? htmlspecialchars($row_student["student_movie"]) : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-bullseye" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">เป้าหมาย</strong>
                        <span style="padding-left:10px;"><?= !empty($row_student["student_goal"]) ? htmlspecialchars($row_student["student_goal"]) : "ยังไม่ได้ระบุ"; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <?php
    require_once ("../component/footer.php")
    ?>
</body>
</html>