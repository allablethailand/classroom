
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
    $page_data = $check_url[1] ;

    $classroom_id = base64_decode(strtr($page_data, '-_', '+/') . str_repeat('=', strlen($page_data) % 4));
    $classroom_id = rtrim($classroom_id, "\0");

    $classrooms = select_data(
        "template.classroom_poster",
        "classroom_template template",
        "where template.classroom_id = '{$classroom_id}'");
    $classroom = $classrooms[0];
    $classroom_poster = $classroom['classroom_poster'] ? GetPublicUrl($classroom['classroom_poster']) : '';

?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Origami Academy • Gallery</title>
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/events/gallery/assets/css/style.css?v=<?php echo time(); ?>">
<script src="/dist/fontawesome-5.11.2/js/all.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js"></script>
<script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>"></script>
</head>
<body>
<input type="hidden" id="classroom_id" value="<?php echo $classroom_id; ?>">
<img src="<?php echo $classroom_poster ; ?>" class="watermark" alt="Logo">
<div class="top-bar">
    <div class="back-btn" onclick="closeGallery();">
        <i class="glyphicon glyphicon-chevron-left"></i>
    </div>
</div>
<header class="event-header">
    <div class="header-box">
        <img src="<?php echo $classroom_poster ; ?>" height="75">
        <img id="posterPreload" alt="">
        <h4 class="event-title"></h4>
        <p><i class="glyphicon glyphicon-map-marker"></i> <span class="event-location"></span></p>
        <p><i class="glyphicon glyphicon-calendar"></i> <span class="event-time"></span></p>
        <p style="margin-top: 15px;"><i class="fas fa-images"></i> <span class="images-counter"></span> photos</p>
    </div>
</header>
<div class="container-fluid gallery-wrapper">
    <div class="row" id="photo-grid"></div>
    <div id="loading-area" class="text-center">
        <div class="loader-orbit"></div>
    </div>
</div>
<div id="previewModal" class="modal-space">
    <div class="modal-controls">
        <button class="btn-rotate" onclick="rotateImage(-1)">
            <i class="glyphicon glyphicon-repeat" style="transform: scaleX(-1);"></i>
        </button>
        <button class="btn-rotate" onclick="rotateImage(1)">
            <i class="glyphicon glyphicon-repeat"></i>
        </button>
    </div>
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <div class="photo-counter"><span class="index-counter"></span> / <span class="images-counter"></span></div>
    <div class="nav-controls">
        <button class="nav-btn" onclick="navImage(-1, event)"><i class="glyphicon glyphicon-chevron-left"></i></button>
        <button class="nav-btn" onclick="navImage(1, event)"><i class="glyphicon glyphicon-chevron-right"></i></button>
    </div>
    <div class="modal-body-space">
        <div class="modal-loader" style="display:none;">
            <div class="loader-orbit"></div>
        </div>
        <img id="modalImg" src="" class="full-img" onclick="event.stopPropagation()">
        <div class="modal-controls-bottom" onclick="event.stopPropagation()">
            <a id="downloadBtn" href="" download class="btn-download">
                <i class="glyphicon glyphicon-download-alt"></i> DOWNLOAD
            </a>
            <p class="download-note"></p>
            <div class="ios-hint">If download doesn't start, long-press the button and select "Download Linked File"</div>
            <div id="thumbStrip" class="thumb-strip"></div>
        </div>
    </div>
</div>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/classroom/study/js/myphoto_album.js?v=<?php echo time(); ?>"></script>
</body>
</html>