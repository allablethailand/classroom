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

?>
    <div class="main-content" style="margin-top: 10px; min-height: 100vh;">
        <!-- <h2 class="menu-section-title">เมนู</h2> -->
        <div class="container-fluid">
            <div class="row">
                <div class="actions-grid">
                    <a class="action-card" href="schedule">
                        <div class="action-icon purple">
                            <svg fill="#FFF" width="24" height="24" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 365.473 365.473" xml:space="preserve">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M4.029,46.205V24.266c0-4.971,4.114-8.705,9.085-8.705h69.915V9c0-4.971,4.029-9,9-9s9,4.029,9,9v6.561h140V9 c0-4.971,4.029-9,9-9s9,4.029,9,9v6.561h70.27c4.971,0,8.73,3.734,8.73,8.705v21.939c0,4.971-4.029,9-9,9s-9-4.029-9-9V33.561h-61 v5.967c0,4.971-4.029,9-9,9s-9-4.029-9-9v-5.967h-140v5.967c0,4.971-4.029,9-9,9s-9-4.029-9-9v-5.967h-61v12.644 c0,4.971-4.029,9-9,9S4.029,51.176,4.029,46.205z M361.444,284.249c0,44.789-36.439,81.224-81.228,81.224 c-44.79,0-81.228-36.445-81.228-81.234c0-34.795,21.994-64.565,52.807-76.112c-1.168-1.519-1.864-3.448-1.864-5.512 c0-4.971,4.029-9.054,9-9.054h42.57c4.971,0,9,4.083,9,9.054c0,2.064-0.695,4.02-1.864,5.539c7.485,2.805,14.451,6.684,20.707,11.45 l4.445-4.445c3.515-3.515,9.214-3.513,12.728,0c3.515,3.515,3.514,9.213,0,12.728l-4.136,4.135 C354.273,246.154,361.444,264.377,361.444,284.249z M343.444,284.252c0-34.864-28.364-63.229-63.228-63.229 c-34.864,0-63.228,28.364-63.228,63.229c0,34.864,28.364,63.228,63.228,63.228C315.08,347.479,343.444,319.116,343.444,284.252z M292.394,262.017l-3.365,3.272v-13.921c0-4.971-4.029-9-9-9s-9,4.029-9,9v35.65c0,1.225,0.338,2.392,0.781,3.456 c0.439,1.058,1.135,2.048,1.995,2.908c0.881,0.881,1.923,1.542,3.01,1.98c0.002,0.001,0.015,0.001,0.017,0.002 c0.004,0.002,0.014-0.222,0.019-0.22c0.949,0.382,1.984,0.417,3.061,0.417c0.006,0,0.011,0,0.019,0c0.095,0,0.19,0.004,0.286,0.004 s0.19-0.004,0.285-0.004c0.006,0,0.013,0,0.019,0c1.096,0,2.142-0.043,3.104-0.437c1.076-0.439,2.084-0.983,2.957-1.856 l18.636-18.58c3.515-3.515,3.467-9.185-0.047-12.7C301.654,258.473,295.909,258.502,292.394,262.017z M196.534,231.561 c0,4.971-4.029,9-9,9h-7.505v66.138c0,4.971-3.941,8.966-8.912,8.966c-0.303,0-0.514-0.05-0.809-0.078 c-0.295,0.029-0.595-0.025-0.897-0.025H13.114c-4.971,0-9.085-3.892-9.085-8.862V80.369v-0.002c0-4.971,4.114-8.806,9.085-8.806 h316.185c4.971,0,8.73,3.835,8.73,8.806v97.588c0,4.971-4.029,9-9,9s-9-4.029-9-9v-13.394h-61v0.769c0,4.971-4.029,9-9,9 s-9-4.029-9-9v-0.769h-61v58h7.505C192.505,222.561,196.534,226.59,196.534,231.561z M83.029,240.561h-61v57h61V240.561z M83.029,164.561h-61v58h61V164.561z M162.029,240.561h-61v57h61V240.561z M162.029,164.561h-61v58h61V164.561z M180.029,109.228 v37.333h61v-37.333c0-4.971,4.029-9,9-9s9,4.029,9,9v37.333h61v-57h-298v57h61v-37.333c0-4.971,4.029-9,9-9s9,4.029,9,9v37.333h61 v-37.333c0-4.971,4.029-9,9-9S180.029,104.257,180.029,109.228z"></path>
                                </g>
                            </svg>
                        </div>
                        <h4>SCHEDULE</h4>
                    </a>

                    <a class="action-card" href="class">
                        <div class="action-icon green">
                            <svg fill="#FFF" width="24" height="24" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.001 512.001" xml:space="preserve">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <g>
                                        <g>
                                            <path d="M467.309,16.768H221.454c-6.128,0-11.095,4.967-11.095,11.095v86.451l12.305-7.64c3.131-1.945,6.475-3.257,9.884-3.978 V38.958h223.665v160.016H232.549v-25.89l-22.19,13.778v23.208c0,6.128,4.967,11.095,11.095,11.095h245.855 c6.127,0,11.095-4.967,11.095-11.095V27.863C478.404,21.735,473.436,16.768,467.309,16.768z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M306.001,78.356c-2.919-3.702-8.285-4.335-11.986-1.418l-38.217,30.133c3.649,2.385,6.85,5.58,9.301,9.527 c0.695,1.117,1.298,2.266,1.834,3.431l37.651-29.687C308.286,87.424,308.92,82.057,306.001,78.356z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <circle cx="121.535" cy="31.935" r="31.935"></circle>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M252.01,124.728c-4.489-7.229-13.987-9.451-21.218-4.963l-31.206,19.375c-0.13-25.879-0.061-12.145-0.144-28.811 c-0.101-20.005-16.458-36.281-36.464-36.281h-15.159c-12.951,33.588-8.779,21.12-19.772,49.63l4.623-20.131 c0.32-1.508,0.088-3.08-0.655-4.43l-6.264-11.393l5.559-10.109c0.829-1.508-0.264-3.356-1.985-3.356h-15.271 c-1.72,0-2.815,1.848-1.985,3.356l5.57,10.13l-6.276,11.414c-0.728,1.325-0.966,2.865-0.672,4.347l4.005,20.172 c-2.159-5.599-17.084-44.306-19.137-49.63H80.093c-20.005,0-36.363,16.275-36.464,36.281l-0.569,113.2 c-0.042,8.51,6.821,15.443,15.331,15.486c0.027,0,0.052,0,0.079,0c8.473,0,15.364-6.848,15.406-15.331l0.569-113.2 c0-0.018,0-0.036,0-0.053c0.024-1.68,1.399-3.026,3.079-3.013c1.68,0.012,3.034,1.378,3.034,3.058l0.007,160.381 c14.106-0.6,27.176,4.488,36.981,13.423v-62.568h7.983v71.773c5.623,8.268,8.914,18.243,8.914,28.974 c0,9.777-2.732,18.928-7.469,26.731c4.866,0.023,9.592,0.669,14.099,1.861c6.076-5.271,13.385-9.151,21.437-11.136 c0-279.342-0.335-106.627-0.335-229.418c0-1.779,1.439-3.221,3.218-3.224c1.779-0.004,3.224,1.432,3.232,3.211 c0.054,10.807,0.224,44.59,0.283,56.351c0.028,5.579,3.07,10.708,7.953,13.407c4.874,2.694,10.835,2.554,15.583-0.394 l54.604-33.903C254.276,141.458,256.499,131.957,252.01,124.728z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <circle cx="429.221" cy="322.831" r="33.803"></circle>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M511.459,405.811c-0.107-21.176-17.421-38.404-38.598-38.404c-9.137,0-76.583,0-84.781,0 c3.637,7.068,5.704,15.069,5.704,23.55c0,9.005-2.405,18.413-7.5,26.782c18.904,0.764,35.468,10.91,45.149,25.897h40.579v-37.43 c0-1.842,1.46-3.352,3.301-3.415s3.402,1.345,3.526,3.182c0,0,0,0.001,0,0.002l0.19,37.661h32.621L511.459,405.811z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M290.469,390.956c0-8.629,2.138-16.763,5.894-23.92c-22.009,0-47.852,0-75.267,0c3.472,6.939,5.437,14.756,5.437,23.029 c0,9.721-2.73,18.926-7.469,26.731c15.558,0.074,29.912,6.538,40.283,17.267c10.054-9.822,23.759-15.914,38.836-15.995 C292.948,409.616,290.469,400.126,290.469,390.956z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M264.819,288.655c-18.668,0-33.804,15.132-33.804,33.803c0,18.628,15.107,33.803,33.804,33.803 c18.518,0,33.803-14.965,33.803-33.803C298.622,303.808,283.517,288.655,264.819,288.655z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M123.217,390.065c0-8.252,1.956-16.053,5.411-22.98c-1.457-0.072,4.672-0.049-89.485-0.049 c-21.068,0-38.491,17.138-38.598,38.404l-0.192,38.196c14.907,0,17.906,0,32.621,0l0.191-38.031 c0.01-1.884,1.541-3.402,3.423-3.397c1.882,0.006,3.404,1.532,3.404,3.414v38.014h45.727c9.855-15.754,26.8-25.646,45.243-26.406 C125.956,409.168,123.217,399.865,123.217,390.065z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M82.786,288.655c-18.668,0-33.803,15.134-33.803,33.803c0,18.584,15.046,33.803,33.803,33.803 c18.536,0,33.804-15.015,33.804-33.803C116.59,303.788,101.455,288.655,82.786,288.655z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M422.533,473.807c-0.105-21.178-17.42-38.406-38.597-38.406c-2.246,0-82.969,0-85.507,0 c-21.176,0-39.601,17.227-39.708,38.404l-0.275-0.891c-0.105-21.092-17.341-38.404-38.597-38.404c-24.544,0-59.795,0-85.507,0 c-21.176,0-39.601,17.227-39.708,38.404L94.442,512h32.621l0.191-38.922c0.008-1.622,1.327-2.93,2.948-2.926 c1.621,0.004,2.932,1.32,2.932,2.941v38.908c19.121,0,68.483,0,86.392,0v-38.908c0-1.736,1.405-3.144,3.141-3.149 c1.735-0.004,3.149,1.397,3.158,3.133l0.191,38.923c6.669,0,58.238,0,65.134,0l0.191-38.031c0,0,0-0.001,0-0.002 c0.009-1.621,1.328-2.928,2.949-2.924c1.621,0.004,2.931,1.32,2.931,2.941v38.016c19.121,0,68.483,0,86.392,0v-38.016 c0-1.736,1.405-3.144,3.141-3.149c1.735-0.004,3.149,1.397,3.158,3.133l0.191,38.031h32.621L422.533,473.807z"></path>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <circle cx="175.934" cy="389.933" r="34.198"></circle>
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <circle cx="342.07" cy="390.821" r="34.198"></circle>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <h4>CLASSROOM</h4>
                    </a>

                    <a class="action-card" href="myphoto">
                        <div class="action-icon orange">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M8 11C9.10457 11 10 10.1046 10 9C10 7.89543 9.10457 7 8 7C6.89543 7 6 7.89543 6 9C6 10.1046 6.89543 11 8 11Z" stroke="#FFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M6.56055 21C12.1305 8.89998 16.7605 6.77998 22.0005 14.63" stroke="#FFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M18 3H6C3.79086 3 2 4.79086 2 7V17C2 19.2091 3.79086 21 6 21H18C20.2091 21 22 19.2091 22 17V7C22 4.79086 20.2091 3 18 3Z" stroke="#FFF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                            </svg>
                        </div>
                        <h4>MY PHOTO</h4>
                    </a>

                    <a class="action-card" href="document">
                        <div class="action-icon blue">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path d="M21 7V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V7C3 4 4.5 2 8 2H16C19.5 2 21 4 21 7Z" stroke="#FFF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path opacity="0.8" d="M14.5 4.5V6.5C14.5 7.6 15.4 8.5 16.5 8.5H18.5" stroke="#FFF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path opacity="0.8" d="M8 13H12" stroke="#FFF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path opacity="0.8" d="M8 17H16" stroke="#FFF" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                                </g>
                            </svg>
                            <!-- <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke="currentColor" strokeWidth="2" />
                            </svg> -->
                        </div>
                        <h4>MY DOC</h4>
                    </a>
                </div>
            </div>
            <div class="row">Course Progress</div>
            <div class="row">
                <div class="container-menu" style="margin-top: 10px;">
                    <div class="header-menu">
                        <span class="title-menu">101 - Applied Physics </span>
                        <span class="subtitle-menu">3 weeks left</span>
                    </div>

                    <div class="usage-menu">
                        <div class="progress-section">
                            <div class="progress-header-flex">
                                <!-- <h3 class="progress-title">test</h3> -->
                                <span class="progress-text">
                                    Your Progress:  
                                </span>
                                <span class="progress-text">
                                     70 / 100
                                </span>
                                
                            </div>

                            <div class="progress-container">
                                <div class="progress-bar-new">
                                   <?php foreach ($segments as $index => $segmentType): ?>
                                        <div class="progress-segment <?php echo htmlspecialchars($segmentType); ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <div class="row">
                <div class="container-menu" style="margin-top: 10px;">
                    <div class="header-menu">
                        <span class="title-menu">505 - Advance English </span>
                        <span class="subtitle-menu">9 weeks left</span>
                    </div>

                    <div class="usage-menu">
                        <div class="progress-section">
                            <div class="progress-header-flex">
                                <!-- <h3 class="progress-title">test</h3> -->
                                <span class="progress-text">
                                    Your Progress:  
                                </span>
                                <span class="progress-text">
                                     20 / 100
                                </span>
                                
                            </div>

                            <div class="progress-container">
                                <div class="progress-bar-new">
                                   <?php foreach ($segments_two as $index => $segmentType): ?>
                                        <div class="progress-segment <?php echo htmlspecialchars($segmentType); ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <?php require_once 'component/footer.php'; ?>
</body>

</html>