<?php
// photo.php - Ultra Fast Version with Pre-extracted Face Embeddings

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
// *** Helper Functions ***
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

function runEmbeddingGeneration($mysqli, $file_id, $file_path, $student_id)
{
    global $base_include;
    
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fr_temp' . DIRECTORY_SEPARATOR;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    $python_interpreter = '"C:\Program Files\Python310\python.exe"';
    $python_script = rtrim($base_include, '/\\') . '/classroom/management/actions/python/generate_embedding.py';
    
    $downloaded_path = null;
    $result_message = '';

    try {
        if (!function_exists('GetFileContent')) {
            throw new Exception("Function GetFileContent is not defined.");
        }
        
        $file_filename = basename($file_path);
        $file_temp_path = $temp_dir . uniqid('emb_') . '_' . $file_filename;
        
        if (!GetFileContent($file_path, $file_temp_path)) {
            throw new Exception("Cannot download profile photo from bucket: {$file_path}");
        }
        $downloaded_path = $file_temp_path;

        $data_for_python = [
            'file_path' => $file_temp_path,
            'file_id' => $file_id,
            'student_id' => $student_id,
        ];

        $json_data_string = json_encode($data_for_python, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $escaped_json_data = str_replace('"', '\"', $json_data_string);
        $json_data_arg = "\"{$escaped_json_data}\"";

        $command = "{$python_interpreter} \"{$python_script}\" {$json_data_arg} 2>&1";
        $output = shell_exec($command);

        $output_lines = explode("\n", trim($output));
        $json_output_string = end($output_lines);
        $python_result = json_decode($json_output_string, true);

        if (json_last_error() === JSON_ERROR_NONE && $python_result) {
            
            if ($python_result['status'] === 'success' && $python_result['embedding']) {
                $embedding_json = json_encode($python_result['embedding']);
                
                $sql_update = "UPDATE `classroom_file_student` 
                               SET `face_embedding_json` = ?, 
                                   `date_modify` = NOW() 
                               WHERE `file_id` = ?";
                $stmt_update = $mysqli->prepare($sql_update);
                if ($stmt_update) {
                    $stmt_update->bind_param("si", $embedding_json, $file_id);
                    if ($stmt_update->execute()) {
                        $result_message = "✅ Success: บันทึก Embedding (ID: {$file_id}) สำเร็จ";
                    } else {
                        $result_message = "❌ DB Error: บันทึก Embedding ล้มเหลว (ID: {$file_id}): " . $stmt_update->error;
                        error_log($result_message);
                    }
                    $stmt_update->close();
                } else {
                    $result_message = "❌ DB Prepare Error: อัปเดต Embedding ล้มเหลว: " . $mysqli->error;
                    error_log($result_message);
                }
                
            } elseif ($python_result['status'] === 'warning') {
                $result_message = "⚠️ Warning (ID: {$file_id}): " . $python_result['message'];
            } else {
                $result_message = "❌ Python Error: " . ($python_result['message'] ? $python_result['message'] : $output);
                error_log("Embedding Generation Error: " . $result_message);
            }

        } else {
            $result_message = "❌ Python JSON Error: ไม่สามารถอ่านผลลัพธ์จาก Python ได้: " . $output;
            error_log($result_message);
        }

    } catch (Exception $e) {
        $result_message = "❌ PHP Process Error: ระบบจัดการไฟล์ล้มเหลว ({$e->getMessage()})";
        error_log("Embedding Generation PHP Error: " . $e->getMessage());
    } finally {
        if ($downloaded_path && file_exists($downloaded_path)) {
            unlink($downloaded_path);
        }
    }
    return $result_message;
}


// ------------------------------------------------------------------------------------------------------
// *** 🚀 NEW: Match Faces (Ultra Fast - ใช้ Embeddings ที่ Extract ไว้แล้ว) ***
// ------------------------------------------------------------------------------------------------------
function matchGroupFaces($mysqli, $group_photo_id, $classroom_id)
{
    global $base_include;
    
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fr_temp' . DIRECTORY_SEPARATOR;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    $python_interpreter = '"C:\Program Files\Python310\python.exe"';
    $python_script = rtrim($base_include, '/\\') . '/classroom/management/actions/python/myphoto.py';

    try {
        error_log("=== MATCH GROUP FACES ===");
        error_log("Group Photo ID: {$group_photo_id}");
        error_log("Classroom ID: {$classroom_id}");
        
        // 1. ดึง embeddings ของใบหน้าในรูปกลุ่ม จาก DB
        $sql_group_faces = "SELECT `face_index`, `face_embedding_json`
                            FROM `classroom_photo_group_faces`
                            WHERE `group_photo_id` = ?
                            ORDER BY `face_index` ASC";
        
        $stmt_gf = $mysqli->prepare($sql_group_faces);
        $stmt_gf->bind_param("i", $group_photo_id);
        $stmt_gf->execute();
        $result_gf = $stmt_gf->get_result();
        
        $group_faces = [];
        while ($row = $result_gf->fetch_assoc()) {
            $group_faces[] = [
                'face_index' => intval($row['face_index']),
                'embedding' => json_decode($row['face_embedding_json'], true)
            ];
        }
        $stmt_gf->close();
        
        if (empty($group_faces)) {
            return "⚠️ ไม่พบ Face Embeddings ของรูปกลุ่มนี้ใน DB";
        }
        
        error_log("Loaded " . count($group_faces) . " group face embeddings from DB");
        
        // 2. ดึง embeddings ของนักเรียน
        $ref_embeddings_all = [];

        $sql_students = "SELECT 
                            cfs.student_id, 
                            cfs.face_embedding_json
                         FROM 
                            classroom_file_student cfs
                         INNER JOIN 
                            classroom_student_join csj 
                            ON cfs.student_id = csj.student_id
                         WHERE 
                            csj.classroom_id = ? AND
                            cfs.face_embedding_json IS NOT NULL AND 
                            cfs.file_type = 'profile_image' AND 
                            cfs.file_status = 1 AND 
                            cfs.is_deleted = 0";
        
        $stmt_s = $mysqli->prepare($sql_students);
        $stmt_s->bind_param("i", $classroom_id);
        $stmt_s->execute();
        $result_s = $stmt_s->get_result();

        while ($row = $result_s->fetch_assoc()) {
            $student_id = $row['student_id'];
            $embedding_json = $row['face_embedding_json'];
            
            if ($embedding_json) {
                $ref_embeddings = json_decode($embedding_json, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($ref_embeddings) && count($ref_embeddings) == 512) {
                    if (!isset($ref_embeddings_all[$student_id])) {
                        $ref_embeddings_all[$student_id] = [];
                    }
                    $ref_embeddings_all[$student_id][] = $ref_embeddings;
                }
            }
        }
        $stmt_s->close();
        
        if (empty($ref_embeddings_all)) {
            return "⚠️ ไม่พบ Face Embedding ของนักเรียน กรุณากดปุ่ม 'สร้าง Face Embedding' ก่อน";
        }
        
        error_log("Loaded embeddings for " . count($ref_embeddings_all) . " students");
        
        // 3. เตรียมข้อมูลและรัน Python (mode: match)
        $data_for_python = [
            'mode' => 'match',
            'group_faces' => $group_faces,
            'all_students_ref_embeddings' => $ref_embeddings_all,
            'threshold' => 0.2
        ];

        $json_data_string = json_encode($data_for_python, JSON_UNESCAPED_SLASHES);
        $json_file = $temp_dir . uniqid('match_') . '.json';
        
        if (file_put_contents($json_file, $json_data_string) === FALSE) {
            throw new Exception("Cannot write JSON file");
        }
        
        $command = "{$python_interpreter} \"{$python_script}\" \"{$json_file}\"";
        
        $output_array = [];
        $return_var = 0;
        exec($command, $output_array, $return_var);
        
        if (file_exists($json_file)) {
            unlink($json_file);
        }
        
        if ($return_var !== 0) {
             throw new Exception("Python script failed");
        }

        if (empty($output_array)) {
            throw new Exception("Python returned empty output");
        }
        
        $json_output_string = end($output_array);
        $python_result = json_decode($json_output_string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON from Python");
        }
        
        if ($python_result['status'] === 'success') {
            $found_student_ids = $python_result['found_student_ids'];

            if (!empty($found_student_ids)) {
                $value_parts = [];
                foreach ($found_student_ids as $sid) {
                    if (is_int($sid)) {
                        $value_parts[] = "({$classroom_id}, {$group_photo_id}, {$sid}, NOW())";
                    }
                }

                if (!empty($value_parts)) {
                    $sql_insert_batch = "REPLACE INTO `classroom_photo_face_detection`
                                            (`classroom_id`, `group_photo_id`, `student_id`, `detection_date`)
                                            VALUES " . implode(", ", $value_parts);

                    if ($mysqli->query($sql_insert_batch)) {
                        error_log("Saved " . count($value_parts) . " students to detection table");
                        return "อัพโหลดสำเร็จ";
                    } else {
                        error_log("DB Insert Error: " . $mysqli->error);
                        return "⚠️ Error: บันทึกข้อมูลลงฐานข้อมูลไม่สำเร็จ";
                    }
                }
            }
            return "ไม่พบนักเรียนคนใดในรูปกลุ่ม";

        } else {
            throw new Exception($python_result['message']);
        }

    } catch (Exception $e) {
        error_log("Match Error: " . $e->getMessage());
        return "⚠️ Error: {$e->getMessage()}";
    }
}


// ------------------------------------------------------------------------------------------------------
// *** 🚀 NEW: Extract Face Embeddings from Group Photo ***
// ------------------------------------------------------------------------------------------------------
function extractGroupFaceEmbeddings($mysqli, $group_photo_id, $group_db_path)
{
    global $base_include;
    
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fr_temp' . DIRECTORY_SEPARATOR;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    $python_interpreter = '"C:\Program Files\Python310\python.exe"';
    $python_script = rtrim($base_include, '/\\') . '/classroom/management/actions/python/myphoto.py';
    
    $group_temp_path = null;

    try {
        error_log("=== EXTRACT GROUP FACES ===");
        error_log("Group Photo ID: {$group_photo_id}");
        
        if (!function_exists('GetFileContent')) {
             throw new Exception("Function GetFileContent is not defined.");
        }
        
        $group_filename = basename($group_db_path);
        $group_temp_path = $temp_dir . uniqid('grp_') . '_' . $group_filename;
        
        if (!GetFileContent($group_db_path, $group_temp_path)) {
             throw new Exception("Cannot download group photo: {$group_db_path}");
        }
        
        // เตรียมข้อมูลสำหรับ Python (mode: extract)
        $data_for_python = [
            'mode' => 'extract',
            'group_path' => $group_temp_path
        ];

        $json_data_string = json_encode($data_for_python, JSON_UNESCAPED_SLASHES);
        $json_file = $temp_dir . uniqid('extract_') . '.json';
        
        if (file_put_contents($json_file, $json_data_string) === FALSE) {
            throw new Exception("Cannot write JSON file");
        }
        
        $command = "{$python_interpreter} \"{$python_script}\" \"{$json_file}\"";
        
        $output_array = [];
        $return_var = 0;
        exec($command, $output_array, $return_var);
        
        if (file_exists($json_file)) {
            unlink($json_file);
        }
        
        if ($return_var !== 0) {
             throw new Exception("Python script failed with code: {$return_var}");
        }

        if (empty($output_array)) {
            throw new Exception("Python returned empty output");
        }
        
        $json_output_string = end($output_array);
        $python_result = json_decode($json_output_string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON from Python");
        }
        
        if ($python_result['status'] === 'success') {
            $faces = $python_result['faces'];
            
            error_log("Extracted " . count($faces) . " faces from group photo");
            
            // 🟢 บันทึก embeddings ลงตาราง classroom_photo_group_faces
            if (!empty($faces)) {
                $value_parts = [];
                foreach ($faces as $face) {
                    $face_index = intval($face['face_index']);
                    $embedding_json = $mysqli->real_escape_string(json_encode($face['embedding']));
                    $bbox_json = $mysqli->real_escape_string(json_encode($face['bbox']));
                    
                    $value_parts[] = "({$group_photo_id}, {$face_index}, '{$embedding_json}', '{$bbox_json}', NOW())";
                }
                
                $sql_insert = "INSERT INTO `classroom_photo_group_faces`
                                  (`group_photo_id`, `face_index`, `face_embedding_json`, `face_bbox`, `date_create`)
                                  VALUES " . implode(", ", $value_parts);
                
                if ($mysqli->query($sql_insert)) {
                    error_log("Saved " . count($faces) . " face embeddings to DB");
                    return [
                        'status' => 'success',
                        'faces' => $faces,
                        'message' => "Extract สำเร็จ " . count($faces) . " ใบหน้า"
                    ];
                } else {
                    throw new Exception("DB Insert Error: " . $mysqli->error);
                }
            }
            
            return [
                'status' => 'success',
                'faces' => [],
                'message' => "ไม่พบใบหน้าในรูป"
            ];
            
        } else {
            throw new Exception($python_result['message']);
        }

    } catch (Exception $e) {
        error_log("Extract Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
        
    } finally {
        if ($group_temp_path && file_exists($group_temp_path)) {
            unlink($group_temp_path);
        }
    }
}

// ------------------------------------------------------------------------------------------------------
// *** Main Upload Handler ***
// ------------------------------------------------------------------------------------------------------
global $mysqli;

$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : 2;

$classroom_id = null;
if (isset($_POST['classroom_id']) && !empty($_POST['classroom_id'])) {
    $classroom_id = intval($_POST['classroom_id']);
} elseif (isset($_GET['classroom_id']) && !empty($_GET['classroom_id'])) {
    $classroom_id = intval($_GET['classroom_id']);
}

// ** Generate Embedding Batch **
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_embedding_batch') {
    header('Content-Type: application/json');
    if (!$classroom_id) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ classroom_id']);
        exit;
    }

    $errors = [];
    $total_processed = 0;
    $total_embeddings_created = 0;
    
    $sql_photos = "SELECT 
                        cfs.file_id, 
                        cfs.student_id,
                        cfs.file_path
                     FROM 
                        classroom_file_student cfs
                     INNER JOIN 
                        classroom_student_join csj 
                        ON cfs.student_id = csj.student_id
                     WHERE 
                        csj.classroom_id = ? AND
                        cfs.file_type = 'profile_image' AND 
                        cfs.file_status = 1 AND 
                        cfs.is_deleted = 0 
                     ORDER BY 
                        cfs.file_id ASC 
                     LIMIT 100";

    $stmt_fetch = $mysqli->prepare($sql_photos);
    $stmt_fetch->bind_param("i", $classroom_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    while ($row = $result_fetch->fetch_assoc()) {
        $total_processed++;
        $result_msg = runEmbeddingGeneration($mysqli, $row['file_id'], $row['file_path'], $row['student_id']);
        
        if (strpos($result_msg, '✅ Success') !== false) {
            $total_embeddings_created++;
        } else {
            $errors[] = $result_msg;
        }
    }
    $stmt_fetch->close();

    echo json_encode([
        'status' => 'success', 
        'message' => "ประมวลผลไป {$total_processed} รูป, สร้าง Embedding {$total_embeddings_created} รูป",
        'processed' => $total_processed,
        'created' => $total_embeddings_created,
        'errors' => $errors
    ]);
    exit;
}

// ==========================================
// API 2: อัปโหลดรูปกลุ่ม (ไม่ Extract)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['group_photo']) && isset($_POST['event_name'])) {
    if (!$classroom_id) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => '❌ Error: ไม่พบ classroom_id',
            'errors' => []
        ]);
        exit;
    }

    $files = $_FILES['group_photo'];
    $event_name = trim($_POST['event_name']);
    $description = $event_name ? $event_name : 'No Event Description';

    $total_uploaded = 0;
    $errors = [];
    $uploaded_group_url = '';
    $uploaded_ids = [];

    for ($i = 0; $i < count($files['name']); $i++) {
        $file_data = [
            'name'      => $files['name'][$i],
            'type'      => $files['type'][$i],
            'tmp_name'  => $files['tmp_name'][$i],
            'error'     => $files['error'][$i],
            'size'      => $files['size'][$i],
        ];

        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "ไฟล์ '{$file_data['name']}' มีข้อผิดพลาด ({$file_data['error']})";
            continue;
        }

        $db_file_path = uploadFile_bucket($file_data, 'classroom');

        if ($db_file_path) {
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
                    $uploaded_ids[] = $new_group_photo_id;

                    if (function_exists('GetUrl')) {
                           $uploaded_group_url = GetUrl($db_file_path);
                    }
                } else {
                    $errors[] = "บันทึก DB ไม่สำเร็จสำหรับ '{$file_data['name']}'";
                }
            }
        } else {
            $errors[] = "อัปโหลดไฟล์ '{$file_data['name']}' ไม่สำเร็จ";
        }
    }

    header('Content-Type: application/json');
    if ($total_uploaded > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "อัปโหลดสำเร็จ {$total_uploaded} ไฟล์",
            'errors' => $errors,
            'uploaded_url' => $uploaded_group_url,
            'uploaded_ids' => $uploaded_ids
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => "❌ ไม่สามารถอัปโหลดไฟล์ใดๆ ได้",
            'errors' => $errors
        ]);
    }
    exit;
}

