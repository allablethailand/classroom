<?php
session_start();
require_once("../../lib/connect_sqli.php");
global $mysqli;

// Get the selected group_id from the URL, default to 0 for all students
$selected_group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

// --- 1. Fetch all groups from the `classroom_group` table ---
// เพิ่ม group_color เข้ามาใน SELECT statement สำหรับการสร้างขอบสี
$groups_query = "SELECT group_id, group_name, group_logo, group_color FROM classroom_group WHERE status = 0 AND classroom_id = 2 ORDER BY group_id ASC";
$groups_result = $mysqli->query($groups_query);
$groups = [];
if ($groups_result) {
    $groups = $groups_result->fetch_all(MYSQLI_ASSOC);
}

// --- 2. Build the query to fetch students and their course name and GROUP NAME ---
// เพิ่ม group_color เข้ามาใน SELECT statement
// เพิ่ม group_logo เข้ามาใน SELECT statement
$query_parts = [
    "SELECT cs.student_id, cs.student_firstname_th, cs.student_lastname_th, cs.student_image_profile, cs.student_mobile, cs.student_email, cs.student_company, cs.student_position, ct.classroom_name, cg.group_name, cg.group_color, cg.group_logo",
    "FROM classroom_student cs",
    "INNER JOIN classroom_student_join csj ON cs.student_id = csj.student_id",
    "LEFT JOIN classroom_template ct ON csj.classroom_id = ct.classroom_id",
    "LEFT JOIN classroom_group cg ON csj.group_id = cg.group_id",
    "WHERE csj.classroom_id = 2 AND csj.status = 0 AND csj.approve_status = 1"
];

if ($selected_group_id > 0) {
    // Filter students by the selected group
    $query_parts[] = "AND csj.group_id = '{$selected_group_id}'";
}

// Combine the query parts and order the results
$query = implode(" ", $query_parts) . " ORDER BY cs.student_id ASC";

$result = $mysqli->query($query);

