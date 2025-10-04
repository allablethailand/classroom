<?php
    include_once(__DIR__ . '/config.php');
    class LINEHelper {
        public static function getRegisterUrl($classroom_id, $channel_id) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $classroom = select_data(
                "classroom_key", "classroom_template", "where classroom_id = '{$classroom_id}'"
            );
            $classroom_key = $classroom[0]['classroom_key'];
            $params = $classroom_key;
            if($channel_id) {
                $params .= "/" . md5($channel_id);
            }
            return "{$scheme}://{$host}/classroom/register/{$params}";
        }
        public static function getLineToken($line_client_id) {
            global $mysqli;
            $line_client_id_safe = mysqli_real_escape_string($mysqli, $line_client_id);
            $token = select_data(
                "line_client_id, line_client_secret",
                "line_token",
                "WHERE line_client_id = '{$line_client_id_safe}' AND status = 0"
            );
            return isset($token[0]) ? $token[0] : null;
        }
        public static function buildLoginUrl($classroom_id, $client_id, $channel_id) {
            if (!$client_id || !$classroom_id) {
                return null;
            }
            $state = "cid_" . intval($classroom_id);
            $state .= "_lid_" . intval($client_id);
            if ($channel_id !== '') {
                $state .= "_ch_" . urlencode($channel_id);
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
        public static function getAccessToken($code, $line_client_id, $line_client_secret) {
            $data = array(
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => LINE_REDIRECT_URI,
                'client_id'     => $line_client_id,
                'client_secret' => $line_client_secret,
            );
            $response = self::curlPost(LINE_TOKEN_URL, $data, array(
                'Content-Type: application/x-www-form-urlencoded'
            ));
            return json_decode($response, true);
        }
        public static function getLineProfile($access_token) {
            $response = self::curlGet(LINE_PROFILE_URL, array(
                "Authorization: Bearer {$access_token}"
            ));
            return json_decode($response, true);
        }
        private static function curlPost($url, $postFields, $headers = array()) {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL            => $url,
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => http_build_query($postFields),
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_TIMEOUT        => 10,
            ));
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }
        private static function curlGet($url, $headers = array()) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_TIMEOUT        => 10,
            ));
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }
    }
?>