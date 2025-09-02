<?php
    session_start();
    require_once("../../lib/connect_sqli.php");
    include_once("../../login_history.php");
    global $mysqli;
    if (isset($_POST['save_thumbnail'])) {
        $this_date_time = date("Y-m-d H:i:s");
        $x1 = ($_POST['x1']) ? $_POST['x1'] : 1;
        $y1 = ($_POST['y1']) ? $_POST['y1'] : 1;
        $w = ($_POST['w_head']) ? $_POST['w_head'] : 1;
        $h = ($_POST['h_head']) ? $_POST['h_head'] : 1;
        $emp_pic = explode("?", $_SESSION["emp_pic"]);
        if ($_SESSION["emp_pic"] != '' && $x1 != '') {
            $sql = "update m_employee_info set emp_pic='" . $emp_pic[0] . "?" . $x1 . "&" . $y1 . "&" . $w . "&" . $h . "', last_update='" . $this_date_time . "' where emp_id='" . $_SESSION["emp_id"] . "'  ";
            $result = $mysqli -> query($sql);
            $_SESSION["emp_pic"] = $emp_pic[0] . "?" . $x1 . "&" . $y1 . "&" . $w . "&" . $h;
        } else {
            $_SESSION["emp_pic"] = $emp_pic[0];
        }
    }
    if (isset($_POST['submit_info'])) {
        $sql = "UPDATE m_employee_info SET signature='" . $_POST['signature'] . "', signature_drawing='" . $_POST['signature_drawing'] . "',last_update = NOW() where emp_id='" . $_POST['hid_id2'] . "'";
        $result = $mysqli -> query($sql);
        $hid_id2 = $_POST['hid_id2'];
        $home_location = $_POST['home_location_input'];
        $home_code = "UPDATE m_employee_info SET home_location = '{$home_location}' WHERE emp_id = '{$hid_id2}' AND (home_location is null OR home_location = '')";
        $home_query = $mysqli -> query($home_code);
        if ($result) {
            redirect("m_profile.php");
        }
    }
    if (isset($_POST['upload_avatar'])) {
        $image_name = $_FILES['image_name']['name'];
        $image_tmp = $_FILES['image_name']['tmp_name'];
        if($image_name) {
            $ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $unique_id = substr(base_convert(time(),10,36).md5(microtime()),0,16).'.'.$ext;
            $dir = 'uploads/employee/'.$_SESSION['comp_id'];
            $path_save = $dir.$unique_id;
            move_uploaded_file($image_tmp,$path_save);
            $sql = "update m_employee_info set emp_pic = '{$path_save}' where emp_id = '{$_SESSION['emp_id']}'";
            $result = $mysqli -> query($sql);
            $_SESSION["emp_pic"] = $path_save;
        }
        echo "<script language=\"javascript\">alert('Upload success.');</script>";
        redirect("../../m_profile.php");
    }
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
    setBucket($fsData);
    $sql_check_goal_menu = $mysqli -> query("SELECT acc.status AS status_goal FROM ac_company acc LEFT JOIN ac_menu acm ON acc.acm_id = acm.acm_id AND acm.status = 'Y' WHERE acc.comp_id = '{$_SESSION['comp_id']}' AND acm.path = 'performance/goal.php' ");
    $check_goal_menu = mysqli_fetch_assoc($sql_check_goal_menu);
    $sql_all = $mysqli -> query("select * from m_employee left join employee_payroll on m_employee.emp_id = employee_payroll.emp_id inner join m_employee_info on m_employee.emp_id=m_employee_info.emp_id where m_employee.emp_id = '{$_SESSION["emp_id"]}' ");
    $row_all = mysqli_fetch_array($sql_all);
    $columnDNA = "dna_name,dna_logo,dna_color";
    $tableDNA = "m_dna";
    $whereDNA = "where dna_id = '{$row_all["dna"]}'";
    $DNA = select_data($columnDNA,$tableDNA,$whereDNA);
    $dna_name = $DNA[0]['dna_name'];
    $dna_logo = GetUrl($DNA[0]['dna_logo']);
    $dna_color = ($DNA[0]['dna_color']) ? $DNA[0]['dna_color'] : '#FFFFFF';
    function _avatar($picname){
        if ($picname == "images/default.png") {
            $img = "<img width=\"150\" title=\"" . $_SESSION["user"] . "\"  src=\"";
            $img .= "images/default.png\"/>";
        } else {
            $img = "<img width=\"150\" id=\"avatar\" title=\"" . $_SESSION["user"] . "\"  src=\"";
            $img .= $picname . "\"/>";
        }
        return $img;
    }
    $this_day = date("Y-m-d");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Setting • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
    
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Kanit', sans-serif;
            color: #333;
        }
        
        /* สไตล์สำหรับแถบเมนูข้างบน */
        .top-nav-container {
            width: 100%;
            display: flex;
            justify-content: center;
            background: #ffc27c;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px; /* เพิ่มระยะห่างด้านล่าง */
        }
        
        .top-nav {
            list-style: none;
            display: flex;
            gap: 25px;
            margin: 0;
            padding: 0;
        }
        
        .top-nav li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 55px;
            height: 55px;
            border-radius: 15px;
            background: #ffe5cc;
            color: #ff6600;
            font-size: 22px;
            transition: all 0.3s ease;
        }
        
        .top-nav li a:hover, .top-nav li a.active {
            background: #ff6600;
            color: #fff;
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(255,102,0,0.3);
        }

        /* สไตล์สำหรับหน้า Settings ใหม่ */
        .settings-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 20px;
        }

        .settings-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 30px;
        }
        
        .settings-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .settings-header .profile-avatar-placeholder {
            width: 120px;
            height: 120px;
            background: #ff6600;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(255,102,0,0.4);
        }

        .settings-header .profile-avatar-placeholder .fa-user {
            color: #fff;
            font-size: 60px;
        }

        .settings-header .profile-name {
            font-size: 1.8em;
            font-weight: 700;
            color: #2c3e50;
        }

        .settings-list .setting-item {
            display: flex;
            align-items: center;
            padding: 20px 25px;
            background-color: #f7f9fc;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .settings-list .setting-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .setting-item .setting-icon {
            width: 45px;
            height: 45px;
            min-width: 45px; /* เพื่อให้ไอคอนไม่หด */
            background: #ff6600;
            color: #fff;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            margin-right: 20px;
        }

        .setting-item .setting-text {
            flex-grow: 1;
        }
        
        .setting-item .setting-text .title {
            font-size: 1.1em;
            font-weight: 600;
            color: #555;
            margin: 0;
        }

        .setting-item .setting-text .description {
            font-size: 0.9em;
            color: #888;
        }

        .setting-item .fa-chevron-right {
            color: #bbb;
            font-size: 1.1em;
            transition: transform 0.2s ease;
        }
        
        .settings-list .setting-item:hover .fa-chevron-right {
            transform: translateX(5px);
            color: #ff6600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-container {
                padding: 10px;
            }
            .settings-card {
                padding: 20px;
            }
        }
        .profile-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    /* margin-bottom: 30px; */
    padding: 40px;
    text-align: center;
    position: relative;
    top: -50px;
}
.profile-avatar-square {
    width: 150px;
    height: 150px;
    border-radius: 50%; /* Square with rounded corners */
    border: 5px solid #ff8c00; /* Orange border */
    overflow: hidden;
    margin: 0 auto 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.profile-avatar-square img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-name {
    font-size: 2.2em;
    font-weight: 800;
    color: #2c3e50; /* Dark blue-gray */
    margin-bottom: 8px;
}
.profile-bio {
    font-size: 1.1em;
    color: #7f8c8d; /* Grayish blue */
    margin-bottom: 25px;
}
    </style>
</head>
<body>
     <?php
    require_once ("component/header.php")
    ?>
    
    <!-- <div class="top-nav-container">
        <ul class="top-nav">
            <li><a href="#"><i class="fa fa-user"></i></a></li>
            <li><a href="#" class="active"><i class="fas fa-cog"></i></a></li>
            <li><a href="#"><i class="fa fa-calendar-alt"></i></a></li>
            <li><a href="#"><i class="fa fa-chalkboard"></i></a></li>
            <li><a href="#"><i class="fa fa-image"></i></a></li>
            <li><a href="#"><i class="fa fa-file-alt"></i></a></li>
            <li><a href="#"><i class="fa fa-heart" style="color:red;"></i></a></li>
        </ul>
    </div> -->
    
    <div class="settings-container">
          
                 <a href="profile" style=" left: 0px; z-index: 1000;">
      <button class="btn btn-warning" style="border-radius: 12px;width: 45px;height: 35px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <i class="fas fa-arrow-left" style="color: #fff; font-size: 1.2em;"></i>
        </button>
    </a>
        <div class="settings-card" style="margin-top: 10px;">
            
            
            <div class="settings-header">
              
                  <div class="profile-card" style="margin-top: 50px;">
                    
                    
                    <div class="profile-avatar-square">
                        <img src="<?php echo $_SESSION["emp_pic"]; ?>" onerror="this.src='../../../images/default.png'" alt="Profile Picture">
                    </div>
                    <h2 class="profile-name">
                        <?= $row_all["firstname"] . " " . $row_all["lastname"]; ?>
                    </h2>
                    <p class="profile-bio">
                        <?= !empty($row_all["bio"]) ? $row_all["bio"] : "ยังไม่ได้เขียน Bio"; ?>
                    </p>
                     <div class="profile-course-container">
                <p class="profile-course" style="margin: 0px;">
                    <i class="fas fa-graduation-cap"></i>
                    หลักสูตร: <span><?= !empty($row_all["course"]) ? $row_all["course"] : "ยังไม่ได้ระบุ"; ?></span>
                </p>
            </div>
                    </div>
            
            <div class="settings-list">
                <a href="edit_profile" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">ตั้งค่าโปรไฟล์</h4>
                        <p class="description">อัปเดตข้อมูลส่วนตัวและรูปภาพ</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="privacy_settings" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">ตั้งค่าความเป็นส่วนตัว</h4>
                        <p class="description">จัดการสิทธิ์การเข้าถึงข้อมูลของคุณ</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>

                <a href="#" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">ตั้งค่าการแจ้งเตือน</h4>
                        <p class="description">เลือกรับการแจ้งเตือนที่คุณสนใจ</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>

                <a href="#" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">ตั้งค่าการใช้งานโดยรวม</h4>
                        <p class="description">ปรับแต่งการทำงานของแอปพลิเคชัน</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>

                <a href="#" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">ภาษา</h4>
                        <p class="description">เลือกภาษาที่ใช้แสดงผล</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="#" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">พิมพ์หนังสือรุ่น</h4>
                        <p class="description">จัดทำหนังสือรุ่นเพื่อเก็บเป็นที่ระลึก</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>

                <a href="#" class="setting-item">
                    <div class="setting-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <div class="setting-text">
                        <h4 class="title">ออกจากระบบ</h4>
                        <p class="description">ลงชื่อออกจากบัญชีผู้ใช้งาน</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>

            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // ส่วนนี้จะจัดการ active class เมื่อคลิกที่เมนู
            $('.top-nav a').on('click', function (e) {
                $('.top-nav a').removeClass('active');
                $(this).addClass('active');
            });
            
            // กำหนดให้เมนูแรกเป็น active ตั้งแต่แรกที่โหลดหน้า
            $('.top-nav a[href="#B"]').addClass('active');

            // เพิ่ม SweetAlert เมื่อคลิกเมนูออกจากระบบ
            $('.setting-item .fa-sign-out-alt').closest('.setting-item').on('click', function(e) {
                e.preventDefault();
                swal({
                    title: "ออกจากระบบ",
                    text: "คุณแน่ใจหรือไม่ว่าต้องการออกจากระบบ?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff6600",
                    confirmButtonText: "ใช่, ออกจากระบบ!",
                    cancelButtonText: "ยกเลิก",
                    closeOnConfirm: false
                }, function() {
                    // ทำงานเมื่อผู้ใช้ยืนยันการออกจากระบบ
                    swal("ออกจากระบบแล้ว!", "คุณได้ออกจากระบบเรียบร้อยแล้ว", "success");
                    // สามารถใส่โค้ดสำหรับนำทางไปยังหน้า login หรือทำการ logout ที่นี่
                    // เช่น window.location.href = '/logout';
                });
            });
        });
    </script>
    <?php
    require_once ("component/footer.php")
    ?>
</body>
</html>