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
    define('base_path', $base_path);
    define('base_include', $base_include);
    try {
        require_once($base_include . "/lib/connect_sqli.php"); 
        $classroom_id = isset($_GET['classroom_id']) ? $_GET['classroom_id'] : '';
        if (empty($classroom_id) || !is_numeric($classroom_id)) {
            throw new Exception("Invalid classroom ID");
        }
        $classrooms = select_data("
            template.register_template, template.classroom_name, 
            DATE_FORMAT(template.classroom_start,'%Y/%m/%d %H:%i') as classroom_start,
            DATE_FORMAT(template.classroom_end,'%Y/%m/%d %H:%i') as classroom_end,
            IFNULL(comp.comp_description, comp.comp_description_thai) as comp_description,
            DATE_FORMAT(NOW(),'%Y/%m/%d %H:%i:%s') as date_now,
            DATE_FORMAT(NOW(),'%Y%m%d%H%i%s') as date_filename,
            (
                case
                    when template.classroom_type = 'online' then IFNULL(pf.platforms_name, 'Online')
                    else IFNULL(template.classroom_source, 'TBD')
                end
            ) as classroom_place
        ", "classroom_template template", "
            LEFT JOIN data_meeting_platforms pf ON pf.platforms_id = template.classroom_plateform 
            LEFT JOIN m_company comp ON comp.comp_id = template.comp_id 
            WHERE template.classroom_id = '{$classroom_id}'
        ");
        if (empty($classrooms)) {
            throw new Exception("Classroom not found");
        }
        $classroom = $classrooms[0];
        $register_template = $classroom['register_template'];
        if (empty($register_template)) {
            $formats = select_data(
                "GROUP_CONCAT(template_id) AS register_template", 
                "classroom_register_template", 
                "WHERE is_default = 0 AND status = 0 ORDER BY template_order ASC"
            );
            $register_template = !empty($formats) ? $formats[0]['register_template'] : '';
        }
        if (empty($register_template)) {
            throw new Exception("No register template found");
        }
        $register_field = $register_template;
        $classroom_name = htmlspecialchars($classroom['classroom_name']);
        $classroom_start = $classroom['classroom_start'];
        $classroom_end = $classroom['classroom_end'];
        $comp_description = htmlspecialchars($classroom['comp_description']);
        $date_now = $classroom['date_now'];
        $date_filename = $classroom['date_filename'];
        $classroom_place = htmlspecialchars($classroom['classroom_place']);
        $fields = select_data(
            "template_id, template_name_en, template_name_th", 
            "classroom_register_template", 
            "WHERE template_id IN ($register_field) ORDER BY template_order ASC"
        );
        $form_data = [];
        $form = select_data("form_id", "classroom_forms", "WHERE classroom_id = '{$classroom_id}'");
        if (count($form) > 0) {
            $form_id = $form[0]['form_id'];
            $question = select_data(
                "question_id, question_text, question_type, has_options, has_required, has_other_option",
                "classroom_form_questions",
                "WHERE form_id = '{$form_id}' ORDER BY question_id ASC"
            );
            $quest = [];
            foreach ($question as $q) {
                $choice = [];
                if ($q['has_options'] == 1) {
                    $choice_data = select_data(
                        "choice_id, choice_text", 
                        "classroom_form_choices", 
                        "WHERE question_id = '{$q['question_id']}' AND status = 0 ORDER BY choice_id ASC"
                    );
                    foreach ($choice_data as $ch) {
                        $choice[] = [
                            'choice_id'   => $ch['choice_id'],
                            'choice_text' => $ch['choice_text']
                        ];
                    }
                }
                $quest[] = [
                    'question_id'      => $q['question_id'],
                    'question_text'    => $q['question_text'],
                    'question_type'    => $q['question_type'],
                    'has_other_option' => $q['has_other_option'],
                    'has_options'      => $q['has_options'],
                    'has_required'     => $q['has_required'],
                    'choice'           => $choice
                ];
            }
            $form_data[] = ['question' => $quest];
        }
        $column = count($fields);
        if (!empty($form_data)) {
            $column += count($form_data[0]['question']);
        }
        $file_name = 'ImportStudent-' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $classroom_name) . '-' . $date_filename;
    } catch (Exception $e) {
        error_log("Export Template Error: " . $e->getMessage());
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.close();</script>";
        exit;
    }
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title><?php echo $classroom_name; ?> • ORIGAMI SYSTEM</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/css/loading.css?v=<?php echo time(); ?>">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/dist/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
<div class="loading-container">
    <div class="spinner"></div>
    <div class="loading-text">กำลังสร้างไฟล์ Excel กรุณารอสักครู่...</div>
    <div class="progress-bar">
        <div class="progress-fill"></div>
    </div>
