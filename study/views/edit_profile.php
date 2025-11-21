<?php
// แก้ไขและตั้งค่า Timezone ให้เป็นเวลากรุงเทพฯ (Asia/Bangkok)
date_default_timezone_set('Asia/Bangkok');
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    // ตรวจสอบว่า /dashboard.php อยู่ที่ root หรือไม่
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}

define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);

// --- ส่วนที่เพิ่มเข้ามาตามโค้ดตัวอย่างที่ถูกต้อง ---
require_once $base_include . '/lib/connect_sqli.php';
require_once $base_include . '/lib/config.php'; // ต้องมีไฟล์นี้สำหรับฟังก์ชัน SaveFile/createThumbnail/Bucket

global $mysqli;

// ดึงค่า Bucket Master เหมือนโค้ดตัวอย่าง
$fsData = getBucketMaster();
$filesystem_user = $fsData['fs_access_user'];
$filesystem_pass = $fsData['fs_access_pass'];
$filesystem_host = $fsData['fs_host'];
$filesystem_path = $fsData['fs_access_path'];
$filesystem_type = $fsData['fs_type'];
$fs_id = $fsData['fs_id'];
setBucket($fsData);
// ----------------------------------------------------


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

/**
 * ฟังก์ชันสำหรับอัปโหลดไฟล์ตามรูปแบบที่ต้องการ (ใช้ SaveFile และ createThumbnail)
 *
 * @param array $file ตัวแปร $_FILES
 * @param string $name ชื่อ field ของไฟล์ในฟอร์ม
 * @param int|null $key Index ของไฟล์ในกรณีที่เป็น multiple file upload
 * @param string $currentFile Path ไฟล์เก่าที่ต้องการแทนที่ (ไม่ได้ใช้ในเคส 'add' แต่คงไว้ตามโครงสร้างเดิม)
 * @return string|null Path ใหม่ของไฟล์ที่อัปโหลดสำเร็จ หรือ null หากล้มเหลว
 */
function uploadFile($file, $name, $key, $currentFile = '', $target_sub_dir = 'classroom')
{
    global $base_path;
    
    // กำหนด target_dir สำหรับการสร้าง path (ตามโค้ดตัวอย่างที่ถูกต้อง)
    $target_dir = "uploads/" . $target_sub_dir . "/";
    
    // ตรวจสอบว่ามีการอัปโหลดไฟล์หรือไม่
    if (!isset($file[$name]['tmp_name']) || !isset($file[$name]['tmp_name'][$key]) || empty($file[$name]['tmp_name'][$key])) {
        // ในกรณีที่เป็น replace ถ้าไม่มีไฟล์ใหม่ให้อัปโหลด ให้คืนค่า path เดิม
        return extractPathFromUrl($currentFile); 
    }

    $tmp_name = $file[$name]['tmp_name'][$key];
    $file_name = $file[$name]['name'][$key];
    $file_error = $file[$name]['error'][$key];

    if ($tmp_name && $file_error == UPLOAD_ERR_OK) {
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_id = uniqid(); // ใช้ ID แทนชื่อไฟล์
        $new_file_name = $new_file_id . '.' . $file_extension;
        
        // 1. กำหนด Path สำหรับไฟล์จริงและไฟล์ Thumbnail 
        $new_file_path = $target_dir . $new_file_name;
        $thumb_file_path = $target_dir . $new_file_id . '_thumb.' . $file_extension;
        
        // **2. เริ่มกระบวนการ SaveFile และสร้าง Thumbnail**
        
        if (function_exists('SaveFile') && function_exists('createThumbnail')) {
            // A. Save ไฟล์ต้นฉบับ
            if (SaveFile($tmp_name, $new_file_path)) {
                
                // B. สร้าง Thumbnail ใน Temp Folder ก่อน
                $thumb_local = sys_get_temp_dir() . '/' . uniqid('thumb_') . '.' . $file_extension;
                
                if (createThumbnail($tmp_name, $thumb_local, 300, 300, 80)) {
                    // C. Save ไฟล์ Thumbnail
                    if (SaveFile($thumb_local, $thumb_file_path)) {
                        @unlink($thumb_local); // ลบไฟล์ temp
                        // D. คืนค่าเป็น Path ของไฟล์จริง
                        return cleanPath($new_file_path);
                    } else {
                        // ไม่สามารถ Save Thumbnail ได้
                        @unlink($thumb_local);
                        // ถ้าไฟล์จริงถูก Save แล้ว อาจจะคืนค่า Path ของไฟล์จริง
                        return cleanPath($new_file_path); 
                    }
                } else {
                    // ไม่สามารถสร้าง Thumbnail ได้
                    return null; 
                }
            } else {
                // ไม่สามารถ Save ไฟล์ต้นฉบับได้
                return null;
            }
        } else {
            // **ทางเลือกสำรอง:** หากไม่พบฟังก์ชัน SaveFile/createThumbnail (อาจจะต้องเอาออกหาก SaveFile เป็นส่วนสำคัญ)
            $target_file = $_SERVER['DOCUMENT_ROOT'] . $base_path . "/" . $new_file_path;
            
            if (!is_dir(dirname($target_file))) {
                @mkdir(dirname($target_file), 0755, true);
            }
            
            if (move_uploaded_file($tmp_name, $target_file)) {
                return cleanPath($new_file_path);
            } else {
                return null;
            }
        }
    }
    return extractPathFromUrl($currentFile);
}

if (!isset($_SESSION['student_id'])) {
    $student_id = 1; // ค่าเริ่มต้นถ้าไม่มี Session
} else {
    $student_id = $_SESSION['student_id'];
}

$sql_student = "
    SELECT 
        cs.*,
        cg.group_color
    FROM classroom_student cs
    LEFT JOIN classroom_student_join csj ON cs.student_id = csj.student_id
    LEFT JOIN classroom_group cg ON csj.group_id = cg.group_id
    WHERE cs.student_id = ?
";
$stmt = $mysqli->prepare($sql_student);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_student = $stmt->get_result();
$row_student = $result_student->fetch_assoc();
$stmt->close();

