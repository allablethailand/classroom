<?php
    function parseState($state) {
        $classroom_id = '';
        $student_id = '';
        $channel_id = '';
        $line_client_id = '';
        if (preg_match('/cid_(\d+)_lid_(\d+)_stu_(\d+)_ch_(\d+)/', $state, $matches)) {
            $classroom_id = $matches[1];
            $line_client_id = $matches[2];
            $student_id = $matches[3];
            $channel_id = $matches[4];
        } 
        elseif (preg_match('/cid_(\d+)_lid_(\d+)_stu_(\d+)/', $state, $matches)) {
            $classroom_id = $matches[1];
            $line_client_id = $matches[2];
            $student_id = $matches[3];
            $channel_id = null;
        }
        return [$classroom_id, $line_client_id, $student_id, $channel_id];
    }
    function redirectTo($url) {
        header("Location: " . $url);
        exit;
    }
    function createConnectionIfNotExist($profile, $student_id) {
        $userId = escape_string($profile['userId']);
        $displayName = isset($profile['displayName']) ? escape_string($profile['displayName']) : '';
        $pictureUrl = isset($profile['pictureUrl']) ? escape_string($profile['pictureUrl']) : '';
        $statusMessage = isset($profile['statusMessage']) ? escape_string($profile['statusMessage']) : '';
        $exits = select_data(
            "connect_id", "classroom_line_connect", "where student_id = '{$student_id}'"
        );
        if(!empty($exits)) {
            update_data(
                "classroom_line_connect",
                "userId = '{$userId}', displayName = '{$displayName}', pictureUrl = '{$pictureUrl}', statusMessage = '{$statusMessage}', update_date = NOW()",
                "student_id = '{$student_id}'"
            );
        } else {
            insert_data(
                "classroom_line_connect", 
                "(student_id, userId, displayName, pictureUrl, statusMessage, connect_date, update_date)",
                "('{$student_id}', '{$userId}', '{$displayName}', '{$pictureUrl}', '{$statusMessage}', NOW(), NOW())"
            );
        }
    }
?>