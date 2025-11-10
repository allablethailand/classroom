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
    require_once $base_include . '/classroom/study/actions/student_func.php'; 
    
    $student_id = getStudentId();
    $class_id = getStudentClassroomId($student_id);
    
    $course_data = select_data(
        "cc.course_type,
        c.trn_id AS course_id,
        c.trn_subject AS course_name,
        c.picture_title AS course_cover,
        c.trn_location AS course_location,
        c.trn_from_time AS course_timestart,
        c.trn_to_time AS course_timeend,
        c.trn_by AS course_instructor,
        c.trn_date AS course_date,
        LENGTH(REPLACE(trn_by, ' ', '')) - LENGTH(REPLACE(REPLACE(trn_by, ' ', ''), ',', '')) + 1 AS trn_count_by
        ",
        "classroom_course AS cc JOIN ot_training_list AS c on cc.course_ref_id = c.trn_id",
        "WHERE cc.classroom_id = '{$class_id}' 
            AND cc.status = 0"
        );

    foreach ($course_data as $course) {
        $formattedDate = $course['course_date'];
        // Prepare time string
        $timeStart = $course['course_timestart'];
        $timeEnd = $course['course_timeend'];
        if (!empty($timeStart) && !empty($timeEnd) && $timeStart !== $timeEnd) {
            $time = $timeStart . ' - ' . $timeEnd;
        } elseif (!empty($timeStart)) {
            $time = $timeStart;
        } else {
            $time = 'TBA'; // or 'ทั้งวัน' if all-day
        }

        // Prepare the entry
        $entry = [
            'subject' => $course['course_name'],
            'time' => $time,
            'status' => 'not_checked_in',  // default value
            'id' => $course['course_id']   // using course_id as unique id
        ];

        // Add to schedule_data grouped by date
        $schedule_data[$formattedDate][] = $entry;
    }

    // Optionally sort by date keys ascending
    ksort($schedule_data);

    // var_dump($schedule_data);

// require_once("actions/login.php");
// // ดึงไฟล์ที่จำเป็นเข้ามาใช้งาน

// require_once($base_include."/lib/connect_sqli.php");
// include_once($base_include."/login_history.php");
// session_start(); // สำคัญมาก: ต้องเรียกใช้ session_start()
global $mysqli;


// --- ส่วน PHP จำลองข้อมูลตารางเรียน (เหมือนเดิม) ---

