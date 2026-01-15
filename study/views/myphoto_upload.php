
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
    DEFINE('base_path', $base_path);
    DEFINE('base_include', $base_include);
    require_once $base_include.'/lib/connect_sqli.php';
    $fsData = getBucketMaster();
    $filesystem_user = $fsData['fs_access_user'];
    $filesystem_pass = $fsData['fs_access_pass'];
    $filesystem_host = $fsData['fs_host'];
    $filesystem_path = $fsData['fs_access_path'];
    $filesystem_type = $fsData['fs_type'];
    $fs_id = $fsData['fs_id'];
    setBucket($fsData);

    $url = $_SERVER['REQUEST_URI'];
    $check_url = explode('?',$url);
    $page = $check_url[0];
    $classroom_data = $check_url[1] ;
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Origami Academy • Photo Upload</title>
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/events/gallery/assets/css/style.css?v=<?php echo time(); ?>">
<script src="/dist/fontawesome-5.11.2/js/all.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>"></script>

<style>
/* Additional styles for user upload UI */
.upload-section:hover {
    border-color: #667eea !important;
    background: #f0f4ff !important;
}

.photo-card {
    transition: all 0.3s ease;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-warning {
    background: #ffc107;
    color: #000;
}

.badge-danger {
    background: #dc3545;
    color: white;
}

.badge-primary {
    background: #007bff;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .upload-section {
        padding: 20px 15px !important;
    }
    
    .upload-section h4 {
        font-size: 16px;
    }
    
    .photo-card img {
        height: 120px !important;
    }
}
</style>
</head>
<body>
<input type="hidden" id="classroom_data" value="<?php echo $classroom_data; ?>">
<input type="hidden" id="classroom_id" value="">
<input type="hidden" id="student_id" value="">


<img src="" class="watermark" alt="Logo">
<div class="top-bar">
    <div class="back-btn" onclick="closeGallery();">
        <i class="glyphicon glyphicon-chevron-left"></i>
    </div>
</div>
<header class="event-header">
    <div class="header-box">
        <img src="" height="75">
        <img id="posterPreload" alt="" style="display:none;">
        <h4 class="classroom-title"></h4>
        <p><i class="glyphicon glyphicon-map-marker"></i> <span class="classroom-location"></span></p>
        <p><i class="glyphicon glyphicon-calendar"></i> <span class="classroom-time"></span></p>
        <p style="margin-top: 15px;">
            <i class="fas fa-images"></i> 
            <span class="images-counter">0</span> รูปภาพ
        </p>
    </div>
</header>

<!-- Content will be dynamically added by JavaScript -->

<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/classroom/study/js/myphoto_upload.js?v=<?php echo time(); ?>"></script>
</body>
</html>