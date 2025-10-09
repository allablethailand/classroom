<?php
// myphoto.php (หน้าแสดงผล)
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

// ------------------------------------------------------------------------------------------------------
// NEW: ดึงข้อมูลชื่อนักเรียน
// ------------------------------------------------------------------------------------------------------

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
    <meta charset="UTF-8">
    <title>My Photo: รูปภาพที่มีฉัน</title>
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
            gap: 10px;
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
        
        /* NEW: Responsive สำหรับ Mobile View (ปรับให้เล็กกว่า Desktop เดิม) */
        @media (max-width: 767px) {
            .album-box {
                /* ปรับขนาดให้เป็น 2 ต่อแถวใน Mobile View (ขนาดประมาณ 150-160px จะดีกว่า 180px) */
                width: 150px; 
                height: 150px;
                /* จัดให้กล่องอัลบั้มอยู่ในแนวกึ่งกลางใน Mobile View */
                margin-left: auto;
                margin-right: auto;
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
                width: 50%; /* ให้รูปใน Modal เป็น 2 ต่อแถว */
                padding-left: 5px;
                padding-right: 5px;
            }
        }
        /* ... (CSS ส่วนปุ่มดาวน์โหลดใน Modal คงเดิม) ... */
        .modal-photo-wrapper {
            position: relative;
            margin-bottom: 15px; 
        }
        .download-menu {
            position: absolute;
            top: 5px;
            right: 5px;
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
</style>
</head>

<body>
    <?php require_once 'component/header.php'; ?>
<div class="main-content">
    <div class="container-fluid">
        <h1 class="heading-1">My Photo</h1>
                    <div class="divider-1"> 
                        <span></span>
                    </div>
    </div>
<div class="container-fluid1" >
    
<h1 style="font-size: 20px; padding: 2em 1em 2em 1em;">My Photo: รูปภาพที่มี <?php echo $display_name; ?></h1>

<div class="photo-container" id="myPhotoGallery">
    <p>กำลังค้นหารูปภาพ...</p>
</div>

<div class="modal fade" id="albumModal" tabindex="-1" role="dialog" aria-labelledby="albumModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="albumModalLabel">รูปภาพในอัลบั้ม: <span></span></h4>
      </div>
      <div class="modal-body">
        <div id="modalGallery" class="row">
          <p class="text-center">กำลังโหลด...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

</div>
</div>
<script>
// ข้อมูลรูปภาพที่ดึงมาจาก API (ใช้เก็บข้อมูล Event ทั้งหมดไว้ในตัวแปรนี้)
let allGroupedPhotos = {}; 

document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.getElementById('myPhotoGallery');
    const studentId = "<?php echo $student_id; ?>";
    const getUrlPrefix = "<?php echo $geturl_prefix; ?>";
    const modalTitleSpan = document.querySelector('#albumModalLabel span');
    const modalGallery = document.getElementById('modalGallery');

    // ฟังก์ชันสำหรับจัดการการดาวน์โหลด
    function downloadPhoto(photoUrl, originalFilename) {
        const a = document.createElement('a');
        a.href = photoUrl;
        a.download = originalFilename; 
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    // ฟังก์ชันสำหรับแสดงรูปภาพทั้งหมดใน Event ใน Modal
    function showAlbumPhotos(eventName) {
        modalTitleSpan.textContent = eventName;
        modalGallery.innerHTML = '<p class="text-center">กำลังโหลด...</p>';
        
        const eventPhotos = allGroupedPhotos[eventName];
        if (!eventPhotos) {
            modalGallery.innerHTML = '<p class="text-center">ไม่พบข้อมูลรูปภาพใน Event นี้</p>';
            return;
        }
        
        modalGallery.innerHTML = ''; // Clear loading message

        eventPhotos.forEach(photo => {
            const full_url = getUrlPrefix + photo.path; 
            const filename = photo.path.substring(photo.path.lastIndexOf('/') + 1);

            const colDiv = document.createElement('div');
            colDiv.className = 'col-xs-6 col-sm-4 col-md-3'; 
            
            // โครงสร้างสำหรับปุ่มดาวน์โหลด
            colDiv.innerHTML = `
                <div class="modal-photo-wrapper">
                    <a href="${full_url}" target="_blank" title="${filename}">
                        <img src="${full_url}" class="img-responsive" style="width: 100%; height: 150px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">
                    </a>
                    
                    <div class="dropdown download-menu">
                        <button class="btn btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="#" class="download-link"><i class="fas fa-download"></i> ดาวน์โหลด</a></li>
                        </ul>
                    </div>
                    
                    
                </div>
            `;
            
            // ผูก Event Handler ให้กับปุ่มดาวน์โหลด
            const downloadLink = colDiv.querySelector('.download-link');
            if (downloadLink) {
                downloadLink.addEventListener('click', function(e) {
                    e.preventDefault(); 
                    e.stopPropagation(); 
                    downloadPhoto(full_url, filename);
                });
            }
            
            modalGallery.appendChild(colDiv);
        });
        
        $('#albumModal').modal('show'); 
    }

    // ฟังก์ชันสำหรับเรียก PHP process และสร้าง Album View
    function fetchMyPhotos() {
        fetch(`myphoto_process?student_id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                gallery.innerHTML = ''; 
                
                if (data.status === 'success' && data.data && data.data.length > 0) {
                    
                    // 1. จัดกลุ่มรูปภาพตาม Event (description)
                    const groupedPhotos = data.data.reduce((acc, current) => {
                        const eventName = current.description || 'รูปภาพที่ไม่มีชื่อ Event'; 
                        if (!acc[eventName]) {
                            acc[eventName] = [];
                        }
                        acc[eventName].push(current);
                        return acc;
                    }, {});
                    
                    allGroupedPhotos = groupedPhotos; 
                    
                    // 2. สร้าง HTML เพื่อแสดงผลเป็น Album Stack
                    for (const eventName in groupedPhotos) {
                        const eventPhotos = groupedPhotos[eventName];
                        const photoCount = eventPhotos.length;

                        const albumBox = document.createElement('div');
                        albumBox.className = 'album-box';
                        albumBox.setAttribute('data-event-name', eventName); 
                        
                        albumBox.addEventListener('click', function() {
                            showAlbumPhotos(eventName);
                        });

                        let stackHtml = '';
                        // NEW: วนลูปเพื่อใช้ 3 รูปแรกสร้างภาพซ้อน (Stack)
                        // วนย้อนกลับเพื่อให้รูปที่ 3 อยู่ล่างสุดและรูปที่ 1 อยู่บนสุด (ตาม CSS nth-child)
                        const imagesToStack = eventPhotos.slice(0, 3).reverse(); 

                        imagesToStack.forEach((photo, index) => {
                             const full_url = getUrlPrefix + photo.path;
                             // Index 0 คือรูปที่ 3 (ล่างสุด), Index 2 คือรูปที่ 1 (บนสุด)
                             stackHtml += `<img src="${full_url}" class="album-stack-item" alt="Stack Item ${3 - index}">`;
                        });
                        
                        // โครงสร้าง Album Stack
                        albumBox.innerHTML = `
                            ${stackHtml}
                            <div class="album-info">
                                <strong>${eventName}</strong> (${photoCount} รูป)
                            </div>
                        `;

                        gallery.appendChild(albumBox);
                    }
                } else {
                    gallery.innerHTML = `<p>⚠️ ไม่พบรูปภาพที่มีคุณอยู่ในอัลบั้มรวม. ${data.message || ''}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                gallery.innerHTML = '<p>❌ เกิดข้อผิดพลาดในการเชื่อมต่อ/ประมวลผล</p>';
            });
    }

    fetchMyPhotos();
});
</script>

   <?php require_once("component/footer.php") ?>
</body>
</html>