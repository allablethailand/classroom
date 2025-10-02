<?php


?>



<!doctype html>
<html>

<head>
    <script>
    // var classroomId = <?php echo json_encode($class_id); ?>;
</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Game • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/classinfo.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/menu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/dist/css/sweetalert.css">
    <script src="/dist/js/jquery/3.6.3/jquery.js"></script>
    <script src="/bootstrap/3.3.6/js/jquery-2.2.3.min.js" type="text/javascript"></script>
    <script src="/dist/js/sweetalert.min.js"></script>
    <script src="/dist/js/jquery.dataTables.min.js"></script>
    <script src="/dist/js/dataTables.bootstrap.min.js"></script>
    <script src="/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/dist/js/select2-build.min.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/all.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/v4-shims.min.js" charset="utf-8" type="text/javascript"></script>
    <script src="/dist/fontawesome-5.11.2/js/fontawesome_custom.js?v=<?php echo time(); ?>" charset="utf-8" type="text/javascript"></script>
    <script src="/classroom/study/js/classinfo.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
    <?php require_once 'component/header.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid" style="margin-bottom: 7rem;">
            <h1 class="heading-1">Mini Game</h1>
            <div class="divider-1"> 
                <span></span>
            </div>
            <div class="text-center mb-4 course-class-info" style="margin-top: 2rem; margin: 1rem">
                <div class="card group-card h-100 bg-white rounded-small" style=" border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
                    <div class="panel-heading border-0" style="padding:0;">
                        <div class="d-flex-bs align-items-center gap-3">
                            <div class="group-icon-large" style="color: #FFF;">
                                <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                            </div>
                            <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                <div class="d-flex-bs align-items-center gap-2 mb-1">
                                    <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
                                </div>
                                <p class="text-secondary mb-0 small text-truncate-2">
                                    <?php echo "เกมแรก" ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card group-card h-100 bg-white rounded-small" style=" margin-top: 2rem; margin: 1rem border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
                    <div class="panel-heading border-0" style="padding:0;">
                        <div class="d-flex-bs align-items-center gap-3">
                            <div class="group-icon-large" style="color: #FFF;">
                                <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                            </div>
                            <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                <div class="d-flex-bs align-items-center gap-2 mb-1">
                                    <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
                                </div>
                                <p class="text-secondary mb-0 small text-truncate-2">
                                    <?php echo "เกมสอง" ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card group-card h-100 bg-white rounded-small" style=" margin-top: 2rem; margin: 1rem border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
                    <div class="panel-heading border-0" style="padding:0;">
                        <div class="d-flex-bs align-items-center gap-3">
                            <div class="group-icon-large" style="color: #FFF;">
                                <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
                                <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
                            </div>
                            <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
                                <div class="d-flex-bs align-items-center gap-2 mb-1">
                                    <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
                                </div>
                                <p class="text-secondary mb-0 small text-truncate-2">
                                    <?php echo "เกมสาม" ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
    
    <?php require_once 'component/footer.php'; ?>


</body>
<script>

</script>

</html>