<?php
    header("Content-type:text/html; charset=UTF-8");        
    header("Cache-Control: no-store, no-cache, must-revalidate");       
    header("Cache-Control: post-check=0, pre-check=0", false); 
    date_default_timezone_set('Asia/Bangkok');
    @session_start();
    date_default_timezone_set("Asia/Bangkok");
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    if($_SERVER['HTTP_HOST'] == 'localhost'){
        $request_uri    = $_SERVER['REQUEST_URI'];
        $exl_path         = explode('/',$request_uri);
        $base_include .= "/".$exl_path[1];
    } 
    require_once $base_include.'/lib/connect_sqli.php';
    $mysqli -> query("SET group_concat_max_len = 1000000");
    $payload = json_decode(file_get_contents('php://input'), false, 512, JSON_BIGINT_AS_STRING);
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
	setBucket($fsData);
    $emp_id         = $_SESSION['emp_id'];
    $comp_id        = $_SESSION['comp_id'];
    $emp_type_code  = isset($_SESSION["stk_type_code"]) ? $_SESSION["stk_type_code"] : NULL;
    if (isset($payload->action) && $emp_id && $comp_id) {
        $action = $payload->action;
        $data   = $payload->data;
        if ($action === 'load_options') { 
            $response = [];
            $questionId = $data->question_id;
            if ($questionId) {
                $code = "SELECT choice_id, choice_text FROM classroom_form_choices WHERE question_id = '{$questionId}' AND status = 0";
                $query = query_sqli($code);
                while ($row = mysqli_fetch_assoc($query)) {
                    $response[] = $row;
                }
            }
            if ($query) {
                echo json_encode(['status' => true, 'data' => $response]);
                exit();
            }else {
                echo json_encode(['status' => false, 'message' => 'Can not get the data.']);
                exit();
            }
        }else if($action === 'load_form_data') { 
            $classroomId = $data->classroom_id;
            $response = [];
            if ($classroomId) {
                $code = "SELECT form_id, form_name FROM classroom_forms WHERE classroom_id = '{$classroomId}'";
                $query = query_sqli($code);
                $fetchData = mysqli_fetch_assoc($query);
                $form_id = $fetchData['form_id'] ? $fetchData['form_id'] : ''; 
                if ($form_id) {
                    $response['form_id'] = $form_id;
                    $response['form_name'] = $fetchData['form_name'] ? $fetchData['form_name'] : ''; 
                    $sql = "SELECT question_id, question_text, question_type, has_required, has_other_option FROM classroom_form_questions WHERE form_id = '{$response['form_id']}' AND status = 0 ORDER BY `order` ASC";
                    $result = query_sqli($sql);
                    $index = 0;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $response['questions'][$index]['question_id']     = $row['question_id'];
                        $response['questions'][$index]['text']            = $row['question_text'];
                        $response['questions'][$index]['type']            = $row['question_type'];
                        $response['questions'][$index]['required']        = ($row['has_required']) ? true : false;
                        $response['questions'][$index]['hasOtherOption']  = ($row['has_other_option']) ? true : false;
                        $index++;
                    } 
                    
                }
                echo json_encode(['status' => true, 'data' => $response]);
                exit();
            }
            echo json_encode(['status' => false, 'message' => 'Can not load form.']);
            exit();
        }else if($action === 'delete_question') { 
            $question_id = $data->question_id;
            if ($question_id) {
                $delete = "UPDATE classroom_form_questions SET status = 1, emp_update = '{$emp_id}', update_date = NOW() WHERE question_id = '{$question_id}'";
                $query = query_sqli($delete);
                if ($query) {
                    echo json_encode(['status' => true, 'data' => 'The form has been deleted successfully.']);
                    exit();
                }
            }
            echo json_encode(['status' => false, 'message' => 'Error: Can not delete a question.']);
            exit();
        }else if($action === 'delete_choice') {
            $choice_id = $data->choice_id;
            if ($choice_id) {
                $delete = "UPDATE classroom_form_choices SET status = 1 WHERE choice_id = '{$choice_id}'";
                $query = query_sqli($delete);
                if ($query) {
                    echo json_encode(['status' => true, 'data' => 'The form has been deleted successfully.']);
                    exit();
                }
            }
            echo json_encode(['status' => false, 'message' => 'Error: Can not delete a choice.']);
            exit();
        }else if($action === 'get_options_answer') {
            $question_id = $data->question_id;
            $classroom_id = $data->classroom_id;
            if ($question_id && $classroom_id) {
                $sql = "SELECT choice_id, choice_text FROM classroom_form_choices WHERE question_id = '{$question_id}' AND status = 0 ORDER BY choice_id ASC";
                $result = query_sqli($sql);
                $data_arr = [];
                $countAnswer = 0;
                while ($rowFecth = mysqli_fetch_assoc($result)) {
                    $choice_id = $rowFecth['choice_id'];
                    $choice_text = $rowFecth['choice_text'];
                    if ($choice_id) {
                        $code = "SELECT
                            COUNT(answer_id) AS response_count
                        FROM
                            classroom_form_answer_users
                        WHERE question_id = '{$question_id}' 
                            AND choice_id = '{$choice_id}'
                            AND classroom_id = '{$classroom_id}'
                            AND answer_type = 1
                            AND status = 0;";
                        $query = query_sqli($code);
                        while ($choice = mysqli_fetch_assoc($query)) {
                            $countAnswer += intval($choice['response_count']);
                            $data_arr[] = [
                                'text' => $choice_text,
                                'response_count' => intval($choice['response_count']),
                            ];
                        }
                    }
                }
                $sql = "SELECT
                    '1' AS response_count,
                    other_text AS choice_text
                FROM
                    classroom_form_answer_users
                WHERE question_id = '{$question_id}'
                    AND classroom_id = '{$classroom_id}'
                    AND answer_type = 2
                    AND status = 0;";
                $query = query_sqli($sql);
                $countOther = mysqli_num_rows($query);
                while ($other = mysqli_fetch_assoc($query)) {
                    $countAnswer++;
                    $data_arr[] = [
                        'text' => $other['choice_text'],
                        'response_count' => intval($other['response_count']),
                    ];
                }
                echo json_encode(['status' => $query ? true : false, 'data' => $data_arr, 'countAnswer' => $countAnswer]);
                exit();
            }
            echo json_encode(['status' => false]);
            exit();
        }else if($action === 'get_short_answer') {
            $question_id = $data->question_id;
            $classroom_id = $data->classroom_id; 
            $limit = isset($data->limit) ? (int)$data->limit : 5;
            $page = isset($data->page) ? (int)$data->page : 1;
            $offset = ($page - 1) * $limit;
            if ($question_id && $classroom_id) {
                $data_arr = [];
                $totalAnswer = ['total' => 0];
                $stmt = "SELECT answer_text FROM classroom_form_answer_users WHERE question_id = '{$question_id}' AND classroom_id = '{$classroom_id}' AND answer_type = 0 AND status = 0 ORDER BY answer_id ASC LIMIT $offset, $limit";
                $result = query_sqli($stmt);
                while ($aRow = mysqli_fetch_assoc($result)) {
                    if ($aRow['answer_text']) {
                        $data_arr[] = ['text' => $aRow['answer_text']];
                    }
                }
                $countQuery = "SELECT COUNT(answer_id) AS total FROM classroom_form_answer_users WHERE question_id = '{$question_id}' AND classroom_id = '{$classroom_id}' AND answer_type = 0 AND status = 0";
                $countResult = query_sqli($countQuery);
                $totalAnswer = mysqli_fetch_assoc($countResult);
                echo json_encode([
                    'status' => true,
                    'data' => $data_arr,
                    'allAnswer' => (int)$totalAnswer['total']
                ]);
                exit();
            }
            echo json_encode(['status' => false]);
            exit();
        }else if($action === 'get_answer_by_question') {
            $classroom_id = $data->classroom_id;
            $question_id = $data->question_id;
            $question_type = $data->question_type;
            if ($classroom_id && $question_id && $question_type) {
                $data_arr = [];
                $index = 0;
                switch ($question_type) {
                    case 'short_answer':
                        $code = "SELECT answer_text, user_id FROM classroom_form_answer_users WHERE question_id = '{$question_id}' AND classroom_id = '{$classroom_id}' AND answer_type = 0 AND status = 0 ORDER BY answer_id ASC";
                        $query = query_sqli($code);
                        while ($row = mysqli_fetch_assoc($query)) {
                            $user_id = $row['user_id'];
                            $users = $user_id ? getUserInfo($user_id) : [];
                            $data_arr[$index++] = [
                                'choice_id' => null,
                                'text'      => $row['answer_text'],
                                'user_info' => $users
                            ];
                        }
                        break;
                    case 'radio' || 'multiple_choice' || 'checkbox':
                        $sql = "SELECT choice_id, choice_text FROM classroom_form_choices WHERE question_id = '{$question_id}' AND status = 0 ORDER BY choice_id ASC";
                        $result = query_sqli($sql);
                        while ($choice = mysqli_fetch_assoc($result)) {
                            $choice_id = $choice['choice_id'];
                            $choice_text = $choice['choice_text'];
                            $code = "SELECT user_id FROM classroom_form_answer_users WHERE question_id = '{$question_id}' AND choice_id = '{$choice_id}' AND classroom_id = '{$classroom_id}' AND answer_type = 1 AND status = 0 ORDER BY answer_id ASC";
                            $query = query_sqli($code);
                            $countUser = mysqli_num_rows($query);
                            if ($countUser === 0) {
                                $data_arr[$index++] = [
                                    'choice_id' => null,
                                    'text'      => $choice_text,
                                    'user_info' => []
                                ];
                            }
                            while ($row = mysqli_fetch_assoc($query)) {
                                $user_id = $row['user_id'];
                                $users = $user_id ? getUserInfo($user_id) : [];
                                $data_arr[$index++] = [
                                    'choice_id' => $choice_id,
                                    'text'      => $choice_text,
                                    'user_info' => $users
                                ];
                            }
                        }
                        $code = "SELECT other_text, user_id FROM classroom_form_answer_users WHERE question_id = '{$question_id}' AND classroom_id = '{$classroom_id}' AND answer_type = 2 AND status = 0 ORDER BY answer_id ASC";
                        $query = query_sqli($code);
                        while ($row = mysqli_fetch_assoc($query)) {
                            $user_id = $row['user_id'];
                            $users = $user_id ? getUserInfo($user_id) : [];
                            $data_arr[$index++] = [
                                'choice_id' => null,
                                'text'      => $row['other_text'],
                                'user_info' => $users
                            ];
                        }
                        break;
        
                    default:
                        echo json_encode(['status' => false, 'message' => 'Error: Question type is not specified.']);
                        exit();
                }
                if (!empty($data_arr)) {
                    echo json_encode(['status' => true, 'data' => $data_arr]);
                } else {
                    echo json_encode(['status' => false, 'message' => 'No data found.']);
                }
                exit();
            }
            echo json_encode(['status' => false, 'message' => 'Invalid input.']);
            exit();
        }        
    }
    if (isset($_POST) && $_POST['action'] && $emp_id && $comp_id) {
        $action = $_POST['action'];
        $data = $_POST;
        if ($action === 'save_forms') {
            $questions = $data['questions'];
            $formID = $data['formID'];
            $formName = escape_string($data['formName']);
            $classroomId = $data['classroom_id'];
            if (!$formID) {
                $formID = createClassroomForm($formName, $classroomId);
                if (!$formID) {
                    echo json_encode(['status' => false, 'message' => 'Can not create the form.']);
                    exit();
                }
            }else {
                $Success = updateClassroomForm($formName, $formID);
                if (!$Success) {
                    echo json_encode(['status' => false, 'message' => 'Can not update form.']);
                    exit();
                }
            }
            $ordering = 1;
            foreach ($questions as $key => $qt) { 
                $id             = $qt['id'];
                $question_id    = $qt['question_id'];
                $text           = escape_string($qt['text']);
                $type           = $qt['type'];
                $hasRequired    = $qt['required'] ? 1 : 0;
                $optionsList    = $qt['options'];
                $hasOtherOption = $qt['hasOtherOption'] ? 1 : 0;
                $hasOptions     = count($optionsList) ? 1 : 0;
                if ($question_id) {
                    $sql = "UPDATE classroom_form_questions SET question_text = '{$text}', has_other_option = '{$hasOtherOption}', has_options = '{$hasOptions}', has_required = '{$hasRequired}', `order` = '{$ordering}', emp_update = '{$emp_id}', update_date = NOW() WHERE question_id = '{$question_id}' AND form_id = '{$formID}'";
                    $query = query_sqli($sql);
                    if ($query && $type !== 'short_answer') {
                        foreach ($optionsList as $choice) {
                            $choice_id      = $choice['choice_id'];
                            $choice_text    = escape_string($choice['choice_text']);
                            if ($choice_id) {
                                updateOptionsID($choice_id, $choice_text);
                            }else {
                                insertOptionsData($question_id, $choice_text);
                            }
                        }
                    }
                }else {
                    $sql = "INSERT INTO classroom_form_questions (question_id, form_id, question_text, question_type, has_other_option, has_options, has_required, `order`, emp_create, create_date, emp_update, update_date) VALUES (NULL, '{$formID}', '{$text}', '{$type}', '{$hasOtherOption}', '{$hasOptions}', '{$hasRequired}', '{$ordering}', '{$emp_id}', NOW(), '{$emp_id}', NOW());";
                    $query = query_sqli($sql);
                    $question_id = $mysqli->insert_id; 
                    if ($question_id) {
                        if ($type !== 'short_answer') {
                            foreach ($optionsList as $choice) {
                                $choice_id      = $choice['choice_id'];
                                $choice_text    = escape_string($choice['choice_text']);
                                insertOptionsData($question_id, $choice_text);
                            }
                        }
                    }
                }
                $ordering++;
            }
            echo json_encode(['status' => true, 'data' => 'The form has been saved successfully.']);
            exit();
        } else {
            echo json_encode(['status' => false, 'message' => 'Error: Can not connect a server.']);
            exit();
        }
    }
    if (isset($_GET) && $_GET['action'] && $emp_id && $comp_id) {
        if ($_GET['action'] === "filter_user") {
            $data = [];
            $form_id = $_GET['form_id'];
            if (!$form_id) {
                $empty[] = ['id' => '','col' => '', 'total_count' => 0];
                echo json_encode($empty);
                exit();
            }
            $term = $_GET['term'] ? $_GET['term'] : '';
            $search = (!empty($term)) ? 
                "AND (student_firstname_en LIKE '%{$term}%' OR student_lastname_en LIKE '%{$term}%' 
                OR student_firstname_th LIKE '%{$term}%' OR student_lastname_th LIKE '%{$term}%')" : "";
            $page = $_GET['page'] ? $_GET['page'] : 1;
            $resultCount = 10;
            $offset = ($page - 1) * $resultCount;
            $countTable = "SELECT DISTINCT user.user_id 
            FROM classroom_form_question_users user 
            LEFT JOIN classroom_student info ON user.user_id = info.student_id 
            WHERE user.user_id IS NOT NULL {$search} 
            AND user.form_id = '{$form_id}'";
            $totalData = query_sqli($countTable);
            $countData = mysqli_num_rows($totalData);
            $totalCount = $countData ? $countData : 0;
            $tableData = "
            SELECT DISTINCT user.user_id AS student_id, student_firstname_en, student_lastname_en, student_firstname_th, student_lastname_th
            FROM classroom_form_question_users user 
            LEFT JOIN classroom_student info ON user.user_id = info.student_id 
            WHERE user.user_id IS NOT NULL $search AND user.form_id = '{$form_id}'
            LIMIT $offset, $resultCount";
            $query = query_sqli($tableData);
            while ($row = mysqli_fetch_assoc($query)) {
                $student_name = (!empty($row['student_firstname_en'])) ? 
                "{$row['student_firstname_en']} {$row['student_lastname_en']}" : 
                "{$row['student_lastname_th']} {$row['student_lastname_th']}";
                $data[] = [
                    'id' => $row['student_id'],
                    'col' => $student_name,
                    'code' => $row['student_id'],
                    'desc' => $student_name,
                    'total_count' => $totalCount, 
                ];
            }
            if (empty($data)) {
                $empty[] = ['id' => '','col' => '', 'total_count' => 0];
                echo json_encode($empty);
                exit();
            } else {
                echo json_encode($data);
                exit();
            }
        } else if($_GET['action'] === 'filter_gender') {
            $term = isset($_GET['term']) ? trim($_GET['term']) : ''; 
            $data = [];
            $classroom_gender = [
                ['gender_id' => 'M', 'gender_desc' => 'Male'],
                ['gender_id' => 'F', 'gender_desc' => 'Female'],
            ];
            foreach ($classroom_gender as $value) {
                if ($term === '' || stripos($value['gender_desc'], $term) !== false) {
                    $data[] = [
                        'id' => $value['gender_id'],
                        'col' => $value['gender_desc'],
                        'total_count' => count($classroomg_gender),
                        'code' => $value['gender_id'],
                        'desc' => $value['gender_desc'],
                    ];
                }
            }
            if (empty($data)) {
                $data[] = ['id' => '','col' => '', 'total_count' => ''];
            }
            echo json_encode($data);
            exit();
        }else if($_GET['action'] === 'export_excel') { 
            $form_id        = isset($_GET['form_id']) ? escape_string($_GET['form_id']) : '';
            $date_create    = isset($_GET['date_create']) ? escape_string($_GET['date_create']) : '';
            $user_id        = isset($_GET['user_id']) ? escape_string($_GET['user_id']) : '';    
            $gender_id      = isset($_GET['gender_id']) ? escape_string($_GET['gender_id']) : '';
            $filter = "";
            $joinTables = "";
            if (!$form_id) {
                echoError("Error: Server error.");
                exit();
            }
            if ($user_id) {
                $filter .= " and form_user.user_id = '{$user_id}' ";
            }
            if($gender_id) {
                $filter .= " and i.student_gender = '{$gender_id}' ";
            }
            if ($gender_id) {
                $joinTables .= " LEFT JOIN classroom_student i ON i.student_id = form_user.user_id ";
            }
            if($date_create) {
                $date = explode('-', $date_create);
                $date_st = trim($date[0]);
                $date_ed = (trim($date[1])) ? trim($date[1]) : trim($date[0]);
                $data_st = substr($date_st, -4) . '-' . substr($date_st, 3, 2) . '-' . substr($date_st, 0, 2);
                $data_ed = substr($date_ed, -4) . '-' . substr($date_ed, 3, 2) . '-' . substr($date_ed, 0, 2);
                $filter .= " AND date(form_user.date_create) BETWEEN date('{$data_st}') AND date('{$data_ed}') ";
            }
            require_once $base_include . '/crm/phpexcel/Classes/PHPExcel.php';
            require_once $base_include . '/crm/phpexcel/Classes/PHPExcel/IOFactory.php';
            try {
				$objPHPExcel = new PHPExcel();
				$objPHPExcel->getProperties()->setCreator("System Login");
				$objPHPExcel->getProperties()->setLastModifiedBy("System Command");
				$objPHPExcel->getProperties()->setTitle("Office XLSX Test Document");
				$objPHPExcel->getProperties()->setSubject("Office XLSX Test Document");
				$objPHPExcel->getProperties()->setDescription("Test document for Office XLSX, generated using PHP classes.");
				$objPHPExcel->getProperties()->setKeywords("office openxml php");
				$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(1);
				$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.75);
				$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.75);
				$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(1);
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                $objPHPExcel->getActiveSheet()->setTitle('Register From Report');
                $questions = getQuestions($form_id);
                if (!is_array($questions)) {
                    $questions = explode(',', $questions);
                }
                $lastColumnIndex = count($questions) + 2; 
                $lastColumn = PHPExcel_Cell::stringFromColumnIndex($lastColumnIndex - 1);
				$objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true)->setSize(10);
                $responseData = getUserForm($form_id, $filter, $joinTables);
                $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue("A1", "TIME STAMP")
                ->setCellValue("B1", "USER");
                $Color = new PHPExcel_Style_Color();
                $Color->setRGB('07006F');
                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $lastColumn . '1')->getFont()->setColor($Color);
                $objPHPExcel->getActiveSheet()
                ->getStyle('A1:' . $lastColumn . '1')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('EFEEFF');
                $col = 'C';  
                foreach ($questions as $question) {
                    $questionTxt = getQuestionData($question); 
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($col . '1', $questionTxt ? $questionTxt : '-- Unknown Question');  
                    $col = chr(ord($col) + 1); 
                }            
                $row = 2;
                foreach ($responseData as $key => $record) {
                    $innerRecord = $record[0]; 
                    $objPHPExcel->getActiveSheet()
                                ->setCellValue("A" . $row, "{$innerRecord['date_stamp']}") 
                                ->setCellValue("B" . $row, "{$innerRecord['user']}"); 
                    $col = 'C';  
                    foreach ($questions as $question) {
                        $answer = isset($innerRecord[$question]) ? $innerRecord[$question] : ''; 
                        $objPHPExcel->getActiveSheet()
                            ->setCellValue($col . $row, "{$answer}");
                        $col = chr(ord($col) + 1);
                    }
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
                $col = 'C';
                foreach ($questions as $question) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                    $col = chr(ord($col) + 1);
                }
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                header('Content-type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="REGISTER_FORM_REPORT_' . date("Ymd") . '.xls"');
                $objWriter->save('php://output');
                $response = [
                    'status' => true,
                    'message' => 'Excel file created successfully',
                ];
            } catch (Exception $e) {
                $response = [
                    'status' => false,
                    'message' => 'Error occurred: ' . $e->getMessage(),
                ];
            }
            echo json_encode($response);
            exit();
        }
    }
    function createClassroomForm($formName, $classroomId) {
        global $mysqli;
        $code = "INSERT INTO classroom_forms (form_id, form_name, emp_create, comp_id, classroom_id, date_create, emp_update, date_update) VALUES (NULL, '{$formName}', '{$_SESSION['emp_id']}', '{$_SESSION['comp_id']}', '{$classroomId}', NOW(), '{$_SESSION['comp_id']}', NOW());";
        $query = $mysqli->query($code);
        $form_id = $mysqli->insert_id;
        return ($query) ? $form_id : '';
    }
    function updateClassroomForm($formName, $formId) {
        global $mysqli;
        if ($formId) {
            $code = "UPDATE classroom_forms SET form_name = '{$formName}', emp_update = '{$_SESSION['emp_id']}', date_update = NOW() WHERE form_id = '{$formId}'";
            $query = $mysqli->query($code);
        }
        return ($query) ? true : false;
    }
    function clearChoices($questionId) {
        $query = query_sqli("UPDATE classroom_form_choices SET status = 1 WHERE question_id = '{$questionId}' AND status = 0");
        return ($query) ? true : false;
    }
    function insertOptionsData($questionId, $choiceText) {
        global $mysqli;
        $choice_id = "";
        if ($questionId) {
            $code = "INSERT INTO classroom_form_choices (choice_id, question_id, choice_text, status) VALUES (NULL, '{$questionId}', '{$choiceText}', 0)";
            $query = $mysqli->query($code);
            $choice_id = $mysqli->insert_id;
        }
        return ($query) ? $choice_id : '';
    }
    function updateOptionsID($choiceId, $choiceText) {
        if ($choiceId) {
            $code = "UPDATE classroom_form_choices SET choice_text = '{$choiceText}' WHERE choice_id = '{$choiceId}'";
            $query = query_sqli($code);
        }
        return ($query) ? true : false;
    }
    function getUserInfo($user_id) {
        $sql = "SELECT student_firstname_en, student_lastname_en, student_nickname_en, student_image_profile AS image FROM classroom_student WHERE student_id = '{$user_id}'";
        $result = query_sqli($sql);
        $users = [];
        while ($User = mysqli_fetch_assoc($result)) {
            if ($User['image']) {
                $User['image'] = GetUrl($User['image']);
            }
            $users[] = [
                'user_id'   => $user_id,
                'image'     => $User['image'],
                'firstname' => $User['student_firstname_en'],
                'lastname'  => $User['student_lastname_en'],
                'nickname'  => $User['student_nickname_en']
            ];
        }
        return $users;
    }
    function getQuestions($formId) {
        $data = [];
        if ($formId) {
            $code = "SELECT GROUP_CONCAT(question_id) AS questions FROM classroom_form_questions WHERE form_id = '{$formId}' AND status = 0";
            $query = query_sqli($code); 
            while ($row = mysqli_fetch_assoc($query)) {
                $data = $row;
            }
        }
        return !empty($data) ? $data['questions'] : [];
    }
    function getQuestionData($questionId) { 
        $data = [];
        if ($questionId) {
            $code = "SELECT question_text FROM classroom_form_questions WHERE question_id = '{$questionId}' AND status = 0";
            $query = query_sqli($code); 
            while ($row = mysqli_fetch_assoc($query)) {
                $data = $row;
            }
        }
        return !empty($data) ? $data['question_text'] : '';
    }
    function getChoiceData($choiceId) {
        $data = [];
        if ($choiceId) {
            $code = "SELECT choice_text FROM classroom_form_choices WHERE choice_id = '{$choiceId}' AND status = 0";
            $query = query_sqli($code); 
            while ($row = mysqli_fetch_assoc($query)) {
                $data = $row;
            }
        }
        return !empty($data) ? $data['choice_text'] : '';
    }
    function getDetails($questionList, $student_id, $dateCreate) {
        $data = [];
        if ($student_id) {
            $data[0] = [
                'date_stamp' => $dateCreate,
                'user' => getUserInfomation($student_id)
            ];
            if (count($questionList) > 0) {
                foreach ($questionList as $questionId) {
                    $code = "SELECT answer_text,question_id,answer_type,choice_id,other_text FROM classroom_form_answer_users WHERE question_id = '{$questionId}' AND user_id = '{$student_id}' AND status = 0";
                    $query = query_sqli($code); 
                    while ($row = mysqli_fetch_assoc($query)) {
                        $questionId = $row['question_id'];
                        $answerType = $row['answer_type'];
                        $choiceId = $row['choice_id'];
                        switch ($answerType) {
                            case '0':
                                $AnswerTxt = $row['answer_text'];
                                break;
                            case '1':
                                $AnswerTxt = getChoiceData($choiceId);
                                break;
                            case '2':
                                $AnswerTxt = '(Other) ' . $row['other_text'];
                                break;
                            default:
                                $AnswerTxt = '';
                                break;
                        }
                        $data[0][$questionId] = $AnswerTxt;
                    }
                }
            }
        }
        return !empty($data) ? $data : [];
    }
    function getUserInfomation($student_id) {
        $student_name = '';
        if ($student_id) {
            $code = "SELECT student_id AS student_id, student_firstname_en, student_lastname_en, student_firstname_th, student_lastname_th FROM classroom_student WHERE student_id = '{$student_id}'";
            $query = query_sqli($code);
            $result = mysqli_fetch_assoc($query);
            $student_name = (!empty($result['student_firstname_en'])) ? 
            "{$result['student_firstname_en']} {$result['student_lastname_en']}" : 
            "{$result['student_firstname_th']} {$result['student_lastname_th']}";
        }
        return $student_name ? $emp_nstudent_nameame : '';
    }
    function getUserForm($formId, $filterConditions, $joinConditions) { 
        $data = [];
        if ($formId) {
            $code = "SELECT DATE_FORMAT(form_user.date_create, '%Y-%m-%d %H:%i') AS date_create, form_user.user_id, form_user.question_list FROM classroom_form_question_users form_user {$joinConditions} WHERE form_user.form_id = '{$formId}' {$filterConditions} ORDER BY form_user.date_create ASC";
            $query = query_sqli($code);
            while ($row = mysqli_fetch_assoc($query)) {
                $student_id = $row['user_id'] ? $row['user_id'] : '';
                $dateCreate = $row['date_create'] ? $row['date_create'] : '';
                $questionList = explode(',', $row['question_list']);
                if ($student_id) {
                    $data[] = getDetails($questionList, $student_id, $dateCreate);
                }
            }
        }
        return !empty($data) ? $data : [];
    }