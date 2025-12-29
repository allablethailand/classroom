<?php
    session_start();
   
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if($_SERVER['HTTP_HOST'] == 'localhost'){
        $request_uri = $_SERVER['REQUEST_URI'];
        $exl_path = explode('/',$request_uri);
        if(!file_exists($base_include."/dashboard.php")){
            $base_path .= "/".$exl_path[1];
        }
        $base_include .= "/".$exl_path[1];
    }
    define('BASE_PATH', $base_path);
    define('BASE_INCLUDE', $base_include);
    require_once $base_include.'/lib/connect_sqli.php';
    require_once $base_include.'/actions/func.php';
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);


// ไฟล์นี้ยัง fix ไว้ว่า emp_modify = 1; 
if(isset($_POST) && $_POST['action'] == 'loadProfile') {
    
    if (!isset($_POST['student_id'])) {
        echo json_encode(['status' => false, 'message' => 'Invalid or missing student_id']);
        exit;
    }
    
    $student_id = $_POST['student_id'];
    $sql_student = "SELECT cs.*, cg.group_color
        FROM classroom_student cs
        LEFT JOIN classroom_student_join csj ON cs.student_id = csj.student_id
        LEFT JOIN classroom_group cg ON csj.group_id = cg.group_id
        WHERE cs.student_id = ?";

    $stmt = $mysqli->prepare($sql_student);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result_student = $stmt->get_result();
    $row_student = $result_student->fetch_assoc();
    $stmt->close();

    $row_student['student_company_logo'] = GetUrl($row_student['student_company_logo']);

    $sql_files = "SELECT file_id, file_path, file_status, file_order
        FROM classroom_file_student
        WHERE student_id = ? AND file_type = 'profile_image' AND is_deleted = 0
        ORDER BY file_status DESC, file_order ASC";
    $stmt_files = $mysqli->prepare($sql_files);
    $stmt_files->bind_param("i", $student_id);
    $stmt_files->execute();
    $result_files = $stmt_files->get_result();
    $student_images = $result_files->fetch_all(MYSQLI_ASSOC);
    if (!$student_images) {
        $student_images = []; 
    }
    $stmt_files->close();

    foreach ($student_images as $key => $img) {
        $student_images[$key]['student_file_path'] = GetUrl($img['file_path']);
    }

    // ดึงรูปภาพบริษัท
    $sql_company_files = "SELECT file_id, file_path
        FROM classroom_student_company_photo
        WHERE student_id = ? AND is_deleted = 0";
    $stmt_company_files = $mysqli->prepare($sql_company_files);
    $stmt_company_files->bind_param("i", $student_id);
    $stmt_company_files->execute();
    $result_company_files = $stmt_company_files->get_result();
    $company_images = $result_company_files->fetch_all(MYSQLI_ASSOC);
    $stmt_company_files->close();

    foreach ($company_images as $keyIn => $cmp_img) {
        $cmp_img[$keyIn]['student_company_filepath'] = GetUrl($cmp_img['file_path']);
    }


    echo json_encode([
        'status' => true,
        'student_info' => $row_student,
        'student_images' => $student_images,
        'company_images' => $company_images,
    ]);
}

