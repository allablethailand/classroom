<?php
    header('Content-Type: application/json');
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
    DEFINE('base_path', $base_path);
    DEFINE('base_include', $base_include);
    require_once $base_include.'/lib/connect_sqli.php';
    
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
    setBucket($fsData);

    // กรณี Upload ส่ง $student_id ลง DB ด้วย
    $student_id = (isset($_SESSION['student_id'])) ? $_SESSION['student_id'] : '';


    // ============================================================
    // ACTION: Get Permission (Existing)
    // ============================================================
    if($_POST['action'] == 'getPermission') {
        $classroom_data = $_POST['classroom_data'];
        if($classroom_data) {
            $columnClass = "classroom_id, student_id, join_id";
            $tableClass = "classroom_student_join";
            $whereClass = "where md5(CONCAT(classroom_id,']C',student_id)) = '{$classroom_data}' and status = 0";
            $Class = select_data($columnClass,$tableClass,$whereClass);
            
            if (!$Class || count($Class) == 0) {
                echo json_encode(['status' => false]);
                exit;
            }
            
            $classroom_id = $Class[0]['classroom_id'];
            $student_id = $Class[0]['student_id'];
            $join_id = $Class[0]['join_id'];
            
            if($classroom_id) {
                $columnClassData = "cv.classroom_id as classroom_id,
                                cv.classroom_name,
                                (case when date(cv.classroom_start) = date(cv.classroom_end) then date_format(cv.classroom_start,'%d %b %y')
                                    else concat(date_format(cv.classroom_start,'%d %b %y'),' - ',date_format(cv.classroom_end,'%d %b %y'))
                                end) as classroom_date,
                                concat(date_format(cv.classroom_start,'%H:%i %p'),' - ',date_format(cv.classroom_end,'%H:%i %p')) as classroom_time,
                                cv.classroom_information as information,
                                cv.classroom_poster as classroom_poster,
                                date(cv.classroom_start) as classroom_start,
                                date(cv.classroom_end) as classroom_end";
                $tableClassData = "classroom_template cv";
                $whereClassData = "where cv.classroom_id = '{$classroom_id}' and cv.status = 0";
                $ClassData = select_data($columnClassData,$tableClassData,$whereClassData);
                
                $class_data = [
                    'classroom_id' => $classroom_id,
                    'classroom_name' => $ClassData[0]['classroom_name'] ?: '',
                    'classroom_date' => $ClassData[0]['classroom_date'] ?: '',
                    'classroom_time' => $ClassData[0]['classroom_time'] ?: '',
                    'information' => $ClassData[0]['information'] ? htmlspecialchars_decode($ClassData[0]['information']) : '',
                    'classroom_poster' => $ClassData[0]['classroom_poster'] ? GetPublicUrl($ClassData[0]['classroom_poster']) : '/images/noimage.jpg'
                ];
                
                echo json_encode([
                    'status' => true,
                    'classroom_id' => $classroom_id,
                    'student_id' => $student_id,
                    'member_id' => $join_id,
                    'classroom_data' => $class_data
                ]);
            } else {
                echo json_encode(['status' => false]);
            }
        } else {
            echo json_encode(['status' => false]);
        }
        exit;
    }
    
    // ============================================================
    // ACTION: User Upload Images
    // ============================================================
    if (isset($_POST['action']) && $_POST['action'] == 'userUploadImages') {
        $event_id = $_POST['event_id'];
        $register_id = $_POST['register_id'];
        
        $MAX_FILE_SIZE = 100 * 1024 * 1024;
        $ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];
        
        // 1. หาหรือสร้างอัลบัม "user"
        $user_album = select_data(
            "album_id",
            "classroom_album",
            "where classroom_id = '{$classroom_id}' and album_name = 'user' and status = 0"
        );
        
        if (!$user_album || count($user_album) == 0) {
            // สร้างอัลบัม user ใหม่
            $comp_id_data = select_data("comp_id", "classroom_template", "where classroom_id = '{$classroom_id}'");
            $comp_id = $comp_id_data[0]['comp_id'];
            
            $album_id = insert_data(
                "classroom_album",
                "(classroom_id, comp_id, album_name, album_description, status, emp_create, date_create, emp_modify, date_modify)",
                "('{$classroom_id}', '{$comp_id}', 'user', 'User uploaded photos', 0, 0, NOW(), 0, NOW())"
            );
        } else {
            $album_id = $user_album[0]['album_id'];
        }
        
        $upload_dir = "uploads/classrooms/{$classroom_id}/user_gallery/";
        $errors = [];
        $success_count = 0;
        $detailed_errors = [];
        
        // 2. ดึง company logo สำหรับใส่ลงรูป (ถ้ามี)
        $event_data = select_data("comp_id", "event_template", "where id = '{$event_id}'");
        $logo_temp = null;
        $logo_error = null;
        
        if ($event_data && count($event_data) > 0) {
            $event_comp_id = $event_data[0]['comp_id'];
            $company_data = select_data("comp_logo", "m_company", "where comp_id = '{$event_comp_id}'");
            
            if ($company_data && count($company_data) > 0 && !empty($company_data[0]['comp_logo'])) {
                $event_logo_url = '/' . $company_data[0]['comp_logo'];
                $logo_file_path = $_SERVER['DOCUMENT_ROOT'] . $event_logo_url;
                
                if (file_exists($logo_file_path)) {
                    // เก็บไฟล์ต้นฉบับโดยไม่ convert
                    $path_info = pathinfo($logo_file_path);
                    $logo_ext = strtolower($path_info['extension']);
                    $logo_temp = sys_get_temp_dir() . '/event_logo_' . md5($event_logo_url) . '.' . $logo_ext;
                    
                    if (!file_exists($logo_temp)) {
                        if (!copy($logo_file_path, $logo_temp)) {
                            $logo_error = "Cannot copy logo file";
                            $logo_temp = null;
                        }
                    }
                }
            }
        }
        
        // 3. อัปโหลดแต่ละไฟล์
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['files']['name'][$key];
            $file_size = $_FILES['files']['size'][$key];
            $file_error = $_FILES['files']['error'][$key];
            
            if ($file_error !== UPLOAD_ERR_OK) {
                $errors[] = "'{$file_name}': Upload error {$file_error}";
                $detailed_errors['upload_error'][] = $file_name;
                continue;
            }
            
            $path_info = pathinfo($file_name);
            $ext = strtolower($path_info['extension']);
            if (!in_array($ext, $ALLOWED_EXTENSIONS)) {
                $errors[] = "'{$file_name}': Invalid extension '{$ext}'";
                $detailed_errors['invalid_extension'][] = $file_name;
                continue;
            }
            
            if ($file_size > $MAX_FILE_SIZE) {
                $size_mb = round($file_size / (1024 * 1024), 2);
                $errors[] = "'{$file_name}': Too large ({$size_mb}MB)";
                $detailed_errors['too_large'][] = $file_name;
                continue;
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/bmp', 'image/x-ms-bmp'];
            if (!in_array($mime_type, $allowed_mimes)) {
                $errors[] = "'{$file_name}': Invalid MIME type '{$mime_type}'";
                $detailed_errors['invalid_mime'][] = $file_name;
                continue;
            }
            
            $image_info = @getimagesize($tmp_name);
            if ($image_info === false) {
                $errors[] = "'{$file_name}': Not a valid image file";
                $detailed_errors['invalid_image'][] = $file_name;
                continue;
            }
            
            $file_base_name = $path_info['filename'];
            $clean_name = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "_", $file_base_name);
            $strname = uniqid() . "_" . $clean_name;
            
            // ============================================================
            // 🆕 STEP 1: บันทึกรูปต้นฉบับ (ไม่มีลายน้ำ) ก่อน
            // ============================================================
            $original_no_logo = $upload_dir . $strname . '_original.' . $ext;
            try {
                $save_original_result = SaveFile($tmp_name, $original_no_logo);
                
                if (!$save_original_result) {
                    $errors[] = "'{$file_name}': Cannot save original (no watermark) to storage";
                    $detailed_errors['storage_save_failed'][] = $file_name;
                    continue;
                }
            } catch (Exception $e) {
                $errors[] = "'{$file_name}': Storage error (original) - {$e->getMessage()}";
                $detailed_errors['storage_exception'][] = $file_name;
                continue;
            }
            
            // ============================================================
            // 🆕 STEP 2: สร้างเวอร์ชันที่มีลายน้ำ
            // ============================================================
            $temp_with_logo = sys_get_temp_dir() . '/' . uniqid('user_upload_') . '.' . $ext;
            if (!copy($tmp_name, $temp_with_logo)) {
                $errors[] = "'{$file_name}': Cannot create temp file";
                $detailed_errors['temp_copy_failed'][] = $file_name;
                continue;
            }
            
            // เพิ่ม logo (ถ้ามี)
            if ($logo_temp !== null && file_exists($logo_temp)) {
                addEventLogoToOriginal($temp_with_logo, $logo_temp);
            }
            
            // ============================================================
            // 🆕 STEP 3: บันทึกเวอร์ชันที่มีลายน้ำไปที่ storage
            // ============================================================
            try {
                $original_image = $upload_dir . $strname . '.' . $ext;
                $save_result = SaveFile($temp_with_logo, $original_image);
                
                if (!$save_result) {
                    $errors[] = "'{$file_name}': Cannot save watermarked version to storage";
                    $detailed_errors['storage_save_failed'][] = $file_name;
                    @unlink($temp_with_logo);
                    continue;
                }
            } catch (Exception $e) {
                $errors[] = "'{$file_name}': Storage error (watermarked) - {$e->getMessage()}";
                $detailed_errors['storage_exception'][] = $file_name;
                @unlink($temp_with_logo);
                continue;
            }
            
            // สร้าง temp file สำหรับ resize queue
            $temp_base = sys_get_temp_dir() . "/event_user_originals/{$event_id}/";
            if (!is_dir($temp_base)) {
                mkdir($temp_base, 0777, true);
            }
            
            $temp_original_path = $temp_base . $strname . '.' . $ext;
            if (!copy($temp_with_logo, $temp_original_path)) {
                $errors[] = "'{$file_name}': Cannot copy to temp queue";
                $detailed_errors['temp_queue_failed'][] = $file_name;
                @unlink($temp_with_logo);
                continue;
            }
            @chmod($temp_original_path, 0777);
            @unlink($temp_with_logo);
            
            // บันทึกลง database
            try {
                $photo_path = escape_string($original_image);
                $photo_name = escape_string($file_base_name);
                
                $photo_id = insert_data(
                    "event_photo",
                    "(album_id, event_id, comp_id, photo_name, photo_path, public, status, register_id, emp_create, date_create, emp_modify, date_modify)",
                    "('{$album_id}', '{$event_id}', '{$comp_id}', '{$photo_name}', '{$photo_path}', 0, 0, '{$register_id}', 0, NOW(), 0, NOW())"
                );
                
                if (!$photo_id) {
                    $errors[] = "'{$file_name}': Cannot insert to database";
                    $detailed_errors['db_insert_failed'][] = $file_name;
                    continue;
                }
                
                // เพิ่มเข้า resize queue
                $temp_original_path_escaped = escape_string($temp_original_path);
                $queue_result = insert_data(
                    "event_photo_resize_queue",
                    "(photo_id, event_id, original_path, temp_original_path, status, created_at, updated_at)",
                    "('{$photo_id}', '{$event_id}', '{$photo_path}', '{$temp_original_path_escaped}', 'pending', NOW(), NOW())"
                );
                
                if (!$queue_result) {
                    $errors[] = "'{$file_name}': Cannot insert to queue";
                    $detailed_errors['queue_insert_failed'][] = $file_name;
                }
                
                $success_count++;
                
            } catch (Exception $e) {
                $errors[] = "'{$file_name}': Database error - {$e->getMessage()}";
                $detailed_errors['db_exception'][] = $file_name;
                continue;
            }
        }
        
        echo json_encode([
            'status' => true,
            'success_count' => $success_count,
            'total_files' => count($_FILES['files']['tmp_name']),
            'failed_count' => count($_FILES['files']['tmp_name']) - $success_count,
            'errors' => $errors,
            'detailed_errors' => $detailed_errors,
            'logo_status' => [
                'logo_available' => ($logo_temp !== null),
                'logo_error' => $logo_error
            ]
        ]);
        exit;
    }
    
    // ============================================================
    // ACTION: Get User Photos
    // ============================================================
    if (isset($_POST['action']) && $_POST['action'] == 'getUserPhotos') {
        $event_id = $_POST['event_id'];
        $register_id = $_POST['register_id'];
        
        $photos = select_data(
            "photo_id, photo_path, public, status, date_format(date_create, '%Y/%m/%d %H:%i:%s') as date_create",
            "event_photo",
            "where event_id = '{$event_id}' and register_id = '{$register_id}' and status = 0 order by date_create desc"
        );
        
        $photo_data = [];
        
        if ($photos && count($photos) > 0) {
            foreach ($photos as $photo) {
                $path_parts = pathinfo($photo['photo_path']);
                $thumbnail_path = $path_parts['dirname'] . '/' . $path_parts['filename'] . '_thumbnail.' . $path_parts['extension'];
                
                $queue_data = select_data(
                    "status, thumbnail_300_path",
                    "event_photo_resize_queue",
                    "where photo_id = '{$photo['photo_id']}'"
                );
                
                $queue_status = 'pending';
                $thumb_url = '';
                
                if ($queue_data && count($queue_data) > 0) {
                    $queue_status = $queue_data[0]['status'];
                    if ($queue_data[0]['thumbnail_300_path']) {
                        $thumb_url = GetPublicUrl($queue_data[0]['thumbnail_300_path']);
                    }
                }
                
                if (!$thumb_url) {
                    $thumb_url = GetPublicUrl($thumbnail_path);
                }
                
                $photo_data[] = [
                    'photo_id' => $photo['photo_id'],
                    'thumb' => $thumb_url,
                    'full' => GetPublicUrl($photo['photo_path']),
                    'date_create' => $photo['date_create'],
                    'queue_status' => $queue_status
                ];
            }
        }
        
        echo json_encode([
            'status' => true,
            'photos' => $photo_data
        ]);
        exit;
    }
    
    // ============================================================
    // ACTION: Delete User Photo
    // ============================================================
    if (isset($_POST['action']) && $_POST['action'] == 'deleteUserPhoto') {
        $photo_id = $_POST['photo_id'];
        $register_id = $_POST['register_id'];
        
        // ตรวจสอบว่ารูปนี้เป็นของ user นี้จริง
        $check = select_data(
            "photo_id",
            "event_photo",
            "where photo_id = '{$photo_id}' and register_id = '{$register_id}'"
        );
        
        if (!$check || count($check) == 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Unauthorized'
            ]);
            exit;
        }
        
        // ลบ (soft delete)
        update_data(
            "event_photo",
            "status = 1, emp_modify = 0, date_modify = NOW()",
            "photo_id = '{$photo_id}'"
        );
        
        update_data(
            "event_photo_queue",
            "status = 'deleted', updated_at = NOW()",
            "photo_id = '{$photo_id}'"
        );
        
        echo json_encode([
            'status' => true,
            'message' => 'Photo deleted successfully'
        ]);
        exit;
    }
    
    // ============================================================
    // Helper Function: Add Logo (แก้ไขใหม่หมด - ไม่บิดเบี้ยวแน่นอน 100%)
    // ============================================================
    function addEventLogoToOriginal($image_path, $logo_path) {
        try {
            if (!file_exists($image_path) || !file_exists($logo_path)) {
                return ['success' => false, 'error' => 'File not found'];
            }
            
            $image_info = getimagesize($image_path);
            if ($image_info === false) {
                return ['success' => false, 'error' => 'Invalid image'];
            }
            
            // สร้าง image resource
            $img = null;
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    $img = @imagecreatefromjpeg($image_path);
                    break;
                case IMAGETYPE_PNG:
                    $img = @imagecreatefrompng($image_path);
                    break;
                case IMAGETYPE_BMP:
                    $img = @imagecreatefrombmp($image_path);
                    break;
            }
            
            if ($img === false || $img === null) {
                return ['success' => false, 'error' => 'Cannot create image resource'];
            }
            
            // แก้ปัญหาการหมุนรูปตาม EXIF Orientation
            if ($image_info[2] == IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($image_path);
                if ($exif && isset($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $img = imagerotate($img, 180, 0);
                            break;
                        case 6:
                            $img = imagerotate($img, -90, 0);
                            break;
                        case 8:
                            $img = imagerotate($img, 90, 0);
                            break;
                    }
                }
            }
            
            // โหลด logo
            $logo_info = getimagesize($logo_path);
            $logo_img = null;
            switch ($logo_info[2]) {
                case IMAGETYPE_JPEG:
                    $logo_img = @imagecreatefromjpeg($logo_path);
                    break;
                case IMAGETYPE_PNG:
                    $logo_img = @imagecreatefrompng($logo_path);
                    break;
                case IMAGETYPE_GIF:
                    $logo_img = @imagecreatefromgif($logo_path);
                    break;
                case IMAGETYPE_BMP:
                    $logo_img = @imagecreatefrombmp($logo_path);
                    break;
                case IMAGETYPE_WEBP:
                    $logo_img = @imagecreatefromwebp($logo_path);
                    break;
            }
            
            if ($logo_img === false || $logo_img === null) {
                imagedestroy($img);
                return ['success' => false, 'error' => 'Cannot create logo resource'];
            }
            
            // ========================================
            // คำนวณขนาด logo ใหม่โดยไม่บิดเบี้ยว
            // ========================================
            $img_width = imagesx($img);
            $img_height = imagesy($img);
            
            $logo_orig_w = imagesx($logo_img);
            $logo_orig_h = imagesy($logo_img);

            // กำหนดขนาดเป้าหมาย = 10% ของความสูงรูป
            $target_height = $img_height * 0.1;
            
            // คำนวณ scale factor จากความสูง
            $scale = $target_height / $logo_orig_h;
            
            // ใช้ scale เดียวกันกับทั้งกว้างและสูง (นี่คือกุญแจสำคัญ!)
            $logo_new_w = round($logo_orig_w * $scale);
            $logo_new_h = round($logo_orig_h * $scale);
            
            // ========================================
            // สร้าง canvas ที่มีขนาดพอดีกับ logo ที่ scale แล้ว
            // ไม่บังคับให้เป็นสี่เหลี่ยมจัตุรัส
            // ========================================
            $logo_resized = imagecreatetruecolor($logo_new_w, $logo_new_h);
            
            // รักษา transparency
            imagealphablending($logo_resized, false);
            imagesavealpha($logo_resized, true);
            $transparent = imagecolorallocatealpha($logo_resized, 0, 0, 0, 127);
            imagefill($logo_resized, 0, 0, $transparent);
            imagealphablending($logo_resized, true);
            
            // ========================================
            // Resize logo โดยใช้ scale factor เดียวกัน
            // และ canvas ที่มีขนาดพอดี ไม่บังคับให้พอดี
            // ========================================
            imagecopyresampled(
                $logo_resized,           // destination (ขนาดพอดีกับ logo)
                $logo_img,               // source
                0, 0,                    // dest x, y (เริ่มที่ 0,0)
                0, 0,                    // src x, y (เริ่มที่ 0,0)
                $logo_new_w,             // dest width (ขนาดจริงหลัง scale)
                $logo_new_h,             // dest height (ขนาดจริงหลัง scale)
                $logo_orig_w,            // src width (ขนาดเดิม)
                $logo_orig_h             // src height (ขนาดเดิม)
            );
            
            // วาง logo ที่มุมขวาบน
            $margin = max(10, (int)($img_width * 0.01));
            $pos_x = $img_width - $logo_new_w - $margin;
            $pos_y = $margin;
            
            // วาง logo ลงบนรูป
            imagealphablending($img, true);
            imagesavealpha($img, true);
            imagecopy($img, $logo_resized, $pos_x, $pos_y, 0, 0, $logo_new_w, $logo_new_h);
            
            // บันทึกรูป
            switch ($image_info[2]) {
                case IMAGETYPE_JPEG:
                    imagejpeg($img, $image_path, 95);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($img, $image_path, 6);
                    break;
                case IMAGETYPE_BMP:
                    imagebmp($img, $image_path);
                    break;
            }
            
            imagedestroy($img);
            imagedestroy($logo_img);
            imagedestroy($logo_resized);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
?>