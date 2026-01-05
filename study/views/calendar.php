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

// require_once("actions/login.php"); ดึงไฟล์ที่จำเป็นเข้ามาใช้งาน

// require_once($base_include."/lib/connect_sqli.php");
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
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>
</head>
<style>
/* ตั้งค่า Font และพื้นหลังโดยรวมให้ดูสะอาดตา */
body {
    background-color: #f5f7fa;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", 'Kanit', sans-serif;
    padding: 0;
    margin: 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.container {
    padding-right: 0px;
    padding-left: 0px;
    margin-right: auto;
    margin-left: auto;
}

/* Container หลัก */
.schedule-container {
    width: 100%;
    max-width: 900px;
    padding-bottom: 20px;
    margin-left: auto;
    margin-right: auto;
}

/* Desktop Layout - Side by Side */
@media (min-width: 992px) {
    .container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Wrapper for flex layout */
    .desktop-flex-wrapper {
        display: flex;
        gap: 30px;
        align-items: flex-start;
    }
    
    /* Left panel - Daily Schedule */
    .schedule-left-panel {
        flex: 1;
        min-width: 0;
        max-width: 300px;
        order: 2;
    }
    
    /* Right panel - Calendar */
    .schedule-right-panel {
        flex: 0 0 600px;
        position: sticky;
        top: 20px;
        order: 1;
    }
    
    .schedule-container {
        max-width: none;
        margin: 0;
        padding-bottom: 0;
    }
    
    #dailyScheduleDisplay {
        max-width: none;
        padding: 0;
        padding-bottom: 20px;
        width: 100%;
    }
    
    /* Calendar styling for desktop */
    .calendar-card {
        background-color: #fff !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    }

    /* ซ่อนปฏิทินแบบเลื่อนบน Desktop */
    #multiMonthCalendarContainer {
        display: none;
    }

    /* ซ่อนปุ่ม Monthly Schedule บน Desktop */
    #monthlyScheduleButton {
        display: none !important;
    }

    /* แสดง Calendar แบบเดิมบน Desktop */
    .desktop-calendar-wrapper {
        display: block !important;
    }
    
    /* *** การแก้ไขสำหรับ Desktop Grid: ปรับขนาดช่องวันให้เป็น 1/7 และคงที่ *** */
    .calendar-grid {
        grid-template-columns: repeat(7, 1fr) !important; /* บังคับให้เป็น 7 ช่องเสมอ */
    }
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
    background-color: #ffffff;
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
    box-shadow: 0 10px 30px rgb(193 220 242 / 47%);
    /* ลบขอบมนด้านบนสำหรับ Mobile Infinite Scroll */
    border-top-left-radius: 0; 
    border-top-right-radius: 0;
}

/* ส่วนแสดงเดือนและปี และปุ่มควบคุม (ซ่อนสำหรับ Mobile Infinite Scroll) */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: #fff;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}
.calendar-header h2 {
    font-weight: 600;
    font-size: 1.5em;
    color: #1a202c;
    margin: 0;
}

/* ปุ่มนำทาง (Prev/Next Month) */
.calendar-nav-btn {
    background: none;
    border: none;
    font-size: 2.0em;
    color: #4a5568;
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    transition: all 0.2s;
}
.calendar-nav-btn:hover {
    color: #007aff;
    background-color: #f0f4f8;
}

/* Grid สำหรับวันในสัปดาห์ */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr); /* บังคับให้เป็น 7 ช่องเสมอ */
    gap: 0;
    text-align: center;
    padding: 10px 0 5px 0;
}

