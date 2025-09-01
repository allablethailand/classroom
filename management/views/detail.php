<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Classroom • ORIGAMI SYSTEM</title>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/daterangepicker/v2/daterangepicker.css">
<link rel="stylesheet" href="/dist/dropify/dist/css/dropify.min.css">
<link rel="stylesheet" href="/dist/fancybox/source/jquery.fancybox.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/css/jquery-clockpicker.min.css">
<link rel="stylesheet" href="/dist/editor/css/froala_editor.min.css">
<link rel="stylesheet" href="/dist/editor/css/froala_style.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/classroom/management/css/detail.css?v=<?php echo time(); ?>">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/dist/js/jquery.dataTables.min.js"></script>
<script src="/dist/js/dataTables.bootstrap.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/dist/moment/moment.min.js"></script>
<script src="/dist/js/moment-with-locales.js"></script>
<script src="/dist/dropify/dist/js/dropify.min.js"></script>
<script src="/dist/fancybox/source/jquery.fancybox.js"></script>
<script src="/dist/js/jquery-ui.min.js"></script>
<script src="/dist/js/jquery-clockpicker.min.js" type="text/javascript"></script>
<script src="/dist/daterangepicker/v2/daterangepicker.js"></script>
<script src="/dist/editor/js/froala_editor.min.js"></script>
<script src="/dist/editor/js/plugins/tables.min.js"></script>
<script src="/dist/editor/js/plugins/urls.min.js"></script>
<script src="/dist/editor/js/plugins/lists.min.js"></script>
<script src="/dist/editor/js/plugins/colors.min.js"></script>
<script src="/dist/editor/js/plugins/font_family.min.js"></script>
<script src="/dist/editor/js/plugins/font_size.min.js"></script>
<script src="/dist/editor/js/plugins/block_styles.min.js"></script>
<script src="/dist/editor/js/plugins/media_manager.min.js"></script>
<script src="/dist/editor/js/plugins/video.min.js"></script>
<script src="/dist/editor/js/plugins/char_counter.min.js"></script>
<script src="/dist/editor/js/plugins/entities.min.js"></script>
<script src="/dist/editor/js/plugins/urls.min.js"></script>
<script src="/classroom/management/js/detail.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/course.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/group.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/evaluate.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/student.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>
<body>
<?php require_once "../../include_header.php"; ?>
<input type="hidden" id="classroom_id" value="<?php echo ($_POST['classroom_id']) ? $_POST['classroom_id'] : ''; ?>">
<div class="container-fluid">
    <div class="row">
        <div class="row-overflow">
            <a href=".management_tab" class="active get-management" data-page="management" data-toggle="tab">
                <i class="fas fa-chalkboard-teacher"></i>
                <span lang="en">Classroom</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="course" data-toggle="tab">
                <i class="fas fa-book"></i>
                <span lang="en">Course Management</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="group" data-toggle="tab">
                <i class="fas fa-cubes"></i>
                <span lang="en">Group</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="student" data-toggle="tab">
                <i class="fas fa-question"></i>
                <span lang="en">Evaluate</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="student" data-toggle="tab">
                <i class="fas fa-users"></i>
                <span lang="en">Student</span>
            </a>
        </div>
        <div class="tab-content">	
            <div class="management_tab tab-pane fade in active">
                <div class="content-container" style="margin: 25px auto;"></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>