<?php

// add_group_photo.php

// ------------------------------------------------------------------------------------------------------
// *** 1. นำเข้าโค้ด _config.php/ไฟล์ตั้งค่า (โค้ดส่วนแรกสุดที่คุณให้มา) ***
// ------------------------------------------------------------------------------------------------------
// แก้ไขและตั้งค่า Timezone ให้เป็นเวลากรุงเทพฯ (Asia/Bangkok)
date_default_timezone_set('Asia/Bangkok');
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
$base_url = "http://" . $_SERVER['HTTP_HOST']; 

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    // ตรวจสอบเพื่อหา Path ของโปรเจกต์จริงบน Localhost
    if (!file_exists($base_include . "/dashboard.php") && isset($exl_path[1])) {
        $base_path .= "/" . $exl_path[1];
        // ปรับ base_url ให้มี project path
        $base_url .= $base_path; 
    }
    // ปรับ $base_include ให้เป็น Path รากของโปรเจกต์
    if (isset($exl_path[1])) {
        $base_include .= "/" . $exl_path[1];
    }
} else {
    // สำหรับ Server จริง (เช่น origami.local) ให้ปรับ base_url เป็น Path ที่ถูกต้อง
    $base_url = "http://origami.local"; 
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
// ------------------------------------------------------------------------------------------------------

require_once BASE_INCLUDE . '/lib/connect_sqli.php';

// *** ดึงฟังก์ชัน uploadFile จากโค้ดส่วนแรก (ไม่มีการแก้ไข) ***
function uploadFile($file, $name, $target_sub_dir = 'classroom') {
    global $base_path; 
    
    // Server Path: /var/www/html/origami.local/ + /uploads/ + classroom/
    $target_dir = rtrim($_SERVER['DOCUMENT_ROOT'] . $base_path, '/') . "/uploads/" . $target_sub_dir . "/";
    
    if (!isset($file[$name]['tmp_name']) || empty($file[$name]['tmp_name'])) {
        return null;
    }

    $tmp_name = $file[$name]['tmp_name'];
    $file_name = $file[$name]['name'];
    $file_error = $file[$name]['error'];

    if ($tmp_name && $file_error == UPLOAD_ERR_OK) {
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            // Path ที่จะบันทึกใน DB: uploads/classroom/68ccd...png
            $db_file_path = "uploads/" . $target_sub_dir . "/" . $new_file_name;
            return $db_file_path;
        } else {
            return null; // ย้ายไฟล์ไม่สำเร็จ
        }
    }
    return null; // อัปโหลดมีปัญหา
}