if(isset($_POST) && $_POST['action'] == 'saveProfile')  {

    if (!isset($_POST['student_id'])) {
        echo json_encode(['status' => false, 'message' => 'Invalid or missing student_id']);
        exit;
    }

    $student_id = $_POST['student_id']; 
    $bio = isset($_POST['bio']) ? $_POST['bio'] : null;
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $line = isset($_POST['line']) ? $_POST['line'] : null;
    $instagram = isset($_POST['instagram']) ? $_POST['instagram'] : null;
    $facebook = isset($_POST['facebook']) ? $_POST['facebook'] : null;
    $hobby = isset($_POST['hobby']) ? $_POST['hobby'] : null;
    $student_music = isset($_POST['student_music']) ? $_POST['student_music'] : null;
    $student_drink = isset($_POST['student_drink']) ? $_POST['student_drink'] : null;
    $student_movie = isset($_POST['student_movie']) ? $_POST['student_movie'] : null;
    $student_allergy = isset($_POST['allergy']) ? $_POST['allergy'] : null;
    $goal = isset($_POST['goal']) ? $_POST['goal'] : null;
    $company = isset($_POST['company']) ? $_POST['company'] : null;
    $company_detail = isset($_POST['company_detail']) ? $_POST['company_detail'] : null;
    $company_url = isset($_POST['company_url']) ? $_POST['company_url'] : null;
    $position = isset($_POST['position']) ? $_POST['position'] : null;
    $emp_modify = $student_id;

    $target_dir = "uploads/classroom/"; 

    if (isset($_FILES['profile_image'])) {
        
        $fileCount = count($_FILES['profile_image']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['profile_image']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['profile_image']['tmp_name'][$i];
                $fileName = basename($_FILES['profile_image']['name'][$i]);
              
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                $unique_id = substr(base_convert(time(),10,36).md5(microtime()),0,16).'.'. $ext;
                // $path_upload = $base_include.'/uploads/classroom/' . 'profile_images/' . $unique_id;
                $path_save = $path_upload . $unique_id;
                $save_name = $target_dir . 'profile_images/' . $unique_id;
                // $save_name = $target_dir . $_SESSION['comp_id'].'/'.$student_id.'/'.$unique_id;
                // CORRECT PATH FOR THIS SAVE_NAME

                $upload = SaveFile($tmpName, $save_name);
                $columnSeq = "ifnull(max(file_order)+1,1) as file_order_max";
                $tableSeq = "classroom_file_student";
                $whereFileStd = "where student_id = '{$student_id}' and is_deleted = 0";
                $fileImgSeq = select_data($columnSeq,$tableSeq,$whereSeq);
                $file_item_max = $fileImgSeq[0]['file_order_max'];

                // MAX ORDER = 4
                if($file_item_max < 5) {
                    $tableInsFile = "classroom_file_student";
                    $columnInsFile = "(student_id,file_path, file_type,  file_order, is_deleted, emp_create, date_create)";
                    $valueInsFile = "('{$student_id}','{$save_name}','profile_image','{$file_item_max}',0,'1',NOW())";
                    insert_data($tableInsFile, $columnInsFile, $valueInsFile);
                } else {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'Can not insert new image, Max File Order!'
                    ]);
                }

            }
        }
    }

    if (isset($_FILES['company_banner']) && $_FILES['company_banner']['error'] === UPLOAD_ERR_OK) {
        $tmpNameBn = $_FILES['company_banner']['tmp_name'];

       
        $fileNameBn = basename($_FILES['company_banner']['name']);
        $extBn = pathinfo($fileNameBn, PATHINFO_EXTENSION);
        $unique_id = substr(base_convert(time(),10,36) . md5(microtime()), 0, 16) . '.' . $extBn;

        $path_save = $path_upload . $unique_id; 
        $save_nameBn = $target_dir . 'company_banners/' . $unique_id;

        $uploadBanner = SaveFile($tmpNameBn, $save_nameBn);

        if ($uploadBanner) {
            // Update database with company_banner
            $tableInsFile = "classroom_student_company_photo";
            $columnInsFile = "(student_id, file_path, is_deleted, date_create, emp_create)";
            $valueInsFile = "('{$student_id}','{$save_nameBn}', '0', NOW() , '1')";

            insert_data($tableInsFile, $columnInsFile, $valueInsFile);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload company banner']);
            exit;
        }
    }

    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['company_logo']['tmp_name'];
        $fileName = basename($_FILES['company_logo']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $unique_id = substr(base_convert(time(),10,36) . md5(microtime()), 0, 16) . '.' . $ext;

        $path_save = $path_upload . $unique_id;
        $save_name = $target_dir . 'company_logos/' . $unique_id;

        $upload = SaveFile($tmpName, $save_name);

        if ($upload) {
            $tableInsFile = "classroom_student";
            $columnInsFile = "student_company_logo = '{$save_name}'";
            $whereInsFile = "student_id = '{$student_id}'";

            update_data($tableInsFile, $columnInsFile, $whereInsFile);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload company logo']);
            exit;
        }
    }

    $valueUpdateProfile = "student_bio = '{$bio}',student_mobile = '{$mobile}', student_email = '{$email}', student_line = '{$line}', student_ig = '{$instagram}', student_facebook = '{$facebook}', student_hobby = '{$hobby}', student_music = '{$student_music}', student_drink = '{$student_drink}', student_movie = '{$student_movie}', student_allergy = '{$student_allergy}', student_goal = '{$goal}', student_company = '{$company}', student_company_detail = '{$company_detail}', student_company_url = '{$company_url}', student_position = '{$position}', emp_modify = '2', date_modify = NOW()";
    $tableUpdateProfile = "classroom_student";
    $whereUpdateProfile = "student_id = '{$student_id}'";
    $profile_data = update_data($tableUpdateProfile, $valueUpdateProfile,  $whereUpdateProfile);

    if ($profile_data) {
        echo json_encode([
            'status' => true,
            'message' => 'Success!'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed!'
        ]);
    }
}

