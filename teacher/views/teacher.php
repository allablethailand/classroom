<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
<title>Controls Application • ORIGAMI SYSTEM</title>
<link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dist/css/sweetalert.css">
<link rel="stylesheet" href="/dist/css/select2.min.css">
<link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
<link rel="stylesheet" href="/dist/css/jquery-ui.css">
<link rel="stylesheet" href="/classroom/teacher/css/teacher.css?v=<?php echo time(); ?>">
<script src="/dist/js/jquery/3.6.3/jquery.js"></script>
<script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
<script src="/dist/js/sweetalert.min.js"></script>
<script src="/dist/js/jquery.dataTables.min.js"></script>
<script src="/dist/js/dataTables.bootstrap.min.js"></script>
<script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript" ></script>
<script src="/classroom/teacher/js/teacher.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>
<body>
<?php require_once "../../include_header.php"; ?>
<div class="container-fluid">
    <table class="table table-border" id="tb_teacher">
        <thead>
            <tr>
                <th lang="en">Profile</th>
                <th lang="en">Teacher Name</th>
                <th lang="en">Nickname</th>
                <th lang="en">Highest Education</th>
                <th lang="en">Email</th>
                <th lang="en">Mobile</th>
                <th lang="en">Available</th>
                <th lang="en">Type</th>
                <th lang="en">Create Date</th>
                <th lang="en">Create By</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
</body>
</html>