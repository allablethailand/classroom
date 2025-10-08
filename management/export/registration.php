<?php
    session_start();
    $base_include = $_SERVER['DOCUMENT_ROOT'];
    $base_path = '';
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $request_uri = $_SERVER['REQUEST_URI'];
        $exl_path = explode('/', $request_uri);
        if (!file_exists($base_include . "/dashboard.php")) {
            $base_path .= "/" . $exl_path[1];
        }
        $base_include .= "/" . $exl_path[1];
    }
    define('BASE_PATH', $base_path);
    define('BASE_INCLUDE', $base_include);
    require_once BASE_INCLUDE . '/lib/connect_sqli.php';
    $classroom_id = isset($_POST['classroom_id']) ? trim($_POST['classroom_id']) : '';
    $status_id = isset($_POST['status_id']) && is_array($_POST['status_id']) ? $_POST['status_id'] : array();
    $filter_date = isset($_POST['filter_date']) ? trim($_POST['filter_date']) : '';
    $filter_channel = isset($_POST['filter_channel']) ? trim($_POST['filter_channel']) : '';
    if (empty($classroom_id)) {
        die('Error: Classroom ID is required');
    }
    $now = date('Y-m-d H:i:s');
    $date_show = date('Y/m/d H:i:s', strtotime($now));
    $date_filename = date('YmdHis');
    function get_company_info($comp_id) {
        if (empty($comp_id)) {
            return '';
        }
        $comp_data = select_data(
            "IFNULL(comp_description, comp_description_thai) AS comp_description", 
            "m_company", 
            "WHERE comp_id = '" . mysqli_real_escape_string($GLOBALS['conn'], $comp_id) . "'"
        );
        return isset($comp_data[0]['comp_description']) ? $comp_data[0]['comp_description'] : '';
    }
    function safe_escape($value) {
        global $conn;
        if (!isset($conn)) {
            return addslashes($value);
        }
        return mysqli_real_escape_string($conn, $value);
    }
    function get_form_questions($form_id) {
        $form_data = array();
        if (empty($form_id)) {
            return $form_data;
        }
        $questions = select_data(
            "question_id, question_text, question_type, has_other_option, has_options, has_required, `order`",
            "classroom_form_questions",
            "WHERE form_id = '" . safe_escape($form_id) . "' AND status = 0 ORDER BY `order` ASC"
        );
        if (empty($questions)) {
            return $form_data;
        }
        foreach ($questions as $q) {
            $question_id = $q['question_id'];
            $option_item = array();
            if ($q['has_options'] == 1) {
                $items = select_data(
                    "choice_id, choice_text",
                    "classroom_form_choices",
                    "WHERE question_id = '" . safe_escape($question_id) . "' AND status = 0 ORDER BY choice_id ASC"
                );
                foreach ($items as $i) {
                    $option_item[] = array(
                        'choice_id' => $i['choice_id'],
                        'choice_text' => $i['choice_text']
                    );
                }
            }
            $form_data[] = array(
                'question_id' => $question_id,
                'question_text' => $q['question_text'],
                'question_type' => $q['question_type'],
                'has_other_option' => $q['has_other_option'],
                'has_options' => $q['has_options'],
                'has_required' => $q['has_required'],
                'option_item' => $option_item
            );
        }
        return $form_data;
    }
    function get_student_answer($classroom_id, $student_id, $question_id) {
        $answers = select_data(
            "answer_text, choice_id, other_text", 
            "classroom_form_answer_users", 
            "WHERE classroom_id = '" . safe_escape($classroom_id) . "' 
            AND student_id = '" . safe_escape($student_id) . "' 
            AND question_id = '" . safe_escape($question_id) . "' 
            AND status = 0"
        );
        $answer_data = array();
        foreach ($answers as $ans) {
            if (!empty($ans['answer_text'])) {
                $answer_data[] = $ans['answer_text'];
            } else if (!empty($ans['other_text'])) {
                $answer_data[] = $ans['other_text'];
            } else if (!empty($ans['choice_id'])) {
                $choice = select_data(
                    "choice_text", 
                    "classroom_form_choices", 
                    "WHERE choice_id = '" . safe_escape($ans['choice_id']) . "'"
                );
                if (!empty($choice)) {
                    $answer_data[] = $choice[0]['choice_text'];
                }
            }
        }
        return implode(', ', $answer_data);
    }
    function format_template_value($column_name, $student) {
        $text = '';
        switch ($column_name) {
            case 'student_birth_date':
                if (!empty($student[$column_name])) {
                    $age = !empty($student['student_age']) ? ' (' . $student['student_age'] . ')' : '';
                    $text = $student[$column_name] . $age;
                }
                break;
            case 'student_password':
                if (!empty($student[$column_name]) && !empty($student['student_password_key'])) {
                    $text = decryptToken($student['student_password'], $student['student_password_key']);
                }
                break;
            case 'copy_of_idcard':
            case 'copy_of_passport':
            case 'work_certificate':
            case 'company_certificate':
            case 'student_image_profile':
                if (!empty($student[$column_name])) {
                    $text = GetPublicUrl($student[$column_name]);
                }
                break;
            default:
                $text = isset($student[$column_name]) ? $student[$column_name] : '';
        }
        return $text;
    }
    $classrooms = select_data(
        "template.classroom_id,
        template.classroom_name, 
        DATE_FORMAT(template.classroom_start, '%Y/%m/%d %H:%i') AS classroom_start, 
        DATE_FORMAT(template.classroom_end, '%Y/%m/%d %H:%i') AS classroom_end,
        template.classroom_type,
        CASE
            WHEN template.classroom_type = 'online' THEN pf.platforms_name
            ELSE template.classroom_plateform
        END AS classroom_place,
        template.classroom_source,
        template.register_template", 
        "classroom_template template", 
        "LEFT JOIN data_meeting_platforms pf ON pf.platforms_id = template.classroom_plateform 
        WHERE template.classroom_id = '" . safe_escape($classroom_id) . "'"
    );
    if (empty($classrooms)) {
        die('Error: Classroom not found');
    }
    $classroom = $classrooms[0];
    $classroom_name = $classroom['classroom_name'];
    $classroom_start = $classroom['classroom_start'];
    $classroom_end = $classroom['classroom_end'];
    $classroom_type = $classroom['classroom_type'];
    $classroom_place = $classroom['classroom_place'];
    $classroom_source = $classroom['classroom_source'];
    $classroom_location = ($classroom_type == 'online') ? $classroom_place : $classroom_source;
    $register_template = $classroom['register_template'];
    $template_ids = explode(',', $register_template);
    $template_ids = array_filter($template_ids);
    if (empty($template_ids)) {
        $templates = array();
    } else {
        $template_ids_safe = implode(',', array_map('intval', $template_ids));
        $templates = select_data(
            "template_order, template_name_en, templace_column", 
            "classroom_register_template", 
            "WHERE template_id IN ($template_ids_safe) ORDER BY template_order ASC"
        );
    }
    $forms = select_data(
        "form_id",
        "classroom_forms",
        "WHERE classroom_id = '" . safe_escape($classroom['classroom_id']) . "'"
    );
    $form_data = array();
    $form_id = '';
    if (!empty($forms)) {
        $form = $forms[0];
        $form_id = $form['form_id'];
        $form_data = get_form_questions($form_id);
    }
    $comp_name = '';
    if (isset($_SESSION['comp_id'])) {
        $comp_name = get_company_info($_SESSION['comp_id']);
    }
    $filter = "";
    if (!empty($filter_date)) {
        $date_parts = explode('-', $filter_date);
        $date_st = trim($date_parts[0]);
        $date_ed = isset($date_parts[1]) ? trim($date_parts[1]) : trim($date_parts[0]);
        $data_st = substr($date_st, -4) . '-' . substr($date_st, 3, 2) . '-' . substr($date_st, 0, 2);
        $data_ed = substr($date_ed, -4) . '-' . substr($date_ed, 3, 2) . '-' . substr($date_ed, 0, 2);
        $filter .= " AND DATE(cjoin.register_date) BETWEEN DATE('" . safe_escape($data_st) . "') AND DATE('" . safe_escape($data_ed) . "') ";
    }
    if (!empty($filter_channel)) {
        $filter .= " AND cjoin.channel_id = '" . safe_escape($filter_channel) . "' ";
    }
    $channel_name = 'All Channel';
    if (!empty($filter_channel)) {
        $channels = select_data(
            "channel_name", 
            "classroom_channel", 
            "WHERE channel_id = '" . safe_escape($filter_channel) . "'"
        );
        if (!empty($channels)) {
            $channel_name = $channels[0]['channel_name'];
        }
    }
    $students = select_data(
        "stu.student_id,
        '' AS join_status_id, 
        c.channel_name,
        DATE_FORMAT(cjoin.register_date, '%Y/%m/%d %H:%i:%s') AS register_date,
        CASE
            WHEN stu.student_perfix = 'Mr.' THEN 'Mr.'
            WHEN stu.student_perfix = 'Mrs.' THEN 'Mrs.'
            WHEN stu.student_perfix = 'Miss' THEN 'Miss'
            ELSE stu.student_perfix_other
        END AS student_perfix,
        stu.student_firstname_en,
        stu.student_lastname_en,
        stu.student_firstname_th,
        stu.student_lastname_th,
        stu.student_nickname_en,
        stu.student_nickname_th,
        CASE
            WHEN stu.student_gender = 'M' THEN 'Male' 
            WHEN stu.student_gender = 'F' THEN 'Female' 
            WHEN stu.student_gender = 'O' THEN 'Other' 
        END AS student_gender,
        stu.student_idcard,
        stu.student_passport,
        DATE_FORMAT(stu.student_passport_expire, '%Y/%m/%d') AS student_passport_expire,
        stu.student_image_profile,
        stu.student_email,
        CONCAT(stu.dial_code, '', stu.student_mobile) AS student_mobile,
        stu.student_company,
        stu.student_position,
        stu.student_username,
        stu.student_password,
        n.nationality_name,
        stu.student_reference,
        DATE_FORMAT(stu.student_birth_date, '%Y/%m/%d') AS student_birth_date,
        CASE 
            WHEN stu.student_birth_date IS NULL OR stu.student_birth_date = '' THEN ''
            ELSE CONCAT(TIMESTAMPDIFF(YEAR, stu.student_birth_date, CURDATE()), ' Yrs.') 
        END AS student_age,
        stu.student_password_key,
        stu.copy_of_idcard,
        stu.copy_of_passport,
        stu.work_certificate,
        stu.company_certificate,
        (
            CASE
                when cjoin.payment_status = 1 then 'Payment'
                when cjoin.payment_status = 2 then 'Not Payment'
                when cjoin.approve_status = 1 and cjoin.payment_status = 0 then 'Approve'
                when cjoin.approve_status = 2 and cjoin.payment_status = 0 then 'Not Approve'
                when cjoin.invite_status = 2 then 'Cancel'
                when cjoin.invite_status = 1 and cjoin.approve_status = 0 then 'Waiting Approve'
                when cjoin.invite_status = 0 and cjoin.approve_status = 0 then 'Lead'
            END
        ) as student_status", 
        "classroom_student stu", 
        "LEFT JOIN classroom_student_join cjoin ON cjoin.student_id = stu.student_id 
            LEFT JOIN m_nationality n ON n.nationality_id = stu.student_nationality
            LEFT JOIN classroom_channel c ON c.channel_id = cjoin.channel_id
            WHERE cjoin.classroom_id = '" . safe_escape($classroom_id) . "' AND cjoin.status = 0 $filter 
        ORDER BY cjoin.register_date DESC"
    );
    $statuses = select_data("status_id, status_name_en", "classroom_join_status", "");
    $status_lookup = array();
    foreach ($statuses as $st) {
        $status_lookup[$st['status_id']] = $st['status_name_en'];
    }
    $column_count = 4 + count($templates) + count($form_data);
    $file_name = 'Student_List_' . $classroom_id . '_' . $date_filename;
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($classroom_name); ?> • ORIGAMI SYSTEM</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/css/loading.css?v=<?php echo time(); ?>">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/dist/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
<table id="myTable" style="display: none;">
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            <?php echo htmlspecialchars($classroom_name); ?>
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            <?php echo htmlspecialchars($classroom_start); ?> - <?php echo htmlspecialchars($classroom_end); ?>
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            <?php echo htmlspecialchars($classroom_location); ?>
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            <?php echo htmlspecialchars($comp_name); ?>
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            Filter Date: <?php echo htmlspecialchars($filter_date); ?>
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            Filter Channel: <?php echo htmlspecialchars($channel_name); ?>
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            Total <?php echo number_format(count($students)); ?> results
        </td>
    </tr>
    <tr>
        <td colspan="<?php echo $column_count; ?>">
            Report date: <?php echo htmlspecialchars($date_show); ?>
        </td>
    </tr>
    <tr>
        <td>No.</td>
        <td>Register Date</td>
        <td>Channel</td>
        <td>Status</td>
        <?php foreach ($templates as $t): ?>
            <td><?php echo htmlspecialchars($t['template_name_en']); ?></td>
        <?php endforeach; ?>
        <?php foreach ($form_data as $q): ?>
            <td><?php echo htmlspecialchars($q['question_text']); ?></td>
        <?php endforeach; ?>
    </tr>
    <?php 
    $student_no = 1;
    foreach ($students as $s): 
        $status_name = isset($status_lookup[$s['join_status_id']]) && $status_lookup[$s['join_status_id']] ? $status_lookup[$s['join_status_id']] : 'N/A';
    ?>
    <tr>
        <td><?php echo $student_no++; ?></td>
        <td><?php echo htmlspecialchars($s['register_date']); ?></td>
        <td><?php echo htmlspecialchars($s['channel_name']); ?></td>
        <td><?php echo htmlspecialchars($s['student_status']); ?></td>
        <?php foreach ($templates as $t): 
            $column_name = $t['templace_column'];
            $text = format_template_value($column_name, $s);
        ?>
            <td><?php echo htmlspecialchars($text); ?></td>
        <?php endforeach; ?>
        <?php foreach ($form_data as $q): 
            $answer_text = get_student_answer($classroom_id, $s['student_id'], $q['question_id']);
        ?>
            <td><?php echo htmlspecialchars($answer_text); ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>
<script>
    $(document).ready(function() {
        setTimeout(function() {
            try {
                var table = document.getElementById("myTable");
                if (!table) {
                    throw new Error('Table not found');
                }
                var workbook = XLSX.utils.book_new();
                var worksheet = XLSX.utils.table_to_sheet(table);
                var colWidths = [];
                for (var i = 0; i < <?php echo $column_count; ?>; i++) {
                    colWidths.push({wch: 15});
                }
                worksheet['!cols'] = colWidths;
                XLSX.utils.book_append_sheet(workbook, worksheet, "Student List");
                XLSX.writeFile(workbook, "<?php echo htmlspecialchars($file_name); ?>.xlsx");
                setTimeout(function() {
                    window.close();
                }, 500);
            } catch (error) {
                console.error('Export error:', error);
                alert('เกิดข้อผิดพลาดในการ export ไฟล์: ' + error.message);
            }
        }, 800);
    });
</script>
</body>
</html>