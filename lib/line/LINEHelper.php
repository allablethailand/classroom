<?php
    include_once(__DIR__ . '/config.php');
    class LINEHelper {
        public static function getLineClientId($line_token_id) {
            global $mysqli;
            $line_token_id_safe = mysqli_real_escape_string($mysqli, $line_token_id);
            $token = select_data(
                "line_client_id",
                "line_token",
                "WHERE line_token_id = '{$line_token_id_safe}' AND line_client_id IS NOT NULL AND line_client_id <> '' AND status = 0"
            );
            return isset($token[0]['line_client_id']) ? $token[0]['line_client_id'] : null;
        }
        public static function buildLoginUrl($classroom_id, $student_id, $client_id) {
            if (!$client_id || !$classroom_id) {
                return null;
            }
            $state = "cid_" . intval($classroom_id);
            if ($student_id !== '') {
                $state .= "_stu_" . urlencode($student_id);
            }
            $params = array(
                'response_type' => 'code',
                'client_id'     => $client_id,
                'redirect_uri'  => LINE_REDIRECT_URI,
                'state'         => $state,
                'scope'         => 'profile openid',
                'bot_prompt'    => 'normal',
            );
            return 'https://access.line.me/oauth2/v2.1/authorize?' . http_build_query($params);
        }
    }
?>