// ==========================================
// API 3: ดึงรหัสใบหน้าจากรูปกลุ่มในอัลบั้มที่ระบุ
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'extract_album_faces') {
    header('Content-Type: application/json');
    $album_name = isset($_POST['album_name']) ? trim($_POST['album_name']) : '';

    if (!$classroom_id || empty($album_name)) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ classroom_id หรือ album_name']);
        exit;
    }

    $errors = [];
    $total_processed = 0;
    $total_extracted = 0;

    // ดึงรูปทั้งหมดในอัลบั้ม
    $sql = "SELECT group_photo_id, group_photo_path 
            FROM classroom_photo_album_group 
            WHERE classroom_id = ? AND description = ? AND is_deleted = 0";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $classroom_id, $album_name);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $total_processed++;
        $extract_result = extractGroupFaceEmbeddings($mysqli, $row['group_photo_id'], $row['group_photo_path']);
        
        if ($extract_result['status'] === 'success') {
            $total_extracted++;
        } else {
            $errors[] = "รูป ID {$row['group_photo_id']}: {$extract_result['message']}";
        }
    }
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => "ประมวลผล {$total_processed} รูป, ดึงรหัสใบหน้าสำเร็จ {$total_extracted} รูป",
        'processed' => $total_processed,
        'extracted' => $total_extracted,
        'errors' => $errors
    ]);
    exit;
}

