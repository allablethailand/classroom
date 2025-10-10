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

// *** ดึงฟังก์ชัน uploadFile จากโค้ดส่วนแรก (มีการปรับปรุงเล็กน้อยเพื่อรองรับการเรียกใช้ภายใน Loop) ***
function uploadFile($file_data, $target_sub_dir = 'classroom') {
    global $base_path; 
    
    // Server Path: /var/www/html/origami.local/ + /uploads/ + classroom/
    $target_dir = rtrim($_SERVER['DOCUMENT_ROOT'] . $base_path, '/') . "/uploads/" . $target_sub_dir . "/";
    
    if (!isset($file_data['tmp_name']) || empty($file_data['tmp_name'])) {
        return null;
    }

    $tmp_name = $file_data['tmp_name'];
    $file_name = $file_data['name'];
    $file_error = $file_data['error'];

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
// *** NEW: ฟังก์ชันสำหรับรัน Python เพื่อตรวจจับใบหน้าในรูปกลุ่มที่เพิ่งอัปโหลด (ไม่มีการแก้ไขจากเดิม)
// ------------------------------------------------------------------------------------------------------
function runFaceDetectionBatch($mysqli, $group_photo_id, $group_db_path) {
    global $base_path;
    $document_root = rtrim($_SERVER['DOCUMENT_ROOT'] . $base_path, '/') . '/'; 

    $python_interpreter = '"C:\Program Files\Python310\python.exe"'; 
    $python_script = BASE_INCLUDE . '/classroom/management/actions/python/myphoto.py'; 

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
            $sql_insert_batch = "REPLACE INTO `classroom_photo_face_detection` 
                                 (`group_photo_id`, `student_id`, `detection_date`) 
                                 VALUES " . implode(", ", $value_parts);

            $mysqli->query($sql_insert_batch);
            
            return "อัพโหลดสำเร็จ";
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
// ใช้ URL ของไฟล์ปัจจุบันสำหรับ Redirect (ถ้าไฟล์นี้ถูกเรียกผ่าน AJAX/Modal ควรจะจัดการ Client-side)
$redirect_to = $_SERVER['REQUEST_URI']; 

// **✅ NEW: เพิ่ม Logic สำหรับ Multiple File Upload**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['group_photo']) && isset($_POST['event_name'])) {
    
    $files = $_FILES['group_photo'];
    // ใช้ event_name เป็น Description และทำความสะอาดข้อมูล
    $event_name = trim($_POST['event_name']); 
    $description = $event_name ? $event_name : 'No Event Description';
    
    $total_uploaded = 0;
    $total_detected = 0;
    $errors = [];

    // วนลูปเพื่อจัดการไฟล์ที่อัปโหลดมาหลายไฟล์
    for ($i = 0; $i < count($files['name']); $i++) {
        // จัดรูปแบบ $files ให้อยู่ในรูปที่ฟังก์ชัน uploadFile ต้องการ
        $file_data = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];

        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "ไฟล์ '{$file_data['name']}' มีข้อผิดพลาดในการอัปโหลด ({$file_data['error']})";
            continue;
        }

        $db_file_path = uploadFile($file_data, 'classroom');

        if ($db_file_path) {
            // 1. บันทึก Path ลงในตาราง classroom_photo_album_group
            $sql = "INSERT INTO `classroom_photo_album_group` 
                     (`group_photo_path`, `description`, `emp_create`, `date_create`) 
                     VALUES (?, ?, ?, NOW())";
            
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                // ใช้ $description เป็นชื่อ Event
                $stmt->bind_param("ssi", $db_file_path, $description, $student_id); 
                if ($stmt->execute()) {
                    $new_group_photo_id = $mysqli->insert_id; 
                    $stmt->close();
                    $total_uploaded++;

                    // 2. รัน Face Recognition Batch Process ทันที
                    $detection_result_msg = runFaceDetectionBatch($mysqli, $new_group_photo_id, $db_file_path);
                    if (strpos($detection_result_msg, 'พบนักเรียน') !== false) {
                        $total_detected++;
                    }

                } else {
                    $errors[] = "Error: บันทึก DB ไม่สำเร็จสำหรับไฟล์ '{$file_data['name']}': " . $stmt->error;
                    @unlink($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $db_file_path);
                }
            }
        } else {
            $errors[] = "Error: อัปโหลดไฟล์ '{$file_data['name']}' ไม่สำเร็จ";
        }
    }
    
    // สรุปผลลัพธ์และส่งกลับไปยัง Client-side
    // เนื่องจากอยู่ภายใน Modal เราจะส่ง JSON response กลับไป
    header('Content-Type: application/json');
    if ($total_uploaded > 0) {
        $msg_summary = " อัปโหลดรูปภาพกลุ่มสำเร็จ";
        $response = [
            'status' => 'success',
            'message' => $msg_summary,
            'errors' => $errors
        ];
    } else {
        $msg_error = "❌ Error: ไม่สามารถอัปโหลดไฟล์ใดๆ ได้ (ตรวจสอบสิทธิ์ไฟล์หรือขนาดไฟล์)";
        $response = [
            'status' => 'error',
            'message' => $msg_error,
            'errors' => $errors
        ];
    }
    echo json_encode($response);
    exit; 
}


