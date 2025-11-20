<?php
// แก้ไขและตั้งค่า Timezone ให้เป็นเวลากรุงเทพฯ (Asia/Bangkok)
date_default_timezone_set('Asia/Bangkok');
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';
global $mysqli;
$fsData = getBucketMaster();
$filesystem_user = $fsData['fs_access_user'];
$filesystem_pass = $fsData['fs_access_pass'];
$filesystem_host = $fsData['fs_host'];
$filesystem_path = $fsData['fs_access_path'];
$filesystem_type = $fsData['fs_type'];
$fs_id = $fsData['fs_id'];
setBucket($fsData);

$student_id = (int) $_SESSION['student_id'];

if (!isset($_SESSION['student_id']) || !isset($student_id)) {
    header("Location: /classroom/study/login");
    exit();
}

// function extractPathFromUrl($url)
// {
//     if (strpos($url, '://') === false) {
//         return cleanPath($url);
//     }
//     $parsed_url = parse_url($url);
//     if (isset($parsed_url['path'])) {
//         $path = $parsed_url['path'];
//         $path = strtok($path, '?');
//         return cleanPath($path);
//     }
//     return '';
// }

// function cleanPath($path)
// {
//     return ltrim($path, '/');
// }

// function uploadFile($file, $name, $key, $target_sub_dir = 'classroom')
// {
//     global $base_path;
//     $target_dir = $_SERVER['DOCUMENT_ROOT'] . $base_path . "/uploads/classroom/" . $target_sub_dir . "/";
//     if (!is_dir($target_dir)) {
//         mkdir($target_dir, 0755, true);
//     }

//     if (!isset($file[$name]['tmp_name']) || !isset($file[$name]['tmp_name'][$key]) || empty($file[$name]['tmp_name'][$key])) {
//         return null;
//     }

//     $tmp_name = $file[$name]['tmp_name'][$key];
//     $file_name = $file[$name]['name'][$key];
//     $file_error = $file[$name]['error'][$key];

//     if ($tmp_name && $file_error == UPLOAD_ERR_OK) {
//         $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
//         $new_file_name = uniqid() . '.' . $file_extension;
//         $target_file = $target_dir . $new_file_name;

//         if (move_uploaded_file($tmp_name, $target_file)) {
//             $new_file_path = "uploads/" . $target_sub_dir . "/" . $new_file_name;
//             return $new_file_path;
//         } else {
//             return null;
//         }
//     }
//     return null;
// }

// ดึงรูปภาพบริษัท
// $sql_company_files = "
//     SELECT file_id, file_path
//     FROM classroom_student_company_photo
//     WHERE student_id = ? AND is_deleted = 0
// ";
// $stmt_company_files = $mysqli->prepare($sql_company_files);
// $stmt_company_files->bind_param("i", $student_id);
// $stmt_company_files->execute();
// $result_company_files = $stmt_company_files->get_result();
// $company_images = $result_company_files->fetch_all(MYSQLI_ASSOC);
// $stmt_company_files->close();