</div>
<table id="myTable" style="display:none;">
    <thead>
        <tr><th colspan="<?php echo $column; ?>" style="background: #4CAF50; color: white; font-size: 16px;"><?php echo $classroom_name; ?></th></tr>
        <tr><td colspan="<?php echo $column; ?>" style="background: #f9f9f9;"><strong>วันที่:</strong> <?php echo $classroom_start . " - " . $classroom_end; ?></td></tr>
        <tr><td colspan="<?php echo $column; ?>" style="background: #f9f9f9;"><strong>สถานที่:</strong> <?php echo $classroom_place; ?></td></tr>
        <tr><td colspan="<?php echo $column; ?>" style="background: #f9f9f9;"><strong>องค์กร:</strong> <?php echo $comp_description; ?></td></tr>
        <tr style="background: #e8f5e8;">
            <?php foreach ($fields as $field): ?>
                <th style="background: #4CAF50; color: white;">
                    <?php echo htmlspecialchars($field['template_name_en']) . "<br>[" . htmlspecialchars($field['template_name_th']) . "]"; ?>
                </th>
            <?php endforeach; ?>
            <?php if (!empty($form_data)): ?>
                <?php foreach ($form_data[0]['question'] as $q): ?>
                    <th style="background: #2196F3; color: white;">
                        <?php echo htmlspecialchars($q['question_text']); ?>
                        <?php if ($q['has_required'] == 1): ?>
                            <span style="color: #ffeb3b;">*</span>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <tr style="background: #fff3cd;">
            <?php foreach ($fields as $field): ?>
                <td style="font-style: italic; color: #856404;">ตัวอย่างข้อมูล</td>
            <?php endforeach; ?>
            <?php if (!empty($form_data)): ?>
                <?php foreach ($form_data[0]['question'] as $q): ?>
                    <td style="font-style: italic; color: #856404;">
                        <?php if ($q['question_type'] == 'short_answer'): ?>
                            ข้อความตอบ
                        <?php elseif ($q['question_type'] == 'multiple_choice' || $q['question_type'] == 'radio'): ?>
                            <?php echo !empty($q['choice']) ? htmlspecialchars($q['choice'][0]['choice_text']) : 'ตัวเลือก'; ?>
                        <?php elseif ($q['question_type'] == 'checkbox'): ?>
                            ตัวเลือก1,ตัวเลือก2
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            <?php endif; ?>
        </tr>
    </tbody>
