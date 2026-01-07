<?php
session_start();
// ตรวจสอบ session และดึง student_id
$student_id = $_SESSION['student_id'] ? $_SESSION['student_id'] : null;
// **ปรับปรุงตรงนี้**
$base_url = "http://origami.local/"; // URL หลักของคุณ
$geturl_prefix = $base_url; // ใช้ URL หลัก เนื่องจากรูปภาพอยู่ใน Root Path ของ Web Server

if (!$student_id) {
    // จัดการกรณีที่ไม่พบ session
    die("กรุณาเข้าสู่ระบบ");
}
// *** จำลองการเรียกไฟล์ตั้งค่าและเชื่อมต่อฐานข้อมูล (ต้องให้เหมือนกับ myphoto_process.php) ***
date_default_timezone_set('Asia/Bangkok');
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) { 
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (isset($exl_path[1]) && !empty($exl_path[1]) && !file_exists($base_include . "/" . $exl_path[1] . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include = rtrim($_SERVER['DOCUMENT_ROOT'] . $base_path, '/');
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once(BASE_INCLUDE . "/lib/connect_sqli.php"); 
global $mysqli;
// *** สิ้นสุดการจำลองการเรียกไฟล์ตั้งค่า ***

$display_name = htmlspecialchars($student_id); // ค่าเริ่มต้น: ใช้ student_id

$sql_name = "SELECT `student_firstname_en`, `student_lastname_en`, `student_firstname_th`, `student_lastname_th` 
             FROM `classroom_student` 
             WHERE `student_id` = ?";
             
$stmt_name = $mysqli->prepare($sql_name);

if ($stmt_name) {
    $stmt_name->bind_param("i", $student_id);
    $stmt_name->execute();
    $result_name = $stmt_name->get_result();
    
    if ($row_name = $result_name->fetch_assoc()) {
        $first_en = trim($row_name['student_firstname_en']);
        $last_en = trim($row_name['student_lastname_en']);
        $first_th = trim($row_name['student_firstname_th']);
        $last_th = trim($row_name['student_lastname_th']);

        // 1. ตรวจสอบชื่อภาษาอังกฤษก่อน
        if (!empty($first_en) ) {
            $name_parts = [];
            if (!empty($first_en)) $name_parts[] = $first_en;
            // if (!empty($last_en)) $name_parts[] = $last_en;
            $display_name = htmlspecialchars(implode(' ', $name_parts));
        } 
        // 2. ถ้าภาษาอังกฤษไม่มี (หรือมีแค่บางส่วนที่ว่าง) ให้ตรวจสอบภาษาไทย
        else if (!empty($first_th) ) {
            $name_parts = [];
            if (!empty($first_th)) $name_parts[] = $first_th;
            // if (!empty($last_th)) $name_parts[] = $last_th;
            $display_name = htmlspecialchars(implode(' ', $name_parts));
        }
        // 3. ถ้าไม่มีเลย จะใช้ student_id ตามค่าเริ่มต้น
    }
    $stmt_name->close();
}

// $python_script = shell_exec("myphoto.py");
// จะได้ผลลัพธ์จาก python

// ------------------------------------------------------------------------------------------------------
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
<link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
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
<script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>


    <style>
        /* สไตล์สำหรับ Album View ใหม่ */
        /* ... (CSS ส่วนอื่น ๆ คงเดิม) ... */
        .event-section {
            margin-top: 30px;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .event-header {
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        /* [แก้ไข/เพิ่ม] สำหรับ Mobile: จัดกลางอัลบั้ม และรองรับการห่อ (wrap) */
        .photo-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 0px; 
            /* NEW: จัดเรียงให้อยู่ตรงกลางเมื่อมีที่ว่าง */
            justify-content: center; 
        }
        
        /* NEW: สไตล์สำหรับกล่องอัลบั้มเดี่ยวแบบซ้อน (Stacked Album) */
        .album-box {
            position: relative;
            /* **แก้ไขตรงนี้สำหรับ Desktop/Tablet ให้เล็กและเป็น 3 ต่อแถว** */
            width: 180px; 
            height: 180px;
            overflow: hidden;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 15px;
        }
        .album-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* [แก้ไข] เอฟเฟกต์ภาพซ้อน: ใช้รูปภาพจริงซ้อนกัน 3 รูป */
        .album-stack-item {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            background: #fff;
            transition: transform 0.3s;
        }
        
        /* ใบที่ 3 (ล่างสุด) */
        .album-stack-item:nth-child(1) {
            z-index: 1;
            /* **ปรับการซ้อนให้แคบลงตามขนาดกล่องที่เล็กลง** */
            transform: translate(7px, 7px); 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            filter: brightness(0.9); 
        }
        /* ใบที่ 2 (กลาง) */
        .album-stack-item:nth-child(2) {
            z-index: 2;
            /* **ปรับการซ้อนให้แคบลงตามขนาดกล่องที่เล็กลง** */
            transform: translate(3px, 3px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            filter: brightness(0.95); 
        }
        /* ใบที่ 1 (หน้าปก - ไม่ต้องปรับ transform) */
        .album-stack-item:nth-child(3) {
            z-index: 3;
            transform: translate(0, 0); 
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        /* [ปรับปรุง] ซ่อนภาพที่ไม่ใช่ 3 รูปแรกจาก Album Stack */
        .album-stack-item:nth-child(n+4) {
             display: none; 
        }
        
        /* รูปหน้าปก (ใช้ `.album-stack-item:nth-child(3)` แทน) */
        .album-cover {
            display: none; /* ซ่อนอันเดิมที่ไม่ได้ใช้แล้ว */
        }
        
        .album-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 5px; /* ปรับ padding เล็กน้อย */
            background: rgba(0, 0, 0, 0.7);
            color: white;
            z-index: 4; /* ต้องอยู่เหนือรูปภาพทั้งหมด */
            text-align: center;
            font-size: 13px; /* ปรับขนาดฟอนต์ */
        }

        .container-fluid1 {
           margin-right: 1em;
           margin-left: 1em;
           /* margin-right: auto; */
           /* margin-left: auto; */
           background-color: #ffffff;
           border-radius: 8px;
        }
        .dropdown-menu {
    position: absolute;
    top: 100%;
    left: -85px;
    z-index: 1000;
    display: none;
    float: left;
    min-width: 100px;
    padding: 5px 5px;
    margin: 2px 0 0;
    font-size: 14px;
    text-align: left;
    list-style: none;
    background-color: #fff;
    -webkit-background-clip: padding-box;
    background-clip: padding-box;
    border: 1px solid #ccc;
    border: 1px solid rgba(0, 0, 0, .15);
    border-radius: 4px;
    -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
    box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
}
.dropdown-menu>li>a {
    display: block;
    padding: 3px 10px;
    clear: both;
    font-weight: 400;
    line-height: 1.42857143;
    color: #333;
    white-space: nowrap;
}
        
        /* NEW: Responsive สำหรับ Mobile View (ปรับให้เล็กกว่า Desktop เดิม) */
        @media (max-width: 767px) {
            .album-box {
                /* ปรับขนาดให้เป็น 2 ต่อแถวใน Mobile View (ขนาดประมาณ 150-160px จะดีกว่า 180px) */
                width: 100px; 
                height: 100px;
                /* จัดให้กล่องอัลบั้มอยู่ในแนวกึ่งกลางใน Mobile View */
                margin-left: 5px;
                margin-right: 5px;
            }
            .photo-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0px;
            margin-top: 0px; 
            /* NEW: จัดเรียงให้อยู่ตรงกลางเมื่อมีที่ว่าง */
            justify-content: center; 
        }
            .album-stack-item:nth-child(1) {
                transform: translate(5px, 5px); /* ปรับการซ้อนสำหรับมือถือ */
            }
            .album-stack-item:nth-child(2) {
                transform: translate(2px, 2px);
            }
            .album-info {
                font-size: 12px;
            }
            
        }
        
        
        /* ปรับ Modal Gallery ให้สวยงามขึ้นใน Mobile/Small Screen */
        @media (max-width: 576px) {
            .modal-body .col-xs-6 {
                width: 33.33%; /* ให้รูปใน Modal เป็น 2 ต่อแถว */
                padding-left: 5px;
                padding-right: 5px;
            }
            photo-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0px;
            margin-top: 0px; 
            /* NEW: จัดเรียงให้อยู่ตรงกลางเมื่อมีที่ว่าง */
            justify-content: center; 
        }
        }
        
        .modal-photo-wrapper {
            position: relative;
            margin-bottom: 10px; 
        }
        .download-menu {
            position: absolute;
            top: 3px;
            right: 8px;
            z-index: 10;
            opacity: 0.7; 
            transition: opacity 0.2s;
        }
        .modal-photo-wrapper:hover .download-menu {
            opacity: 1;
        }
        .download-menu .dropdown-toggle {
            color: #fff !important; 
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .download-menu .dropdown-toggle:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }
        .download-menu .fa-ellipsis-v {
            font-size: 14px;
        }
        .modal-header {
            padding: 15px;
            border-bottom: 1px solid #e5e5e5;
            background-color: #fbe299;
            border-radius: 8px;
        }
        #modalGallery .col-xs-6,
        #modalGallery .col-sm-4 {
            padding-left: 5px !important; /* ใช้ !important เพื่อให้แน่ใจว่า Override Bootstrap default */
            padding-right: 5px !important; /* ใช้ !important เพื่อให้แน่ใจว่า Override Bootstrap default */
            padding-bottom: 5px;
        }

        .main-card {
            background: linear-gradient(135deg, #FF9800 0%, #FF6D00 40%, #D84315 100%);
            border-radius: 20px;
            margin-top: 30px;
            padding: 30px 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);

        }
        .logo {
            font-size: 2.7em;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: 0.2em;
        }
        .logo-wave {
            display: block;
            width: 120px;
            margin: 20px auto 10px auto;
            border-radius: 30px;
        }
        .instruction {
            color: #fff;
            font-size: 1.3em;
            text-align: center;
            margin-bottom: 25px;
        }
        .search-btn-group {
            margin-bottom: 18px;
            text-align: center;
        }
        .search-btn-group .btn {
            margin: 0 8px 16px 8px;
            font-size: 1.2em;
            width: 150px;
        }
        .btn-icon {
            margin-right: 7px;
        }

        .btn-white-aura {
            border-radius: 8px;
            box-shadow: 0 3px 4px rgba(0, 0, 0, 0.05); /* shadow-sm */
            border: none;
            padding-inline: 6px;
        }

        .help-btn {
            background: #FF9800;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 8px 30px;
            font-size: 1.2em;
            margin: 25px auto 0 auto;
            display: block;
        }
</style>
</head>

<body>
    <input type="hidden" id="student_code_id" value="<?php echo $_SESSION['student_id'] ?>">
    <?php require_once 'component/header.php'; ?>
        <div class="main-content col-sm-10">
            <div class="container-fluid">
                <h1 class="heading-1">My Photo</h1>
                    <div class="divider-1"> 
                        <span></span>
                    </div>
                    <div class="main-card hidden">
                        <div class="text-center">
                            <div class="logo">ORIGAMI FACE DETECT</div>
                            <span class="logo-wave">
                                <svg fill="#FFF" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M22.8716 8.43578C23.6147 8.43578 24 8.02294 24 7.27982V4.10092C24 1.38991 22.6101 0 19.8578 0H16.6789C15.9358 0 15.5229 0.412844 15.5229 1.1422C15.5229 1.8578 15.9358 2.27064 16.6789 2.27064H19.7752C21.0275 2.27064 21.7431 2.94495 21.7431 4.25229V7.27982C21.7431 8.02294 22.1422 8.43578 22.8716 8.43578ZM1.12844 8.43578C1.87156 8.43578 2.27064 8.02294 2.27064 7.27982V4.25229C2.27064 2.94495 2.97248 2.27064 4.23853 2.27064H7.33486C8.06422 2.27064 8.47706 1.8578 8.47706 1.1422C8.47706 0.412844 8.06422 0 7.33486 0H4.15596C1.40367 0 0 1.38991 0 4.10092V7.27982C0 8.02294 0.412844 8.43578 1.12844 8.43578ZM10.9817 14.367H11.1055C12.4266 14.367 13.1422 13.6514 13.1422 12.3303V8.22936C13.1422 7.72018 12.7982 7.37615 12.289 7.37615C11.7523 7.37615 11.422 7.72018 11.422 8.22936V12.4266C11.422 12.5505 11.3394 12.6193 11.2156 12.6193H10.789C10.3073 12.6193 9.93578 12.9908 9.93578 13.4725C9.93578 14.0367 10.3211 14.367 10.9817 14.367ZM7.33486 11.3119C7.96789 11.3119 8.40826 10.8716 8.40826 10.2385V8.4633C8.40826 7.83028 7.96789 7.38991 7.33486 7.38991C6.72936 7.38991 6.28899 7.84404 6.28899 8.4633V10.2385C6.28899 10.8716 6.72936 11.3119 7.33486 11.3119ZM16.6789 11.3119C17.2844 11.3119 17.7385 10.8716 17.7385 10.2385V8.4633C17.7385 7.84404 17.2844 7.38991 16.6789 7.38991C16.0459 7.38991 15.6055 7.83028 15.6055 8.4633V10.2385C15.6055 10.8716 16.0459 11.3119 16.6789 11.3119ZM11.9587 18.344C13.5275 18.344 15.1239 17.6697 16.2523 16.5413C16.4174 16.3899 16.5 16.1697 16.5 15.8945C16.5 15.3991 16.1147 15.0413 15.633 15.0413C15.3716 15.0413 15.1927 15.1239 14.945 15.3991C14.2431 16.1284 13.1009 16.6376 11.9587 16.6376C10.8578 16.6376 9.72936 16.156 8.97248 15.3991C8.76606 15.1927 8.58716 15.0413 8.27064 15.0413C7.78899 15.0413 7.40367 15.3991 7.40367 15.8945C7.40367 16.1284 7.48624 16.3349 7.66514 16.5275C8.71101 17.7248 10.3624 18.344 11.9587 18.344ZM4.15596 24H7.33486C8.06422 24 8.47706 23.6009 8.47706 22.8716C8.47706 22.1422 8.06422 21.7431 7.33486 21.7431H4.23853C2.97248 21.7431 2.27064 21.0688 2.27064 19.7477V16.7202C2.27064 15.9908 1.8578 15.578 1.12844 15.578C0.399083 15.578 0 15.9908 0 16.7202V19.8991C0 22.6239 1.40367 24 4.15596 24ZM16.6789 24H19.8578C22.6101 24 24 22.6239 24 19.8991V16.7202C24 15.9908 23.6009 15.578 22.8716 15.578C22.1284 15.578 21.7431 15.9908 21.7431 16.7202V19.7477C21.7431 21.0688 21.0275 21.7431 19.7752 21.7431H16.6789C15.9358 21.7431 15.5229 22.1422 15.5229 22.8716C15.5229 23.6009 15.9358 24 16.6789 24Z"></path> </g></svg>
                            </span>
                        </div>
                        <div class="instruction">
                            เลือกวิธีค้นหาภาพด้วยใบหน้า<br>เพื่อค้นหาภาพของคุณ
                        </div>
                        <div class="search-btn-group">
                            <button class="btn btn-white-aura btn-default">
                                <span class="btn-icon"><i class="fa fa-camera"></i></span> ถ่ายภาพใบหน้า
                            </button>
                            <button class="btn btn-white-aura btn-default">
                                <span class="btn-icon"><i class="fa fa-smile-o"></i></span> ใช้ภาพใบหน้า
                            </button>
                        </div>
                        <button class="help-btn">
                            <i class="fa fa-question-circle"></i> วิธีการค้นหา
                        </button>
                    </div>

                <div class="myphoto-page-content " id="galleryView">
                    <div class="myphoto-logo myphoto-header col-sm-12">CLASSROOM GALLERY</div>
                    <div class="myphoto-white-firstmain-card myphoto-row" style="margin-bottom: 100px;">
                        <div class="myphoto-gallery-btn-group row-flex">
                            <div class="col-xs-12 col-sm-6 btn-block-container">
                                <button class="myphoto-btn myphoto-btn-default btn-block-with-radius" onclick="switchView('findMyPhotoView')" id="btn-send-profile">
                                    <svg  class="myphoto-svg-container" fill="#F18C20"  viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M22.8716 8.43578C23.6147 8.43578 24 8.02294 24 7.27982V4.10092C24 1.38991 22.6101 0 19.8578 0H16.6789C15.9358 0 15.5229 0.412844 15.5229 1.1422C15.5229 1.8578 15.9358 2.27064 16.6789 2.27064H19.7752C21.0275 2.27064 21.7431 2.94495 21.7431 4.25229V7.27982C21.7431 8.02294 22.1422 8.43578 22.8716 8.43578ZM1.12844 8.43578C1.87156 8.43578 2.27064 8.02294 2.27064 7.27982V4.25229C2.27064 2.94495 2.97248 2.27064 4.23853 2.27064H7.33486C8.06422 2.27064 8.47706 1.8578 8.47706 1.1422C8.47706 0.412844 8.06422 0 7.33486 0H4.15596C1.40367 0 0 1.38991 0 4.10092V7.27982C0 8.02294 0.412844 8.43578 1.12844 8.43578ZM10.9817 14.367H11.1055C12.4266 14.367 13.1422 13.6514 13.1422 12.3303V8.22936C13.1422 7.72018 12.7982 7.37615 12.289 7.37615C11.7523 7.37615 11.422 7.72018 11.422 8.22936V12.4266C11.422 12.5505 11.3394 12.6193 11.2156 12.6193H10.789C10.3073 12.6193 9.93578 12.9908 9.93578 13.4725C9.93578 14.0367 10.3211 14.367 10.9817 14.367ZM7.33486 11.3119C7.96789 11.3119 8.40826 10.8716 8.40826 10.2385V8.4633C8.40826 7.83028 7.96789 7.38991 7.33486 7.38991C6.72936 7.38991 6.28899 7.84404 6.28899 8.4633V10.2385C6.28899 10.8716 6.72936 11.3119 7.33486 11.3119ZM16.6789 11.3119C17.2844 11.3119 17.7385 10.8716 17.7385 10.2385V8.4633C17.7385 7.84404 17.2844 7.38991 16.6789 7.38991C16.0459 7.38991 15.6055 7.83028 15.6055 8.4633V10.2385C15.6055 10.8716 16.0459 11.3119 16.6789 11.3119ZM11.9587 18.344C13.5275 18.344 15.1239 17.6697 16.2523 16.5413C16.4174 16.3899 16.5 16.1697 16.5 15.8945C16.5 15.3991 16.1147 15.0413 15.633 15.0413C15.3716 15.0413 15.1927 15.1239 14.945 15.3991C14.2431 16.1284 13.1009 16.6376 11.9587 16.6376C10.8578 16.6376 9.72936 16.156 8.97248 15.3991C8.76606 15.1927 8.58716 15.0413 8.27064 15.0413C7.78899 15.0413 7.40367 15.3991 7.40367 15.8945C7.40367 16.1284 7.48624 16.3349 7.66514 16.5275C8.71101 17.7248 10.3624 18.344 11.9587 18.344ZM4.15596 24H7.33486C8.06422 24 8.47706 23.6009 8.47706 22.8716C8.47706 22.1422 8.06422 21.7431 7.33486 21.7431H4.23853C2.97248 21.7431 2.27064 21.0688 2.27064 19.7477V16.7202C2.27064 15.9908 1.8578 15.578 1.12844 15.578C0.399083 15.578 0 15.9908 0 16.7202V19.8991C0 22.6239 1.40367 24 4.15596 24ZM16.6789 24H19.8578C22.6101 24 24 22.6239 24 19.8991V16.7202C24 15.9908 23.6009 15.578 22.8716 15.578C22.1284 15.578 21.7431 15.9908 21.7431 16.7202V19.7477C21.7431 21.0688 21.0275 21.7431 19.7752 21.7431H16.6789C15.9358 21.7431 15.5229 22.1422 15.5229 22.8716C15.5229 23.6009 15.9358 24 16.6789 24Z"></path> </g></svg>
                                    <span class="myphoto-btn-icon" style="color: #F18C20 !important;">FIND MY PHOTO</span> 
                                    <p class="text-muted" style="margin-block: 5px; font-size: 0.8rem;">ค้นหารูปของคุณในคลาส</p> 
                                </button>
                            </div>
                            <div class="col-xs-12 col-sm-6 btn-block-container" >
                                <button class="myphoto-btn myphoto-btn-default btn-block-with-radius" onclick="goToPhotoAlbum()"  id="btn-new-img-gallery">
                                    <svg class="myphoto-svg-container" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M17.2905 11.9684C17.2905 12.7071 16.6984 13.3059 15.9679 13.3059C15.2374 13.3059 14.6453 12.7071 14.6453 11.9684C14.6453 11.2297 15.2374 10.6309 15.9679 10.6309C16.6984 10.6309 17.2905 11.2297 17.2905 11.9684Z" fill="#F18C20"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M18.1316 7.40774C17.2832 7.28707 16.1897 7.28709 14.8267 7.28711H9.17326C7.81031 7.28709 6.7168 7.28707 5.86839 7.40774C4.99062 7.53259 4.25955 7.80048 3.71603 8.42826C3.17252 9.05605 3.00655 9.82426 3.00019 10.7206C2.99404 11.587 3.13858 12.6831 3.31873 14.0493L3.68419 16.8211C3.825 17.8892 3.93897 18.7537 4.11616 19.4306C4.3006 20.1352 4.57289 20.7194 5.08383 21.1718C5.59477 21.6241 6.20337 21.8199 6.91841 21.9116C7.60534 21.9998 8.46777 21.9998 9.53332 21.9998H14.4667C15.5322 21.9998 16.3947 21.9998 17.0816 21.9116C17.7966 21.8199 18.4052 21.6241 18.9162 21.1718C19.4271 20.7194 19.6994 20.1352 19.8838 19.4306C20.061 18.7537 20.175 17.8892 20.3158 16.8211L20.6813 14.0493C20.8614 12.6831 21.006 11.587 20.9998 10.7206C20.9934 9.82426 20.8275 9.05605 20.284 8.42826C19.7404 7.80048 19.0094 7.53259 18.1316 7.40774ZM6.05259 8.73222C5.32568 8.83561 4.95802 9.02418 4.71116 9.30931C4.4643 9.59445 4.32805 9.98791 4.32278 10.7302C4.31738 11.4915 4.44802 12.4942 4.63662 13.9246L4.68663 14.3039L5.05822 14.0318C6.0171 13.3295 7.43388 13.364 8.34576 14.1273L11.7301 16.9601C12.0499 17.2278 12.6011 17.2778 12.9989 17.0438L13.2341 16.9054C14.3594 16.2435 15.8676 16.3133 16.9059 17.0955L18.7378 18.4755C18.8281 17.9799 18.909 17.3707 19.0107 16.5996L19.3634 13.9246C19.552 12.4942 19.6826 11.4915 19.6772 10.7302C19.6719 9.98791 19.5357 9.59445 19.2888 9.30931C19.042 9.02418 18.6743 8.83561 17.9474 8.73222C17.2019 8.62619 16.2018 8.62462 14.7748 8.62462H9.22521C7.79821 8.62462 6.7981 8.62619 6.05259 8.73222Z" fill="#F18C20"></path> <g opacity="0.7"> <path d="M6.87908 4.5C5.62752 4.5 4.60128 5.33974 4.25881 6.45377C4.25167 6.477 4.24482 6.50034 4.23828 6.5238C4.59662 6.40323 4.96954 6.32446 5.34706 6.27068C6.3194 6.13218 7.54821 6.13225 8.97563 6.13234L9.08223 6.13234L15.1785 6.13234C16.606 6.13225 17.8348 6.13218 18.8071 6.27068C19.1846 6.32446 19.5575 6.40323 19.9159 6.5238C19.9093 6.50034 19.9025 6.477 19.8953 6.45377C19.5529 5.33974 18.5266 4.5 17.2751 4.5H6.87908Z" fill="#E22726"></path> </g> <g opacity="0.4"> <path d="M8.8585 2.00001H15.1406C15.3498 1.99995 15.5102 1.99991 15.6505 2.01515C16.6475 2.12351 17.4636 2.78957 17.8097 3.68676H6.18945C6.53552 2.78957 7.35159 2.12351 8.34863 2.01515C8.48886 1.99991 8.64927 1.99995 8.8585 2.00001Z" fill="#E22726"></path> </g> </g></svg>
                                    <span class="myphoto-btn-icon" style="color: #F18C20 !important;">PHOTO ALBUM</span>
                                    <p class="text-muted" style="margin-block: 5px; font-size: 0.8rem;">อัลบั้มรูปทั้งหมดในคลาส</p> 
                                </button>
                            </div>
                            <div class="col-xs-12 col-sm-6 btn-block-container" >
                                <button class="myphoto-btn myphoto-btn-default btn-block-with-radius" onclick="goToPhotoUpload()" id="btn-gallery-upload">
                                    <svg class="myphoto-svg-container" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#FF9900" stroke="#FF9900"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M736.68 435.86a173.773 173.773 0 0 1 172.042 172.038c0.578 44.907-18.093 87.822-48.461 119.698-32.761 34.387-76.991 51.744-123.581 52.343-68.202 0.876-68.284 106.718 0 105.841 152.654-1.964 275.918-125.229 277.883-277.883 1.964-152.664-128.188-275.956-277.883-277.879-68.284-0.878-68.202 104.965 0 105.842zM285.262 779.307A173.773 173.773 0 0 1 113.22 607.266c-0.577-44.909 18.09-87.823 48.461-119.705 32.759-34.386 76.988-51.737 123.58-52.337 68.2-0.877 68.284-106.721 0-105.842C132.605 331.344 9.341 454.607 7.379 607.266 5.417 759.929 135.565 883.225 285.262 885.148c68.284 0.876 68.2-104.965 0-105.841z" fill="#E5594F"></path><path d="M339.68 384.204a173.762 173.762 0 0 1 172.037-172.038c44.908-0.577 87.822 18.092 119.698 48.462 34.388 32.759 51.743 76.985 52.343 123.576 0.877 68.199 106.72 68.284 105.843 0-1.964-152.653-125.231-275.917-277.884-277.879-152.664-1.962-275.954 128.182-277.878 277.879-0.88 68.284 104.964 68.199 105.841 0z" fill="#F39A2B"></path><path d="M545.039 473.078c16.542 16.542 16.542 43.356 0 59.896l-122.89 122.895c-16.542 16.538-43.357 16.538-59.896 0-16.542-16.546-16.542-43.362 0-59.899l122.892-122.892c16.537-16.542 43.355-16.542 59.894 0z" fill="#F39A2B"></path><path d="M485.17 473.078c16.537-16.539 43.354-16.539 59.892 0l122.896 122.896c16.538 16.533 16.538 43.354 0 59.896-16.541 16.538-43.361 16.538-59.898 0L485.17 532.979c-16.547-16.543-16.547-43.359 0-59.901z" fill="#F39A2B"></path><path d="M514.045 634.097c23.972 0 43.402 19.433 43.402 43.399v178.086c0 23.968-19.432 43.398-43.402 43.398-23.964 0-43.396-19.432-43.396-43.398V677.496c0.001-23.968 19.433-43.399 43.396-43.399z" fill="#E5594F"></path></g></svg>
                                    <span class="myphoto-btn-icon" style="color: #F18C20 !important;">PHOTO UPLOAD</span>
                                    <p class="text-muted" style="margin-block: 5px; font-size: 0.8rem;">อัปโหลดรูปลงอัลบั้ม</p> 
                                </button>
                            </div>
                            <div class="col-xs-12 col-sm-6 btn-block-container" >
                                <button class="myphoto-btn myphoto-btn-default btn-block-with-radius" onclick="goToAlbum()" id="btn-new-image-test" style="display: none;">
                                    <svg class="myphoto-svg-container" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M17.2905 11.9684C17.2905 12.7071 16.6984 13.3059 15.9679 13.3059C15.2374 13.3059 14.6453 12.7071 14.6453 11.9684C14.6453 11.2297 15.2374 10.6309 15.9679 10.6309C16.6984 10.6309 17.2905 11.2297 17.2905 11.9684Z" fill="#F18C20"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M18.1316 7.40774C17.2832 7.28707 16.1897 7.28709 14.8267 7.28711H9.17326C7.81031 7.28709 6.7168 7.28707 5.86839 7.40774C4.99062 7.53259 4.25955 7.80048 3.71603 8.42826C3.17252 9.05605 3.00655 9.82426 3.00019 10.7206C2.99404 11.587 3.13858 12.6831 3.31873 14.0493L3.68419 16.8211C3.825 17.8892 3.93897 18.7537 4.11616 19.4306C4.3006 20.1352 4.57289 20.7194 5.08383 21.1718C5.59477 21.6241 6.20337 21.8199 6.91841 21.9116C7.60534 21.9998 8.46777 21.9998 9.53332 21.9998H14.4667C15.5322 21.9998 16.3947 21.9998 17.0816 21.9116C17.7966 21.8199 18.4052 21.6241 18.9162 21.1718C19.4271 20.7194 19.6994 20.1352 19.8838 19.4306C20.061 18.7537 20.175 17.8892 20.3158 16.8211L20.6813 14.0493C20.8614 12.6831 21.006 11.587 20.9998 10.7206C20.9934 9.82426 20.8275 9.05605 20.284 8.42826C19.7404 7.80048 19.0094 7.53259 18.1316 7.40774ZM6.05259 8.73222C5.32568 8.83561 4.95802 9.02418 4.71116 9.30931C4.4643 9.59445 4.32805 9.98791 4.32278 10.7302C4.31738 11.4915 4.44802 12.4942 4.63662 13.9246L4.68663 14.3039L5.05822 14.0318C6.0171 13.3295 7.43388 13.364 8.34576 14.1273L11.7301 16.9601C12.0499 17.2278 12.6011 17.2778 12.9989 17.0438L13.2341 16.9054C14.3594 16.2435 15.8676 16.3133 16.9059 17.0955L18.7378 18.4755C18.8281 17.9799 18.909 17.3707 19.0107 16.5996L19.3634 13.9246C19.552 12.4942 19.6826 11.4915 19.6772 10.7302C19.6719 9.98791 19.5357 9.59445 19.2888 9.30931C19.042 9.02418 18.6743 8.83561 17.9474 8.73222C17.2019 8.62619 16.2018 8.62462 14.7748 8.62462H9.22521C7.79821 8.62462 6.7981 8.62619 6.05259 8.73222Z" fill="#F18C20"></path> <g opacity="0.7"> <path d="M6.87908 4.5C5.62752 4.5 4.60128 5.33974 4.25881 6.45377C4.25167 6.477 4.24482 6.50034 4.23828 6.5238C4.59662 6.40323 4.96954 6.32446 5.34706 6.27068C6.3194 6.13218 7.54821 6.13225 8.97563 6.13234L9.08223 6.13234L15.1785 6.13234C16.606 6.13225 17.8348 6.13218 18.8071 6.27068C19.1846 6.32446 19.5575 6.40323 19.9159 6.5238C19.9093 6.50034 19.9025 6.477 19.8953 6.45377C19.5529 5.33974 18.5266 4.5 17.2751 4.5H6.87908Z" fill="#E22726"></path> </g> <g opacity="0.4"> <path d="M8.8585 2.00001H15.1406C15.3498 1.99995 15.5102 1.99991 15.6505 2.01515C16.6475 2.12351 17.4636 2.78957 17.8097 3.68676H6.18945C6.53552 2.78957 7.35159 2.12351 8.34863 2.01515C8.48886 1.99991 8.64927 1.99995 8.8585 2.00001Z" fill="#E22726"></path> </g> </g></svg>
                                    <span class="myphoto-btn-icon" style="color: #F18C20 !important;">PHOTO ALBUM</span>
                                    <p class="text-muted" style="margin-block: 5px; font-size: 0.8rem;">อัลบั้มรูปทั้งหมดในงานคลาส</p> 
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="findMyPhotoView" class="myphoto-page-content hidden">
                    <div class="myphoto-logo">FIND MY PHOTO</div>
                    <div class="myphoto-main-card myphoto-row" style="margin-bottom: 100px;">
                        <div class="myphoto-text-center">
                            <div class="myphoto-logo-white">ORIGAMI FACE DETECT</div>
                        </div>

                        <span id="faceScanIcon" class="myphoto-logo-wave">
                            <svg class="myphoto-svg-container" fill="#fff"  viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M22.8716 8.43578C23.6147 8.43578 24 8.02294 24 7.27982V4.10092C24 1.38991 22.6101 0 19.8578 0H16.6789C15.9358 0 15.5229 0.412844 15.5229 1.1422C15.5229 1.8578 15.9358 2.27064 16.6789 2.27064H19.7752C21.0275 2.27064 21.7431 2.94495 21.7431 4.25229V7.27982C21.7431 8.02294 22.1422 8.43578 22.8716 8.43578ZM1.12844 8.43578C1.87156 8.43578 2.27064 8.02294 2.27064 7.27982V4.25229C2.27064 2.94495 2.97248 2.27064 4.23853 2.27064H7.33486C8.06422 2.27064 8.47706 1.8578 8.47706 1.1422C8.47706 0.412844 8.06422 0 7.33486 0H4.15596C1.40367 0 0 1.38991 0 4.10092V7.27982C0 8.02294 0.412844 8.43578 1.12844 8.43578ZM10.9817 14.367H11.1055C12.4266 14.367 13.1422 13.6514 13.1422 12.3303V8.22936C13.1422 7.72018 12.7982 7.37615 12.289 7.37615C11.7523 7.37615 11.422 7.72018 11.422 8.22936V12.4266C11.422 12.5505 11.3394 12.6193 11.2156 12.6193H10.789C10.3073 12.6193 9.93578 12.9908 9.93578 13.4725C9.93578 14.0367 10.3211 14.367 10.9817 14.367ZM7.33486 11.3119C7.96789 11.3119 8.40826 10.8716 8.40826 10.2385V8.4633C8.40826 7.83028 7.96789 7.38991 7.33486 7.38991C6.72936 7.38991 6.28899 7.84404 6.28899 8.4633V10.2385C6.28899 10.8716 6.72936 11.3119 7.33486 11.3119ZM16.6789 11.3119C17.2844 11.3119 17.7385 10.8716 17.7385 10.2385V8.4633C17.7385 7.84404 17.2844 7.38991 16.6789 7.38991C16.0459 7.38991 15.6055 7.83028 15.6055 8.4633V10.2385C15.6055 10.8716 16.0459 11.3119 16.6789 11.3119ZM11.9587 18.344C13.5275 18.344 15.1239 17.6697 16.2523 16.5413C16.4174 16.3899 16.5 16.1697 16.5 15.8945C16.5 15.3991 16.1147 15.0413 15.633 15.0413C15.3716 15.0413 15.1927 15.1239 14.945 15.3991C14.2431 16.1284 13.1009 16.6376 11.9587 16.6376C10.8578 16.6376 9.72936 16.156 8.97248 15.3991C8.76606 15.1927 8.58716 15.0413 8.27064 15.0413C7.78899 15.0413 7.40367 15.3991 7.40367 15.8945C7.40367 16.1284 7.48624 16.3349 7.66514 16.5275C8.71101 17.7248 10.3624 18.344 11.9587 18.344ZM4.15596 24H7.33486C8.06422 24 8.47706 23.6009 8.47706 22.8716C8.47706 22.1422 8.06422 21.7431 7.33486 21.7431H4.23853C2.97248 21.7431 2.27064 21.0688 2.27064 19.7477V16.7202C2.27064 15.9908 1.8578 15.578 1.12844 15.578C0.399083 15.578 0 15.9908 0 16.7202V19.8991C0 22.6239 1.40367 24 4.15596 24ZM16.6789 24H19.8578C22.6101 24 24 22.6239 24 19.8991V16.7202C24 15.9908 23.6009 15.578 22.8716 15.578C22.1284 15.578 21.7431 15.9908 21.7431 16.7202V19.7477C21.7431 21.0688 21.0275 21.7431 19.7752 21.7431H16.6789C15.9358 21.7431 15.5229 22.1422 15.5229 22.8716C15.5229 23.6009 15.9358 24 16.6789 24Z"></path> </g></svg>
                        </span>

                        <!-- Hidden old embedding section -->
                        <div id="oldEmbeddingSection" class="myphoto-old-embedding" style="display: none;">
                            <img id="oldProfilePhotoImg" src="" class="myphoto-history-photo-preview" alt="Previous photo"  />
                        </div>

                        <!-- Hidden preview container -->
                        <div id="avatarPreviewContainer" class="avatar-preview-container" style="display: none;">
                            <label for="photo-ref-send">
                                <div id="previewImgContainer" class="myphoto-img-container" style="display: none;">
                                    <img id="photo-previewImg" class="myphoto-scanning-preview" 
                                        src="" 
                                        onerror="this.src='/images/profile-default.jpg'"
                                        alt="Something went wrong.">
                                </div>
                            </label>
                        </div>

                        <div class="myphoto-instruction">
                            เลือกวิธีค้นหาภาพด้วยใบหน้า<br>เพื่อค้นหาภาพของคุณ
                            <br>
                            <p style="font-size:0.8rem; margin-top: 5px;">[ไฟล์ที่รองรับ .jpeg, .jpg, .png, .bmp] <br> </p>
                            <p style="font-size:0.6rem; margin-top: 2px; color: #ffdfdffa;">*ควรเปิดผ่านเบราว์เซอร์ Chrome หรือ Firefox เท่านั้น* <br> </p>
                            </div>
                        <div class="myphoto-search-btn-group" style="font-size:0.8rem; color: #f2a839;">
                            <!-- Hidden "Use Previous Photo" button -->
                            <button v-if="hasOldEmbedding" onclick="useSameUploadedImage()"  class="myphoto-btn myphoto-btn-default" style="color: rgb(111 111 111); padding: 6px 15px;" id="btn-use-samephoto">
                                <div style="display: flex; align-items: center;">
                                    <span class="myphoto-btn-icon text-center">
                                        <i class="fas fa-portrait" style="font-size: 1.8rem;"></i>
                                    </span> <p style="font-size: 14px; margin-left: 12px;"> ใช้รูปภาพที่<br> &nbsp; อัปโหลดก่อนหน้า</p>
                                </div>
                            </button>
                            <button onclick="triggerMobileGallery()" class="myphoto-btn myphoto-btn-default" style="color: rgb(111 111 111); padding: 6px 15px;" id="btn-send-profile">
                                <div style="display: flex; align-items: center;">
                                    <span class="myphoto-btn-icon">
                                    <i class="fas fa-images " style="font-size: 1.8rem;"></i>
                                    </span> <p style="font-size: 14px; margin-left: 8px;"> เลือกภาพใบหน้าจากอัลบั้ม</p>
                                </div>
                            </button>
                            <button onclick="triggerCamera()" class="myphoto-btn myphoto-btn-default" id="btn-profile-camera" style="color: rgb(111 111 111); padding: 6px 15px;">
                                <div style="display: flex; align-items: center;">
                                    <span class="myphoto-btn-icon text-center">
                                    <i class="fa fa-camera" style="font-size: 1.8rem;"></i>
                                    </span> <p style="font-size: 14px; margin-left: 10px;">อัปโหลดภาพใบหน้าชัดๆของท่าน</p>
                                </div>
                                
                            </button>
                            <button onclick="onSendProfileClick()" class="myphoto-btn myphoto-btn-default" id="btn-profile-avatar" style="font-size: 1.2rem; color: #f2a839; display:none" :disabled="!($root.info?.avatar && $root.info.avatar !== '/images/profile-default.jpg')" :class="{ 'disabled': !($root.info?.avatar && $root.info.avatar !== '/images/profile-default.jpg') }">
                                <span class="myphoto-btn-icon text-center"><i class="fas fa-user-circle"></i></span> ใช้รูปจากโปรไฟล์
                            </button>
                        </div>
                        <button class="myphoto-help-btn" onclick="openInstructionModal()">
                            <i class="fa fa-question-circle"></i> วิธีการค้นหา
                        </button>
                    </div>
                </div>

                <div id="uploadView" class="myphoto-page-content hidden">

                </div>

                <div id="resultsView" class="myphoto-page-content hidden">

                </div>
    <?php require_once("component/footer.php") ?>
</body>
</html>


