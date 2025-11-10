<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Setup • ORIGAMI SYSTEM</title>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/daterangepicker/v2/daterangepicker.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/classroom/setup/css/setup.css?v=<?php echo time(); ?>">
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
<script src="/classroom/setup/js/setup.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/setup/js/position.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/setup/js/payment.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>
<body>
<?php require_once "../../include_header.php"; ?>
<div class="container-fluid">
    <div class="row">
        <div class="row-overflow">
            <a href=".setup_tab" class="active get-setup" data-page="position" data-toggle="tab">
                <i class="fas fa-briefcase"></i>
                <span lang="en">Position</span>
            </a>
            <a href=".setup_tab" class= get-setup" data-page="payment" data-toggle="tab">
                <i class="fas fa-money-check"></i>
                <span lang="en">Payment</span>
            </a>
        </div>
        <div class="tab-content">	
            <div class="setup_tab tab-pane fade in active">
                <div class="content-container" style="margin: 25px auto;"></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>