<?php
// บรรทัดแรกสุดของไฟล์
// login.php
require_once("actions/login.php");
// ดึงไฟล์ที่จำเป็นเข้ามาใช้งาน
require_once($base_include."/lib/connect_sqli.php");
include_once($base_include."/login_history.php");
session_start(); // สำคัญมาก: ต้องเรียกใช้ session_start()
global $mysqli;

// ตรวจสอบว่ามีค่า student_id หรือ join_info ใน Session หรือไม่
// ถ้าไม่มี แสดงว่ายังไม่ได้ล็อกอิน ให้ Redirect กลับไปหน้า login.php ทันที
if (!isset($_SESSION['student_id']) || !isset($_SESSION['join_info'])) {
    header("Location: http://origami.local/classroom/study/login.php");
    exit();
}

// โค้ดส่วนอื่นๆ ของหน้าจะเริ่มที่นี่
?>
<?php
session_start();

// --- ส่วน PHP จำลองข้อมูลตารางเรียน (เหมือนเดิม) ---
$schedule_data = [
    '2025-09-01' => [
        ['subject' => 'วิชาคณิตศาสตร์', 'time' => '09:00 - 10:30', 'status' => 'checked_in', 'id' => 1],
        ['subject' => 'วิชาภาษาไทย', 'time' => '11:00 - 12:30', 'status' => 'not_checked_in', 'id' => 2]
    ],
    '2025-09-03' => [
        ['subject' => 'วิชาภาษาอังกฤษ', 'time' => '13:00 - 14:30', 'status' => 'not_checked_in', 'id' => 3],
    ],
    '2025-09-05' => [
        ['subject' => 'วิชาวิทยาศาสตร์', 'time' => '10:00 - 12:00', 'status' => 'checked_in', 'id' => 4],
        ['subject' => 'วิชาศิลปะ', 'time' => '14:00 - 16:00', 'status' => 'not_checked_in', 'id' => 5],
    ],
    '2025-09-08' => [
        ['subject' => 'วิชาคอมพิวเตอร์', 'time' => '09:30 - 11:30', 'status' => 'not_checked_in', 'id' => 6],
    ],
    '2025-09-10' => [
        ['subject' => 'วิชาสังคมศึกษา', 'time' => '13:00 - 15:00', 'status' => 'not_checked_in', 'id' => 7],
    ],
];

// เพิ่มฟังก์ชันสำหรับดึงข้อมูลทั้งเดือน
$all_month_schedule = [];
foreach ($schedule_data as $date => $classes) {
    foreach ($classes as $class) {
        $all_month_schedule[] = [
            'date' => $date,
            'subject' => $class['subject'],
            'time' => $class['time'],
            'status' => $class['status'],
            'id' => $class['id'],
        ];
    }
}
$json_schedule = json_encode($schedule_data);
$json_all_month_schedule = json_encode($all_month_schedule);

// --- ส่วน PHP สำหรับดึงข้อมูลนักเรียนจากฐานข้อมูล ---
require_once("../../lib/connect_sqli.php");
global $mysqli;

$students_data = [];
// เพิ่มคอลัมน์ที่จำเป็นจากโค้ด studentinfo.php เข้ามาใน query
$sql = "SELECT 
    student_id, 
    student_firstname_th, 
    student_lastname_th, 
    student_image_profile, 
    student_bio, 
    student_education,
    student_birth_date,
    student_religion,
    student_bloodgroup,
    student_hobby,
    student_music,
    student_movie,
    student_goal,
    student_mobile,
    student_email,
    student_line,
    student_ig,
    student_facebook
    FROM `classroom_student` WHERE status = 1"; // เพิ่มเงื่อนไข status = 1 ด้วย

$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students_data[$row['student_firstname_th'] . ' ' . $row['student_lastname_th']] = [
            "student_id" => $row['student_id'],
            "firstname" => $row['student_firstname_th'],
            "lastname" => $row['student_lastname_th'],
            "student_pic" => $row['student_image_profile'],
            "bio" => $row['student_bio'],
            "education" => $row['student_education'],
            // เพิ่มข้อมูลส่วนตัวและไลฟ์สไตล์
            "birth_date" => $row['student_birth_date'],
            "religion" => $row['student_religion'],
            "bloodgroup" => $row['student_bloodgroup'],
            "hobby" => $row['student_hobby'],
            "music" => $row['student_music'],
            "movie" => $row['student_movie'],
            "goal" => $row['student_goal'],
            // เพิ่มข้อมูลติดต่อ
            "mobile" => $row['student_mobile'],
            "email" => $row['student_email'],
            "line" => $row['student_line'],
            "ig" => $row['student_ig'],
            "facebook" => $row['student_facebook']
        ];
    }
}
$mysqli->close();

