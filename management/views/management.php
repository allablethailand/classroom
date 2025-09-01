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
<script src="/classroom/management/js/management.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>
<body>
<?php require_once "../../include_header.php"; ?>
<div class="container-fluid">
    <div class="row">
        <div class="filter">
            <a class="toggleFilter" title="Click to show or hide filter"><i class="fas fa-sliders-h"></i></a>
            <label class="countFilter">0</label>
            <div class="row">
                <div class="col-md-5ths col-sm-4 col-xs-12">
                    <p style="margin:10px auto;">
                        <i class="far fa-calendar"></i>
                        <span lang="en">Classroom Date</span>
                    </p>
                    <input type="text" id="filter_date" class="form-control filter-object" placeholder="All">
                </div>
                <div class="col-md-5ths col-sm-4 col-xs-12">
                    <p style="margin:10px auto;">
                        <i class="fas fa-cubes"></i>
                        <span lang="en">Mode</span>
                    </p>
                    <select class="form-control filter-object filter-select" id="filter_mode">
                        <option value="" selected>All</option>
                        <option value="online">Online</option>
                        <option value="onsite">Onsite</option>
                    </select>
                </div>
            </div>
        </div>
        <table class="table table-border" id="tb_classroom">
            <thead>
                <tr>
                    <th></th>
                    <th lang="en">Classroom</th>
                    <th lang="en">Start - End</th>
                    <th lang="en">Student</th>
                    <th lang="en">Mode</th>
                    <th lang="en">Register</th>
                    <th lang="en">Create Date</th>
                    <th lang="en">Create By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</body>
</html>