// $schedule_data = [
//     '2025-10-01' => [
//         ['subject' => 'ลงทะเบียนผู้เข้าอบรม, รายงานตัว, ตัดสูท, ถ่ายรูป, แจกเสื้อโปโล หมวก, ป้ายชื่อ, สแกน QR เข้ากลุ่ม 3 กลุ่ม, sign PDPA, สมุดโทรศัพท์', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 1],
//         ['subject' => 'พิธีเปิด ประธานกล่าวเปิดหลักสูตร, ผอ.หลักสูตร อธิบายรายละเอียดหลักสูตร, กิจกรรมละลายพฤติกรรม', 'time' => '13:00 - 17:00', 'status' => 'not_checked_in', 'id' => 2],
//         ['subject' => 'แต่ละกลุ่มคุยเรื่องการแสดงโชว์ในช่วงกินเลี้ยง, กินเลี้ยง, แสดงโชว์แต่ละกลุ่ม ("หลักสูตร เป็นเจ้าภาพจัดเลี้ยง")', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 3]
//     ],
//     '2025-10-02' => [
//         ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Deep drive in AI', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 4],
//         ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Knowledge Base and Business AI in Organization', 'time' => '13:00 - 16:00', 'status' => 'not_checked_in', 'id' => 5]
//     ],
//     '2025-10-03' => [
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: Green : Shift & Sustainability Landscape', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 6],
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: กลยุทธ์และธรรมมาภิบาล ESG', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 7],
//         ['subject' => 'กลุ่มดิน เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 8]
//     ],
//     '2025-10-04' => [
//         ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: AWS Deep AI Technology', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 9],
//         ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Transform your organization by Huawei cloud', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 10],
//         ['subject' => 'กลุ่มน้ำ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 11]
//     ],
//     '2025-10-05' => [
//         ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 12]
//     ],
//     '2025-10-06' => [
//         ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 13]
//     ],
//     '2025-10-07' => [
//         ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 14]
//     ],
//     '2025-10-08' => [
//         ['subject' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน', 'time' => 'ทั้งวัน', 'status' => 'not_checked_in', 'id' => 15]
//     ],
//     '2025-10-09' => [
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: การเงินสีเขียว & ความเสี่ยงสภาพภูมิอากาศ', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 16],
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: Green Innovation & Cirular Models', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 17],
//         ['subject' => 'กลุ่มลม เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 18]
//     ],
//     '2025-10-10' => [
//         ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Digital Transformation by AI in Organization', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 19],
//         ['subject' => 'รับฟังการบรรยาย AI, หัวข้อ: Organization Digital Technology', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 20],
//         ['subject' => 'กลุ่มไฟ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 21]
//     ],
//     '2025-10-11' => [
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: Sector Deep Dive (เลือกตามกลุ่มเป้าหมาย)', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 22],
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: ผู้นำ องค์กร และอนาคต', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 23],
//         ['subject' => 'กลุ่มหลักสูตร เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 24]
//     ],
//     '2025-10-12' => [
//         ['subject' => 'เยี่ยมชม โรงงาน', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 25],
//         ['subject' => 'เยี่ยมชม โรงงาน', 'time' => '14:30 - 16:00', 'status' => 'not_checked_in', 'id' => 26]
//     ],
//     '2025-10-13' => [
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: การพัฒนาอุตสหกรรมสู่สังคมคาร์บอนเครดิตต่ำ ในสถานประกอบการ', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 27],
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: การส่งเสริมยกระดับมาตรฐานสถานประกอบการสู่อุตสาหกรรมสีเขียว', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 28],
//         ['subject' => '**กลุ่มดิน+น้ำ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 29]
//     ],
//     '2025-10-14' => [
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย', 'time' => '09:30 - 12:00', 'status' => 'not_checked_in', 'id' => 30],
//         ['subject' => 'รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC', 'time' => '13:00 - 16:30', 'status' => 'not_checked_in', 'id' => 31],
//         ['subject' => '**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 32]
//     ],
//     '2025-10-15' => [
//         ['subject' => 'สรุปประสบการณ์และผลการเรียนรู้ ปิดหลักสูตร', 'time' => '09:30 - 16:00', 'status' => 'not_checked_in', 'id' => 33],
//         ['subject' => 'หลักสูตรเป็นเจ้าภาพจัดเลี้ยง, theme กาล่าดินเนอร์ เดินพรมแดง', 'time' => '18:00', 'status' => 'not_checked_in', 'id' => 34]
//     ]
// ];


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
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/calendar.js?v=<?php echo time(); ?>" type="text/javascript"></script>

</head>
<style>
/* ตั้งค่า Font และพื้นหลังโดยรวมให้ดูสะอาดตา */
body {
    background-color: #f5f7fa; /* สีพื้นหลังอ่อน ๆ คล้ายในรูป */
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", 'Kanit', sans-serif;
    padding: 0;
    margin: 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Container หลัก */
.schedule-container {
    width: 100%;
    max-width: 900px;
    /* margin: auto; */
    padding-bottom: 20px;
    margin-left: auto;
    margin-right: auto;
    width: 100%;
}

/* Header ของปฏิทิน: "Calendar" และปุ่มค้นหา */
.calendar-header-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px 20px 20px;
    max-width: 900px;
    margin: auto;
}
.calendar-header-main h1 {
    font-size: 2em;
    font-weight: 700;
    color: #1a202c;
    margin: 0;
}
.search-btn {
    background: none;
    border: none;
    font-size: 1.8em;
    color: #1a202c;
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    transition: background-color 0.2s;
}
.search-btn:hover {
    background-color: #e2e8f0;
}

/* Calendar Card */
.calendar-card {
    background-color: #ebf5ff;
    border-radius: 20px; /* มุมโค้งมนให้ดูทันสมัย */
    /* padding: 15px; */
    box-shadow: 0 10px 30px rgb(193 220 242 / 47%); /* เงาบางเบา */
}

/* ส่วนแสดงเดือนและปี และปุ่มควบคุม */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px; /* ลด padding จากเดิม */
    background-color: #fff; /* ลบพื้นหลังสีเทาออก */
}
.calendar-header h2 {
    font-weight: 600;
    font-size: 1.5em;
    color: #1a202c;
    margin: 0;
}

