<?php

// iniate session start before calling this function
function recheckUserSession() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;  // Session is active and user is logged in
    }
    return false; // No active session or user not logged in
}

function getStudentId() {
    return isset($_SESSION['student_id']) ? (int)$_SESSION['student_id'] : null;
}


function getStudentClassroomId($student_id) {
    $result = select_data("classroom_id", "classroom_student_join", "WHERE student_id = '{$student_id}'");
    return $result ? $result[0]['classroom_id'] : null;
}

// function ซ้ำ
function getStudentAlumni($student_id) {
    $result = select_data("classroom_id", "classroom_template", "WHERE student_id = '{$student_id}'");
    return $result ? $result[0]['classroom_id'] : false;
}

function getStudentGroupId($student_id) {
    $result = select_data("group_id", "classroom_group", "WHERE student_id = '{$student_id}'");
    return $result ? $result[0]['group_id'] : null;
}

// function getStudentCourseId($classroom_id) {
//     $result = select_data("course_id", "classroom_course", "WHERE classroom_id = '{$classroom_id}'");
//     return $result ? $result[0]['course_id'] : null;
// }

// $our_class = $student_class[0]["classroom_id"];

// $columnCourseGroup  = ", ctp.classroom_information, ctp.classroom_poster, ctp.classroom_student, count(student.join_id) as classroom_register,";
// $tableCourseGroup = "classroom_template ctp";
// $whereCourseGroup = "LEFT JOIN classroom_group cg ON ctp.classroom_id = cg.classroom_id WHERE ctp.classroom_id = '{$our_class}'";

function getAlumniClassroom($student_id){
    $result = select_data(
        "template.classroom_id, template.classroom_name, COUNT(student.join_id) as classroom_register, classroom_student, COUNT(cg.group_id) AS group_count, template.classroom_information, template.classroom_poster",
        "classroom_template template",
        "LEFT JOIN classroom_group cg ON cg.classroom_id = template.classroom_id 
        LEFT JOIN classroom_student_join student ON student.classroom_id = template.classroom_id 
        WHERE student.status = 0 AND student.student_id = '{$student_id}'"
    );

    return !empty($result) ? $result : [];
}


function getStudentClassroomList($student_id) {
   // Adjusted SQL condition to separate JOIN and WHERE clauses if needed
    $result = select_data(
        "csj.classroom_id AS classroom_id, ct.classroom_name AS classroom_name",
        "classroom_student_join csj",
        "LEFT JOIN classroom_template ct ON csj.classroom_id = ct.classroom_id WHERE student_id = '{$student_id}'"
    );

    return !empty($result) ? $result : [];
}

function getStudentClassroomCourseList($student_id, $classroom_id) {
    $result = select_data(
        "cc.course_id, cc.course_type, cc.course_ref_id, csj.classroom_name",
        "classroom_course cc",
        "LEFT JOIN classroom_student_join csj ON cc.classroom_id = csj.classroom_id WHERE cc.classroom_id = '{$classroom_id}' AND cc.status = 0 AND csj.student_id = '{$student_id}'"
    );

    return !empty($result) ? $result : [];
}

function getStudentClassroomCourseAll($student_id, $classroom_id) {
    $result = select_data(
        "cc.course_id, cc.course_type, cc.course_ref_id, csj.classroom_name",
        "classroom_course cc",
        "LEFT JOIN classroom_student_join csj ON cc.classroom_id = csj.classroom_id 
        LEFT JOIN ot_training_list WHERE cc.classroom_id = '{$classroom_id}' AND cc.status = 0 AND csj.student_id = '{$student_id}'"
    );

    return !empty($result) ? $result : [];
}

function getStudentClassroomCount($student_id) {
    $result = select_data(
        "COUNT(DISTINCT classroom_id) AS total_classrooms",
        "classroom_student_join",
        "WHERE student_id = '{$student_id}' AND status = 0"
    );

    return !empty($result) ? $result : [];
}

function getStudentClassroomGroup($classroom_id){

    $result = select_data(
        "cg.group_id, cg.classroom_id, cg.group_name, cg.group_logo, cg.group_description, cg.group_color",
        "classroom_group cg",
        "WHERE cg.classroom_id = '{$classroom_id}' AND cg.status = 0"
    );
    
    // cs.student_id, cs.student_firstname_th, cs.student_lastname_th, cs.student_image_profile, cs.student_mobile, cs.student_email, cs.student_company, cs.student_position, ct.classroom_name, cg.group_name, cg.group_color, cg.group_logo

    // "classroom_student cs",
    // "INNER JOIN classroom_student_join csj ON cs.student_id = csj.student_id 
    // LEFT JOIN classroom_template ct ON csj.classroom_id = ct.classroom_id
    // LEFT JOIN classroom_group cg ON csj.group_id = cg.group_id
    // WHERE csj.classroom_id = '{$classroom_id}' AND csj.status = 0")

    // $result = select_data(
    //     "csj.classroom_id, csj.group_id, cg.group_name, cg.group_logo, cg.group_description, cg.group_color",
    //     "classroom_student_join csj",
    //     "JOIN classroom_group cg ON csj.classroom_id = cg.classroom_id WHERE csj.classroom_id = '{$classroom_id}' AND csj.status = 0");

    return !empty($result) ? $result : [];
}