$sql_files = "
    SELECT file_id, file_path, file_status, file_order
    FROM classroom_file_student
    WHERE student_id = ? AND file_type = 'profile_image' AND is_deleted = 0
    ORDER BY file_status DESC, file_order ASC
";
$stmt_files = $mysqli->prepare($sql_files);
$stmt_files->bind_param("i", $student_id);
$stmt_files->execute();
$result_files = $stmt_files->get_result();
$student_images = $result_files->fetch_all(MYSQLI_ASSOC);
$stmt_files->close();

// ดึงรูปภาพบริษัท
$sql_company_files = "
    SELECT file_id, file_path
    FROM classroom_student_company_photo
    WHERE student_id = ? AND is_deleted = 0
";
$stmt_company_files = $mysqli->prepare($sql_company_files);
$stmt_company_files->bind_param("i", $student_id);
$stmt_company_files->execute();
$result_company_files = $stmt_company_files->get_result();
$company_images = $result_company_files->fetch_all(MYSQLI_ASSOC);
$stmt_company_files->close();

$_SESSION["user"] = $row_student["student_firstname_th"] . " " . $row_student["student_lastname_th"];
$_SESSION["emp_pic"] = isset($student_images[0]) ? $student_images[0]['file_path'] : null;

$profile_border_color = !empty($row_student['group_color']) ? htmlspecialchars($row_student['group_color']) : '#ff8c00';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    if (!isset($_SESSION['student_id'])) {
        $response['status'] = 'error';
        $response['message'] = 'Session expired. Please log in again.';
        echo json_encode($response);
        exit;
    }
    $student_id = (int) $_SESSION['student_id'];
    $response = ['status' => 'success', 'message' => 'บันทึกการเปลี่ยนแปลงโปรไฟล์สำเร็จ'];

    date_default_timezone_set('Asia/Bangkok');
    $current_datetime = date("Y-m-d H:i:s");

    if (isset($_POST['update_type']) && $_POST['update_type'] == 'text') {
        $bio = $_POST['bio'] ? $_POST['bio'] : '';
        $mobile = $_POST['mobile'] ? $_POST['mobile'] : '';
        $email = $_POST['email'] ? $_POST['email'] : '';
        $line = $_POST['line'] ? $_POST['line'] : '';
        $ig = $_POST['ig'] ? $_POST['ig'] : '';
        $facebook = $_POST['facebook'] ? $_POST['facebook'] : '';
        $hobby = $_POST['hobby'] ? $_POST['hobby'] : '';
        $student_music = $_POST['student_music'] ? $_POST['student_music'] : '';
        $student_drink = $_POST['student_drink'] ? $_POST['student_drink'] : '';
        $student_movie = $_POST['student_movie'] ? $_POST['student_movie'] : '';
        $goal = $_POST['goal'] ? $_POST['goal'] : '';
        $company = $_POST['company'] ? $_POST['company'] : '';
        $company_detail = $_POST['company_detail'] ? $_POST['company_detail'] : '';
        $company_url = $_POST['company_url'] ? $_POST['company_url'] : '';
        $position = $_POST['position'] ? $_POST['position'] : '';
        $emp_modify = $student_id;

        $sql_update = "UPDATE `classroom_student` SET 
            `student_bio` = ?, `student_mobile` = ?, `student_email` = ?, `student_line` = ?, `student_ig` = ?,
            `student_facebook` = ?, `student_hobby` = ?, `student_music` = ?, `student_drink` = ?,
            `student_movie` = ?, `student_goal` = ?, `student_company` = ?, `student_company_detail` = ?,
            `student_company_url` = ?, `student_position` = ?, `emp_modify` = ?, `date_modify` = ?
            WHERE `student_id` = ?";

        $stmt = $mysqli->prepare($sql_update);
        if ($stmt === false) {
            $response = ['status' => 'error', 'message' => 'Prepare failed: ' . $mysqli->error];
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("sssssssssssssssssi", $bio, $mobile, $email, $line, $ig, $facebook, $hobby, $student_music, $student_drink, $student_movie, $goal, $company, $company_detail, $company_url, $position, $emp_modify, $current_datetime, $student_id);
        if (!$stmt->execute()) {
            $response = ['status' => 'error', 'message' => 'อัปเดตข้อมูล Text ไม่สำเร็จ: ' . $stmt->error];
        }
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    if (isset($_POST['update_type']) && $_POST['update_type'] == 'file') {
        $file_action = $_POST['file_action'] ? $_POST['file_action'] : '';
        $file_id = isset($_POST['file_id']) && $_POST['file_id'] !== '' ? $_POST['file_id'] : null;
        $file_index = isset($_POST['file_index']) ? $_POST['file_index'] : null;
        $file_type = isset($_POST['file_type']) ? $_POST['file_type'] : 'profile_image';

        $response = ['status' => 'success', 'message' => 'ดำเนินการสำเร็จ'];

        switch ($file_action) {
            case 'add':
            case 'replace':
                $is_replace = $file_action == 'replace';
                $is_company_logo = $file_type == 'company_logo';
                
                if (isset($_FILES['file_upload'])) {
                    // **เรียกใช้ uploadFile ที่ถูกแก้ไขแล้ว**
                    // ในกรณี 'replace' ต้องหา file_path เก่ามาใส่ใน $currentFile เพื่อให้ฟังก์ชัน uploadFile จัดการลบไฟล์เก่าได้
                    $current_file_path = null;
                    if ($is_replace && $file_id) {
                        // ดึง file_path เดิมจาก DB ก่อนอัปโหลด
                        if ($file_type == 'profile_image') {
                            $sql_get_old = "SELECT file_path FROM classroom_file_student WHERE file_id = ?";
                        } elseif ($file_type == 'company_photo') {
                            $sql_get_old = "SELECT file_path FROM classroom_student_company_photo WHERE file_id = ?";
                        }
                        
                        if (isset($sql_get_old)) {
                            $stmt_get_old = $mysqli->prepare($sql_get_old);
                            $stmt_get_old->bind_param("i", $file_id);
                            $stmt_get_old->execute();
                            $result_old = $stmt_get_old->get_result()->fetch_assoc();
                            $current_file_path = $result_old['file_path'] ? $result_old['file_path'] : null;
                            $stmt_get_old->close();
                        }
                    } elseif ($is_company_logo) {
                         // สำหรับโลโก้บริษัท ต้องดึงจากตาราง classroom_student
                         $sql_get_logo = "SELECT student_company_logo FROM classroom_student WHERE student_id = ?";
                         $stmt_get_logo = $mysqli->prepare($sql_get_logo);
                         $stmt_get_logo->bind_param("i", $student_id);
                         $stmt_get_logo->execute();
                         $result_logo = $stmt_get_logo->get_result()->fetch_assoc();
                         $current_file_path = $result_logo['student_company_logo'] ? $result_logo['student_company_logo'] : null;
                         $stmt_get_logo->close();
                    }

                    $new_file_path = uploadFile($_FILES, 'file_upload', $file_index, $current_file_path, 'classroom');
                    
                    if ($new_file_path) {
                        $emp_modify = $student_id;
                        $emp_create = $student_id;

                        if ($is_company_logo) {
                            // 1. จัดการ student_company_logo (อัพโหลด/แทนที่)
                            $sql_update_logo = "UPDATE `classroom_student` SET `student_company_logo` = ?, `date_modify` = NOW(), `emp_modify` = ? WHERE `student_id` = ?";
                            $stmt_update_logo = $mysqli->prepare($sql_update_logo);
                            if ($stmt_update_logo === false) {
                                $response = ['status' => 'error', 'message' => 'Prepare update logo failed: ' . $mysqli->error];
                            } else {
                                $stmt_update_logo->bind_param("sii", $new_file_path, $emp_modify, $student_id);
                                if (!$stmt_update_logo->execute()) {
                                    $response = ['status' => 'error', 'message' => 'อัปเดตโลโก้ไม่สำเร็จ: ' . $stmt_update_logo->error];
                                }
                                $stmt_update_logo->close();
                            }
                        } elseif ($file_type == 'profile_image') {
                            // 2. จัดการ profile_image (เพิ่ม)
                            if ($file_action == 'add') {
                                $table_name = 'classroom_file_student';
                                $columns = '`student_id`, `file_path`, `file_type`, `file_status`, `file_order`, `date_create`, `emp_create`';
                                
                                $sql_count_active = "SELECT COUNT(*) AS total FROM classroom_file_student WHERE student_id = ? AND file_type = 'profile_image' AND is_deleted = 0";
                                $stmt_count = $mysqli->prepare($sql_count_active);
                                $stmt_count->bind_param("i", $student_id);
                                $stmt_count->execute();
                                $total_active = $stmt_count->get_result()->fetch_assoc()['total'];
                                $stmt_count->close();
                                
                                if ($total_active >= 4) {
                                    $response = ['status' => 'error', 'message' => 'คุณสามารถมีรูปโปรไฟล์ได้สูงสุด 4 รูป'];
                                    // ลบไฟล์ที่เพิ่งอัปโหลด (ต้องมีฟังก์ชัน DeleteFile หรือ unlink)
                                    // ในโค้ดตัวอย่างที่ถูกต้องมีการเรียกฟังก์ชัน DeleteFile
                                    // if (function_exists('DeleteFile')) { DeleteFile($new_file_path); }
                                    // else { 
                                        $full_path_to_delete = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/' . $new_file_path;
                                        if (file_exists($full_path_to_delete)) { @unlink($full_path_to_delete); } 
                                    // }
                                } else {
                                    $file_order = $total_active + 1;
                                    $file_status = ($total_active == 0) ? 1 : 0;
                                    $sql_insert = "INSERT INTO {$table_name} ({$columns}) VALUES (?, ?, 'profile_image', ?, ?, NOW(), ?)";
                                    $stmt_insert = $mysqli->prepare($sql_insert);
                                    $stmt_insert->bind_param("isiii", $student_id, $new_file_path, $file_status, $file_order, $emp_create);
                                    if (!$stmt_insert->execute()) {
                                        $response = ['status' => 'error', 'message' => 'เพิ่มรูปภาพไม่สำเร็จ: ' . $stmt_insert->error];
                                    } else {
                                        $response['file_id'] = $mysqli->insert_id;
                                    }
                                    $stmt_insert->close();
                                }
                            } elseif ($file_action == 'replace') {
                                // 2. จัดการ profile_image (แทนที่)
                                $table_name = 'classroom_file_student';
                                $sql_update_path = "UPDATE {$table_name} SET file_path = ?, date_modify = NOW(), emp_modify = ? WHERE file_id = ?";
                                $stmt_update_path = $mysqli->prepare($sql_update_path);
                                $stmt_update_path->bind_param("sii", $new_file_path, $emp_modify, $file_id);
                                if (!$stmt_update_path->execute()) {
                                    $response = ['status' => 'error', 'message' => 'เปลี่ยนรูปภาพไม่สำเร็จ: ' . $stmt_update_path->error];
                                }
                                $stmt_update_path->close();
                            }
                        } elseif ($file_type == 'company_photo') {
                            // 3. จัดการรูปภาพบริษัท (เพิ่ม/แทนที่)
                            $table_name = 'classroom_student_company_photo';
                            if ($file_action == 'add') {
                                $columns = '`student_id`, `file_path`, `date_create`, `emp_create`';
                                $sql_insert = "INSERT INTO {$table_name} ({$columns}) VALUES (?, ?, NOW(), ?)";
                                $stmt_insert = $mysqli->prepare($sql_insert);
                                $stmt_insert->bind_param("isi", $student_id, $new_file_path, $emp_create);
                                if (!$stmt_insert->execute()) {
                                    $response = ['status' => 'error', 'message' => 'เพิ่มรูปภาพไม่สำเร็จ: ' . $stmt_insert->error];
                                } else {
                                    $response['file_id'] = $mysqli->insert_id;
                                }
                                $stmt_insert->close();
                            } elseif ($file_action == 'replace') {
                                $sql_update_path = "UPDATE {$table_name} SET file_path = ?, date_modify = NOW(), emp_modify = ? WHERE file_id = ?";
                                $stmt_update_path = $mysqli->prepare($sql_update_path);
                                $stmt_update_path->bind_param("sii", $new_file_path, $emp_modify, $file_id);
                                if (!$stmt_update_path->execute()) {
                                    $response = ['status' => 'error', 'message' => 'เปลี่ยนรูปภาพไม่สำเร็จ: ' . $stmt_update_path->error];
                                }
                                $stmt_update_path->close();
                            }
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ'];
                    }
                }
                echo json_encode($response);
                exit;
                break;
            
            case 'delete':
                if ($file_type == 'company_logo') {
                    // 1. ลบโลโก้บริษัท (ตั้งค่าเป็น NULL)
                    $emp_modify = $student_id;
                    $sql_delete_logo = "UPDATE `classroom_student` SET `student_company_logo` = NULL, `date_modify` = NOW(), `emp_modify` = ? WHERE `student_id` = ?";
                    $stmt_delete_logo = $mysqli->prepare($sql_delete_logo);
                    $stmt_delete_logo->bind_param("ii", $emp_modify, $student_id);
                    if (!$stmt_delete_logo->execute()) {
                        $response = ['status' => 'error', 'message' => 'ลบโลโก้ไม่สำเร็จ: ' . $stmt_delete_logo->error];
                    } else {
                        $response['message'] = 'ลบโลโก้บริษัทสำเร็จ';
                        // *ควรเพิ่มโค้ดสำหรับลบไฟล์ออกจาก Server/Bucket ด้วย*
                    }
                    $stmt_delete_logo->close();

                } elseif ($file_id) {
                    // 2. ลบรูปโปรไฟล์/รูปภาพบริษัท (ตั้งค่า is_deleted = 1)
                    $emp_modify = $student_id;
                    $table_name = ($file_type == 'company_photo') ? 'classroom_student_company_photo' : 'classroom_file_student';
                    
                    // ใช้ UPDATE เพื่อตั้งค่า is_deleted = 1
                    $sql_delete = "UPDATE {$table_name} SET is_deleted = 1, date_modify = NOW(), emp_modify = ? WHERE file_id = ?";
                    $stmt_delete = $mysqli->prepare($sql_delete);
                    $stmt_delete->bind_param("ii", $emp_modify, $file_id);
                    
                    if (!$stmt_delete->execute()) {
                        $response = ['status' => 'error', 'message' => 'ลบรูปภาพไม่สำเร็จ: ' . $stmt_delete->error];
                    } else {
                        $response['message'] = 'ลบรูปภาพสำเร็จ';
                        // *ควรเพิ่มโค้ดสำหรับลบไฟล์ออกจาก Server/Bucket ด้วย*
                    }
                    $stmt_delete->close();
                    
                    if ($file_type == 'profile_image') {
                        // Reorder profile images
                        // ... (โค้ด reorder เดิม)
                    }

                } else {
                    $response = ['status' => 'error', 'message' => 'ไม่พบข้อมูลรูปภาพที่ต้องการลบ'];
                }
                echo json_encode($response);
                exit;
                break;
            
            case 'set_main':
                // ... (โค้ด set_main เดิมสำหรับ profile_image)
                if ($file_id) {
                    $mysqli->begin_transaction();
                    try {
                        $sql_reset_main = "UPDATE classroom_file_student SET file_status = 0, file_order = 0, date_modify = NOW(), emp_modify = ? WHERE student_id = ? AND file_type = 'profile_image' AND is_deleted = 0";
                        $stmt_reset = $mysqli->prepare($sql_reset_main);
                        $stmt_reset->bind_param("ii", $student_id, $student_id);
                        $stmt_reset->execute();
                        $stmt_reset->close();

                        $sql_set_main = "UPDATE classroom_file_student SET file_status = 1, file_order = 1, date_modify = NOW(), emp_modify = ? WHERE file_id = ?";
                        $stmt_set_main = $mysqli->prepare($sql_set_main);
                        $emp_modify = $student_id;
                        $stmt_set_main->bind_param("ii", $emp_modify, $file_id);
                        $stmt_set_main->execute();
                        $stmt_set_main->close();

                        $sql_reorder = "SELECT file_id FROM classroom_file_student WHERE student_id = ? AND file_type = 'profile_image' AND is_deleted = 0 AND file_id != ? ORDER BY date_create ASC";
                        $stmt_reorder_select = $mysqli->prepare($sql_reorder);
                        $stmt_reorder_select->bind_param("ii", $student_id, $file_id);
                        $stmt_reorder_select->execute();
                        $result_reorder = $stmt_reorder_select->get_result();
                        $order_counter = 2;
                        while ($row = $result_reorder->fetch_assoc()) {
                            $sql_update_order = "UPDATE classroom_file_student SET file_order = ?, date_modify = NOW(), emp_modify = ? WHERE file_id = ?";
                            $stmt_update_order = $mysqli->prepare($sql_update_order);
                            $stmt_update_order->bind_param("iii", $order_counter, $student_id, $row['file_id']);
                            $stmt_update_order->execute();
                            $stmt_update_order->close();
                            $order_counter++;
                        }
                        $stmt_reorder_select->close();

                        $mysqli->commit();
                        $response = ['status' => 'success', 'message' => 'ตั้งค่ารูปหลักสำเร็จ'];
                    } catch (mysqli_sql_exception $e) {
                        $mysqli->rollback();
                        $response = ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการตั้งค่ารูปหลัก: ' . $e->getMessage()];
                    }
                }
                echo json_encode($response);
                exit;
                break;
        }
    }
    $mysqli->close();
    exit;
}

function thaidate($value)
{
    return empty($value) ? "" : substr($value, 8, 2) . "/" . substr($value, 5, 2) . "/" . substr($value, 0, 4);
}
function find_birth($birthday, $today)
{
    list($byear, $bmonth, $bday) = explode("-", $birthday);
    list($tyear, $tmonth, $tday) = explode("-", $today);
    $u_y = date("Y", mktime(0, 0, 0, $tmonth, $tday, $tyear) - mktime(0, 0, 0, $bmonth, $bday, $byear)) - 1970;
    $u_m = date("m", mktime(0, 0, 0, $tmonth, $tday, $tyear) - mktime(0, 0, 0, $bmonth, $bday, $byear)) - 1;
    $u_d = date("d", mktime(0, 0, 0, $tmonth, $tday, $tyear) - mktime(0, 0, 0, $bmonth, $bday, $byear)) - 1;
    return "$u_y ปี $u_m เดือน $u_d วัน";
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Setting • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/setting.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8"
    
        type="text/javascript"></script>

    <style>
        .profile-image-gallery {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .profile-image-item,
        .profile-image-placeholder,
        .image-preview-container {
            /* รวม selector เพื่อกำหนดขนาดเดียวกัน */
            position: relative;
            width: 150px;
            height: 150px;
            cursor: pointer;
            border-radius: 50%;
            /* เพื่อให้ทุกองค์ประกอบเป็นวงกลม */
        }

        /* .profile-image-item {
            position: relative;
            width: 150px;
            height: 150px;
            cursor: pointer;
        } */
        .profile-image-item img {
            width: 100%;
            /* ใช้ 100% เพื่อให้ปรับขนาดตาม container */
            height: 150px;
            /* ใช้ 100% เพื่อให้ปรับขนาดตาม container */
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #ddd;
            transition: all 0.3s ease;
        }

        .profile-image-item.is-main img {
            border-color:
                <?= $profile_border_color; ?>
            ;
            box-shadow: 0 0 10px rgba(255, 140, 0, 0.5);
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-overlay1 {
            position: absolute;
            top: 25%;
            left: 25%;
            width: 50%;
            height: 50%;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-image-item:hover .image-overlay {
            opacity: 1;
        }

        .company-image-item:hover .image-overlay1 {
            opacity: 1;
        }

        .company-logo-item:hover .image-overlay1 {
            opacity: 1;
        }

        .company-image-item:hover .image-overlay2 {
            opacity: 1;
        }

        .company-logo-item:hover .image-overlay2 {
            opacity: 1;
        }
        

        .overlay-actions {
            display: flex;
            gap: 10px;
        }

        .overlay-btn {
            background: #fff;
            color: #333;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2em;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }
        /* บังคับสีให้เป็นสีเดียวกับปุ่มเพื่อความแน่ใจ */
        .overlay-btn i { 
            color: #333 !important; /* ตรวจสอบว่าไอคอนมีสีตามที่ต้องการ */
        }
        .overlay-btn:hover {
            transform: scale(1.1);
        }

        .profile-image-placeholder {
            /* width: 100%; */
            /* ใช้ 100% เพื่อให้ปรับขนาดตาม container */
            /* height: 100%; */
            /* ใช้ 100% เพื่อให้ปรับขนาดตาม container */
            border: 4px dashed #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #aaa;
            font-size: 3em;
            transition: border-color 0.3s ease;
            cursor: pointer;
        }

        .profile-image-placeholder:hover {
            border-color: #ff8c00;
            color: #ff8c00;
        }

        /* New styles for mobile responsiveness */
        @media (max-width: 768px) {
            .profile-image-gallery {
                flex-direction: column;
                /* เปลี่ยน layout เป็นแนวตั้ง */
                gap: 20px;
            }

            .profile-image-item,
            .profile-image-placeholder,
            .image-preview-container {
                width: 120px;
                /* ลดขนาดวงกลมให้เล็กลง */
                height: 120px;
            }

            .overlay-btn {
                width: 35px;
                /* ลดขนาดปุ่มเครื่องมือ */
                height: 35px;
                font-size: 1em;
                /* ลดขนาด icon */
            }

            .profile-image-item img {
                width: 100%;
                /* ใช้ 100% เพื่อให้ปรับขนาดตาม container */
                height: 120px;
                /* ใช้ 100% เพื่อให้ปรับขนาดตาม container */
                object-fit: cover;
                border-radius: 50%;
                border: 4px solid #ddd;
                transition: all 0.3s ease;
            }
           .image-overlay2 {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

        }

        /* .image-preview-container {
            position: relative;
            width: 150px;
            height: 150px;
        } */
        .image-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #ddd;
        }

        .image-preview-container .preview-action-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: #ff6600;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 1.2em;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* The rest of your styles from the original code */
        .main-container {
            max-width: 960px;
            /* margin: 0 auto;*/
            padding: 0 20px; 
        }

        .section-header-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            color: #ff9900;
        }

        .section-header-icon i {
            font-size: 2em;
            color: #ff6600;
            margin-right: 15px;
        }

        .section-title {
            font-weight: 700;
            color: #333;
            margin: 0;
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
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.4);
            transition: all 0.3s ease;
            display: block;
            margin: 40px auto;
        }

        .btn-save-changes:hover {
            background-color: #e55c00;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.5);
        }

        .edit-profile-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 0 5px rgba(255, 140, 0, 0.3);
        }

        .profile-img-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #ff8c00;
            object-fit: cover;
            display: block;
            margin: 0 auto 20px;
        }
           /* Modern Upload Zone Styles */
    .upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        background: #f9fafb;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .upload-zone:hover {
        border-color: #ff8800;
        background: #fff5eb;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 136, 0, 0.1);
    }

    .upload-zone.dragover {
        border-color: #ff8800;
        background: #fff5eb;
        border-style: solid;
    }

    .upload-zone .upload-icon {
        font-size: 48px;
        color: #ff8800;
        margin-bottom: 16px;
    }

    .upload-zone .upload-text {
        font-size: 16px;
        color: #374151;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .upload-zone .upload-hint {
        font-size: 14px;
        color: #9ca3af;
    }

    .upload-zone input[type="file"] {
        display: none;
    }

    /* Image Preview with Overlay */
    .image-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }

    .image-wrapper img {
        width: 100%;
        height: 200px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .image-wrapper:hover img {
        transform: scale(1.05);
    }

    /* Image Preview with Overlay */
    .image-wrapperprofile {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }

    .image-wrapperprofile img {
        width: 100%;
        /* height: 200px; */
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .image-wrapperprofile:hover img {
        transform: scale(1.05);
    }

    .image-overlay1 {
        position: absolute;
        top: 50px;
        left: 35%;
        /* right: 50%; */
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .image-overlay2 {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 2em;
    border-radius: 50%;
}

    .image-wrapper:hover .image-overlay1 {
        opacity: 1;
    }

    .image-wrapper:hover .image-overlay2 {
        opacity: 1;
    }


    .overlay-actions {
        display: flex;
        gap: 10px;
    }

    .overlay-btn {
        background: white;
        color: #374151;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 16px;
    }

    .overlay-btn:hover {
        background: #ff8800;
        color: white;
        transform: scale(1.1);
    }

    .btn-save-changes {
        background: linear-gradient(135deg, #ff8800 0%, #ff6600 100%);
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(255, 136, 0, 0.3);
    }

    .btn-save-changes:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 136, 0, 0.4);
    }
    </style>
    <title>Profile • ORIGAMI SYSTEM</title>
</head>

<body>
    <?php require_once("component/header.php") ?>

    <div class="main-content" style="padding-inline: 20px;">
    <div class="tab-content">
        <div class="edit-profile-card">
            <div class="section-header-icon">
                <i class="fas fa-edit" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">แก้ไขข้อมูลโปรไฟล์</h3>
            </div>
            <hr>
            <form id="editProfileForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div class="profile-image-gallery" id="imageGallery">
                            <?php
                            $img_count = count($student_images);

                            // วนลูปเพื่อแสดงรูปภาพที่มีอยู่แล้ว
                            foreach ($student_images as $index => $image) {
                                // *นี่คือส่วนที่ถูกต้องอยู่แล้ว*
                                $file_url = GetUrl($image['file_path']);
                                $is_main = $image['file_status'] == 1;
                                ?>
                                <div class="profile-image-item"
                                    data-file-id="<?= htmlspecialchars($image['file_id']); ?>"
                                    data-file-path="<?= htmlspecialchars($image['file_path']); ?>"
                                    data-file-index="<?= $index; ?>">
                                    <div class="image-wrapperprofile">
                                        <img src="<?= $file_url; ?>" onerror="this.src='/images/default.png'"
                                            alt="Profile Image <?= $index + 1; ?>"
                                            class="profile-image <?= $is_main ? 'is-main' : ''; ?>">
                                    </div>
                                    <div class="image-overlay">
                                        <div class="overlay-actions">
                                            <button type="button" class="overlay-btn btn-delete-image" title="ลบรูปภาพ">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <label for="replace-file-<?= $index; ?>" class="overlay-btn"
                                                title="เปลี่ยนรูปภาพ">
                                                <i class="fas fa-exchange-alt"></i>
                                            </label>
                                            <?php if (!$is_main) { ?>
                                                <button type="button" class="overlay-btn btn-set-main"
                                                    title="ตั้งเป็นรูปหลัก">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <input type="file" id="replace-file-<?= $index; ?>" class="file-input-handler"
                                        style="display: none;" accept="image/*">
                                </div>
                                <?php
                            }

                            // ตรวจสอบว่าจำนวนรูปภาพที่มีอยู่ไม่เกิน 4 รูป
                            if ($img_count < 4) {
                                ?>
                                <div class="profile-image-item profile-image-placeholder">
                                    <label for="add-file" style="cursor: pointer;">
                                        <i class="fas fa-plus"></i>
                                    </label>
                                    <input type="file" id="add-file" class="file-input-handler" style="display: none;"
                                        accept="image/*">
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <small class="text-muted">คุณสามารถอัปโหลดรูปโปรไฟล์ได้สูงสุด 4 รูป</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstname">ชื่อ</label>
                            <input type="text" id="firstname" name="firstname" class="form-control-edit"
                                value="<?= $row_student["student_firstname_th"]; ?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lastname">นามสกุล</label>
                            <input type="text" id="lastname" name="lastname" class="form-control-edit"
                                value="<?= $row_student["student_lastname_th"]; ?>" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea name="bio" id="bio" class="form-control-edit"
                                rows="3"><?= $row_student["student_bio"]; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="section-header-icon">
                    <i class="fas fa-address-book" style="font-size: 25px; "></i>
                    <h3 style="padding-left:10px;" class="section-title">ช่องทางการติดต่อ</h3>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mobile">เบอร์โทรศัพท์</label>
                            <input type="text" name="mobile" id="mobile" class="form-control-edit"
                                value="<?= $row_student['student_mobile']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">อีเมล</label>
                            <input type="email" name="email" id="email" class="form-control-edit"
                                value="<?= $row_student['student_email']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="line">Line</label>
                            <input type="text" name="line" id="line" class="form-control-edit"
                                value="<?= $row_student['student_line']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" name="instagram" id="instagram" class="form-control-edit"
                                value="<?= $row_student['student_ig']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="facebook">Facebook</label>
                            <input type="text" name="facebook" id="facebook" class="form-control-edit"
                                value="<?= $row_student['student_facebook']; ?>">
                        </div>
                    </div>
                </div>
                <div class="section-header-icon" style="font-size: 25px; ">
                    <i class="fas fa-heartbeat"></i>
                    <h3 class="section-title" style="padding-left:10px;">ไลฟ์สไตล์</h3>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hobby">งานอดิเรก</label>
                            <input type="text" name="hobby" id="hobby" class="form-control-edit"
                                value="<?= $row_student["student_hobby"]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="student_music">ดนตรีที่ชอบ</label>
                            <input type="text" name="student_music" id="student_music" class="form-control-edit"
                                value="<?= $row_student["student_music"]; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="student_drink">เครื่องดื่มที่ชื่นชอบ</label>
                            <input type="text" name="student_drink" id="student_drink" class="form-control-edit"
                                value="<?= $row_student["student_drink"]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="student_movie">หนังที่ชอบ</label>
                            <input type="text" name="student_movie" id="student_movie" class="form-control-edit"
                                value="<?= $row_student["student_movie"]; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="goal">เป้าหมาย</label>
                            <input type="text" name="goal" id="goal" class="form-control-edit"
                                value="<?= $row_student["student_goal"]; ?>">
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="section-header-icon" style="font-size: 25px; ">
                    <i class="fas fa-building"></i>
                    <h3 class="section-title" style="padding-left:10px;">บริษัท</h3>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company">ชื่อบริษัท</label>
                            <input type="text" name="company" id="company" class="form-control-edit"
                                value="<?= $row_student["student_company"]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="position">ตำแหน่งงาน</label>
                            <input type="text" name="position" id="position" class="form-control-edit"
                                value="<?= $row_student["student_position"]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_url">URL บริษัท</label>
                            <input type="url" name="company_url" id="company_url" class="form-control-edit"
                                value="<?= $row_student["student_company_url"]; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company_detail">รายละเอียดบริษัท</label>
                            <textarea name="company_detail" id="company_detail" class="form-control-edit"
                                rows="3"><?= $row_student["student_company_detail"]; ?></textarea>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
<h5 class="card-title">โลโก้บริษัท 🖼️</h5>
<div class="row" id="company-logo-container">
    <div class="col-md-12 mb-4 company-logo-item" data-file-id="0" data-file-index="logo">
        <div class="image-wrapper">
            <?php if (!empty($row_student["student_company_logo"])): ?>
                <img src="<?= GetUrl($row_student["student_company_logo"]); ?>"
                    alt="Company Logo" class="company-logo img-thumbnail">
                <div class="image-overlay2">
                    <div class="overlay-actions">
                        <button type="button" class="overlay-btn btn-delete-image-logo" title="ลบโลโก้">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <label for="replace-company-logo" class="overlay-btn" title="เปลี่ยนโลโก้">
                            <i class="fas fa-exchange-alt"></i>
                        </label>
                    </div>
                </div>
                <input type="file" id="replace-company-logo" class="file-input-handler"
                    data-file-type="company_logo" accept="image/*">
            <?php else: ?>
                <div class="upload-zone" onclick="document.getElementById('add-company-logo').click()">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">คลิกเพื่อเพิ่มโลโก้บริษัท</div>
                    <div class="upload-hint">หรือลากไฟล์มาวางที่นี่</div>
                    <input type="file" class="file-input-handler" data-file-type="company_logo"
                        id="add-company-logo" accept="image/*">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<hr class="my-4">
<h5 class="card-title">รูปภาพบริษัท 📸</h5>
<div class="row" id="company-photos-container">
    <?php if (!empty($company_images)): ?>
        <?php foreach ($company_images as $index => $image): ?>
            <div class="col-md-3 mb-4 company-image-item" data-file-id="<?= $image['file_id']; ?>"
                data-file-index="<?= $index; ?>">
                <div class="image-wrapper">
                    <img src="<?= GetUrl($image['file_path']); ?>"
                        alt="Company Photo" class="company-image img-thumbnail">
                    <div class="image-overlay2">
                        <div class="overlay-actions">
                            <button type="button" class="overlay-btn btn-delete-image-company" title="ลบรูปภาพ">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="col-md-3 mb-4 company-image-item" data-file-index="<?= count($company_images); ?>">
        <div class="upload-zone" onclick="document.getElementById('add-company-file').click()">
            <div class="upload-icon">
                <i class="fas fa-images"></i>
            </div>
            <div class="upload-text">เพิ่มรูปภาพ</div>
            <div class="upload-hint">คลิกหรือลากไฟล์</div>
            <input type="file" class="file-input-handler" data-file-type="company_photo"
                id="add-company-file" accept="image/*">
        </div>
    </div>
</div>

<div class="text-center">
    <button type="button" name="submit_edit_profile" class="btn-save-changes"
        id="saveBtn">บันทึกการเปลี่ยนแปลง</button>
</div>
            </form>
        </div>
    </div>
</div>
    <?php require_once("component/footer.php") ?>

    <script type="text/javascript">
    $(document).ready(function () {
        // Drag and Drop functionality
        $('.upload-zone').on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $('.upload-zone').on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $('.upload-zone').on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const input = $(this).find('input[type="file"]')[0];
                input.files = files;
                $(input).trigger('change');
            }
        });

        // Function to handle the file upload and preview
        function handleFileSelect(event) {
            const fileInput = event.target;
            const file = fileInput.files[0];
            if (!file) { return; }

            const parentItem = $(fileInput).closest('.profile-image-item, .company-image-item, .company-logo-item');
            const fileId = parentItem.data('file-id') || '';
            const fileIndex = parentItem.data('file-index');
            const fileType = $(fileInput).data('file-type') || 'profile_image';
            
            const isCompanyLogo = fileType === 'company_logo';
            const fileAction = isCompanyLogo ? 'replace' : (fileId ? 'replace' : 'add'); 

            const fileFormData = new FormData();
            fileFormData.append('update_type', 'file');
            fileFormData.append('file_action', fileAction);
            fileFormData.append('file_type', fileType);
            
            if (fileAction === 'replace' && fileType !== 'company_logo') {
                fileFormData.append('file_id', fileId);
            }
            fileFormData.append('file_index', fileIndex);
            fileFormData.append(`file_upload[${fileIndex}]`, file);

            $.ajax({
                url: window.location.href,
                type: "POST",
                data: fileFormData,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function (response) {
                    if (response.status === 'success') {
                        if (fileAction === 'add' && response.file_id) {
                            parentItem.data('file-id', response.file_id);
                        }
                        swal({ title: "อัปโหลดสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
                    } else {
                        swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown);
                    swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์เพื่ออัปโหลดไฟล์ได้", type: "error" });
                }
            });

            // Real-time Preview
            const reader = new FileReader();
            reader.onload = function (e) {
                const isCompanyPhoto = fileType === 'company_photo';
                const isProfileImage = fileType === 'profile_image';
                const imageClass = isCompanyPhoto ? 'company-image' : (isCompanyLogo ? 'company-logo' : 'profile-image');

                if (fileAction === 'add') {
                    const isCompany = isCompanyPhoto;
                    const newId = new Date().getTime();
                    const newItem = $('<div>').addClass('col-md-3 mb-4 ' + (isCompany ? 'company-image-item' : 'profile-image-item')).attr('data-file-index', newId).attr('data-file-id', newId);

                    const imageWrapper = $('<div>').addClass('image-wrapper');
                    const newImage = $('<img>').attr({
                        src: e.target.result,
                        alt: 'Preview Image',
                        class: imageClass + ' img-thumbnail'
                    });
                    imageWrapper.append(newImage);

                    const newOverlay = `
                        <div class="image-overlay1">
                            <div class="overlay-actions">
                                <button type="button" class="overlay-btn btn-delete-image${isCompany ? '-company' : ''}" title="ลบรูปภาพ">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <label for="replace-file-${newId}" class="overlay-btn" title="เปลี่ยนรูปภาพ">
                                    <i class="fas fa-exchange-alt"></i>
                                </label>
                                <input type="file" id="replace-file-${newId}" class="file-input-handler" data-file-type="${fileType}" accept="image/*">
                                ${isProfileImage ? `
                                <button type="button" class="overlay-btn btn-set-main" title="ตั้งเป็นรูปหลัก">
                                    <i class="fas fa-star"></i>
                                </button>` : ''}
                            </div>
                        </div>`;
                    newItem.append(imageWrapper).append(newOverlay);

                    const placeholder = parentItem.find('.upload-zone').closest('.col-md-3');
                    newItem.insertBefore(placeholder);
                } else if (isCompanyLogo) {
                    const hasLogoPlaceholder = parentItem.find('.upload-zone').length > 0;
                    if (hasLogoPlaceholder) {
                        const newLogoHtml = `
                            <img src="${e.target.result}" alt="Company Logo" class="company-logo img-thumbnail">
                            <div class="image-overlay1">
                                <div class="overlay-actions">
                                    <button type="button" class="overlay-btn btn-delete-image-logo" title="ลบโลโก้">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <label for="replace-company-logo" class="overlay-btn" title="เปลี่ยนโลโก้">
                                        <i class="fas fa-exchange-alt"></i>
                                    </label>
                                </div>
                            </div>
                            <input type="file" id="replace-company-logo" class="file-input-handler" data-file-type="company_logo" accept="image/*">
                        `;
                        parentItem.find('.image-wrapper').html(newLogoHtml);
                    } else {
                        parentItem.find('.company-logo').attr('src', e.target.result).show();
                    }
                } else if (fileAction === 'replace') {
                    parentItem.find('.' + imageClass).attr('src', e.target.result).show();
                }
            };
            reader.readAsDataURL(file);
        }

        $(document).on('change', '.file-input-handler', handleFileSelect);

        // Handle Save Action
        $("#saveBtn").on("click", function (e) {
            e.preventDefault();
            const formData = new FormData($("#editProfileForm")[0]);
            formData.append('update_type', 'text');

            $.ajax({
                url: window.location.href,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function (response) {
                    if (response.status === 'success') {
                        swal({ title: "บันทึกสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
                    } else {
                        swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error:", textStatus, errorThrown);
                    swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้", type: "error" });
                }
            });
        });

        // Delete handlers
        $(document).on('click', '.btn-delete-image', function () {
            const parentItem = $(this).closest('.profile-image-item');
            const fileId = parentItem.data('file-id');
            deleteFile(fileId, 'profile_image');
        });

        $(document).on('click', '.btn-delete-image-company', function () {
            const parentItem = $(this).closest('.company-image-item');
            const fileId = parentItem.data('file-id');
            deleteFile(fileId, 'company_photo');
        });

        $(document).on('click', '.btn-delete-image-logo', function () {
            deleteFile(null, 'company_logo');
        });

        function deleteFile(fileId, fileType) {
            if (!fileId && fileType !== 'company_logo') {
                swal({ title: "ข้อผิดพลาด", text: "ไม่พบข้อมูลรูปภาพที่ต้องการลบ", type: "error" });
                return;
            }
            
            const deleteText = (fileType === 'company_logo') ? "โลโก้บริษัทจะถูกลบออก" : "รูปภาพจะถูกลบออกจากรายการ";
            const confirmTitle = (fileType === 'company_logo') ? "ยืนยันการลบโลโก้บริษัท?" : "ยืนยันการลบรูปภาพ?";

            swal({
                title: confirmTitle,
                text: deleteText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "ใช่, ลบเลย",
                cancelButtonText: "ยกเลิก",
                closeOnConfirm: false
            }, function () {
                const formData = new FormData();
                formData.append('update_type', 'file');
                formData.append('file_action', 'delete');
                formData.append('file_type', fileType);
                if (fileId) {
                    formData.append('file_id', fileId);
                }

                $.ajax({
                    url: window.location.href,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'JSON',
                    success: function (response) {
                        if (response.status === 'success') {
                            swal({ title: "ลบสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
                        } else {
                            swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
                        }
                    },
                    error: function () {
                        swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถลบรูปภาพได้", type: "error" });
                    }
                });
            });
        }

        // Set Main Image
        $(document).on('click', '.btn-set-main', function () {
            const parentItem = $(this).closest('.profile-image-item');
            const fileId = parentItem.data('file-id');
            if (!fileId) {
                swal({ title: "ข้อผิดพลาด", text: "ไม่พบข้อมูลรูปภาพที่ต้องการตั้งเป็นรูปหลัก", type: "error" });
                return;
            }

            const formData = new FormData();
            formData.append('update_type', 'file');
            formData.append('file_action', 'set_main');
            formData.append('file_id', fileId);

            $.ajax({
                url: window.location.href,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function (response) {
                    if (response.status === 'success') {
                        swal({ title: "ตั้งค่าสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
                    } else {
                        swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
                    }
                },
                error: function () {
                    swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถตั้งค่ารูปหลักได้", type: "error" });
                }
            });
        });
    });
</script>
</body>

</html>