// ------------------------------------------------------------------------------------------------------
// *** NEW: ฟังก์ชันสำหรับรัน Python เพื่อตรวจจับใบหน้าในรูปกลุ่มที่เพิ่งอัปโหลด
// ------------------------------------------------------------------------------------------------------
function runFaceDetectionBatch($mysqli, $group_photo_id, $group_db_path) {
    global $base_path;
    $document_root = rtrim($_SERVER['DOCUMENT_ROOT'] . $base_path, '/') . '/'; 

    $python_interpreter = '"C:\Program Files\Python310\python.exe"'; 
    $python_script = BASE_INCLUDE . '/classroom/study/views/myphoto1.py'; 

    // 1. ดึงรูปโปรไฟล์ของนักเรียนทุกคน
    $ref_paths_all = [];
    $student_ids = []; 
    $sql_all_students = "SELECT DISTINCT t1.student_id, t2.file_path 
                         FROM `classroom_student` t1
                         JOIN `classroom_file_student` t2 ON t1.student_id = t2.student_id
                         WHERE t2.`file_type` = 'profile_image' 
                         AND t2.`is_deleted` = 0 
                         ORDER BY t1.student_id, t2.file_id DESC";
                         
    $result_all = $mysqli->query($sql_all_students);
    
    // Grouping file_path by student_id
    while ($row = $result_all->fetch_assoc()) {
        $student_id = $row['student_id'];
        $db_path = $row['file_path'];
        
        if (!isset($ref_paths_all[$student_id])) {
            $ref_paths_all[$student_id] = [];
        }
        // Limit 5 profile images per student
        if (count($ref_paths_all[$student_id]) < 5) {
            // แปลงเป็น Server Path และใช้ Backslash สำหรับ Python/Windows
            $server_path = str_replace('/', '\\', rtrim($document_root, '/\\') . '/' . ltrim($db_path, '/\\'));
            $ref_paths_all[$student_id][] = $server_path;
        }
        if (!in_array($student_id, $student_ids)) {
             $student_ids[] = $student_id;
        }
    }
    
    // 2. เตรียมข้อมูลสำหรับ Python
    $group_server_path = str_replace('/', '\\', rtrim($document_root, '/\\') . '/' . ltrim($group_db_path, '/\\'));

    $data_for_python = [
        // Map: student_id => [ref_server_paths]
        'all_students_ref_paths' => $ref_paths_all, 
        // Single group image to check
        'group_path' => $group_server_path, 
        'group_photo_id' => $group_photo_id
    ];
    
    // 3. เรียกใช้ Python
    $json_data_string = json_encode($data_for_python, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $escaped_json_data = str_replace('"', '\"', $json_data_string); 
    $json_data_arg = "\"{$escaped_json_data}\""; 
    
    // คำสั่งเรียก Python (ใช้ 2>&1 เพื่อจับ error output ด้วย)
    $command = "{$python_interpreter} \"{$python_script}\" {$json_data_arg} 2>&1";
    $output = shell_exec($command);
    
    // 4. ประมวลผลผลลัพธ์จาก Python (คาดหวังว่าจะเป็น JSON)
    $output_lines = explode("\n", trim($output));
    $json_output_string = end($output_lines); 
    $python_result = json_decode($json_output_string, true);
    
    if (json_last_error() === JSON_ERROR_NONE && $python_result && $python_result['status'] === 'success') {
        $found_student_ids = $python_result['found_student_ids'];
        
        // 5. บันทึกผลลัพธ์ลง DB
        if (!empty($found_student_ids)) {
            $value_parts = [];
            foreach ($found_student_ids as $sid) {
                // (group_photo_id, student_id, detection_date)
                $value_parts[] = "({$group_photo_id}, {$sid}, NOW())"; 
            }
            
            // ใช้ REPLACE INTO เพื่อป้องกัน Duplicate Key หากมีการรันซ้ำ
            $sql_insert_batch = "REPLACE INTO `photo_face_detection` 
                                 (`group_photo_id`, `student_id`, `detection_date`) 
                                 VALUES " . implode(", ", $value_parts);

            $mysqli->query($sql_insert_batch);
            
            return "พบนักเรียน {$group_photo_id} ในรูป: " . count($found_student_ids) . " คน";
        }
        return "ไม่พบนักเรียนคนใดในรูปกลุ่ม";

    } else {
        // Log หรือจัดการกับ Error จาก Python
        $error_msg = "Python Batch Error: " . ($python_result['message'] ? $python_result['message'] : $output);
        error_log($error_msg); 
        return "⚠️ Error: ประมวลผล Face Recognition ไม่สำเร็จ (ตรวจสอบ Log)";
    }
}
// ------------------------------------------------------------------------------------------------------


global $mysqli;

$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null;
if (!$student_id) {
    // ใช้ค่าเริ่มต้นเพื่อการทดสอบ
    $student_id = 2; 
}

$message = '';
$redirect_to = $_SERVER['PHP_SELF']; // URL ของไฟล์ปัจจุบัน

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['group_photo'])) {
    
    $file = $_FILES['group_photo'];
    $description = $_POST['description'] ? $_POST['description'] : 'No Description';
    
    $db_file_path = uploadFile(['group_photo' => $file], 'group_photo', 'classroom');

    if ($db_file_path) {
        // 1. บันทึก Path ลงในตาราง photo_album_group
        $sql = "INSERT INTO `photo_album_group` 
                (`group_photo_path`, `description`, `emp_create`, `date_create`) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssi", $db_file_path, $description, $student_id); 
            if ($stmt->execute()) {
                $new_group_photo_id = $mysqli->insert_id; // ได้ ID ของรูปกลุ่มที่เพิ่มใหม่
                $stmt->close();

                // 2. NEW: รัน Face Recognition Batch Process ทันที
                $detection_result_msg = runFaceDetectionBatch($mysqli, $new_group_photo_id, $db_file_path);
                
                // ✅ ใช้ PRG Pattern: Redirect หลัง INSERT และ Process สำเร็จ
                $success_msg = urlencode("✅ อัปโหลดรูปภาพกลุ่มสำเร็จ และ: {$detection_result_msg}");
                header("Location: {$redirect_to}?msg={$success_msg}&status=success");
                exit; // ออกจากการทำงาน
            } else {
                $message = "<div class='alert alert-danger'>❌ Error: บันทึก DB ไม่สำเร็จ: " . $stmt->error . "</div>";
                @unlink($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $db_file_path);
            }
            // $stmt->close() ถูกย้ายไปด้านใน
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ Error: อัปโหลดไฟล์ไม่สำเร็จ (อาจเป็นข้อจำกัดด้านขนาด/สิทธิ์การเขียนไฟล์)</div>";
    }
}

// **แสดง Message จาก Query Parameter หลัง Redirect**
if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_GET['msg'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars(urldecode($_GET['msg'])) . "</div>";
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Add Group Photo • ORIGAMI SYSTEM</title>
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
</head>

<body>
    <?php  require_once 'component/header.php'; // สมมติว่ามีไฟล์ header ?>

<div class="container-fluid">
    <div class="container-fluid">
        <h2>เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition</h2>
        <?php echo $message; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="group_photo">เลือกรูปภาพกลุ่ม:</label>
                <input type="file" class="form-control" name="group_photo" id="group_photo" accept="image/jpeg, image/png" required>
            </div>
            <div class="form-group">
                <label for="description">คำอธิบาย:</label>
                <input type="text" class="form-control" name="description" id="description" maxlength="255">
            </div>
            <button type="submit" class="btn btn-primary">อัปโหลดและบันทึก</button>
        </form>
        <hr>
        <p>Path การอัปโหลดใน Server: `<?php echo $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/uploads/classroom/'; ?>`</p>
    </div>
</div>
<?php  require_once("component/footer.php") // สมมติว่ามีไฟล์ footer ?>
</body>
</html>