$json_students = json_encode($students_data, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Schedule • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
</head>
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Kanit', sans-serif;
        padding: 0;
        margin: 0;
    }

    /* Container และ Card */
    .schedule-container {
        width: 100%;
        max-width: 900px;
        margin: auto;
        padding: 20px;
    }

    /* Calendar Section */
    .calendar-card {
        background-color: #fff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        color: #ff8c00;
    }
    .calendar-header h2 {
        font-weight: 700;
        margin: 0;
    }
    .calendar-nav-btn {
        background: none;
        border: none;
        font-size: 1.5em;
        color: #ff8c00;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .calendar-nav-btn:hover {
        transform: scale(1.1);
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        text-align: center;
    }
    .calendar-weekday {
        font-weight: 700;
        color: #7f8c8d;
        padding-bottom: 5px;
    }
    .calendar-day {
        position: relative;
        background-color: #f7f9fc;
        padding: 10px 5px;
        border-radius: 10px;
        min-height: 80px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
    }
    .calendar-day:hover {
        background-color: #e9ecef;
        transform: translateY(-3px);
    }
    .calendar-day.today {
        background-color: #ff8c00;
        color: #fff;
        border-color: #ff8c00;
    }
    .calendar-day.today .day-number,
    .calendar-day.today .event-item {
        color: #fff !important;
    }
    .calendar-day.inactive {
        background-color: transparent;
        color: #bbb;
        cursor: default;
        box-shadow: none;
        border: none;
    }
    .calendar-day.inactive:hover {
        transform: none;
    }
    .day-number {
        font-size: 1.5em;
        font-weight: 700;
        color: #2c3e50;
    }
    .event-item {
        font-size: 0.8em;
        padding: 2px 5px;
        border-radius: 5px;
        color: #fff;
        margin-top: 3px;
        width: 100%;
        box-sizing: border-box;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    .event-checked-in {
        background-color: #2ecc71; /* สีเขียว */
    }
    .event-not-checked-in {
        background-color: #f39c12; /* สีส้ม */
    }
    
    /* Responsive styles for mobile devices */
    @media (max-width: 767px) {
        .calendar-day {
            min-height: 60px; /* ลดความสูงของวันในปฏิทิน */
        }
        .day-number {
            font-size: 1.2em; /* ลดขนาดตัวเลขวัน */
        }
        .event-item {
            display: none; /* ซ่อนชื่อวิชา */
            width: 8px;
            height: 8px;
            padding: 0;
            border-radius: 50%; /* เปลี่ยนเป็นจุดสี */
        }
        .calendar-day.today .event-item {
            border: 1px solid #fff;
        }
        .calendar-day {
            min-height: 60px;
            padding: 5px;
            flex-direction: column;
            justify-content: flex-start;
        }
        .calendar-day .day-number {
            margin-bottom: 5px;
        }
        .calendar-day .event-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2px;
        }
        .calendar-day .event-item {
            display: block; /* แสดงจุดสี */
            width: 8px;
            height: 8px;
            padding: 0;
            border-radius: 50%;
            margin: 2px;
            text-indent: -9999px; /* ซ่อนข้อความแบบเข้าถึงได้ */
        }
        .calendar-day.today .event-item {
            border: 1px solid #fff; /* เพิ่มขอบสีขาวให้จุดบนวันปัจจุบัน */
        }
    }
    
    /* Full-screen modal for all monthly schedules */
    .modal-dialog.modal-fullscreen {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
    }
    .modal-content.modal-fullscreen {
        height: 100%;
        border-radius: 0;
        padding: 20px;
        box-shadow: none;
        border: none;
    }
    .modal-header.modal-fullscreen {
        background: #f0f2f5;
        border-bottom: none;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 0;
    }
    .modal-title-full {
        font-size: 1.8em;
        font-weight: 700;
        color: #333;
        flex-grow: 1;
        text-align: center;
        margin: 0;
    }
    .modal-body-full {
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
    }
    .daily-schedule-list {
        padding: 0;
    }
    .daily-schedule-item {
        background-color: #fff;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 8px 8px 10px rgba(0,0,0,0.1);
        border-left: 5px solid;
        cursor: pointer;
    }
    .daily-schedule-item.checked-in {
        
        border-left-color: #2ecc71;
    }
    .daily-schedule-item.not-checked-in {
        border-left-color: #f39c12;
    }
    .daily-schedule-item .subject {
        font-weight: 600;
        font-size: 1.1em;
        color: #333;
    }
    .daily-schedule-item .date-time {
        color: #7f8c8d;
        font-size: 0.9em;
    }
    .daily-schedule-item .status-text {
        color: #2ecc71;
        font-weight: 600;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Class detail page (separate modal for a specific class) */
    #classDetailModal .modal-content {
        border-radius: 15px;
    }
    #classDetailModal .checkin-button {
        width: 100%;
        margin-top: 20px;
        background-color: #ff8c00;
        border: none;
        color: white;
        padding: 15px 0;
        border-radius: 10px;
        font-size: 1.2em;
        font-weight: bold;
        cursor: pointer;
    }
    #classDetailModal .checkin-button:hover {
        background-color: #e57e00;
    }
    
    .class-detail-info h4 {
        font-size: 1.5em;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    .class-detail-info p {
        font-size: 1.1em;
        color: #7f8c8d;
        margin: 0;
    }
    .friends-list {
        margin-top: 20px;
        border-top: 1px solid #ddd;
        padding-top: 15px;
    }
    .friends-list h5 {
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }
    .friend-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .friend-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden; /* เพื่อให้รูปโปรไฟล์เป็นวงกลม */
        margin-right: 10px;
        flex-shrink: 0;
    }
    .friend-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 2px solid #ff8c00;
        border-radius: 50%;
    }
    .friend-item a {
        text-decoration: none;
        color: inherit;
    }

    /* Custom check-in modal for camera */
    #cameraModal .modal-content {
        border-radius: 15px;
    }
    #cameraModal .modal-body {
        text-align: center;
    }
    .btn-checkin {
    background-color: #f39c12; /* เปลี่ยนสีพื้นหลังเป็นสีส้ม */
    color: #fff; /* เปลี่ยนสีตัวอักษรเป็นสีขาว */
    border: none; /* ลบขอบของปุ่มออก */
    border-radius: 10px; /* เพิ่มความโค้งมนให้ปุ่ม */
    padding: 5px 10px; /* กำหนดระยะห่างภายในปุ่ม */
    font-size: 1em; /* ปรับขนาดตัวอักษร */
    font-weight: bold; /* ทำตัวอักษรให้หนา */
    cursor: pointer; /* แสดงผลเป็นรูปมือเมื่อชี้ */
    transition: background-color 0.3s; /* เพิ่ม transition ให้ดู smooth ขึ้น */
    }

    .btn-checkin:hover {
        background-color: #e67e22; /* เปลี่ยนสีเมื่อเอาเมาส์ไปชี้ */
    }

    /* Modal for student info */
    #studentInfoModal .student-info-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        padding: 30px;
        text-align: center;
    }
    #studentInfoModal .student-avatar-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto 20px;
        border: 4px solid #ff8c00;
    }
    #studentInfoModal .student-avatar-lg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #studentInfoModal .student-name-lg {
        font-size: 2em;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    #studentInfoModal .student-detail-item {
        margin-bottom: 10px;
    }
    #studentInfoModal .student-detail-item strong {
        color: #ff8c00;
        margin-right: 5px;
    }
    #studentInfoModal .student-detail-item span {
        font-size: 1.1em;
        color: #555;
    }
    #studentInfoModal .close-btn {
        background: none;
        border: none;
        font-size: 40px;
        position: absolute;
        top: 5px;
        right: 30px;
        opacity: .7;
    }
    /* เพิ่มสไตล์สำหรับส่วนข้อมูลติดต่อในป๊อปอัป */
