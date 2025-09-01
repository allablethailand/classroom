<?php
    session_start();
    require_once("lib/connect_sqli.php");
    include_once("login_history.php");
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
        redirect("m_profile.php");
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
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/classroom/study/css/setting.css?v=<?php echo time(); ?>">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/dist/js/jquery.dataTables.min.js"></script>
<script src="/dist/js/dataTables.bootstrap.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript" ></script>
<script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
<script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
<script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
<script src="/classroom/study/js/setting.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>
<body>
<div class="container-fluid">
</div>
</body>
</html>







<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="dist/editor/css/froala_editor.min.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="dist/editor/css/froala_style.min.css">
<link rel="stylesheet" href="dist/css/select2.min.css" />
<link rel="stylesheet" href="dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="dist/css/sweetalert.css">
<link rel="stylesheet" href="dist/ui/jquery-ui.min.css" type="text/css" />
<link rel='stylesheet prefetch' href='dist/css/jquery.orgchart.min.css'>
<link rel="stylesheet" type="text/css" href="dist/daterangepicker/v2/daterangepicker.css">
<link rel="stylesheet" href="css/profile.css?v=<?php echo time(); ?>">
<script src="dist/fontawesome-5.11.2/js/all.min.js"></script>
<script src="dist/fontawesome-5.11.2/js/v4-shims.min.js"></script>
<script src="dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>"></script>
<script src="bootstrap/3.3.6/js/jquery-2.2.3.min.js"></script>
<script src="bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="dist/js/jquery.dataTables.min.js"></script>
<script src="dist/js/dataTables.bootstrap.min.js"></script>
<script src="dist/lodash/lodash.js"></script>
<script src="dist/moment/moment.min.js"></script>
<script src="dist/editor/js/froala_editor.min.js"></script>
<script src="dist/editor/js/froala_editor.min.js"></script>
<script src="dist/js/jquery.redirect.js"></script>
<script src="dist/js/select2-build.min.js?v=<?php echo time(); ?>" ></script>
<script src="dist/tippy/js/popper.min.js"></script>
<script src="dist/tippy/js/tipsy.min.js"></script>
<script src="dist/js/sweetalert.min.js"></script>
<script src="dist/js/moment-with-locales.js"></script>
<script src="dist/js/jSignature/jSignature.min.js"></script>
<script src="dist/ui/jquery-ui.min.js" type="text/javascript"></script>
<script type="text/javascript" src="dist/daterangepicker/v2/daterangepicker.js"></script>
<script type="text/javascript" src="dist/js/jquery.mask.min.js"></script>
<script type="text/javascript" src="dist/inputmask/dist/jquery.inputmask.bundle.js"></script>
<script src='dist/js/jquery.orgchart.min.js'></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".avatar.avatar-profile").css("border", "5px solid <?php echo $dna_color; ?>");
        $('input[type=file]').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var fileTypes = ['jpg', 'jpeg', 'png', 'gif'];
                var extension = input.files[0].name.split('.').pop().toLowerCase(),
                    isSuccess = fileTypes.indexOf(extension) > -1;
                if (isSuccess) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        console.log(e.target.result);
                        var img = e.target.result;
                        var data = e.target.result.replace("data:", "");
                        var w_user = '300px';
                        var h_user = '100px';
                        $("#docsign").empty();
                        $("#docsign").append('<i class="fa fa-undo reset_signature_img" onclick="reset_signature_img(\'docsign\');"></i>');
                        $("#docsign").append('<img name="imgsign" id="imgsign" src="' + img + '" width="' + w_user + '" height="' + h_user + '" />');
                        $("#docsign").removeClass('signature');
                        $("#docsign").addClass('signature_img');
                        $('#signature_drawing').val(data);
                    };
                    reader.readAsDataURL(input.files[0]);
                } else {
                    swal({
                        type: 'warning',
                        title: window.lang.translate('Please Use only File such as .jpeg, .jpg, .gif, .png'),
                        showConfirmButton: true,
                        timer: 3500
                    });
                }
            }
        });
        $('#elementsToOperateOn input, #elementsToOperateOn textarea, #elementsToOperateOn select, #elementsToOperateOn checkbox').attr('disabled', true);
        $('#elementsToOperateOn .fa-remove,#elementsToOperateOn .btn-warning').hide();
        var get_datascource = load_datascource(<?php echo $_SESSION['emp_id']; ?>);
        var ajaxURLs = {
            'children': function(nodeData) {
                return 'orgchart/children.php?id=' + nodeData.id;
            }
        };
        $('#chart-orgami').orgchart({
            'data': get_datascource,
            'ajaxURL': ajaxURLs,
            'nodeContent': 'name',
            'parentNodeSymbol': '',
            'pan': true,
            'zoom': true,
            'createNode': function($node, data) {
                $node.children('.title').html('<img src="' + ((data.pic)?'../'+data.pic:'../'+'images/default.png') + '" onerror="this.src=\'../images/default.png\'" class="bxtCXs dqIZME">');
                var nickname = (data.nickname) ? ' ('+data.nickname+')' : '';
                $node.children('.content').append('<div class="kydEot"><b>'+data.name+' '+data.lastname+nickname+'</b></div>');
                if(data.position){ 
                    $node.children('.content').append('<div class="ebDfCA">'+data.position+'</div>'); 
                }
                if(data.dept){ 
                    $node.children('.content').append('<div class="ebDfCA">'+data.dept+'</div>'); 
                }
                if(data.division){ 
                    $node.children('.content').append('<div class="ebDfCA">'+data.division+'</div>'); 
                }
            }
        });
        get_select();
        $('table.display').DataTable({
            "lengthMenu": [25, 50, 100],
            "language": {
                "decimal": "",
                "emptyTable": "<span lang='en'>No data available in table</span>",
                "info": "<span lang='en'>Showing</span> _START_ <span lang='en'>to</span> _END_ <span lang='en'>of</span> _TOTAL_ <span lang='en'>entries</span>",
                "infoEmpty": "<span lang='en'><span lang='en'>Showing</span> 0 <span lang='en'>to</span> 0 <span lang='en'>of</span> 0 <span lang='en'>entries</span>",
                "infoFiltered": "(<span lang='en'>filtered from</span> _MAX_ <span lang='en'>total entries</span>)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "<span lang='en'>Show</span> _MENU_ <span lang='en'>entries</span>",
                "loadingRecords": "<span lang='en'>Loading...</span>",
                "processing": "<span lang='en'>Processing...</span>",
                "search": "<span lang='en'>Search:</span>",
                "zeroRecords": "<span lang='en'>No matching records found</span>",
                "paginate": {
                    "first": "<span lang='en'>First</span>",
                    "last": "<span lang='en'>Last</span>",
                    "next": "<span lang='en'>Next</span>",
                    "previous": "<span lang='en'>Previous</span>"
                },
                "aria": {
                    "sortAscending": ": <span lang='en'>activate to sort column ascending</span>",
                    "sortDescending": ": <span lang='en'>activate to sort column descending</span>"
                }
            },
        });
    });
    $('body').bind('DOMNodeInserted', function(e) {
        if (e.target.tagName == 'select') {
            bind_select(this);
        } else {
            $(e.target).find('select').each(function() {
                bind_select(this);
            });
        }
    });
    function load_datascource(id) {
        var datascource_json;
        $.ajax({
            url: 'orgchart/datasource.php',
            data: {
                id: id
            },
            type: 'post',
            dataType: 'JSON',
            async: false,
            success: function(result) {
                datascource_json = result;
            }
        });
        return datascource_json;
    }
