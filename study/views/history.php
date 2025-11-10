<?php

// บรรทัดแรกสุดของไฟล์
// session_start();

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
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';
require_once $base_include . '/classroom/study/actions/student_func.php';   

$student_id = getStudentId();
$alumni_list = getStudentClassroomList($student_id);

// var_dump($alumni_list);


?>


<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>History • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <!-- <link rel="stylesheet" href="/dist/css/select2.min.css">
    <link rel="stylesheet" href="/dist/css/select2-bootstrap.css"> -->
    <link rel="stylesheet" href="/dist/css/jquery-ui.css">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/history.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/daterangepicker/v2/daterangepicker.css">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/dist/moment/moment.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script> -->
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/js/moment-with-locales.js"></script>
    <script src="/dist/daterangepicker/v2/daterangepicker.js"></script>
    <script src="/classroom/study/js/history.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>" type="text/javascript"></script>

    
    <style>
        /* body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        } */

        /* .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            position: relative;
            padding-bottom: 70px;
        } */
        /* .daterangepicker {
            z-index: 1600 !important;
        } */
        .top-notch {
            height: 30px;
            background: white;
            position: relative;
        }

        .notch {
            width: 150px;
            height: 25px;
            background: #f5f5f5;
            margin: 0 auto;
            border-radius: 0 0 20px 20px;
        }

        /* .header {
            padding: 20px;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        } */

        .notification-icon {
            font-size: 20px;
            color: #333;
        }

        .tabs-section {
            padding: 15px 20px 10px;
            background: #fff;
            margin-inline: 10px;
            border-radius: 15px;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
        }

        .main-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .main-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: all 0.3s;
        }

        .main-tab.active {
            background: #F68D26;
            color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .filter-tabs::-webkit-scrollbar {
            display: none;
        }

        .filter-tab,
        .filter-tab-present, 
        .filter-tab-absent, 
        .filter-tab-early, 
        .filter-tab-late {
            padding: 8px 16px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.3s;
        }


        .filter-tab.active {
            background: #f57c00;
            color: white;
            border-color: #fff3e0;
        }

        .filter-tab-present.active {
            background: #e3f2fd;
            color: #1976d2;
            border-color: #1976d2;
        }

        .filter-tab-present.active .badge-count {
            display: inline-block;
            background: #c5cbf9;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .filter-tab-absent.active {
            background: #ffbfc9;
            color: #E13252;
            border-color: #E13252;
        }

        .filter-tab-absent.active .badge-count {
            display: inline-block;
            background: #f9cfd5;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }

         .filter-tab-early.active {
            background: #c6f4eb;
            color: #009578;
            border-color: #009578;
        }

        .filter-tab-early.active .badge-count {
            display: inline-block;
            background: #82f29c;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }
        .filter-tab-late.active {
            background: #fff3e0;
            color: #f57c00;
            border-color: #f57c00;
        }

        .filter-tab-late.active .badge-count {
            display: inline-block;
            background: #ffe4b7;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .badge-count {
            display: inline-block;
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .filter-tab.active .badge-count {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .orders-container {
            padding: 15px 20px;
        }

        .order-card {
            display: flex;
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .order-status {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-current {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-waiting {
            background: #fff3e0;
            color: #f57c00;
        }

        .order-icon {
            /* width: 45px;
            height: 45px;
            background: #f5f5f5; */
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #666;
            /* float: left; */
            margin-right: 20px;
        }

        .order-info {
            overflow: hidden;
        }

        .order-title {
            font-weight: 600;
            font-size: 16px;
            margin: 0 0 4px 0;
            color: #333;
        }

        .order-number {
            color: #999;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .order-details {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #666;
            flex-wrap: wrap;
        }

        .order-detail-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .order-detail-item i {
            font-size: 12px;
            color: #999;
        }
/* 
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            max-width: 480px;
            width: 100%;
            background: white;
            border-top: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        } */

        .nav-item {
            flex: 1;
            text-align: center;
            text-decoration: none;
            color: #999;
            transition: all 0.3s;
        }

        .nav-item.active {
            color: #4a5cff;
        }

        .nav-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 4px;
        }

        .nav-item span {
            font-size: 12px;
            display: block;
        }

        .list-indicator {
            position: absolute;
            left: 20px;
            top: 390px;
            color: #999;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .list-indicator::before {
            content: '';
            width: 2px;
            height: 60px;
            background: #e0e0e0;
            display: block;
        }

        @media (max-width: 480px) {
            .mobile-container {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php require_once 'component/header.php'; ?>

    <div class="main-content">
        <!-- Tabs Section -->
         <div class="container-fluid">
             <h1 class="heading-1" data-lang="classhistory">ประวัติการเข้าเรียน</h1>
             <div class="divider-1">
                 <span></span>
             </div>
         </div>
            <div class="tabs-section">
                
                <div class="" style="display: flex; gap: 10px;">
                     <!-- <button class="navbox-button" id="searchBtn">
                        <svg class="icon-svg" viewBox="0 0 45 45" style="width:150px; height:150px;">
                            <g>
                                <path d="M22.2356 14.7298C25.828 14.7301 28.7405 17.6431 28.7405 21.2356C28.7403 22.616 28.3079 23.8947 27.5745 24.9475L29.9329 27.3069C30.382 27.756 30.382 28.4838 29.9329 28.9329C29.4838 29.382 28.756 29.382 28.3069 28.9329L25.9475 26.5745C24.8946 27.3079 23.616 27.7404 22.2356 27.7405C18.6431 27.7405 15.7301 24.828 15.7297 21.2356C15.7297 17.6429 18.6429 14.7298 22.2356 14.7298ZM22.2356 17.0305C19.9131 17.0305 18.0305 18.9132 18.0305 21.2356C18.0309 23.5578 19.9134 25.4407 22.2356 25.4407C24.5575 25.4403 26.4403 23.5576 26.4407 21.2356C26.4407 18.9134 24.5578 17.0309 22.2356 17.0305Z" fill="#26273A" />
                            </g>
                        </svg>
                    </button> -->
                    <div style="display: flex; align-items: center;">
                        <p data-lang="program">หลักสูตร: </p>
                    </div>
                    <div class="account-picker">
                       <select class="form-control" name="classroom_id" style="border: 1.8px solid #ccc; border-radius: 10px">
                           <?php foreach($alumni_list as $alumni): ?>
                                <option value="<?php echo htmlspecialchars($alumni['classroom_id']); ?>">
                                    <?php echo htmlspecialchars($alumni['classroom_name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <!-- <option value="">
                                ทั้งหมด
                            </option> -->
                        </select>
                    </div>
                </div>
                <div class="main-tabs">
                    <button class="main-tab active" data-lang="summarize" onclick="switchMainTab(this, 'summarize')">Summarize</button>
                    <button class="main-tab" data-lang="online" onclick="switchMainTab(this, 'ongoing')">Online</button>
                    <button class="main-tab" data-lang="onsite" onclick="switchMainTab(this, 'history')">Onsite</button>
                    <button class="navbox-button" id="filterBtn" style="border: 1px solid #ccc" data-toggle="modal" data-target="#bottomModal">
                        <svg class="icon-svg" viewBox="0 0 14 13">
                            <g>
                                <path d="M5.55726 8.90859H1.03659C0.464442 8.90859 -8.3819e-08 9.36489 -8.3819e-08 9.92701C-8.3819e-08 10.4884 0.464442 10.9454 1.03659 10.9454H5.55726C6.12941 10.9454 6.59385 10.4884 6.59385 9.92701C6.59385 9.36489 6.12941 8.90859 5.55726 8.90859Z" ," fill="#26273A" opacity="0.4" />
                                <path d="M13.7501 2.32275C13.7501 1.76138 13.2856 1.30509 12.7142 1.30509H8.19357C7.62142 1.30509 7.15698 1.76138 7.15698 2.32275C7.15698 2.88486 7.62142 3.34116 8.19357 3.34116H12.7142C13.2856 3.34116 13.7501 2.88486 13.7501 2.32275Z" fill="#26273A" opacity="0.4" />
                                <path d="M4.72845 2.32276C4.72845 3.60609 3.67047 4.64627 2.36422 4.64627C1.05874 4.64627 0 3.60609 0 2.32276C0 1.04018 1.05874 0 2.36422 0C3.67047 0 4.72845 1.04018 4.72845 2.32276Z" fill="#26273A" />
                                <path d="M13.75 9.89945C13.75 11.182 12.692 12.2222 11.3858 12.2222C10.0803 12.2222 9.02155 11.182 9.02155 9.89945C9.02155 8.61612 10.0803 7.57594 11.3858 7.57594C12.692 7.57594 13.75 8.61612 13.75 9.89945Z" fill="#26273A" />
                            </g>
                        </svg>
                    </button>
           
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab active" data-lang="alldata" onclick="switchFilterTab(this, 'all')">
                        All
                    </button>
                    <button class="filter-tab-present"  onclick="switchFilterTab(this, 'present')">
                        <p data-lang="present" style="display: inline;">Present</p> <span class="badge-count">0</span>
                    </button>
                    <button class="filter-tab-absent" onclick="switchFilterTab(this, 'absent')">
                         <p data-lang="absent" style="display: inline;">Absent </p> <span class="badge-count">0</span>
                    </button>
                    <button class="filter-tab-early" onclick="switchFilterTab(this, 'early')">
                       <p data-lang="early" style="display: inline;">Early </p> <span class="badge-count">0</span>
                    </button>
                    <button class="filter-tab-late" onclick="switchFilterTab(this, 'late')">
                        <p data-lang="late" style="display: inline;">Late </p> <span class="badge-count">0</span>
                    </button>
                </div>
            </div>
            <div class="mobile-container">
                <div class="orders-container" id="ordersContainer">
                    
                </div>
            </div>

    </div>
    <div id="bottomModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" style="position: fixed; bottom: 0; margin: 0; width: 100%; max-width: 100%;">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title" data-lang="filters">Filters</h4>
                                </div>
                                <div class="modal-body">
                                    <h4 class="modal-title" data-lang="startdate">Start Date:</h4>
                                    <div class="input-group date" data-provide="datepicker">
                                        <input type="text" id="start_date" class="form-control" style="margin-top:0px !important; margin-bottom:0px !important;">
                                        <div class="input-group-addon">
                                            <span><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                     <h4 class="modal-title" style="margin-top: 10px;" data-lang="enddate">End Date:</h4>
                                    <div class="input-group date" data-provide="datepicker">
                                        <input type="text" id="end_date" class="form-control" style="margin-top:0px !important; margin-bottom:0px !important;">
                                        <div class="input-group-addon">
                                            <span><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                    <!-- Your modal content here -->
                                    <div class="daterange-filter">
                                        <div class="" style="margin-top: 10px;">
                                            <p data-lang="selectrange">Select Range</p>
                                        </div>
                                        <div class="" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                            <div class="dropdown">
                                                <div class="filter-tag active" id="filter-date-today" data-filter="today">
                                                    <span class="filter-text" data-lang="today">Today</span>
                                                    <span class="remove-btn">×</span>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <div class="filter-tag" id="filter-date-lastmonth" data-filter="lastmonth">
                                                    <span class="filter-text" data-lang="lastmonth">Last month</span>
                                                    <span class="remove-btn">×</span>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <div class="filter-tag" id="filter-date-lastweek" data-filter="lastweek">
                                                    <span class="filter-text" data-lang="lastweek">Last week</span>
                                                    <span class="remove-btn">×</span>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <div class="filter-tag" id="filter-date-thismonth" data-filter="thismonth">
                                                    <span class="filter-text" data-lang="thismonth">This month</span>
                                                    <span class="remove-btn">×</span>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <div class="filter-tag" id="filter-date-thisweek" data-filter="thisweek">
                                                    <span class="filter-text" data-lang="thisweek">This week</span>
                                                    <span class="remove-btn">×</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" style="margin-inline: 0.5rem;">
                                        <button id="searchDateRange" class="btn btn-primary btn-block" data-lang="searchresults">Search Result</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

        
  

    <?php require_once 'component/footer.php'; ?>
</body>

</html>