/* ปุ่มนำทาง (Prev/Next Month) - ทำให้ใหญ่และดู Minimal */
.calendar-nav-btn {
    background: none;
    border: none;
    font-size: 2.0em; /* ทำให้ไอคอนใหญ่ขึ้น */
    color: #4a5568; /* สีเทาเข้ม */
    cursor: pointer;
    padding: 10px; /* เพิ่ม padding เพื่อให้กดง่ายขึ้น */
    border-radius: 50%;
    transition: all 0.2s;
}
.calendar-nav-btn:hover {
    color: #007aff; /* เปลี่ยนสีเมื่อโฮเวอร์ */
    background-color: #f0f4f8;
}

/* Grid สำหรับวันในสัปดาห์ */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0; /* ลบ gap เดิมออก */
    text-align: center;
    padding: 10px 0 5px 0;
}

/* ชื่อวันในสัปดาห์ */
.calendar-weekday {
    font-weight: 500;
    font-size: 1.3em;
    color: #a0aec0; /* สีเทาอ่อน */
    padding-bottom: 5px;
}
/* สไตล์สีวันในสัปดาห์ (คงไว้ตามเดิมหากต้องการสีเฉพาะ) */
.calendar-weekday:nth-child(1) { color: #ff4040ff; } /* อาทิตย์: แดงอ่อน */
.calendar-weekday:nth-child(2) { color: #555; } /* จันทร์: ส้มอ่อน */
.calendar-weekday:nth-child(3) { color: #555; } /* อังคาร: ส้ม */
.calendar-weekday:nth-child(4) { color: #555; } /* พุธ: เขียว */
.calendar-weekday:nth-child(5) { color: #555; } /* พฤหัส: ม่วง */
.calendar-weekday:nth-child(6) { color: #555; } /* ศุกร์: น้ำเงิน */
.calendar-weekday:nth-child(7) { color: #805ad5; } /* เสาร์: ชมพู */

/* ช่องวันในปฏิทิน */
.calendar-day {
    position: relative;
    background-color: transparent; /* ลบพื้นหลังออกจากแต่ละช่อง */
    /* *** แก้ไขที่นี่: เพิ่ม padding ด้านบนและล่าง เพื่อให้ช่องดูสูงขึ้น *** */
    padding: 10px 5px;
    border-radius: 12px; /* มุมโค้งมน */
    /* *** แก้ไขที่นี่: เพิ่มความสูงขั้นต่ำ *** */
    min-height: 60px; /* เพิ่มจาก 50px */
    aspect-ratio: 1 / 1; /* ทำให้เป็นสี่เหลี่ยมจัตุรัส */
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    box-sizing: border-box;
}
.calendar-day:hover {
    background-color: #ffffffff; /* สีโฮเวอร์อ่อนๆ */
    transform: none; /* ลบ animation ยกขึ้น */
}
.calendar-day:hover .day-number {
    color: #d87e75 !important;
    font-weight: 700;
}

/* ตัวเลขวัน */
.day-number {
    font-size: 1.5em;
    font-weight: 500;
    color: #4a5568; /* สีตัวเลขปกติ */
    padding: 5px;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    line-height: 1; /* จัดตำแหน่งตัวเลขให้ตรงกลาง */
}

/* วันที่ที่ไม่ได้อยู่ในเดือนปัจจุบัน */
.calendar-day.inactive {
    color: #cbd5e0; /* สีเทาจางมาก */
    cursor: default;
}
.calendar-day.inactive .day-number {
    color: #cbd5e0;
}
.calendar-day.inactive:hover {
    background-color: transparent;
}

/* วันที่ปัจจุบัน (Today) */
.calendar-day.today {
    background-color: #ffffffff; /* พื้นหลังอ่อนๆ */
    /* border: 2px solid #007aff; ขอบสีน้ำเงิน */
}
.calendar-day.today .day-number {
    /* background-color: #007aff; วงกลมสีน้ำเงินเข้ม */
    color: #ff9900 !important;
    font-weight: 700;
}

/* วันที่มีการเลือก (คล้ายกับวันที่ 23 ในรูป) */
.calendar-day.selected {
    background-color: #fce4ec; /* สีพื้นหลังอ่อนๆ เช่น สีชมพู */
    border: 2px solid #f9a8d4; /* ขอบสีหลัก */
}
.calendar-day.selected .day-number {
    color: #ffffffff; /* สีตัวเลขในวงกลม */
}

/* Event Dots Container */
.calendar-day .event-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 3px; /* ระยะห่างระหว่างจุด */
    margin-top: 5px;
    width: 100%;
    min-height: 10px;
}

/* Event Dot (จุดบอกอีเวนต์) */
.calendar-day .event-item {
    display: block;
    width: 6px; /* ขนาดจุด */
    height: 6px; /* ขนาดจุด */
    padding: 0;
    border-radius: 50%;
    margin: 0;
    text-indent: -9999px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* สีของ Event Dots (อ้างอิงจากสีในรูป) */
.event-checked-in {
    background-color: #4299e1; /* สีเขียว: เสร็จสิ้น */
}
.event-not-checked-in {
    background-color: #805ad5; /* สีส้ม: ยังไม่เช็คอิน */
}
/* ตัวอย่างสีอื่น ๆ (ถ้ามี) */
/* สีม่วง (Purple) - สำหรับงานอื่น */
.event-purple {
    background-color: #805ad5;
}
/* สีฟ้า (Blue) - สำหรับงานอื่น */
.event-blue {
    background-color: #4299e1;
}

/* ส่วนแสดงตารางเรียนรายวันด้านล่าง */
.daily-schedule-display-container {
    padding: 20px;
    padding-bottom: 80px;
}
.daily-schedule-list {
    padding: 0;
}

/* Item ตารางเรียนรายวัน */
.daily-schedule-item {
    background-color: #fff;
    border-radius: 15px; /* มุมโค้งมน */
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border-left: 6px solid;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.daily-schedule-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}
.daily-schedule-item.checked-in {
    border-left-color: #48bb78; /* เขียว */
}
.daily-schedule-item.not-checked-in {
    border-left-color: #ae80f061; /* ส้ม */
}
.daily-schedule-item .subject {
    /* font-weight: 700; */ 
    font-size: 1.1em;
    /* color: #1a202c; */
    /* margin-bottom: 5px; */
}
.daily-schedule-item .date-time {
    color: #718096;
    font-size: 1em;
    margin-bottom: 10px;
}
.daily-schedule-item .status-text {
    color: #48bb78;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}
.daily-schedule-item .status-text-not-checked-in {
    /* ซ่อนสถานะที่ไม่เช็คอิน (ให้เหลือแค่ปุ่ม) */
    display: none;
}
.no-events-message {
    text-align: center;
    color: #a0aec0;
    font-size: 1.1em;
    font-weight: 500;
    padding: 40px 20px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    margin-top: 20px;
    border: 1px dashed #e2e8f0;
}

/* ปุ่มเช็คอิน (ทำให้ใหญ่และสวยงาม) */
.btn-checkin-container {
    padding-top: 10px;
}
.btn-checkin {
    background-color: #f6ad55; /* สีส้ม */
    color: #fff;
    border: none;
    border-radius: 12px; /* โค้งมนสวยงาม */
    /* padding: 12px 25px; ทำให้ปุ่มใหญ่ขึ้น */
    font-size: .9em; /* ตัวอักษรใหญ่ขึ้น */
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
    width: 50%; /* เต็มความกว้าง */
    box-shadow: 0 4px 10px rgba(246, 173, 85, 0.4);
}
.btn-checkin:hover {
    background-color: #ed8936;
    transform: translateY(-1px);
}
.btn-checkin:active {
    transform: translateY(0);
}

/* สไตล์ Modal (Popup) - ทำให้ดูสะอาดตาและทันสมัย */
#cameraModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    border: none;
}
#cameraModal .modal-header {
    border-bottom: none;
    padding: 20px 20px 0 20px;
}
#cameraModal .modal-title {
    font-weight: 700;
    color: #1a202c;
}
#cameraModal .modal-body p {
    color: #4a5568;
}
#cameraModal .modal-footer {
    border-top: none;
    padding: 10px 20px 20px 20px;
    display: flex;
    justify-content: center; /* จัดปุ่มให้อยู่กึ่งกลาง */
    gap: 15px;
}

/* ปุ่มใน Modal (ทำให้ใหญ่และดูดีขึ้น) */
#cameraModal .btn {
    padding: 12px 25px; /* ทำให้ปุ่มใหญ่ขึ้น */
    font-size: 1.1em;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.2s;
}
#cameraModal .btn-default {
    background-color: #e2e8f0;
    color: #4a5568;
    border: none;
}
#cameraModal .btn-default:hover {
    background-color: #cbd5e0;
}
#cameraModal .btn-primary {
    background-color: #4299e1; /* สีน้ำเงิน */
    color: #fff;
    border: none;
}
#cameraModal .btn-primary:hover {
    background-color: #3182ce;
}

