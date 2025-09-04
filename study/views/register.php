<?php
require_once("actions/register.php");
// ดึงไฟล์ที่จำเป็นเข้ามาใช้งาน
require_once($base_include."/lib/connect_sqli.php");
include_once($base_include."/login_history.php");
session_start(); // สำคัญมาก: ต้องเรียกใช้ session_start()
global $mysqli;

// ตัวแปรสำหรับเก็บสถานะและข้อความแจ้งเตือน
$status_message = '';
$is_success = false;

// ตรวจสอบว่ามีข้อมูลถูกส่งมาผ่านเมธอด POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ดึงข้อมูลจากฟอร์มและตรวจสอบความถูกต้อง
    $firstname = $_POST['firstname'] ? $_POST['firstname'] : '';
    $lastname = $_POST['lastname'] ? $_POST['lastname'] : '';
    $email = $_POST['email'] ? $_POST['email'] : '';
    $mobile = $_POST['mobile'] ? $_POST['mobile'] : '';
    $username_input = $_POST['username'] ? $_POST['username'] : '';
    $plain_password = $_POST['password'] ? $_POST['password'] : '';
    $confirm_password = $_POST['confirm_password'] ? $_POST['confirm_password'] : '';

    // ตรวจสอบว่ารหัสผ่านและยืนยันรหัสผ่านตรงกันหรือไม่
    if ($plain_password !== $confirm_password) {
        $status_message = "รหัสผ่านไม่ตรงกัน โปรดลองอีกครั้ง";
    } else {
        // *** ส่วนที่สำคัญ: Hash รหัสผ่านอย่างปลอดภัย ***
        $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
        $password_encrypt = encryptToken($plain_password, $student_password_key);

        // ตรวจสอบว่าชื่อผู้ใช้ซ้ำหรือไม่เพื่อหลีกเลี่ยงข้อผิดพลาด
        $stmt = $mysqli->prepare("SELECT `student_username` FROM `classroom_student` WHERE `student_username` = ?");
        $stmt->bind_param("s", $username_input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $status_message = "ชื่อผู้ใช้งานนี้มีอยู่แล้ว โปรดใช้ชื่ออื่น";
        } else {
            // เตรียมคำสั่ง SQL เพื่อป้องกัน SQL Injection
            $sql = "INSERT INTO `classroom_student` (
                            `student_firstname_th`, `student_lastname_th`, `student_email`, 
                            `student_mobile`, `student_username`, `student_password`, `student_password_key`,
                            `date_create`, `status`
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)";

            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                // Bind parameters และ execute
                $stmt->bind_param("sssssss", $firstname, $lastname, $email, $mobile, $username_input, $password_encrypt, $student_password_key);

                if ($stmt->execute()) {
                    $status_message = "ลงทะเบียนสำเร็จ!";
                    $is_success = true;
                } else {
                    $status_message = "บันทึกข้อมูลล้มเหลว: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $status_message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $mysqli->error;
            }
        }
        $stmt->close();
    }
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>ลงทะเบียน • ORIGAMI SYSTEM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }

        .input-style {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem; /* rounded-lg */
            padding: 0.75rem 1rem; /* px-4 py-3 */
            width: 100%;
            transition: all 0.3s ease;
        }

        .input-style:focus {
            border-color: var(--orange-main);
            box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.1);
            outline: none;
        }

        .btn-orange {
            background-color: var(--orange-main);
            color: white;
            padding: 0.875rem 1.25rem; /* py-3 px-5 */
            border-radius: 0.5rem; /* rounded-lg */
            font-weight: 600; /* font-semibold */
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .btn-orange:hover {
            background-color: var(--orange-dark);
            transform: translateY(-2px);
        }
        
        .btn-orange:active {
            transform: translateY(0);
        }

        .link-orange {
            color: var(--orange-main);
            transition: color 0.3s;
        }

        .link-orange:hover {
            color: var(--orange-dark);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl p-8 md:p-10 space-y-8 transform transition duration-500 hover:scale-105">
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png" alt="VON BUNDIT Logo" class="w-16 h-16 rounded-2xl shadow-md p-2 " style="    width: 30%; height: 30%;">
            </div>
            <h1 class="text-3xl font-bold text-gray-800 tracking-wider">ลงทะเบียน</h1>
            <p class="text-gray-500 mt-2">กรอกข้อมูลเพื่อสร้างบัญชีใหม่</p>
        </div>

        <?php if ($status_message): ?>
            <div id="message" class="text-center text-sm font-medium block mt-4 p-3 rounded-lg
                <?php echo $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>

        <form action="register" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-2">ชื่อจริง</label>
                    <input type="text" id="firstname" name="firstname" class="input-style" required>
                </div>
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-2">นามสกุล</label>
                    <input type="text" id="lastname" name="lastname" class="input-style" required>
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">อีเมล</label>
                <input type="email" id="email" name="email" class="input-style" required>
            </div>
            <div>
                <label for="mobile" class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์</label>
                <input type="tel" id="mobile" name="mobile" class="input-style" required>
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">ชื่อผู้ใช้งาน</label>
                <input type="text" id="username" name="username" class="input-style" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">รหัสผ่าน</label>
                <input type="password" id="password" name="password" class="input-style" required>
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">ยืนยันรหัสผ่าน</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input-style" required>
            </div>

            <button type="submit" class="w-full btn-orange text-lg">
                ลงทะเบียน
            </button>
        </form>
        <div class="text-center text-sm text-gray-600">
            มีบัญชีอยู่แล้วใช่ไหม?
            <a href="/classroom/study/login" class="font-medium link-orange">เข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>