</script>
<script>
    $(document).ready(function(){
        // ส่วนนี้จะจัดการ active class เมื่อคลิกที่เมนู
        $('.top-nav a').on('click', function (e) {
            $('.top-nav a').removeClass('active');
            $(this).addClass('active');
        });
        
        // กำหนดให้เมนูแรกเป็น active ตั้งแต่แรกที่โหลดหน้า
        $('.top-nav a[href="#A"]').addClass('active');
    });
    $(document).ready(function() {
        // ดักจับการคลิกที่ปุ่มบันทึก
        $("#saveBtn").on("click", function() {
            // แสดง SweetAlert
            swal({
                title: "บันทึกสำเร็จ",
                text: "ข้อมูลโปรไฟล์ของคุณถูกบันทึกเรียบร้อยแล้ว",
                type: "success",
                showCancelButton: false,
                confirmButtonColor: "#ff6600",
                confirmButtonText: "ตกลง",
                closeOnConfirm: false
            }, function() {
                // เมื่อผู้ใช้กด "ตกลง" ให้ทำการรีเฟรชหน้าเว็บ
                location.reload();
            });
        });
    });
</script>
<style>
    
/* --- New Style for a modern, flat UI --- */
body {
    background-color: #f0f2f5; /* Light gray background */
    font-family: 'Helvetica Neue', Arial, sans-serif;
    color: #333;
}
.top-nav-container {
    width: 100%;
    display: flex;
    justify-content: center; /* ให้อยู่กลาง */
    background: #ffc27c;;
    padding: 15px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-top: 35px;
    /* border-radius: 0 0 20px 20px; มุมโค้งล่าง */
}

.top-nav {
    list-style: none;
    display: flex;
    gap: 25px; /* ระยะห่าง icon */
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
    background: #ffe5cc; /* พื้นหลังอ่อนโทนส้ม */
    color: #ff6600; /* ไอคอนส้ม */
    font-size: 22px;
    transition: all 0.3s ease;
}

.top-nav li a:hover {
    background: #ff6600;
    color: #fff;
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(255,102,0,0.3);
}