/* ปุ่มปิด Modal (X) - ทำให้ใหญ่ขึ้นตามคำขอ */
#cameraModal .close {
    font-size: 2.5em; /* ทำให้ใหญ่ขึ้นมาก */
    opacity: 0.5;
    transition: opacity 0.2s;
}
#cameraModal .close:hover {
    opacity: 0.9;
}


/* Media Query สำหรับมือถือ (เน้นจุด) */
@media (max-width: 768px) {
    .calendar-card {
        /* padding: 10px; */
        border-radius: 15px;
    }
    .calendar-day {
        /* *** แก้ไขที่นี่: ปรับ padding และ min-height บนมือถือให้สูงขึ้น *** */
        min-height: 50px; /* เพิ่มจาก 40px */
        padding: 8px 5px; /* เพิ่ม padding */
    }
    .day-number {
        font-size: 1.3em;
        width: 25px;
        height: 25px;
    }
    .calendar-nav-btn {
        font-size: 1.5em; /* ลดขนาดปุ่มนำทางเล็กน้อยบนมือถือ */
        padding: 5px;
    }
    
    /* ซ่อนข้อความในจุดอีเวนต์ (เพื่อให้เหลือแต่จุด) */
    .calendar-day .event-item {
        width: 6px;
        height: 6px;
        margin: 1px;
    }
    .container{
    margin-left: 0px;
    margin-right: 0px;
}
}