// ==========================================
// API 4: จับคู่ใบหน้า (Match Faces)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batch_match_faces') {
    header('Content-Type: application/json');
    if (!$classroom_id) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ classroom_id']);
        exit;
    }

    $errors = [];
    $total_processed = 0;
    $total_detected = 0;
    
    // ค้นหา description ล่าสุด
    $sql_latest_description = "SELECT `description`
                               FROM `classroom_photo_album_group`
                               WHERE `classroom_id` = ? AND `is_deleted` = 0
                               ORDER BY `group_photo_id` DESC
                               LIMIT 1";

    $stmt_desc = $mysqli->prepare($sql_latest_description);
    $stmt_desc->bind_param("i", $classroom_id);
    $stmt_desc->execute();
    $result_desc = $stmt_desc->get_result();
    $latest_description = null;
    if ($row_desc = $result_desc->fetch_assoc()) {
        $latest_description = $row_desc['description'];
    }
    $stmt_desc->close();
    
    if (empty($latest_description)) {
         echo json_encode([
            'status' => 'success', 
            'message' => "✅ ไม่พบชื่อ Event ล่าสุดใน Classroom นี้",
            'processed' => 0,
            'detected' => 0,
            'errors' => []
        ]);
        exit;
    }

    // ค้นหารูปที่ต้อง Match
    $sql_photos_to_match = "SELECT 
                                cpag.group_photo_id
                            FROM 
                                classroom_photo_album_group cpag
                            INNER JOIN 
                                classroom_photo_group_faces cpgf 
                                ON cpag.group_photo_id = cpgf.group_photo_id
                            LEFT JOIN 
                                classroom_photo_face_detection cpfd
                                ON cpag.group_photo_id = cpfd.group_photo_id
                            WHERE 
                                cpag.classroom_id = ? AND
                                cpag.description = ? AND
                                cpfd.group_photo_id IS NULL
                            GROUP BY
                                cpag.group_photo_id
                            LIMIT 100";

    $stmt_fetch = $mysqli->prepare($sql_photos_to_match);
    $stmt_fetch->bind_param("is", $classroom_id, $latest_description);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    $ids_to_match = [];
    while ($row = $result_fetch->fetch_assoc()) {
        $ids_to_match[] = intval($row['group_photo_id']);
    }
    $stmt_fetch->close();
    
    if (empty($ids_to_match)) {
        echo json_encode([
            'status' => 'success', 
            'message' => "✅ ไม่พบรูปกลุ่มใน Event '{$latest_description}' ที่ต้องทำการ Match",
            'processed' => 0,
            'detected' => 0,
            'errors' => []
        ]);
        exit;
    }
    
    // เริ่ม Match
    foreach ($ids_to_match as $group_photo_id_to_match) {
        $total_processed++;
        $match_result = matchGroupFaces($mysqli, $group_photo_id_to_match, $classroom_id);
        
        if (strpos($match_result, 'อัพโหลดสำเร็จ') !== false) {
            $total_detected++;
        } else if (strpos($match_result, 'Error') !== false) {
            $errors[] = "ID: {$group_photo_id_to_match} - {$match_result}";
        }
    }

    echo json_encode([
        'status' => 'success', 
        'message' => "ประมวลผลการ Match Event '{$latest_description}' ไป {$total_processed} รูป, ตรวจพบนักเรียน {$total_detected} รูป",
        'processed' => $total_processed,
        'detected' => $total_detected,
        'errors' => $errors
    ]);
    exit;
}

