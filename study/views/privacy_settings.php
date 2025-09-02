<?php
    session_start();
    require_once("../../lib/connect_sqli.php");
    include_once("../../login_history.php");
    global $mysqli;

    // Fetch user data
    $sql_all = $mysqli->query("SELECT * FROM m_employee LEFT JOIN employee_payroll ON m_employee.emp_id = employee_payroll.emp_id INNER JOIN m_employee_info ON m_employee.emp_id=m_employee_info.emp_id WHERE m_employee.emp_id = '{$_SESSION["emp_id"]}' ");
    $row_all = mysqli_fetch_array($sql_all);

    // Handle form submission for password change
    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_pass'];
        $new_pass = $_POST['new_pass'];
        $firm_pass = $_POST['firm_pass'];
        
        // Validation check
        if ($new_pass != $firm_pass) {
            echo "<script>alert('รหัสผ่านใหม่ไม่ตรงกัน!');</script>";
        } else {
            $current_password_hashed = $row_all['password'];
            // You should use a secure password hashing function like password_hash() and password_verify()
            // For this example, we'll use a simple check for demonstration purposes.
            // You should replace this with a secure method.
            
            // NOTE: Replace this with your actual password verification logic.
            // Example using md5 (NOT recommended for production)
            if (md5($old_pass) == $current_password_hashed) {
                // Hashing the new password (replace with a secure method like password_hash())
                $new_password_hashed = md5($new_pass); 
                $sql = "UPDATE m_employee SET password='{$new_password_hashed}' WHERE emp_id='{$_SESSION["emp_id"]}'";
                $result = $mysqli->query($sql);
                if ($result) {
                    echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จ');</script>";
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน');</script>";
                }
            } else {
                echo "<script>alert('รหัสผ่านเก่าไม่ถูกต้อง');</script>";
            }
        }
    }

    // Handle form submission for privacy settings
    if (isset($_POST['update_privacy'])) {
        $show_mobile = isset($_POST['show_mobile']) ? 'Y' : 'N';
        $show_email = isset($_POST['show_email']) ? 'Y' : 'N';
        $show_line = isset($_POST['show_line']) ? 'Y' : 'N';
        $show_ig = isset($_POST['show_ig']) ? 'Y' : 'N';
        $show_facebook = isset($_POST['show_facebook']) ? 'Y' : 'N';
        $sql = "UPDATE m_employee_info SET
                show_mobile='{$show_mobile}',
                show_email='{$show_email}',
                show_line='{$show_line}',
                show_ig='{$show_ig}',
                show_facebook='{$show_facebook}',
                last_update=NOW()
                WHERE emp_id='{$_SESSION["emp_id"]}'";
        $result = $mysqli->query($sql);
        if ($result) {
            echo "<script>alert('บันทึกการตั้งค่าความเป็นส่วนตัวสำเร็จ');</script>";
            redirect("privacy_settings.php");
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
        }
    }
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Privacy Settings • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Kanit', sans-serif;
            color: #333;
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
        .main-container {
            max-width: 960px;
            margin: 0 auto;
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
        .edit-profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 40px;
            position: relative;
            top: -50px;
            margin-top: 100px;
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
        /* New Styles for Privacy Settings */
        .setting-box {
            background-color: #f7f9fc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .setting-box h4 {
            font-weight: 600;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            font-weight: normal;
            font-size: 1.1em;
            cursor: pointer;
        }
        .checkbox-label input[type="checkbox"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php
    require_once ("component/header.php")
    ?>
    <div class="main-container">
         <a href="setting" style="position: absolute; top: 170px; left: 20px; z-index: 1000;">
        <button class="btn btn-warning" style="border-radius: 12px;width: 45px;height: 35px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <i class="fas fa-arrow-left" style="color: #fff; font-size: 1.2em;"></i>
        </button>
    </a>
        <div class="tab-content">
            <br>
            <div class="edit-profile-card">
               
                
                <div class="section-header-icon" style="justify-content: center;">
                    <i class="fas fa-lock" style="font-size: 25px;"></i>
                    <h3 class="section-title" style="padding-left:10px;">ตั้งค่าความเป็นส่วนตัว</h3>
                </div>
                <hr>

                <form action="privacy_settings.php" method="POST">
                    <div class="setting-box">
                        <div class="section-header-icon" style="justify-content: flex-start; margin-bottom: 15px;">
                            <i class="fas fa-key" style="font-size: 20px;"></i>
                            <h4 style="margin: 0; padding-left: 10px;">เปลี่ยนรหัสผ่าน</h4>
                        </div>
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
                    </div>
                    
                    <div class="setting-box">
                        <div class="section-header-icon" style="justify-content: flex-start; margin-bottom: 15px;">
                            <i class="fas fa-eye-slash" style="font-size: 20px;"></i>
                            <h4 style="margin: 0; padding-left: 10px;">ซ่อนข้อมูลติดต่อ</h4>
                        </div>
                        <p class="text-muted">เลือกข้อมูลที่คุณต้องการซ่อนจากผู้ใช้คนอื่น</p>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_mobile" value="Y" <?= ($row_all['show_mobile'] == 'Y') ? 'checked' : ''; ?>>
                                <span class="checkmark">เบอร์โทรศัพท์</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_email" value="Y" <?= ($row_all['show_email'] == 'Y') ? 'checked' : ''; ?>>
                                <span class="checkmark">อีเมล</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_line" value="Y" <?= ($row_all['show_line'] == 'Y') ? 'checked' : ''; ?>>
                                <span class="checkmark">Line</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_ig" value="Y" <?= ($row_all['show_ig'] == 'Y') ? 'checked' : ''; ?>>
                                <span class="checkmark">IG</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_facebook" value="Y" <?= ($row_all['show_facebook'] == 'Y') ? 'checked' : ''; ?>>
                                <span class="checkmark">Facebook</span>
                            </label>
                        </div>
                        <button type="submit" name="update_privacy" class="btn btn-warning">บันทึกการตั้งค่าความเป็นส่วนตัว</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>