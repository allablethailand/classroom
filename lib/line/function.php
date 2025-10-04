<?php
    function parseState($state) {
        $classroom_id = '';
        $channel_id = '';
        $line_client_id = '';
        if (preg_match('/cid_(\d+)_lid_(\d+)_ch_(\d+)/', $state, $matches)) {
            $classroom_id = $matches[1];
            $line_client_id = $matches[2];
            $channel_id = $matches[3];
        } 
        elseif (preg_match('/cid_(\d+)_lid_(\d+)/', $state, $matches)) {
            $classroom_id = $matches[1];
            $line_client_id = $matches[2];
            $channel_id = null;
        }
        return [$classroom_id, $line_client_id, $channel_id];
    }
    function redirectTo($url) {
        header("Location: " . $url);
        exit;
    }
    function createConnectionIfNotExist($profile) {
        $userId = escape_string($profile['userId']);
        $displayName = isset($profile['displayName']) ? escape_string($profile['displayName']) : '';
        $pictureUrl = isset($profile['pictureUrl']) ? escape_string($profile['pictureUrl']) : '';
        $statusMessage = isset($profile['statusMessage']) ? escape_string($profile['statusMessage']) : '';
        $exits = select_data(
            "connect_id", "classroom_line_connect", "where userId = '{$userId}'"
        );
        if(!empty($exits)) {
            update_data(
                "classroom_line_connect",
                "displayName = '{$displayName}', pictureUrl = '{$pictureUrl}', statusMessage = '{$statusMessage}', update_date = NOW()",
                "userId = '{$userId}'"
            );
        } else {
            insert_data(
                "classroom_line_connect", 
                "(userId, displayName, pictureUrl, statusMessage, connect_date, update_date)",
                "('{$userId}', '{$displayName}', '{$pictureUrl}', '{$statusMessage}', NOW(), NOW())"
            );
        }
    }
?>