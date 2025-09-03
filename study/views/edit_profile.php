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

global $mysqli;

// แก้ไข: ดึงข้อมูลจากตาราง classroom_student แทน
$sql_student = "SELECT * FROM classroom_student WHERE student_id = 1"; // ควรเปลี่ยนเป็น $_SESSION['student_id'] ถ้ามี
$result_student = $mysqli->query($sql_student);
$row_student = mysqli_fetch_assoc($result_student);

// ตั้งค่า session สำหรับรูปโปรไฟล์และชื่อ
$_SESSION["user"] = $row_student["student_firstname_th"] . " " . $row_student["student_lastname_th"];
$_SESSION["emp_pic"] = $row_student["student_image_profile"];
$comp_id = $_SESSION['comp_id'] ? $_SESSION['comp_id']: null; // ใช้ Null Coalescing Operator เพื่อป้องกัน error ถ้าไม่มี session

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST มาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตั้งค่าให้ return เป็น JSON
    header('Content-Type: application/json');

    // ตรวจสอบว่ามี session id ของนักเรียนหรือไม่ (เพื่อความปลอดภัย)
    if (!isset($_SESSION['student_id'])) {
        $response['status'] = 'error';
        $response['message'] = 'Session expired. Please log in again.';
        echo json_encode($response);
        exit;
    }

    // ดึงค่า student_id จาก session
    $student_id = (int)$_SESSION['student_id'];

    // เตรียมตัวแปรสำหรับเก็บข้อมูลที่รับมา
    $bio = $_POST['bio'] ?  $_POST['bio']: '';
    $mobile = $_POST['mobile'] ?  $_POST['mobile']: '';
    $email = $_POST['email'] ?  $_POST['email']: '';
    $line = $_POST['line'] ?  $_POST['line']: '';
    $ig = $_POST['ig'] ?  $_POST['ig']: '';
    $facebook = $_POST['facebook'] ?  $_POST['facebook']: '';
    $hobby = $_POST['hobby'] ?  $_POST['hobby']: '';
    $favorite_music = $_POST['favorite_music'] ?  $_POST['favorite_music']: '';
    $favorite_movie = $_POST['favorite_movie'] ?  $_POST['favorite_movie']: '';
    $goal = $_POST['goal'] ?  $_POST['goal']: '';
    $emp_modify = $student_id;
    $date_modify = date("Y-m-d H:i:s");

    // สร้าง SQL query สำหรับอัปเดตข้อมูล Text
    $sql_update = "UPDATE `classroom_student` SET 
        `student_bio` = ?,
        `student_mobile` = ?,
        `student_email` = ?,
        `student_line` = ?,
        `student_ig` = ?,
        `student_facebook` = ?,
        `student_hobby` = ?,
        `student_music` = ?,
        `student_movie` = ?,
        `student_goal` = ?,
        `comp_id` = ?,
        `emp_modify` = ?,
        `date_modify` = ?
        WHERE `student_id` = ?";
    
    // ใช้ prepared statement เพื่อป้องกัน SQL Injection
    $stmt = $mysqli->prepare($sql_update);
    if ($stmt === false) {
        $response['status'] = 'error';
        $response['message'] = 'Prepare failed: ' . $mysqli->error;
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param(
        "sssssssssssssi",
        $bio,
        $mobile,
        $email,
        $line,
        $ig,
        $facebook,
        $hobby,
        $favorite_music,
        $favorite_movie,
        $goal,
        $comp_id,
        $emp_modify,
        $date_modify,
        $student_id
    );

    $is_updated = false;
    if ($stmt->execute()) {
        $is_updated = true;
    }

    $stmt->close();

    // ส่วนจัดการการอัปโหลดไฟล์รูปภาพ
    // if (isset($_FILES['image_name']) && $_FILES['image_name']['error'] == UPLOAD_ERR_OK) {
    //     $upload_dir = '../../uploads/profile/'; // กำหนด path สำหรับบันทึกรูปภาพ
        
    //     // ตรวจสอบและสร้างโฟลเดอร์ถ้าไม่มี
    //     if (!is_dir($upload_dir)) {
    //         mkdir($upload_dir, 0777, true);
    //     }

    //     $image_ext = pathinfo($_FILES['image_name']['name'], PATHINFO_EXTENSION);
    //     $new_image_name = 'profile_' . $student_id . '_' . time() . '.' . $image_ext;
    //     $target_file = $upload_dir . $new_image_name;

    //     // ย้ายไฟล์ที่อัปโหลด
    //     if (move_uploaded_file($_FILES['image_name']['tmp_name'], $target_file)) {
    //         // อัปเดต path ของรูปภาพในฐานข้อมูล
    //         $sql_update_image = "UPDATE `classroom_student` SET `student_image_profile` = ? WHERE `student_id` = ?";
    //         $stmt_image = $mysqli->prepare($sql_update_image);
    //         if ($stmt_image === false) {
    //              $response['status'] = 'error';
    //              $response['message'] = 'Prepare image update failed: ' . $mysqli->error;
    //              echo json_encode($response);
    //              exit;
    //         }
    //         $stmt_image->bind_param("si", $target_file, $student_id);
            
    //         if ($stmt_image->execute()) {
    //             // อัปเดต session สำหรับรูปโปรไฟล์ใหม่
    //             $_SESSION["emp_pic"] = $target_file;
    //         }
    //         $stmt_image->close();
    //     } else {
    //         // ถ้าอัปโหลดรูปไม่สำเร็จ
    //         $response['status'] = 'error';
    //         $response['message'] = 'ไม่สามารถอัปโหลดรูปภาพได้';
    //         echo json_encode($response);
    //         exit;
    //     }
    // }

    // ตรวจสอบผลลัพธ์สุดท้ายของการอัปเดต
    if ($is_updated) {
        $response['status'] = 'success';
        $response['message'] = 'บันทึกการเปลี่ยนแปลงโปรไฟล์สำเร็จ';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'ไม่สามารถบันทึกข้อมูลได้: ' . $mysqli->error;
    }

    echo json_encode($response);
    $mysqli->close();
    exit;
}
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
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8"
        type="text/javascript"></script>


    <script src="/classroom/study/js/setting.js?v=<?php echo time(); ?>" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $(".avatar.avatar-profile").css("border", "5px solid <?php echo $dna_color; ?>");
            $('input[type=file]').change(function () {
                var input = this;
                if (input.files && input.files[0]) {
                    var fileTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    var extension = input.files[0].name.split('.').pop().toLowerCase(),
                        isSuccess = fileTypes.indexOf(extension) > -1;
                    if (isSuccess) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
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
            var get_datascource = load_datascource(<?php echo $_SESSION['student_id']; ?>);
            var ajaxURLs = {
                'children': function (nodeData) {
                    return 'orgchart/children.php?id=' + nodeData.id;
                }
            };
            // $('#chart-orgami').orgchart({
            //     'data': get_datascource,
            //     'ajaxURL': ajaxURLs,
            //     'nodeContent': 'name',
            //     'parentNodeSymbol': '',
            //     'pan': true,
            //     'zoom': true,
            //     'createNode': function ($node, data) {
            //         $node.children('.title').html('<img src="' + ((data.pic) ? '../' + data.pic : '../' + 'images/default.png') + '" onerror="this.src=\'../images/default.png\'" class="bxtCXs dqIZME">');
            //         var nickname = (data.nickname) ? ' (' + data.nickname + ')' : '';
            //         $node.children('.content').append('<div class="kydEot"><b>' + data.name + ' ' + data.lastname + nickname + '</b></div>');
            //         if (data.position) {
            //             $node.children('.content').append('<div class="ebDfCA">' + data.position + '</div>');
            //         }
            //         if (data.dept) {
            //             $node.children('.content').append('<div class="ebDfCA">' + data.dept + '</div>');
            //         }
            //         if (data.division) {
            //             $node.children('.content').append('<div class="ebDfCA">' + data.division + '</div>');
            //         }
            //     }
            // });
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
        $('body').bind('DOMNodeInserted', function (e) {
            if (e.target.tagName == 'select') {
                bind_select(this);
            } else {
                $(e.target).find('select').each(function () {
                    bind_select(this);
                });
            }
        });
        function load_datascource(id) {
            var datascource_json;
            $.ajax({
                url: '../../orgchart/datasource.php',
                data: {
                    id: id
                },
                type: 'post',
                dataType: 'JSON',
                async: false,
                success: function (result) {
                    datascource_json = result;
                }
            });
            return datascource_json;
        }
    </script>
    <script>
        $(document).ready(function () {
            // ส่วนนี้จะจัดการ active class เมื่อคลิกที่เมนู
            $('.top-nav a').on('click', function (e) {
                $('.top-nav a').removeClass('active');
                $(this).addClass('active');
            });

            // กำหนดให้เมนูแรกเป็น active ตั้งแต่แรกที่โหลดหน้า
            $('.top-nav a[href="#A"]').addClass('active');

              // ดักจับการคลิกที่ปุ่มบันทึก
            $("#saveBtn").on("click", function (e) {
                e.preventDefault(); // ป้องกันการ submit ฟอร์มแบบปกติ
                
                // ดึงข้อมูลจากฟอร์มทั้งหมดด้วย FormData
                var formData = new FormData($("#editProfileForm")[0]);
                
                // ส่งข้อมูลด้วย AJAX
                $.ajax({
                    url: "views/edit_profile.php", // **สำคัญ: ต้องเปลี่ยนเป็นชื่อไฟล์ PHP ของคุณ**
                    type: "POST",
                    data: formData,
                    processData: false, // จำเป็นสำหรับ FormData
                    contentType: false, // จำเป็นสำหรับ FormData
                    dataType: 'JSON',
                    success: function (response) {
                        // response คือค่าที่ส่งกลับมาจากไฟล์ PHP
                        if (response.status === 'success') {
                            swal({
                                title: "บันทึกสำเร็จ",
                                text: response.message, // ข้อความจาก PHP
                                type: "success",
                                showCancelButton: false,
                                confirmButtonColor: "#ff6600",
                                confirmButtonText: "ตกลง",
                                closeOnConfirm: false
                            }, function () {
                                location.reload(); // รีเฟรชหน้าเว็บเมื่อกดตกลง
                            });
                        } else {
                            // ถ้าบันทึกไม่สำเร็จ
                            swal({
                                title: "เกิดข้อผิดพลาด",
                                text: response.message, // ข้อความจาก PHP
                                type: "error",
                                showCancelButton: false,
                                confirmButtonColor: "#ff6600",
                                confirmButtonText: "ตกลง",
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // กรณีเกิดข้อผิดพลาดในการเชื่อมต่อ
                        console.error("AJAX Error:", textStatus, errorThrown);
                        swal({
                            title: "เกิดข้อผิดพลาด",
                            text: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ หรือมีข้อผิดพลาดในการประมวลผล",
                            type: "error",
                            showCancelButton: false,
                            confirmButtonColor: "#ff6600",
                            confirmButtonText: "ตกลง",
                        });
                    }
                });
            });
        });
      
    </script>
    <style>
        /* --- New Style for a modern, flat UI --- */
        body {
            background-color: #f0f2f5;
            /* Light gray background */
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
        }

        .top-nav-container {
            width: 100%;
            display: flex;
            justify-content: center;
            /* ให้อยู่กลาง */
            background: #ffc27c;
            ;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            /* margin-top: 35px; */
            /* border-radius: 0 0 20px 20px; มุมโค้งล่าง */
        }

        .top-nav {
            list-style: none;
            display: flex;
            gap: 25px;
            /* ระยะห่าง icon */
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
            /* พื้นหลังอ่อนโทนส้ม */
            color: #ff6600;
            /* ไอคอนส้ม */
            font-size: 22px;
            transition: all 0.3s ease;
        }

        .top-nav li a:hover {
            background: #ff6600;
            color: #fff;
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(255, 102, 0, 0.3);
        }

        .top-nav li a.active,
        .top-nav li a.active:hover {
            background: #ff6600;
            /* พื้นหลังสีส้มเข้ม */
            color: #fff;
            /* ตัวอักษรและไอคอนสีขาว */
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(255, 102, 0, 0.3);
        }

        /* ปรับขนาด icon ใน contact-grid */
        .contact-grid {
            display: flex;
            flex-wrap: wrap;
            /* ให้บรรทัดขึ้นใหม่ได้ */
            justify-content: center;
            gap: 20px;
        }

        .contact-item {
            text-align: center;
            flex-basis: 100px;
            /* กำหนดขนาดพื้นฐานให้แต่ละไอเทม */
            flex-grow: 1;
            /* ให้แต่ละไอเทมขยายได้ */
        }

        .contact-item a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #7f8c8d;
            font-size: 1.1em;
            /* เพิ่มขนาดฟอนต์ข้อความ */
            transition: transform 0.2s ease;
        }

        .contact-item a span {
            font-size: 1.1em;
            /* เพิ่มขนาดฟอนต์ข้อความ */
        }

        .contact-icon-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 8px;
            font-size: 32px;
            /* ขนาดไอคอนในวงกลมใหญ่ขึ้น */
            color: #fff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
            /* เพิ่ม transition ให้การเปลี่ยนสีดูนุ่มนวลขึ้น */
        }

        @media (max-width: 768px) {
            .top-nav {
                gap: 10px;
                /* ลดช่องว่างระหว่างเมนูบนมือถือ */
                justify-content: space-around;
                /* ให้กระจายตัวเท่าๆ กัน */
            }

            .top-nav li a {
                width: 50px;
                height: 50px;
            }

            .top-nav li a i {
                font-size: 20px;
                /* ปรับขนาดไอคอนให้เล็กลงเล็กน้อย */
            }

            .contact-grid {
                grid-template-columns: repeat(3, 1fr);
                /* แสดง 3 คอลัมน์บนมือถือ */
            }

            .contact-icon-circle {
                width: 55px;
                /* ปรับขนาดเล็กน้อยสำหรับมือถือ */
                height: 55px;
                /* font-size: 50px; ปรับขนาดไอคอนให้เหมาะสม */
            }

            .contact-item a span {
                font-size: 1em;
                /* ปรับขนาดฟอนต์ให้เหมาะสมกับมือถือ */
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            /* margin-bottom: 30px; */
            padding: 40px;
            text-align: center;
            position: relative;
            top: -50px;
        }

        .profile-avatar-square {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            /* Square with rounded corners */
            border: 5px solid #ff8c00;
            /* Orange border */
            overflow: hidden;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar-square img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 2.2em;
            font-weight: 800;
            color: #2c3e50;
            /* Dark blue-gray */
            margin-bottom: 8px;
        }

        .profile-bio {
            font-size: 1.1em;
            color: #7f8c8d;
            /* Grayish blue */
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
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

        .contact-icon-circle.phone {
            background-color: #2ecc71;
        }

        /* สีเดิม */
        .contact-icon-circle.mail {
            background-color: #D44638;
        }

        /* Gmail/Email Red */
        .contact-icon-circle.line {
            background-color: #00B900;
        }

        /* Line Green */
        .contact-icon-circle.ig {
            background-color: #e4405f;
        }

        /* Instagram Purple-Red */
        .contact-icon-circle.fb {
            background-color: #3b5998;
        }

        /* Facebook Blue */
        .info-grid-section {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 4px 15px rgba(255, 102, 0, 0.4);
            transition: all 0.3s ease;
            display: block;
            margin: 40px auto;
        }

        .btn-save-changes:hover {
            background-color: #e55c00;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 102, 0, 0.5);
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
            background-color: #bcbcbcb8;
            /* สีพื้นหลังแถบ */
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            padding: 40px;
            position: relative;
            top: -50px;
            margin-top: 100px;
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
            box-shadow: 0 0 5px rgba(255, 140, 0, 0.3);
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
            border-radius: 50%;
            border: 5px solid #ff8c00;
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
            margin-left: 8px;
            /* เพิ่มระยะห่างระหว่างข้อความกับไอคอน */
            /*color: #ff6600; /* สีไอคอนให้เข้ากับโทนส้ม */
            font-size: 1.2em;
            /* เพิ่มขนาดเล็กน้อยให้ดูเด่น */
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


    <!-- <div class="top-nav-container">

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
</div> -->
    <?php
    require_once("component/header.php")
        ?>

    <div class="main-container">

        <div class="tab-content">

<div class="edit-profile-card">
    <div class="section-header-icon">
        <i class="fas fa-edit" style="font-size: 25px;"></i>
        <h3 class="section-title" style="padding-left:10px;">แก้ไขข้อมูลโปรไฟล์</h3>
    </div>
    <hr>
    <form id="editProfileForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">
                <img src="<?= $row_student["student_image_profile"]; ?>"
                    onerror="this.src='../../../images/default.png'" alt="Profile Picture"
                    class="profile-img-preview">
                <div class="form-group">
                    <label for="image_name">รูปโปรไฟล์</label>
                    <input type="file" name="image_name" id="image_name" class="form-control-file"
                        accept="image/*">
                    <small class="text-muted">เลือกรูปภาพเพื่อเปลี่ยนรูปโปรไฟล์ (นามสกุลไฟล์ที่รองรับ:
                        .jpeg, .jpg, .png, .gif)</small>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="firstname">ชื่อ</label>
                    <input type="text" id="firstname" name="firstname" class="form-control-edit"
                        value="<?= $row_student["student_firstname_th"]; ?>" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="lastname">นามสกุล</label>
                    <input type="text" id="lastname" name="lastname" class="form-control-edit"
                        value="<?= $row_student["student_lastname_th"]; ?>" disabled>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea name="bio" id="bio" class="form-control-edit"
                        rows="3"><?= $row_student["student_bio"]; ?></textarea>
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
                    <input type="text" name="mobile" id="mobile" class="form-control-edit"
                        value="<?= $row_student['student_mobile']; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" name="email" id="email" class="form-control-edit"
                        value="<?= $row_student['student_email']; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="line">Line</label>
                    <input type="text" name="line" id="line" class="form-control-edit"
                        value="<?= $row_student['student_line']; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ig">ig</label>
                    <input type="text" name="ig" id="ig" class="form-control-edit"
                        value="<?= $row_student['student_ig']; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="facebook">facebook</label>
                    <input type="text" name="facebook" id="facebook" class="form-control-edit"
                        value="<?= $row_student['student_facebook']; ?>">
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
                    <input type="text" name="hobby" id="hobby" class="form-control-edit"
                        value="<?= $row_student["student_hobby"]; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="favorite_music">ดนตรีที่ชอบ</label>
                    <input type="text" name="favorite_music" id="favorite_music" class="form-control-edit"
                        value="<?= $row_student["student_music"]; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="favorite_movie">หนังที่ชอบ</label>
                    <input type="text" name="favorite_movie" id="favorite_movie" class="form-control-edit"
                        value="<?= $row_student["student_movie"]; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="goal">เป้าหมาย</label>
                    <input type="text" name="goal" id="goal" class="form-control-edit"
                        value="<?= $row_student["student_goal"]; ?>">
                </div>
            </div>
        </div>
        <div class="text-center">
            <button type="button" name="submit_edit_profile" class="btn-save-changes"
                id="saveBtn">บันทึกการเปลี่ยนแปลง</button>
        </div>
    </form>
</div>
            











        </div>
    </div>
</body>

</html>
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzxc7D9o3CcmSyLWVo6h4rCxS0yL_wB2k&libraries=places"></script>

<script language="javascript">
    function buildHealth() {
        $(".loader").addClass("active");
        $(".health_area").load("hrm/library/template/health.php");
    }
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader2 = new FileReader();
            $('.preview-uploads img.avatar-uploads').css("display", "block");
            $('.preview-uploads span').css("display", "none");
            reader2.onload = function (e) {
                $('.preview-uploads img.avatar-uploads').attr('src', e.target.result);
            };
            reader2.readAsDataURL(input.files[0]);
            $(".upload-img").attr("disabled", false);
        } else {
            $('.preview-uploads img.avatar-uploads').css("display", "none");
            $('.preview-uploads span').css("display", "block");
            $(".upload-img").attr("disabled", true);
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
function thaidate($value)
{
    if (empty($value)) {
        return "";
    }
    return substr($value, 8, 2) . "/" . substr($value, 5, 2) . "/" . substr($value, 0, 4);
}
function find_birth($birthday, $today)
{
    list($byear, $bmonth, $bday) = explode("-", $birthday);
    list($tyear, $tmonth, $tday) = explode("-", $today);
    $mbirthday = mktime(0, 0, 0, $bmonth, $bday, $byear);
    $mnow = mktime(0, 0, 0, $tmonth, $tday, $tyear);
    $mage = ($mnow - $mbirthday);
    $u_y = date("Y", $mage) - 1970;
    $u_m = date("m", $mage) - 1;
    $u_d = date("d", $mage) - 1;
    return "$u_y   ปี    $u_m เดือน      $u_d  วัน";
}
?>