// ==========================================
// API 5: ดึงรายการอัลบั้มทั้งหมด
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch_albums') {
    header('Content-Type: application/json');
    
    // ตรวจสอบความถูกต้องของ classroom_id
    if (!$classroom_id) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ classroom_id']);
        exit;
    }

    $sql = "SELECT 
                MAX(cpag.group_photo_id) AS group_photo_id,
                cpag.description AS album_name,
                COUNT(cpag.group_photo_id) AS photo_count,
                (SELECT COUNT(DISTINCT cpgf.group_photo_id) 
                 FROM classroom_photo_group_faces cpgf 
                 INNER JOIN classroom_photo_album_group cpag2 
                 ON cpgf.group_photo_id = cpag2.group_photo_id
                 WHERE cpag2.description = cpag.description 
                 AND cpag2.classroom_id = ? 
                 AND cpag2.is_deleted = 0) AS extracted_count,
                (SELECT cpa.group_photo_path 
                 FROM classroom_photo_album_group cpa 
                 WHERE cpa.description = cpag.description 
                 AND cpa.classroom_id = ? 
                 AND cpa.is_deleted = 0 
                 ORDER BY cpa.group_photo_id DESC LIMIT 1) AS cover_photo_path
            FROM 
                `classroom_photo_album_group` cpag
            WHERE 
                `classroom_id` = ? AND `is_deleted` = 0
            GROUP BY 
                cpag.description
            ORDER BY 
                MAX(cpag.date_create) DESC"; // <-- แก้ไขตรงนี้

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("iii", $classroom_id, $classroom_id, $classroom_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $albums = [];
    while ($row = $result->fetch_assoc()) {
        $cover_url = $row['cover_photo_path'] ? (function_exists('GetUrl') ? GetUrl($row['cover_photo_path']) : $row['cover_photo_path']) : '';
        $is_extracted = $row['extracted_count'] > 0;
        
        $albums[] = [
            'album_id' => $row['group_photo_id'],
            'album_name' => htmlspecialchars($row['album_name']),
            'photo_count' => (int)$row['photo_count'],
            'extracted_count' => (int)$row['extracted_count'],
            'is_extracted' => $is_extracted,
            'cover_url' => $cover_url,
        ];
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'albums' => $albums]);
    exit;
}