if ($result) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $num_rows = count($students);
} else {
    $num_rows = 0;
    $students = [];
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Student • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <!-- <link rel="stylesheet" href="/classroom/study/js/style.css?v=<?php echo time(); ?>"> -->
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>

    <style>
    /* body {
        background-color: #f0f2f5;
        font-family: 'Kanit', sans-serif;
        color: #333;
    } */

    .main-container {
        max-width: 960px;
        margin: 0 auto;
        padding: 20px;
        /* *** แก้ไขที่นี่: เพิ่ม padding-bottom เพื่อให้มีระยะห่างจาก footer และสามารถ Scroll ได้จนสุดรายการ *** */
        padding-bottom: 100px;
        margin-bottom: 0; /* ลบ margin-bottom เดิมออก */
    }

    .header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #ffc27c;
        color: #fff;
        padding: 15px 25px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
    }

    .header-bar h2 {
        font-size: 1.8em;
        font-weight: 700;
        margin: 0;
        color: #333;
    }

    .header-bar .fa-arrow-left {
        color: #333;
    }

    .search-container {
        margin-bottom: 20px;
    }

    .search-container input {
        width: 100%;
        padding: 12px 20px;
        border-radius: 25px;
        border: 1px solid #ddd;
        font-size: 1em;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .search-container input:focus {
        border-color: #ff8c00;
        box-shadow: 0 0 10px rgba(255, 140, 0, 0.2);
        outline: none;
    }

    /* New styles for the centered dropdown/modal */
    .group-dropdown-container {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .dropdown-button {
        background-color: #ff8c00;
        color: #fff;
        padding: 12px 20px;
        border-radius: 25px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background-color 0.2s ease, transform 0.2s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .dropdown-button:hover {
        background-color: #e67e22;
        transform: translateY(-2px);
    }

    .dropdown-content {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        min-width: 300px;
        max-width: 90%;
        padding: 25px;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        border-radius: 15px;
        border: 2px solid #ff8c00;
        animation: fadeIn 0.3s ease-out;
    }

    .dropdown-content.show {
        display: block;
    }

    .dropdown-header {
        font-size: 1.5em;
        font-weight: bold;
        text-align: center;
        margin-bottom: 15px;
        color: #ff8c00;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
    }

    .group-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .group-item-dropdown {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        text-decoration: none;
        color: #333;
        border-radius: 8px;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .group-item-dropdown:hover {
        background-color: #f1f1f1;
        transform: translateX(5px);
        text-decoration: none;
        color: #333;
    }

    .group-logo-container {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 15px;
        flex-shrink: 0;
        border: 2px solid transparent;
        transition: border-color 0.2s ease;
    }

    .group-logo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .group-item-dropdown i.fa-users {
        color: #7f8c8d;
        margin-right: 10px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translate(-50%, -60%);
        }

        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .modal-overlay.show {
        display: block;
    }

    .student-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        /* เพิ่ม margin-bottom เพื่อช่วยให้รายการสุดท้ายห่างจากขอบด้านล่างของ main-container */
        /* margin-bottom: 20px; */ 
    }

    .student-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        display: flex;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        padding-top: 50px;
    }

    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        text-decoration: none;
        color: inherit;
    }

    /* แก้ไข margin-top สำหรับรูปโปรไฟล์เพื่อชดเชย padding ด้านบน */
    .student-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 20px;
        flex-shrink: 0;
        border: 4px solid #ff8c00;
        margin-top: -15px; /* ปรับค่านี้เพื่อให้รูปโปรไฟล์ไม่เลื่อนต่ำเกินไป */
    }

    .student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-info {
        flex-grow: 1;
    }

    .student-name {
        font-size: 1.3em;
        font-weight: 700;
        margin: 0 0 5px 0;
        color: #2c3e50;
    }

    .student-details {
        font-size: 0.9em;
        color: #7f8c8d;
        margin: 2px 0 0;
        display: flex;
        align-items: center;
    }

    .student-details i {
        margin-right: 8px;
        min-width: 20px;
        text-align: center;
    }

    .student-details i.fa-graduation-cap {
        color: #3498db;
    }

    .student-details i.fa-building {
        color: #95a5a6;
    }

    .student-details i.fa-briefcase {
        color: #a0522d;
    }

    .student-details i.fa-phone {
        color: #27ae60;
    }

    .student-details i.fa-envelope {
        color: #e74c3c;
    }

    .highlight-text {
        font-size: 1.1em;
        font-weight: bold;
    }

    .student-id-display {
        font-size: 1.3em;
        font-weight: 700;
        color: #555;
        position: absolute;
        top: 15px;
        left: 20px;
        margin: 0;
        background-color: #fff;
        padding: 5px 10px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 10;
    }

    .group-name-container {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .group-name-container img {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
    }

    .company-name-label {
        color: #ff9900;
        font-size: 1.3em;
        font-weight: 700;
        margin-right: 8px;
    }

    .company-name {
        margin: 0; /* ลบ margin ที่ไม่จำเป็นออก */
    }

    .company-name-container {
        display: flex;
        align-items: baseline; /* จัดให้ข้อความ "บริษัท:" และชื่อบริษัทอยู่แนวเดียวกัน */
    }

    .company-name {
        font-size: 1.3em;
        font-weight: 700;
        margin: 0;
        color: #ff9900;
        /* ใช้ CSS เพื่อให้แสดงผลทั้งหมดในตอนแรก */
        max-width: none;
        white-space: normal;
        overflow: visible;
    }

    /* เพิ่ม Media Query สำหรับหน้าจอขนาดเล็ก (Mobile) */
    @media (max-width: 768px) {
        .company-name {
            max-width: 120px; /* จำกัดความกว้างสำหรับหน้าจอมือถือ */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    .company-name.expanded {
        max-width: none;
        white-space: normal;
    }
</style>

</head>

<body>
    <?php
    require_once("component/header.php")
    ?>

    <div class="main-container">

        <div class="modal-overlay" id="modalOverlay"></div>

        <div class="group-dropdown-container">
            <!-- <button class="dropdown-button" id="dropdownBtn">
                <i class="fas fa-book-open"></i>
                <span>เลือกกลุ่ม</span>
                <i class="fas fa-caret-down"></i>
            </button> -->
            <div class="dropdown-content" id="groupDropdown">
                <div class="dropdown-header">เลือกกลุ่ม</div>
                <div class="group-list">
                    <a href="?group_id=0" style="justify-content: center" class="group-item-dropdown">
                        <i class="fas fa-star" style="color: #ffee00ff; font-size: 2.5em; padding-right:.5em;"></i>
                        ทั้งหมด
                    </a>
                    <?php foreach ($groups as $group): ?>
                        <a href="?group_id=<?= htmlspecialchars($group['group_id']); ?>" class="group-item-dropdown">
                            <div class="group-logo-container"
                                style="border-color: <?= htmlspecialchars($group['group_color']); ?>;">
                                <?php
                                $group_logo = !empty($group['group_logo']) ? GetUrl($group['group_logo']) : '';
                                ?>
                                <img src="<?= htmlspecialchars($group_logo); ?>" alt="Group Logo" class="group-logo">
                            </div>
                            <?= htmlspecialchars($group['group_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="search-container">
            <input type="text" id="studentSearch" onkeyup="searchStudents()" placeholder="ค้นหานักเรียน...">
        </div>

        <div class="student-list">
            <?php
            if ($num_rows > 0) {
                foreach ($students as $row) {
                    $student_pic = !empty($row['student_image_profile']) ? GetUrl($row['student_image_profile']) : '../../../images/default.png';
                    $group_logo = !empty($row['group_logo']) ? GetUrl($row['group_logo']) : '../../../images/logo_academy.png';
                    // กำหนดสีขอบรูปภาพเริ่มต้นเป็นสีส้ม ถ้าไม่มี group_color
                    $border_color = !empty($row['group_color']) ? htmlspecialchars($row['group_color']) : '#ff8c00';
                    ?>
                    <a href="studentinfo?id=<?= htmlspecialchars($row['student_id']); ?>" class="student-card">
                        <p class="student-id-display">
                            ID: <?= htmlspecialchars($row['student_id']); ?>
                        </p>

                        <div class="student-avatar" style="border-color: <?= $border_color; ?>;">
                            <img src="<?= htmlspecialchars($student_pic); ?>" alt="Student Avatar"
                                onerror="this.src='../../../images/default.png'">
                        </div>

                        <div class="student-info">
                           
                            <div class="company-name-container" style="margin-bottom: 10px;">
                                    <span class="company-name-label">บริษัท:</span>
                                    <p class="company-name">
                                        <?= !empty($row['student_company']) ? htmlspecialchars($row['student_company']) : "-"; ?>
                                    </p>
                            </div>
                            
                            <h4 class="student-name">
                                <i class="fas fa-user-graduate" style="margin-right:10px"></i>  
                                <?= htmlspecialchars($row['student_firstname_th'] . " " . $row['student_lastname_th']); ?>
                            </h4>
                            <!-- <p class="student-details highlight-text">
                                <i class="fas fa-graduation-cap" style="margin-right:10px"></i>
                                <?= !empty($row['classroom_name']) ? htmlspecialchars($row['classroom_name']) : "-"; ?>
                            </p> -->
                            <p class="student-details highlight-text group-name-container">
                                <img src="<?= htmlspecialchars($group_logo); ?>" onerror="this.src='/images/logo_academy.png'" alt="Group Logo">
                                <?= !empty($row['group_name']) ? htmlspecialchars($row['group_name']) : "-"; ?>
                            </p>
                            <p class="student-details">
                                <i class="fas fa-briefcase" style="margin-right:10px"></i>
                                <?= !empty($row['student_position']) ? htmlspecialchars($row['student_position']) : "-"; ?>
                            </p>
                            <p class="student-details">
                                <i class="fas fa-phone" style="margin-right:10px"></i>
                                <?= !empty($row['student_mobile']) ? htmlspecialchars($row['student_mobile']) : "-"; ?>
                            </p>
                            <p class="student-details">
                                <i class="fas fa-envelope" style="margin-right:10px"></i>
                                <?= !empty($row['student_email']) ? htmlspecialchars($row['student_email']) : "-"; ?>
                            </p>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo "<p style='text-align: center; color: #888;'>ไม่พบข้อมูลนักเรียนในกลุ่มนี้</p>";
            }
            ?>
        </div>
    </div>
    <?php
    require_once("component/footer.php")
    ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script>
        function searchStudents() {
            var input, filter, cards, a, i, txtValue;
            input = document.getElementById('studentSearch');
            filter = input.value.toUpperCase();
            cards = document.getElementsByClassName('student-card');
            for (i = 0; i < cards.length; i++) {
                a = cards[i];
                // ค้นหาทั้งจากชื่อนักเรียนและ Student ID
                var studentName = a.querySelector('.student-name').textContent || a.querySelector('.student-name').innerText;
                var studentId = a.querySelector('.student-id-display').textContent || a.querySelector('.student-id-display').innerText;
                if (studentName.toUpperCase().indexOf(filter) > -1 || studentId.toUpperCase().indexOf(filter) > -1) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }

        // New JavaScript for modal functionality
        document.getElementById('dropdownBtn').addEventListener('click', function () {
            document.getElementById('groupDropdown').classList.add('show');
            document.getElementById('modalOverlay').classList.add('show');
        });

        // Close the modal when clicking outside of it
        document.getElementById('modalOverlay').addEventListener('click', function () {
            document.getElementById('groupDropdown').classList.remove('show');
            this.classList.remove('show');
        });

        // // ฟังก์ชันใหม่สำหรับสลับการแสดงชื่อบริษัท
        // function toggleCompany(event, element) {
        //     // หยุด event bubbling เพื่อไม่ให้คลิกแล้วไปหน้า studentinfo
        //     event.preventDefault();
        //     event.stopPropagation();
            
        //     const companyNameElement = element.querySelector('.company-name');
        //     companyNameElement.classList.toggle('expanded');
        // }
    </script>
</body>

</html>