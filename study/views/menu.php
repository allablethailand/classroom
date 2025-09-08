<?php
// บรรทัดแรกสุดของไฟล์
session_start();

// โค้ดส่วนอื่นๆ ของหน้าจะเริ่มที่นี่
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Classroom • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/menu.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/menu.js?v=<?php echo time(); ?>" type="text/javascript"></script>

</head>

<body>
    <?php require_once 'component/header.php'; ?>
    <?php
    $segments = ['complete', 'complete', 'complete', 'complete', 'complete', 'upcoming', 'upcoming', 'upcoming',];
    $segments_two = ['complete', 'complete', 'upcoming', 'upcoming', 'upcoming', 'upcoming', 'upcoming', 'upcoming',];
    $old_segment = '<div class="progress-container">
                                <div class="progress-bar-new">
                                    <?php foreach ($segments as $index => $segmentType): ?>
                                        <div class="progress-segment <?php echo htmlspecialchars($segmentType); ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>';
    ?>
    <div class="main-content" style="margin-top: 10px;  margin-bottom: 5rem;">
        <!-- <h2 class="menu-section-title">เมนู</h2> -->
        <div class="container-fluid" style="margin: 0 1rem;">
            <div class="row">Upcoming Class</div>
            <div class="row">
                <div class="container-menu" style="margin-top: 10px;">
                    <div class="header-menu">
                        <span class="title-menu">Training Registration</span>
                        <span class="subtitle-menu">1 day left</span>
                    </div>

                    <div class="usage-menu">
                        <div class="progress-section">
                            <div class="progress-header-flex">
                                <!-- <h3 class="progress-title">test</h3> -->
                                <span class="progress-text">
                                    Tuesday, September 9, 2025
                                </span>
                                <span class="progress-text">
                                    9:30 - 12:00 A.M.
                                </span>

                            </div>
                            <div class="progress-header-flex">
                                <!-- <h3 class="progress-title">test</h3> -->
                                <span id="nextclass" class="progress-text-bottom">
                                    Class will begin in 1 hour 30 mins.
                                </span>
                                <span class="progress-text-end">
                                    <span class="label label-default pill pill-icon-before">ยังไม่เช็คอิน</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="container-menu" style="margin-top: 10px;">
                    <div class="header-menu">
                        <span class="title-menu">Opening Ceremony</span>
                        <span class="subtitle-menu">3 weeks left</span>
                    </div>

                    <div class="usage-menu">
                        <div class="progress-section">
                            <div class="progress-header-flex">
                                <!-- <h3 class="progress-title">test</h3> -->
                                <span class="progress-text">
                                    Wednesday, October 1, 2025
                                </span>
                                <span class="progress-text">
                                    1:00 - 5:00 P.M.
                                </span>
                            </div>


                            <div class="progress-container">
                                <!-- <div class="progress-bar-new">
                                    <?php foreach ($segments_two as $index => $segmentType): ?>
                                        <div class="progress-segment <?php echo htmlspecialchars($segmentType); ?>"></div>
                                    <?php endforeach; ?>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="container-menu" style="margin-top: 10px;">
                    <div class="header-menu">
                        <span class="title-menu">Dinner</span>
                        <span class="subtitle-menu">3 weeks left</span>
                    </div>
                    <div class="usage-menu">
                        <div class="progress-section">
                            <div class="progress-header-flex">
                                <!-- <h3 class="progress-title">test</h3> -->
                                <span class="progress-text">
                                    Wednesday, October 1, 2025
                                </span>
                                <span class="progress-text">
                                    6:00 P.M.
                                </span>

                            </div>


                            <div class="progress-container">
                                <!-- <div class="progress-bar-new">
                                    <?php foreach ($segments_two as $index => $segmentType): ?>
                                        <div class="progress-segment <?php echo htmlspecialchars($segmentType); ?>"></div>
                                    <?php endforeach; ?>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top: 1rem; ">
                <div class="actions-grid">

                    <!-- schedule -->
                    <a class="action-card" href="schedule">
                        <svg width="60" height="60" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="0.00012207" width="48" height="48" rx="9.99999" fill="url(#paint0_linear_204_460)" />
                            <path d="M38.6863 20.0417L39.6554 36.2856C39.6882 36.7291 39.4747 37.0247 39.3598 37.1561C39.2284 37.3039 38.9491 37.5339 38.4893 37.5339H34.0053L37.5531 20.0417H38.6863ZM40.493 13.7183L40.4766 13.7511C40.5095 14.1453 40.4766 14.5559 40.3781 14.9665L28.2732 37.189C27.879 38.8478 26.4008 39.9976 24.6926 39.9976H38.4893C40.608 39.9976 42.2833 38.2073 42.1191 36.0885L40.493 13.7183Z" fill="white" />
                            <path d="M23.1642 7.54466C23.3284 6.88768 22.9178 6.21427 22.2608 6.05003C21.6038 5.90221 20.9304 6.2964 20.7662 6.95338L19.9449 10.3533H22.4743L23.1642 7.54466Z" fill="white" />
                            <path d="M34.0052 7.49103C34.153 6.81763 33.726 6.17707 33.0526 6.02925C32.3956 5.88143 31.7386 6.30847 31.5908 6.98187L30.8517 10.3818H33.3811L34.0052 7.49103Z" fill="white" />
                            <path d="M40.1954 12.6202C39.6534 11.3062 38.3723 10.37 36.7955 10.37H33.3792L32.4594 14.624C32.328 15.1988 31.8189 15.593 31.2604 15.593C31.1783 15.593 31.0798 15.593 30.9976 15.5602C30.3407 15.4123 29.9136 14.7554 30.045 14.0984L30.8498 10.3536H22.4733L21.4386 14.624C21.3072 15.1824 20.798 15.5602 20.2396 15.5602C20.141 15.5602 20.0425 15.5437 19.9439 15.5273C19.2869 15.3631 18.8763 14.7061 19.0406 14.0327L19.9275 10.3372H16.5933C14.9837 10.3372 13.5548 11.3883 13.0785 12.9322L6.16372 35.1875C5.44104 37.5691 7.19847 39.9999 9.67858 39.9999H31.2604C32.9686 39.9999 34.4468 38.8502 34.841 37.1913L40.3761 14.9689C40.4746 14.5583 40.5075 14.1477 40.4746 13.7535C40.4418 13.3593 40.3596 12.9651 40.1954 12.6202ZM28.5011 31.377H15.3615C14.6881 31.377 14.1296 30.8186 14.1296 30.1452C14.1296 29.4718 14.6881 28.9133 15.3615 28.9133H28.5011C29.1745 28.9133 29.733 29.4718 29.733 30.1452C29.733 30.8186 29.1745 31.377 28.5011 31.377ZM30.1436 24.8072H17.0039C16.3305 24.8072 15.7721 24.2488 15.7721 23.5754C15.7721 22.9019 16.3305 22.3435 17.0039 22.3435H30.1436C30.817 22.3435 31.3754 22.9019 31.3754 23.5754C31.3754 24.2488 30.817 24.8072 30.1436 24.8072Z" fill="white" />
                            <defs>
                                <linearGradient id="paint0_linear_204_460" x1="4" y1="3.00012" x2="45" y2="46.0001" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#1EC0FB" />
                                    <stop offset="1" stop-color="#198FE9" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <h4 style="margin-top: 10px;">SCHEDULE</h4>
                    </a>

                    <!-- Classroom -->
                    <a class="action-card" href="class">

                        <svg width="60" height="60" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="9.99999" fill="url(#paint0_linear_204_456)" />
                            <path d="M41.7792 11.8795V33.1379C41.7792 34.8722 40.3668 36.4635 38.6325 36.678L38.0782 36.7495C35.1461 37.1429 31.016 38.3587 27.6904 39.7532C26.5283 40.236 25.241 39.3599 25.241 38.0905V13.2205C25.241 12.559 25.6164 11.9511 26.2064 11.6292C29.4783 9.85919 34.4309 8.28582 37.7922 7.99976H37.8995C40.045 7.99976 41.7792 9.73404 41.7792 11.8795Z" fill="white" />
                            <path d="M21.5906 11.6292C18.3187 9.85919 13.3661 8.28582 10.0048 7.99976H9.87967C7.73416 7.99976 5.99988 9.73404 5.99988 11.8795V33.1379C5.99988 34.8722 7.41234 36.4635 9.14662 36.678L9.70088 36.7495C12.6331 37.1429 16.7632 38.3587 20.0887 39.7532C21.2508 40.236 22.5382 39.3599 22.5382 38.0905V13.2205C22.5382 12.5411 22.1806 11.9511 21.5906 11.6292ZM11.3815 17.0466H15.4043C16.1374 17.0466 16.7453 17.6545 16.7453 18.3876C16.7453 19.1385 16.1374 19.7285 15.4043 19.7285H11.3815C10.6485 19.7285 10.0406 19.1385 10.0406 18.3876C10.0406 17.6545 10.6485 17.0466 11.3815 17.0466ZM16.7453 25.0923H11.3815C10.6485 25.0923 10.0406 24.5023 10.0406 23.7513C10.0406 23.0183 10.6485 22.4104 11.3815 22.4104H16.7453C17.4783 22.4104 18.0862 23.0183 18.0862 23.7513C18.0862 24.5023 17.4783 25.0923 16.7453 25.0923Z" fill="white" />
                            <defs>
                                <linearGradient id="paint0_linear_204_456" x1="4" y1="3" x2="45" y2="46" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#CC4CFF" />
                                    <stop offset="1" stop-color="#7F2EBD" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <h4 style="margin-top: 10px;">CLASSROOM</h4>
                    </a>

                    <!-- Calendar -->
                    <a class="action-card" href="calendar">
                        <svg width="60" height="60" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="-0.000244141" width="48" height="48" rx="9.99999" fill="url(#paint0_linear_204_467)" />
                            <path d="M41.9999 31.7766C41.9999 37.5608 37.2472 42.2499 31.3845 42.2499H16.6153C10.7525 42.2499 5.99988 37.5608 5.99988 31.7766V18.5713H41.9999V31.7766ZM13.846 30.4106C13.0813 30.4106 12.4614 31.0222 12.4614 31.7766C12.4614 32.5311 13.0813 33.1427 13.846 33.1427H15.6922C16.4569 33.1427 17.0768 32.5311 17.0768 31.7766C17.0768 31.0222 16.4569 30.4106 15.6922 30.4106H13.846ZM23.0768 30.4106C22.3121 30.4106 21.6922 31.0222 21.6922 31.7766C21.6922 32.5311 22.3121 33.1427 23.0768 33.1427H24.9229C25.6876 33.1427 26.3076 32.5311 26.3076 31.7766C26.3076 31.0222 25.6876 30.4106 24.9229 30.4106H23.0768ZM32.3076 30.4106C31.5429 30.4106 30.9229 31.0222 30.9229 31.7766C30.9229 32.5311 31.5429 33.1427 32.3076 33.1427H34.1537C34.9184 33.1427 35.5383 32.5311 35.5383 31.7766C35.5383 31.0222 34.9184 30.4106 34.1537 30.4106H32.3076ZM13.846 23.1249C13.0813 23.1249 12.4614 23.7365 12.4614 24.4909C12.4614 25.2454 13.0813 25.857 13.846 25.857H15.6922C16.4569 25.857 17.0768 25.2454 17.0768 24.4909C17.0768 23.7365 16.4569 23.1249 15.6922 23.1249H13.846ZM23.0768 23.1249C22.3121 23.1249 21.6922 23.7365 21.6922 24.4909C21.6922 25.2454 22.3121 25.857 23.0768 25.857H24.9229C25.6876 25.857 26.3076 25.2454 26.3076 24.4909C26.3076 23.7365 25.6876 23.1249 24.9229 23.1249H23.0768ZM32.3076 23.1249C31.5429 23.1249 30.9229 23.7365 30.9229 24.4909C30.9229 25.2454 31.5429 25.857 32.3076 25.857H34.1537C34.9184 25.857 35.5383 25.2454 35.5383 24.4909C35.5383 23.7365 34.9184 23.1249 34.1537 23.1249H32.3076ZM30.9229 10.8302C30.9229 11.5847 31.5429 12.1963 32.3076 12.1963C33.0723 12.1963 33.6922 11.5847 33.6922 10.8302V6.98104C38.0046 7.92452 41.329 11.4558 41.9079 15.8392H6.09182C6.6707 11.4558 9.99517 7.92452 14.3076 6.98104V10.8302C14.3076 11.5847 14.9275 12.1963 15.6922 12.1963C16.4569 12.1963 17.0768 11.5847 17.0768 10.8302V6.73202H30.9229V10.8302ZM15.6922 3.99988C16.4569 3.99988 17.0768 4.61149 17.0768 5.36595V6.73202H16.6153C15.8226 6.73202 15.0505 6.8185 14.3076 6.98104V5.36595C14.3076 4.61149 14.9275 3.99988 15.6922 3.99988ZM32.3076 3.99988C33.0723 3.99988 33.6922 4.61149 33.6922 5.36595V6.98104C32.9492 6.8185 32.1771 6.73202 31.3845 6.73202H30.9229V5.36595C30.9229 4.61149 31.5429 3.99988 32.3076 3.99988Z" fill="white" />
                            <defs>
                                <linearGradient id="paint0_linear_204_467" x1="4" y1="2.99975" x2="45" y2="45.9997" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FFD900" />
                                    <stop offset="1" stop-color="#FF8000" />
                                </linearGradient>
                            </defs>
                        </svg>

                        <h4 style="margin-top: 10px;">CALENDAR</h4>
                    </a>

                    <!-- Histroy -->
                    <a class="action-card" href="history">

                        <svg width="60" height="60" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect width="48" height="48" rx="9.99999" fill="url(#paint0_linear_226_27)"/>
