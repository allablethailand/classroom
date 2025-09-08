<?php
session_start();
require_once("../../lib/connect_sqli.php");
// ตรวจสอบว่าไฟล์ actions/register.php และ actions/login.php มีฟังก์ชัน encryptToken/decryptToken หรือไม่
// ถ้าไม่มี ต้องเพิ่มโค้ดที่จำเป็นสำหรับการเข้ารหัสเข้าไป
// ตัวอย่าง: ถ้าฟังก์ชันนี้อยู่ในไฟล์อื่น
// require_once("path/to/encryption_functions.php");
global $mysqli;

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['student_id'])) {
    header("Location: /classroom/study/login");
    exit();
}

$student_id = $_SESSION['student_id'];
$status_message = '';
$is_success = false;

// ดึงข้อมูลนักเรียนปัจจุบัน
$stmt_fetch = $mysqli->prepare("SELECT `student_firstname_th`, `student_lastname_th`, `student_email`, `student_mobile`, `student_password`, `student_password_key` FROM `classroom_student` WHERE `student_id` = ?");
$stmt_fetch->bind_param("i", $student_id);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();
$row_all = $result_fetch->fetch_assoc();
$stmt_fetch->close();

// === ส่วนสำหรับจัดการการเปลี่ยนรหัสผ่าน ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_pass = $_POST['old_pass'] ? $_POST['old_pass'] : '';
    $new_pass = $_POST['new_pass'] ? $_POST['new_pass'] : '';
    $firm_pass = $_POST['firm_pass'] ? $_POST['firm_pass'] : '';

    if ($new_pass !== $firm_pass) {
        $status_message = "รหัสผ่านใหม่ไม่ตรงกัน!";
        $is_success = false;
    } else {
        // ตรวจสอบรหัสผ่านเก่าด้วยการถอดรหัส
        $stored_password_hash = $row_all['student_password'];
        $stored_password_key = $row_all['student_password_key'];
        
        // สมมติว่ามีฟังก์ชัน decryptToken() ที่ใช้ได้
        // NOTE: หากไม่มี ต้องนำโค้ดฟังก์ชันมาจากไฟล์อื่น
        // โค้ดนี้สมมติว่ามีฟังก์ชันนี้อยู่แล้ว
        $current_password_plain = decryptToken($stored_password_hash, $stored_password_key);
        
        if ($old_pass === $current_password_plain) {
            // สร้าง key ใหม่และเข้ารหัสรหัสผ่านใหม่
            $new_password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $new_password_encrypt = encryptToken($new_pass, $new_password_key);
            
            // ใช้ Prepared Statement ในการอัปเดต
            $stmt_update = $mysqli->prepare("UPDATE `classroom_student` SET `student_password`=?, `student_password_key`=?, `date_modify`=NOW() WHERE `student_id` = ?");
            if ($stmt_update) {
                $stmt_update->bind_param("ssi", $new_password_encrypt, $new_password_key, $student_id);
                if ($stmt_update->execute()) {
                    $status_message = "เปลี่ยนรหัสผ่านสำเร็จ!";
                    $is_success = true;
                } else {
                    $status_message = "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน: " . $stmt_update->error;
                    $is_success = false;
                }
                $stmt_update->close();
            } else {
                $status_message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $mysqli->error;
                $is_success = false;
            }
        } else {
            $status_message = "รหัสผ่านเก่าไม่ถูกต้อง!";
            $is_success = false;
        }
    }
    // อัปเดตข้อมูลเพื่อให้แสดงผลล่าสุดบนหน้าเว็บ
    $row_all = $result_fetch->fetch_assoc();
}

// === ส่วนสำหรับจัดการการแก้ไขข้อมูลส่วนตัว ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstname = $_POST['firstname'] ? $_POST['firstname'] : '';
    $lastname = $_POST['lastname'] ? $_POST['lastname'] : '';
    $email = $_POST['email'] ? $_POST['email'] : '';
    $mobile = $_POST['mobile'] ? $_POST['mobile'] : '';

    // ใช้ Prepared Statement ในการอัปเดตข้อมูล
    $stmt_profile = $mysqli->prepare("UPDATE `classroom_student` SET `student_firstname_th`=?, `student_lastname_th`=?, `student_email`=?, `student_mobile`=?, `date_modify`=NOW() WHERE `student_id` = ?");
    if ($stmt_profile) {
        $stmt_profile->bind_param("ssssi", $firstname, $lastname, $email, $mobile, $student_id);
        if ($stmt_profile->execute()) {
            $status_message = "บันทึกข้อมูลส่วนตัวสำเร็จ!";
            $is_success = true;
        } else {
            $status_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt_profile->error;
            $is_success = false;
        }
        $stmt_profile->close();
    } else {
        $status_message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $mysqli->error;
        $is_success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>ตั้งค่าโปรไฟล์ • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Kanit', sans-serif;
            color: #333;
        }
        .profile-card, .edit-profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 40px;
            position: relative;
        }
        .main-container {
            max-width: 960px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .section-header-icon {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        .section-header-icon i {
            font-size: 25px;
            color: #ff6600;
            margin-right: 15px;
        }
        .section-title {
            font-size: 1.8em;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }
        .form-control-edit {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        .form-control-edit:focus {
            border-color: #ff8c00;
            box-shadow: 0 0 5px rgba(255,140,0,0.3);
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
        .alert {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
     <?php require_once("component/header.php") ?>
    <div class="main-container">
        <div class="edit-profile-card">
            <div class="section-header-icon">
                <i class="fas fa-user-edit"></i>
                <h3 class="section-title">แก้ไขข้อมูลส่วนตัว</h3>
            </div>
            <hr>
            
            <?php if ($status_message): ?>
                <div class="alert <?php echo $is_success ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo $status_message; ?>
                </div>
            <?php endif; ?>

            <!-- <form action="" method="POST">
                <div class="form-group">
                    <label for="firstname">ชื่อจริง</label>
                    <input type="text" name="firstname" id="firstname" class="form-control-edit" value="<?php echo htmlspecialchars($row_all['student_firstname_th']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastname">นามสกุล</label>
                    <input type="text" name="lastname" id="lastname" class="form-control-edit" value="<?php echo htmlspecialchars($row_all['student_lastname_th']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" name="email" id="email" class="form-control-edit" value="<?php echo htmlspecialchars($row_all['student_email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mobile">เบอร์โทรศัพท์</label>
                    <input type="tel" name="mobile" id="mobile" class="form-control-edit" value="<?php echo htmlspecialchars($row_all['student_mobile']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">บันทึกข้อมูลส่วนตัว</button>
            </form> -->

            <hr>
            
            <div class="section-header-icon">
                <i class="fas fa-key"></i>
                <h3 class="section-title">เปลี่ยนรหัสผ่าน</h3>
            </div>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="old_pass">รหัสผ่านเก่า</label>
                    <input type="password" name="old_pass" id="old_pass" class="form-control-edit" required>
                </div>
                <div class="form-group">
                    <label for="new_pass">รหัสผ่านใหม่</label>
                    <input type="password" name="new_pass" id="new_pass" class="form-control-edit" required>
                </div>
                <div class="form-group">
                    <label for="firm_pass">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="firm_pass" id="firm_pass" class="form-control-edit" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-warning">บันทึกรหัสผ่านใหม่</button>
            </form>
        </div>
    </div>
    <?php require_once("component/footer.php") ?>
</body>
</html>