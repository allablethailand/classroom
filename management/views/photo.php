<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Photo • ORIGAMI SYSTEM</title>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/daterangepicker/v2/daterangepicker.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/classroom/management/css/management.css?v=<?php echo time(); ?>">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/dist/js/jquery.dataTables.min.js"></script>
<script src="/dist/js/dataTables.bootstrap.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/dist/moment/moment.min.js"></script>
<script src="/dist/js/moment-with-locales.js"></script>
<script src="/dist/daterangepicker/v2/daterangepicker.js"></script>
<script src="/dist/js/jquery.redirect.js"></script>
<script src="/js/clipboard.min.js"></script>
<script src="/node_modules/clipboard/dist/clipboard.min.js"></script>
<script src="/classroom/management/js/photo.js?v=<?php echo time(); ?>"></script>
</head>
<body>
<input type="hidden" id="classroom_id" value="<?php echo $_POST['classroom_id'] ?>">
<input type="hidden" id="group_id" value="<?php echo $_POST['group_id'] ?>">
<?php require_once "../../include_header.php"; ?>
<div class="container-fluid">
    <div class="row">
        <div class="row-overflow">
            <a href=".group_tab" class="active get-group" data-page="photo" data-toggle="tab">
                <i class="fas fa-users"></i>
                <span lang="en"></span>
            </a>
        </div>
        <div class="group-info"></div>
        <div class="tab-content">	
            <div class="group_tab tab-pane fade in active">
                <div class="content-container" style="margin: 25px auto;"></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>