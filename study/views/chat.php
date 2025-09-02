<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Chat • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <link rel="stylesheet" href="/dist/css/select2.min.css">
    <link rel="stylesheet" href="/dist/css/select2-bootstrap.css">
    <link rel="stylesheet" href="/dist/css/jquery-ui.css">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/group.css?v=<?php echo time(); ?>">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/dist/js/jquery.redirect.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/group.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
    <?php 
    require_once 'component/header.php'; ?>

    <!-- work ON mobile screen ONLY -->
    <div class="min-vh-100 bg-ori-gray">
        <div class="container-fluid px-4 py-2">
            <div class="text-center mb-4">
                <h1 class="display-4 fw-bold text-dark mb-bs-5 text-center">
                    Group
                    <!-- Element Group -->
                </h1>
            </div>

            <div class="g-4 justify-content-center bg-element-fire-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">
                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <div class="group-icon-large" style="color: #FFF;">
                                    <!-- {group.icon} -->
                                    <i class="fas fa-fire-alt" style="width: 50px;"></i>
                                </div>

                                <!-- {/* Group Info on Right */} -->
                                <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                    <div class="d-flex-bs align-items-center gap-2 mb-1">
                                        <h4 class="panel-title mb-0 text-truncate">Fire</h4>
                                    </div>
                                    <p class="text-secondary mb-0 small text-truncate-2">
                                        สมาชิกปัจจุบัน 39 คน
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="g-4 justify-content-center bg-element-water-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">

                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <div class="group-icon-large" style="color: #FFF;">
                                    <!-- {group.icon} -->
                                    <i class="fas fa-tint" style="width: 50px;"></i>
                                </div>

                                <!-- {/* Group Info on Right */} -->
                                <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                    <div class="d-flex-bs align-items-center gap-2 mb-1">
                                        <h4 class="panel-title mb-0 text-truncate">Water</h4>
                                    </div>
                                    <p class="text-secondary mb-0 small text-truncate-2">
                                        สมาชิกปัจจุบัน 40 คน
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="g-4 justify-content-center bg-element-earth-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">
                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <a href="student">

                                    <!-- {group.icon} -->
                                    <i class="fas fa-leaf" style="width: 50px;"></i>

                            </div>

                            <!-- {/* Group Info on Right */} -->
                            <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                <div class="d-flex-bs align-items-center gap-2 mb-1">
                                    <h4 class="panel-title mb-0 text-truncate">Fire</h4>
                                    <!-- <span 
                                        class="badge-custom rounded-pill"
                                        style=" 
                                        backgroundColor: `${group.color}20`, 
                                        color: group.color,
                                        border: `1px solid ${group.color}30`
                                        "
                                    >
                                     50 / 50
                                </span> -->
                                </div>
                                <p class="text-secondary mb-0 small text-truncate-2">
                                    สมาชิกปัจจุบัน 50 คน
                                </p>
                            </div>
                        </div>
                        <b>TESTT</b>
                        </a>

                    </div>
                </div>


            </div>
            <div class="g-4 justify-content-center bg-element-wind-two mx-3 mb-bs-3 rounded-small">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card group-card h-100 ">
                        <div class="panel-heading border-0">
                            <div class="d-flex-bs align-items-center gap-3">
                                <div class="group-icon-large" style="color: #FFF;">
                                    <!-- {group.icon} -->
                                    <i class="fas fa-wind" style="width: 50px;"></i>
                                </div>

                                <!-- {/* Group Info on Right */} -->
                                <div class="flex-grow-bs-1" style=" min-Width: 0 ">
                                    <div class="d-flex-bs align-items-center gap-2 mb-1">
                                        <h4 class="panel-title mb-0 text-truncate">Fire</h4>
                                    </div>
                                    <p class="text-secondary mb-0 small text-truncate-2">
                                        สมาชิกปัจจุบัน 50 คน
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>


        <?php require_once 'component/footer.php'; ?>
    </div>


</body>

</html>