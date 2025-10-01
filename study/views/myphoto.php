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
        .photo-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .photo-box {
            border: 1px solid #ccc;
            padding: 5px;
            width: 200px; /* กำหนดขนาดกล่องรูป */
            text-align: center;
        }
        .photo-box img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <?php require_once 'component/header.php'; ?>

<div class="container-fluid">
<h1>My Photo: รูปภาพที่มี <?php echo htmlspecialchars($student_id); ?></h1>

<div class="photo-container" id="myPhotoGallery">
    <p>กำลังค้นหารูปภาพ...</p>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.getElementById('myPhotoGallery');
    const studentId = "<?php echo $student_id; ?>";
    const getUrlPrefix = "<?php echo $geturl_prefix; ?>";

    // ฟังก์ชันสำหรับเรียก PHP process
    function fetchMyPhotos() {
        // ใช้ fetch เพื่อเรียก myphoto_process.php (ควรใช้ POST แต่ตัวอย่างนี้ใช้ GET เพื่อความง่าย)
        fetch(`myphoto_process.php?student_id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                gallery.innerHTML = ''; // ล้างข้อความ 'กำลังค้นหา'
                if (data.status === 'success' && data.images && data.images.length > 0) {
                    // แสดงรูปภาพที่พบ
                    data.images.forEach(image_path => {
                        // image_path ที่ได้กลับมาคือ Path ใน Server เช่น uploads/classroom/68d0c3a261674.png
                        // ต้องต่อกับ $geturl_prefix เพื่อแสดงผลบนเว็บ
                        const full_url = getUrlPrefix + image_path; 

                        const photoBox = document.createElement('div');
                        photoBox.className = 'photo-box';
                        photoBox.innerHTML = `
                            <img src="${full_url}" alt="รูปภาพกลุ่ม">
                            <p>${image_path.substring(image_path.lastIndexOf('/') + 1)}</p>
                        `;
                        gallery.appendChild(photoBox);
                    });
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

