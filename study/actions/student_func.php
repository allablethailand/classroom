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


?>