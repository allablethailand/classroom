<?php
// login.php
require_once("actions/login.php");
// ดึงไฟล์ที่จำเป็นเข้ามาใช้งาน
require_once($base_include."/lib/connect_sqli.php");
include_once($base_include."/login_history.php");
session_start(); // สำคัญมาก: ต้องเรียกใช้ session_start()
global $mysqli;

// สมมติว่ามีการตั้งค่าการเชื่อมต่อฐานข้อมูล ($mysqli) ไว้แล้ว
// เช่น: $mysqli = new mysqli("localhost", "your_user", "your_password", "your_db");

// ตรวจสอบการเชื่อมต่อ
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// ตรวจสอบการส่งค่าจากฟอร์ม Consent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_consent'])) {
    $student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null ;
    $classroom_id = $_SESSION['join_info']['classroom_id'] ? $_SESSION['join_info']['classroom_id'] : null;

    if ($student_id && $classroom_id) {
        // อัปเดตค่า consent_accept เป็น 1
        $update_sql = "UPDATE `classroom_student_join` SET `consent_accept` = 1 WHERE `student_id` = ? AND `classroom_id` = ?";
        $update_stmt = $mysqli->prepare($update_sql);
        $update_stmt->bind_param("ii", $student_id, $classroom_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Redirect ไปหน้าหลักหลังจากยอมรับ
        header("Location: /classroom/study/menu");
        exit();
    }
}

// ตรวจสอบว่ามีข้อมูลถูกส่งมาผ่านเมธอด POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ? $_POST['username'] : ''; // ใช้ null coalescing operator เพื่อความกระชับ
    $password = $_POST['password'] ? $_POST['password'] : '';

    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($username) || empty($password)) {
        $error_message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        // เตรียมคำสั่ง SQL ด้วย Prepared Statement เพื่อป้องกัน SQL Injection
        $sql = "SELECT `student_id`, `student_password`, student_password_key, comp_id FROM `classroom_student` WHERE `student_username` = ?";
        $stmt = $mysqli->prepare($sql);

        if ($stmt === false) {
            $error_message = "Database prepare error: " . $mysqli->error;
        } else {
            // Bind parameter และ execute คำสั่ง
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $student_password = $row['student_password'];
                $student_password_key = $row['student_password_key'];
                $stored_password_hash = decryptToken($student_password, $student_password_key);
                $student_id = $row['student_id'];

                // ตรวจสอบรหัสผ่าน
                if ($password == $stored_password_hash) {
                    // ล็อกอินสำเร็จ: บันทึกข้อมูลที่จำเป็นลงใน Session
                    $_SESSION['student_id'] = $student_id;
                    $_SESSION['comp_id'] = $row["comp_id"];

                    // ดึงข้อมูลจากตาราง classroom_student_join และตรวจสอบ consent_accept
                    $join_sql = "SELECT `join_id`, `student_id`, `classroom_id`, `group_id`, `consent_accept` FROM `classroom_student_join` WHERE `student_id` = ? AND `status` = 0";
                    $join_stmt = $mysqli->prepare($join_sql);
                    $join_stmt->bind_param("i", $student_id);
                    $join_stmt->execute();
                    $join_result = $join_stmt->get_result();

                    if ($join_result && $join_result->num_rows > 0) {
                        $join_data = $join_result->fetch_assoc();

                        // เก็บข้อมูล join ไว้ใน session
                        $_SESSION['join_info'] = [
                            'join_id' => $join_data['join_id'],
                            'student_id' => $join_data['student_id'],
                            'classroom_id' => $join_data['classroom_id'],
                            'group_id' => $join_data['group_id']
                        ];
                        $consent_accept = $join_data['consent_accept'];

                        // ตรวจสอบค่า consent_accept
                        if ($consent_accept == 1) {
                            // ยอมรับแล้ว: Redirect ไปหน้าหลัก
                            header("Location: /classroom/study/menu");
                            exit();
                        } else {
                            // ยังไม่ยอมรับ: ดึงเนื้อหา Consent จาก classroom_template
                            $classroom_id = $join_data['classroom_id'];
                            $template_sql = "SELECT `classroom_consent` FROM `classroom_template` WHERE `classroom_id` = ?";
                            $template_stmt = $mysqli->prepare($template_sql);
                            $template_stmt->bind_param("i", $classroom_id);
                            $template_stmt->execute();
                            $template_result = $template_stmt->get_result();

                            if ($template_result && $template_result->num_rows > 0) {
                                $template_data = $template_result->fetch_assoc();
                                $classroom_consent_content = $template_data['classroom_consent'];
                                // เก็บเนื้อหา Consent ไว้ใน Session หรือตัวแปรเพื่อให้ HTML แสดงผล
                                $_SESSION['classroom_consent'] = $classroom_consent_content;
                                // ตั้งค่าสถานะเพื่อแสดงหน้า Consent
                                $show_consent_form = true;
                            } else {
                                // ไม่พบข้อมูล Consent ให้ Redirect ไปเลย
                                header("Location: /classroom/study/menu");
                                exit();
                            }
                        }
                    } else {
                        // ไม่พบข้อมูลใน classroom_student_join ก็ให้ Redirect ไปหน้าหลัก
                        header("Location: /classroom/study/menu");
                        exit();
                    }
                } else {
                    // รหัสผ่านไม่ถูกต้อง
                    $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                }
            } else {
                // ไม่พบชื่อผู้ใช้
                $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            }
            $stmt->close();
        }
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Login • ORIGAMI SYSTEM</title>
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
<link rel="stylesheet" href="/classroom/study/css/login.css?v=<?php echo time(); ?>">
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
<script src="/classroom/study/js/login.js?v=<?php echo time(); ?>" type="text/javascript"></script>

