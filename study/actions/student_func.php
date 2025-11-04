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

function getStudentClassroomGroup($student_id){
    $result = select_data(
        "csj.classroom_id, csj.group_id, cg.group_name, cg.group_logo, cg.group_description,cg.group_color",
        "classroom_student_join csj",
        "LEFT JOIN classroom_group cg ON csj.classroom_id = cg.classroom_id 
            AND csj.group_id = cg.group_id
            WHERE csj.student_id = '{$student_id}'");

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
    $result = select_data("position_name_en, position_name_th, COUNT(*) AS count_role",
    "classroom_position",
    "WHERE status = 0 GROUP BY position_name_en, position_name_th");

    return !empty($result) ? $result : [];
}

function getEarlyTimeAttendanceStatus($workshop_id, $student_id)
{
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
    "WHERE otwe.emp_id = '{$student_id}' AND otwe.workshop_id = '{$workshop_id}' AND otw.status = 0 AND otwe.status = 0");

    return !empty($result) ? $result : [];
}


?>