.student-contact-section-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    padding: 30px;
    margin-top: 20px;
}
.student-contact-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}
.student-contact-item {
    text-align: center;
    flex-basis: 80px;
    flex-grow: 1;
}
.student-contact-item a {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #7f8c8d;
    font-size: 1.1em;
}
.student-contact-icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 8px;
    font-size: 28px;
    color: #fff;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.student-contact-icon-circle.phone { background-color: #2ecc71; }
.student-contact-icon-circle.mail { background-color: #D44638; }
.student-contact-icon-circle.line { background-color: #00B900; }
.student-contact-icon-circle.ig { background-color: #e4405f; }
.student-contact-icon-circle.fb { background-color: #3b5998; }
/* เพิ่มสไตล์สำหรับ Modal (popup) เพื่อให้คล้ายกับ studentinfo.php */
#studentInfoModal .modal-dialog {
    max-width: 960px;
    margin: 30px auto;
}
#studentInfoModal .modal-content {
    background: transparent;
    border: none;
    box-shadow: none;
}
#studentInfoModal .modal-header {
    border-bottom: none;
}
#studentInfoModal .modal-body {
    padding: 0;
}
/* สไตล์จาก studentinfo.php ที่เราจะนำมาใช้ใน popup */
#studentInfoModal .profile-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 40px;
    text-align: center;
    position: relative;
}
#studentInfoModal .profile-avatar-square {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid #ff8c00;
    overflow: hidden;
    margin: 0 auto 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
