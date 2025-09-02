<?php
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
    require_once $base_include.'/actions/func.php';
    if(isset($_POST) && $_POST['action'] == 'buildMenagementData') {
        $classroom_id = $_POST['classroom_id'];
        $classrooms = select_data(
            "
                template.classroom_name,
                template.classroom_information,
                template.classroom_poster,
                date_format(template.classroom_start, '%Y/%m/%d') as classroom_start_date,
                date_format(template.classroom_start, '%H:%i') as classroom_start_time,
                date_format(template.classroom_end, '%Y/%m/%d') as classroom_end_date,
                date_format(template.classroom_end, '%H:%i') as classroom_end_time,
                template.classroom_student,
                template.classroom_allow_register,
                date_format(template.classroom_open_register, '%Y/%m/%d') as classroom_open_register_date,
                date_format(template.classroom_open_register, '%H:%i') as classroom_open_register_time,
                date_format(template.classroom_close_register, '%Y/%m/%d') as classroom_close_register_date,
                date_format(template.classroom_close_register, '%H:%i') as classroom_close_register_time,
                template.close_register_message,
                template.classroom_type,
                (
                    case
                        when template.classroom_type = 'online' then pf.platforms_id
                        else template.classroom_plateform
                    end
                ) as platforms_id,
                (
                    case
                        when template.classroom_type = 'online' then pf.platforms_name
                        else template.classroom_plateform
                    end
                ) as platforms_name,
                template.classroom_source,
                template.line_oa,
                line.line_token_id as line_oa_id,
                line.line_token_name as line_oa_name,
                template.auto_approve,
                template.auto_username,
                template.auto_username_type,
                template.auto_username_length,
                template.auto_password,
                template.password_type,
                template.auto_password_type,
                template.auto_password_length,
                template.auto_password_custom,
                template.password_sensitivity_case
            ",
            "classroom_template template",
            "
                left join data_meeting_platforms pf on pf.platforms_id = template.classroom_plateform
                left join line_token line on line.line_token_id = template.line_oa_link
                where template.classroom_id = '{$classroom_id}'
            "
        );
        $classroom = $classrooms[0];
        $staffs = select_data(
            "group_concat(emp_id) as staff_group",
            "classroom_staff",
            "where classroom_id = '{$classroom_id}' and status = 0"
        );
        $staff_groups = '';
        if(isset($staffs)) {
            $staff_groups = $staffs[0]['staff_group'];
        }
        echo json_encode([
            'status' => true,
            'classroom_data' => [
                'classroom_name' => $classroom['classroom_name'],
                'classroom_information' => $classroom['classroom_information'],
                'classroom_poster' => ($classroom['classroom_poster']) ? GetUrl($classroom['classroom_poster']) : '',
                'classroom_start_date' => $classroom['classroom_start_date'],
                'classroom_start_time' => $classroom['classroom_start_time'],
                'classroom_end_date' => $classroom['classroom_end_date'],
                'classroom_end_time' => $classroom['classroom_end_time'],
                'classroom_student' => $classroom['classroom_student'],
                'classroom_allow_register' => $classroom['classroom_allow_register'],
                'classroom_open_register_date' => $classroom['classroom_open_register_date'],
                'classroom_open_register_time' => $classroom['classroom_open_register_time'],
                'classroom_close_register_date' => $classroom['classroom_close_register_date'],
                'classroom_close_register_time' => $classroom['classroom_close_register_time'],
                'close_register_message' => $classroom['close_register_message'],
                'classroom_type' => $classroom['classroom_type'],
                'platforms_id' => $classroom['platforms_id'],
                'platforms_name' => $classroom['platforms_name'],
                'classroom_source' => $classroom['classroom_source'],
                'line_oa' => $classroom['line_oa'],
                'line_oa_id' => $classroom['line_oa_id'],
                'line_oa_name' => $classroom['line_oa_name'],
                'auto_approve' => $classroom['auto_approve'],
                'auto_username' => $classroom['auto_username'],
                'auto_username_type' => $classroom['auto_username_type'],
                'auto_username_length' => $classroom['auto_username_length'],
                'auto_password' => $classroom['auto_password'],
                'auto_password_type' => $classroom['auto_password_type'],
                'auto_password_length' => $classroom['auto_password_length'],
                'auto_password_custom' => $classroom['auto_password_custom'],
                'password_sensitivity_case' => $classroom['password_sensitivity_case'],
                'staff_groups' => $staff_groups,
            ]
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'buildStaff') {
        $classroom_id = $_POST['classroom_id'];
        $emp_group = ($_POST['emp_group']) ? $_POST['emp_group'] : '';
        if($emp_group) {
            $union = "UNION 
                SELECT 
                    emp.emp_id,
                    emp.emp_code,
                    i.emp_pic,
                    i.gender,
                    CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_name,
                    posi.posi_description,
                    dept.dept_description
                FROM 
                    m_employee emp
                LEFT JOIN 
                    m_employee_info i on i.emp_id = emp.emp_id
                LEFT JOIN 
                    m_position posi on posi.posi_id = emp.posi_id
                LEFT JOIN 
                    m_department dept on dept.dept_id = emp.dept_id
                WHERE 
                    emp.emp_id in ($emp_group)";
        }
        $table = "SELECT 
                    emp_id,emp_code,emp_pic,gender,emp_name,posi_description,dept_description 
                FROM 
                (
                    SELECT 
                        staff.emp_id,
                        emp.emp_code,
                        i.emp_pic,
                        i.gender,
                        CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_name,
                        posi.posi_description,
                        dept.dept_description
                    FROM 
                        classroom_staff staff 
                    LEFT JOIN 
                        m_employee emp on emp.emp_id = staff.emp_id
                    LEFT JOIN 
                        m_employee_info i on i.emp_id = emp.emp_id
                    LEFT JOIN 
                        m_position posi on posi.posi_id = emp.posi_id
                    LEFT JOIN 
                        m_department dept on dept.dept_id = emp.dept_id
                    WHERE 
                        staff.classroom_id = '{$classroom_id}' and staff.status = 0 
                    $union
                ) data_table GROUP BY emp_id";
		$primaryKey = 'emp_id';
        $columns = array(
            array('db' => 'emp_id', 'dt' => 'emp_id'),
            array('db' => 'emp_code', 'dt' => 'emp_code'),
            array('db' => 'emp_name', 'dt' => 'emp_name'),
            array('db' => 'posi_description', 'dt' => 'posi_description'),
            array('db' => 'dept_description', 'dt' => 'dept_description'),
            array('db' => 'gender', 'dt' => 'gender'),
			array('db' => 'emp_pic', 'dt' => 'emp_pic','formatter' => function ($d, $row) {
				return GetMemberAvatar($d, $row['gender']);
			}),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_GET['action']) && $_GET['action'] == 'buildDepartment') {
		$keyword = trim($_GET['term']);
		$search = ($keyword) ? " and dept_description like '%{$keyword}%' " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                dept_id as data_code,
                dept_description as data_desc 
            from 
                m_department 
            where 
                comp_id = '{$_SESSION['comp_id']}' and dept_del is null and status = 0 $search
        ) data_table";
        $whereData = (($_GET['page']) ? "LIMIT ".$end.",".$start : "")."";
        $Data = select_data($columnData,$tableData,$whereData);
		$count_data = count($Data);
		$i = 0;
		while($i < $count_data) {
			$data[] = ['id' => $Data[$i]['data_code'],'col' => $Data[$i]['data_desc'],'total_count' => $count_data,'code' => $Data[$i]['data_code'],'desc' => $Data[$i]['data_desc'],];
			++$i;
		}
		if (empty($data)) {
			$data[] = ['id' => '','col' => '', 'total_count' => ''];
		}
        echo json_encode($data);
	}
    if(isset($_GET['action']) && $_GET['action'] == 'buildLineOA') {
		$keyword = trim($_GET['term']);
		$search = ($keyword) ? " and line_token_name like '%{$keyword}%' " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                line_token_id as data_code,
                line_token_name as data_desc 
            from 
                line_token 
            where 
                comp_id = '{$_SESSION['comp_id']}' and status = 0 $search
        ) data_table";
        $whereData = (($_GET['page']) ? "LIMIT ".$end.",".$start : "")."";
        $Data = select_data($columnData,$tableData,$whereData);
		$count_data = count($Data);
		$i = 0;
		while($i < $count_data) {
			$data[] = ['id' => $Data[$i]['data_code'],'col' => $Data[$i]['data_desc'],'total_count' => $count_data,'code' => $Data[$i]['data_code'],'desc' => $Data[$i]['data_desc'],];
			++$i;
		}
		if (empty($data)) {
			$data[] = ['id' => '','col' => '', 'total_count' => ''];
		}
        echo json_encode($data);
	}
    if(isset($_GET['action']) && $_GET['action'] == 'buildPlatform') {
		$keyword = trim($_GET['term']);
		$search = ($keyword) ? " and platforms_name like '%{$keyword}%' " : "";
		$resultCount = 10;
		$end = ($_GET['page'] - 1) * $resultCount;
		$start = $end + $resultCount;
        $columnData = "*";
        $tableData = "(
            select 
                platforms_id as data_code,
                platforms_name as data_desc 
            from 
                data_meeting_platforms 
            where 
                status = 0 $search
        ) data_table";
        $whereData = (($_GET['page']) ? "LIMIT ".$end.",".$start : "")."";
        $Data = select_data($columnData,$tableData,$whereData);
		$count_data = count($Data);
		$i = 0;
		while($i < $count_data) {
			$data[] = ['id' => $Data[$i]['data_code'],'col' => $Data[$i]['data_desc'],'total_count' => $count_data,'code' => $Data[$i]['data_code'],'desc' => $Data[$i]['data_desc'],];
			++$i;
		}
		if (empty($data)) {
			$data[] = ['id' => '','col' => '', 'total_count' => ''];
		}
        echo json_encode($data);
	}
    if($_POST['action'] == 'buildUIGroup') {
        $emp_select = $_POST['emp_select'];
        $dept_search = $_POST['dept_search'];
        $filter = "";
        $filter .= ($emp_select) ? "and emp.emp_id not in ($emp_select)" : "";
        $filter .= "and emp.dept_id = '{$dept_search}'";
        $columnData = "emp.emp_id";
        $tableData = "m_employee emp";
        $whereData = "WHERE emp.comp_id = '{$_SESSION['comp_id']}' and emp.emp_del is null and date(ifnull(emp.emp_resign_date,NOW())) >= date(NOW()) $filter group by emp.emp_id";
        $Data = select_data($columnData,$tableData,$whereData);
        echo json_encode($Data);
    }
    if($_POST['action'] == 'buildTableJoin') {
		$emp_select = ($_POST['emp_select']) ? $_POST['emp_select'] : "'X'";
		$filter = ($emp_select) ? "and emp.emp_id in ($emp_select)" : "";
		$table = "SELECT 
					emp.emp_id,
					emp.emp_code,
					emp_info.emp_pic,
					emp_info.gender,
					CONCAT(IFNULL(emp_info.firstname,emp_info.firstname_th),' ',IFNULL(emp_info.lastname,emp_info.lastname_th)) AS emp_name,
					posi.posi_description,
					dept.dept_description
				FROM 
					m_employee emp
				LEFT JOIN 
					m_employee_info emp_info on emp_info.emp_id = emp.emp_id
				LEFT JOIN 
					m_position posi on posi.posi_id = emp.posi_id
				LEFT JOIN 
					m_department dept on dept.dept_id = emp.dept_id
				WHERE 
                emp.comp_id = '{$_SESSION['comp_id']}' and emp.emp_del is null and date(ifnull(emp.emp_resign_date,NOW())) >= date(NOW()) $filter
                group by 
                    emp.emp_id";
		$primaryKey = 'emp_id';
        $columns = array(
            array('db' => 'emp_id', 'dt' => 'emp_id'),
            array('db' => 'emp_code', 'dt' => 'emp_code'),
            array('db' => 'emp_name', 'dt' => 'emp_name'),
            array('db' => 'posi_description', 'dt' => 'posi_description'),
            array('db' => 'dept_description', 'dt' => 'dept_description'),
            array('db' => 'gender', 'dt' => 'gender'),
			array('db' => 'emp_pic', 'dt' => 'emp_pic','formatter' => function ($d, $row) {
				return GetMemberAvatar($d, $row['gender']);
			}),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
	}
    if($_POST['action'] == 'buildUI') {
        $keyword = htmlspecialchars_decode(str_replace(' ','',$_POST['keyword'])); 
        $emp_select = $_POST['emp_select'];
        $dept_search = $_POST['dept_search'];
        $filter = "";
        $filter .= ($emp_select) ? "and emp.emp_id not in ($emp_select)" : "";
        $filter .= ($dept_search) ? "and emp.dept_id = '{$dept_search}'" : "";
        if($keyword) {
            $filter .= "and (
                LOWER(emp_info.firstname) like LOWER('%{$keyword}%')
                or 
                LOWER(emp_info.firstname_th) like LOWER('%{$keyword}%')
                or 
                LOWER(emp_info.lastname) like LOWER('%{$keyword}%')
                or 
                LOWER(emp_info.lastname_th) like LOWER('%{$keyword}%')
                or 
                LOWER(emp.emp_code) like LOWER('%{$keyword}%')
                or 
                LOWER(posi.posi_description) like LOWER('%{$keyword}%')
                or 
                LOWER(dept.dept_description) like LOWER('%{$keyword}%')
                or 
                LOWER(CONCAT(IFNULL(emp_info.firstname,emp_info.firstname_th),'',IFNULL(emp_info.lastname,emp_info.lastname_th))) like LOWER('%{$keyword}%')
            )";
        }
        $columnData = "emp.emp_id,
                    emp.emp_code,
                    emp_info.emp_pic,
                    emp_info.gender,
                    CONCAT(IFNULL(emp_info.firstname,emp_info.firstname_th),' ',IFNULL(emp_info.lastname,emp_info.lastname_th)) AS emp_name,
                    posi.posi_description,
                    dept.dept_description";
        $tableData = "m_employee emp";
        $whereData = "LEFT JOIN 
                        m_employee_info emp_info on emp_info.emp_id = emp.emp_id
                    LEFT JOIN 
                        m_position posi on posi.posi_id = emp.posi_id
                    LEFT JOIN 
                        m_department dept on dept.dept_id = emp.dept_id
                    WHERE 
                        emp.comp_id = '{$_SESSION['comp_id']}' and emp.emp_del is null and date(ifnull(emp.emp_resign_date,NOW())) >= date(NOW()) $filter  
                    group by 
                        emp.emp_id";
        $Data = select_data($columnData,$tableData,$whereData);
        $count_data = count($Data);
        $i_data = 0;
        while($i_data < $count_data) {
            $Data[$i_data]['emp_pic'] = GetMemberAvatar($Data[$i_data]['emp_pic'], $Data[$i_data]['gender']);
            ++$i_data;
        }
        echo json_encode($Data);
    }
    if(isset($_GET) && $_GET['action'] == 'saveManagement') {
        $classroom_id = $_POST['classroom_id'];
        $classroom_name = initVal($_POST['classroom_name']);
        $classroom_start_date = $_POST['classroom_start_date'];
        $classroom_start_time = $_POST['classroom_start_time'];
        $classroom_end_date = $_POST['classroom_end_date'];
        $classroom_end_time = $_POST['classroom_end_time'];
        $sql_classroom_start = convertDateTime($classroom_start_date, $classroom_start_time);
        $sql_classroom_end = convertDateTime($classroom_end_date, $classroom_end_time);
        $classroom_start = initVal($sql_classroom_start);
        $classroom_end = initVal($sql_classroom_end);
        $classroom_type = initVal($_POST['classroom_type']);
        $classroom_plateform = initVal($_POST['classroom_plateform']);
        $classroom_source = initVal($_POST['classroom_source']);
        $classroom_student = $_POST['classroom_student'];
        $classroom_allow_register = $_POST['classroom_allow_register'];
        $classroom_open_register_date = $_POST['classroom_open_register_date'];
        $classroom_open_register_time = $_POST['classroom_open_register_time'];
        $classroom_close_register_date = $_POST['classroom_close_register_date'];
        $classroom_close_register_time = $_POST['classroom_close_register_time'];
        $classroom_open_register = '';
        $classroom_close_register = '';
        $sql_classroom_open_register = convertDateTime($classroom_open_register_date, $classroom_open_register_time);
        $sql_classroom_close_register = convertDateTime($classroom_close_register_date, $classroom_close_register_time);
        $classroom_open_register = initVal($sql_classroom_open_register);
        $classroom_close_register = initVal($sql_classroom_close_register);
        $close_register_message = initVal($_POST['close_register_message']);
        $line_oa = $_POST['line_oa'];
        $line_oa_link = initVal($_POST['line_oa_link']);
        $auto_approve = $_POST['auto_approve'];
        $auto_username = $_POST['auto_username'];
        $auto_username_type = ($_POST['auto_username_type']) ? "'" . implode(',', $_POST['auto_username_type']) . "'" : "null";
        $auto_username_length = initVal($_POST['auto_username_length']);
        $auto_password = $_POST['auto_password'];
        $password_type = initVal($_POST['password_type']);
        $auto_password_custom = initVal($_POST['auto_password_custom']);
        $auto_password_type = ($_POST['auto_password_type']) ? "'" . implode(',', $_POST['auto_password_type']) . "'" : "null";
        $auto_password_length = initVal($_POST['auto_password_length']);
        $password_sensitivity_case = $_POST['password_sensitivity_case'];
        $classroom_information = initVal($_POST['classroom_information']);
        if($classroom_id) {
            update_data(
                "classroom_template",
                "
                    classroom_name = $classroom_name,
                    classroom_information = $classroom_information,
                    classroom_start = $classroom_start,
                    classroom_end = $classroom_end,
                    classroom_student = $classroom_student,
                    classroom_allow_register = $classroom_allow_register,
                    classroom_open_register = $classroom_open_register,
                    classroom_close_register = $classroom_close_register,
                    close_register_message = $close_register_message,
                    classroom_type = $classroom_type,
                    classroom_plateform = $classroom_plateform,
                    classroom_source = $classroom_source,
                    line_oa = $line_oa,
                    line_oa_link = $line_oa_link,
                    auto_approve = $auto_approve,
                    auto_username = $auto_username,
                    auto_username_type = $auto_username_type,
                    auto_username_length = $auto_username_length,
                    auto_password = $auto_password,
                    password_type = $password_type,
                    auto_password_type = $auto_password_type,
                    auto_password_length = $auto_password_length,
                    auto_password_custom = $auto_password_custom,
                    password_sensitivity_case = $password_sensitivity_case,
                    emp_modify = '{$_SESSION['emp_id']}',
                    date_modify = NOW()
                ",
                "classroom_id = '{$classroom_id}'"
            );
        } else {
            $classroom_id = insert_data(
                "classroom_template",
                "(
                    classroom_name,
                    classroom_information,
                    classroom_start,
                    classroom_end,
                    classroom_student,
                    classroom_allow_register,
                    classroom_open_register,
                    classroom_close_register,
                    close_register_message,
                    classroom_type,
                    classroom_plateform,
                    classroom_source,
                    line_oa,
                    line_oa_link,
                    auto_approve,
                    auto_username,
                    auto_username_type,
                    auto_username_length,
                    auto_password,
                    password_type,
                    auto_password_type,
                    auto_password_length,
                    auto_password_custom,
                    password_sensitivity_case,
                    comp_id,
                    status,
                    emp_create,
                    date_create,
                    emp_modify,
                    date_modify
                )",
                "(
                    $classroom_name,
                    $classroom_information,
                    $classroom_start,
                    $classroom_end,
                    $classroom_student,
                    $classroom_allow_register,
                    $classroom_open_register,
                    $classroom_close_register,
                    $close_register_message,
                    $classroom_type,
                    $classroom_plateform,
                    $classroom_source,
                    $line_oa,
                    $line_oa_link,
                    $auto_approve,
                    $auto_username,
                    $auto_username_type,
                    $auto_username_length,
                    $auto_password,
                    $password_type,
                    $auto_password_type,
                    $auto_password_length,
                    $auto_password_custom,
                    $password_sensitivity_case,
                    '{$_SESSION['comp_id']}',
                    0,
                    '{$_SESSION['emp_id']}',
                    NOW(),
                    '{$_SESSION['emp_id']}',
                    NOW()
                )"
            );
        }
        if(!$classroom_id) {
            echo json_encode([
                'status' => false
            ]);
            exit;
        }
        $emp_group = $_POST['emp_group'];
        update_data(
            "classroom_staff", 
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
            "classroom_id = '{$classroom_id}'"
        );
        if($emp_group) {
            $groups = explode(',' ,$emp_group);
            foreach($groups as $emp_id) {
                $exists = select_data(
                    "staff_id",
                    "classroom_staff",
                    "where emp_id = '{$emp_id}' and classroom_id = '{$classroom_id}'"
                );
                if(count($exists) > 0) {
                    update_data(
                        "classroom_staff", 
                        "status = 0, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()", 
                        "classroom_id = '{$classroom_id}' and emp_id = '{$emp_id}'"
                    );
                } else {
                    insert_data(
                        "classroom_staff",
                        "(
                            classroom_id,
                            emp_id,
                            comp_id,
                            status,
                            emp_create,
                            date_create,
                            emp_modify,
                            date_modify
                        )",
                        "(
                            '{$classroom_id}',
                            '{$emp_id}',
                            '{$_SESSION['comp_id']}',
                            0,
                            '{$_SESSION['emp_id']}',
                            NOW(),
                            '{$_SESSION['emp_id']}',
                            NOW()
                        )"
                    );
                }
            }
        }
        $ex_classroom_poster = initVal($_POST['ex_classroom_poster']);
        if(empty($ex_classroom_poster)) {
            update_data("classroom_template", "classroom_poster = null", "classroom_id = '{$classroom_id}'");
        }
        $classroom_poster_name = $_FILES['classroom_poster']['name'];
        $classroom_poster_tmp = $_FILES['classroom_poster']['tmp_name'];
        $classroom_poster = null;
        $classroom_poster_thumb = null;
        if ($classroom_poster_name && $classroom_poster_tmp) {
            $strname = md5($classroom_id);
            $classroom_poster_dir = 'uploads/classroom/' . $_SESSION['comp_id'] . '/';
            $path_info = pathinfo($classroom_poster_name);
            $classroom_poster_ext = strtolower($path_info['extension']);
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($classroom_poster_ext, $allowed_extensions)) {
                echo json_encode([
                    'status' => false,
                    'message' => "Error: Invalid file type. Only JPG, PNG, GIF allowed."
                ]);
                exit;
            }
            $classroom_poster = $classroom_poster_dir . $strname . '.' . $classroom_poster_ext;
            $classroom_poster_thumb = $classroom_poster_dir . $strname . '_thumbnail.' . $classroom_poster_ext;
            $classroom_poster_save = "{$classroom_poster}";
            if (SaveFile($classroom_poster_tmp, $classroom_poster)) {
                if (!createThumbnail($classroom_poster, $classroom_poster_thumb, 300, 300, 80)) {
                    echo json_encode([
                        'status' => false,
                        'message' => "Warning: Could not create thumbnail"
                    ]);
                    exit;
                }
            } else {
                echo "Error: Could not save original file";
                echo json_encode([
                    'status' => false,
                    'message' => "Error: Could not save original file"
                ]);
                exit;
                $classroom_poster = null;
                $classroom_poster_thumb = null;
                $classroom_poster_save = "null";
            }
        }
        update_data("classroom_template", "classroom_poster = $classroom_poster_save", "classroom_id = '{$classroom_id}'");
        echo json_encode([
            'status' => true,
            'classroom_id' => $classroom_id
        ]);
    }
    function initVal($val) {
        global $mysqli;
        if($val) {
            return "'" . mysqli_real_escape_string($mysqli, $val) . "'";
        } else {
            return "null";
        }
    }
    function convertDateTime($date, $time) {
        if (empty($date) || empty($time)) {
            return null;
        }
        $datetime_string = $date . ' ' . $time;
        $datetime = DateTime::createFromFormat('Y/m/d H:i', $datetime_string);
        return ($datetime !== false) ? $datetime->format('Y-m-d H:i:s') : null;
    }
?>