// ==========================================
// API 6: ดึงรูปภาพในอัลบั้ม (ไม่มีการเปลี่ยนแปลง)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch_album_photos') {
    header('Content-Type: application/json');
    $album_name = isset($_POST['album_name']) ? trim($_POST['album_name']) : '';

    if (!$classroom_id || empty($album_name)) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ classroom_id หรือ album_name']);
        exit;
    }

    $sql = "SELECT 
                `group_photo_id`, 
                `group_photo_path`, 
                `date_create`,
                `description`
            FROM 
                `classroom_photo_album_group`
            WHERE 
                `classroom_id` = ? AND `description` = ? AND `is_deleted` = 0
            ORDER BY 
                `date_create` DESC";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $classroom_id, $album_name);
    $stmt->execute();
    $result = $stmt->get_result();

    $photos = [];
    while ($row = $result->fetch_assoc()) {
        $photo_url = $row['group_photo_path'] ? (function_exists('GetUrl') ? GetUrl($row['group_photo_path']) : $row['group_photo_path']) : '';
        $photos[] = [
            'id' => (int)$row['group_photo_id'],
            'url' => $photo_url,
            'date_create' => $row['date_create'],
        ];
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'photos' => $photos, 'album_name' => htmlspecialchars($album_name)]);
    exit;
}
// ==========================================
// API 7: ลบรูปภาพ
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
    header('Content-Type: application/json');
    $photo_id = isset($_POST['photo_id']) ? intval($_POST['photo_id']) : 0;

    if ($photo_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ Photo ID ที่ถูกต้อง']);
        exit;
    }

    $sql = "UPDATE `classroom_photo_album_group` SET `is_deleted` = 1 WHERE `group_photo_id` = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $photo_id);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => '✅ ลบรูปภาพออกจากอัลบั้มสำเร็จ']);
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ลบรูปภาพไม่สำเร็จ: ' . $mysqli->error]);
    }
    exit;
}

// ==========================================
// API 8: ลบอัลบั้ม
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_album') {
    header('Content-Type: application/json');
    $album_name = isset($_POST['album_name']) ? trim($_POST['album_name']) : '';

    if (!$classroom_id || empty($album_name)) {
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ไม่พบ classroom_id หรือ album_name']);
        exit;
    }

    $sql = "UPDATE `classroom_photo_album_group` SET `is_deleted` = 1 WHERE `classroom_id` = ? AND `description` = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("is", $classroom_id, $album_name);

    if ($stmt->execute()) {
        $rows_affected = $stmt->affected_rows;
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => "✅ ลบอัลบั้ม '{$album_name}' และรูปภาพ {$rows_affected} รูปสำเร็จ"]);
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => '❌ Error: ลบอัลบั้มไม่สำเร็จ: ' . $mysqli->error]);
    }
    exit;
}
?>

<!-- ==========================================
     HTML UI
     ========================================== -->



<div class="panel panel-default">
    <div class="panel-heading" style="background-color: #4CAF50; color: white;">
        <h4 class="panel-title"><i class="fas fa-upload fa-fw"></i> อัปโหลดรูปกลุ่ม</h4>
    </div>
    <div class="panel-body">
        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" enctype="multipart/form-data" id="photo-upload-form">
            
            <input type="hidden" name="classroom_id" id="form_classroom_id" value="<?php echo $classroom_id ? $classroom_id : ''; ?>">
            
            <div class="form-group">
                <label for="event_name_modal">
                    <i class="fas fa-tag fa-fw"></i> ชื่อ Event / คำอธิบาย:
                </label>
                <input type="text" class="form-control" name="event_name" id="event_name_modal" maxlength="255" placeholder="เช่น กิจกรรมวันปีใหม่ 2568" required>
            </div>
            
            <div class="form-group">
                <label for="group_photo_modal">
                    <i class="fas fa-images fa-fw"></i> เลือกรูปภาพกลุ่ม (300+ รูปได้):
                </label>
                <input type="file" class="form-control" name="group_photo[]" id="group_photo_modal" accept="image/jpeg, image/png" multiple required>
            </div>
            
            <button type="submit" class="btn btn-lg btn-block btn-success" id="upload-photo-btn">
                <i class="fas fa-cloud-upload-alt fa-fw"></i> อัปโหลดรูปกลุ่ม
            </button>
            
        </form>
        <div id="upload-message-area" style="margin-top: 15px;"></div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" style="background-color: #3f51b5; color: white;">
        <h4 class="panel-title"><i class="fas fa-folder fa-fw"></i> อัลบั้มรูปภาพใน Classroom</h4>
    </div>
    <div class="panel-body">
        <p class="text-muted">
            <i class="fas fa-info-circle"></i> อัลบั้มจะถูกสร้างจาก **ชื่อ Event / คำอธิบาย** ที่คุณใส่ตอนอัปโหลดรูป
        </p>
        <div id="album-list" class="row">
            <p class="text-center text-muted" id="loading-albums"><i class="fas fa-spinner fa-spin"></i> กำลังโหลดอัลบั้ม...</p>
        </div>
    </div>
