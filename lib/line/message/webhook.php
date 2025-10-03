<?php
    include_once(__DIR__ . "/../../connect_sqli.php");
    include_once(__DIR__ . "/../function.php");
    require_once(__DIR__ . "/../../lib/ai/InferenceRequest.php");
    require_once(__DIR__ . "/../../lib/ai/getResponse.php");
    $content = file_get_contents('php://input');
    $events = json_decode($content, true);
    foreach ($events['events'] as $event) {
        if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
            $replyToken = $event['replyToken'];
            $bot_user_id = $event['source']['userId'];
            $msg = escape_string(trim($event['message']['text']));
            $queue = select_data("event_id", "log_event_line_queue", "WHERE bot_user_id = '{$bot_user_id}' AND queue_end IS NULL");
            if (empty($queue)) exit;
            $event_id = $queue[0]['event_id'];
            update_data("log_event_line_queue", "queue_active = NOW()", "bot_user_id = '{$bot_user_id}' AND event_id = '{$event_id}' AND queue_end IS NULL");
            $line = select_data(
                "line_oa,comp_id,event_open_ai,
                CASE
                    WHEN event_end_ai IS NULL OR event_end_ai = '0000-00-00 00:00:00' THEN 1
                    WHEN event_end_ai <= NOW() THEN 1
                    ELSE 0
                END AS is_open_ai,
                event_welcome_ai,
                event_close_ai,
                event_noanswer_ai", 
                "event_template", 
                "WHERE id = '{$event_id}'"
            );
            $line_oa = $line[0]['line_oa'];
            $comp_id = $line[0]['comp_id'];
            $event_open_ai = $line[0]['event_open_ai'];
            $is_open_ai = $line[0]['is_open_ai'];
            $event_welcome_ai = ($line[0]['event_welcome_ai']) ? $line[0]['event_welcome_ai'] : 'สวัสดี มีอะไรให้ช่วยไหมครับ';
            $event_close_ai = ($line[0]['event_close_ai']) ? $line[0]['event_close_ai'] : 'กรุณารอการติดต่อกลับจากเจ้าหน้าที่ครับ';
            $event_noanswer_ai = ($line[0]['event_noanswer_ai']) ? $line[0]['event_noanswer_ai'] : 'คำถามนี้ AI ไม่สามารถตอบได้ กรุณารอการติดต่อกลับจากเจ้าหน้าที่ครับ';
            $Token = select_data("channel_access_token", "line_token", "WHERE line_token_id = '{$line_oa}'");
            $accessToken = $Token[0]['channel_access_token'];
            if (strpos($msg, 'login:') === 0) {
                list($tag, $login_user_id) = explode(':', $msg);
                replyMessage($replyToken, "บัญชีเชื่อมกับ Event [$event_id] เรียบร้อย ✅", $accessToken);
                exit;
            } elseif ($msg == 'Origami AI') {
                if($event_open_ai == 0 && $is_open_ai > 0) {
                    replyMessage($replyToken, $event_welcome_ai, $accessToken);
                } else {
                    replyMessage($replyToken, $event_close_ai, $accessToken);
                }
                exit;
            }
            $message_id = insert_data("log_event_line_message", "(event_id,message_user_id,message,date_create,status)", "('{$event_id}','{$bot_user_id}','{$msg}',NOW(),0)");
            if($event_open_ai == 0 && $is_open_ai > 0) {
                $startTime = microtime(true);
                $providers_id = 1;
                $answerData = getResponseByDevRev($comp_id, $providers_id, $msg);
                $elapsed = microtime(true) - $startTime;
                if (isset($devrevResult['status']) && $devrevResult['status'] === 'success' && !empty($devrevResult['response'])) {
                    $answer = $devrevResult['response'];
                } else {
                    $answer = $event_noanswer_ai;
                }
                $res = json_encode($answerData, JSON_UNESCAPED_UNICODE);
                $answer_esc = escape_string($answer);
                $res_esc = escape_string($res);
                if ($elapsed > 60) {
                    sleep(2);
                    $maxRetries = 3;
                    $attempt = 0;
                    $success = false;
                    while (!$success && $attempt < $maxRetries) {
                        $result = pushMessage($bot_user_id, $answer, $accessToken, $message_id);
                        if ($result !== false && strpos($result, '"message"') === false) {
                            $success = true;
                        } else {
                            $attempt++;
                            sleep(1);
                        }
                    }
                    file_put_contents("log_ai_fallback.txt", date("Y-m-d H:i:s") . " | {$msg} | {$elapsed}s | push " . ($success ? '✅' : '❌') . "\n", FILE_APPEND);
                } else {
                    replyMessage($replyToken, $answer_esc, $accessToken, $message_id);
                }
            } else {
                $answer_esc = $event_close_ai;
                replyMessage($replyToken, $answer_esc, $accessToken);
            }
            update_data("log_event_line_message",
                "response = '{$answer_esc}', message_data = '{$res_esc}', date_response = NOW()",
                "id = '{$message_id}'"
            );
        }
    }
    function replyMessage($replyToken, $text, $accessToken, $message_id) {
        update_data("log_event_line_message","sending_date = NOW()","id = '{$message_id}'");
        $text = sanitizeText($text);
        $maxLength = 4998;
        $chunks = str_split($text, $maxLength);
        $messages = [];
        foreach ($chunks as $chunk) {
            $messages[] = ['type' => 'text', 'text' => $chunk];
        }
        $url = 'https://api.line.me/v2/bot/message/reply';
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer {$accessToken}"
        ];
        $post = [
            'replyToken' => $replyToken,
            'messages' => array_slice($messages, 0, 5)
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    function pushMessage($to, $text, $accessToken, $message_id = null) {
        $text = sanitizeText($text);
        $maxLength = 5000;
        $chunks = str_split($text, $maxLength);
        $messages = [];
        foreach ($chunks as $chunk) {
            $messages[] = ['type' => 'text', 'text' => $chunk];
        }
        $url = 'https://api.line.me/v2/bot/message/push';
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer {$accessToken}"
        ];
        $post = [
            'to' => $to,
            'messages' => array_slice($messages, 0, 5)
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    function sanitizeText($text) {
        $text = str_replace('\\n', "\n", $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
        $text = trim($text);
        return $text;
    }
?>