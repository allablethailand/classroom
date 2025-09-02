<?php
    session_start();
    require_once("../../lib/connect_sqli.php");
    // require_once("../../component/header.php");
    include_once("../../login_history.php");
    global $mysqli;

    // Query to fetch all students from the classroom_student table
    // ดึงข้อมูลที่จำเป็นจากตาราง classroom_student
    $query = "SELECT student_id, student_firstname_th, student_lastname_th, student_nickname_th, student_image_profile, student_bio FROM classroom_student ORDER BY student_id ASC";
    $result = $mysqli->query($query);
    
    // Check if the query was successful
    if ($result) {
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $num_rows = count($students);
    } else {
        $num_rows = 0;
        $students = [];
        // Optional: Add error handling here
        // die("Query failed: " . $mysqli->error);
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Kanit', sans-serif;
        color: #333;
    }
    .main-container {
        max-width: 960px;
        margin: 0 auto;
        padding: 20px;
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
        box-shadow: 0 0 10px rgba(255,140,0,0.2);
        outline: none;
    }
    .student-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .student-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        padding: 20px;
        display: flex;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        text-decoration: none;
        color: inherit;
    }
    .student-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 20px;
        flex-shrink: 0;
        border: 3px solid #ff8c00;
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
        margin: 0;
        color: #2c3e50;
    }
    .student-details {
        font-size: 0.9em;
        color: #7f8c8d;
        margin: 5px 0 0;
    }
    .student-details i {
        margin-right: 5px;
        color: #ff8c00;
    }
</style>
</head>
<body>
     <?php
    require_once ("component/header.php")
    ?>
    <div class="main-container">
        <div class="header-bar">
            <a href="group"></a>
            <h2>รายชื่อนักเรียน</h2>
            <div style="width: 20px;"></div>
        </div>

        <div class="search-container">
            <input type="text" id="studentSearch" onkeyup="searchStudents()" placeholder="ค้นหานักเรียน...">
        </div>
        
        <div class="student-list">
            <?php
                if ($num_rows > 0) {
                    foreach ($students as $row) {
                        $student_pic = !empty($row['student_image_profile']) ? $row['student_image_profile'] : 'https://i.stack.imgur.com/34AD4.jpg';
            ?>
            <a href="studentinfo.php?id=<?= htmlspecialchars($row['student_id']); ?>" class="student-card">
                <div class="student-avatar">
                    <img src="<?= htmlspecialchars($student_pic); ?>" alt="Student Avatar" onerror="this.src='https://i.stack.imgur.com/34AD4.jpg'">
                </div>
                <div class="student-info">
                    <h4 class="student-name">
                        <?= htmlspecialchars($row['student_firstname_th'] . " " . $row['student_lastname_th']); ?>
                    </h4>
                    <p class="student-details">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($row['student_nickname_th']); ?>
                    </p>
                    <p class="student-details">
                        <i class="fas fa-briefcase"></i> <?= htmlspecialchars($row['student_bio']); ?>
                    </p>
                </div>
            </a>
            <?php
                    }
                } else {
                    echo "<p style='text-align: center; color: #888;'>ไม่พบข้อมูลนักเรียน</p>";
                }
            ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script>
        function searchStudents() {
            var input, filter, cards, a, i, txtValue;
            input = document.getElementById('studentSearch');
            filter = input.value.toUpperCase();
            cards = document.getElementsByClassName('student-card');
            for (i = 0; i < cards.length; i++) {
                a = cards[i];
                txtValue = a.querySelector('.student-name').textContent || a.querySelector('.student-name').innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    cards[i].style.display = "";
                } else {
                    cards[i].style.display = "none";
                }
            }
        }
    </script>
    <?php
    require_once ("component/footer.php")
    ?>
</body>
</html>