.container{
    margin-left: auto;
    margin-right: auto;
}
</style>

<body>
    <?php
    require_once ("component/header.php")
    ?>
    <div class="container">
        <div class="schedule-container">
            <div class="calendar-header">
                    <button class="calendar-nav-btn" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                    <h2 id="currentMonthYear"></h2>
                    <button class="calendar-nav-btn" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                </div>
            <div class="calendar-card">
            
                <div class="calendar-grid">
                    <div class="calendar-weekday">Sun</div>
                    <div class="calendar-weekday">Mon</div>
                    <div class="calendar-weekday">Tue</div>
                    <div class="calendar-weekday">Wed</div>
                    <div class="calendar-weekday">Thu</div>
                    <div class="calendar-weekday">Fri</div>
                    <div class="calendar-weekday">Sat</div>
                </div>
                <div class="calendar-grid" id="calendarGrid">
                </div>
            </div>
        </div>
    </div>
<div id="dailyScheduleDisplay" class="daily-schedule-display-container" style="width: 100%;max-width: 500px;margin: auto; padding: 20px; padding-bottom: 80px; padding-top: 0px;"></div>

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

    const monthNames = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"];

    function redirectToschedule(dateStr) {
        // Construct the URL dynamically with the dateStr
        const url = `schedule?date_range=${encodeURIComponent(dateStr)}`;
        window.location.href = url;
    }

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

    let htmlContent = `<div id="dailyScheduleHeader" style="color:#555; "class="schedule-header-inline"><h3 style="font-size: 16px;">ตารางเรียนวันที่ ${formatDateThai(dateStr)}</h3></div>`;

    if (classes && classes.length > 0) {
        htmlContent += `<div class="daily-schedule-list">`;
        classes.forEach(cls => {
            const statusText = cls.status === 'checked_in' ? 
                `<span class="status-text"><i class="fas fa-check-circle"></i> เช็คอินแล้ว</span>` :
                `<span class="status-text-not-checked-in"></span>`;

            const checkinButtonHtml = cls.status === 'checked_in'
                ? ``
                : `<div class="btn-checkin-container" style="padding-top:5px;">
                        <button class="btn-checkin" onclick="redirectToschedule('${cls.date}')">ดูรายละเอียด</button>
                    </div>`;
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
        htmlContent += `<p class="no-events-message">ไม่มีตารางเรียนในวันนี้ครับ 🙂</p>`;
    }

    dailyScheduleDisplay.innerHTML = htmlContent;

    // โค้ดที่เพิ่มเข้ามาใหม่
    const dailyScheduleHeader = document.getElementById('dailyScheduleHeader');
    if (dailyScheduleHeader) {
        dailyScheduleHeader.scrollIntoView({ behavior: 'smooth' });
    }
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