</div>

<!-- Modal แสดงรูปภาพในอัลบั้ม -->
<div class="modal fade" id="albumPhotoModal" tabindex="-1" role="dialog" aria-labelledby="albumPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="albumPhotoModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-right mb-3">
                    <button type="button" class="btn btn-danger btn-sm" id="delete-album-btn"><i class="fas fa-trash-alt"></i> ลบอัลบั้มนี้</button>
                </div>
                <div id="photo-grid" class="row">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" style="background-color: #f7f7f7; color: #333;">
        <h4 class="panel-title"><i class="fas fa-magic fa-fw"></i> การจัดการ Face Embedding ล่วงหน้า</h4>
    </div>
    <div class="panel-body">
        <p class="text-muted">
            <i class="fas fa-info-circle"></i> 🚀 <strong>เวอร์ชัน Optimized</strong>: ประมวลผลแบบ Batch เร็วขึ้นหลายเท่า รองรับ 300+ รูปพร้อมกัน
        </p>
        <button type="button" class="btn btn-warning btn-block" id="generate-embedding-btn">
            <i class="fas fa-bolt fa-fw"></i> สร้าง Face Embedding สำหรับรูปโปรไฟล์ (Batch Processing)
        </button>
        <div id="embedding-message-area" style="margin-top: 15px;"></div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" style="background-color: #f7f7f7; color: #333;">
        <h4 class="panel-title"><i class="fas fa-binoculars fa-fw"></i> จับคู่รหัสใบหน้า (Match Faces)</h4>
    </div>
    <div class="panel-body">
        <p class="text-muted">
            <i class="fas fa-info-circle"></i> ตรวจจับและจับคู่นักเรียนในรูปกลุ่มที่ดึงรหัสใบหน้าไว้แล้ว
        </p>
        <button type="button" class="btn btn-primary btn-block" id="match-faces-btn">
            <i class="fas fa-eye fa-fw"></i> เริ่มการจับคู่ใบหน้า (Batch Matching)
        </button>
        <div id="match-message-area" style="margin-top: 15px;"></div>
    </div>
</div>