</table>
<script>
    $(document).ready(function () {
        let downloadStarted = false;
        function startDownload() {
            if (downloadStarted) return;
            downloadStarted = true;
            try {
                let table = document.getElementById("myTable");
                if (!table) {
                    throw new Error('Table not found');
                }
                let wb = XLSX.utils.book_new();
                let ws = XLSX.utils.table_to_sheet(table);
                const colWidths = [];
                <?php for ($i = 0; $i < $column; $i++): ?>
                colWidths.push({ wch: 20 });
                <?php endfor; ?>
                ws['!cols'] = colWidths;
                XLSX.utils.book_append_sheet(wb, ws, "Student List");
                let questions = [];
                let choicesArray = [];
                let maxChoices = 0;
                <?php
                $js_questions = [];
                $js_choicesArray = [];
                if (!empty($form_data)) {
                    foreach ($form_data[0]['question'] as $q) {
                        $js_questions[] = $q['question_text'];
                        if ($q['has_options'] == 1 && !empty($q['choice'])) {
                            $choices = array_map(function ($c) {
                                return $c['choice_text'];
                            }, $q['choice']);
                            if ($q['has_other_option'] == 1) {
                                $choices[] = "other: [ข้อความอื่นๆ]";
                            }
                        } else {
                            $choices = ["กรอกข้อความ"];
                        }
                        $js_choicesArray[] = $choices;
                    }
                }
                ?>
                questions = <?php echo json_encode($js_questions, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT); ?>;
                choicesArray = <?php echo json_encode($js_choicesArray, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT); ?>;
                if (questions.length > 0) {
                    choicesArray.forEach(function(choices) {
                        if (choices.length > maxChoices) {
                            maxChoices = choices.length;
                        }
                    });
                    let formDescription = [];
                    formDescription.push(['คำถาม', 'ตัวเลือกการตอบ']);
                    for (let i = 0; i < questions.length; i++) {
                        formDescription.push([questions[i], '']);
                        for (let j = 0; j < choicesArray[i].length; j++) {
                            formDescription.push(['', choicesArray[i][j]]);
                        }
                        formDescription.push(['', '']); 
                    }
                    let ws2 = XLSX.utils.aoa_to_sheet(formDescription);
                    ws2['!cols'] = [{ wch: 30 }, { wch: 50 }];
                    XLSX.utils.book_append_sheet(wb, ws2, "Form Description");
                }
                let instructions = [
                    ['คำแนะนำการใช้งาน'],
                    [''],
                    ['1. กรอกข้อมูลในแถวที่ 6 เป็นต้นไป'],
                    ['2. อย่าแก้ไขหรือลบแถวหัวข้อ (แถว 1-5)'],
                    ['3. สำหรับคำถามแบบเลือกตอบ ให้ดูตัวเลือกในแผ่น Form Description'],
                    ['4. สำหรับคำถามแบบ Checkbox สามารถตอบหลายตัวเลือกได้ คั่นด้วยเครื่องหมายคอมม่า (,)'],
                    ['5. หากต้องการตอบ "อื่นๆ" ให้พิมพ์ "other: ข้อความที่ต้องการ"'],
                    ['6. ช่องที่มีเครื่องหมาย * คือช่องที่จำเป็นต้องกรอก'],
                    [''],
                    ['หมายเหตุ: บันทึกไฟล์เป็น .xlsx เท่านั้น']
                ];
                let ws3 = XLSX.utils.aoa_to_sheet(instructions);
                ws3['!cols'] = [{ wch: 60 }];
                XLSX.utils.book_append_sheet(wb, ws3, "Instructions");
                wb.Props = {
                    Title: "<?php echo addslashes($classroom_name); ?> - Student Import Template",
                    Subject: "Student Registration Template",
                    Author: "ORIGAMI SYSTEM",
                    CreatedDate: new Date()
                };
                XLSX.writeFile(wb, "<?php echo $file_name; ?>.xlsx");
                setTimeout(function () {
                    $('.loading-text').text('ดาวน์โหลดสำเร็จ! กำลังปิดหน้าต่าง...');
                    setTimeout(function () {
                        window.close();
                    }, 1000);
                }, 500);
            } catch (error) {
                console.error('Export error:', error);
                $('.loading-container').html(`
                    <div style="color: #f44336; text-align: center;">
                        <h3>เกิดข้อผิดพลาด</h3>
                        <p>${error.message}</p>
                        <button onclick="window.close()" style="padding: 10px 20px; margin-top: 10px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">ปิดหน้าต่าง</button>
                    </div>
                `);
            }
        }
        setTimeout(startDownload, 1000);
    });
</script>
</body>
</html>