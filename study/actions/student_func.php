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

function getStudentAlumni($student_id) {
    $result = select_data("classroom_id", "classroom_template", "WHERE student_id = '{$student_id}'");
    return $result ? (bool)$result[0]['classroom_id'] : false;
}

function getStudentGroupId($student_id) {
    $result = select_data("group_id", "classroom_group", "WHERE student_id = '{$student_id}'");
    return $result ? $result[0]['group_id'] : null;
}

// function getStudentCourseId($classroom_id) {
//     $result = select_data("course_id", "classroom_course", "WHERE classroom_id = '{$classroom_id}'");
//     return $result ? $result[0]['course_id'] : null;
// }

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


?>