<path d="M31 18C23.837 18 18 23.837 18 31C18 38.163 23.837 44 31 44C38.163 44 44 38.163 44 31C44 23.837 38.163 18 31 18ZM36.655 35.641C36.473 35.953 36.148 36.122 35.81 36.122C35.641 36.122 35.472 36.083 35.316 35.979L31.286 33.574C30.285 32.976 29.544 31.663 29.544 30.506V25.176C29.544 24.643 29.986 24.201 30.519 24.201C31.052 24.201 31.494 24.643 31.494 25.176V30.506C31.494 30.974 31.884 31.663 32.287 31.897L36.317 34.302C36.785 34.575 36.941 35.173 36.655 35.641Z" fill="white"/>
<path d="M30.5547 5C37.1098 5 41.0179 8.90782 41 15.4629V19.8203C38.7322 17.7904 35.8433 16.4406 32.6533 16.0908C33.0742 15.8605 33.3641 15.4129 33.3643 14.9053C33.3643 14.167 32.752 13.5548 32.0137 13.5547H14.0049C13.2665 13.5547 12.6543 14.1669 12.6543 14.9053C12.6545 15.6435 13.2666 16.2559 14.0049 16.2559H28.2324C24.6214 16.9295 21.4661 18.8964 19.2646 21.6582H14.0049C13.2666 21.6582 12.6544 22.2705 12.6543 23.0088C12.6543 23.7472 13.2665 24.3594 14.0049 24.3594H17.5479C16.7307 26.0115 16.2082 27.8353 16.0508 29.7627H14.0049C13.2665 29.7627 12.6543 30.3749 12.6543 31.1133C12.6544 31.8515 13.2666 32.4639 14.0049 32.4639H16.0713C16.3883 35.7372 17.7571 38.7024 19.8359 41.0176H15.4629C8.90794 41.0174 5.00014 37.1101 5 30.5371V15.4629C5.00013 8.90793 8.90793 5.00013 15.4629 5H30.5547Z" fill="white"/>
<defs>
<linearGradient id="paint0_linear_226_27" x1="4" y1="3" x2="45" y2="46" gradientUnits="userSpaceOnUse">
<stop stop-color="#57EA49"/>
<stop offset="1" stop-color="#009F0B"/>
</linearGradient>
</defs>
</svg>

                        <h4 style="margin-top: 10px;">HISTORY</h4>
                    </a>


                </div>
            </div>
        </div>
    </div>
    <?php require_once 'component/footer.php'; ?>
