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

if (isset($_POST) && $_POST['action'] == 'fetch_schedules') {
    $dateSchedule = $_POST['date_range'];

    // course logo
    // w.image_path,

    $scheduleItems = select_data(
        "w.workshop_name as schedule_name,
            DATE_FORMAT(w.date_start,'%Y/%m/%d') as date_start, 
            DATE_FORMAT(w.time_start,'%H:%i') as time_start, 
            DATE_FORMAT(w.time_end,'%H:%i') as time_end",
        "ot_workshop w INNER JOIN ot_training_topic_setup tts ON w.workshop_id = tts.workshop_id 
            INNER JOIN classroom_course cc ON tts.trn_id = cc.course_ref_id",
        "cc.classroom_id = 2 
            AND cc.status = 0 
            AND tts.status = 0 
            AND tts.workshop_id IS NOT NULL 
            AND tts.workshop_id <> ''
            AND w.date_start = '{$dateSchedule}'"
    );

    $schedule_data = $scheduleItems[0];

    echo json_encode([
        'status' => true,
        'group_data' => [
            'schedule_name' => $schedule_data['schedule_name'],
            'schedule_date' => $schedule_data['date_start'],
            'schedule_start' => $schedule_data['time_start'],
            'schedule_end' => $schedule_data['time_end'],
        ]
    ]);
}


// DATA FROM JS
$input = json_decode(file_get_contents('php://input'), true);
// Receive JSON data from POST body
if (isset($input['action']) && $input['action'] == 'fetch_mydata') {
    $sessions = isset($input['sessions']) ? $input['sessions'] : [];
    $date = isset($input['date']) ? $input['date'] : '';

    $timestamp = strtotime($date);
    $displayDate = $timestamp ? date('d/m/Y', $timestamp) : '';

    if (empty($sessions)) {
        http_response_code(400); // or 500 depending on error type
        echo json_encode(['message' => 'Session is null or not set']);
        exit;
    }

    // Sanitize output helper
    function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    // Render the schedule HTML for these sessions
    foreach ($sessions as $index => $item):
        $isLast = ($index === count($sessions) - 1) ? ' last' : '';
        $startTime = isset($item['event_start']) ? $item['event_start'] : '';
        $endTime = isset($item['event_end']) ? $item['event_end'] : '';

?>
        <div class="schedule-container<?= $isLast ?>">
            <div class="schedule-item">
                <div class="schedule-time">
                    <span class="schedule-time-text"><?= $startTime ?></span>
                    <span class="schedule-time-bottom"><?= $endTime ?></span>
                </div>

                <div class="schedule-timeline">
                    <div class="timeline-dot timeline-dot-purple"></div>
                    <div class="timeline-line"></div>
                </div>

                <div class="schedule-content schedule-content-purple">
                    <div class="schedule-header">
                        <div>
                            <h3 class="schedule-title" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= escape($item['session_detail']) ?>
                            </h3>
                            <p class="schedule-duration">
                                <?= escape($displayDate) . " • " . escape($startTime) ?><?= $endTime ? ' - ' . escape($endTime) : ''; ?> 
                            </p>
                        </div>
                        <span class="schedule-badge badge-class">
                            <?= isset($item['session_speaker']) && $item['session_speaker'] ? escape($item['session_speaker']) : 'ยังไม่ระบุ' ?>
                        </span>
                    </div>

                    <div class="schedule-footer">
                        <div class="member-avatars">
                            <div class="member-avatar avatar-purple"><span>👤</span></div>
                            <div class="member-avatar avatar-teal"><span>👤</span></div>
                            <div class="member-avatar avatar-orange"><span>👤</span></div>
                        </div>
                        <button type="button" class="btn btn-primary" style="background-color: #7936e4; border-radius: 15px;"
                            data-toggle="modal"
                            data-target="#scheduleModal-<?= $index ?>"
                            data-index="<?= $index ?>">
                            รายละเอียด
                        </button>

                        <div id="scheduleModal-<?= $index ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content custom-modal-color">
                                    <div class="modal-header custom-header-color">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title" id="scheduleModalLabel">Schedule Detail</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p id="modalDetails"><strong>รายละเอียด:</strong>  <?= escape($item['session_detail']) ?> </p>
                                        <p id="modalTime"><strong>ช่วงเวลาระหว่าง:</strong>  <?= escape($startTime) ?><?= $endTime ? ' - ' . escape($endTime) : ''; ?> </p>
                                        <p id="modalSpeakers"><strong>วิทยากร:</strong>  <?= isset($item['session_speaker']) && $item['session_speaker'] ? escape($item['session_speaker']) : 'ยังไม่ระบุ'; ?>  </p>

                                        <div class="" style="text-align: right;">
                                            <button type="button" class="btn btn-primary open-new-modal" data-toggle="modal" data-target="#newModal-<?= $index ?>">
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


                        <div id="newModal-<?= $index ?>" class="modal fade" tabindex="-2" role="dialog" aria-labelledby="newModalLabel">
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
                                            <p><b>ช่วงเวลาระหว่าง: </b></p>
                                            <p id="modalTimeNew" style="margin-left: 10px;"> <?= escape($startTime) ?><?= $endTime ? ' - ' . escape($endTime) : ''; ?> </p>
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
                </div>
            </div>
        </div>
<?php endforeach;
}

?>