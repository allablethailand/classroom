<?php
// photo.php - เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition

// ------------------------------------------------------------------------------------------------------
// *** 1. นำเข้าโค้ด _config.php/ไฟล์ตั้งค่า ***
// ------------------------------------------------------------------------------------------------------
session_start();
date_default_timezone_set('Asia/Bangkok');
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
$base_url = "http://" . $_SERVER['HTTP_HOST']; 

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php") && isset($exl_path[1])) {
        $base_path .= "/" . $exl_path[1];
        $base_url .= $base_path;
    }
    if (isset($exl_path[1])) {
        $base_include .= "/" . $exl_path[1];
    }
} else {
    $base_url = "http://" . $_SERVER['HTTP_HOST'];
}

define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';

// **สมมติว่า GetUrl/SaveFile/getBucketMaster/setBucket และ GetFileContent/DownloadFile ถูกกำหนดไว้ใน connect_sqli.php หรือไฟล์ที่เกี่ยวข้อง**

// ดึงข้อมูล Bucket (ต้องมีฟังก์ชันเหล่านี้ในระบบ)
if (function_exists('getBucketMaster') && function_exists('setBucket')) {
    global $mysqli;
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
    setBucket($fsData);
}

// ------------------------------------------------------------------------------------------------------
// *** 2. ฟังก์ชันจัดการ Path ***
// ------------------------------------------------------------------------------------------------------
function extractPathFromUrl($url)
{
    if (strpos($url, '://') === false) {
        return cleanPath($url);
    }
    $parsed_url = parse_url($url);
    if (isset($parsed_url['path'])) {
        $path = $parsed_url['path'];
        $path = strtok($path, '?');
        return cleanPath($path);
    }
    return '';
}

function cleanPath($path)
{
    return ltrim($path, '/');
}


// ------------------------------------------------------------------------------------------------------
// *** 3. ฟังก์ชัน uploadFile แบบ Bucket ***
// ------------------------------------------------------------------------------------------------------
/**
 * ฟังก์ชันสำหรับอัปโหลดไฟล์ตามรูปแบบที่ต้องการ (ใช้ SaveFile)
 *
 * @param array $file_data ข้อมูลไฟล์เดียวจาก $_FILES ที่จัดรูปแบบแล้ว
 * @param string $target_sub_dir Sub-directory ภายใต้ uploads/
 * @return string|null Path ใหม่ของไฟล์ที่อัปโหลดสำเร็จ (Path สำหรับ DB: uploads/classroom/...) หรือ null หากล้มเหลว
 */
function uploadFile_bucket($file_data, $target_sub_dir = 'classroom')
{
    if (!function_exists('SaveFile')) {
        return null; 
    }

    $target_dir = "uploads/" . $target_sub_dir . "/";

    if (!isset($file_data['tmp_name']) || empty($file_data['tmp_name'])) {
        return null;
    }

    $tmp_name = $file_data['tmp_name'];
    $file_name = $file_data['name'];
    $file_error = $file_data['error'];

    if ($tmp_name && $file_error == UPLOAD_ERR_OK) {
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_id = uniqid(); 
        $new_file_name = $new_file_id . '.' . $file_extension;

        // กำหนด Path สำหรับไฟล์จริง (Path ที่จะถูกบันทึกใน Bucket/DB)
        $new_file_path = $target_dir . $new_file_name;

        // A. Save ไฟล์ต้นฉบับไปยัง Bucket
        if (SaveFile($tmp_name, $new_file_path)) {
            // คืนค่าเป็น Path ที่จะบันทึกใน DB
            return cleanPath($new_file_path);
        } else {
            return null;
        }
    }
    return null;
}
// ------------------------------------------------------------------------------------------------------
function GetFileContent($db_path, $save_to) {
    // ถ้า path ไม่ใช่ URL ให้แปลงเป็น URL ก่อน
    if (!preg_match('/^https?:\/\//', $db_path)) {
        if (function_exists('GetUrl')) {
            $db_path = GetUrl($db_path);
        }
    }

    // ดาวน์โหลดจาก URL จริง
    $data = file_get_contents($db_path);
    if ($data === false) return false;

    return file_put_contents($save_to, $data) !== false;
}

