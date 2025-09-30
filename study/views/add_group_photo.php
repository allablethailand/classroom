<?php
// add_group_photo.php
session_start();
// ตรวจสอบ session/สิทธิ์ของผู้ใช้
$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null;
if (!$student_id) {
    die("กรุณาเข้าสู่ระบบ");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['group_photo'])) {
    require_once("../../lib/connect_sqli.php");
    global $mysqli;

    // ***กำหนด SERVER PATH และ Upload Directory***
    // ต้องตรงกับ $document_root ที่ใช้ใน myphoto_process.php
    $document_root = "/var/www/html/"; 
    $upload_base_dir = "uploads/classroom/"; // โฟลเดอร์ปลายทาง
    
    // สร้าง Server Path เต็มสำหรับปลายทาง
    $target_dir = $document_root . $upload_base_dir;
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file = $_FILES['group_photo'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_error = $file['error'];
    $description = $_POST['description'] ? $_POST['description'] : 'No Description';

    if ($file_error === UPLOAD_ERR_OK) {
        // สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำกัน
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_file_name;
        
        // Path ที่จะบันทึกใน DB
        $db_file_path = $upload_base_dir . $new_file_name; 

        if (move_uploaded_file($file_tmp, $target_file)) {
            // บันทึก Path ลงในตาราง photo_album_group
            $sql = "INSERT INTO `photo_album_group` 
                    (`group_photo_path`, `description`, `emp_create`) 
                    VALUES (?, ?, ?)";
            
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sss", $db_file_path, $description, $student_id);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>✅ อัปโหลดรูปภาพกลุ่มสำเร็จ และบันทึก Path ลง DB แล้ว!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>❌ Error: บันทึก DB ไม่สำเร็จ: " . $stmt->error . "</div>";
                }
                $stmt->close();
            }
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: ย้ายไฟล์ไม่สำเร็จ</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ Error: การอัปโหลดไฟล์มีปัญหา (โค้ด: {$file_error})</div>";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>My Photo • ORIGAMI SYSTEM</title>
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
<link rel="stylesheet" href="/classroom/study/css/myphoto.css?v=<?php echo time(); ?>">
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
<script src="/classroom/study/js/myphoto.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <meta charset="UTF-8">
    <title>My Photo: รูปภาพที่มีฉัน</title>
    </head>
<body>
    <div class="container">
        <h2>เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition</h2>
        <?php echo $message; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="group_photo">เลือกรูปภาพกลุ่ม:</label>
                <input type="file" class="form-control" name="group_photo" id="group_photo" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="description">คำอธิบาย:</label>
                <input type="text" class="form-control" name="description" id="description" maxlength="255">
            </div>
            <button type="submit" class="btn btn-primary">อัปโหลดและบันทึก</button>
        </form>
    </div>
</body>
</html>