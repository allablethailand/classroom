<?php
// บรรทัดแรกสุดของไฟล์
// login.php
// session_start();

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

// require_once("actions/login.php");
// // ดึงไฟล์ที่จำเป็นเข้ามาใช้งาน

// require_once($base_include."/lib/connect_sqli.php");
// include_once($base_include."/login_history.php");
// session_start(); // สำคัญมาก: ต้องเรียกใช้ session_start()
global $mysqli;


// --- ส่วน PHP จำลองข้อมูลตารางเรียน (เหมือนเดิม) ---

$schedule_data = [
    '2025-10-01' => [
        ['subject' => 'ลงทะเบียนผู้เข้าอบรม, รายงานตัว, ตัดสูท, ถ่ายรูป, แจกเสื้อโปโล หมวก, ป้ายชื่อ, สแกน QR เข้ากลุ่ม 3 กลุ่ม, sign PDPA, สมุดโทรศัพท์', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 1],
        ['subject' => 'พิธีเปิด ประธานกล่าวเปิดหลักสูตร, ผอ.หลักสูตร อธิบายรายละเอียดหลักสูตร, กิจกรรมละลายพฤติกรรม', 'time' => '13:00 - 17:00', 'status' => 'not_checked_in', 'id' => 2],
        ['subject' => 'แต่ละกลุ่มคุยเรื่องการแสดงโชว์ในช่วงกินเลี้ยง, กินเลี้ยง, แสดงโชว์แต่ละกลุ่ม ("หลักสูตร เป็นเจ้าภาพจัดเลี้ยง")', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 3]
    ],
    '2025-10-02' => [
        ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Deep drive in AI', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 4],
        ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Knowledge Base and Business AI in Organization', 'time' => '13:00 - 16:00', 'status' => 'not_checked_in', 'id' => 5]
    ],
    '2025-10-03' => [
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: Green : Shift & Sustainability Landscape', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 6],
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: กลยุทธ์และธรรมมาภิบาล ESG', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 7],
        ['subject' => 'กลุ่มดิน เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 8]
    ],
    '2025-10-04' => [
        ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: AWS Deep AI Technology', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 9],
        ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Transform your organization by Huawei cloud', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 10],
        ['subject' => 'กลุ่มน้ำ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 11]
    ],
    '2025-10-05' => [
        ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 12]
    ],
    '2025-10-06' => [
        ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 13]
    ],
    '2025-10-07' => [
        ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 14]
    ],
    '2025-10-08' => [
        ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 15]
    ],
    '2025-10-09' => [
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: การเงินสีเขียว & ความเสี่ยงสภาพภูมิอากาศ', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 16],
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: Green Innovation & Cirular Models', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 17],
        ['subject' => 'กลุ่มลม เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 18]
    ],
    '2025-10-10' => [
        ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Digital Transformation by AI in Organization', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 19],
        ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Organization Digital Technology', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 20],
        ['subject' => 'กลุ่มไฟ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 21]
    ],
    '2025-10-11' => [
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: Sector Deep Dive (เลือกตามกลุ่มเป้าหมาย)', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 22],
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: ผู้นำ องค์กร และอนาคต', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 23],
        ['subject' => 'กลุ่มหลักสูตร เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 24]
    ],
    '2025-10-12' => [
        ['subject' => 'เยี่ยมชม โรงงาน', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 25],
        ['subject' => 'เยี่ยมชม โรงงาน', 'time' => '14:30 - 16:00', 'status' => 'not_checked_in', 'id' => 26]
    ],
    '2025-10-13' => [
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: การพัฒนาอุตสหกรรมสู่สังคมคาร์บอนเครดิตต่ำ ในสถานประกอบการ', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 27],
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: การส่งเสริมยกระดับมาตรฐานสถานประกอบการสู่อุตสาหกรรมสีเขียว', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 28],
        ['subject' => '**กลุ่มดิน+น้ำ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 29]
    ],
    '2025-10-14' => [
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 30],
        ['subject' => 'รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 31],
        ['subject' => '**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 32]
    ],
    '2025-10-15' => [
        ['subject' => 'สรุปประสบการณ์และผลการเรียนรู้ ปิดหลักสูตร', 'time' => '09:30 - 16:00', 'status' => 'not_checked_in', 'id' => 33],
        ['subject' => 'หลักสูตรเป็นเจ้าภาพจัดเลี้ยง, theme กาล่าดินเนอร์ เดินพรมแดง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 34]
    ]
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
// global $mysqli;

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
    FROM `classroom_student` WHERE status = 0"; // เพิ่มเงื่อนไข status = 1 ด้วย

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
// $mysqli->close();

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
        border-bottom-right-radius: 20px;
        border-bottom-left-radius: 20px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-top-right-radius: 20px;
        border-top-left-radius: 20px;
        color: #ff8c00;
        background-color: #ebebeb;
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
        padding-bottom: 5px;
    }
    .calendar-weekday {
    font-weight: bold;
    font-size: 1.3em;
    /* สีฟอนต์เริ่มต้นสำหรับวันในสัปดาห์ */
}

/* สีเฉพาะเจาะจงสำหรับแต่ละวัน */
.calendar-weekday:nth-child(1) {
    color: #fd0101; /* สีแดงเข้มสำหรับวันอาทิตย์ */
}

.calendar-weekday:nth-child(2) {
    color: #e9c500; /* สีเหลืองสำหรับวันจันทร์ */
}

.calendar-weekday:nth-child(3) {
    color: #FF1493; /* สีชมพูเข้มสำหรับวันอังคาร */
}

.calendar-weekday:nth-child(4) {
    color: #00d600; /* สีเขียวเข้มสำหรับวันพุธ */
}

.calendar-weekday:nth-child(5) {
    color: #FF8C00; /* สีส้มเข้มสำหรับวันพฤหัสบดี */
}

.calendar-weekday:nth-child(6) {
    color: #0000ff; /* สีน้ำเงินเข้มสำหรับวันศุกร์ */
}

.calendar-weekday:nth-child(7) {
    color: #9503ff; /* สีม่วงเข้มสำหรับวันเสาร์ */
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
    @media (max-width: 900px) {
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
        font-size: 1em;
        color: #555;
        padding-bottom: .6em;
    }
    .daily-schedule-item .date-time {
        color: #7f8c8d;
        font-size: 0.9em;
        padding-bottom: .6em;
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
/* New style for 'no events' message */
.no-events-message {
    text-align: center;
    color: #7f8c8d;
    font-size: 1.3em;
    font-weight: 600;
    padding: 40px 20px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    margin-top: 20px;
    border: 1px dashed #ccc;
}
/* เพิ่ม Media Query สำหรับหน้าจอขนาดใหญ่ (Desktop) */
@media (min-width: 901px) {
    /* กำหนดขนาดสูงสุดและจัดกึ่งกลางสำหรับคอนเทนเนอร์หลัก */
    .schedule-container,
    .daily-schedule-display-container {
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }

    /* จัดการให้ event-item แสดงผลได้ดีขึ้นบนจอใหญ่ */
    .calendar-day .event-item {
        /* ใช้คำสั่งนี้เพื่อกำหนดให้ข้อความเกินขอบแล้วแสดงเป็น ... */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 2px 8px;
        /* ปรับขนาดฟอนต์ให้เหมาะสม */
        font-size: 0.85em;
        width: 90%;
        margin: 3px auto 0;
    }

    /* เพิ่มสไตล์สำหรับ card รายวันในส่วนแสดงตารางเรียนรายวัน */
    .daily-schedule-item {
        max-width: 600px; /* จำกัดความกว้างของแต่ละรายการ */
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 15px;
    }
}

/* เพิ่ม Media Query สำหรับจอใหญ่ */
@media (min-width: 901px) {
    /* กำหนดสไตล์ใหม่สำหรับ event-item บนจอใหญ่ */
    .event-item {
        /* บังคับให้ข้อความอยู่ในบรรทัดเดียว */
        white-space: nowrap; 
        /* ซ่อนส่วนที่ล้นออกมา */
        overflow: hidden; 
        /* แสดงเครื่องหมายจุดไข่ปลา (...) เมื่อข้อความล้น */
        text-overflow: ellipsis; 
        /* ตั้งค่าความกว้างสูงสุดเพื่อให้ข้อความไม่ยืดยาวเกินไป */
        max-width: 100px; 
        /* เพื่อให้การตัดข้อความมีประสิทธิภาพ ควรใช้ flex-shrink: 0; */
        flex-shrink: 0; 
    }
}
</style>
<body>
    <?php
    require_once ("component/header.php")
    ?>
<div class="schedule-container">
     <div class="calendar-header">
            <button class="calendar-nav-btn" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
            <h2 id="currentMonthYear"></h2>
            <button class="calendar-nav-btn" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
        </div>
    <div class="calendar-card">
       
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

<div id="dailyScheduleDisplay" class="daily-schedule-display-container" style="width: 100%;max-width: 900px;margin: auto; padding: 20px; padding-bottom: 80px; padding-top: 0px;"></div>

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



<script>
    const scheduleData = <?= $json_schedule; ?>;
    const allMonthScheduleData = <?= $json_all_month_schedule; ?>;
    const studentsData = <?= $json_students; ?>;

    const calendarGrid = document.getElementById('calendarGrid');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const dailyScheduleDisplay = document.getElementById('dailyScheduleDisplay');
    const cameraModal = $('#cameraModal');
    const webcamElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('canvas');
    const takePhotoBtn = document.getElementById('takePhotoBtn');
    const confirmCheckinBtn = document.getElementById('confirmCheckinBtn');

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
        const todayDateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

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
                    eventElement.textContent = cls.subject;
                    eventContainer.appendChild(eventElement);
                });
                dayElement.appendChild(eventContainer);
            }

            dayElement.addEventListener('click', () => {
                showDailySchedule(dateStr);
            });

            calendarGrid.appendChild(dayElement);
        }
    }

    function showDailySchedule(dateStr) {
        const classes = allMonthScheduleData.filter(cls => cls.date === dateStr);
        
        let htmlContent = `<div style="color:#555;"class="schedule-header-inline"><h3>ตารางเรียนวันที่ ${formatDateThai(dateStr)}</h3></div>`;

        if (classes && classes.length > 0) {
            htmlContent += `<div class="daily-schedule-list">`;
            classes.forEach(cls => {
                const statusText = cls.status === 'checked_in' ? 
                    `<span class="status-text"><i class="fas fa-check-circle"></i> เช็คอินแล้ว</span>` :
                    `<span class="status-text-not-checked-in"></span>`;

                const checkinButtonHtml = cls.status === 'checked_in'
                    ? ``
                    : `<div class="btn-checkin-container" style="padding-top:5px;"><button class="btn-checkin" >ยังไม่เช็คอิน</button></div>`;

                htmlContent += `
                    <div class="daily-schedule-item ${cls.status === 'checked_in' ? 'checked-in' : 'not-checked-in'}">
                        <div class="subject">${cls.subject}</div>
                        <div class="date-time">${formatDateThai(cls.date)} • ${cls.time}</div>
                        ${statusText}
                        ${checkinButtonHtml}
                    </div>
                `;
            });
            htmlContent += `</div>`;
        } else {
            // แก้ไขตรงนี้
            htmlContent += `<p class="no-events-message">ไม่มีตารางเรียนในวันนี้ครับ 🙂</p>`;
        }
        
        dailyScheduleDisplay.innerHTML = htmlContent;
    }

    function initiateCheckIn(classId) {
        currentClassId = classId;
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
                
                cameraModal.modal('hide');
                renderCalendar();
                showDailySchedule(classToUpdate.date);
                Swal.fire({
                    title: "สำเร็จ!",
                    text: "เช็คอินเรียบร้อยแล้ว",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }, 1500);
    });

    cameraModal.on('hidden.bs.modal', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
    
    function formatDateThai(dateStr) {
        const [year, month, day] = dateStr.split('-');
        const d = new Date(year, month - 1, day);
        const dayStr = d.getDate();
        const monthStr = monthNames[d.getMonth()];
        const yearStr = d.getFullYear() + 543;
        return `${dayStr} ${monthStr} ${yearStr}`;
    }

    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
        dailyScheduleDisplay.innerHTML = ''; 
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
        dailyScheduleDisplay.innerHTML = '';
    });

    function initialize() {
        renderCalendar();
        const today = new Date();
        const todayDateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        showDailySchedule(todayDateStr);
    }

    // Initial render
    initialize();
</script>

    <?php
    require_once ("component/footer.php")
    ?>
</body>
</html>