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
            // password_hash() เป็นฟังก์ชันมาตรฐานของ PHP ที่ใช้ bcrypt และสร้าง salt ให้โดยอัตโนมัติ
            $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

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
                            `student_mobile`, `student_username`, `student_password`, 
                            `date_create`, `status`
                        ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)";

                $stmt = $mysqli->prepare($sql);
                if ($stmt) {
                    // Bind parameters และ execute
                    $stmt->bind_param("ssssss", $firstname, $lastname, $email, $mobile, $username_input, $hashed_password);

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
    <title>หน้าสมัครสมาชิก</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Use Inter font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-lg bg-white rounded-xl shadow-lg p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-800">ลงทะเบียน</h1>
            <p class="text-gray-500 mt-2">สร้างบัญชีใหม่เพื่อเข้าสู่ระบบ</p>
        </div>

        <!-- แสดงข้อความแจ้งเตือนตามสถานะ -->
        <?php if ($status_message): ?>
            <div id="message" class="text-center text-sm font-medium block mt-4 p-3 rounded-md
                <?php echo $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>

        <form action="register" method="POST" class="space-y-4">
            <!-- ส่วนข้อมูลส่วนตัว -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700">ชื่อจริง</label>
                    <input type="text" id="firstname" name="firstname" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700">นามสกุล</label>
                    <input type="text" id="lastname" name="lastname" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
            </div>

            <!-- ส่วนข้อมูลติดต่อ -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                <input type="email" id="email" name="email" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="mobile" class="block text-sm font-medium text-gray-700">เบอร์โทรศัพท์</label>
                <input type="tel" id="mobile" name="mobile" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <!-- ส่วนข้อมูลการเข้าสู่ระบบ -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้งาน</label>
                <input type="text" id="username" name="username" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">ยืนยันรหัสผ่าน</label>
                <input type="password" id="confirm_password" name="confirm_password" class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                ลงทะเบียน
            </button>
        </form>
        <div class="text-center text-sm text-gray-600 mt-4">
            มีบัญชีอยู่แล้วใช่ไหม?
            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">เข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>



