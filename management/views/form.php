<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Academy • ORIGAMI SYSTEM</title>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/daterangepicker/v2/daterangepicker.css">
<link rel="stylesheet" href="/dist/dropify/dist/css/dropify.min.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/css/jquery-clockpicker.min.css">
<link rel="stylesheet" href="/dist/editor/css/froala_editor.min.css">
<link rel="stylesheet" href="/dist/editor/css/froala_style.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" type="text/css" href="/dist/summernote/summernote.css">
<link rel="stylesheet" href="/dist/vue/loading.min.css">
<link rel="stylesheet" href="/dist/element-ui/css/lib/theme-chalk/index.css">
<link rel="stylesheet" href="/dist/element-ui/css/lib/theme-chalk/display.css">
<link rel="stylesheet" href="/classroom/management/css/form.css?v=<?php echo time(); ?>">
<link href="/classroom/css/classroom-component.css?v=<?php echo time(); ?>" rel="stylesheet">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/dist/js/table-to-excel/tableToExcel.js" type="text/javascript"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/dist/js/jquery.dataTables.min.js"></script>
<script src="/dist/js/dataTables.bootstrap.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/dist/lodash/lodash.js" type="text/javascript"></script>
<script src="/dist/moment/moment.min.js" type="text/javascript"></script>
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
<script src="/js/clipboard.min.js"></script>
<script src="/node_modules/clipboard/dist/clipboard.min.js"></script>
<script src="/classroom/management/js/detail.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/course.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/group.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/registration.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/consent.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/student.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/teacher.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/email.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<?php
    $dev_host = array('dev.origami.life','origami.local','origami-dev.net','localhost','video.origami.life');
    if(in_array($_SERVER['HTTP_HOST'],$dev_host)){
        echo "<script src='/dist/vue/dev.js'></script>".PHP_EOL;
    } else {
        echo "<script src='/dist/vue/prod.js'></script>".PHP_EOL;
    }
?>
<script src="/dist/element-ui/js/lib/index.js"></script>
<script src="/dist/element-ui/js/lib/umd/locale/en.js"></script>
<script src="/dist/element-ui/js/lib/umd/locale/th.js"></script>
<script src="/dist/axios/axios.min.js" type="text/javascript"></script>
<script src="/js/origami-component.js"></script>
<script src="/dist/summernote/summernote.js" type="text/javascript"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<script src="/dist/js/jquery.fileDownload.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-tagsinput@0.7.1/dist/bootstrap-tagsinput.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-tagsinput@0.7.1/dist/bootstrap-tagsinput.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/jquery-clockpicker.min.js" integrity="sha512-x0qixPCOQbS3xAQw8BL9qjhAh185N7JSw39hzE/ff71BXg7P1fkynTqcLYMlNmwRDtgdoYgURIvos+NJ6g0rNg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/clockpicker/0.0.7/bootstrap-clockpicker.css" integrity="sha512-BB0bszal4NXOgRP9MYCyVA0NNK2k1Rhr+8klY17rj4OhwTmqdPUQibKUDeHesYtXl7Ma2+tqC6c7FzYuHhw94g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php require_once "../../include_header.php"; ?>
<input type="hidden" id="classroom_id" value="<?php echo ($_POST['classroom_id']) ? $_POST['classroom_id'] : ''; ?>">
<input type="hidden" id="type_management" value="<?php echo ($_GET['type']) ? $_GET['type'] : ''; ?>">
<input type="hidden" id="id_mangement" value="<?php echo ($_GET['id']) ? $_GET['id'] : ''; ?>">
    <div id="form-container" style="padding: 4em;">
    </div>
    <script src="/classroom/management/js/form.js?v=<?php echo time(); ?>"></script>
    <!-- <script>
        // You can use a global variable to pass data from PHP to JS
        var formType = $('#type_management').val();
        var formId = $('#id_mangement').val();
        
        // This function will be defined in form.js
        $(document).ready(function() {
            console.log(formType,formId);
            
            initForm(formType, formId);
        });
    </script> -->
</body>
</html>