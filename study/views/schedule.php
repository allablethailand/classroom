<?php

error_reporting(E_ALL & ~E_NOTICE);

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

// Program Name
$program_name = 'Green Tech Leadership (GTL) รุ่นที่1';
$program_slogan = '"CONNECT LOCAL TO GLOBAL"';

// Day 1
$date1 = '2025-10-01';
$event_location1 = 'พัทยา/ชลบุรี';
$morning_session_time1 = '09:30-12:00';
$morning_session_details1 = 'ลงทะเบียนผู้เข้าอบรม, รายงานตัว, ตัดสูท, ถ่ายรูป, แจกเสื้อโปโล หมวก, ป้ายชื่อ, สแกน QR เข้ากลุ่ม 3 กลุ่ม, sign PDPA, สมุดโทรศัพท์';
$afternoon_session_time1 = '13:00-17:00';
$afternoon_session_details1 = 'พิธีเปิด ประธานกล่าวเปิดหลักสูตร, ผอ.หลักสูตร อธิบายรายละเอียดหลักสูตร, กิจกรรมละลายพฤติกรรม';
$evening_session_time1 = '18:00';
$evening_session_details1 = 'แต่ละกลุ่มคุยเรื่องการแสดงโชว์ในช่วงกินเลี้ยง, กินเลี้ยง, แสดงโชว์แต่ละกลุ่ม ("หลักสูตร เป็นเจ้าภาพจัดเลี้ยง")';

// Day 2
$date2 = '2025-10-02';
$event_location2 = 'พัทยา'; // Assumed from subsequent days
$morning_session_time2 = '9.30-12.00';
$morning_session_details2 = 'รับฟังการบรรยาย AI, หัวข้อ: Deep drive in AI';
$morning_session_speaker2 = 'พี่กฤษ';
$afternoon_session_time2 = '13.00-16.00';
$afternoon_session_details2 = 'รับฟังการบรรยาย AI, หัวข้อ: Knowledge Base and Business AI in Organization';
$afternoon_session_speaker2 = 'พี่กฤษ';

// Day 3
$date3 = '2025-10-03';
$event_location3 = 'พัทยา';
$morning_session_time3 = '9.30-12.00';
$morning_session_details3 = 'รับฟังการบรรยาย, หัวข้อ: Green : Shift & Sustainability Landscape';
$morning_session_speaker3 = 'พี่เบนซ์';
$afternoon_session_time3 = '13.00-16.30';
$afternoon_session_details3 = 'รับฟังการบรรยาย, หัวข้อ: กลยุทธ์และธรรมมาภิบาล ESG';
$afternoon_session_speaker3 = 'พี่เบนซ์';
$evening_session_time3 = '18:00';
$evening_session_details3 = 'กลุ่มดิน เป็นเจ้าภาพจัดเลี้ยง';

// Day 4
$date4 = '2025-10-04';
$event_location4 = 'พัทยา';
$morning_session_time4 = '9.30-12.00';
$morning_session_details4 = 'รับฟังการบรรยาย AI, หัวข้อ: AWS Deep AI Technology';
$morning_session_speaker4 = 'พี่กฤษ';
$afternoon_session_time4 = '13.00-16.30';
$afternoon_session_details4 = 'รับฟังการบรรยาย AI, หัวข้อ: Transform your organization by Huawei cloud';
$afternoon_session_speaker4 = 'พี่กฤษ';
$evening_session_time4 = '18:00';
$evening_session_details4 = 'กลุ่มน้ำ เป็นเจ้าภาพจัดเลี้ยง';

// Day 5-8
$date5_8 = '2025-10-05 to 2025-10-08';
$event_location5_8 = 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน';
$event_details5_8 = 'เยี่ยมชมองค์กร และโครงการต้นแบบ';