if (isset($_POST) && $_POST['action'] == 'updateImageProfile') {

    if (!isset($_POST['student_id']) && !isset($_POST['file_id'])) {
        echo json_encode(['status' => false, 'message' => 'Missing student_id or Missing File Input']);
        exit;
    }

    $student_id = $_POST['student_id'];
    $file_id = $_POST['file_id'];

    $tableStd = "classroom_file_student";
    $colStd = "file_path";
    $whereStd = "student_id = '{$student_id}' AND file_id = '{$file_id}' AND is_deleted = '0'";
    $profile_data = select_data($colStd, $tableStd, $whereStd);
    $profile_img = $profile_data[0]['file_path'];

    if(!isset($profile_img)){
        echo json_encode(['status' => false, 'message' => 'No file uploaded or upload error']);
        exit();
    }

      // "uploads/classroom/";

    if (isset($_FILES['new_img_file']) && $_FILES['new_img_file']['error'] == UPLOAD_ERR_OK) {
        $tmpName = $_FILES['new_img_file']['tmp_name'];
        $fileName = basename($_FILES['new_img_file']['name']);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $unique_id = substr(base_convert(time(),10,36).md5(microtime()),0,16).'.'.$ext;

        $target_dir = "uploads/classroom/profile_images/"; 
        $save_name = $target_dir . $unique_id;

        $upload = SaveFile($tmpName, $save_name); 

        if ($upload) {
            // Update database record with new file path
            $tableUpProfile = "classroom_file_student";
            $valueUpProfile = "file_path = '{$save_name}', date_modify = NOW(), emp_modify = '1'";
            $whereUpProfile = "student_id = '{$student_id}' AND file_id = '{$file_id}' AND is_deleted = '0'";
            update_data($tableUpProfile,$valueUpProfile,$whereUpProfile);

            echo json_encode(['status' => true, 'message' => 'Profile image updated successfully']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to save the uploaded file']);
        }
    } else {
        echo json_encode(['status' => false, 'message' => 'No file uploaded or upload error']);
    }
} 

if (isset($_POST) && $_POST['action'] == 'deleteCompLogo') {
        
    if (!isset($_POST['student_id'])) {
        echo json_encode(['status' => false, 'message' => 'Invalid or missing student_id']);
        exit;
    }

    $student_id = $_POST['student_id'];

    $studentTable = "classroom_student";
    $studentCol = "student_company_logo = NULL";
    $studentWhere = "student_id = '{$student_id}'";
    update_data($studentCol, $studentTable, $studentWhere);

    echo json_encode(['status' => true, 'message' => 'Company logo deleted successfully']);
} 

if (isset($_POST) && $_POST['action'] == 'setMainImage') {
    
    if (!isset($_POST['student_id']) && !isset($_POST['file_id'])) {
        echo json_encode(['status' => false, 'message' => 'Missing student_id or Missing File Input']);
        exit;
    }

    $student_id = $_POST['student_id'];
    $file_id = $_POST['file_id'];

    $table = "classroom_file_student";
    $where = "student_id = '{$student_id}' AND is_deleted = '0'";

    // Get all file entries for this student ordered by file_order ascending
    $all_files = select_data("file_id, file_order", $table, $where . " ORDER BY file_order ASC");

    if (empty($all_files)) {
        echo json_encode([
            'status' => false, 
            'message' => 'No files found for this student']);
        exit;
    }

    // Loop all files and update file_order: set current file_id to 1, others incremented accordingly
    $order = 2;
    foreach ($all_files as $file) {
        if ($file['file_id'] == $file_id) {
            $new_order = 1;
        } else {
            $new_order = $order;
            $order++;
        }
        // Update this file_order in DB
        update_data($table, "file_order = {$new_order}", "file_id = '{$file['file_id']}' AND student_id = '{$student_id}' AND is_deleted = '0'");
    }

    echo json_encode([
        'status' => true,
        'message' => 'Main profile image set successfully'
    ]);
} 


if (isset($_POST) && $_POST['action'] == 'deleteProfileImg') {

    if (!isset($_POST['student_id']) && !isset($_POST['file_id'])) {
        echo json_encode(['status' => false, 'message' => 'Missing Student Id or Missing File Input']);
        exit;
    }

    $student_id = $_POST['student_id'];
    $file_id = $_POST['file_id'];

    $studentUpTable = "classroom_file_student";
    $studentUpCol = "is_deleted = '1'";
    $studentUpWhere = "student_id = '{$student_id}' AND file_id = '{$file_id}'";
    update_data($studentCol, $studentTable, $studentWhere);

    echo json_encode([
        'status' => true, 
        'message' => 'Main profile image set successfully'
    ]);
}


if (isset($_POST) && $_POST['action'] == 'deleteBanner') {
    
    if (!isset($_POST['student_id']) && !isset($_POST['file_id'])) {
        echo json_encode(['status' => false, 'message' => 'Missing student_id or Missing File Input']);
        exit;
    }

    $student_id = $_POST['student_id'];
    $file_id = $_POST['file_id'];

    $studentUpTable = "classroom_student_company_photo";
    $studentUpCol = "is_deleted = '1'";
    $studentUpWhere = "student_id = '{$student_id}' AND file_id = '{$file_id}'";
    update_data($studentCol, $studentTable, $studentWhere);

    echo json_encode([
        'status' => true, 
        'message' => 'Company banner deleted successfully'
    ]);
}

?>