/* ชื่อวันในสัปดาห์ */
.calendar-weekday {
    font-weight: 500;
    font-size: 1.3em;
    color: #a0aec0;
    padding-bottom: 5px;
}
.calendar-weekday:nth-child(1) { color: #ff4040ff; }
.calendar-weekday:nth-child(2) { color: #555; }
.calendar-weekday:nth-child(3) { color: #555; }
.calendar-weekday:nth-child(4) { color: #555; }
.calendar-weekday:nth-child(5) { color: #555; }
.calendar-weekday:nth-child(6) { color: #555; }
.calendar-weekday:nth-child(7) { color: #805ad5; }

/* ช่องวันในปฏิทิน */
.calendar-day {
    position: relative;
    background-color: transparent;
    padding: 10px 5px;
    border-radius: 12px;
    min-height: 60px;
    /* *** การแก้ไข: ลบ aspect-ratio ออก เพื่อให้ grid ยืดตามความสูงและมี 7 วันต่อแถวเสมอ *** */
    /* aspect-ratio: 1 / 1; */
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    box-sizing: border-box;
    /* *** เพิ่มความกว้างขั้นต่ำ (เผื่อกรณียืดหยุ่น) *** */
    min-width: 14.28%; /* 100% / 7 */
}
.calendar-day:hover {
    background-color: #ffffffff;
    transform: none;
}
.calendar-day:hover .day-number {
    color: #d87e75 !important;
    font-weight: 700;
}

/* ตัวเลขวัน */
.day-number {
    font-size: 1.5em;
    font-weight: 500;
    color: #4a5568;
    padding: 5px;
    width: 30px;
    height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    line-height: 1;
}

/* วันที่ที่ไม่ได้อยู่ในเดือนปัจจุบัน */
.calendar-day.inactive {
    color: #ffffffff;
    cursor: default;
}
.calendar-day.inactive .day-number {
    color: #ffffffff;
}
.calendar-day.inactive:hover {
    background-color: transparent;
}

/* วันที่ปัจจุบัน (Today) */
.calendar-day.today {
    background-color: #ffffffff;
}
.calendar-day.today .day-number {
    color: #ff9900 !important;
    font-weight: 700;
}

/* วันที่มีการเลือก */
.calendar-day.selected {
    background-color: #fce4ec;
    border: 2px solid #f9a8d4;
}
.calendar-day.selected .day-number {
    color: #d87e75;
}

/* Event Dots Container */
.calendar-day .event-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 3px;
    margin-top: 5px;
    width: 100%;
    min-height: 10px;
}

/* Event Dot (จุดบอกอีเวนต์) */
.calendar-day .event-item {
    display: block;
    width: 6px;
    height: 6px;
    padding: 0;
    border-radius: 50%;
    margin: 0;
    text-indent: -9999px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* สีของ Event Dots */
.event-checked-in {
    background-color: #4299e1;
}
.event-not-checked-in {
    background-color: #805ad5;
}
.event-purple {
    background-color: #805ad5;
}
.event-blue {
    background-color: #4299e1;
}

/* ส่วนแสดงตารางเรียนรายวันด้านล่าง */
.daily-schedule-display-container {
    padding-bottom: 80px;
}

/* ======================================= */
/* *** สไตล์ที่ถูกแก้ไขเพื่อความสวยงาม (Daily/Monthly Card) *** */
/* ======================================= */

/* Item ตารางเรียนรายวัน */
.daily-schedule-item {
    background-color: #fff;
    border-radius: 15px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border-left: 6px solid;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex; 
    flex-direction: column;
}
/* สไตล์เมื่อชี้เมาส์ */
.daily-schedule-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

/* Border สีตามสถานะ */
.daily-schedule-item.checked-in {
    border-left-color: #48bb78; /* Green for Check-in */
}
.daily-schedule-item.not-checked-in {
    border-left-color: #805ad5; /* Purple for Not Check-in */
}

/* Subject/หัวข้อ */
.daily-schedule-item .subject {
    font-size: 1.1em;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 5px;
}

/* Date/Time */
.daily-schedule-item .date-time {
    font-size: 0.9em;
    color: #718096;
    margin-bottom: 8px;
}

/* Status Text */
.daily-schedule-item .status-text {
    font-weight: 600;
    color: #48bb78; /* Green */
    font-size: 0.9em;
    margin-top: 5px;
}
.daily-schedule-item .status-text i {
    margin-right: 5px;
}

/* ปุ่ม "ดูรายละเอียด" / "เช็คอิน" */
.btn-checkin-container {
    padding-top: 10px;
    margin-top: auto;
}

.btn-checkin {
    background-color: #4299e1; /* Blue */
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 15px;
    font-size: 0.9em;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(66, 153, 225, 0.4);
    transition: background-color 0.2s, box-shadow 0.2s, transform 0.1s;
    width: 100%;
}

.btn-checkin:hover {
    background-color: #3182ce;
    box-shadow: 0 4px 8px rgba(66, 153, 225, 0.6);
    transform: translateY(-1px);
}

/* ======================================= */
/* *** สไตล์สำหรับ Monthly Schedule Modal (Popup) *** */
/* ======================================= */
.modal-content {
    border-radius: 15px;
}
.modal-header {
    /* ทำให้ Header ของ Modal ถูกตรึงอยู่ด้านบนเมื่อ Scroll */
    position: sticky;
    top: 0;
    z-index: 1055; /* สูงกว่าเนื้อหา Modal เล็กน้อย */
    background-color: #fff; /* ใส่พื้นหลังสีขาว */
    border-bottom: 1px solid #e9ecef; /* เส้นแบ่งเบาๆ */
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 15px;
}
.modal-body {
    padding: 0 15px 15px 15px; /* ลบ padding ด้านบนออก เพราะ header ถูกตรึงแล้ว */
}
.modal-title {
    font-weight: 700;
    color: #2d3748;
}
/* ปุ่มปิด (X) ใน Modal Header */
.modal-header .close {
    padding: 1rem 1rem;
    margin: -1rem -1rem -1rem auto;
    font-size: 1.5rem;
    color: #a0aec0;
}
.modal-header .close:hover {
    color: #2d3748;
}

/* หัวข้อวันที่ใน Modal */
.monthly-date-header {
    margin-top: 25px !important;
    margin-bottom: 10px;
    color: #4a5568;
    font-size: 1.2em;
    font-weight: 600;
    padding-left: 5px;
    border-bottom: 2px solid #edf2f7; /* เส้นแบ่งวันที่ */
    padding-bottom: 5px;
}


/* ======================================= */
/* *** Media Query สำหรับมือถือ (ปรับปุ่มลอย) *** */
/* ======================================= */
@media (max-width: 991px) {
    .desktop-flex-wrapper {
        display: block;
    }

    .schedule-left-panel {
        order: 2;
    }
    
    .schedule-right-panel {
        position: static !important;
        order: 1;
    }

    .desktop-calendar-wrapper {
        display: none !important;
    }

    .schedule-container {
        padding-bottom: 0;
    }
    
    #multiMonthCalendarContainer {
        height: 75vh;
        overflow-y: scroll;
        -webkit-overflow-scrolling: touch;
        border-radius: 15px;
        background-color: #ffffff;
        box-shadow: 0 10px 30px rgb(193 220 242 / 47%);
    }

    .month-view-wrapper {
        padding: 0 20px 20px 20px;
    }
    
    .month-header-mobile {
        font-weight: 600;
        font-size: 1.8em;
        color: #1a202c;
        margin-top: 20px;
        margin-bottom: 15px;
        padding-left: 5px;
    }

    .daily-schedule-display-container {
        padding-bottom: 100px !important;
    }

    /* ปุ่มลอยแสดงตารางเรียนทั้งเดือน */
    #monthlyScheduleButton {
        position: fixed;
        bottom: 85px; 
        left: 50%;
        transform: translateX(-50%);
        background-color: #805ad5; /* Green: สีใหม่ */
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 12px 25px;
        font-size: 1.1em;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(99, 198, 108, 0.5); /* Shadow สีเขียว */
        z-index: 1000;
        transition: opacity 0.3s, transform 0.3s, background-color 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    #monthlyScheduleButton:hover {
        background-color: #48bb78; /* Darker Green on hover */
    }
    
    .schedule-count {
        background-color: #ff9900;
        color: white;
        border-radius: 50%;
        padding: 4px 8px;
        font-size: 0.8em;
        font-weight: 800;
        min-width: 25px;
        text-align: center;
    }
}
</style>

<body>
    <?php
    require_once ("component/header.php")
    ?>
    <div class="container">
    <h1 class="heading-1" style="padding-left:1em;" data-lang="calendar">ปฏิทิน</h1>
    <div class="divider-1">
        <span></span>
    </div>
    
    <div class="desktop-flex-wrapper ">
        <div class="schedule-right-panel">
            <div class="schedule-container">
                
                <div class="desktop-calendar-wrapper" style="display: none;">
                    <div class="calendar-header">
                        <button class="calendar-nav-btn" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                        <h2 id="currentMonthYear"></h2>
                        <button class="calendar-nav-btn" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="calendar-card">
                        <div class="calendar-grid">
                            <div class="calendar-weekday" data-lang="sunday">Sun</div>
                            <div class="calendar-weekday" data-lang="monday">Mon</div>
                            <div class="calendar-weekday" data-lang="tuesday">Tue</div>
                            <div class="calendar-weekday" data-lang="wednesday">Wed</div>
                            <div class="calendar-weekday" data-lang="thursday">Thu</div>
                            <div class="calendar-weekday" data-lang="friday">Fri</div>
                            <div class="calendar-weekday" data-lang="saturday">Sat</div>
                        </div>
                        <div class="calendar-grid" id="calendarGridDesktop">
                            </div>
                    </div>
                </div>

                <div id="multiMonthCalendarContainer" class="calendar-card">
                    </div>
                
            </div>
        </div>

        <div class="schedule-left-panel">
            <div id="dailyScheduleDisplay" class="daily-schedule-display-container"></div>
        </div>
    </div>
</div>

<button id="monthlyScheduleButton" style="display: none;">
    <i class="fas fa-list-alt"></i>
    <span id="monthlyScheduleText">ตารางเรียนเดือน</span>
    <span class="schedule-count" id="monthlyScheduleCount">0</span>
</button>

<div class="modal fade" id="monthlyScheduleModal" tabindex="-1" role="dialog" aria-labelledby="monthlyScheduleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" style="color:#000;" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="monthlyScheduleModalLabel">ตารางเรียนประจำเดือน</h4>
            </div>
            <div class="modal-body">
                <div id="monthlyScheduleContent">
                    </div>
            </div>
            <div class="modal-footer" style="display:none;">
                <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
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


<script>
    // **ตัวแปรข้อมูล (จาก PHP)**
    // สมมติว่าตัวแปรเหล่านี้ถูกกำหนดค่ามาจาก PHP แล้ว
    const scheduleData = <?= $json_schedule; ?>; // scheduleData: { 'YYYY-MM-DD': [{...}, {...}], ... } สำหรับจุด
    const allMonthScheduleData = <?= $json_all_month_schedule; ?>; // allMonthScheduleData: [{date: 'YYYY-MM-DD', ...}, ...] สำหรับรายละเอียดทั้งหมด
    const studentsData = <?= $json_students; ?>;

    // **DOM Elements**
    const multiMonthCalendarContainer = document.getElementById('multiMonthCalendarContainer');
    const calendarGridDesktop = document.getElementById('calendarGridDesktop'); // สำหรับ Desktop
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const dailyScheduleDisplay = document.getElementById('dailyScheduleDisplay');
    const monthlyScheduleButton = document.getElementById('monthlyScheduleButton');
    const monthlyScheduleModal = $('#monthlyScheduleModal');
    const monthlyScheduleContent = document.getElementById('monthlyScheduleContent');
    const monthlyScheduleText = document.getElementById('monthlyScheduleText');
    const monthlyScheduleCount = document.getElementById('monthlyScheduleCount');
    
    // **Camera Modal (ยังคงเดิม)**
    const cameraModal = $('#cameraModal');
    const webcamElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('canvas');
    const takePhotoBtn = document.getElementById('takePhotoBtn');
    const confirmCheckinBtn = document.getElementById('confirmCheckinBtn');

    // **State Variables**
    let currentDate = new Date();
    let stream;
    let currentClassId = null;
    let lang = localStorage.getItem('lang') || 'TH'; // เปลี่ยน Default เป็น TH
    let desktopMode = window.matchMedia('(min-width: 992px)').matches;
    let activeObserver; // สำหรับ Intersection Observer

    // **Helper Functions**
    function getMonthName(monthIndex) {
        return (typeof translations !== 'undefined' && translations[lang] && translations[lang].months) 
            ? translations[lang].months[monthIndex] 
            : ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'][monthIndex];
    }
    
    function formatDateThai(dateStr) {
        const [year, month, day] = dateStr.split('-');
        const d = new Date(year, month - 1, day);
        const dayStr = d.getDate();
        const monthStr = getMonthName(d.getMonth());
        const yearStr = d.getFullYear() + 543;
        return `${dayStr} ${monthStr} ${yearStr}`;
    }

    function getSchedulesByMonth(year, month) {
        const startOfMonth = `${year}-${String(month + 1).padStart(2, '0')}-01`;
        const endOfMonth = `${year}-${String(month + 2).padStart(2, '0')}-01`;

        return allMonthScheduleData.filter(cls => {
            return cls.date >= startOfMonth && cls.date < endOfMonth;
        });
    }

    function redirectToschedule(dateStr) {
        const url = `schedule?date_range=${encodeURIComponent(dateStr)}`;
        window.location.href = url;
    }

    // **Calendar Renderer (Desktop View)**
    function renderCalendar() {
        if (!desktopMode) return; // ไม่ต้อง render Desktop ถ้าอยู่ใน Mobile Mode

        const container = calendarGridDesktop;
        container.innerHTML = '';
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        currentMonthYear.textContent = `${getMonthName(month)} ${year + 543}`;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        const todayDateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        for (let i = 0; i < firstDay; i++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day inactive';
            container.appendChild(dayElement);
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

            container.appendChild(dayElement);
        }
    }


    // **Multi-Month Calendar Renderer (Mobile Infinite Scroll)**
    function createMonthView(date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        const monthKey = `${year}-${month}`; // Key for the month wrapper
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        const monthSchedules = getSchedulesByMonth(year, month);
        const scheduleCount = monthSchedules.length;

        // Wrapper for the month view
        const monthWrapper = document.createElement('div');
        monthWrapper.className = 'month-view-wrapper';
        monthWrapper.dataset.monthKey = monthKey;
        monthWrapper.dataset.scheduleCount = scheduleCount;

        // Month Header
        const monthHeader = document.createElement('h3');
        monthHeader.className = 'month-header-mobile';
        monthHeader.textContent = `${getMonthName(month)} ${year + 543}`;
        monthWrapper.appendChild(monthHeader);

        // Calendar Weekdays Grid
        const weekdaysGrid = document.createElement('div');
        weekdaysGrid.className = 'calendar-grid';
        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach((day, index) => {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-weekday';
            dayElement.dataset.lang = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][index];
            dayElement.textContent = day;
            weekdaysGrid.appendChild(dayElement);
        });
        monthWrapper.appendChild(weekdaysGrid);

        // Calendar Days Grid
        const daysGrid = document.createElement('div');
        daysGrid.className = 'calendar-grid month-days-grid';
        
        // Add inactive days (padding)
        for (let i = 0; i < firstDay; i++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day inactive';
            daysGrid.appendChild(dayElement);
        }

        // Add days of the month
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
                    eventContainer.appendChild(eventElement);
                });
                dayElement.appendChild(eventContainer);
            }

            dayElement.addEventListener('click', () => {
                // Remove existing selection from all days
                document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
                // Add selection to the clicked day
                dayElement.classList.add('selected');

                showDailySchedule(dateStr);
            });

            daysGrid.appendChild(dayElement);
        }

        monthWrapper.appendChild(daysGrid);
        return monthWrapper;
    }

    function renderInfiniteCalendar() {
        if (desktopMode) return; // ไม่ต้อง render Mobile ถ้าอยู่ใน Desktop Mode

        const container = multiMonthCalendarContainer;
        container.innerHTML = '';
        
        const today = new Date();
        const startMonth = new Date(today.getFullYear(), today.getMonth() - 10, 1); // 10 เดือนก่อนหน้า
        const endMonth = new Date(today.getFullYear(), today.getMonth() + 10, 1); // 10 เดือนถัดไป (รวมเดือนปัจจุบัน)
        
        // 1. Render all 21 months (10 before + current + 10 after)
        for (let d = startMonth; d < endMonth; d.setMonth(d.getMonth() + 1)) {
            const monthView = createMonthView(d);
            container.appendChild(monthView);
        }

        // 2. Scroll to the current month after rendering
        const currentMonthKey = `${today.getFullYear()}-${today.getMonth()}`;
        const currentMonthElement = document.querySelector(`.month-view-wrapper[data-month-key="${currentMonthKey}"]`);
        
        if (currentMonthElement) {
            // Scroll to the current month in the scrollable container
            currentMonthElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // 3. Setup Intersection Observer
        setupIntersectionObserver();
    }

    function showMonthlySchedulePopup(year, month) {
    const monthSchedules = getSchedulesByMonth(year, month);
    const monthName = getMonthName(month);
    const yearThai = year + 543;
    
    $('#monthlyScheduleModalLabel').text(`ตารางเรียนประจำเดือน ${monthName} ${yearThai}`);
    
    let htmlContent = '';
    if (monthSchedules.length > 0) {
        htmlContent += `<div class="daily-schedule-list">`;
        // Group by date and sort
        const schedulesByDate = monthSchedules.reduce((acc, cls) => {
            acc[cls.date] = acc[cls.date] || [];
            acc[cls.date].push(cls);
            return acc;
        }, {});

        // เรียงลำดับวันที่
        Object.keys(schedulesByDate).sort().forEach(dateStr => {
            // ใช้ class ใหม่สำหรับ header วันที่ใน Modal (ตามที่แก้ในโค้ดครั้งก่อน)
            htmlContent += `<h4 class="monthly-date-header">วันที่ ${formatDateThai(dateStr)}</h4>`; 
            
            schedulesByDate[dateStr].forEach(cls => {
                const isCheckedIn = cls.status === 'checked_in';
                
                // 1. สร้าง Status Text
                const statusText = isCheckedIn ? 
                    `<span class="status-text" style="color:#48bb78;"><i class="fas fa-check-circle"></i> เช็คอินแล้ว</span>` :
                    // ใช้ style สีม่วง และไอคอนตามที่กำหนดไว้
                    `<span class="status-text" style="color:#805ad5;"><i class="fas fa-hourglass-half"></i> รอเช็คอิน</span>`;
                    
                // 2. สร้างปุ่ม "ดูรายละเอียด" สำหรับคลาสที่ยังไม่ได้เช็คอินเท่านั้น (เหมือน showDailySchedule)
                const checkinButtonHtml = isCheckedIn
                    ? ``
                    : `<div class="btn-checkin-container">
                        <button class="btn-checkin" onclick="redirectToschedule('${cls.date}')">ดูรายละเอียด / เช็คอิน</button>
                    </div>`;

                htmlContent += `
                    <div class="daily-schedule-item ${isCheckedIn ? 'checked-in' : 'not-checked-in'}">
                        <div class="subject">${cls.subject}</div>
                        <div class="date-time">${cls.time}</div>
                        ${statusText}
                        ${checkinButtonHtml} </div>
                `;
            });
        });

        htmlContent += `</div>`;
    } else {
        htmlContent = `<p class="no-events-message" style="padding: 20px;">ไม่มีตารางเรียนในเดือน ${monthName} ${yearThai} ครับ 🙂</p>`;
    }

    document.getElementById('monthlyScheduleContent').innerHTML = htmlContent;
    monthlyScheduleModal.modal('show');
}

    // Event Listener for the floating button
    monthlyScheduleButton.addEventListener('click', () => {
        const monthKey = monthlyScheduleButton.dataset.activeMonthKey;
        if (monthKey) {
            const [year, month] = monthKey.split('-').map(Number);
            showMonthlySchedulePopup(year, month);
        }
    });

    // **Intersection Observer for Mobile Floating Button**
    function setupIntersectionObserver() {
        if (activeObserver) {
            activeObserver.disconnect();
        }

        const observerOptions = {
            root: multiMonthCalendarContainer,
            rootMargin: '0px',
            threshold: 0.25 // เมื่อ 25% ของเดือนปรากฏใน viewport
        };

        const observerCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const monthElement = entry.target;
                    const monthKey = monthElement.dataset.monthKey;
                    const scheduleCount = parseInt(monthElement.dataset.scheduleCount || 0);
                    const [year, month] = monthKey.split('-').map(Number);
                    
                    // Update Floating Button
                    monthlyScheduleButton.style.display = 'flex';
                    monthlyScheduleButton.dataset.activeMonthKey = monthKey;
                    monthlyScheduleText.textContent = `ตารางเรียนเดือน ${getMonthName(month)}`;
                    monthlyScheduleCount.textContent = scheduleCount;

                    // Update Active Month Header for Modal
                    $('#monthlyScheduleModalLabel').text(`ตารางเรียนประจำเดือน ${getMonthName(month)} ${year + 543}`);
                }
            });
        };

        activeObserver = new IntersectionObserver(observerCallback, observerOptions);
        
        document.querySelectorAll('.month-view-wrapper').forEach(monthElement => {
            activeObserver.observe(monthElement);
        });
    }


    // **Daily Schedule Display (ยังคงเดิม)**
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

        // Scroll to the daily schedule header
        const dailyScheduleHeader = document.getElementById('dailyScheduleHeader');
        if (dailyScheduleHeader) {
            dailyScheduleHeader.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }


    // **Check-in Logic (ยังคงเดิม)**
    function initiateCheckIn(classId) {
        currentClassId = classId;
        // ... (check-in logic remains the same) ...
    }
    
    // **Navigation for Desktop (ยังคงเดิม)**
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

    // **Initialization**
    function initialize() {
        desktopMode = window.matchMedia('(min-width: 992px)').matches;

        if (desktopMode) {
            document.querySelector('.desktop-calendar-wrapper').style.display = 'block';
            multiMonthCalendarContainer.style.display = 'none';
            monthlyScheduleButton.style.display = 'none';
            renderCalendar();
        } else {
            document.querySelector('.desktop-calendar-wrapper').style.display = 'none';
            multiMonthCalendarContainer.style.display = 'block';
            renderInfiniteCalendar();
        }

        const today = new Date();
        const todayDateStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        showDailySchedule(todayDateStr);
    }

    // Run Initialization
    initialize();

    // Re-initialize on window resize (to switch between mobile/desktop views)
    window.addEventListener('resize', () => {
        const newDesktopMode = window.matchMedia('(min-width: 992px)').matches;
        if (newDesktopMode !== desktopMode) {
            initialize();
        }
    });

</script>

    <?php
    require_once ("component/footer.php")
    ?>
</body>
</html>