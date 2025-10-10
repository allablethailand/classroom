<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
  <title>Chatbot • ORIGAMI SYSTEM</title>
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
  <link rel="stylesheet" href="/classroom/study/css/chat.css?v=<?php echo time(); ?>">
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
</head>

<body>
  <?php
  require_once 'component/header.php'; ?>

  <!-- work ON mobile screen ONLY -->
  <div class="main-content">
    <div class="container-fluid">
      <h1 class="heading-1" >Origami AI</h1>
       <div class="divider-1">
           <span></span>
       </div>
    </div>
    <div class="text-center space-y-8 max-w-4xl mx-auto">
      <div class="relative mx-auto w-32 h-32 md:w-40 md:h-40 mb-8" style="margin-bottom: 1rem;">
        <div class="absolute inset-0 bg-gradient-to-r from-orange-400 to-pink-500 rounded-full animate-ping opacity-75"></div>
      </div>
      <div class="container-fluid px-4 py-2" style="margin-bottom: 1rem;">
        <div class="space-y-4">
          <h3 class="text-2xl md:text-6xl lg:text-7xl font-bold bg-gradient-to-r from-pink-400 via-purple-400 to-cyan-400 bg-clip-text text-transparent animate-pulse">
            Origami AI is coming soon!
          </h3>
          <!-- <h2 class="text-2xl md:text-3xl lg:text-4xl font-semibold text-white">
          </h2> -->
          <p class="text-lg md:text-xl text-purple-200 max-w-2xl mx-auto leading-relaxed">
            Get ready for an incredible experience <br>
            that will blow your mind!
          </p>
        </div>
      </div>
      <div class="relative" >
        <div  style="display: flex;">
          <img src="" alt="" onerror="this.src='/images/origami-logo-robot.png'" class="img-rounded img-contain">
        </div>
        <!-- <div class="robot-container">
          <div class="robot-head">

            <div class="eye eye-left">
              <div class="eye-pupil"></div>
            </div>
            <div class="eye eye-right">
              <div class="eye-pupil"></div>
            </div>


            <div class="mouth"></div>


            <div class="head-detail head-detail-1"></div>
            <div class="head-detail head-detail-2"></div>
          </div>


          <div class="robot-body">

            <div class="chest-panel">
              <div class="gear gear-1"></div>
              <div class="gear gear-2"></div>
              <div class="status-light status-light-1"></div>
              <div class="status-light status-light-2"></div>
              <div class="status-light status-light-3"></div>
            </div>


            <div class="arm arm-left">
              <div class="hand hand-left"></div>
            </div>
            <div class="arm arm-right">
              <div class="hand hand-right"></div>
            </div>
          </div>


          <div class="robot-legs">
            <div class="leg leg-left">
              <div class="foot foot-left"></div>
            </div>
            <div class="leg leg-right">
              <div class="foot foot-right"></div>
            </div>
          </div>
        </div> -->


      </div>
    </div>
  </div>
  <?php require_once 'component/footer.php'; ?>


</body>

</html>