<?php
    session_start();
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

    if(isset($_POST) && $_POST['action'] == 'fetch_schedules') {
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
    if (isset($input['action']) && $input['action'] == 'fetch_mydata') 
    {
        $sessions = isset($input['sessions']) ? $input['sessions'] : [];
        $date = isset($input['date']) ? $input['date'] : '';

        if(empty($sessions))
        {
            http_response_code(400); // or 500 depending on error type
            echo json_encode(['message' => 'Session is null or not set']);
            exit;
        }

        // Sanitize output helper
        function escape($string) {
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
                                <?= escape($startTime) ?><?= $endTime ? ' - ' . escape($endTime) : '' ?>
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
                            data-target="#scheduleModal"
                            data-index="<?= $index ?>">
                            รายละเอียด
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; 
    }
  
?>