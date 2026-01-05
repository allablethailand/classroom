<?php
// photo.php - เพิ่มรูปภาพกลุ่มสำหรับ Face Recognition (แก้ไขให้ใช้ classroom_id)

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

// ดึงข้อมูล Bucket
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
        $new_file_path = $target_dir . $new_file_name;

        if (SaveFile($tmp_name, $new_file_path)) {
            return cleanPath($new_file_path);
        } else {
            return null;
        }
    }
    return null;
}

// ------------------------------------------------------------------------------------------------------
function GetFileContent($db_path, $save_to) {
    if (!preg_match('/^https?:\/\//', $db_path)) {
        if (function_exists('GetUrl')) {
            $db_path = GetUrl($db_path);
        }
    }

    $data = file_get_contents($db_path);
    if ($data === false) return false;

    return file_put_contents($save_to, $data) !== false;
}

// ------------------------------------------------------------------------------------------------------
// *** 4. ฟังก์ชันสำหรับรัน Python เพื่อตรวจจับใบหน้า (✅ แก้ไขให้ใช้ classroom_id) ***
// ------------------------------------------------------------------------------------------------------
function runFaceDetectionBatch($mysqli, $group_photo_id, $group_db_path, $classroom_id)
{
    global $base_include; 
    
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fr_temp' . DIRECTORY_SEPARATOR;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    $python_interpreter = '"C:\Program Files\Python310\python.exe"';
    $python_script = rtrim($base_include, '/\\') . '/classroom/management/actions/python/myphoto.py';
    
    $downloaded_paths = [];
    $found_student_ids = [];
    $is_error = false;
    $error_message = '';

    try {
        // --- 1. ดาวน์โหลดรูปกลุ่มจาก Bucket ---
        if (!function_exists('GetFileContent')) {
             throw new Exception("Function GetFileContent is not defined.");
        }
        
        $group_filename = basename($group_db_path);
        $group_temp_path = $temp_dir . uniqid('grp_') . '_' . $group_filename;
        
        if (!GetFileContent($group_db_path, $group_temp_path)) {
             throw new Exception("Cannot download group photo from bucket: {$group_db_path}");
        }
        $downloaded_paths[] = $group_temp_path;

        // --- 2. ✅ ดึงรูปโปรไฟล์เฉพาะนักเรียนใน classroom_id นี้เท่านั้น ---
        $ref_paths_all = [];

        // ✅ Query ที่แก้ไข: JOIN ตาราง classroom_student_join เพื่อกรองเฉพาะนักเรียนใน classroom_id
        $sql_all_students = "SELECT 
                                 cfs.student_id, 
                                 cfs.file_path
                             FROM 
                                 classroom_file_student cfs
                             INNER JOIN 
                                 classroom_student_join csj 
                                 ON cfs.student_id = csj.student_id
                             WHERE 
                                 csj.classroom_id = ? AND
                                 cfs.file_path IS NOT NULL AND 
                                 cfs.file_path != '' AND
                                 cfs.file_type = 'profile_image' AND  
                                 cfs.file_status = 1 AND 
                                 cfs.is_deleted = 0";
        
        $stmt = $mysqli->prepare($sql_all_students);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $mysqli->error);
        }
        
        $stmt->bind_param("i", $classroom_id);
        $stmt->execute();
        $result_all = $stmt->get_result();

        while ($row = $result_all->fetch_assoc()) {
            $student_id = $row['student_id'];
            $db_path = $row['file_path'];
            
            if ($db_path) {
                $ref_filename = basename($db_path);
                $ref_temp_path = $temp_dir . uniqid('ref_') . '_' . $ref_filename;

                if (GetFileContent($db_path, $ref_temp_path)) {
                    if (!isset($ref_paths_all[$student_id])) {
                        $ref_paths_all[$student_id] = [];
                    }
                    $ref_paths_all[$student_id][] = $ref_temp_path;
                    $downloaded_paths[] = $ref_temp_path;
                }
            }
        }
        $stmt->close();
        
        // --- 3. เตรียมข้อมูลและรัน Python ---
        $data_for_python = [
            'all_students_ref_paths' => $ref_paths_all, 
            'group_path' => $group_temp_path,
            'group_photo_id' => $group_photo_id
        ];

        $json_data_string = json_encode($data_for_python, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $escaped_json_data = str_replace('"', '\"', $json_data_string);
        $json_data_arg = "\"{$escaped_json_data}\"";

        $command = "{$python_interpreter} \"{$python_script}\" {$json_data_arg} 2>&1";
        $output = shell_exec($command);

        // --- 4. ประมวลผลผลลัพธ์จาก Python ---
        $output_lines = explode("\n", trim($output));
        $json_output_string = end($output_lines);
        $python_result = json_decode($json_output_string, true);

        if (json_last_error() === JSON_ERROR_NONE && $python_result && $python_result['status'] === 'success') {
            $found_student_ids = $python_result['found_student_ids'];

            // 5. ✅ บันทึกผลลัพธ์พร้อม classroom_id ลง DB
            if (!empty($found_student_ids)) {
                $value_parts = [];
                foreach ($found_student_ids as $sid) {
                    // ✅ เพิ่ม classroom_id ในการบันทึก
                    $value_parts[] = "({$classroom_id}, {$group_photo_id}, {$sid}, NOW())";
                }

                $sql_insert_batch = "REPLACE INTO `classroom_photo_face_detection`
                                     (`classroom_id`, `group_photo_id`, `student_id`, `detection_date`)
                                     VALUES " . implode(", ", $value_parts);

                $mysqli->query($sql_insert_batch);
                return "อัพโหลดสำเร็จ";
            }
            return "ไม่พบนักเรียนคนใดในรูปกลุ่ม";

        } else {
            $is_error = true;
            $error_message = "Python Batch Error: " . ($python_result['message'] ? $python_result['message'] : $output);
            error_log($error_message);
            return "⚠️ Error: ประมวลผล Face Recognition ไม่สำเร็จ (ตรวจสอบ Log: " . ($python_result['message'] ? $python_result['message'] : 'Unknown Error') . ")";
        }

    } catch (Exception $e) {
        $is_error = true;
        $error_message = "PHP Process Error: " . $e->getMessage();
        error_log($error_message);
        return "⚠️ Error: ระบบจัดการไฟล์ล้มเหลว ({$e->getMessage()})";
    } finally {
        // --- 6. ลบไฟล์ชั่วคราว ---
        foreach ($downloaded_paths as $path_to_delete) {
            if (file_exists($path_to_delete)) {
                unlink($path_to_delete);
            }
        }
    }
}

