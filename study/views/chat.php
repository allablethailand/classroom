<?php 
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';
require_once $base_include . '/classroom/study/actions/student_func.php'; 

$student_id = getStudentId();
$comp_id = getStudentCompId($student_id);
$employee_id = getStudentEmpId($student_id);

?>

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
  <!-- <link rel="stylesheet" href="/dist/css/origami.css?v=<?php echo time(); ?>"> -->
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
  <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>
  <script src="/classroom/study/js/chat.js?v=<?php echo time(); ?>"  type="text/javascript"></script>


</head>

<body>
  <?php
  require_once 'component/header.php'; ?>

  <input type="hidden" id="emp_id" value="<?php echo $employee_id; ?>">
  <input type="hidden" id="comp_id" value="<?php echo $comp_id; ?>">

  <!-- work ON mobile screen ONLY -->
  <div class="main-content">
    <div class="container-fluid">
      <h1 class="heading-1" >Origami AI</h1>
       <div class="divider-1">
           <span></span>
       </div>
    </div>

    <div class="chat-bg-container">
        <div class="chat-start-button" style="display: flex;">
            <button class="start-chat-btn">
                Start Chat <i class="fas fa-play-circle"></i>
            </button>
        </div>
        <div class="chat-text-header" style="display: none;">
            <div class="chat-flex-header" >
                <p>Origami AI</p>
                <div class="">
                    <button class="transparent-button" id="chatHistoryButton">
                    <i class="fas fa-history"></i>
                    </button>
                    <button class="transparent-button" id="clearChatButton">
                        <i class="fas fa-comment-medical"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="chat-messages" id="chatMessages" style="margin: 20px; max-height: 80vh; overflow-y: scroll; display: none;">
            <!-- <div class="message message-user">
                <div class="message-content">
                    I installed a few Chrome extensions and updated Zoom.
                </div>
            </div>

            <div class="message message-bot">
                <div class="message-content">
                    slow since yesterday.
                </div>
            </div>

            <div class="message message-bot">
                <div class="message-content">
                    Let's troubleshoot like tech ninjas 🥷💻<br>
                    First question: Did you install or update anything recently?
                </div>
            </div>

            
            <div class="message message-user">
                <div class="message-content">
                    Pretty full. Like, 12GB left on a 256GB SSD.
                </div>
            </div>

            <div class="message message-bot">
                <div class="message-wrapper">

                    <div class="message-content">
                        Chrome extensions... the usual suspects. 🕵️<br>
                        Let's try this:<br>
                        1. Disable unnecessary extensions<br>
                        2. Clear browser cache<br>
                        3. Restart your laptop<br><br>
                        Also, how's your disk space looking?
                    </div>
                    <div class="message-time">10:23 AM</div>
                    <div class="message-actions">
                        <i class="fa fa-files-o" title="Copy"></i>
                        <i class="fa fa-rotate-right" title="Regenerate"></i>
                        <i class="fa fa-thumbs-o-up" title="Good response"></i>
                        <i class="fa fa-thumbs-o-down" title="Bad response"></i>
                    </div>
                </div>
            </div>

            <div class="message message-bot">
                <div class="message-content">
                    Yep, your laptop's gasping for space. Try cleaning up large files or offloading to the cloud.
                </div>
            </div> -->

            <!-- <div class="typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div> -->
        </div>
        <div class="chat-input-container" style="display: none;">
                <div class="input-wrapper">
                    <div class="input-icons">
                        <div class="message-icon-shape-sm-show">
                            <i class="fa fa-paperclip"></i>
                        </div>
                        <div class="message-icon-shape">
                            <i class="fa fa-smile-o"></i>
                        </div>
                         <div class="message-icon-shape">
                             <i class="fa fa-ellipsis-h"></i>
                         </div>
                    </div>
                    <input type="text" class="chat-input" placeholder="Ask anything" id="chatmessageInput" data-group="test">
                    <div class="send-button" >

                        <button class="send-button" id="sendMessageButton">
                            <i class="fa fa-arrow-up"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="disclaimer" style="display: none;">
                AI can make mistakes. Please double-check responses.
            </div>
    </div>
   
  </div>
  <?php require_once 'component/footer.php'; ?>

<div class="modal fade chatHistoryModal" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>


</body>

</html>