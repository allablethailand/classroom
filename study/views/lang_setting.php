<?php
// บรรทัดแรกสุดของไฟล์
session_start();
// โค้ดส่วนอื่นๆ ของหน้าจะเริ่มที่นี่
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Language Setting • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/menu.css?v=<?php echo time(); ?>">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    
    <style>
        .gap-row {
            display: flex;
            flex-wrap: wrap;    /* allow flex items to wrap into multiple rows */
            gap: 20px 0;        /* 20px vertical gap (row-gap), 0 horizontal gap (column-gap) */
            flex-direction: column;  /* stack items vertically */
            flex-wrap: wrap;         /* allow wrapping */          /* gap between rows */
            overflow-y: auto; 
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            align-items: center; 
            cursor: pointer;
        }

        .action-card label {
            display: flex;
            align-items: center;
            gap: 6px; /* spacing between radio, flag, and text */
            cursor: pointer;
        }

        .action-card input[type="radio"] {
            margin: 0; /* remove default margin */
        }

        .action-card {
            margin-bottom: 1rem; /* adjust the spacing as you like */
        }

        .action-card:not(:last-child) {
            margin-bottom: 1rem;
        }

        input[type="radio"] {
  appearance: none;
  -webkit-appearance: none; /* for Safari */
  width: 20px;
  height: 20px;
  border: 2px solid #ccc;
  border-radius: 50%;
  background-color: transparent;
  cursor: pointer;
  transition: border-color 0.2s, background-color 0.2s;
}


        input[type="radio"]:checked {
            border-color: #F68D26; /* your custom border color */
            background-color: #F68D26; /* your custom fill color */
            box-shadow: inset 0 0 0 4px #fff; /* optional: white inner circle */
        }
        

    </style>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/menu.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
    <?php require_once 'component/header.php'; ?>
    <div class="main-content">
        <div class="container-fluid" style="margin: 0 1rem;">
            <div class="row" style="margin-bottom:1rem;">
                <p class="menu-title" data-lang="lang_sett"></p>
            </div>
            <div class="action-card" >
                <label>
                    <input type="radio" name="language" value="TH">
                    <img src="https://flagcdn.com/w20/th.png" alt="[translate:ภาษาไทย] flag" style="height: 14px; margin-right: 6px;">
                    <span data-lang="thai">ภาษาไทย</span>
                </label>
            </div>
            <div class="action-card">
                <label>
                    <input type="radio" name="language" value="EN" checked>
                    <img src="https://flagcdn.com/w20/us.png" alt="English flag" style="height: 14px; margin-right: 6px;">
                    <span data-lang="eng">English</span>
                </label>
            </div>
            <div class="action-card">
                <label>
                    <input type="radio" name="language" value="CN">
                    <img src="https://flagcdn.com/w20/cn.png" alt="[translate:中文] flag" style="height: 14px; margin-right: 6px;">
                    <span data-lang="chinese">ภาษาจีน</span>
                </label>
            </div>
            <div class="action-card">
                <label>
                    <input type="radio" name="language" value="JP">
                    <img src="https://flagcdn.com/w20/jp.png" alt="[translate:日本語] flag" style="height: 14px; margin-right: 6px;">
                    <span data-lang="japanese">日本語</span>
                </label>
            </div>
            <div class="action-card">
                <label>
                    <input type="radio" name="language" value="KR">
                    <img src="https://flagcdn.com/w20/kr.png" alt="[translate:한국어] flag" style="height: 14px; margin-right: 6px;">
                    <span data-lang="korea">한국어</span>
                </label>
            </div>
        </div>
    </div>
    <?php require_once 'component/footer.php'; ?>
</body>
</html>