.top-nav li a.active, .top-nav li a.active:hover {
    background: #ff6600; /* พื้นหลังสีส้มเข้ม */
    color: #fff; /* ตัวอักษรและไอคอนสีขาว */
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(255,102,0,0.3);
}
/* ปรับขนาด icon ใน contact-grid */
.contact-grid {
    display: flex;
    flex-wrap: wrap; /* ให้บรรทัดขึ้นใหม่ได้ */
    justify-content: center;
    gap: 20px;
}
.contact-item {
    text-align: center;
    flex-basis: 100px; /* กำหนดขนาดพื้นฐานให้แต่ละไอเทม */
    flex-grow: 1; /* ให้แต่ละไอเทมขยายได้ */
}
.contact-item a {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #7f8c8d;
    font-size: 1.1em; /* เพิ่มขนาดฟอนต์ข้อความ */
    transition: transform 0.2s ease;
}
.contact-item a span {
    font-size: 1.1em; /* เพิ่มขนาดฟอนต์ข้อความ */
}
.contact-icon-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 8px;
    font-size: 32px; /* ขนาดไอคอนในวงกลมใหญ่ขึ้น */
    color: #fff;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: background-color 0.3s ease; /* เพิ่ม transition ให้การเปลี่ยนสีดูนุ่มนวลขึ้น */
}
@media (max-width: 768px) {
    .top-nav {
        gap: 10px; /* ลดช่องว่างระหว่างเมนูบนมือถือ */
        justify-content: space-around; /* ให้กระจายตัวเท่าๆ กัน */
    }
    .top-nav li a {
        width: 50px;
        height: 50px;
    }
    .top-nav li a i {
        font-size: 20px; /* ปรับขนาดไอคอนให้เล็กลงเล็กน้อย */
    }
    .contact-grid {
        grid-template-columns: repeat(3, 1fr); /* แสดง 3 คอลัมน์บนมือถือ */
    }
    .contact-icon-circle {
        width: 55px; /* ปรับขนาดเล็กน้อยสำหรับมือถือ */
        height: 55px;
        /* font-size: 50px; ปรับขนาดไอคอนให้เหมาะสม */
    }
    .contact-item a span {
        font-size: 1em; /* ปรับขนาดฟอนต์ให้เหมาะสมกับมือถือ */
    }
}
/* The rest of your styles from the original code */
.main-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 20px;
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
.divider {
    height: 2px;
    width: 80px;
    background-color: #ff8c00;
    margin: 20px auto;
}
.contact-section-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 30px;
}
.section-header-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 25px;
}
.section-header-icon i {
    font-size: 2em;
    color: #ff6600;
    margin-right: 15px;
}
.section-title {
    /* font-size: 1.8em; */
    font-weight: 700;
    color: #333;
    margin: 0;
}
.contact-icon-circle.phone { background-color: #2ecc71; } /* สีเดิม */
.contact-icon-circle.mail { background-color: #D44638; } /* Gmail/Email Red */
.contact-icon-circle.line { background-color: #00B900; } /* Line Green */
.contact-icon-circle.ig { background-color: #e4405f; } /* Instagram Purple-Red */
.contact-icon-circle.fb { background-color: #3b5998; } /* Facebook Blue */
.info-grid-section {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 30px;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}
.info-item-box {
    background-color: #f7f9fc;
    padding: 25px;
    border-radius: 15px;
    border: 1px solid #eee;
    display: flex;
    align-items: center;
    transition: transform 0.2s ease;
}
.info-item-box:hover {
    transform: translateY(-5px);
}
.info-item-box i {
    font-size: 22px;
    color: #ff8c00;
    margin-right: 15px;
}
.info-text strong {
    display: block;
    font-size: 1.1em;
    font-weight: 700;
    color: #555;
    margin-bottom: 4px;
}
.info-text span {
    font-size: 1em;
    color: #888;
}
/* New CSS for the buttons */
.btn-save-changes {
    padding: 15px 40px;
    background-color: #ff6600;
    color: #fff;
    border: none;
    border-radius: 50px;
    font-weight: bold;
    font-size: 1.2em;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(255,102,0,0.4);
    transition: all 0.3s ease;
    display: block;
    margin: 40px auto;
}
.btn-save-changes:hover {
    background-color: #e55c00;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255,102,0,0.5);
}
.profile-course-container {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #f0f7ff;
    border-radius: 10px;
    display: inline-block;
}
.preview-header-bar {
    width: 100%;
    padding: 15px 20px;
    background-color: #bcbcbcb8; /* สีพื้นหลังแถบ */
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
	border-radius: 0 0 20px 20px;
}

.preview-header-bar a {
    color: #555;
    font-size: 20px;
}

/* New CSS for Edit Profile Page */
.edit-profile-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    padding: 40px;
    position: relative;
    top: -50px;
    margin-top:100px;
}
.form-group {
    margin-bottom: 25px;
}
.form-group label {
    font-weight: bold;
    color: #555;
    margin-bottom: 8px;
}
.form-control-edit {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s ease;
}
.form-control-edit:focus {
    border-color: #ff8c00;
    box-shadow: 0 0 5px rgba(255,140,0,0.3);
}
.input-group {
    display: flex;
    align-items: center;
}
.input-group .form-control-edit {
    flex-grow: 1;
}
.input-group-btn {
    margin-left: 10px;
}
.btn-upload-pic {
    background-color: #2ecc71;
    color: #fff;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.btn-upload-pic:hover {
    background-color: #27ae60;
}
.profile-img-preview {
    width: 150px;
    height: 150px;
    border-radius: 25px;
    object-fit: cover;
    display: block;
    margin: 0 auto 20px;
}
.preview-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}
/* Styling for the eye icon in the title */
.preview-title .fa-eye {
    margin-left: 8px; /* เพิ่มระยะห่างระหว่างข้อความกับไอคอน */
    /*color: #ff6600; /* สีไอคอนให้เข้ากับโทนส้ม */
    font-size: 1.2em; /* เพิ่มขนาดเล็กน้อยให้ดูเด่น */
}
.page-container {
    padding-top: 100px;
}
/* เพิ่ม Media Query สำหรับอุปกรณ์มือถือโดยเฉพาะ */
@media (max-width: 379px) {
    .top-nav-container {
        padding-top: 60px;
    }

}
@media (max-width: 420px) {

    .top-nav li a {
        width: 40px;
        height: 40px;
    }
}
</style>
<title>Profile • ORIGAMI SYSTEM</title>
</head>
<body>
    <?php require_once("include_header.php"); ?>
    
    	<div class="top-nav-container">

	<ul class="top-nav">
		<li>
			<a href="#A" onclick="hideborder();" data-toggle="tab">
				<i class="fa fa-user"></i>
			</a>
		</li>
		<li>
			<a href="#B" onclick="hideborder(); buildPassword();" data-toggle="tab">
				<i class="fas fa-cog"></i>
			</a>
		</li>
		<li>
    <a href="#C" onclick="hideborder();" data-toggle="tab">
        <i class="fa fa-calendar-alt"></i>
    </a>
</li>
<li>
    <a href="#G" data-toggle="tab" onclick="hideborder();">
        <i class="fa fa-chalkboard"></i>
    </a>
</li>
<li>
    <a href="#E" onclick="hideborder();" data-toggle="tab">
        <i class="fa fa-image"></i>
    </a>
</li>
<li>
    <a href="#F" data-toggle="tab" onclick="hideborder();">
        <i class="fa fa-file-alt"></i>
    </a>
</li>
		<li>
			<a href="#I" onclick="hideborder();" data-toggle="tab">
				<i class="fa fa-heart" style="color:red;"></i>
			</a>
		</li>
	</ul>
</div>
    
    <div class="main-container">
        <div class="tab-content">
            <div class="tab-pane fade in active" id="A">
               <div class="preview-header-bar">
    <a href="#" ></i></a>
    <h1 class="preview-title">ตัวอย่างหน้าโปรไฟล์ <i class="fas fa-eye"></i></h1>
    <a href="#" ></i></a>
</div>
                <div class="profile-card" style="margin-top: 100px;">
                    <div class="profile-avatar-square">
                        <img src="<?php echo $_SESSION["emp_pic"]; ?>" onerror="this.src='../images/default.png'" alt="Profile Picture">
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
                <div class="contact-section-card">
                    <div class="section-header-icon">
                        <i class="fas fa-address-book" style="font-size: 25px; "></i>
                        <h3 style="padding-left:10px;" class="section-title">ช่องทางการติดต่อ</h3>
                    </div>
                    <div class="contact-grid">
                        <div class="contact-item">
                            <a href="tel:<?= $row_all['mobile']; ?>">
                                <div class="contact-icon-circle phone"><i class="fas fa-phone" ></i></div>
                                <span>โทรศัพท์</span>
                            </a>
                        </div>
                        <div class="contact-item">
                            <a href="mailto:<?= $row_all['email']; ?>">
                                <div class="contact-icon-circle mail"><i class="fas fa-envelope"></i></div>
                                <span>อีเมล</span>
                            </a>
                        </div>
                        <div class="contact-item">
                            <a href="#">
                                <div class="contact-icon-circle line"><i class="fab fa-line"></i></div>
                                <span>Line</span>
                            </a>
                        </div>
                        <div class="contact-item">
                            <a href="#">
                                <div class="contact-icon-circle ig"><i class="fab fa-instagram"></i></div>
                                <span>Instagram</span>
                            </a>
                        </div>
                        <div class="contact-item">
                            <a href="#">
                                <div class="contact-icon-circle fb"><i class="fab fa-facebook-f"></i></div>
                                <span>Facebook</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="info-grid-section">
                    <div class="section-header-icon">
                        <i class="fas fa-user-circle" style="font-size: 25px; "></i>
                        <h3 class="section-title" style="padding-left:10px;">ข้อมูลส่วนตัว</h3>
                    </div>
                    <div class="info-grid">
                        <div class="info-item-box">
                            <i class="fas fa-birthday-cake" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">วันเกิด</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["birthday"]) ? date("j F Y", strtotime($row_all["birthday"])) : "-"; ?></span>
                            </div>
                        </div>
                        <div class="info-item-box">
                            <i class="fas fa-church" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">ศาสนา</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["religion"]) ? $row_all["religion"] : "-"; ?></span>
                            </div>
                        </div>
                        <div class="info-item-box">
                            <i class="fas fa-tint" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">กรุ๊ปเลือด</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["blood_type"]) ? $row_all["blood_type"] : "-"; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="info-grid-section">
                    <div class="section-header-icon" style="font-size: 25px; ">
                        <i class="fas fa-heartbeat"></i>
                        <h3 class="section-title" style="padding-left:10px;">ไลฟ์สไตล์</h3>
                    </div>
                    <div class="info-grid">
                        <div class="info-item-box">
                            <i class="fas fa-star" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">งานอดิเรก</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["hobby"]) ? $row_all["hobby"] : "ยังไม่ได้ระบุ"; ?></span>
                            </div>
                        </div>
                        <div class="info-item-box">
                            <i class="fas fa-music" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">ดนตรีที่ชอบ</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["favorite_music"]) ? $row_all["favorite_music"] : "ยังไม่ได้ระบุ"; ?></span>
                            </div>
                        </div>
                        <div class="info-item-box">
                            <i class="fas fa-film" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">หนังที่ชอบ</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["favorite_movie"]) ? $row_all["favorite_movie"] : "ยังไม่ได้ระบุ"; ?></span>
                            </div>
                        </div>
                        <div class="info-item-box">
                            <i class="fas fa-bullseye" style="font-size: 25px; "></i>
                            <div class="info-text">
                                <strong style="padding-left:10px;">เป้าหมาย</strong>
                                <span style="padding-left:10px;"><?= !empty($row_all["goal"]) ? $row_all["goal"] : "ยังไม่ได้ระบุ"; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="B" style="margin-bottom:30px;">
                <br>
                <form action="m_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="edit-profile-card">
                        <div class="section-header-icon">
                            <i class="fas fa-edit" style="font-size: 25px;"></i>
                            <h3 class="section-title" style="padding-left:10px;">แก้ไขข้อมูลโปรไฟล์</h3>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3 text-center">
                                <img src="<?= $_SESSION["emp_pic"]; ?>" onerror="this.src='../images/default.png'" alt="Profile Picture" class="profile-img-preview">
                                <div class="form-group">
                                    <label for="image_name">รูปโปรไฟล์</label>
                                    <input type="file" name="image_name" id="image_name" class="form-control-file" accept="image/*">
                                    <small class="text-muted">เลือกรูปภาพเพื่อเปลี่ยนรูปโปรไฟล์ (นามสกุลไฟล์ที่รองรับ: .jpeg, .jpg, .png, .gif)</small>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="firstname">ชื่อ</label>
                                    <input type="text" id="firstname" class="form-control-edit" value="<?= $row_all["firstname"]; ?>" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lastname">นามสกุล</label>
                                    <input type="text" id="lastname" class="form-control-edit" value="<?= $row_all["lastname"]; ?>" disabled>
                                </div>
                            </div>
                        </div>
                          <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea name="bio" id="bio" class="form-control-edit" rows="3"><?= $row_all["bio"]; ?></textarea>
                                </div>
                            </div>
                        </div>
                         <div class="section-header-icon">
                        <i class="fas fa-address-book" style="font-size: 25px; "></i>
                        <h3 style="padding-left:10px;" class="section-title">ช่องทางการติดต่อ</h3>
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile">เบอร์โทรศัพท์</label>
                                    <input type="text" name="mobile" id="mobile" class="form-control-edit" value="<?= $row_all['mobile']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">อีเมล</label>
                                    <input type="email" name="email" id="email" class="form-control-edit" value="<?= $row_all['email']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile">Line</label>
                                    <input type="text" name="mobile" id="mobile" class="form-control-edit" value="<?= $row_all['mobile']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">ig</label>
                                    <input type="email" name="email" id="email" class="form-control-edit" value="<?= $row_all['email']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile">facebook</label>
                                    <input type="text" name="mobile" id="mobile" class="form-control-edit" value="<?= $row_all['mobile']; ?>">
                                </div>
                            </div>
                        </div>
                         <div class="section-header-icon" style="font-size: 25px; ">
                        <i class="fas fa-heartbeat"></i>
                        <h3 class="section-title" style="padding-left:10px;">ไลฟ์สไตล์</h3>
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hobby">งานอดิเรก</label>
                                    <input type="text" name="hobby" id="hobby" class="form-control-edit" value="<?= $row_all["hobby"]; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="favorite_music">ดนตรีที่ชอบ</label>
                                    <input type="text" name="favorite_music" id="favorite_music" class="form-control-edit" value="<?= $row_all["favorite_music"]; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="favorite_movie">หนังที่ชอบ</label>
                                    <input type="text" name="favorite_movie" id="favorite_movie" class="form-control-edit" value="<?= $row_all["favorite_movie"]; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="goal">เป้าหมาย</label>
                                    <input type="text" name="goal" id="goal" class="form-control-edit" value="<?= $row_all["goal"]; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="submit_edit_profile" class="btn-save-changes" id="saveBtn">บันทึกการเปลี่ยนแปลง</button>
                        </div>
                    </div>
                </form>
            </div>
    
    




			<div class="tab-pane fade" id="C" style="margin-bottom:30px;">
				<h1 style="color:#999;text-align:left;font-size: 2em;text-shadow:none;padding:0px;text-decoration:underline;" lang="en">Profile</h1>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Emp Code</td>
						<td>
							<div>
								<input readonly class="form-control" type="text" name="emp_code" id="emp_code" value="<?= $row_all["emp_code"]; ?>" onkeyup="check_availability_dym('emp_code','submit_change','<?= $row_all["emp_code"]; ?>','emp_code','m_employee','emp_del is null and comp_id');" oncontextmenu="return false;"><br><span id='availability_result_emp_code'></span>
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Payroll Code</td>
						<td>
							<div>
								<input readonly class="form-control" type="text" name="payroll_code" id="payroll_code" value="<?= $row_all["emp_payroll_code"]; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Time Code</td>
						<td>
							<div><input readonly class="form-control" type="text" name="emp_time_id" id="emp_time_id" value="<?= $row_all["emp_time_id"]; ?>" /></div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Branch</td>
						<td>
							<div>
<?php
								$sql_bch = "select branch_name from m_branch where m_branch.comp_id='" . $_SESSION["comp_id"] . "' and m_branch.branch_del = '0' and m_branch.branch_id = '" . $row_all['branch_id'] . "' limit 0,1";
								$que_bch = $mysqli -> query($sql_bch);
								$row_bch = mysqli_fetch_assoc($que_bch);
?>
								<input readonly class="form-control" type="text" id="sel_bch" name="sel_bch" value="<?= ($row_bch['branch_name']) ? $row_bch["branch_name"] : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Department</td>
						<td>
							<div>
<?php
								$sql_dept = "select dept_description from m_department where m_department.comp_id='" . $_SESSION["comp_id"] . "' and m_department.dept_id = '" . $row_all['dept_id'] . "' and (m_department.dept_id<>'1' && m_department.dept_code<>'Admin') and m_department.dept_del is null limit 0,1";
								$que_dept = $mysqli -> query($sql_dept);
								$row_dept = mysqli_fetch_assoc($que_dept);
?>
								<input readonly class="form-control" id="sel_dept" name="sel_dept" value="<?= ($row_dept['dept_description']) ? $row_dept["dept_description"] : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Division</td>
						<td>
							<div>
<?php
								$sql_divis = "select divis_description from m_division where m_division.comp_id='" . $_SESSION["comp_id"] . "' and m_division.divis_id = '" . $row_all['divis_id'] . "' and m_division.divis_del is null ";
								$que_divis = $mysqli -> query($sql_divis);
								$row_divis = mysqli_fetch_assoc($que_divis);
?>
								<input readonly class="form-control" id="sel_divis" name="sel_divis" value="<?= ($row_divis['divis_description']) ? $row_divis["divis_description"] : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Position Level</td>
						<td>
							<div>
<?php 
								$sql_posilv = "select posilv_name from m_position_level where comp_id='" . $_SESSION["comp_id"] . "'  and posilv_id = '" . $row_all['posilv_id'] . "' and posilv_del <> 1 ";
								$que_posilv = $mysqli -> query($sql_posilv);
								$row_posilv = mysqli_fetch_assoc($que_posilv);
?>
								<input readonly class="form-control" id="sel_posilv" name="sel_posilv" value="<?= ($row_posilv['posilv_name']) ? $row_posilv["posilv_name"] : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Job title</td>
						<td>
							<div>
<?php 
								$sql_posi = "select * from m_position where posi_id<>'1' and comp_id='" . $_SESSION['comp_id'] . "' and posi_id = '" . $row_all['posi_id'] . "' and posi_del is null ";
								$que_posi = $mysqli -> query($sql_posi);
								$row_posi = mysqli_fetch_assoc($que_posi);
?>
								<input readonly class="form-control" id="sel_posi" name="sel_posi" value="<?= ($row_posi['posi_description']) ? $row_posi["posi_description"] : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Shift</td>
						<td>
							<div>
<?php
								$sql_shilf_emp = "select working_index from abs_user_workingday where emp_id = " . $row_all["emp_id"] . " group by working_index limit 0,1";
								$rs_shilf_emp = mysqli_fetch_assoc($mysqli -> query($sql_shilf_emp));
								$sql_shilf = "select working_index,working_name from abs_set_workingday where comp_id = " . $_SESSION["comp_id"] . " and working_index = '" . $rs_shilf_emp['working_index'] . "' group by working_index limit 0,1";
								$que_shilf = $mysqli -> query($sql_shilf);
								$row_shilf = mysqli_fetch_assoc($que_shilf);
?>
								<input readonly class="form-control" id="sel_shilf" name="sel_shilf" value="<?= ($row_shilf['working_name']) ? $row_shilf["working_name"] : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Header of User</td>
						<td>
							<div>
<?php
								$headname = '';
								$sql_view = " select * from m_employee inner join m_employee_info on m_employee.emp_id=m_employee_info.emp_id left join m_department on m_employee.dept_id=m_department.dept_id where m_employee.comp_id='" . $_SESSION["comp_id"] . "' and m_employee.emp_id = '" . $row_all['under_emp_id'] . "' and m_department.dept_code<>'Admin' and m_employee.emp_del is null and ifnull(date(m_employee.emp_resign_date),date(NOW())) >= date(NOW())";
								$que_view = $mysqli -> query($sql_view);
								while ($row_view = mysqli_fetch_array($que_view)) {
									if ($row_all['under_emp_id'] == $row_view['emp_id']) {
										$headname = $row_view['firstname'] . ' &nbsp; ' . $row_view['lastname'];
									}
								}
?>
								<input readonly class="form-control" id="sel_header" name="sel_header" value="<?= ($headname) ? $headname : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Email Group of User</td>
						<td>
							<div>
<?php
								$sel_mail_group = '';
								$sql_view = " select * from m_email_group where comp_id='" . $_SESSION['comp_id'] . "' and mail_group_del is null ";
								$que_view = $mysqli -> query($sql_view);
								while ($row_view = mysqli_fetch_array($que_view)) {
									if ($row_all['mail_group_id'] == $row_view['mail_group_id']) {
										$sel_mail_group = $row_view['mail_group_description'];
									}
								}
?>
								<input readonly class="form-control" id="sel_mail_group" name="sel_mail_group" value="<?= ($sel_mail_group) ? $sel_mail_group : ''; ?>" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">DNA</td>
						<td>
							<div>
								<input readonly type="text" name="dna" class="form-control" id="dna" value="<?= $dna_name; ?>">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Role</td>
						<td>
							<div>
<?php
								$role_edit = '';
								$sql_license_edit = "SELECT r.acrp_id as role_admin FROM m_employee m LEFT JOIN ac_role_position r ON r.acrp_id = m.acrp_id WHERE m.comp_id ='" . $_SESSION['comp_id'] . "' AND r.acrp_name =  'admin' limit 1";
								$result_license_edit = $mysqli -> query($sql_license_edit) or die($mysqli->connect_errno);
								$row_license_edit = mysqli_fetch_assoc($result_license_edit);
								if (!$row_license_edit['role_admin'] || $row_license_edit['role_admin'] == $row_all['acrp_id']) {
									$sql_role_edit = "SELECT acrp_id,acrp_name FROM ac_role_position where comp_id = '" . $_SESSION['comp_id'] . "' order by acrp_name asc";
								} else {
									$sql_role_edit = "SELECT acrp_id,acrp_name FROM ac_role_position where comp_id = '" . $_SESSION['comp_id'] . "' and acrp_id <> '" . $row_license_edit['role_admin'] . "' order by acrp_name asc";
								}
								$rs_role_edit = $mysqli -> query($sql_role_edit);
								while ($row_role_edit = mysqli_fetch_array($rs_role_edit)) { 
									if ($row_all['acrp_id'] == $row_role_edit['acrp_id']) {
										$role_edit = $row_role_edit['acrp_name'];
									} 
								}
?>
								<input readonly type="text" name="Role_edit" class="form-control" id="Role_edit" value="<?= $role_edit; ?>">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Mobile. Office</td>
						<td>
							<div>
								<input readonly value="<?= $row_all['tel_office']; ?>" type="text" name="tel_office" id="tel_office" class="form-control">
							</div>
						</td>
					</tr>
				</table>
				<h1 style="color:#999;text-align:left;font-size: 2em;text-shadow:none;padding:0px;margin-top:20px;text-decoration:underline;" lang="en">Benefit</h1>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Size Shirt</td>
						<td>
							<div>
								<input readonly type="text" name="size_shirt" class="form-control" id="size_shirt" value="<?= $row_all['size_shirt']; ?>">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Social Insurance</td>
						<td>
							<div class="hosp_block">
<?php
								$columnSSO = "sso.sso_hospital_id,
											hos.hosp_name_th as hosp_name,
											date_format(sso.sso_hospital_start,'%Y/%m/%d') as hospital_start,
											date_format(sso.sso_hospital_end,'%Y/%m/%d') as hospital_end";
								$tableSSO = "m_employee_welfare sso";
								$whereSSO = "left join 
												m_hospital hos on hos.hosp_id = sso.sso_hospital_id 
											where 
												sso.emp_id = '{$_SESSION['emp_id']}'";
								$SSO = select_data($columnSSO,$tableSSO,$whereSSO);
								$hosp_name = $SSO[0]['hosp_name'];
								$hospital_start = $SSO[0]['hospital_start'];
								$hospital_end = $SSO[0]['hospital_end'];
?>
								<input readonly value="<?php echo $hosp_name; ?>" type="text" id="txtsocial_hosp" name="txtsocial_hosp" class="form-control" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en"></td>
						<td>
							<div>
								<span lang="en">Start</span>
								<input readonly value="<?php echo $hospital_start; ?>" type="text" id="start_date_social_hosp" name="start_date_social_hosp" class="form-control" style="width:118px;display:inline;">
								<span lang="en">End</span>
								<input readonly value="<?php echo $hospital_end; ?>" type="text" id="end_date_social_hosp" name="end_date_social_hosp" class="form-control" style="width:118px;display:inline;">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Group Insurance</td>
						<td>
							<div class="hosp_block">
<?php
								$columnINS = "sso.group_hospital_id,
											hos.hosp_name_th as hosp_name,
											date_format(sso.group_hospital_start,'%Y/%m/%d') as hospital_start,
											date_format(sso.group_hospital_end,'%Y/%m/%d') as hospital_end";
								$tableINS = "m_employee_welfare sso";
								$whereINS = "left join 
												m_hospital hos on hos.hosp_id = sso.group_hospital_id 
											where 
												sso.emp_id = '{$_SESSION['emp_id']}'";
								$INS = select_data($columnINS,$tableINS,$whereINS);
								$ins_hosp_name = $INS[0]['hosp_name'];
								$ins_hospital_start = $INS[0]['hospital_start'];
								$ins_hospital_end = $INS[0]['hospital_end'];
?>
								<input readonly value="<?php echo $ins_hosp_name; ?>" type="text" id="txtgroup_hosp" name="txtgroup_hosp" class="form-control" />
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en"></td>
						<td>
							<div>
								<span lang="en">Start</span>
								<input readonly value="<?php echo $ins_hospital_start; ?>" type="text" id="start_date_group_hosp" name="start_date_group_hosp" class="form-control" style="width:118px;display:inline;">
								<span lang="en">End</span>
								<input readonly value="<?php echo $ins_hospital_end; ?>" type="text" id="end_date_group_hosp" name="end_date_group_hosp" class="form-control" style="width:118px;display:inline;">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Bank Account</td>
						<td>
							<div>
								<input readonly value="<?= $row_all['bank_account']; ?>" type="text" name="bank_account" id="bank_account" class="form-control">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en"></td>
						<td>
							<div>
<?php
								$sql_emp_file = "select * from m_employee_file where emp_id = " . $row_all['emp_id'] . " and emp_file_status is null and emp_file_type = 3 order by emp_file_id desc limit 1";
								$result_emp_file = $mysqli -> query($sql_emp_file) or die($mysqli->connect_errno);
?>
								<table name="tb_upload_file_bookbank" id="tb_upload_file_bookbank">
									<tr style="height:1px;">
										<th width="80"></th>
										<th width="200"></th>
										<th width="50"></th>
										<th width="100"></th>
									</tr>
<?php 
									while ($row_emp_file = mysqli_fetch_array($result_emp_file)) { 
?>
										<tr class="tr_upload_file_<?php echo $row_emp_file['emp_file_id']; ?>">
											<td>
												<?php echo date_format(date_create($row_emp_file["emp_file_date"]), 'd/m/Y'); ?>
											</td>
											<td>
												<?php echo $row_emp_file['emp_file_name']; ?>
											</td>
											<td>
<?php 
												if (in_array(end(explode(".", $row_emp_file['path_file'])), array('pdf', 'PDF'))) { 
?>
													<a class="fancy fancy_pdf" href="<?php echo $row_emp_file['path_file']; ?>" target="_blank">
														<i class="fa fa-file-pdf-o fa-2x"></i>
													</a>
<?php 
												} else { 
?>
													<a class="fancy fancy_img" href="<?php echo $row_emp_file['path_file']; ?>">
														<i class="fa fa-file-photo-o fa-2x"></i>
													</a>
<?php 
												} 
?>
											</td>
											<td></td>
										</tr>
<?php 
									}
?>
								</table>
							</div>
						</td>
					</tr>
				</table>
				<h1 style="color:#999;text-align:left;font-size: 2em;text-shadow:none;padding:0px;margin-top:20px;text-decoration:underline;" lang="en">Referrer</h1>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Referrer</td>
						<td>
							<div>
<?php
								$emp_referrer = '';
								$sql_view = " select * from m_employee inner join m_employee_info on m_employee.emp_id=m_employee_info.emp_id left join m_department on m_employee.dept_id=m_department.dept_id where m_employee.comp_id='" . $_SESSION["comp_id"] . "' and m_employee.emp_id <> '" . $row_all['emp_id'] . "' and m_department.dept_code<>'Admin' and m_employee.emp_del is null and ifnull(date(m_employee.emp_resign_date),date(NOW())) >= date(NOW()) order by m_employee.emp_code asc ";
								$que_view = $mysqli -> query($sql_view);
								while ($row_view = mysqli_fetch_array($que_view)) {
									if ($row_all['emp_referrer'] == $row_view['emp_id']) {
										$emp_referrer = $row_view['firstname'] . ' &nbsp; ' . $row_view['lastname'];
									} 
								} 
?>
								<input readonly value="<?= $emp_referrer; ?>" type="text" name="emp_referrer" id="emp_referrer" class="form-control">
							</div>
						</td>
					</tr>
					<tr>
						<td style="height:40px; width:210px; padding-left:7px;" lang="en">Referrer to you</td>
						<td>
							<div>
<?php
								$new_refer = 0;
								$pass_refer = 0;
								$str_refer = '';
								$sql_refer_emp = "select * from m_employee e left join m_employee_info i on e.emp_id = i.emp_id where e.emp_referrer = '" . $row_all['emp_id'] . "' order by i.firstname";
								$rs_refer_emp = $mysqli -> query($sql_refer_emp);
								$num_refer_emp = mysqli_num_rows($rs_refer_emp);
								if ($num_refer_emp) {
									while ($row_refer_emp = mysqli_fetch_array($rs_refer_emp)) {
										switch ($row_refer_emp['pass_pro']) {
											case 'Y':
												$pass_refer++;
												$type_refer = '<span lang="en">Pass Prof</span>';
												break;
											case 'N':
												$new_refer++;
												$type_refer = '<span lang="en">New Emp</span>';
												break;
										}
										$str_refer .= '<li id="list_refer_' . $row_refer_emp['emp_id'] . '">';
										$str_refer .= '<span class="refer_name">';
										$str_refer .= $row_refer_emp['firstname'] . ' ' . $row_refer_emp['lastname'];
										$str_refer .= '</span>';
										$str_refer .= '<span class="refer_type">';
										$str_refer .= $type_refer;
										$str_refer .= '</span>';
										$str_refer .= '<span class="refer_action">';
										$str_refer .= '<a onclick="del_refer(\'' . $row_refer_emp['emp_id'] . '\',\'' . $row_refer_emp['pass_pro'] . '\');">';
										$str_refer .= '</a>';
										$str_refer .= '</span>';
										$str_refer .= '</li>';
									}
								}
?>
								<input type="hidden" name="refer_del" id="refer_del" value="" />
								<div style="display:inline-block;height:40px; width:120px;padding-top:11px;"><span lang="en">New Emp</span>. <span id="new_refer"><?php echo $new_refer; ?></span></div>
								<div style="display:inline-block;height:40px; width:120px;padding-top:11px;"><span lang="en">Pass Prof</span>. <span id="pass_refer"><?php echo $pass_refer; ?></span></div>
							</div>
						</td>
					</tr>
<?php 
					if ($num_refer_emp) {
?>
						<tr>
							<td style="width:80px; padding-left:7px;" colspan="2">
								<hr />
								<ol>
									<?php echo $str_refer; ?>
								</ol>
							</td>
						</tr>
<?php 
					}
?>
				</table>
				<br /><br /><br /><br /><br />
			</div>
			<div class="tab-pane fade" id="G" style="margin-bottom:30px;"><br />
				<ul class="nav nav-tabs">
					<li class="nav active"><a href="#trainee" data-toggle="tab"><span lang="en">Trainee</span></a></li>
					<li class="nav"><a href="#coach" data-toggle="tab"><span lang="en">Coach</span></a></li>
				</ul>
				<div class="tab-content"> <br>
					<div class="tab-pane fade in active" id="trainee">
						<div class="table-responsive">
							<table class="table table-striped display" id="table-course">
								<thead>
									<tr>
										<th width="20">#</th>
										<th lang="en" width="100">Date</th>
										<th lang="en">Name Course</th>
										<th lang="en" width="30">Hours</th>
										<th lang="en" width="150">Open VDO</th>
										<th lang="en" width="150"></th>
									</tr>
								</thead>
								<tbody>
<?php
									$num_course = 1;
									$trn_all = array();
									$sql_traning = "select *,SUBSTRING(IF(ot_training_list.trn_to_time <> '' AND ot_training_list.trn_to_time IS NOT NULL AND ot_training_list.trn_from_time <> '' AND  ot_training_list.trn_from_time IS NOT NULL, SEC_TO_TIME((TIME_TO_SEC(REPLACE(concat(CURDATE(),' ',ot_training_list.trn_to_time),'.',':')))-(TIME_TO_SEC(REPLACE(concat(CURDATE(),' ',ot_training_list.trn_from_time),'.',':')))), '00:00:00'),1,5) as total_time from ot_training_list left join ot_employee_training on ot_training_list.trn_id = ot_employee_training.trn_id where ot_training_list.comp_id='" . $_SESSION['comp_id'] . "' and ot_employee_training.emp_id='" . $row_all['emp_id'] . "'";
									$result_traning = $mysqli -> query($sql_traning);
									$numrow_traning = mysqli_num_rows($result_traning);
									if ($numrow_traning != 0) {
										while ($row_traning = mysqli_fetch_array($result_traning)) {
											$trn_all[] = $row_traning['trn_id'];
											$trn_date = ($row_traning['trn_date'] &&  $row_traning['trn_date'] != '0000-00-00') ? thaidate($row_traning['trn_date']) : '';
											$sdate = thaidate($row_traning['emp_trn_start_date_time']);
											$edate = thaidate($row_traning['emp_trn_end_date_time']);
?>
											<tr id="row-trn-<?php echo $row_traning['trn_id']; ?>">
												<td class="td-middle">
													<?php echo $num_course; ?>
													<input type="hidden" name="trn_id_<?php echo $row_traning['trn_id']; ?>" id="trn_id_<?php echo $row_traning['trn_id']; ?>" value="<?php echo $row_traning['trn_id']; ?>" />
												</td>
												<td class="td-middle"><?php echo $trn_date; ?></td>
												<td class="td-middle"><?php echo $row_traning['trn_subject']; ?></td>
												<td class="td-middle"><?php echo $row_traning['total_time']; ?></td>
												<td class="td-middle">
													<span lang="en">Start Date</span>
													<span><?php echo $sdate; ?></span>
												</td>
												<td class="td-middle">
													<span lang="en">End Date</span>
													<span><?php echo $edate; ?></span>
												</td>
											</tr>
<?php
											$num_course++;
										}
									}
?>
								</tbody>
							</table>
							<input type="hidden" name="num_course" id="num_course" value="<?php echo $num_course; ?>" />
							<input type="hidden" name="all_trn_id" id="all_trn_id" value="<?php echo implode(',', $trn_all); ?>" />
							<input type="hidden" name="all_trn_id_new" id="all_trn_id_old" value="<?php echo implode(',', $trn_all); ?>" />
						</div>
					</div>
					<div class="tab-pane fade" id="coach">
						<div style="margin:0 auto;">
							<div class="table-responsive">
								<table class="table table-striped display" id="table-coach">
									<thead>
										<tr>
											<th width="20">#</th>
											<th lang="en" width="100">Date</th>
											<th lang="en">Name Course</th>
											<th lang="en" width="30">Hours</th>
										</tr>
									</thead>
									<tbody>
<?php
										$num_coach = 1;
										$sql_coach ="select *,SUBSTRING(IF(ot_training_list.trn_to_time <> '' AND ot_training_list.trn_to_time IS NOT NULL AND ot_training_list.trn_from_time <> '' AND  ot_training_list.trn_from_time IS NOT NULL, SEC_TO_TIME((TIME_TO_SEC(REPLACE(concat(CURDATE(),' ',ot_training_list.trn_to_time),'.',':')))-(TIME_TO_SEC(REPLACE(concat(CURDATE(),' ',ot_training_list.trn_from_time),'.',':')))), '00:00:00'),1,5) as total_time from ot_training_list where ot_training_list.comp_id='" . $_SESSION['comp_id'] . "' and FIND_IN_SET('" . $row_all['emp_id'] . "',ot_training_list.trn_by_emp) ";
										$result_coach = $mysqli -> query($sql_coach);
										$numrow_coach = mysqli_num_rows($result_coach);
										if ($numrow_coach != 0) {
											while ($row_coach = mysqli_fetch_array($result_coach)) {
												$trn_date_coach = ($row_coach['trn_date'] &&  $row_coach['trn_date'] != '0000-00-00') ? thaidate($row_coach['trn_date']) : '';
?>
												<tr>
													<td class="td-middle"><?php echo $num_coach; ?></td>
													<td class="td-middle"><?php echo $trn_date_coach; ?></td>
													<td class="td-middle"><?php echo $row_coach['trn_subject']; ?></td>
													<td class="td-middle"><?php echo $row_coach['total_time']; ?></td>
												</tr>
<?php
												$num_coach++;
											}
										}
?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="D" style="margin-bottom:30px;">
				<br>
				<div id="profile_personal">
<?php
					$sql_rg_detail = " select app_register_id,emp_id from app_register_employee where emp_id='" . $_SESSION["emp_id"] . "' ";
					$rs_rg_detail = $mysqli -> query($sql_rg_detail);
					$row_rg_detail = mysqli_fetch_array($rs_rg_detail);
					if ($row_rg_detail['app_register_id']) {
						$register_id = $row_rg_detail["app_register_id"];
						$register_comp = $_SESSION["comp_id"];
						$turn_back = 'm_profile.php';
						$turn_page = 'm_profile.php';
						include('personal/include_profile.php');
					} else {
						echo "<h1>Don't have detail register.</h1>";
					}
?>
				</div>
			</div>
			<div class="tab-pane fade" id="E" style="margin-bottom:30px;">
				<!-- <div class="health_area"></div> -->
			</div>
			<div class="tab-pane fade" id="F" style="margin-bottom:30px;">
				<div id="chart-orgami" class="chart-container"></div>
			</div>
			<div class="tab-pane fade" id="I" style="margin-bottom:30px;">
				<?php include('m_profile_merit.php'); ?>
			</div>
		</div>
	</div>
	<div id="profileModal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<form id="form_avatar" method="POST" enctype="multipart/form-data">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
					</div>
					<div class="modal-body">
						<label class="preview-uploads" for="image_name">
							<img class="avatar-uploads" src="<?php echo $_SESSION["emp_pic"]; ?>" onerror="this.src='../images/default.png'">
							<span style="display:<?php if($_SESSION["emp_pic"]){echo 'none';}else{echo 'block';} ?>;"><i class="fas fa-cloud-upload-alt fa-2x"></i><h5 lang="en">Upload image profile</h5></span>
							<input name="image_name" id="image_name" type="file" onchange="readURL(this);">
							<a><i class="fas fa-tools"></i></a>
						</label>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-orange upload-img" name="upload_avatar" lang="en" disabled>Upload</button>
						<button type="button" class="btn btn-white" data-dismiss="modal" lang="en">Cancel</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>
</html>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzxc7D9o3CcmSyLWVo6h4rCxS0yL_wB2k&libraries=places"></script>

<script language="javascript">
	function buildHealth() {
		$(".loader").addClass("active");
		$(".health_area").load("hrm/library/template/health.php");
	}
	function readURL(input) {
		if (input.files && input.files[0]) {
			var reader2 = new FileReader();
			$('.preview-uploads img.avatar-uploads').css("display","block");
			$('.preview-uploads span').css("display","none");
			reader2.onload = function (e) {
				$('.preview-uploads img.avatar-uploads').attr('src', e.target.result);
			};
			reader2.readAsDataURL(input.files[0]);
			$(".upload-img").attr("disabled",false);
		} else {
			$('.preview-uploads img.avatar-uploads').css("display","none");
			$('.preview-uploads span').css("display","block");
			$(".upload-img").attr("disabled",true);
		}
	}
	function chk_submit_info() {
		var d = $('#docsign.signature').jSignature("getData", "native");
		if (d.length > 0 || (d.length === 1 && d[0].x.length > 0)) {
			var sign = $('#docsign.signature').jSignature("getData", "image");
			$('#signature_drawing').val(sign);
		}
	}
	function chk_submit() {
		var pass_old = document.getElementById("old_pass");
		var pass_new = document.getElementById("new_pass");
		var pass_firm = document.getElementById("firm_pass");
		if (pass_old.value == "") {
			alert('Please Insert Old Password');
			pass_old.focus();
			return false;
		}
		if (pass_new.value == "") {
			alert('Please Insert New Password');
			pass_new.focus();
			return false;
		}
		if (pass_firm.value == "") {
			alert('Please Insert Confirm Password');
			pass_firm.focus();
			return false;
		}
		if (pass_new.value != pass_firm.value) {
			alert('Password does not match. Please renew Password');
			pass_new.value = "";
			pass_firm.value = "";
			pass_new.focus();
			return false;
		}
	}
	function reset_signature(id) {
		$('#' + id).jSignature("clear");
		$('#signature_drawing').val('');
		return true;
	}
	function reset_signature_img(id) {
		var w_user = '300px';
		var h_user = '100px';
		$("#docsign").empty();
		$("#docsign").append('<i class="fa fa-undo reset_signature" onclick="reset_signature(\'docsign\');"></i>');
		$("#docsign").removeClass('signature_img');
		$("#docsign").addClass('signature');
		$("#docsign").jSignature({
			'width': w_user,
			'height': h_user,
			'decor-color': 'transparent'
		});
		$("#docsign").jSignature("clear");
		$('#signature_drawing').val('');
		return true;
	}

</script>
<?php
	function thaidate($value){
		if(empty($value)){
			return "";
		}
		return substr($value,8,2)."/".substr($value,5,2)."/".substr($value,0,4);
	}
	function find_birth($birthday,$today){
		list($byear, $bmonth, $bday)= explode("-",$birthday);
		list($tyear, $tmonth, $tday)= explode("-",$today);
		$mbirthday = mktime(0, 0, 0, $bmonth, $bday, $byear); 
		$mnow = mktime(0, 0, 0, $tmonth, $tday, $tyear );
		$mage = ($mnow - $mbirthday);
		$u_y = date("Y", $mage)-1970;
		$u_m = date("m",$mage)-1;
		$u_d = date("d",$mage)-1;
		return "$u_y   ปี    $u_m เดือน      $u_d  วัน";
	}
?>