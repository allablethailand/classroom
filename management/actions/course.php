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
    if(isset($_POST) && $_POST['action'] == 'buildCourse') {
        $classroom_id = $_POST['classroom_id'];
        $table = "SELECT 
            course.course_id,
            (
                CASE
                    WHEN course.course_type = 'course' then c.trn_subject
                    ELSE l.learning_map_name
                END
            ) as course_name,
            course.course_type,
            (
                CASE
                    WHEN course.course_type = 'course' then c.picture_title
                    ELSE l.learning_map_pic
                END
            ) as course_cover,
            date_format(course.date_create, '%Y/%m/%d %H:%i:%s') as date_create,
            CONCAT(IFNULL(i.firstname,i.firstname_th),' ',IFNULL(i.lastname,i.lastname_th)) AS emp_create
        FROM 
            classroom_course course
        LEFT JOIN 
            ot_training_list c on c.trn_id = course.course_ref_id and course.course_type = 'course'
        LEFT JOIN 
            ot_learning_map_list l on l.learning_map_id = course.course_ref_id and course.course_type = 'learning_map'
        LEFT JOIN 
            m_employee_info i on i.emp_id = course.emp_create
        WHERE 
            course.classroom_id = '{$classroom_id}' and course.status = 0";
        $primaryKey = 'course_id';
        $columns = array(
            array('db' => 'course_id', 'dt' => 'course_id'),
            array('db' => 'course_name', 'dt' => 'course_name'),
            array('db' => 'course_type', 'dt' => 'course_type'),
            array('db' => 'course_cover', 'dt' => 'course_cover','formatter' => function ($d, $row) {
				if (!empty($d)) {
                    $filePath = realpath(__DIR__ . '/../../../') . '/' . $d;
                    if ($filePath && file_exists($filePath)) {
                        return '/' . $d;
                    } else {
                        return GetUrl($d);
                    }
                } else {
                    return '/images/training.jpg';
                }
			}),
            array('db' => 'date_create', 'dt' => 'date_create'),
            array('db' => 'emp_create', 'dt' => 'emp_create'),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST) && $_POST['action'] == 'buildCourseMaster') {
        $classroom_id = $_POST['classroom_id'];
        $table = "SELECT 
            t.course_id,
            t.course_type,
            t.course_name,
            t.course_cover
        FROM (
            SELECT 
                c.trn_id AS course_id,
                'course' AS course_type,
                c.trn_subject AS course_name,
                c.picture_title AS course_cover
            FROM ot_training_list AS c
            WHERE 
                c.status = 0 AND c.comp_id = '{$_SESSION['comp_id']}' AND NOT EXISTS (SELECT 1 FROM classroom_course cc WHERE cc.course_ref_id = c.trn_id AND cc.course_type = 'course' AND cc.classroom_id = '{$classroom_id}' AND cc.status = 0)
            UNION ALL
            SELECT 
                l.learning_map_id AS course_id,
                'learning_map' AS course_type,
                l.learning_map_name AS course_name,
                l.learning_map_pic AS course_cover
            FROM ot_learning_map_list AS l
            WHERE 
                l.status = 0 AND l.comp_id = '{$_SESSION['comp_id']}' AND NOT EXISTS (SELECT 1 FROM classroom_course cc WHERE cc.course_ref_id = l.learning_map_id AND cc.course_type = 'learning_map' AND cc.classroom_id = '{$classroom_id}' AND cc.status = 0)
        ) AS t";
        $primaryKey = 'course_id';
        $columns = array(
            array('db' => 'course_id', 'dt' => 'course_id'),
            array('db' => 'course_type', 'dt' => 'course_type'),
            array('db' => 'course_name', 'dt' => 'course_name'),
            array('db' => 'course_cover', 'dt' => 'course_cover','formatter' => function ($d, $row) {
				if (!empty($d)) {
                    $filePath = realpath(__DIR__ . '/../../../') . '/' . $d;
                    if ($filePath && file_exists($filePath)) {
                        return '/' . $d;
                    } else {
                        return GetUrl($d);
                    }
                } else {
                    return '/images/training.jpg';
                }
			}),
		);
		$sql_details = array('user' => $db_username,'pass' => $db_pass_word,'db'   => $db_name,'host' => $db_host);
		require($base_include.'/lib/ssp-subquery.class.php');
		echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns));
		exit();
    }
    if(isset($_POST['action']) && $_POST['action'] == 'addToClassroom') {
        $classroom_id = $_POST['classroom_id'];
        $course_id    = $_POST['course_id'];
        $course_type  = $_POST['course_type'];
        $where = "classroom_id = '{$classroom_id}' AND course_ref_id = '{$course_id}' AND course_type = '{$course_type}'";
        $exists = select_data("course_id", "classroom_course", "WHERE {$where}");
        if(!empty($exists)) {
            update_data(
                "classroom_course",
                "status = 0, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
                $where
            );
        } else {
            $columns = "(classroom_id, course_type, course_ref_id, comp_id, status, emp_create, date_create, emp_modify, date_modify)";
            $values  = "('{$classroom_id}', '{$course_type}', '{$course_id}', '{$_SESSION['comp_id']}', 0, '{$_SESSION['emp_id']}', NOW(), '{$_SESSION['emp_id']}', NOW())";
            insert_data("classroom_course", $columns, $values);
        }
        echo json_encode([
            'status' => true
        ]);
    }
    if(isset($_POST) && $_POST['action'] == 'delCourse') {
        $course_id = $_POST['course_id'];
        update_data(
            "classroom_course",
            "status = 1, emp_modify = '{$_SESSION['emp_id']}', date_modify = NOW()",
            "course_id = '{$course_id}'"
        );
        echo json_encode([
            'status' => true
        ]);
    }
?>