</body>
<script>
function calculateTimeDiffInBangkok(targetDate) {
  // Get current time in Bangkok timezone (UTC+7)
  const now = new Date();
  const bangkokNow = new Date(now.toLocaleString("en-US", { timeZone: "Asia/Bangkok" }));

//   console.log(now)

  // Parse the targetDate as a Date object if it's not already
  const target = new Date(targetDate);

  // Calculate time difference in milliseconds
  const diffMs = Math.abs(target - bangkokNow);

  // Convert milliseconds to total minutes
  const totalMinutes = Math.floor(diffMs / (1000 * 60));

  // Calculate hour and minute difference
  let hourdiff = 0;
  let minutediff = 0;

  if (totalMinutes < 24 * 60) {
    hourdiff = Math.floor(totalMinutes / 60);
    minutediff = totalMinutes % 60;
  }

  return { hourdiff, minutediff };
}

// Example usage:
const targetDate = "2025-09-09T09:30:00+07:00"; // ISO string in Bangkok timezone
const { hourdiff, minutediff } = calculateTimeDiffInBangkok(targetDate);

const nextclassSpan = document.getElementById("nextclass");
if (hourdiff > 0 || minutediff > 0) {
  nextclassSpan.innerHTML = `Class will begin in <span style="color: #ff8c5a;">&nbsp; ${hourdiff} &nbsp;</span> hour${hourdiff !== 1 ? "s" : ""} <span style="color: #ff8c5a;">&nbsp; ${minutediff} &nbsp;</span> min${minutediff !== 1 ? "s" : ""}.`;
} else {
  nextclassSpan.innerHTML = "Class has started or time not valid.";
}
</script>


</html>