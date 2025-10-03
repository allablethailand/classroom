<?php   
    function notiMail($classroom_id, $student_id, $mail_type) {
        global $base_include, $mysqli;
        $student_id = (int) $student_id;
        $classroom_id = (int) $classroom_id;
        if ($student_id <= 0 || $classroom_id <= 0) return;
        $email_data = select_data("student_email", "classroom_student", "where student_id = '{$student_id}'");
        $email = !empty($email_data[0]['student_email']) ? $email_data[0]['student_email'] : '';
        if (empty($email)) return;
        $classroom_data = select_data("classroom_name", "classroom_template", "where classroom_id = '{$classroom_id}'");
        if (empty($classroom_data)) return;
        $classroom_name = $classroom_data[0]['classroom_name'];
        $subject = "ORIGAMI ACADEMY • {$classroom_name}";
        switch ($mail_type) {
            case 'Register':
                $email_key = "and mail_subject = 'Register'";
                autoApprove($base_include, $classroom_id, $student_id);
                break;
            case 'Rejection':
                $email_key = "and mail_subject = 'Rejection'";
                break;
            case 'Approve':
                $email_key = "and mail_subject = 'Approve'";
                break;
            case 'Receive Certificate':
                $email_key = "and mail_subject = 'Receive Certificate'";
                break;
            default:
                $mail_type = mysqli_real_escape_string($mysqli, $mail_type);
                $email_key = "and mail_template_id = '{$mail_type}'";
        }
        $template = select_data("mail_subject,mail_description", "classroom_mail_template", "where classroom_id = '{$classroom_id}' and status = 0 {$email_key}");
        if (empty($template)) return;
        $mail_subject = $template[0]['mail_subject'];
        $mail_description = $template[0]['mail_description'];
        $subject .= " {$mail_subject}";
        $body_raw = previewTemplate($classroom_id, $mail_description, $student_id);
        $body = (stripos($body_raw, '<body') !== false) 
            ? "<!DOCTYPE html><html>{$body_raw}</html>"
            : "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width'></head><body>{$body_raw}</body></html>";
        sendPHPMailer($email, $subject, $body);
    }
    function sendPHPMailer($to, $subject, $html_body) {
        global $mysqli;
        $to = mysqli_real_escape_string($mysqli, $to);
        $subject = mysqli_real_escape_string($mysqli, $subject);
        $html_body = mysqli_real_escape_string($mysqli, $html_body);
        insert_data("queue_emails","(mail_to,subject,body,status,date_create,date_modify,module)","('{$to}','{$subject}','{$html_body}',0,NOW(),NOW(),'academy')");
    }
    function classroomData($classroom_id) {
        $classroom_id = (int) $classroom_id;
        if ($classroom_id <= 0) return [];
        $result = select_data(
            "
                template.classroom_poster as academy_logo,
                template.classroom_name,
                date_format(template.classroom_start,'%Y/%m/%d %H:%i') as classroom_start,
                date_format(template.classroom_end,'%Y/%m/%d %H:%i') as classroom_end,
                (
                    CASE
                        WHEN template.classroom_type = 'onsite' then template.classroom_source
                        ELSE pf.platforms_name
                    END
                ) as classroom_location,
                template.classroom_information,
                template.contact_us
            ",
            "classroom_template template",
            "left join data_meeting_platforms pf on pf.platforms_id = template.classroom_plateform 
            where template.classroom_id = '{$classroom_id}'"
        );
        if (empty($result)) return [];
        $classroom = $result[0];
        return [
            'academy_logo' => ($classroom['academy_logo']) ? GetPublicUrl($classroom['academy_logo']) : '',
            'classroom_name' => ($classroom['classroom_name']) ? $classroom['classroom_name'] : '',
            'classroom_start' => ($classroom['classroom_start']) ? $classroom['classroom_start'] : '',
            'classroom_end' => ($classroom['classroom_end']) ? $classroom['classroom_end'] : '',
            'classroom_location' => ($classroom['classroom_location']) ? $classroom['classroom_location'] : '',
            'classroom_information' => ($classroom['classroom_information']) ? htmlspecialchars_decode($classroom['classroom_information']) : '',
            'contact_us' => ($classroom['contact_us']) ? htmlspecialchars_decode($classroom['contact_us']) : '',
        ];
    }
    function getStudentData($classroom_id, $student_id) {
        $student_id = (int) $student_id;
        if ($student_id <= 0) return [];
        $columns = "CONCAT(student_firstname_en, ' ', student_lastname_en) AS student_name,
            student_image_profile,
            student_email,
            student_mobile,
            student_company,
            student_position,
            student_username,
            student_password,
            student_password_key";
        $joins = "WHERE student_id = '{$student_id}'";
        $Student = select_data($columns, "classroom_student", $joins);
        if (empty($Student)) return [];
        $s = $Student[0];
        $avatar = $s['student_image_profile'] ? GetPublicUrl($s['student_image_profile']) : '';
        $password = ($s['student_password']) ? decryptToken($s['student_password'], $s['student_password_key']) : '';
        return [
            $s['student_name'] ?: '',
            $avatar,
            $s['student_email'] ?: '',
            $s['student_mobile'] ?: '',
            $s['student_company'] ?: '',
            $s['student_position'] ?: '',
            $s['student_username'] ?: '',
            $password ?: '',
        ];
    }
    function previewTemplate($classroom_id, $template, $student_id) {
        global $domain_name;
        $template = str_replace('http://origami.local/', $domain_name, $template);
        $origami_academy_logo = $domain_name . 'images/ogm_logo.png';
        $origami_academy_logo_img = '<img src="' . $origami_academy_logo . '" style="height:125px;">';
        $classroom = classroomData($classroom_id);
        $tenant = select_data("tenant_key", "ogm_tenant", "where comp_id = '{$_SESSION['comp_id']}' and status = 0");
        $tenant_url = $domain_name;
        if (!empty($tenant)) {
            $tenant_url .= $tenant[0]['tenant_key'];
        }
        $replacements = [
            '{{origamiAcademyLogo}}' => $origami_academy_logo_img,
            '{{academyLogo}}' => ($classroom['academy_logo']) ? '<img src="' . $classroom['academy_logo'] . '" style="max-width:100%; max-height:300px;">' : '',
            '{{academyName}}' => $classroom['classroom_name'],
            '{{academyStart}}' => $classroom['classroom_start'],
            '{{academyEnd}}' => $classroom['classroom_end'],
            '{{academyLocationName}}' => $classroom['classroom_location'],
            '{{academyInfomation}}' => $classroom['classroom_information'],
            '{{academyContactUs}}' => $classroom['contact_us'],
            'tenantLink' => $tenant_url,
        ];
        $student_keys = [
            '{{studentName}}', '{{studentAvatar}}', '{{studentEmail}}',
            '{{studentTel}}', '{{studentCompany}}', '{{studentPosition}}', '{{studentUsername}}',
            '{{studentPassword}}'
        ];
        $student_data = getStudentData($classroom_id, $student_id);
        foreach ($student_keys as $index => $key) {
            $replacements[$key] = !empty($student_data[$index]) ? $student_data[$index] : '';
        }
        $data = str_replace(array_keys($replacements), array_values($replacements), $template);
        $html = htmlspecialchars_decode($data);
        return $html;
    }
    function autoApprove($base_include, $classroom_id, $student_id) {
        $classroom_id = (int) $classroom_id;
        $student_id = (int) $student_id;
        if ($classroom_id <= 0 || $student_id <= 0) return;
        $AutoApprove = select_data("auto_approve", "classroom_template", "where classroom_id = '{$classroom_id}'");
        if (empty($AutoApprove) || (int) $AutoApprove[0]['auto_approve'] !== 0) return;
        $ApStatus = select_data("approve_status", "classroom_student_join", "where student_id = '{$student_id}' and classroom_id = '{$classroom_id}'");
        if (empty($ApStatus) || (int) $ApStatus[0]['approve_status'] != 0) return;
        update_data(
            "classroom_student_join", 
            "approve_status = 1, approve_date = NOW()", 
            "student_id = '{$student_id}' and classroom_id = '{$classroom_id}'"
        );
        generateUser($classroom_id, $student_id);
        notiMail($classroom_id, $student_id, 'Approve');
    }
    function generateUser($classroom_id, $student_id) {
        global $mysqli;
        $classroom_id = (int) $classroom_id;
        $student_id = (int) $student_id;
        if ($classroom_id <= 0 || $student_id <= 0) return;
        $template = select_data(
            "
                auto_username,
                auto_username_type,
                auto_username_length,
                auto_password,
                password_type,
                auto_password_type,
                auto_password_length,
                auto_password_custom,
                comp_id
            ",
            "classroom_template",
            "where classroom_id = '{$classroom_id}'"
        );
        if (empty($template)) return;
        $auto_username = (int) $template[0]['auto_username'];
        $auto_username_type = $template[0]['auto_username_type'];
        $auto_username_length = (int) $template[0]['auto_username_length'];
        $auto_password = (int) $template[0]['auto_password'];
        $password_type = $template[0]['password_type'];
        $auto_password_type = $template[0]['auto_password_type'];
        $auto_password_length = (int) $template[0]['auto_password_length'];
        $auto_password_custom = $template[0]['auto_password_custom'];
        $comp_id = (int) $template[0]['comp_id'];
        if($auto_username == 1 && $auto_password == 1) {
            return;
        }
        $studentDataResult = select_data(
            "student_mobile, student_username, student_password, student_email",
            "classroom_student",
            "where student_id = '{$student_id}'"
        );
        if (empty($studentDataResult)) return;
        $studentData = $studentDataResult[0];
        $student_mobile = preg_replace('/[^0-9]/', '', $studentData['student_mobile']);
        $student_email = trim(strtolower($studentData['student_email']));
        $student_username = $studentData['student_username'];
        $student_password = $studentData['student_password'];
        $needUpdate = false;
        $username = $student_username;
        $password = $student_password;
        if ($auto_username == 0) {
            $include_array = array_map('intval', explode(',', $auto_username_type));
            $username = '';
            if (!empty($student_mobile)) {
                $username = (substr($student_mobile, 0, 1) == '0') ? $student_mobile : '0' . $student_mobile;
                $checkExist = select_data("count(*) as total", "classroom_student", 
                    "where student_username = '{$username}' and comp_id = '{$comp_id}'");
                $checkExist = !empty($checkExist) ? (int)$checkExist[0]['total'] : 0;
                if ($checkExist > 0 && !empty($student_email)) {
                    $username = $student_email;
                    $checkExist = select_data("count(*) as total", "classroom_student", 
                        "where student_username = '{$username}' and comp_id = '{$comp_id}'");
                    $checkExist = !empty($checkExist) ? (int)$checkExist[0]['total'] : 0;
                }
                if ($checkExist > 0) {
                    $username = substr(str_shuffle('0123456789'), 0, 10);
                }
            } elseif (!empty($student_email)) {
                $username = $student_email;
                $checkExist = select_data("count(*) as total", "classroom_student", 
                    "where student_username = '{$username}' and comp_id = '{$comp_id}'");
                $checkExist = !empty($checkExist) ? (int)$checkExist[0]['total'] : 0;
                if ($checkExist > 0) {
                    $username = generateSecurity($include_array,$auto_username_length);
                }
            } else {
                $username = generateSecurity($include_array,$auto_username_length);
            }
            $username = strtolower($username);
            $needUpdate = true;
        }
        if ($auto_password == 0) {
            if($password_type == 'custom') {
                $password = $auto_password_custom;
            } else {
                $include_array = array_map('intval', explode(',', $auto_password_type));
                $password = generateSecurity($include_array,$auto_password_length);
            }
            $needUpdate = true;
        }
        if ($needUpdate) {
            $username = mysqli_real_escape_string($mysqli, $username);
            $password = mysqli_real_escape_string($mysqli, $password);
            $student_password_key = bin2hex(openssl_random_pseudo_bytes(16));
            $pwd = encryptToken($password, $student_password_key);
            $valueUpd = "student_username = '{$username}', student_password = '{$pwd}', student_password_key = '{$student_password_key}'";
            $whereUpd = "student_id = '{$student_id}'";
            update_data("classroom_student", $valueUpd, $whereUpd);
        }
    }
    function generateSecurity($include_char, $length) {
        $length = (int) $length;
        if ($length <= 0) return '';
        $char_sets = array(
            1 => '0123456789',
            2 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            3 => 'abcdefghijklmnopqrstuvwxyz',
            4 => '!@#$%'
        );
        $pool = '';
        foreach ($include_char as $type) {
            if (isset($char_sets[$type])) {
                $pool .= $char_sets[$type];
            }
        }
        if ($pool === '') {
            return '';
        }
        $result = '';
        $max_index = strlen($pool) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $pool[mt_rand(0, $max_index)];
        }
        return $result;
    }
?>