#studentInfoModal .profile-avatar-square img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
#studentInfoModal .profile-name {
    font-size: 2.2em;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 8px;
}
#studentInfoModal .profile-bio {
    font-size: 1.1em;
    color: #7f8c8d;
    margin-bottom: 25px;
}
#studentInfoModal .profile-course-container {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #f0f7ff;
    border-radius: 10px;
    display: inline-block;
}
#studentInfoModal .section-header-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 25px;
}
#studentInfoModal .section-header-icon i {
    font-size: 2em;
    color: #ff6600;
    margin-right: 15px;
}
#studentInfoModal .section-title {
    font-weight: 700;
    color: #333;
    margin: 0;
}
/* Contact Grid styles */
#studentInfoModal .contact-section-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 30px;
    margin-top: 20px; /* เพิ่ม margin เพื่อแยกส่วน */
}
#studentInfoModal .contact-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}
#studentInfoModal .contact-item {
    text-align: center;
    flex-basis: 100px;
    flex-grow: 1;
}
#studentInfoModal .contact-item a {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #7f8c8d;
    font-size: 1.1em;
}
#studentInfoModal .contact-icon-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 8px;
    font-size: 32px;
    color: #fff;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
#studentInfoModal .contact-icon-circle.phone { background-color: #2ecc71; }
#studentInfoModal .contact-icon-circle.mail { background-color: #D44638; }
#studentInfoModal .contact-icon-circle.line { background-color: #00B900; }
#studentInfoModal .contact-icon-circle.ig { background-color: #e4405f; }
#studentInfoModal .contact-icon-circle.fb { background-color: #3b5998; }
/* Info Grid styles */
#studentInfoModal .info-grid-section {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 30px;
}
#studentInfoModal .info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}
#studentInfoModal .info-item-box {
    width: 80%;
    background-color: #f7f9fc;
    padding: 10px;
    border-radius: 15px;
    border: 1px solid #eee;
    display: flex;
    align-items: center;
}
#studentInfoModal .info-item-box i {
    font-size: 22px;
    color: #ff8c00;
    margin-right: 15px;
}
#studentInfoModal .info-text strong {
    display: block;
    font-size: 1.1em;
    font-weight: 700;
    color: #555;
    margin-bottom: 4px;
}
#studentInfoModal .info-text span {
    font-size: 1em;
    color: #888;
}
/* Responsive styles for mobile devices */
@media (max-width: 768px) {
    #studentInfoModal .contact-grid {
        justify-content: space-around;
        gap: 10px;
    }
    #studentInfoModal .contact-item {
        flex-basis: 70px;
    }
    #studentInfoModal .contact-icon-circle {
        width: 55px;
        height: 55px;
        font-size: 24px;
    }
}
</style>
<body>
     <?php
    require_once ("component/header.php")
    ?>
