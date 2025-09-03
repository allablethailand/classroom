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

// ตรวจสอบว่ามีข้อมูลถูกส่งมาผ่านเมธอด POST หรือไม่
// / ตรวจสอบว่ามีข้อมูลถูกส่งมาผ่านเมธอด POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ? $_POST['username'] : ''; // ใช้ null coalescing operator เพื่อความกระชับ
    $password = $_POST['password'] ? $_POST['password'] : '';

    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($username) || empty($password)) {
        $error_message = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        // เตรียมคำสั่ง SQL ด้วย Prepared Statement เพื่อป้องกัน SQL Injection
        // แก้ไข: นำเงื่อนไข AND `status` = 1 ออก เพื่อให้สามารถล็อกอินได้
        $sql = "SELECT `student_id`, `student_password`, `student_password_key` FROM `classroom_student` WHERE `student_username` = ?";
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
                $stored_password_hash = $row['student_password'];
                $stored_hass_pass = decryptToken($row['student_password'], $row['student_password_key']);
                $student_id = $row['student_id'];

                // ตรวจสอบรหัสผ่านที่ส่งมากับรหัสผ่านที่ถูก hash ในฐานข้อมูล
                if ($stored_hass_pass == $password) {
                    // ล็อกอินสำเร็จ: บันทึกข้อมูลที่จำเป็นลงใน Session
                    $_SESSION['student_id'] = $student_id;
                    

                    // ดึงข้อมูลจากตาราง classroom_student_join
                    $join_sql = "SELECT `join_id`, `student_id`, `classroom_id`, `group_id` FROM `classroom_student_join` WHERE `student_id` = ? AND `status` = 0";
                    $join_stmt = $mysqli->prepare($join_sql);
                    $join_stmt->bind_param("i", $student_id);
                    $join_stmt->execute();
                    $join_result = $join_stmt->get_result();
                    
                    if ($join_result && $join_result->num_rows > 0) {
                        $join_data = $join_result->fetch_assoc();
                        
                        // เก็บค่า join_id, student_id, classroom_id, group_id ไว้ใน Session
                        $_SESSION['join_info'] = [
                            'join_id' => $join_data['join_id'],
                            'student_id' => $join_data['student_id'],
                            'classroom_id' => $join_data['classroom_id'],
                            'group_id' => $join_data['group_id']
                        ];
                    }

                    // Redirect ไปที่หน้าต่อไป
                    header("Location: http://origami.local/classroom/study/menu");
                    exit();
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
// หากมี Session อยู่แล้ว ให้ Redirect ไปหน้าหลักเลย
// if (isset($_SESSION['student_id']) && $_SESSION['student_id']) {
//     header("Location: http://origami.local/classroom/study/");
//     exit();
// }
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
        padding-top: 0; /* Adjusted to start from top */
    }
    .top-bg {
        width: 100%;
        height: 150px;
        background-color: #00ceff; /* สีน้ำเงินตามภาพ */
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
    }
    .login-wrapper {
        position: relative;
        width: 100%;
        max-width: 400px; /* ปรับขนาดให้พอดีกับมือถือ */
        z-index: 2;
        margin-top: 80px; /* เลื่อนลงมาให้เห็นพื้นหลังด้านบน */
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
        width: 60px; /* ปรับขนาดโลโก้ */
        height: 60px;
        border-radius: 50%;
        /* border: 2px solid white; */
        margin-bottom: 10px;
    }
    .logo-container h2 {
        font-size: 18px;
        margin: 0;
        font-weight: bold;
    }
    .login-container {
        background-color: white;
        padding: 30px 25px; /* ลด padding ลงเล็กน้อย */
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
        color: #007bff; /* สีเดียวกับพื้นหลังด้านบน */
        text-decoration: none;
        font-size: 13px;
    }
    .login-button {
        width: 100%;
        padding: 12px;
        background-color: #ff9800; /* โทนสีส้ม */
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .login-button:hover {
        background-color: #fb8c00; /* โทนสีส้มเข้มขึ้นเมื่อชี้ */
    }
    .register-link {
        margin-top: 20px;
        font-size: 13px;
    }
    .register-link a {
        color: #007bff; /* สีเดียวกับพื้นหลังด้านบน */
        text-decoration: none;
    }
    .error-message {
        color: red;
        margin-bottom: 15px;
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
        <div class="login-container">
            <h2>login</h2>
            <p class="welcome-text">
                ยินดีต้อนรับ<br>
                กรุณากรอกข้อมูลด้านล่างให้ครบ เพื่อเริ่มต้นการใช้งาน
            </p>

            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="login" method="POST">
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
        </div>
    </div>
    </div>
</body>
</html>