// Day 9
$date9 = '2025-10-09';
$event_location9 = 'พัทยา';
$morning_session_time9 = '9.30-12.00';
$morning_session_details9 = 'รับฟังการบรรยาย, หัวข้อ: การเงินสีเขียว & ความเสี่ยงสภาพภูมิอากาศ';
$morning_session_speaker9 = 'พี่เบนซ์';
$afternoon_session_time9 = '13.00-16.30';
$afternoon_session_details9 = 'รับฟังการบรรยาย, หัวข้อ: Green Innovation & Cirular Models';
$afternoon_session_speaker9 = 'พี่เบนซ์';
$evening_session_time9 = '18:00';
$evening_session_details9 = 'กลุ่มลม เป็นเจ้าภาพจัดเลี้ยง';

// Day 10
$date10 = '2025-10-10';
$event_location10 = 'พัทยา';
$morning_session_time10 = '9.30-12.00';
$morning_session_details10 = 'รับฟังการบรรยาย AI, หัวข้อ: Digital Transformation by AI in Organization';
$morning_session_speaker10 = 'พี่กฤษ';
$afternoon_session_time10 = '13.00-16.30';
$afternoon_session_details10 = 'รับฟังการบรรยาย AI, หัวข้อ: Organization Digital Technology';
$afternoon_session_speaker10 = 'พี่กฤษ';
$evening_session_time10 = '18:00';
$evening_session_details10 = 'กลุ่มไฟ เป็นเจ้าภาพจัดเลี้ยง';

// Day 11
$date11 = '2025-10-11';
$event_location11 = 'พัทยา';
$morning_session_time11 = '9.30-12.00';
$morning_session_details11 = 'รับฟังการบรรยาย, หัวข้อ: Sector Deep Dive (เลือกตามกลุ่มเป้าหมาย)';
$morning_session_speaker11 = 'พี่เบนซ์';
$afternoon_session_time11 = '13.00-16.30';
$afternoon_session_details11 = 'รับฟังการบรรยาย, หัวข้อ: ผู้นำ องค์กร และอนาคต';
$afternoon_session_speaker11 = 'พี่เบนซ์';
$evening_session_time11 = '18:00';
$evening_session_details11 = 'กลุ่มหลักสูตร เป็นเจ้าภาพจัดเลี้ยง';

// Day 12
$date12 = '2025-10-12';
$event_location12 = 'พัทยา'; // Assumed from subsequent days
$morning_session_time12 = '9.30-12.00';
$afternoon_session_time12 = '14.30-16.00';
$event_details12 = 'เยี่ยมชม โรงงาน';

// Day 13
$date13 = '2025-10-13';
$event_location13 = 'พัทยา';
$morning_session_time13 = '9.30-12.00';
$morning_session_details13 = 'รับฟังการบรรยาย, หัวข้อ: การพัฒนาอุตสหกรรมสู่สังคมคาร์บอนเครดิตต่ำ ในสถานประกอบการ';
$morning_session_speaker13 = 'เจ้อัง';
$afternoon_session_time13 = '13.00-16.30';
$afternoon_session_details13 = 'รับฟังการบรรยาย, หัวข้อ: การส่งเสริมยกระดับมาตรฐานสถานประกอบการสู่อุตสาหกรรมสีเขียว';
$afternoon_session_speaker13 = 'เจ้อัง';
$evening_session_time13 = '18:00';
$evening_session_details13 = '**กลุ่มดิน+น้ำ เป็นเจ้าภาพจัดเลี้ยง';

// Day 14
$date14 = '2025-10-14';
$event_location14 = 'พัทยา';
$morning_session_time14 = '9.30-12.00';
$morning_session_details14 = 'รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย';
$morning_session_speaker14 = 'เจ้อัง';
$afternoon_session_time14 = '13.00-16.30';
$afternoon_session_details14 = 'รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC';
$afternoon_session_speaker14 = 'อ.จุฬา (เจ้อัง)';
$evening_session_time14 = '18:00';
$evening_session_details14 = '**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง';

// Day 15
$date15 = '2025-10-15';
$event_location15 = 'พัทยา';
$morning_session_time15 = '9.30-16.00';
$morning_session_details15 = 'สรุปประสบการณ์และผลการเรียนรู้ ปิดหลักสูตร';
$evening_session_time15 = '18:00';
$evening_session_details15 = 'หลักสูตรเป็นเจ้าภาพจัดเลี้ยง, theme กาล่าดินเนอร์ เดินพรมแดง';