<div class="schedule-container">
    <div class="calendar-card">
        <div class="calendar-header">
            <button class="calendar-nav-btn" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
            <h2 id="currentMonthYear"></h2>
            <button class="calendar-nav-btn" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-grid">
            <div class="calendar-weekday">อา</div>
            <div class="calendar-weekday">จ</div>
            <div class="calendar-weekday">อ</div>
            <div class="calendar-weekday">พ</div>
            <div class="calendar-weekday">พฤ</div>
            <div class="calendar-weekday">ศ</div>
            <div class="calendar-weekday">ส</div>
        </div>
        <div class="calendar-grid" id="calendarGrid">
        </div>
    </div>
</div>

<div class="modal fade" id="allMonthScheduleModal" tabindex="-1" role="dialog" aria-labelledby="allMonthScheduleModalLabel" style="padding-left: 0px; background-color: #fff;">
  <div class="modal-dialog modal-fullscreen" role="document">
    <div class="modal-content modal-fullscreen">
      <div class="modal-header modal-fullscreen">
        <button type="button" class="close" data-dismiss="modal" style="font-size: 40px; opacity: .7;" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title-full" id="allMonthScheduleModalLabel"></h4>
      </div>
      <div class="modal-body modal-body-full" id="allMonthScheduleModalBody">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="classDetailModal" tabindex="-1" role="dialog" aria-labelledby="classDetailModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" style="font-size: 40px; opacity: .7;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="classDetailModalLabel"></h4>
      </div>
      <div class="modal-body" id="classDetailModalBody">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-labelledby="cameraModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" style="font-size: 40px; opacity: .7;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="cameraModalLabel">ยืนยันตัวตน</h4>
      </div>
      <div class="modal-body" style="text-align: center;">
        <p>เปิดกล้องเพื่อถ่ายรูปยืนยันการเช็คอิน</p>
        <video id="webcam" width="320" height="240" autoplay></video>
        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="takePhotoBtn">ถ่ายรูป</button>
        <button type="button" class="btn btn-primary" id="confirmCheckinBtn" style="display:none;">ยืนยัน</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="studentInfoModal" tabindex="-1" role="dialog" aria-labelledby="studentInfoModalLabel" style="overflow-y: auto;padding-left: 0px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 20px; padding: 20px;">
            <button type="button" class="close-btn" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <div class="student-info-card" id="studentInfoModalBody">
            </div>
        </div>
    </div>
</div>

