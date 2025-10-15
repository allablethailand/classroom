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
require_once $base_include . '/classroom/study/actions/student_func.php'; 
    
$student_id = getStudentId();

if (isset($_POST) && $_POST['action'] == 'fetch_schedules') {
    $dateSchedule = $_POST['date_range'];

    $classroom_id = getStudentClassroomId($student_id);


    $scheduleItems = select_data(
        "course.trn_subject AS course_name,
			course.trn_detail AS course_detail,
            categories.categories_name AS course_category,
            cc.course_id AS course_id,
            DATE_FORMAT(course.trn_date,'%Y/%m/%d') AS date_start,
            course.trn_from_time AS time_start,
            course.trn_to_time AS time_end",
        "ot_training_list course 
            LEFT JOIN classroom_course cc 
            ON course.trn_id = cc.course_ref_id
        JOIN ot_training_categories categories ON course.categories_id = categories.categories_id",
        "WHERE cc.classroom_id = '{$classroom_id}'
            AND cc.status = 0 
            AND course.status = 0 
            AND course.trn_date = '{$dateSchedule}' 
            ORDER BY time_start ASC");


//     $instructor_column = "*";
//     $instructor_table = "(SELECT 
//     sch.schedule_name,
//     sch.course_id,
//     sch.topic_name,
//     sch.date_start,
//     sch.time_start,
//     sch.time_end,
//     coach.coach_id,
//     coach.coach_type,
//     coach.coach_name,
//     coach.coach_image,
//     coach.coach_gender,
//     coach.coach_academy,
//     coach.coach_position
// FROM
//     (
//         SELECT 
//             w.workshop_name AS schedule_name,
//             cc.course_id AS course_id,
//             tts.topic_name AS topic_name,
//             DATE_FORMAT(w.date_start,'%Y/%m/%d') AS date_start,
//             DATE_FORMAT(w.time_start,'%H:%i') AS time_start,
//             DATE_FORMAT(w.time_end,'%H:%i') AS time_end
//         FROM 
//             ot_workshop AS w
//         INNER JOIN 
//             ot_training_topic_setup AS tts ON w.workshop_id = tts.workshop_id
//         INNER JOIN 
//             classroom_course AS cc ON tts.trn_id = cc.course_ref_id
//         WHERE 
//             cc.classroom_id = '{$classroom_id}'
//             AND cc.status = 0
//             AND tts.status = 0
//             AND tts.workshop_id IS NOT NULL
//             AND tts.workshop_id <> ''
//             AND w.date_start = '{$dateSchedule}'
//     ) sch
//     LEFT JOIN
//     (
//         SELECT 
//             emp.emp_id AS coach_id, 
//             'emp' AS coach_type, 
//             CONCAT(
//                 CASE  
//                     WHEN i.title = 1 OR i.title = 'Mr.' THEN 'Mr.'
//                     WHEN i.title = 2 OR i.title = 'Mrs.' THEN 'Mrs.'
//                     WHEN i.title = 4 OR i.title = 'Miss.' THEN 'Miss.'
//                     WHEN i.title = 5 OR i.title = 'Dr.' THEN 'Dr.'
//                     ELSE ''
//                 END,
//                 ' ',
//                 COALESCE(i.firstname, i.firstname_th),
//                 ' ',
//                 COALESCE(i.lastname, i.lastname_th)
//             ) AS coach_name, 
//             i.emp_pic AS coach_image, 
//             i.gender AS coach_gender, 
//             comp.comp_description AS coach_academy, 
//             pos.posi_description AS coach_position,
//             course.trn_id AS course_id
//         FROM 
//             ot_training_list course
//         JOIN 
//             m_employee emp ON FIND_IN_SET(emp.emp_id, course.trn_by_emp)
//         LEFT JOIN 
//             m_employee_info i ON i.emp_id = emp.emp_id
//         LEFT JOIN 
//             m_company comp ON comp.comp_id = emp.comp_id
//         LEFT JOIN 
//             m_position pos ON pos.posi_id = emp.posi_id
//         UNION ALL
//         SELECT 
//             cont.cus_cont_id AS coach_id, 
//             'cont' AS coach_type, 
//             CONCAT(IFNULL(m_customer_cont_group.cont_group_name, ''), ' ', cont.cus_cont_name, ' ', cont.cus_cont_surname) AS coach_name, 
//             cont.cus_cont_photo AS coach_image, 
//             'O' AS coach_gender, 
//             COALESCE(cus.cus_name_en, cus.cus_name_th) AS coach_academy, 
//             cont.cus_posi_id AS coach_position,
//             course.trn_id AS course_id
//         FROM 
//             ot_training_list course
//         JOIN 
//             m_customer_contact cont ON FIND_IN_SET(cont.cus_cont_id, course.trn_by_cont)
//         LEFT JOIN 
//             m_customer cus ON cus.cus_id = cont.cus_id
//         LEFT JOIN 
//             m_customer_cont_group ON m_customer_cont_group.cont_group_id = cont.cont_group_id
//     ) coach ON sch.course_id = coach.course_id
//         ORDER BY sch.time_start ASC) schedule_data";

    $instructor_column = "*";
    $instructor_table = "(
        SELECT 
            emp.emp_id AS coach_id, 
            'emp' AS coach_type, 
            CONCAT((
                    CASE  
                        WHEN i.title = 1 or i.title = 'Mr.' THEN 'Mr.'
                        WHEN i.title = 2 or i.title = 'Mrs.' THEN 'Mrs.'
                        WHEN i.title = 4 or i.title = 'Miss.' THEN 'Miss.'
                        WHEN i.title = 5 or i.title = 'Dr.' THEN 'Dr.'
                        ELSE ''
                    END
                ),
                ' ',COALESCE(i.firstname, i.firstname_th), ' ', COALESCE(i.lastname, i.lastname_th)) AS coach_name, 
            i.emp_pic AS coach_image, 
            i.gender AS coach_gender, 
            comp.comp_description AS coach_academy, 
            pos.posi_description AS coach_position,
			course.trn_subject AS course_name,
			course.trn_detail AS course_detail,
            cc.course_id AS course_id,
            DATE_FORMAT(course.trn_date,'%Y/%m/%d') AS date_start,
            course.trn_from_time AS time_start,
            course.trn_to_time AS time_end
        FROM 
            ot_training_list course
        JOIN 
            m_employee emp ON FIND_IN_SET(emp.emp_id, REPLACE(course.trn_by_emp, ' ', ''))
        LEFT JOIN
            classroom_course cc ON course.trn_id = cc.course_ref_id
        LEFT JOIN 
            m_employee_info i ON i.emp_id = emp.emp_id 
        LEFT JOIN 
            m_company comp ON comp.comp_id = emp.comp_id 
        LEFT JOIN 
            m_position pos ON pos.posi_id = emp.posi_id
        WHERE 
            cc.classroom_id = '{$classroom_id}'
            AND course.trn_date = '{$dateSchedule}') schedule_data";

        // UNION ALL
        // SELECT 
        //     teacher_id,
        //     teacher_ref_id,
        //     teacher_ref_type,
        //     teacher_perfix,
        //     teacher_firstname_en,
        //     teacher_lastname_en,
        //     teacher_firstname_th,
        //     teacher_lastname_th,
        //     teacher_nickname_en,
        //     teacher_nickname_th,
        //     teacher_idcard,
        //     teacher_passport,
        //     teacher_image_profile,
        //     teacher_card_front,
        //     teacher_card_back,
        //     teacher_email,
        //     teacher_mobile,
        //     teacher_address,
        //     teacher_birth_date,
        //     teacher_bioteacher_attach_document,
        //     teacher_education,
        //     teacher_experience,
        //     teacher_company,
        //     teacher_position,
        //     teacher_username,
        //     teacher_password,
        //     teacher_password_key,
        //     position_id,
        //     comp_id,
        //     status,
        //     emp_create,
        //     date_create,
        //     emp_modify,
        //     date_modify
        //     FROM teacher;
        // FROM 
        //     classroom_teacher course 

    $instructor_data = select_data($instructor_column, $instructor_table);

    if (!empty($scheduleItems)) {
        // $schedule_data = $scheduleItems[0];
        echo json_encode([
            'status' => true,
            'group_data' => $scheduleItems,
            'instructor' => $instructor_data,
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'group_data' => [],
            'instructor' => []
        ]);
    }
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
                                        <p id="modalDetails"><strong>รายละเอียด:</strong> <?= escape($item['session_detail']) ?> </p>
                                        <p id="modalTime"><strong>ช่วงเวลาระหว่าง:</strong> <?= escape($startTime) ?><?= $endTime ? ' - ' . escape($endTime) : ''; ?> </p>
                                        <p id="modalSpeakers"><strong>วิทยากร:</strong> <?= isset($item['session_speaker']) && $item['session_speaker'] ? escape($item['session_speaker']) : 'ยังไม่ระบุ'; ?> </p>

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