<style>
    /* New modern design styles */
    :root {
        --orange-main: #FF6B00;
        --orange-light: #FF9800;
        --orange-dark: #E65A00;
        --gray-text: #555;
        --white-bg: #F5F7FA;
    }

    body {
        font-family: 'Kanit', sans-serif;
        background-color: var(--white-bg);
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        overflow: hidden;
    }

    .top-bg {
        /* Removed as it's not needed for the new design */
        display: none;
    }

    .login-wrapper {
        position: relative;
        width: 100%;
        max-width: 420px;
        z-index: 2;
        padding: 20px;
        box-sizing: border-box;
    }
    
    .login-card {
        background-color: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .login-card:hover {
        transform: translateY(-5px);
    }

    .logo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin-bottom: 30px;
    }

    .logo-container img {
        width: 40%;
        height: 40%;
        border-radius: 25%;
        margin-bottom: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
        padding: 10px;
    }

    .logo-container h2 {
        font-size: 24px;
        margin: 0;
        font-weight: 600;
        color: var(--orange-dark);
        letter-spacing: 1px;
    }

    .welcome-text {
        font-size: 16px;
        color: var(--gray-text);
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .form-group {
        text-align: left;
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: var(--gray-text);
        font-weight: 500;
        font-size: 14px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px 20px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        box-sizing: border-box;
        font-size: 16px;
        color: #333;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: var(--orange-main);
        box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
        outline: none;
    }

    .forgot-password {
        text-align: right;
        margin-top: -10px;
        margin-bottom: 25px;
    }

    .forgot-password a {
        color: var(--orange-main);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s;
    }

    .forgot-password a:hover {
        color: var(--orange-dark);
    }

    .login-button {
        width: 100%;
        padding: 15px;
        background-color: var(--orange-main);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    .login-button:hover {
        background-color: var(--orange-dark);
        transform: translateY(-2px);
    }
    
    .login-button:active {
        transform: translateY(0);
    }

    .register-link {
        margin-top: 25px;
        font-size: 14px;
        color: #888;
    }

    .register-link a {
        color: var(--orange-main);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }
    
    .register-link a:hover {
        color: var(--orange-dark);
    }

    .error-message {
        color: #d9534f;
        background-color: #fcebeb;
        border: 1px solid #f2c7c7;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    /* Consent Modal Styles */
    .consent-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        backdrop-filter: blur(5px);
    }

    .consent-content-container {
        background-color: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        max-width: 800px;
        width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .consent-content {
        flex-grow: 1;
        overflow-y: auto;
        text-align: left;
        padding-right: 15px;
        line-height: 1.8;
        font-size: 16px;
        color: #333;
    }

    .consent-content h3 {
        color: var(--orange-dark);
    }

    .consent-content p, .consent-content li {
        margin-bottom: 10px;
    }
</style>
</head>
<body>
    <!-- <div class="container-fluid"> -->
    <div class="login-wrapper">
        <div class="login-card">
            <?php if (isset($show_consent_form) && $show_consent_form): ?>
            <?php else: ?>
            <div class="logo-container">
                <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="VON BUNDIT Logo">
                <h2>เข้าสู่ระบบ</h2>
            </div>
            <p class="welcome-text">
                ยินดีต้อนรับสู่ระบบ ORIGAMI
                </p>
                 <p class="welcome-text">
                กรุณาเข้าสู่ระบบเพื่อเริ่มต้นการใช้งาน
            </p>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้งาน (Username)</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">รหัสผ่าน (password)</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <!-- <p class="forgot-password"><a href="#">ลืมรหัสผ่าน ?</a></p> -->
                <button type="submit" class="login-button">เข้าสู่ระบบ</button>
            </form>
            <p class="register-link">ยังไม่มีบัญชี? <a href=" /classroom/study/register">ลงทะเบียนใช้งาน</a></p>
            <?php endif; ?>
        </div>
    </div>
    <!-- </div> -->

    <?php if (isset($show_consent_form) && $show_consent_form): ?>
    <div class="consent-modal">
        <div class="consent-content-container">
            <h2 style="text-align: center;">ข้อตกลงและเงื่อนไข</h2>
            <div class="consent-content">
                <?php echo html_entity_decode($_SESSION['classroom_consent']); ?>
            </div>
            <form action="" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="accept_consent" value="1">
                <button type="submit" class="login-button">ฉันยอมรับข้อตกลงและเงื่อนไข</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>