<script>
    const scheduleData = <?= $json_schedule; ?>;
    const allMonthScheduleData = <?= $json_all_month_schedule; ?>;
    const studentsData = <?= $json_students; ?>; // ใช้ตัวแปรใหม่

    const classmatesData = {
        1: ['จอห์น โด', 'เจน สมิธ', 'สมชาย ไชยบุญ'], // อัปเดตรายชื่อให้ตรงกับข้อมูลในตาราง
        2: ['จอห์น โด', 'เจน สมิธ'],
        3: ['สมชาย ไชยบุญ'],
        4: ['เจน สมิธ', 'จอห์น โด'],
        5: ['สมชาย ไชยบุญ'],
        6: ['จอห์น โด', 'เจน สมิธ', 'สมชาย ไชยบุญ'],
        7: ['เจน สมิธ', 'สมชาย ไชยบุญ']
    };

    const calendarGrid = document.getElementById('calendarGrid');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');

    const allMonthScheduleModal = $('#allMonthScheduleModal');
    const allMonthScheduleModalLabel = document.getElementById('allMonthScheduleModalLabel');
    const allMonthScheduleModalBody = document.getElementById('allMonthScheduleModalBody');
    const classDetailModal = $('#classDetailModal');
    const classDetailModalLabel = document.getElementById('classDetailModalLabel');
    const classDetailModalBody = document.getElementById('classDetailModalBody');
    const cameraModal = $('#cameraModal');
    const webcamElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('canvas');
    const takePhotoBtn = document.getElementById('takePhotoBtn');
    const confirmCheckinBtn = document.getElementById('confirmCheckinBtn');
    const studentInfoModal = $('#studentInfoModal');
    const studentInfoModalBody = document.getElementById('studentInfoModalBody');

    let currentDate = new Date();
    let stream;
    let currentClassId = null;

    const monthNames = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
                        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];

    function renderCalendar() {
        calendarGrid.innerHTML = '';
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        currentMonthYear.textContent = `${monthNames[month]} ${year + 543}`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        const isMobile = window.innerWidth < 768;

        for (let i = 0; i < firstDay; i++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day inactive';
            calendarGrid.appendChild(dayElement);
        }

        for (let i = 1; i <= daysInMonth; i++) {
            const dayElement = document.createElement('div');
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            
            dayElement.className = 'calendar-day';
            dayElement.innerHTML = `<span class="day-number">${i}</span>`;
            
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                dayElement.classList.add('today');
            }

            const classes = scheduleData[dateStr];
            if (classes && classes.length > 0) {
                const eventContainer = document.createElement('div');
                eventContainer.className = 'event-container';
                classes.forEach(cls => {
                    const eventElement = document.createElement('div');
                    eventElement.className = `event-item ${cls.status === 'checked_in' ? 'event-checked-in' : 'event-not-checked-in'}`;
                    eventElement.textContent = cls.subject; // ข้อความจะถูกซ่อนด้วย CSS บน mobile
                    eventContainer.appendChild(eventElement);
                });
                dayElement.appendChild(eventContainer);
            }

            calendarGrid.appendChild(dayElement);
        }
    }

    // New function to show all monthly events in a modal
    function showAllMonthlySchedule() {
        allMonthScheduleModalLabel.textContent = `1 กันยายน 2568`; // Hardcoded as per the image
        allMonthScheduleModalBody.innerHTML = '';

        if (allMonthScheduleData && allMonthScheduleData.length > 0) {
            allMonthScheduleData.sort((a, b) => new Date(a.date) - new Date(b.date));
            const dailyScheduleList = document.createElement('div');
            dailyScheduleList.className = 'daily-schedule-list';

            // Filter for September 2025 as per the image
            const filteredData = allMonthScheduleData.filter(cls => {
                const classDate = new Date(cls.date);
                return classDate.getMonth() === 8 && classDate.getFullYear() === 2025;
            });

            filteredData.forEach(cls => {
                const item = document.createElement('div');
                item.className = `daily-schedule-item ${cls.status === 'checked_in' ? 'checked-in' : 'not-checked-in'}`;
                item.innerHTML = `
                    <div class="subject">${cls.subject}</div>
                    <div class="date-time">${formatDateThai(cls.date)} • ${cls.time}</div>
                    ${cls.status === 'checked_in' ? 
                        `<span class="status-text"><i class="fas fa-check-circle"></i> เช็คอินแล้ว</span>` :
                        `<div class="btn-checkin-container"><button class="btn-checkin">เช็คอิน</button></div>`
                    }
                `;
                item.addEventListener('click', () => showClassDetail(cls));
                dailyScheduleList.appendChild(item);
            });
            allMonthScheduleModalBody.appendChild(dailyScheduleList);
        } else {
            allMonthScheduleModalBody.innerHTML = `<p class="no-events-message">ไม่มีตารางเรียนสำหรับเดือนนี้ครับ 🙂</p>`;
        }
        
        allMonthScheduleModal.modal('show');
    }

    // New function to show a single class detail with check-in button and student avatars
    function showClassDetail(cls) {
        classDetailModalLabel.textContent = cls.subject;
        
        let classmatesHtml = '';
        if (classmatesData[cls.id]) {
            classmatesHtml = `
                <div class="friends-list">
                    <h5>รายชื่อเพื่อนร่วมชั้น</h5>
                    ${classmatesData[cls.id].map(name => {
                        const student = studentsData[name]; // ดึงข้อมูลจากตัวแปรใหม่
                        const studentPic = student ? student.student_pic : '../../../images/default.png';
                        return `
                            <div class="friend-item">
                                <a href="#" onclick="showStudentInfo('${name}'); return false;">
                                    <div class="friend-avatar">
                                        <img src="${studentPic}" alt="${name}" onerror="this.src='https://randomuser.me/api/portraits/men/32.jpg'">
                                    </div>
                                </a>
                                <span>${name}</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        const checkinButtonHtml = cls.status === 'checked_in'
            ? `<span class="status-text" style="text-align: center; display: block; color: #2ecc71; font-size: 1.2em;"><i class="fas fa-check-circle"></i> เช็คอินแล้ว</span>`
            : `<button class="checkin-button" data-id="${cls.id}" onclick="initiateCheckIn(${cls.id})">เช็คอิน</button>`;

        classDetailModalBody.innerHTML = `
            <div class="class-detail-info">
                <div>
                    <p><strong>วันที่:</strong> ${formatDateThai(cls.date)}</p>
                    <p><strong>เวลา:</strong> ${cls.time}</p>
                </div>
            </div>
            ${classmatesHtml}
            ${checkinButtonHtml}
        `;
        
        allMonthScheduleModal.modal('hide');
        classDetailModal.modal('show');
    }

    // New function to show student detail in a new modal
    function showStudentInfo(studentName) {
    const student = studentsData[studentName];
    if (!student) {
        Swal.fire("ไม่พบข้อมูล", "ไม่พบข้อมูลนักเรียนคนนี้", "error");
        return;
    }

    // ฟังก์ชันช่วยจัดรูปแบบวันที่
    function formatDate(dateString) {
        if (!dateString) return "-";
        const date = new Date(dateString);
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('th-TH', options);
    }
    
    // ตรวจสอบว่ามีข้อมูลติดต่ออย่างน้อยหนึ่งอย่างหรือไม่
    const hasContact = student.mobile || student.email || student.line || student.ig || student.facebook;

    // สร้าง HTML สำหรับช่องทางการติดต่อ
    let contactSectionHtml = '';
    if (hasContact) {
        contactSectionHtml = `
            <div class="contact-section-card">
                <div class="section-header-icon">
                    <i class="fas fa-address-book" style="font-size: 25px;"></i>
                    <h3 class="section-title" style="padding-left:10px;">ช่องทางการติดต่อ</h3>
                </div>
                <div class="contact-grid">
                    ${student.mobile ? `<div class="contact-item">
                        <a href="tel:${student.mobile}">
                            <div class="contact-icon-circle phone"><i class="fas fa-phone"></i></div>
                            <span>โทรศัพท์</span>
                        </a>
                    </div>` : ''}
                    ${student.email ? `<div class="contact-item">
                        <a href="mailto:${student.email}">
                            <div class="contact-icon-circle mail"><i class="fas fa-envelope"></i></div>
                            <span>อีเมล</span>
                        </a>
                    </div>` : ''}
                    ${student.line ? `<div class="contact-item">
                        <a href="https://line.me/ti/p/~${student.line}" target="_blank">
                            <div class="contact-icon-circle line"><i class="fab fa-line"></i></div>
                            <span>Line</span>
                        </a>
                    </div>` : ''}
                    ${student.ig ? `<div class="contact-item">
                        <a href="https://www.instagram.com/${student.ig}" target="_blank">
                            <div class="contact-icon-circle ig"><i class="fab fa-instagram"></i></div>
                            <span>Instagram</span>
                        </a>
                    </div>` : ''}
                    ${student.facebook ? `<div class="contact-item">
                        <a href="https://www.facebook.com/${student.facebook}" target="_blank">
                            <div class="contact-icon-circle fb"><i class="fab fa-facebook-f"></i></div>
                            <span>Facebook</span>
                        </a>
                    </div>` : ''}
                </div>
            </div>
        `;
    }

    // สร้าง HTML สำหรับข้อมูลส่วนตัวและไลฟ์สไตล์ทั้งหมด
    const infoSectionsHtml = `
        <div class="info-grid-section">
            <div class="section-header-icon">
                <i class="fas fa-user-circle" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">ข้อมูลส่วนตัว</h3>
            </div>
            <div class="info-grid">
                <div class="info-item-box">
                    <i class="fas fa-birthday-cake" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">วันเกิด</strong>
                        <span style="padding-left:10px;">${formatDate(student.birth_date)}</span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-church" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">ศาสนา</strong>
                        <span style="padding-left:10px;">${student.religion || "-"}</span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-tint" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">กรุ๊ปเลือด</strong>
                        <span style="padding-left:10px;">${student.bloodgroup || "-"}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="info-grid-section">
            <div class="section-header-icon">
                <i class="fas fa-heartbeat" style="font-size: 25px;"></i>
                <h3 class="section-title" style="padding-left:10px;">ไลฟ์สไตล์</h3>
            </div>
            <div class="info-grid">
                <div class="info-item-box">
                    <i class="fas fa-star" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">งานอดิเรก</strong>
                        <span style="padding-left:10px;">${student.hobby || "ยังไม่ได้ระบุ"}</span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-music" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">ดนตรีที่ชอบ</strong>
                        <span style="padding-left:10px;">${student.music || "ยังไม่ได้ระบุ"}</span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-film" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">หนังที่ชอบ</strong>
                        <span style="padding-left:10px;">${student.movie || "ยังไม่ได้ระบุ"}</span>
                    </div>
                </div>
                <div class="info-item-box">
                    <i class="fas fa-bullseye" style="font-size: 25px;"></i>
                    <div class="info-text">
                        <strong style="padding-left:10px;">เป้าหมาย</strong>
                        <span style="padding-left:10px;">${student.goal || "ยังไม่ได้ระบุ"}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    // ประกอบร่าง HTML ทั้งหมด
    studentInfoModalBody.innerHTML = `
        <div class="profile-card">
            <div class="profile-avatar-square">
                <img src="${student.student_pic || '../../../images/default.png'}" 
                    onerror="this.src='../../../images/default.png'" 
                    alt="Profile Picture">
            </div>
            <h2 class="profile-name">
                ${student.firstname} ${student.lastname}
            </h2>
            <p class="profile-bio">
                ${student.bio || "ยังไม่ได้เขียน Bio"}
            </p>
            <div class="profile-course-container">
                <p class="profile-course" style="margin: 0px;">
                    <i class="fas fa-graduation-cap"></i>
                    หลักสูตร: <span>${student.education || "ยังไม่ได้ระบุ"}</span>
                </p>
            </div>
        </div>
        ${contactSectionHtml}
        ${infoSectionsHtml}
    `;

    // ซ่อน modal เก่าและเปิด modal ใหม่
    classDetailModal.modal('hide');
    studentInfoModal.modal('show');
}
    
    // Check-in process
    function initiateCheckIn(classId) {
        currentClassId = classId;
        classDetailModal.modal('hide'); // Hide detail modal immediately
        cameraModal.modal('show');
        
        takePhotoBtn.style.display = 'block';
        confirmCheckinBtn.style.display = 'none';

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(s => {
                stream = s;
                webcamElement.srcObject = stream;
                webcamElement.style.display = 'block';
                canvasElement.style.display = 'none';
            })
            .catch(err => {
                console.error("Error accessing camera: ", err);
                Swal.fire("ผิดพลาด", "ไม่สามารถเข้าถึงกล้องได้", "error");
            });
    }

    takePhotoBtn.addEventListener('click', () => {
        const context = canvasElement.getContext('2d');
        context.drawImage(webcamElement, 0, 0, canvasElement.width, canvasElement.height);
        webcamElement.style.display = 'none';
        canvasElement.style.display = 'block';
        confirmCheckinBtn.style.display = 'block';
        takePhotoBtn.style.display = 'none';
    });

    confirmCheckinBtn.addEventListener('click', () => {
        console.log("Photo captured and sent for verification. Class ID: " + currentClassId);
        
        setTimeout(() => {
            const classToUpdate = allMonthScheduleData.find(cls => cls.id === currentClassId);
            if (classToUpdate) {
                classToUpdate.status = 'checked_in';
                
                // Update UI by re-rendering
                cameraModal.modal('hide');
                renderCalendar();
                showAllMonthlySchedule(); // Re-open the monthly schedule modal
                Swal.fire({
                    title: "สำเร็จ!",
                    text: "เช็คอินเรียบร้อยแล้ว",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }, 1500); // Simulate API call delay
    });

    cameraModal.on('hidden.bs.modal', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
    
    // Close student info modal and re-open class detail modal
    studentInfoModal.on('hidden.bs.modal', function () {
        classDetailModal.modal('show');
    });

    function formatDateThai(dateStr) {
        const [year, month, day] = dateStr.split('-');
        const d = new Date(year, month - 1, day);
        const dayStr = d.getDate();
        const monthStr = monthNames[d.getMonth()];
        const yearStr = d.getFullYear() + 543;
        return `${dayStr} ${monthStr} ${yearStr}`;
    }

    // Event Listeners
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
    
    calendarGrid.addEventListener('click', () => {
        showAllMonthlySchedule();
    });

    // Initial render
    renderCalendar();
</script>
 <?php
    require_once ("component/footer.php")
    ?>
</body>
</html>