// ------------------------------------------------------------------------------------------------------
// *** 5. ส่วนจัดการการ Upload ***
// ------------------------------------------------------------------------------------------------------
global $mysqli;

$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null;
if (!$student_id) {
    $student_id = 2; // ใช้ค่าเริ่มต้นเพื่อการทดสอบ
}

// ✅ รับ classroom_id จาก POST หรือ GET
$classroom_id = null;
if (isset($_POST['classroom_id']) && !empty($_POST['classroom_id'])) {
    $classroom_id = intval($_POST['classroom_id']);
} elseif (isset($_GET['classroom_id']) && !empty($_GET['classroom_id'])) {
    $classroom_id = intval($_GET['classroom_id']);
}

$message = '';
$redirect_to = $_SERVER['REQUEST_URI'];
$uploaded_group_url = '';

// ✅ ตรวจสอบว่ามี classroom_id หรือไม่ก่อนทำงาน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['group_photo']) && isset($_POST['event_name'])) {

    if (!$classroom_id) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => '❌ Error: ไม่พบ classroom_id กรุณาเลือก Classroom ก่อน',
            'errors' => [],
            'uploaded_url' => '',
            'uploaded_path' => ''
        ]);
        exit;
    }

    $files = $_FILES['group_photo'];
    $event_name = trim($_POST['event_name']);
    $description = $event_name ? $event_name : 'No Event Description';

    $total_uploaded = 0;
    $total_detected = 0;
    $errors = [];
    $db_file_path = '';

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

        $db_file_path = uploadFile_bucket($file_data, 'classroom');

        if ($db_file_path) {
            // 1. ✅ บันทึก Path พร้อม classroom_id ลงในตาราง classroom_photo_album_group
            $sql = "INSERT INTO `classroom_photo_album_group`
                              (`classroom_id`, `group_photo_path`, `description`, `emp_create`, `date_create`)
                              VALUES (?, ?, ?, ?, NOW())";

            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("issi", $classroom_id, $db_file_path, $description, $student_id);
                if ($stmt->execute()) {
                    $new_group_photo_id = $mysqli->insert_id;
                    $stmt->close();
                    $total_uploaded++;
                    
                    if (function_exists('GetUrl')) {
                           $uploaded_group_url = GetUrl($db_file_path);
                    }
                    
                    // 2. ✅ รัน Face Recognition โดยส่ง classroom_id ไปด้วย
                    $detection_result_msg = runFaceDetectionBatch($mysqli, $new_group_photo_id, $db_file_path, $classroom_id);
                    if (strpos($detection_result_msg, 'อัพโหลดสำเร็จ') !== false) {
                        $total_detected++;
                    } else if (strpos($detection_result_msg, 'Error') !== false) {
                        $errors[] = $detection_result_msg;
                    }

                } else {
                    $errors[] = "Error: บันทึก DB ไม่สำเร็จสำหรับไฟล์ '{$file_data['name']}': " . $stmt->error;
                }
            }
        } else {
            $errors[] = "Error: อัปโหลดไฟล์ '{$file_data['name']}' ไม่สำเร็จ (SaveFile ล้มเหลว)";
        }
    }

    header('Content-Type: application/json');
    if ($total_uploaded > 0) {
        $msg_summary = "อัปโหลดรูปภาพกลุ่มสำเร็จ ({$total_uploaded} ไฟล์) และประมวลผล Face Recognition เรียบร้อย (พบใบหน้า {$total_detected} รูป)";
        $response = [
            'status' => 'success',
            'message' => $msg_summary,
            'errors' => $errors,
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
        <i class="fas fa-camera-retro fa-fw"></i> เพิ่มเมนู มินิเกม (Mini Game)
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
                
                <!-- ✅ Hidden Field สำหรับเก็บ classroom_id -->
                <input type="hidden" name="classroom_id" id="form_classroom_id" value="<?php echo $classroom_id ? $classroom_id : ''; ?>">
                
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

</div>

<script>
$(document).ready(function() {
    var iconUpload = '<i class="fas fa-cloud-upload-alt fa-fw"></i>';
    var iconLoading = '<i class="fas fa-spinner fa-spin fa-fw"></i>';

    $('#photo-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = $('#upload-photo-btn');
        var messageArea = $('#upload-message-area');
        
        submitBtn.prop('disabled', true).html(iconLoading + ' กำลังอัปโหลดและตรวจจับใบหน้า');
        messageArea.empty();

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    messageArea.html('<div class="alert alert-success"><i class="fas fa-check-circle fa-fw"></i> ' + response.message + '</div>');
                    form[0].reset(); 
                    // ✅ เก็บค่า classroom_id ไว้หลัง reset
                    var classroomId = $('#classroom_id').val();
                    $('#form_classroom_id').val(classroomId);
                    $('#event_name_modal').focus(); 

                } else {
                    var errorHtml = '<div class="alert alert-danger"><i class="fas fa-times-circle fa-fw"></i> ' + response.message + '</div>';
                    if (response.errors && response.errors.length > 0) {
                        errorHtml += '<div class="alert alert-warning" style="margin-top: 10px;"><strong>รายละเอียด:</strong><ul><li>' + response.errors.join('</li><li>') + '</li></ul></div>';
                    }
                    messageArea.html(errorHtml);
                }
            },
            error: function(xhr, status, error) {
                messageArea.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle fa-fw"></i> เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error + '</div>');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(iconUpload + ' อัปโหลดและบันทึก');
            }
        });
    });
});
</script>