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
        header("Location: http://origami.local/classroom/study/menu");
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
        $sql = "SELECT `student_id`, `student_password`, student_password_key FROM `classroom_student` WHERE `student_username` = ?";
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
                            header("Location: http://origami.local/classroom/study/menu");
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
                                header("Location: http://origami.local/classroom/study/menu");
                                exit();
                            }
                        }
                    } else {
                        // ไม่พบข้อมูลใน classroom_student_join ก็ให้ Redirect ไปหน้าหลัก
                        header("Location: http://origami.local/classroom/study/menu");
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
    body {
        font-family: 'Kanit', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding-top: 0;
    }
    .top-bg {
        width: 100%;
        height: 150px;
        background-color: #00ceff;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
    }
    .login-wrapper {
        position: relative;
        width: 100%;
        max-width: 400px;
        z-index: 2;
        margin-top: 80px;
    }
    .logo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin-bottom: 20px;
        color: white;
    }
    .logo-container img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-bottom: 10px;
    }
    .logo-container h2 {
        font-size: 18px;
        margin: 0;
        font-weight: bold;
    }
    .login-container {
        background-color: white;
        padding: 30px 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        width: 100%;
        text-align: center;
    }
    .login-container h2 {
        font-size: 20px;
        color: #333;
        font-weight: bold;
        margin-top: 0;
    }
    .welcome-text {
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
        line-height: 1.5;
    }
    .form-group {
        text-align: left;
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-weight: normal;
        font-size: 14px;
    }
    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-sizing: border-box;
        font-size: 16px;
        color: #333;
    }
    input[type="text"]:focus,
    input[type="password"]:focus {
        border-color: #ff9800;
        outline: none;
    }
    .forgot-password {
        text-align: right;
        margin-top: 5px;
        margin-bottom: 20px;
    }
    .forgot-password a {
        color: #007bff;
        text-decoration: none;
        font-size: 13px;
    }
    .login-button {
        width: 100%;
        padding: 12px;
        background-color: #ff9800;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .login-button:hover {
        background-color: #fb8c00;
    }
    .register-link {
        margin-top: 20px;
        font-size: 13px;
    }
    .register-link a {
        color: #007bff;
        text-decoration: none;
    }
    .error-message {
        color: red;
        margin-bottom: 15px;
    }
    .consent-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .consent-content-container {
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        max-width: 800px;
        width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
    }
    .consent-content {
        flex-grow: 1;
        overflow-y: auto;
        text-align: left;
        padding-right: 15px; /* เพื่อให้มีพื้นที่สำหรับ scrollbar */
        line-height: 1.6;
        font-size: 16px;
    }
    .consent-content p, .consent-content li {
        margin-bottom: 10px;
    }
</style>
</head>
<body>
    <div class="container-fluid">
    <div class="top-bg"></div>
    <div class="login-wrapper">
        <div class="logo-container">
            <img src="https://www.origami.life/uploads/app/2_20180425103337.ico" alt="VON BUNDIT Logo">
            <h2 style="color:black;">login</h2>
        </div>
        <div class="login-container" id="login-form">
            <?php if (isset($show_consent_form) && $show_consent_form): ?>
                <?php else: ?>
                <h2>login</h2>
                <p class="welcome-text">
                    ยินดีต้อนรับ<br>
                    กรุณากรอกข้อมูลด้านล่างให้ครบ เพื่อเริ่มต้นการใช้งาน
                </p>
                <?php if (isset($error_message)): ?>
                    <p class="error-message"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="username">ชื่อผู้ใช้งาน (User ID)</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">รหัสผ่าน</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <p class="forgot-password"><a href="#">ลืมรหัสผ่าน ?</a></p>
                    <button type="submit" class="login-button">เข้าสู่ระบบ</button>
                </form>
                <p class="register-link"><a href=" http://origami.local/classroom/study/register">ลงทะเบียนใช้งาน</a></p>
            <?php endif; ?>
        </div>
    </div>
    </div>

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