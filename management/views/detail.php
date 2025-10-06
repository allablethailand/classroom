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
<link rel="stylesheet" href="/dist/fancybox/source/jquery.fancybox.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/dist/css/jquery-clockpicker.min.css">
<link rel="stylesheet" href="/dist/editor/css/froala_editor.min.css">
<link rel="stylesheet" href="/dist/editor/css/froala_style.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" type="text/css" href="/dist/summernote/summernote.css">
<link rel="stylesheet" href="/dist/vue/loading.min.css">
<link rel="stylesheet" href="/dist/element-ui/css/lib/theme-chalk/index.css">
<link rel="stylesheet" href="/dist/element-ui/css/lib/theme-chalk/display.css">
<link rel="stylesheet" href="/classroom/management/css/management.css?v=<?php echo time(); ?>">
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
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="/classroom/management/js/detail.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/course.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/group.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/channel.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/registration.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/consent.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/student.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/teacher.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/email.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/pricing.js?v=<?php echo time(); ?>" type="text/javascript"></script>
<script src="/classroom/management/js/message.js?v=<?php echo time(); ?>" type="text/javascript"></script>
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
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
</head>
<body>
<?php require_once "../../include_header.php"; ?>
<input type="hidden" id="classroom_id" value="<?php echo ($_POST['classroom_id']) ? $_POST['classroom_id'] : ''; ?>">
<div class="container-fluid">
    <div class="row">
        <div class="row-overflow">
            <a href=".management_tab" class="active get-management" data-page="management" data-toggle="tab">
                <i class="fas fa-chalkboard-teacher"></i>
                <span lang="en">Academy</span>
            </a>
            <a href=".classroom_form_tab"  class="get-management edit-mode" data-page="form" data-toggle="tab">
                <i class="fab fa-wpforms"></i> <span lang="en">Register Form</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="consent" data-toggle="tab">
                <i class="fas fa-user-shield"></i>
                <span lang="en">Consent</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="course" data-toggle="tab">
                <i class="fas fa-book"></i>
                <span lang="en">Course Management</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="group" data-toggle="tab">
                <i class="fas fa-cubes"></i>
                <span lang="en">Group</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="channel" data-toggle="tab">
                <i class="fas fa-cubes"></i>
                <span lang="en">Channel</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="pricing" data-toggle="tab">
                <i class="fas fa-dollar-sign"></i>
                <span lang="en">Pricing</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="teacher" data-toggle="tab">
                <i class="fas fa-chalkboard-teacher"></i>
                <span lang="en">Teacher</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="registration" data-toggle="tab">
                <i class="fas fa-users"></i>
                <span lang="en">Registration</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="student" data-toggle="tab">
                <i class="fas fa-users"></i>
                <span lang="en">Student</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="email" data-toggle="tab">
                <i class="fas fa-mail-bulk"></i>
                <span lang="en">Email</span>
            </a>
            <a href=".management_tab" class="get-management edit-mode" data-page="message" data-toggle="tab">
                <i class="fas fa-comments"></i>
                <span lang="en">Notification Message</span>
            </a>
        </div>
        <div class="tab-content">	
            <div class="management_tab tab-pane fade in active">
                <div class="content-container" style="margin: 25px auto;"></div>
            </div>
            <div class="classroom_form_tab tab-pane fade in" id="classroom_form_tab">
                <div class="menu-container">
                    <div v-for="tab in formTabs" :key="tab.name" class="sub-menus" :class="{ active: currentTab === tab.name }" @click="selectMune(tab.name)"><i :class="tab.icon"></i><span>{{ tab.label }}</span></div>
                </div>
                <div class="menu-content" v-loading="loading_page">
                    <div v-if="currentTab === 'classroom-form-builder'" ref="classroom-form-builder">
                        <div id="form_builder">
                            <div class="form-container form">
                                <h1 class="form-title">&ldquo;Register Form&rdquo;</h1>
                                <input v-model="formName" ref="form-name" placeholder="Form Name" class="form-name-input" maxlength="250"/>
                            </div>
                            <question-type-modal v-show="showModal" @confirm="confirmType" @close="closeModal"></question-type-modal>
                            <div v-if="questions.length && loading_menu === false">
                                <form-builder :questions="questions"></form-builder>
                            </div>
                            <div v-else class="form-container">
                                <div style="text-align: center;color: #ddd;"><h4>No Question</h4></div>
                            </div>
                            <button @click="onSaveForms" class="save-question-button btn btn-warning"><i class="fas fa-save"></i> Save Form</button>
                            <button @click="addQuestion" class="add-question-button btn btn-primary"><i class="fas fa-plus"></i> Add Question</button>
                        </div>
                    </div>
                    <div v-else-if="currentTab === 'classroom-form-dashboard'" ref="classroom-form-dashboard">
                        <div id="form_dashboard">
                            <div class="form-container response-form">
                                <h4 class="form-title mb-2">&ldquo;{{ formName }}&rdquo;</h4>
                                <div class="response-menu-container mb-3">
                                    <div class="response-sub-menus" :class="{ active: responseType === 'Summary' }" @click="switchResponseType('Summary')">Summary</div>
                                    <div class="response-sub-menus" :class="{ active: responseType === 'Question' }" @click="switchResponseType('Question')">Question</div>
                                </div>
                            </div>
                            <div v-if="questions.length && responseType === 'Summary'">
                                <summary-form-response :questions="questions" :classroom_id="classroom_id"></summary-form-response>
                            </div>
                            <div v-else-if="questions.length && responseType === 'Question'">
                                <question-form-response :questions="questions" :classroom_id="classroom_id"></question-form-response>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="currentTab === 'classroom-form-export'" ref="classroom-form-export">
                        <div id="form_export">
                            <div class="form-container export-form">
                                <h4 class="form-title mb-2">&ldquo;{{ formName }}&rdquo;</h4>
                                <div class="export-filter-container mb-3">
                                    <div class="row">
                                        <div class="col col-xs-12 col-sm-6 form-group export-filter">
                                            <label for="export-filter-date"><i class="far fa-calendar"></i> Time stamp</label>
                                            <el-daterange-picker :value="form_export.excel.filter_date" @update="handleUpdateDateFilter" custom-class="form-control export-filters" name="export-filter-date" id="export-filter-date"  :disabled="exporting"/>
                                        </div>
                                        <div class="col col-xs-12 col-sm-6 form-group export-filter">
                                            <label for="export-filter-gender"><i class="fas fa-venus-mars"></i> Gender</label>
                                            <select v-model="form_export.excel.filter_gender" ref="export-filter-gender" name="export-filter-gender" id="export-filter-gender" class="form-control select2 export-filters" placeholder="Select Gender" :disabled="exporting">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                        <div class="col col-xs-12 col-sm-6 form-group export-filter">
                                            <label for="export-filter-user"><i class="fas fa-chalkboard-teacher"></i> User</label>
                                            <select v-model="form_export.excel.filter_user" ref="export-filter-user" name="export-filter-user" id="export-filter-user" class="form-control select2 export-filters" placeholder="Select User" :disabled="exporting">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-container export-form-actions text-right">
                                <el-button title="Export Excel" v-loading="exporting" :disabled="exporting" class="btn btn-white success" @click="exportExcel"><span><i class="far fa-file-excel"></i> Export Excel</span></el-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzxc7D9o3CcmSyLWVo6h4rCxS0yL_wB2k&libraries=places"></script>
<div class="modal fade modal-template" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="closeTemplate()">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div class="modal fade previewModal" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-body"></div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<script src="/classroom/js/classroom-component.js?v=<?php echo time(); ?>&classroom_id=<?php echo $_POST['classroom_id']; ?>"></script>
</body>
</html>