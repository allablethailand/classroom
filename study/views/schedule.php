<?php

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


date_default_timezone_set('Asia/Bangkok'); // or your timezone

function getWeekDays($baseDate = null)
{
    // Return an array of 7 days objects for the week (Sunday to Saturday)
    if (!$baseDate) {
        $baseDate = new DateTime(); // today
    }
    // Adjust to Sunday of this week
    $dayOfWeek = (int)$baseDate->format('w'); // 0 (Sun) - 6 (Sat)
    $startOfWeek = clone $baseDate;
    $startOfWeek->modify("-{$dayOfWeek} days");

    $weekDays = [];
    for ($i = 0; $i < 7; $i++) {
        $d = clone $startOfWeek;
        $d->modify("+{$i} days");

        $weekDays[] = (object)[
            'date' => $d->format('Y-m-d'),
            'displayDate' => $d->format('j'), // Day Number without leading 0
            'name' => $d->format('D'), // e.g., Sun, Mon
            'isToday' => $d->format('Y-m-d') === (new DateTime())->format('Y-m-d'),
        ];
    }
    return $weekDays;
}

$months = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
];
$selectedMonth = date('F');

// Simulate getTimelineData function by generating some dummy data
function getTimelineData($date)
{
    // For demo: even dates have classes
    if (intval(date('j', strtotime($date))) % 2 == 0) {
        return ['class1', 'class2']; // some dummy array when classes exist
    }
    return [];
}

// Initial week (today)
$weekDays = getWeekDays();
$selectedDate = (new DateTime())->format('Y-m-d'); // today

// Program Name
$program_name = 'Green Tech Leadership (GTL) รุ่นที่ 1';
$program_slogan = '"CONNECT LOCAL TO GLOBAL"';


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
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/schedule.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    
    <style>
        .sweet-alert h2 {
            font-family: 'Kanit', sans-serif !important;
        }

        .sweet-alert p {
            font-family: 'Kanit', sans-serif !important;
        }

        .sa-confirm-button-container button {
            font-family: 'Kanit', sans-serif !important;
        }
    </style>
</head>

<body>
    <?php require_once("component/header.php"); ?>

    <div style="min-height:140vh;">
        <div class="container-fluid" style="margin-top: 1rem;">
            <div class="schedule-section">
                <!-- <div class="schedule-header">
                    <h3 class="schedule-title-card">Class Schedule</h3>
                </div> -->

                <div class="calendar-container">
                    <!-- change to next day -->
                    <button
                        id="prev-day"
                        class="day-nav-button prev"
                        aria-label="Previous day"
                        type="button">
                        ‹
                    </button>
                    <span id="current-date" style="margin: 0 1rem; font-weight: bold; font-size: 2rem;">
                        Tue, 9 Aug, 2025
                    </span>

                    <!-- change to next day -->
                    <button
                        id="next-day"
                        class="day-nav-button next"
                        aria-label="Next day"
                        type="button">
                        ›
                    </button>
                </div>
            </div>

            <div class="featured-class">
                <div class="featured-header">
                    <div>
                        <h2 class="featured-title"><?php echo $program_name; ?></h2>
                        <p class="featured-time"><?php echo $program_slogan; ?></p>
                        <p><?php echo "01/10/2025 - 15/10/2025"; ?></p>
                    </div>
                </div>
                <div class="featured-decoration-1"></div>
                <div class="featured-decoration-2"></div>
            </div>

            <?php foreach ($arrayData as $index => $item) {
                $isLast = ($index === count($arrayData) - 1) ? ' last' : ''; ?>
                <div class="schedule-container<?php echo $isLast; ?>">
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
                                    <h3 class="schedule-title" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo $item['morning_session_details']; ?>
                                    </h3>
                                    <p class="schedule-duration">
                                        <?php
                                        // Fixing typo keys: should be morning_session_time and evening_session_time
                                        echo isset($item['morning_session_time']) ? $item['morning_session_time'] : $item['evening_session_time'];
                                        ?>
                                    </p>
                                </div>
                                <span class="schedule-badge badge-class"><?php echo isset($item['morning_session_speaker']) ? $item['morning_session_speaker'] : 'ยังไม่ระบุ'; ?></span>
                            </div>

                            <div class="schedule-footer">
                                <div class="member-avatars">
                                    <div class="member-avatar avatar-purple"><span>👤</span></div>
                                    <div class="member-avatar avatar-teal"><span>👤</span></div>
                                    <div class="member-avatar avatar-orange"><span>👤</span></div>
                                </div>
                                <!-- <span class="member-count"><?php echo $item['morning_session_speaker']; ?></span> -->
                                <button type="button" class="btn btn-primary" style="background-color: #7936e4;  border-radius: 15px;"
                                    data-toggle="modal"
                                    data-target="#scheduleModal"
                                    data-index="<?php echo $index; ?>">
                                    รายละเอียด
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>

            <!-- First Modal -->
            <div id="scheduleModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content custom-modal-color">
                        <div class="modal-header custom-header-color">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="scheduleModalLabel">Schedule Detail</h4>
                        </div>
                        <div class="modal-body">
                            <p id="modalDetails"></p>
                            <p id="modalTime"></p>
                            <p id="modalSpeakers"></p>
                            <!-- Button to open second modal -->
                            <div class="" style="text-align: right;">
                                <button type="button" class="btn btn-primary open-new-modal" data-toggle="modal" data-target="#newModal">
                                    เข้าร่วม
                                </button>
                                <button type="button" class="btn btn-secondary decline-modal" data-toggle="modal" style="margin-left:  10px;">
                                    ปฎิเสธ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Modal -->
            <div id="newModal" class="modal fade" tabindex="-2" role="dialog" aria-labelledby="newModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content custom-modal-color-2">
                        <div class="modal-header custom-header-color-2">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="newModalLabel">Join Event</h4>
                        </div>
                        <div class="modal-body" style="text-align: center;">
                            <!-- Content of the second modal -->
                            <p>เช็คอินเพื่อเข้าร่วมอีเว้นท์นี้เลยใช่มั้ย</p>
                            <div style="display: flex; margin:auto">
                                <p><b>ช่วงเวลาระหว่าง: </b>
                                <p id="modalTimeNew" style="margin-left: 10px;"></p>
                                </p>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary accept-event" data-dismiss="modal">ตกลง</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php require_once("component/footer.php"); ?>

    </div>



</body>
</html>