// ------------------------------------------------------------------------------------------------------
// *** 4. ฟังก์ชันสำหรับรัน Python เพื่อตรวจจับใบหน้าในรูปกลุ่ม (มีการปรับปรุงส่วนดึงรูปโปรไฟล์/รูปกลุ่ม) ***
// ------------------------------------------------------------------------------------------------------
function runFaceDetectionBatch($mysqli, $group_photo_id, $group_db_path)
{
    global $base_include; 
    
    // ตั้งค่า Path ชั่วคราวบน Local Server 
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fr_temp' . DIRECTORY_SEPARATOR;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    // PATH ของ Python และ Script 
    $python_interpreter = '"C:\Program Files\Python310\python.exe"';
    $python_script = rtrim($base_include, '/\\') . '/classroom/management/actions/python/myphoto.py';
    
    $downloaded_paths = [];
    $found_student_ids = [];
    $is_error = false;
    $error_message = '';

    try {
        // --- 1. ดาวน์โหลดรูปกลุ่มจาก Bucket มาเก็บไว้ที่ Local Temp ---
        // **✅ การแก้ไขที่สำคัญ: ดาวน์โหลดจาก Bucket และส่ง Local Path ให้ Python**
        if (!function_exists('GetFileContent')) {
             throw new Exception("Function GetFileContent/DownloadFile is not defined.");
        }
        
        $group_filename = basename($group_db_path);
        $group_temp_path = $temp_dir . uniqid('grp_') . '_' . $group_filename;
        
        if (!GetFileContent($group_db_path, $group_temp_path)) {
            // GetFileContent ควรดาวน์โหลดไฟล์และคืนค่า true/false 
            // หรือใช้ 'DownloadFile' หากระบบของคุณใช้ชื่อฟังก์ชันนั้น
             throw new Exception("Cannot download group photo from bucket: {$group_db_path}");
        }
        $downloaded_paths[] = $group_temp_path;

        // --- 2. ดึงรูปโปรไฟล์ของนักเรียนทุกคนจาก DB และดาวน์โหลดมาเก็บไว้ที่ Local Temp ---
        $ref_paths_all = [];

        // ❌ โค้ดเดิม: ดึงจากตาราง classroom_student
        /*
        $sql_all_students = "SELECT `student_id`, `student_image_profile`
                             FROM `classroom_student`
                             WHERE `student_image_profile` IS NOT NULL AND `student_image_profile` != ''";
        */
        
        // ✅ โค้ดที่แก้ไข: ดึงจากตาราง classroom_file_student ตามเงื่อนไข
        $sql_all_students = "SELECT 
                                 `student_id`, 
                                 `file_path`
                             FROM 
                                 `classroom_file_student`
                             WHERE 
                                 `file_path` IS NOT NULL AND 
                                 `file_path` != '' AND
                                 `file_type` = 'profile_image' AND  
                                 `file_status` = 1 AND 
                                 `is_deleted` = 0";
        
        $result_all = $mysqli->query($sql_all_students);

        while ($row = $result_all->fetch_assoc()) {
            $student_id = $row['student_id'];
            // ❌ โค้ดเดิมใช้ $row['student_image_profile']
            $db_path = $row['file_path']; // Path ใน DB: uploads/classroom/....png
            
            if ($db_path) {
                // สร้างชื่อไฟล์ชั่วคราว
                $ref_filename = basename($db_path);
                $ref_temp_path = $temp_dir . uniqid('ref_') . '_' . $ref_filename;

                if (GetFileContent($db_path, $ref_temp_path)) {
                    if (!isset($ref_paths_all[$student_id])) {
                        $ref_paths_all[$student_id] = [];
                    }
                    // ✅ ส่ง Local Temp Path ให้ Python
                    $ref_paths_all[$student_id][] = $ref_temp_path;
                    $downloaded_paths[] = $ref_temp_path;
                }
            }
        }
        
        // --- 3. เตรียมข้อมูลและรัน Python ---
        $data_for_python = [
            // ✅ ส่ง Local Temp Path ให้ Python
            'all_students_ref_paths' => $ref_paths_all, 
            'group_path' => $group_temp_path, // ✅ Local Temp Path ของรูปกลุ่ม
            'group_photo_id' => $group_photo_id
        ];

        $json_data_string = json_encode($data_for_python, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $escaped_json_data = str_replace('"', '\"', $json_data_string);
        $json_data_arg = "\"{$escaped_json_data}\"";

        // รันคำสั่ง Python
        $command = "{$python_interpreter} \"{$python_script}\" {$json_data_arg} 2>&1";
        $output = shell_exec($command);

        // --- 4. ประมวลผลผลลัพธ์จาก Python ---
        $output_lines = explode("\n", trim($output));
        $json_output_string = end($output_lines);
        $python_result = json_decode($json_output_string, true);

        if (json_last_error() === JSON_ERROR_NONE && $python_result && $python_result['status'] === 'success') {
            $found_student_ids = $python_result['found_student_ids'];

            // 5. บันทึกผลลัพธ์ลง DB 
            if (!empty($found_student_ids)) {
                $value_parts = [];
                foreach ($found_student_ids as $sid) {
                    $value_parts[] = "({$group_photo_id}, {$sid}, NOW())";
                }

                $sql_insert_batch = "REPLACE INTO `classroom_photo_face_detection`
                                     (`group_photo_id`, `student_id`, `detection_date`)
                                     VALUES " . implode(", ", $value_parts);

                $mysqli->query($sql_insert_batch);
                return "อัพโหลดสำเร็จ";
            }
            return "ไม่พบนักเรียนคนใดในรูปกลุ่ม";

        } else {
            $is_error = true;
            $error_message = "Python Batch Error: " . ($python_result['message'] ? $python_result['message'] : $output);
            error_log($error_message);
            return "⚠️ Error: ประมวลผล Face Recognition ไม่สำเร็จ (ตรวจสอบ Log: " . ($python_result['message'] ? $python_result['message'] : 'Unkown Error') . ")";
        }

    } catch (Exception $e) {
        $is_error = true;
        $error_message = "PHP Process Error: " . $e->getMessage();
        error_log($error_message);
        return "⚠️ Error: ระบบจัดการไฟล์ล้มเหลว ({$e->getMessage()})";
    } finally {
        // --- 6. ลบไฟล์ชั่วคราวทั้งหมดที่ดาวน์โหลดมา ---
        // **✅ การแก้ไขที่สำคัญ: ลบไฟล์ Local Temp หลังใช้งาน**
        foreach ($downloaded_paths as $path_to_delete) {
            if (file_exists($path_to_delete)) {
                unlink($path_to_delete);
            }
        }
        // สามารถลบ Folder temp_dir ได้ถ้าต้องการ (แต่ไม่จำเป็น)
    }
}
// ------------------------------------------------------------------------------------------------------


global $mysqli;
// [ ... โค้ดส่วนที่เหลือของ photo.php ตั้งแต่ global $mysqli; จนจบไฟล์ เหมือนเดิม ... ]
$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null;
if (!$student_id) {
    $student_id = 2; // ใช้ค่าเริ่มต้นเพื่อการทดสอบ
}

$message = '';
$redirect_to = $_SERVER['REQUEST_URI'];
$uploaded_group_url = ''; // NEW: สำหรับเก็บ URL รูปกลุ่มที่อัปโหลดสำเร็จ

// **✅ NEW: ใช้ uploadFile_bucket แทน uploadFile เดิม**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['group_photo']) && isset($_POST['event_name'])) {

    $files = $_FILES['group_photo'];
    $event_name = trim($_POST['event_name']);
    $description = $event_name ? $event_name : 'No Event Description';

    $total_uploaded = 0;
    $total_detected = 0;
    $errors = [];
    $db_file_path = ''; // ประกาศนอก Loop เพื่อให้ใช้งานใน response ได้

    // วนลูปเพื่อจัดการไฟล์ที่อัปโหลดมาหลายไฟล์
    for ($i = 0; $i < count($files['name']); $i++) {
        $file_data = [
            'name'      => $files['name'][$i],
            'type'      => $files['type'][$i],
            'tmp_name'  => $files['tmp_name'][$i],
            'error'     => $files['error'][$i],
            'size'      => $files['size'][$i],
        ];

        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "ไฟล์ '{$file_data['name']}' มีข้อผิดพลาดในการอัปโหลด ({$file_data['error']})";
            continue;
        }

        // **ใช้ฟังก์ชันที่รองรับ Bucket**
        $db_file_path = uploadFile_bucket($file_data, 'classroom');

        if ($db_file_path) {
            // 1. บันทึก Path ลงในตาราง classroom_photo_album_group
            $sql = "INSERT INTO `classroom_photo_album_group`
                              (`group_photo_path`, `description`, `emp_create`, `date_create`)
                              VALUES (?, ?, ?, NOW())";

            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssi", $db_file_path, $description, $student_id);
                if ($stmt->execute()) {
                    $new_group_photo_id = $mysqli->insert_id;
                    $stmt->close();
                    $total_uploaded++;
                    
                    // เก็บ URL สำหรับแสดงผลหากมีการอัปโหลดสำเร็จ
                    if (function_exists('GetUrl')) {
                           $uploaded_group_url = GetUrl($db_file_path);
                    }
                    
                    // 2. รัน Face Recognition Batch Process ทันที
                    $detection_result_msg = runFaceDetectionBatch($mysqli, $new_group_photo_id, $db_file_path);
                    if (strpos($detection_result_msg, 'อัพโหลดสำเร็จ') !== false) {
                        $total_detected++;
                    } else if (strpos($detection_result_msg, 'Error') !== false) {
                        $errors[] = $detection_result_msg;
                    }

                } else {
                    $errors[] = "Error: บันทึก DB ไม่สำเร็จสำหรับไฟล์ '{$file_data['name']}': " . $stmt->error;
                    // ไม่ต้อง unlink เพราะไฟล์อยู่ใน Bucket/OSS/S3
                }
            }
        } else {
            $errors[] = "Error: อัปโหลดไฟล์ '{$file_data['name']}' ไม่สำเร็จ (SaveFile ล้มเหลว)";
        }
    }

    // สรุปผลลัพธ์และส่งกลับไปยัง Client-side
    header('Content-Type: application/json');
    if ($total_uploaded > 0) {
        $msg_summary = "อัปโหลดรูปภาพกลุ่มสำเร็จ ({$total_uploaded} ไฟล์) และประมวลผล Face Recognition เรียบร้อย (พบใบหน้า {$total_detected} รูป)";
        $response = [
            'status' => 'success',
            'message' => $msg_summary,
            'errors' => $errors,
            // **NEW: เพิ่ม URL ของรูปภาพที่อัปโหลดล่าสุด**
            'uploaded_url' => $uploaded_group_url, 
            'uploaded_path' => $db_file_path
        ];
    } else {
        $msg_error = "❌ Error: ไม่สามารถอัปโหลดไฟล์ใดๆ ได้ (ตรวจสอบสิทธิ์ไฟล์/SaveFile/ขนาดไฟล์)";
        $response = [
            'status' => 'error',
            'message' => $msg_error,
            'errors' => $errors,
            'uploaded_url' => '',
            'uploaded_path' => ''
        ];
    }
    echo json_encode($response);
    exit;
}
?>
<div class="container-fluid" style="padding: 15px;">
    <h3 style="border-left: 5px solid #00C292; padding-left: 10px; margin-bottom: 20px;">
        <i class="fas fa-camera-retro fa-fw"></i> เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition
    </h3>
    
    <div id="upload-message-area">
    <?php 
    if (isset($_GET['status']) && isset($_GET['msg'])) {
        $status = htmlspecialchars($_GET['status']);
        $message = htmlspecialchars(urldecode($_GET['msg']));
        $alert_class = ($status == 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert {$alert_class}'><i class='fas fa-info-circle'></i> {$message}</div>";
    }
    ?>
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading" style="background-color: #f7f7f7; color: #333;">
            <h4 class="panel-title"><i class="fas fa-upload fa-fw"></i> กรอกรายละเอียดและอัปโหลด</h4>
        </div>
        <div class="panel-body">
            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" enctype="multipart/form-data" id="photo-upload-form">
                
                <div class="form-group">
                    <label for="event_name_modal">
                        <i class="fas fa-tag fa-fw"></i> ชื่อ Event / คำอธิบาย:
                        <small class="text-muted">(จะใช้จัดกลุ่มภาพ)</small>
                    </label>
                    <input type="text" class="form-control" name="event_name" id="event_name_modal" maxlength="255" placeholder="เช่น กิจกรรมวันปีใหม่ 2568" required>
                </div>
                
                <div class="form-group">
                    <label for="group_photo_modal">
                        <i class="fas fa-images fa-fw"></i> เลือกรูปภาพกลุ่ม:
                        <small class="text-muted">(สามารถเลือกได้หลายไฟล์ .jpeg, .png)</small>
                    </label>
                    <input type="file" class="form-control" name="group_photo[]" id="group_photo_modal" accept="image/jpeg, image/png" multiple required>
                </div>
                
                <button type="submit" class="btn btn-lg btn-block" style="background-color: #00C292; color: white; margin-top: 20px;" id="upload-photo-btn">
                    <i class="fas fa-cloud-upload-alt fa-fw"></i> อัปโหลดและบันทึก
                </button>
                
            </form>
        </div>
    </div>
    
    <!-- <div class="panel panel-default" id="uploaded-image-panel" style="display: none; margin-top: 20px;">
        <div class="panel-heading">
            <h4 class="panel-title"><i class="fas fa-image fa-fw"></i> รูปภาพกลุ่มที่อัปโหลดล่าสุด</h4>
        </div>
        <div class="panel-body text-center">
            <img id="uploaded-group-img" src="" alt="Uploaded Group Photo" class="img-responsive" style="max-width: 100%; max-height: 300px; margin: 0 auto; border: 1px solid #ddd;">
            <p style="margin-top: 10px;">
                <small class="text-muted">Path ที่ถูกบันทึก: <code id="uploaded-group-path"></code></small><br>
                <small class="text-muted">URL สำหรับแสดงผล: <code id="uploaded-group-url"></code></small>
            </p>
        </div>
    </div> -->

</div>

<script>
// สคริปต์สำหรับการจัดการฟอร์มผ่าน AJAX เพื่อให้ Modal ไม่ปิด
// ต้องแน่ใจว่าได้โหลด jQuery แล้ว
$(document).ready(function() {
    var iconUpload = '<i class="fas fa-cloud-upload-alt fa-fw"></i>';
    var iconLoading = '<i class="fas fa-spinner fa-spin fa-fw"></i>';

    $('#photo-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = $('#upload-photo-btn');
        var messageArea = $('#upload-message-area');
        var imgPanel = $('#uploaded-image-panel');
        
        // ปิดปุ่ม, แสดง Loading
        submitBtn.prop('disabled', true).html(iconLoading + ' กำลังอัปโหลดและตรวจับใบหน้า');
        messageArea.empty();
        imgPanel.hide(); // ซ่อน Panel รูปเก่า

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // คาดหวัง JSON Response
            success: function(response) {
                if (response.status === 'success') {
                    // แสดงผลสำเร็จด้วย Alert-success
                    messageArea.html('<div class="alert alert-success"><i class="fas fa-check-circle fa-fw"></i> ' + response.message + '</div>');
                    // Clear form
                    form[0].reset(); 
                    $('#event_name_modal').focus(); 
                    
                    // **NEW: แสดงรูปภาพและ Path**
                    if (response.uploaded_url) {
                        $('#uploaded-group-img').attr('src', response.uploaded_url);
                        $('#uploaded-group-url').text(response.uploaded_url);
                        $('#uploaded-group-path').text(response.uploaded_path);
                        imgPanel.show();
                    }

                } else {
                    // แสดงผลผิดพลาดด้วย Alert-danger
                    var errorHtml = '<div class="alert alert-danger"><i class="fas fa-times-circle fa-fw"></i> ' + response.message + '</div>';
                    if (response.errors && response.errors.length > 0) {
                        errorHtml += '<div class="alert alert-warning" style="margin-top: 10px;"><strong>รายละเอียด:</strong><ul><li>' + response.errors.join('</li><li>') + '</li></ul></div>';
                    }
                    messageArea.html(errorHtml);
                }
            },
            error: function(xhr, status, error) {
                // ข้อผิดพลาดจากการเชื่อมต่อ
                messageArea.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle fa-fw"></i> เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error + '</div>');
            },
            complete: function() {
                // เปิดปุ่มกลับมา
                submitBtn.prop('disabled', false).html(iconUpload + ' อัปโหลดและบันทึก');
            }
        });
    });
});
</script>