<script>
$(document).ready(function() {
    var iconUpload = '<i class="fas fa-cloud-upload-alt fa-fw"></i>';
    var iconLoading = '<i class="fas fa-spinner fa-spin fa-fw"></i>';
    var iconMatch = '<i class="fas fa-eye fa-fw"></i>';
    var iconExtract = '<i class="fas fa-cog fa-fw"></i>';
    var iconBolt = '<i class="fas fa-bolt fa-fw"></i>';
    var currentAlbumName = '';
    
    fetchAlbums();

    // ==========================================
    // ฟังก์ชัน: ดึงรายการอัลบั้ม
    // ==========================================
    function fetchAlbums() {
        var classroomId = $('#form_classroom_id').val();
        var albumList = $('#album-list');
        albumList.html('<p class="text-center text-muted" id="loading-albums"><i class="fas fa-spinner fa-spin"></i> กำลังโหลดอัลบั้ม...</p>');

        if (!classroomId) {
            albumList.html('<p class="text-center text-danger"><i class="fas fa-times-circle"></i> ไม่พบ Classroom ID</p>');
            return;
        }

        $.ajax({
            url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
            type: 'POST',
            data: { action: 'fetch_albums', classroom_id: classroomId },
            dataType: 'json',
            success: function(response) {
                albumList.empty();
                if (response.status === 'success' && response.albums.length > 0) {
                    response.albums.forEach(function(album) {
                        var coverUrl = album.cover_url || 'https://via.placeholder.com/300x200?text=No+Photo';
                        
                        // สร้าง Badge แสดงสถานะ
                        var statusBadge = album.is_extracted 
                            ? '<span class="badge badge-success" style="background-color: #28a745;"><i class="fas fa-check-circle"></i> ดึงรหัสแล้ว</span>'
                            : '<span class="badge badge-warning" style="background-color: #ffc107; color: #000;"><i class="fas fa-exclamation-triangle"></i> ยังไม่ดึงรหัส</span>';
                        
                        // สร้างปุ่ม Extract (แสดงเฉพาะอัลบั้มที่ยังไม่ได้ดึงรหัส)
                        var extractButton = !album.is_extracted 
                            ? `<button class="btn btn-warning btn-sm btn-block extract-album-btn" data-album-name="${album.album_name}" style="margin-top: 10px;">
                                   <i class="fas fa-cog fa-fw"></i> ดึงรหัสใบหน้า
                               </button>`
                            : '';
                        
                        var albumHtml = `
                            <div class="col-xs-12 col-sm-6 col-md-3" style="margin-bottom: 20px;">
                                <div class="card album-card-wrapper" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <div class="album-card" data-album-name="${album.album_name}" style="cursor: pointer;">
                                        <div class="img-container" style="height: 150px; overflow: hidden; position: relative;">
                                            <img src="${coverUrl}" alt="${album.album_name}" class="img-responsive" style="width: 100%; height: 100%; object-fit: cover;">
                                            <div style="position: absolute; top: 10px; right: 10px;">
                                                ${statusBadge}
                                            </div>
                                        </div>
                                        <div class="card-body" style="padding: 15px;">
                                            <h5 class="card-title" style="font-weight: bold; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <i class="fas fa-folder fa-fw text-primary"></i> ${album.album_name}
                                            </h5>
                                            <p class="card-text text-muted" style="font-size: 0.9em; margin-bottom: 5px;">
                                                <i class="fas fa-image fa-fw"></i> ${album.photo_count} รูป
                                            </p>
                                            <p class="card-text text-muted" style="font-size: 0.85em; margin-bottom: 0;">
                                                <i class="fas fa-check-circle fa-fw"></i> ดึงรหัสแล้ว: ${album.extracted_count}/${album.photo_count}
                                            </p>
                                        </div>
                                    </div>
                                    ${extractButton}
                                </div>
                            </div>
                        `;
                        albumList.append(albumHtml);
                    });
                } else {
                    albumList.html('<p class="text-center text-muted"><i class="fas fa-box-open"></i> ไม่พบอัลบั้มใน Classroom นี้</p>');
                }
            },
            error: function() {
                albumList.html('<p class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> เกิดข้อผิดพลาดในการดึงอัลบั้ม</p>');
            }
        });
    }

    // ==========================================
    // คลิกที่ Card เพื่อเปิด Modal แสดงรูปภาพ
    // ==========================================
    $(document).on('click', '.album-card', function() {
        currentAlbumName = $(this).data('album-name');
        $('#albumPhotoModalLabel').text(currentAlbumName);
        $('#photo-grid').html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> กำลังโหลดรูปภาพ...</p>');
        $('#albumPhotoModal').modal('show');
        
        fetchAlbumPhotos(currentAlbumName);
    });

    // ==========================================
    // ฟังก์ชันดึงรูปภาพในอัลบั้ม
    // ==========================================
    function fetchAlbumPhotos(albumName) {
        var photoGrid = $('#photo-grid');
        var classroomId = $('#form_classroom_id').val();

        $.ajax({
            url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
            type: 'POST',
            data: { action: 'fetch_album_photos', classroom_id: classroomId, album_name: albumName },
            dataType: 'json',
            success: function(response) {
                photoGrid.empty();
                if (response.status === 'success' && response.photos.length > 0) {
                    response.photos.forEach(function(photo) {
                        var photoHtml = `
                            <div class="col-xs-6 col-sm-4 col-md-3 photo-item" id="photo-${photo.id}" style="margin-bottom: 15px;">
                                <div style="position: relative; border: 1px solid #eee; border-radius: 4px; overflow: hidden;">
                                    <img src="${photo.url}" alt="Photo ${photo.id}" class="img-responsive" style="width: 100%; height: 150px; object-fit: cover;">
                                    <div style="position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.5); padding: 5px; border-bottom-left-radius: 4px;">
                                        <button class="btn btn-danger btn-xs delete-photo-btn" data-photo-id="${photo.id}" title="ลบรูปนี้">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted" style="display: block; margin-top: 5px; font-size: 0.8em;">${photo.date_create}</small>
                            </div>
                        `;
                        photoGrid.append(photoHtml);
                    });
                } else {
                    photoGrid.html('<p class="text-center text-muted"><i class="fas fa-camera"></i> ไม่มีรูปภาพในอัลบั้มนี้</p>');
                }
            },
            error: function() {
                photoGrid.html('<p class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> เกิดข้อผิดพลาดในการโหลดรูปภาพ</p>');
            }
        });
    }

    // ==========================================
    // ปุ่ม: ดึงรหัสใบหน้าจากอัลบั้ม
    // ==========================================
    $(document).on('click', '.extract-album-btn', function(e) {
        e.stopPropagation();
        var albumName = $(this).data('album-name');
        var btn = $(this);
        var classroomId = $('#form_classroom_id').val();
        
        if (confirm(`คุณต้องการดึงรหัสใบหน้าจากรูปทั้งหมดในอัลบั้ม "${albumName}" หรือไม่?`)) {
            btn.prop('disabled', true).html(iconLoading + ' กำลังดึงรหัสใบหน้า...');
            
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
                type: 'POST',
                data: { 
                    action: 'extract_album_faces', 
                    classroom_id: classroomId, 
                    album_name: albumName 
                },
                dataType: 'json',
                timeout: 300000, // 5 นาที
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        fetchAlbums(); // โหลดอัลบั้มใหม่เพื่ออัปเดตสถานะ
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + response.message);
                        btn.prop('disabled', false).html(iconExtract + ' ดึงรหัสใบหน้า');
                    }
                },
                error: function(xhr, status, error) {
                    alert('เกิดข้อผิดพลาดในการส่งคำขอ: ' + error);
                    btn.prop('disabled', false).html(iconExtract + ' ดึงรหัสใบหน้า');
                }
            });
        }
    });

    // ==========================================
    // ปุ่ม: สร้าง Embedding สำหรับรูปโปรไฟล์
    // ==========================================
    $('#generate-embedding-btn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var messageArea = $('#embedding-message-area');
        var classroomId = $('#form_classroom_id').val();
        
        if (!classroomId) {
            messageArea.html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ไม่พบ Classroom ID</div>');
            return;
        }
        
        if (confirm('คุณต้องการสร้าง Face Embedding สำหรับรูปโปรไฟล์ทั้งหมดหรือไม่?')) {
            btn.prop('disabled', true).html(iconLoading + ' กำลังประมวลผล...');
            messageArea.html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> กำลังสร้าง Embedding กรุณารอสักครู่...</div>');
            
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
                type: 'POST',
                data: { action: 'generate_embedding_batch', classroom_id: classroomId },
                dataType: 'json',
                timeout: 300000,
                success: function(response) {
                    if (response.status === 'success') {
                        var alertHtml = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + response.message + '</div>';
                        if (response.errors && response.errors.length > 0) {
                            alertHtml += '<div class="alert alert-warning" style="margin-top: 10px;"><strong>คำเตือน:</strong><ul><li>' + response.errors.join('</li><li>') + '</li></ul></div>';
                        }
                        messageArea.html(alertHtml);
                    } else {
                        messageArea.html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    messageArea.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> เกิดข้อผิดพลาด: ' + error + '</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).html(iconBolt + ' สร้าง Face Embedding สำหรับรูปโปรไฟล์ (Batch Processing)');
                }
            });
        }
    });

    // ==========================================
    // ฟอร์ม: อัปโหลดรูปกลุ่ม
    // ==========================================
    $('#photo-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(form[0]);
        var submitBtn = $('#upload-photo-btn');
        var messageArea = $('#upload-message-area');
        
        submitBtn.prop('disabled', true).html(iconLoading + ' กำลังอัปโหลด...');
        messageArea.empty();

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 300000,
            success: function(response) {
                if (response.status === 'success') {
                    messageArea.html('<div class="alert alert-success"><i class="fas fa-check-circle fa-fw"></i> ' + response.message + '</div>');
                    form[0].reset();
                    fetchAlbums(); // โหลดอัลบั้มใหม่
                } else {
                    var errorHtml = '<div class="alert alert-danger"><i class="fas fa-times-circle fa-fw"></i> ' + response.message + '</div>';
                    if (response.errors && response.errors.length > 0) {
                         errorHtml += '<div class="alert alert-warning" style="margin-top: 10px;"><strong>คำเตือน:</strong><ul><li>' + response.errors.join('</li><li>') + '</li></ul></div>';
                    }
                    messageArea.html(errorHtml);
                }
            },
            error: function(xhr, status, error) {
                messageArea.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle fa-fw"></i> เกิดข้อผิดพลาด: ' + error + '</div>');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(iconUpload + ' อัปโหลดรูปกลุ่ม');
            }
        });
    });

    // ==========================================
    // ปุ่ม: จับคู่ใบหน้า (Match Faces)
    // ==========================================
    $('#match-faces-btn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var messageArea = $('#match-message-area');
        var classroomId = $('#form_classroom_id').val();
        
        if (!classroomId) {
            messageArea.html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ไม่พบ Classroom ID</div>');
            return;
        }
        
        if (confirm('คุณต้องการเริ่มจับคู่ใบหน้าในรูปกลุ่มที่ดึงรหัสแล้วหรือไม่?')) {
            btn.prop('disabled', true).html(iconLoading + ' กำลังจับคู่ใบหน้า...');
            messageArea.html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล กรุณารอสักครู่...</div>');
            
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
                type: 'POST',
                data: { action: 'batch_match_faces', classroom_id: classroomId },
                dataType: 'json',
                timeout: 300000,
                success: function(response) {
                    if (response.status === 'success') {
                        var alertHtml = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + response.message + '</div>';
                        if (response.errors && response.errors.length > 0) {
                            alertHtml += '<div class="alert alert-warning" style="margin-top: 10px;"><strong>คำเตือน:</strong><ul><li>' + response.errors.join('</li><li>') + '</li></ul></div>';
                        }
                        messageArea.html(alertHtml);
                    } else {
                        messageArea.html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    messageArea.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> เกิดข้อผิดพลาด: ' + error + '</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).html(iconMatch + ' เริ่มการจับคู่ใบหน้า (Batch Matching)');
                }
            });
        }
    });

    // ==========================================
    // ลบรูปภาพเดี่ยว
    // ==========================================
    $(document).on('click', '.delete-photo-btn', function(e) {
        e.stopPropagation();
        var photoId = $(this).data('photo-id');
        
        if (confirm('คุณต้องการลบรูปภาพนี้ออกจากอัลบั้มหรือไม่?')) {
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
                type: 'POST',
                data: { action: 'delete_photo', photo_id: photoId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#photo-' + photoId).fadeOut(300, function() { 
                            $(this).remove();
                            fetchAlbums();
                            if ($('#photo-grid').children('.photo-item').length === 0) {
                                $('#photo-grid').html('<p class="text-center text-muted"><i class="fas fa-camera"></i> ไม่มีรูปภาพในอัลบั้มนี้</p>');
                            }
                        });
                        alert(response.message);
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการส่งคำขอลบรูปภาพ');
                }
            });
        }
    });

    // ==========================================
    // ลบอัลบั้มทั้งชุด
    // ==========================================
    $('#delete-album-btn').on('click', function() {
        var classroomId = $('#form_classroom_id').val();
        
        if (!currentAlbumName) {
            alert('ไม่พบชื่ออัลบั้มที่ต้องการลบ');
            return;
        }

        if (confirm(`คุณต้องการลบอัลบั้ม "${currentAlbumName}" และรูปภาพทั้งหมดในอัลบั้มนี้ใช่หรือไม่?`)) {
            $.ajax({
                url: '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>',
                type: 'POST',
                data: { action: 'delete_album', classroom_id: classroomId, album_name: currentAlbumName },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        $('#albumPhotoModal').modal('hide');
                        fetchAlbums();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการส่งคำขอลบอัลบั้ม');
                }
            });
        }
    });
});
</script>