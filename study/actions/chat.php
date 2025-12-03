<?php
    date_default_timezone_set('Asia/Bangkok'); 
    session_start();
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if($_SERVER['HTTP_HOST'] == 'localhost'){
        $request_uri = $_SERVER['REQUEST_URI'];
        $exl_path = explode('/',$request_uri);
        if(!file_exists($base_include."/dashboard.php")){
            $base_path .= "/".$exl_path[1];
        }
        $base_include .= "/".$exl_path[1];
    }
    define('BASE_PATH', $base_path);
    define('BASE_INCLUDE', $base_include);
    require_once $base_include.'/lib/connect_sqli.php';

    if($_POST && $_POST['action'] == 'create_chat'){
        $emp_id = $_POST['emp_id'];
        $comp_id = $_POST['comp_id'];

        $license = checkLicense(null, $comp_id);
        if(!empty($license)){
            $duplicateGroupCheck = checkGroupChat($emp_id, $comp_id);
            $duplicateGroupCount = count($duplicateGroupCheck);

            if($duplicateGroupCount > 0){
                echo json_encode([
                    'status' => true,
                    'group_id' => intval($duplicateGroupCheck[0]['group_id'])
                ]);
            }else{
                $tableInsData = "ai_live_group_message";
                $columnInsData = "(
                    emp_id,
                    comp_id,
                    date_created	
                )";
                $valueInsData = "(
                    '{$emp_id}',
                    '{$comp_id}',
                    NOW()
                )";
                $ins_id = insert_data($tableInsData,$columnInsData,$valueInsData);
                echo json_encode([
                    'status' => true,
                    'group_id' => $ins_id
                ]);
            }
        
        }else{
            echo json_encode([
                'status' => false,
                'message' => 'Origami AI สวัสดีค่ะ <br>
                ขณะนี้บริษัทของคุณยังไม่ได้ถูก Activate ให้สามารถใช้งาน Origami AI ได้ค่ะ <br>
                หากต้องการเปิดการใช้งาน กรุณาติดต่อทีม Allable เพื่อดำเนินการเปิดระบบ Origami AI ให้พร้อมใช้งานนะคะ <br>'
            ]);
        }
    }

    if($_POST && $_POST['action'] == 'get_message_ai') {
        $emp_id = $_POST['emp_id'];
        $comp_id = $_POST['comp_id'];
        $group_id = $_POST['group_id'];

        $emp_message = $_POST['user_message'];
        $now = date('Y-m-d H:i:s');

        $license = checkLicense(null, $comp_id);
        if(!empty($license)){

            $duplicateGroupCheck = checkDuplicateGroup($emp_id, $comp_id, $group_id);
            $duplicateGroupCount = count($duplicateGroupCheck);

            $questionText = escape_string(strip_tags($emp_message));

            if($duplicateGroupCount > 0){
                foreach ($duplicateGroupCheck as $group) {
                    if (is_null($group['subject'])) {
                        $sql_up = "UPDATE `ai_live_group_message` 
                                    SET `subject` = '{$questionText}'
                                    WHERE emp_id = '{$group['emp_id']}' 
                                    AND comp_id = '{$group['comp_id']}' 
                                    AND group_id = '{$group['group_id']}'";
                        $rs_up = query_sqli( $sql_up);
                    }
                }
            }else{
                $groupTable = "ai_live_group_message";
                $groupColumns = "(
                    emp_id,
                    comp_id,
                    subject,
                    date_created	
                )";
                $groupValues = "(
                    '{$emp_id}',
                    '{$comp_id}',
                    '{$questionText}',
                    '{$now}'
                )";
                $group_id = insert_data($groupTable, $groupColumns, $groupValues);
            }

            $messageTable = "ai_live_message";
            $messageColumns = "(
                group_id,
                emp_id,
                comp_id,
                q_message,
                live_message_create
            )";
            $messageValues = "(
                '{$group_id}',
                '{$emp_id}',
                '{$comp_id}',
                '{$questionText}',
                '{$now}'
            )";
            $insert = insert_data($messageTable, $messageColumns, $messageValues);

            if($insert){
                $aiResponse = getReplyAi($emp_message, $emp_id, $comp_id);

                $getAiObject = json_decode($aiResponse);
                $status_reply  = isset($getAiObject->status) ? $getAiObject->status : null;
                $message_reply = isset($getAiObject->reply)  ? $getAiObject->reply  : null;

                $article_id         = null;
                $resource_url       = null;
                $question_answer_id = null;

                if (!empty($getAiObject->sources) && is_array($getAiObject->sources)) {
                    foreach ($getAiObject->sources as $src) {

                        if ($article_id === null && isset($src->article_id)) {
                            $article_id = $src->article_id;
                        }
                        if ($resource_url === null && isset($src->resource_url)) {
                            $resource_url = $src->resource_url;
                        }
                        if ($question_answer_id === null && isset($src->question_answer_id)) {
                            $question_answer_id = $src->question_answer_id;
                        }

                        if ($article_id !== null &&
                            $resource_url !== null &&
                            $question_answer_id !== null) {
                            break;
                        }
                    }
                }

                // $article_id = $sources_reply[0] -> article_id;
                // $resource_url = $sources_reply[0] -> resource_url;
                // $question_answer_id = $sources_reply[1] -> question_answer_id;

                if(!empty($message_reply)){
                    $answerText = escape_string(strip_tags($message_reply));
                    $answerTextRes = nl2br($message_reply);
                    $answerUrl = $resource_url;
                    $articles_id = $article_id;
                    $qa_id = $question_answer_id;
                    $now_up = date('Y-m-d H:i:s');
                }else{
                    $answerTextRes = 'Sorry, but I don\'t have a relevant answer. Can you please try rephrasing your question?';
                    $answerText = mysqli_real_escape_string($mysqli, $answerTextRes);
                    $answerUrl = '';
                    $articles_id = '';
                    $qa_id = '';
                    $now_up = date('Y-m-d H:i:s');
                }

                $sql_up_message = "UPDATE `ai_live_message` 
                                SET 
                                a_message = '{$answerText}',
                                message_source = '{$answerUrl}',
                                ai_date_respond = '{$now_up}',
                                articles_id = '{$articles_id}',
                                qa_id = '{$qa_id}'
                                WHERE live_message_id = '{$insert}'";
                $rs_up_message = query_sqli( $sql_up_message);
            }

            echo json_encode([
                    'status' => true,
                    'chat_id' => $insert,
                    'reply' => $answerTextRes,
                    'url' => $answerUrl,
                    'date_time' => $now_up
                ]);

        }else{
            echo json_encode([
                'status' => true,
                'chat_id' => '',
                'reply' => 'Origami AI สวัสดีค่ะ <br>
                ขณะนี้บริษัทของคุณยังไม่ได้ถูก Activate ให้สามารถใช้งาน Origami AI ได้ค่ะ <br>
                หากต้องการเปิดการใช้งาน กรุณาติดต่อทีม Allable เพื่อดำเนินการเปิดระบบ Origami AI ให้พร้อมใช้งานนะคะ <br>
                ',
                'url' => '',
                'date_time' => $now
            ]);

        }
    }


    function getReplyAi($message, $emp_id, $comp_id){
        // $DataLicense = checkLicense($emp_id, $comp_id);
        $DataLicense = checkToken($emp_id, $comp_id);
        $hostAPI = 'https://api.devrev.ai';
        $postData = json_encode([
            "query" => $message
        ]);
        $urlPath = $hostAPI . '/recommendations.get-reply';
        $headers = [
            'Authorization: Bearer '.$DataLicense[0]['token_key'],
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return json_encode(['status' => 'error', 'message' => 'CURL Error: ' . curl_error($ch)]);
        }
        curl_close($ch);
        $data = json_decode($response, true);

        $reply = $data['reply'];
        $sources = $data['sources'];

        $dataArray = [];
        foreach ($sources as $src) {
            switch ($src['type']) {
                case 'article':
                    $dataArray[] = [
                        'article_id' => $src['display_id'],
                        'resource_url' => $src['resource']['url'],
                    ];
                    break;

                case 'question_answer':
                    $dataArray[] = [
                        'question_answer_id' => $src['display_id']
                    ];
                    break;
            }
        }
        
        if (isset($data['reply'])) {
            return json_encode([
                'status' => 'success',
                'reply' => $reply,
                'sources' => $dataArray
            ]);
        }else{
            return json_encode([
                'status' => 'error',
                'message' => 'Invalid response from API'
            ]);
        }

    }

    function checkLicense($emp_id, $comp_id){
        $sql_license = "SELECT * FROM `ai_company_license` WHERE comp_id = '{$comp_id}' AND `status` = 0  LIMIT 1";
        $rs_license = query_sqli( $sql_license);
        $licenses = [];
        if ($rs_license) {
            while ($row = mysqli_fetch_assoc($rs_license)) {
                $licenses[] = $row;
            }
        }
        return $licenses;
    }

    function checkToken($emp_id, $comp_id){
        $sql_license = "SELECT * FROM `ai_company_token` WHERE comp_id = '{$comp_id}' AND `status` = 0  LIMIT 1";
        $rs_license = query_sqli( $sql_license);
        $licenses = [];
        if ($rs_license) {
            while ($row = mysqli_fetch_assoc($rs_license)) {
                $licenses[] = $row;
            }
        }
        return $licenses;
    }

    function checkDuplicateGroup($emp_id, $comp_id, $group_id){
        global $mysqli;
        $sql_check = "SELECT * FROM `ai_live_group_message` 
                        WHERE emp_id = '{$emp_id}' 
                        AND comp_id = '{$comp_id}'
                        AND group_id = '{$group_id}'
                        ORDER BY group_id ASC";
        $rs_check = mysqli_query($mysqli, $sql_check);
        $result = [];
        if ($rs_check && mysqli_num_rows($rs_check) > 0) {
            while ($row = mysqli_fetch_assoc($rs_check)) {
                $result[] = $row;
            }
        }
        return $result;
    }

    function checkGroupChat($emp_id, $comp_id) {
        global $mysqli;
        $sql_check = "SELECT group_id FROM `ai_live_group_message` 
                        WHERE emp_id = '{$emp_id}' 
                        AND comp_id = '{$comp_id}' 
                        AND subject IS NULL 
                        ORDER BY group_id ASC";

        $rs_check = mysqli_query($mysqli, $sql_check);

        $result = [];
        if ($rs_check && mysqli_num_rows($rs_check) > 0) {
            while ($row = mysqli_fetch_assoc($rs_check)) {
                $result[] = $row;
            }
        }
        return $result;
    }

?>