// $_SESSION["user"] = $row_student["student_firstname_th"] . " " . $row_student["student_lastname_th"];
// $_SESSION["emp_pic"] = isset($student_images[0]) ? $student_images[0]['file_path'] : null;

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
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/edit_profile.js?v=<?php echo time(); ?>"></script>


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

        .company-image{
            max-width: 250px;
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
            border-color: #fff;
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

        /* .image-overlay1 {
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
        } */

        .profile-image-item:hover .image-overlay {
            opacity: 1;
        }

        .company-image-item:hover .image-overlay1 {
            opacity: 1;
        }

        .company-logo-item:hover .image-overlay1 {
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
        }

        .circle-logo {
            border-radius: 50% !important; /* Ensures the shape is circular */
            object-fit: cover; /* Ensures the image covers the container without distortion */
            max-width: 100px;
            max-height: 100px;
        }

        .circle-logo-container {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
        }

        .circle-logo-container .image-overlay1 {
            position: absolute;
            top: 0%;
            left: 0%;
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

        .circle-logo-container:hover .image-overlay1 {
            opacity: 1;
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
    </style>
    <!-- <title>Profile • ORIGAMI SYSTEM</title> -->
</head>

<body>
    <?php require_once("component/header.php") ?>
    <div class="main-content" style="padding-inline: 20px;" >
        <div class="tab-content">
            <div class="edit-profile-card">
                <div class="section-header-icon">
                    <i class="fas fa-edit" style="font-size: 25px;"></i>
                    <h3 class="section-title" style="padding-left:10px;">แก้ไขข้อมูลโปรไฟล์</h3>
                </div>
                <hr>
                <form id="editProfileForm" enctype="multipart/form-data">
                    <input type="hidden" style="display: none;" id="student_id" name="student_id" value="<?php echo $student_id; ?>">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="profile-image-gallery" id="imageGallery">
                            </div>
                            <small class="text-muted">คุณสามารถอัปโหลดรูปโปรไฟล์ได้สูงสุด 4 รูป</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstname">ชื่อ</label>
                                <input type="text" id="firstname" name="firstname" class="form-control-edit" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname">นามสกุล</label>
                                <input type="text" id="lastname" name="lastname" class="form-control-edit" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea name="bio" id="bio" class="form-control-edit"
                                    rows="3"></textarea>
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
                                <input type="text" name="mobile" id="mobile" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">อีเมล</label>
                                <input type="email" name="email" id="email" class="form-control-edit">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="line">Line</label>
                                <input type="text" name="line" id="line" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="instagram">Instagram</label>
                                <input type="text" name="instagram" id="instagram" class="form-control-edit">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="facebook">Facebook</label>
                                <input type="text" name="facebook" id="facebook" class="form-control-edit">
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
                                <input type="text" name="hobby" id="hobby" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_music">ดนตรีที่ชอบ</label>
                                <input type="text" name="student_music" id="student_music" class="form-control-edit">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_drink">เครื่องดื่มที่ชื่นชอบ</label>
                                <input type="text" name="student_drink" id="student_drink" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_movie">หนังที่ชอบ</label>
                                <input type="text" name="student_movie" id="student_movie" class="form-control-edit">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="goal">เป้าหมาย</label>
                                <input type="text" name="goal" id="goal" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="allergy">อาหารที่แพ้</label>
                                <input type="text" name="allergy" id="allergy" class="form-control-edit">
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="section-header-icon" style="font-size: 25px; ">
                        <i class="fas fa-heartbeat"></i>
                        <h3 class="section-title" style="padding-left:10px;">บริษัท</h3>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company">ชื่อบริษัท</label>
                                <input type="text" name="company" id="company" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position">ตำแหน่งงาน</label>
                                <input type="text" name="position" id="position" class="form-control-edit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_url">URL บริษัท</label>
                                <input type="url" name="company_url" id="company_url" class="form-control-edit">
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
                    </div>

                    <hr class="my-4">
                    <h5 class="card-title">รูปภาพบริษัท 📸</h5>
                    <div class="row" id="company-photos-container">
                        <!-- <?php if (!empty($company_images)): ?>
                            <?php foreach ($company_images as $index => $image): ?>
                                <div class="col-md-3 mb-4 company-image-item" data-file-id="<?= $image['file_id']; ?>"
                                    data-file-index="<?= $index; ?>">
                                    <div class="image-wrapper">
                                        <img src="<?= htmlspecialchars(BASE_PATH . '/' . $image['file_path']); ?>"
                                            alt="Company Photo" class="company-image img-thumbnail">
                                    </div>
                                    <div class="image-overlay1">
                                        <div class="overlay-actions">
                                            <button type="button" class="overlay-btn btn-delete-image-company" title="ลบรูปภาพ">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="col-md-3 mb-4 company-image-item" data-file-index="<?= count($company_images); ?>">
                            <div class="image-wrapper company-add-placeholder">
                                <i class="fas fa-plus-circle fa-2x text-muted"></i>
                                <span class="text-muted">เพิ่มรูปภาพ</span>
                                <input type="file" class="file-input-handler d-none" data-file-type="company_photo"
                                    id="add-company-file">
                                <label for="add-company-file" class="stretched-link"></label>
                            </div>
                        </div> -->
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
          

            // // Handle Save Action (for text data only)
            // $("#saveBtn").on("click", function (e) {
            //     e.preventDefault();
            //     const formData = new FormData($("#editProfileForm")[0]);
            //     formData.append('update_type', 'text');

            //     $.ajax({
            //         url: window.location.href,
            //         type: "POST",
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         dataType: 'JSON',
            //         success: function (response) {
            //             if (response.status === 'success') {
            //                 swal({ title: "บันทึกสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
            //             } else {
            //                 swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
            //             }
            //         },
            //         error: function (jqXHR, textStatus, errorThrown) {
            //             console.error("AJAX Error:", textStatus, errorThrown);
            //             swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้", type: "error" });
            //         }
            //     });
            // });

            // // Handle Delete Profile Image
            // $(document).on('click', '.btn-delete-image', function () {
            //     const parentItem = $(this).closest('.profile-image-item');
            //     const fileId = parentItem.data('file-id');
            //     deleteFile(fileId, 'profile_image');
            // });

            // // Handle Delete Company Image (is_deleted = 1)
            // $(document).on('click', '.btn-delete-image-company', function () {
            //     const parentItem = $(this).closest('.company-image-item');
            //     const fileId = parentItem.data('file-id');
            //     deleteFile(fileId, 'company_photo');
            // });

            // // Handle Delete Company Logo (set student_company_logo = NULL)
            // $(document).on('click', '.btn-delete-image-logo', function () {
            //     const parentItem = $(this).closest('.company-logo-item');
            //     deleteFile(null, 'company_logo'); // fileId เป็น null เพราะอ้างอิงจาก student_id ในตาราง student
            // });


            // Handle Set Main Image (เฉพาะรูปโปรไฟล์)
            // $(document).on('click', '.btn-set-main', function () {
            //     const parentItem = $(this).closest('.profile-image-item');
            //     const fileId = parentItem.data('file-id');
            //     if (!fileId) {
            //         swal({ title: "ข้อผิดพลาด", text: "ไม่พบข้อมูลรูปภาพที่ต้องการตั้งเป็นรูปหลัก", type: "error" });
            //         return;
            //     }

            //     const formData = new FormData();
            //     formData.append('update_type', 'file');
            //     formData.append('file_action', 'set_main');
            //     formData.append('file_id', fileId);

            //     $.ajax({
            //         url: window.location.href,
            //         type: "POST",
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         dataType: 'JSON',
            //         success: function (response) {
            //             if (response.status === 'success') {
            //                 swal({ title: "ตั้งค่าสำเร็จ", text: response.message, type: "success" }, function () { location.reload(); });
            //             } else {
            //                 swal({ title: "เกิดข้อผิดพลาด", text: response.message, type: "error" });
            //             }
            //         },
            //         error: function () {
            //             swal({ title: "เกิดข้อผิดพลาด", text: "ไม่สามารถตั้งค่ารูปหลักได้", type: "error" });
            //         }
            //     });
            // });
        });
    </script>
</body>

</html>