$arrayData = [
    [
        'date' => '2025-10-01',
        'event_location' => 'พัทยา/ชลบุรี',
        'morning_session_time' => '09:30-12:00',
        'morning_session_details' => 'ลงทะเบียนผู้เข้าอบรม, รายงานตัว, ตัดสูท, ถ่ายรูป, แจกเสื้อโปโล หมวก, ป้ายชื่อ, สแกน QR เข้ากลุ่ม 3 กลุ่ม, sign PDPA, สมุดโทรศัพท์',
        'morning_session_speaker' => null,
        'afternoon_session_time' => '13:00-17:00',
        'afternoon_session_details' => 'พิธีเปิด ประธานกล่าวเปิดหลักสูตร, ผอ.หลักสูตร อธิบายรายละเอียดหลักสูตร, กิจกรรมละลายพฤติกรรม',
        'afternoon_session_speaker' => null,
        'evening_session_time' => '18:00',
        'evening_session_details' => 'แต่ละกลุ่มคุยเรื่องการแสดงโชว์ในช่วงกินเลี้ยง, กินเลี้ยง, แสดงโชว์แต่ละกลุ่ม ("หลักสูตร เป็นเจ้าภาพจัดเลี้ยง")',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-02',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย AI, หัวข้อ: Deep drive in AI',
        'morning_session_speaker' => 'พี่กฤษ',
        'afternoon_session_time' => '13.00-16.00',
        'afternoon_session_details' => 'รับฟังการบรรยาย AI, หัวข้อ: Knowledge Base and Business AI in Organization',
        'afternoon_session_speaker' => 'พี่กฤษ',
        'evening_session_time' => null,
        'evening_session_details' => null,
        'event_details' => null,
    ],
    [
        'date' => '2025-10-03',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย, หัวข้อ: Green : Shift & Sustainability Landscape',
        'morning_session_speaker' => 'พี่เบนซ์',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย, หัวข้อ: กลยุทธ์และธรรมมาภิบาล ESG',
        'afternoon_session_speaker' => 'พี่เบนซ์',
        'evening_session_time' => '18:00',
        'evening_session_details' => 'กลุ่มดิน เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-04',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย AI, หัวข้อ: AWS Deep AI Technology',
        'morning_session_speaker' => 'พี่กฤษ',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย AI, หัวข้อ: Transform your organization by Huawei cloud',
        'afternoon_session_speaker' => 'พี่กฤษ',
        'evening_session_time' => '18:00',
        'evening_session_details' => 'กลุ่มน้ำ เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-05 to 2025-10-08',
        'event_location' => 'ดูงานต่างประเทศ, เซินเจิ้น ประเทศจีน',
        'morning_session_time' => null,
        'morning_session_details' => null,
        'morning_session_speaker' => null,
        'afternoon_session_time' => null,
        'afternoon_session_details' => null,
        'afternoon_session_speaker' => null,
        'evening_session_time' => null,
        'evening_session_details' => null,
        'event_details' => 'เยี่ยมชมองค์กร และโครงการต้นแบบ',
    ],
    [
        'date' => '2025-10-09',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย, หัวข้อ: การเงินสีเขียว & ความเสี่ยงสภาพภูมิอากาศ',
        'morning_session_speaker' => 'พี่เบนซ์',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย, หัวข้อ: Green Innovation & Cirular Models',
        'afternoon_session_speaker' => 'พี่เบนซ์',
        'evening_session_time' => '18:00',
        'evening_session_details' => 'กลุ่มลม เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-10',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย AI, หัวข้อ: Digital Transformation by AI in Organization',
        'morning_session_speaker' => 'พี่กฤษ',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย AI, หัวข้อ: Organization Digital Technology',
        'afternoon_session_speaker' => 'พี่กฤษ',
        'evening_session_time' => '18:00',
        'evening_session_details' => 'กลุ่มไฟ เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-11',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย, หัวข้อ: Sector Deep Dive (เลือกตามกลุ่มเป้าหมาย)',
        'morning_session_speaker' => 'พี่เบนซ์',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย, หัวข้อ: ผู้นำ องค์กร และอนาคต',
        'afternoon_session_speaker' => 'พี่เบนซ์',
        'evening_session_time' => '18:00',
        'evening_session_details' => 'กลุ่มหลักสูตร เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-12',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => null,
        'morning_session_speaker' => null,
        'afternoon_session_time' => '14.30-16.00',
        'afternoon_session_details' => null,
        'afternoon_session_speaker' => null,
        'evening_session_time' => null,
        'evening_session_details' => null,
        'event_details' => 'เยี่ยมชม โรงงาน',
    ],
    [
        'date' => '2025-10-13',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย, หัวข้อ: การพัฒนาอุตสหกรรมสู่สังคมคาร์บอนเครดิตต่ำ ในสถานประกอบการ',
        'morning_session_speaker' => 'เจ้อัง',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย, หัวข้อ: การส่งเสริมยกระดับมาตรฐานสถานประกอบการสู่อุตสาหกรรมสีเขียว',
        'afternoon_session_speaker' => 'เจ้อัง',
        'evening_session_time' => '18:00',
        'evening_session_details' => '**กลุ่มดิน+น้ำ เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-14',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-12.00',
        'morning_session_details' => 'รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย',
        'morning_session_speaker' => 'เจ้อัง',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC',
        'afternoon_session_speaker' => 'อ.จุฬา (เจ้อัง)',
        'evening_session_time' => '18:00',
        'evening_session_details' => '**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ],
    [
        'date' => '2025-10-15',
        'event_location' => 'พัทยา',
        'morning_session_time' => '9.30-16.00',
         'morning_session_details' => 'รับฟังการบรรยาย, หัวข้อ: แนวการจัดการกากอุตสาหกรรมตามหลักกฎหมาย',
        'morning_session_speaker' => 'เจ้อัง',
        'afternoon_session_time' => '13.00-16.30',
        'afternoon_session_details' => 'รับฟังการบรรยาย, หัวข้อ: โอกาสทองของอุตสาหกรรมกับพื้นที่ EEC',
        'afternoon_session_speaker' => 'อ.จุฬา (เจ้อัง)',
        'evening_session_time' => '18:00',
        'evening_session_details' => '**กลุ่มลม+ไฟ เป็นเจ้าภาพจัดเลี้ยง',
        'event_details' => null,
    ]
    ];


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
  <link rel="stylesheet" href="/classroom/study/css/schedule.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="/dist/css/sweetalert.css">
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
  <script src="/classroom/study/js/menu.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
  <?php require_once("component/header.php"); ?>
  <div class="" style="min-height:140vh;">
    <div class="container-fluid" style="margin-top: 2rem;">
      <div class="">
        
      <div class="featured-class">
        <div class="featured-header">
          <div>
            <h2 class="featured-title"><?php echo $program_name; ?></h2>
            <p class="featured-time"><?php echo $program_slogan; ?></p>
            <p><?php echo "01/10/2025 - 15/10/2025" ?></p>
          </div>
          <!-- <svg class="featured-more-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z" />
          </svg> -->
        </div>

        <!-- <div class="featured-professor">
          <div class="professor-avatar-container">
            <div class="professor-avatar">
              <span style="font-size: 20px;">👨‍🏫</span>
            </div>
          </div>
          <div>
            <p class="professor-name">Mr. Cody Fisher</p>
            <p class="professor-title">Professor</p>
          </div> -->
        </div>

        <div class="featured-decoration-1"></div>
        <div class="featured-decoration-2"></div>
      </div>
      <?php foreach($arrayData as $item) { 

      // 'date' => '2025-10-01',
      //   'event_location' => 'พัทยา/ชลบุรี',
      //   'morning_session_time' => '09:30-12:00',
      //   'morning_session_details' => 'ลงทะเบียนผู้เข้าอบรม, รายงานตัว, ตัดสูท, ถ่ายรูป, แจกเสื้อโปโล หมวก, ป้ายชื่อ, สแกน QR เข้ากลุ่ม 3 กลุ่ม, sign PDPA, สมุดโทรศัพท์',
      //   'morning_session_speaker' => null,
      //   'afternoon_session_time' => '13:00-17:00',
      //   'afternoon_session_details' => 'พิธีเปิด ประธานกล่าวเปิดหลักสูตร, ผอ.หลักสูตร อธิบายรายละเอียดหลักสูตร, กิจกรรมละลายพฤติกรรม',
      //   'afternoon_session_speaker' => null,
      //   'evening_session_time' => '18:00',
      //   'evening_session_details' => 'แต่ละกลุ่มคุยเรื่องการแสดงโชว์ในช่วงกินเลี้ยง, กินเลี้ยง, แสดงโชว์แต่ละกลุ่ม ("หลักสูตร เป็นเจ้าภาพจัดเลี้ยง")',
      //   'event_details' => null,
      
      ?>
      <div class="schedule-container">
        <div class="schedule-item">
          <div class="schedule-time">
            <span class="schedule-time-text"><?php echo $item['date']; ?></span>
          </div>

          <div class="schedule-timeline">
            <div class="timeline-dot timeline-dot-purple"></div>
            <div class="timeline-line"></div>
          </div>

          <div class="schedule-content schedule-content-purple">
            <div class="schedule-header">
              <div>
                <h3 class="schedule-title" style=" display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;"><?php echo $item['morning_session_details']; ?></h3>
                <p class="schedule-duration"><?php echo isset($item['morning_sesstion_time']) ? $item['morning_sesstion_time'] : $item['evening_sesstion_time']; ?></p>
              </div>
              <span class="schedule-badge badge-class">Class</span>
            </div>

            <div class="schedule-footer">
              <div class="member-avatars">
                <div class="member-avatar avatar-purple">
                  <span>👤</span>
                </div>
                <div class="member-avatar avatar-teal">
                  <span>👤</span>
                </div>
                <div class="member-avatar avatar-orange">
                  <span>👤</span>
                </div>
              </div>
              <span class="member-count"><?php echo $item['morning_session_speaker']; ?></span>
            </div>
          </div>
        </div>
      <?php }?>

        <!-- <div class="schedule-item">
          <div class="schedule-time">
            <span class="schedule-time-text">13 pm</span>
          </div>

          <div class="schedule-timeline">
            <div class="timeline-dot timeline-dot-teal"></div>
            <div class="timeline-line"></div>
          </div>

          <div class="schedule-content schedule-content-teal">
            <div class="schedule-header">
              <div>
                <h3 class="schedule-title">Geometry</h3>
                <p class="schedule-duration">14 pm - 16 pm</p>
              </div>
              <span class="schedule-badge badge-consultation">Consultation</span>
            </div>

            <div class="schedule-footer">
              <div class="member-avatars">
                <div class="member-avatar avatar-purple">
                  <span>👤</span>
                </div>
                <div class="member-avatar avatar-teal">
                  <span>👤</span>
                </div>
                <div class="member-avatar avatar-orange">
                  <span>👤</span>
                </div>
              </div>
              <span class="member-count">+ 7 members</span>
            </div>
          </div>
        </div> -->
      </div>
    
      
      <!-- <div class="schedule-item">
        <div class="schedule-time">
          <span class="schedule-time-text"><?php echo $item['date']; ?></span>
        </div>

        <div class="schedule-timeline">
          <div class="timeline-dot timeline-dot-orange"></div>
        </div>

        <div class="schedule-content schedule-content-orange">
          <div class="schedule-header">
            <div>
              <h3 class="schedule-title"></h3>
              <p class="schedule-duration"></p>
            </div>
            <span class="schedule-badge badge-class">Class</span>
          </div>

          <div class="schedule-footer">
            <div class="member-avatars">
              <div class="member-avatar avatar-purple">
                <span>👤</span>
              </div>
              <div class="member-avatar avatar-teal">
                <span>👤</span>
              </div>
              <div class="member-avatar avatar-orange">
                <span>👤</span>
              </div>
            </div>
            <span class="member-count">+ 7 members</span>
          </div>
        </div>
      </div> -->



      <?php require_once("component/footer.php"); ?>
    </div>


</body>

</html>