// **แสดง HTML Form สำหรับ Modal Body**
// หากมีการเรียกไฟล์นี้โดยไม่มี POST (เช่น ถูกโหลดครั้งแรกผ่าน AJAX เพื่อแสดง Form)
?>
<div class="container-fluid">
    <h2>เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition</h2>
    <div id="upload-message-area">
    <?php 
    // ถ้ามีการ Redirect มาพร้อม Message ใน Query Param 
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $status = htmlspecialchars($_GET['status']);
        $message = htmlspecialchars(urldecode($_GET['msg']));
        $alert_class = ($status == 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert {$alert_class}'>{$message}</div>";
    }
    ?>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" enctype="multipart/form-data" id="photo-upload-form">
        <div class="form-group">
            <label for="event_name">ชื่อ Event / คำอธิบาย (จะใช้จัดกลุ่มภาพ):</label>
            <input type="text" class="form-control" name="event_name" id="event_name_modal" maxlength="255" required>
        </div>
        <div class="form-group">
            <label for="group_photo">เลือกรูปภาพกลุ่ม (สามารถเลือกได้หลายไฟล์):</label>
            <input type="file" class="form-control" name="group_photo[]" id="group_photo_modal" accept="image/jpeg, image/png" multiple required>
        </div>
        <button type="submit" style="background-color: #00C292;" class="btn btn-primary" id="upload-photo-btn">อัปโหลดและบันทึก</button>
    </form>
</div>

<script>
// สคริปต์สำหรับการจัดการฟอร์มผ่าน AJAX เพื่อให้ Modal ไม่ปิด
// ต้องแน่ใจว่าได้โหลด jQuery แล้ว
$(document).ready(function() {
    $('#photo-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = $('#upload-photo-btn');
        var messageArea = $('#upload-message-area');

        // ปิดปุ่ม, แสดง Loading
        submitBtn.prop('disabled', true).text('กำลังอัปโหลด...');
        messageArea.empty();

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // คาดหวัง JSON Response
            success: function(response) {
                if (response.status === 'success') {
                    messageArea.html('<div class="alert alert-success">' + response.message + '</div>');
                    // อาจจะ clear form ได้ที่นี่
                    form[0].reset();
                } else {
                    var errorHtml = '<div class="alert alert-danger">' + response.message + '</div>';
                    if (response.errors && response.errors.length > 0) {
                         errorHtml += '<div class="alert alert-warning">รายละเอียดข้อผิดพลาด: ' + response.errors.join('<br>') + '</div>';
                    }
                    messageArea.html(errorHtml);
                }
            },
            error: function(xhr, status, error) {
                messageArea.html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error + '</div>');
            },
            complete: function() {
                // เปิดปุ่มกลับมา
                submitBtn.prop('disabled', false).html('<i class="fas fa-camera-retro"></i> อัปโหลดและบันทึก');
            }
        });
    });
});
</script>