function getStudentClassroomGroupCount($group_id, $classroom_id)
{
    $result = select_data(
        "COUNT(student_id) AS total_student",
        "classroom_student_join",
        "WHERE classroom_id = '{$classroom_id}' AND group_id = '{$group_id}'");

    return !empty($result) ? $result : [];
}

function getTeacherList()
{
    $result = select_data("ct.teacher_id,
        ct.teacher_perfix,
        ct.teacher_firstname_en,
        ct.teacher_lastname_en,
        ct.teacher_firstname_th,
        ct.teacher_lastname_th,
        ct.teacher_nickname_en,
        ct.teacher_nickname_th,
        ct.teacher_idcard,
        ct.teacher_passport,
        ct.teacher_image_profile,
        ct.teacher_card_front,
        ct.teacher_card_back,
        ct.teacher_email,
        ct.teacher_mobile,
        ct.teacher_address",
        "classroom_teacher_join ctj",
        "INNER JOIN classroom_teacher ct ON ctj.teacher_id = ct.teacher_id
        WHERE ctj.classroom_id = '2'"
    );

    return !empty($result) ? $result : [];
}

function getStaffMemberlist($classroom_id)
{
    $result = select_data("cst.staff_id, cst.comp_id, cst.emp_id, empinfo.firstname, empinfo.lastname",
    "classroom_staff cst",
    "LEFT JOIN m_employee_info empinfo ON cst.emp_id = empinfo.emp_id
    WHERE cst.classroom_id = '{$classroom_id}'");
    
    return !empty($result) ? $result : [];
}

function getMemberRole()
{
    $result = select_data("position_id, position_name_en, position_name_th, COUNT(*) AS count_role",
    "classroom_position",
    "WHERE status = 0 GROUP BY position_name_en, position_name_th");

    return !empty($result) ? $result : [];
}

function getEarlyTimeAttendanceStatus($workshop_id, $student_id)
{

    $emp_id = getStudentEmpId($student_id);

    $result = select_data("otw.workshop_id,
    otw.workshop_name,
    otw.date_start,
    otw.time_start,
    otw.time_end,
    otwe.emp_id,
    otwe.stamp_in,
    otwe.stamp_out,
    (CASE
        WHEN TIME(otwe.stamp_in) < otw.time_start THEN TIMEDIFF(otw.time_start, TIME(otwe.stamp_in))
        ELSE '00:00:00'
    END) AS early_check_in,
    (CASE
        WHEN TIME(otwe.stamp_in) > otw.time_start THEN TIMEDIFF(TIME(otwe.stamp_in), otw.time_start)
        ELSE '00:00:00'
    END) AS late_check_in,
    (CASE
        WHEN TIME(otwe.stamp_out) < otw.time_end THEN TIMEDIFF(otw.time_end, TIME(otwe.stamp_out))
        ELSE '00:00:00'
    END) AS early_check_out,
    (CASE
        WHEN TIME(otwe.stamp_out) > otw.time_end THEN TIMEDIFF(TIME(otwe.stamp_out), otw.time_end)
        ELSE '00:00:00'
    END) AS late_check_out",
    "ot_workshop otw LEFT JOIN ot_workshop_emp otwe ON otw.workshop_id = otwe.workshop_id",
    "WHERE otwe.emp_id = '{$emp_id}' AND otwe.workshop_id = '{$workshop_id}' AND otw.status = 0 AND otwe.status = 0");

    return !empty($result) ? $result : [];
}

function getEarlyTestAttendanceStatus($workshop_id, $student_id)
{
    $emp_id = getStudentEmpId($student_id);

    $result = select_data("otw.workshop_id,
    otw.workshop_name,
    otw.date_start,
    otw.time_start,
    otw.time_end,
    otwe.emp_id,
    otwe.stamp_in,
    otwe.stamp_out,
    (CASE
        WHEN TIME(otwe.stamp_in) < otw.time_start THEN TIMEDIFF(otw.time_start, TIME(otwe.stamp_in))
        ELSE '00:00:00'
    END) AS early_check_in,
    (CASE
        WHEN TIME(otwe.stamp_in) > otw.time_start THEN TIMEDIFF(TIME(otwe.stamp_in), otw.time_start)
        ELSE '00:00:00'
    END) AS late_check_in,
    (CASE
        WHEN TIME(otwe.stamp_out) < otw.time_end THEN TIMEDIFF(otw.time_end, TIME(otwe.stamp_out))
        ELSE '00:00:00'
    END) AS early_check_out,
    (CASE
        WHEN TIME(otwe.stamp_out) > otw.time_end THEN TIMEDIFF(TIME(otwe.stamp_out), otw.time_end)
        ELSE '00:00:00'
    END) AS late_check_out",
    "ot_workshop otw LEFT JOIN ot_workshop_emp otwe ON otw.workshop_id = otwe.workshop_id",
    "WHERE otwe.emp_id = '{$emp_id}' AND otwe.workshop_id = '{$workshop_id}' AND otw.status = 0 AND otwe.status = 0");

    return !empty($result) ? $result : [];
}

function getCertificateListStudent($student_id){

    $result = select_data(
        "st.stamp_id,
        CONCAT(COALESCE(csi.student_firstname_th, ''), ' ', COALESCE(csi.student_lastname_th, '')) AS emp_name,
        oc.certification_name,
        CASE 
            WHEN oc.certification_code IS NULL AND st.certification_no IS NOT NULL THEN st.certification_no
            WHEN oc.certification_code IS NOT NULL AND st.certification_no IS NOT NULL THEN CONCAT(st.certification_no, '-', oc.certification_code)
            ELSE ''
        END AS certification_no,
        DATE_FORMAT(st.certification_date, '%Y/%m/%d %H:%i:%s') AS certification_date,
        IFNULL(table_progress.learn_percent, 0) AS completion_percentage,
        cfs.file_path,
        csi.student_gender,
        '' AS emp_avatar,
        csj.emp_id,
        csj.comp_id,
        st.certification_id",
        "ot_certification_stamp st
        INNER JOIN classroom_student_join csj ON csj.emp_id = st.emp_id
        INNER JOIN classroom_student csi ON csi.student_id = csj.student_id
        LEFT JOIN classroom_file_student cfs ON cfs.student_id = csj.student_id
        LEFT JOIN ot_certification_course oc ON oc.certification_id = st.certification_id
        LEFT JOIN
        (
            SELECT 
                emp_id,
                ROUND(
                    SUM(
                        CASE
                            WHEN topic_percent_pass IS NULL OR topic_percent_pass = '' OR topic_percent_pass = 0 THEN 
                                CASE
                                    WHEN learn_flag = 'Y' THEN 100
                                    WHEN learn_flag = 'W' THEN 50
                                    ELSE 0
                                END
                            WHEN topic_percent_pass > 0 THEN 
                                CASE
                                    WHEN learn_flag = 'Y' THEN 100
                                    WHEN learn_flag IN ('W', 'N') OR learn_flag IS NULL THEN COALESCE(topic_percent_value, 0)
                                    ELSE 0
                                END
                            ELSE 0
                        END
                    ) / COUNT(*), 2
                ) AS learn_percent
            FROM 
                ot_learning_plan
            WHERE 
                status = 0
            GROUP BY 
                emp_id
        ) table_progress ON table_progress.emp_id = csj.emp_id",
        "WHERE 
            csj.student_id = '{$student_id}' 
            AND st.status = 0 
            AND oc.status = 0
        GROUP BY
            csj.student_id, st.certification_id");
            
    return !empty($result) ? $result : [];
}


function getCertificateListByCourse($student_id, $course_id){
   $table = "
        SELECT 
            st.stamp_id,
            CONCAT(COALESCE(csi.student_firstname_th, ''), ' ', COALESCE(csi.student_lastname_th, '')) AS emp_name,
            oc.certification_name,
            CASE 
                WHEN oc.certification_code IS NULL AND st.certification_no IS NOT NULL THEN st.certification_no
                WHEN oc.certification_code IS NOT NULL AND st.certification_no IS NOT NULL THEN CONCAT(st.certification_no, '-', oc.certification_code)
                ELSE ''
            END AS certification_no,
            DATE_FORMAT(st.certification_date, '%Y/%m/%d %H:%i:%s') AS certification_date,
            IFNULL(table_progress.learn_percent, 0) AS completion_percentage,
            cfs.file_path,
            csi.student_gender,
            '' AS emp_avatar,
            csj.emp_id,
            csj.comp_id,
            st.certification_id
        FROM 
            ot_certification_stamp st
        INNER JOIN 
            classroom_student_join csj ON csj.emp_id = st.emp_id
        INNER JOIN 
            classroom_student csi ON csi.student_id = csj.student_id
        LEFT JOIN 
            classroom_file_student cfs ON cfs.student_id = csj.student_id
        LEFT JOIN
            ot_certification_course oc ON oc.certification_id = st.certification_id
        LEFT JOIN
        (
            SELECT 
                emp_id,
                ROUND(
                    SUM(
                        CASE
                            WHEN topic_percent_pass IS NULL OR topic_percent_pass = '' OR topic_percent_pass = 0 THEN 
                                CASE
                                    WHEN learn_flag = 'Y' THEN 100
                                    WHEN learn_flag = 'W' THEN 50
                                    ELSE 0
                                END
                            WHEN topic_percent_pass > 0 THEN 
                                CASE
                                    WHEN learn_flag = 'Y' THEN 100
                                    WHEN learn_flag IN ('W', 'N') OR learn_flag IS NULL THEN COALESCE(topic_percent_value, 0)
                                    ELSE 0
                                END
                            ELSE 0
                        END
                    ) / COUNT(*), 2
                ) AS learn_percent
            FROM 
                ot_learning_plan
            WHERE 
                trn_id = '{$course_id}'
                AND status = 0
            GROUP BY 
                emp_id
        ) table_progress ON table_progress.emp_id = csj.emp_id
        WHERE 
            csj.student_id = '{$student_id}' 
            AND st.status = 0 
            AND oc.status = 0
        GROUP BY 
            csj.student_id, st.certification_id";
    
    return $table;
}

function getStudentEmpId($student_id){
    $result = select_data(
        "emp_id",
        "classroom_student_join",
        "WHERE student_id = '{$student_id}'"
    );

    return !empty($result) ? $result[0]['emp_id'] : null;
}

function getCourseStudent($alumni_id){
    $result = select_data(
        "*",
        "classroom_course",
        "WHERE classroom_id = '{$alumni_id}' AND status = 0"
    );

    $course_trn_ids = []; // Initialize as empty array

    if (!empty($result)) {
        foreach ($result as $row) {
            // Append each row's course_type and course_ref_id as an associative array
            $course_trn_ids[] = [
                'course_type' => $row['course_type'], 
                'course_trn' => $row['course_ref_id']
            ];
        }
    }

    return $course_trn_ids;
}

function getCourseDetail($classroom_id){
    $result = select_data(
        "cc.course_id,
        cc.course_type,
        cc.course_ref_id,
        ct.classroom_name",
        "classroom_course cc",
        "LEFT JOIN classroom_student_join ctj ON cc.classroom_id = ctj.classroom_id
        LEFT JOIN classroom_template ct ON cc.classroom_id = ct.classroom_id
        WHERE cc.classroom_id = '{$classroom_id}' AND cc.status = 0 AND ctj.status = 0"
    );

    return !empty($result) ? $result : [];
}



function getCertificateListOfStudent($student_id){

    $emp_id = getStudentEmpId($student_id);

    $alumni_id = getStudentClassroomId($student_id);

    $trn_course = getCourseStudent($alumni_id);

    $course_ids = array_column($trn_course, 'course_trn'); // get course_ref_ids
    $course_search = implode(',', $course_ids);
    $columnCert = "cert.certification_id,
                cert.certification_level,
                cert.certification_name,
                cert.certification_description,
                cert.certification_background,
                stamp.content_pass,
                stamp.content_pass_val,
                stamp.video_quality,
                stamp.video_quality_val,
                stamp.challenge_pass,
                stamp.challenge_pass_val,
                stamp.event_pass,
                stamp.event_pass_val,
                date_format(stamp.certification_date,'%Y/%m/%d %H:%i:%s') as certification_date,
                stamp.certification_no,
                cert.certification_code,
                stamp.certification_dowload,
                stamp.trn_id as course_id";
    $tableCert = "ot_certification_stamp stamp";
    $whereCert = "left join 
                    ot_certification_course cert on cert.certification_id = stamp.certification_id 
                where 
                    stamp.trn_id in ($course_search) and stamp.emp_id = '{$emp_id}' and stamp.status = 0 and stamp.certification_no is not null
                group by 
                    cert.certification_id
                order by 
                    certification_level asc";

    $result = select_data($columnCert,$tableCert,$whereCert);

    $groupedCertificates = [];
    foreach ($result as $cert) {
        $name = $cert['certification_name'];
        if (!isset($groupedCertificates[$name])) {
            $groupedCertificates[$name] = [
                'count' => 0,
                'details' => $cert
            ];
        }
        $groupedCertificates[$name]['count']++;
    }

    return $groupedCertificates;
}


?>