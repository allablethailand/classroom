<?php


?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Mini Game • ORIGAMI SYSTEM</title>
    <link href='https://fonts.googleapis.com/css?family=Kanit' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="/classroom/study/css/game.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/classroom/study/css/style.css?v=<?php echo time(); ?>">
    <!-- <link rel="stylesheet" href="/classroom/study/css/menu.css?v=<?php echo time(); ?>"> -->
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
    <script src="/classroom/study/js/game.js?v=<?php echo time(); ?>" type="text/javascript"></script>
    <script src="/classroom/study/js/lang.js?v=<?php echo time(); ?>"  type="text/javascript"></script>
</head>

<body>
    <?php require_once 'component/header.php'; ?>

    <div class="main-content col-md-10">
        <div class="container-fluid" style="margin-bottom: 7rem;">
            <h1 class="heading-1" id="mini-game-title" data-lang="minigame">Mini Game</h1>
            <div class="divider-1">
                <span></span>
            </div>

            <div class="row" id="game-menu">
                <div class="actions-grid">
                    <button class="action-card" id="btn-quiz-game">
                        
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M36.1167 32.918L36.8771 39.0799C37.0721 40.6983 35.3367 41.8293 33.9522 40.9908L27.2053 36.9739C26.7373 36.7009 26.6203 36.1159 26.8738 35.6479C27.8488 33.854 28.3753 31.826 28.3753 29.798C28.3753 22.6612 22.2524 16.8503 14.7255 16.8503C13.1851 16.8503 11.6836 17.0843 10.2796 17.5523C9.55814 17.7862 8.85615 17.1233 9.03165 16.3823C10.8061 9.2844 17.631 4 25.7818 4C35.2977 4 43 11.1954 43 20.0677C43 25.3326 40.2896 29.993 36.1167 32.918Z" fill="url(#paint0_linear_315_5)"/>
<path d="M25.4496 29.7977C25.4496 32.1182 24.5916 34.2631 23.1487 35.9596C21.2182 38.2996 18.1567 39.801 14.7248 39.801L9.6354 42.8235C8.77741 43.35 7.68543 42.6285 7.80243 41.634L8.28992 37.7926C5.67697 35.9791 4 33.0737 4 29.7977C4 26.3658 5.83297 23.3433 8.64092 21.5494C10.3764 20.4184 12.4628 19.7749 14.7248 19.7749C20.6527 19.7749 25.4496 24.2598 25.4496 29.7977Z" fill="#292D32"/>
<path d="M11.4735 25.1389C11.2016 24.7088 11.2839 24.1394 11.7042 23.8524C11.9636 23.6795 12.2416 23.5312 12.5381 23.4077C12.8345 23.278 13.1557 23.1791 13.5017 23.1112C13.8537 23.0371 14.2305 23 14.632 23C15.1818 23 15.6821 23.0772 16.133 23.2316C16.5901 23.3799 16.9793 23.5961 17.3005 23.8802C17.6217 24.1582 17.8718 24.4979 18.051 24.8994C18.2301 25.2947 18.3197 25.7395 18.3197 26.2336C18.3197 26.7154 18.2486 27.1324 18.1066 27.4844C17.9645 27.8365 17.7884 28.1423 17.5784 28.4017C17.3684 28.6612 17.1399 28.8866 16.8928 29.0781C16.6457 29.2634 16.411 29.4394 16.1886 29.6062C15.9662 29.773 15.7748 29.9367 15.6142 30.0973C15.4536 30.2579 15.3578 30.4401 15.3269 30.6439L15.2275 31.2652C15.1567 31.708 14.7747 32.0338 14.3262 32.0338C13.8579 32.0338 13.4656 31.6793 13.4181 31.2134L13.3441 30.4864C13.338 30.4555 13.3349 30.4308 13.3349 30.4123C13.3349 30.3876 13.3349 30.3567 13.3349 30.3197C13.3349 30.0417 13.4028 29.8008 13.5387 29.597C13.6808 29.3931 13.8568 29.2016 14.0668 29.0225C14.2769 28.8372 14.4992 28.6612 14.734 28.4944C14.9749 28.3214 15.2003 28.1361 15.4103 27.9385C15.6203 27.7408 15.7933 27.5153 15.9292 27.2621C16.0713 27.0088 16.1423 26.7092 16.1423 26.3633C16.1423 26.141 16.0991 25.9402 16.0126 25.7611C15.9261 25.5758 15.8056 25.4183 15.6512 25.2886C15.503 25.1588 15.3238 25.06 15.1138 24.9921C14.9038 24.9241 14.6753 24.8901 14.4282 24.8901C14.0638 24.8901 13.7549 24.9303 13.5017 25.0106C13.2546 25.0847 13.0446 25.1712 12.8716 25.27C12.6987 25.3689 12.5504 25.4584 12.4269 25.5387C12.3095 25.619 12.2045 25.6592 12.1118 25.6592C11.8833 25.6592 11.7196 25.5634 11.6208 25.3719L11.4735 25.1389ZM12.7975 35.5824C12.7975 35.3847 12.8315 35.1994 12.8994 35.0265C12.9735 34.8473 13.0754 34.696 13.2052 34.5725C13.3349 34.4428 13.4862 34.3408 13.6592 34.2667C13.8383 34.1864 14.0298 34.1463 14.2336 34.1463C14.4313 34.1463 14.6166 34.1864 14.7895 34.2667C14.9687 34.3408 15.12 34.4428 15.2435 34.5725C15.3733 34.696 15.4752 34.8473 15.5493 35.0265C15.6296 35.1994 15.6698 35.3847 15.6698 35.5824C15.6698 35.7862 15.6296 35.9746 15.5493 36.1476C15.4752 36.3205 15.3733 36.4719 15.2435 36.6016C15.12 36.7251 14.9687 36.8209 14.7895 36.8888C14.6166 36.9629 14.4313 37 14.2336 37C14.0298 37 13.8383 36.9629 13.6592 36.8888C13.4862 36.8209 13.3349 36.7251 13.2052 36.6016C13.0754 36.4719 12.9735 36.3205 12.8994 36.1476C12.8315 35.9746 12.7975 35.7862 12.7975 35.5824Z" fill="white"/>
<defs>
<linearGradient id="paint0_linear_315_5" x1="26.0022" y1="4" x2="26.0022" y2="41.2749" gradientUnits="userSpaceOnUse">
<stop stop-color="#F28A1E"/>
<stop offset="1" stop-color="#FE6502"/>
</linearGradient>
</defs>
</svg>

                        <h4 style="margin-top: 10px;" data-lang="quiz">Quiz</h4>

                    </button>
                    <button class="action-card" id="btn-memory-game">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect x="4" y="5" width="2.57143" height="9" rx="1.28571" fill="#707070"/>
<rect x="4" y="7.57141" width="2.57143" height="9" rx="1.28571" transform="rotate(-90 4 7.57141)" fill="#707070"/>
<rect width="2.57143" height="9" rx="1.28571" transform="matrix(-1 0 0 1 44 5)" fill="#707070"/>
<rect width="2.57143" height="9" rx="1.28571" transform="matrix(4.37114e-08 -1 -1 -4.37114e-08 44 7.57141)" fill="#707070"/>
<rect x="4" y="43" width="2.57143" height="9" rx="1.28571" transform="rotate(-90 4 43)" fill="#707070"/>
<rect x="6.57141" y="43" width="2.57143" height="9" rx="1.28571" transform="rotate(180 6.57141 43)" fill="#707070"/>
<rect width="2.57143" height="9" rx="1.28571" transform="matrix(4.37114e-08 -1 -1 -4.37114e-08 44 43)" fill="#707070"/>
<rect width="2.57143" height="9" rx="1.28571" transform="matrix(1 8.74228e-08 8.74228e-08 -1 41.4286 43)" fill="#707070"/>
<path d="M24.9012 4.06591C24.4261 3.23314 24.6295 2.16859 25.4449 1.66422C25.9479 1.3608 26.4836 1.10546 27.0522 0.898197C27.6212 0.679311 28.2342 0.520656 28.891 0.422231C29.5599 0.312707 30.2722 0.275039 31.0279 0.309227C32.0625 0.356039 32.9976 0.543959 33.8331 0.872989C34.6808 1.19092 35.3948 1.63094 35.9751 2.19306C36.5559 2.74355 36.9978 3.40425 37.3008 4.17515C37.6043 4.93443 37.7349 5.77908 37.6929 6.70911C37.6518 7.61588 37.4827 8.39455 37.1853 9.0451C36.8879 9.69564 36.5306 10.2561 36.1132 10.7265C35.6959 11.1969 35.2465 11.6017 34.7652 11.9411C34.2844 12.2688 33.8277 12.5801 33.3949 12.8751C32.9622 13.17 32.5879 13.4618 32.272 13.7504C31.956 14.039 31.7603 14.3738 31.6848 14.7548L31.4449 15.9155C31.2738 16.7429 30.5271 17.3235 29.6831 17.2853C28.8017 17.2454 28.0935 16.5449 28.0439 15.664L27.9665 14.2895C27.9575 14.2308 27.9538 14.1841 27.9554 14.1492C27.9575 14.1027 27.9601 14.0446 27.9633 13.9748C27.9869 13.4517 28.1353 13.0041 28.4085 12.632C28.6932 12.2605 29.0408 11.9151 29.4513 11.5958C29.8624 11.2649 30.2959 10.9526 30.7518 10.6587C31.22 10.3537 31.6601 10.0241 32.0722 9.66997C32.4843 9.31585 32.829 8.90625 33.1063 8.44118C33.3952 7.97664 33.5544 7.41885 33.5839 6.76783C33.6028 6.34932 33.5385 5.96782 33.391 5.62332C33.2441 5.26719 33.0308 4.96049 32.7512 4.70321C32.4832 4.44645 32.1545 4.24519 31.765 4.09943C31.3756 3.95367 30.9483 3.87027 30.4833 3.84923C29.7974 3.8182 29.2127 3.86746 28.7292 3.99703C28.2579 4.11549 27.8553 4.26037 27.5214 4.43165C27.1874 4.60292 26.9008 4.75887 26.6614 4.89948C26.4337 5.04061 26.2327 5.10724 26.0583 5.09935C25.6282 5.07989 25.3282 4.88576 25.1585 4.51695L24.9012 4.06591ZM26.5037 23.8339C26.5206 23.4619 26.6003 23.116 26.7429 22.7963C26.8977 22.4654 27.1024 22.1893 27.357 21.9678C27.6122 21.7348 27.9057 21.5558 28.2375 21.431C28.5815 21.2952 28.9453 21.2359 29.3289 21.2533C29.7009 21.2701 30.0463 21.3614 30.365 21.5273C30.6958 21.6821 30.9719 21.8868 31.1934 22.1414C31.427 22.385 31.6059 22.6785 31.7302 23.0219C31.8666 23.3543 31.9264 23.7064 31.9095 24.0784C31.8922 24.4621 31.8006 24.8132 31.6347 25.1319C31.4805 25.4511 31.2758 25.7273 31.0206 25.9603C30.7776 26.1823 30.4846 26.3496 30.1417 26.4623C29.8099 26.587 29.4579 26.641 29.0859 26.6242C28.7023 26.6068 28.3451 26.5208 28.0142 26.366C27.6945 26.2234 27.4178 26.0303 27.1842 25.7868C26.9511 25.5316 26.7722 25.2381 26.6474 24.9063C26.5343 24.575 26.4864 24.2175 26.5037 23.8339Z" fill="url(#paint0_linear_313_5)"/>
<path d="M19.1707 24.3415C23.6833 24.3415 27.3415 20.6833 27.3415 16.1707C27.3415 11.6582 23.6833 8 19.1707 8C14.6582 8 11 11.6582 11 16.1707C11 20.6833 14.6582 24.3415 19.1707 24.3415Z" fill="#292D32"/>
<path d="M23.8544 25.4269C15.6673 25.4269 9 30.9176 9 37.683C9 38.1405 9.35951 38.5001 9.81707 38.5001H37.8917C38.3493 38.5001 38.7088 38.1405 38.7088 37.683C38.7088 30.9176 32.0415 25.4269 23.8544 25.4269Z" fill="#292D32"/>
<defs>
<linearGradient id="paint0_linear_313_5" x1="31.0802" y1="0.311637" x2="29.8881" y2="26.6605" gradientUnits="userSpaceOnUse">
<stop stop-color="#F28A1E"/>
<stop offset="1" stop-color="#FE6502"/>
</linearGradient>
</defs>
</svg>

                        <h4 style="margin-top: 10px;" data-lang="guesswho">Guess Who</h4>
                    </button>
                    <button class="action-card" id="btn-card-flip-game">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect x="21" y="16" width="19" height="27" rx="2" fill="#707070"/>
<rect x="8" y="5" width="19" height="27" rx="2" fill="url(#paint0_linear_314_17)"/>
<path d="M19.7553 41C12.7275 41 7 35.2682 7 28.2353C7 27.56 7.55959 27 8.23438 27C8.90918 27 9.46876 27.56 9.46876 28.2353C9.46876 33.1106 12.8592 37.1953 17.4182 38.2659L16.9738 37.5247C16.6282 36.9318 16.8092 36.1741 17.4017 35.8282C17.9778 35.4824 18.7513 35.6635 19.0969 36.2565L20.8251 39.1388C21.0555 39.5176 21.0555 39.9953 20.8415 40.3741C20.6111 40.7529 20.1997 41 19.7553 41Z" fill="#292D32"/>
<path d="M40.7656 20C40.0908 20 39.5312 19.44 39.5312 18.7647C39.5312 13.8894 36.1408 9.80471 31.5818 8.73412L32.0262 9.47529C32.3718 10.0682 32.1908 10.8259 31.5983 11.1718C31.0222 11.5176 30.2487 11.3365 29.9031 10.7435L28.1749 7.86118C27.9445 7.48235 27.9445 7.00471 28.1585 6.62588C28.3889 6.24706 28.8003 6 29.2447 6C36.2725 6 42 11.7318 42 18.7647C42 19.44 41.4404 20 40.7656 20Z" fill="#292D32"/>
<defs>
<linearGradient id="paint0_linear_314_17" x1="17.5" y1="5" x2="17.5" y2="32" gradientUnits="userSpaceOnUse">
<stop stop-color="#F28A1E"/>
<stop offset="1" stop-color="#FE6502"/>
</linearGradient>
</defs>
</svg>

                        <h4 style="margin-top: 10px;" data-lang="cardflip">Card Flip</h4>

                    </button>
                    <button class="action-card" id="btn-wordle-game">
                        <svg width="64" height="64" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.157 29.0697C18.4162 29.0697 17.4757 28.7896 17.3357 28.2293L16.3452 24.3575H11.573L10.7026 28.0792C10.5826 28.6995 9.62212 29.0097 7.8213 29.0097C6.86086 29.0097 6.15053 28.9596 5.69032 28.8596C5.23011 28.7395 5 28.6495 5 28.5895L10.3124 8.27012C10.3124 8.11005 11.6831 8.03001 14.4243 8.03001C17.1656 8.03001 18.5362 8.11005 18.5362 8.27012L23.7286 28.6195C23.7286 28.7595 23.2684 28.8696 22.348 28.9496C21.4276 29.0297 20.6972 29.0697 20.157 29.0697ZM12.3234 20.5458H15.4748L14.1242 14.3329H13.9441L12.3234 20.5458Z" fill="url(#paint0_linear_314_40)"/>
                            <path d="M40.4545 13.4325C40.4545 15.5135 39.5541 17.0942 37.7533 18.1747C38.7337 18.5348 39.5541 19.1551 40.2144 20.0355C40.8747 20.9159 41.2048 22.1665 41.2048 23.7873C41.2048 25.388 40.6046 26.6586 39.404 27.599C38.2235 28.5394 36.5227 29.0097 34.3017 29.0097H27.2184C26.8783 29.0097 26.5981 28.8996 26.378 28.6795C26.1579 28.4394 26.0479 28.1293 26.0479 27.7491V8.54025C26.0479 8.32015 26.0779 8.18008 26.1379 8.12006C26.218 8.04002 26.368 8 26.5881 8H33.4313C38.1134 8 40.4545 9.81083 40.4545 13.4325ZM31.8105 12.8622V16.794H31.9606C33.8815 16.794 34.8419 16.1537 34.8419 14.8732C34.8419 14.1728 34.6518 13.6626 34.2717 13.3425C33.9115 13.0223 33.3012 12.8622 32.4408 12.8622H31.8105ZM31.8105 20.3357V24.2375H32.3508C33.2112 24.2375 33.8415 24.0774 34.2416 23.7572C34.6418 23.4371 34.8419 22.9269 34.8419 22.2265C34.8419 21.5262 34.6518 21.036 34.2717 20.7559C33.9115 20.4757 33.3012 20.3357 32.4408 20.3357H31.8105Z" fill="url(#paint1_linear_314_40)"/>
                            <circle cx="33.9183" cy="34.4901" r="8" fill="#292D32"/>
                            <path d="M39.9183 34.9901C39.9183 38.8561 36.2843 38.4901 32.4183 38.4901C28.5523 38.4901 26.9183 38.8561 26.9183 34.9901C26.9183 31.1241 30.0523 29.9901 33.9183 29.9901C37.7843 29.9901 39.9183 31.1241 39.9183 34.9901Z" fill="white"/>
                            <path d="M38.4184 29.9902L35.0323 28.9901C32.5831 27.7809 28.6275 29.4615 27.4183 31.9106L26.9183 33.8999C25.7058 36.3558 26.9693 37.7809 29.4184 38.9901L33.4184 39.9901C35.8676 41.1992 38.1727 39.7783 39.3819 37.3292L40.6156 34.6939C41.8315 32.2481 40.8675 31.1993 38.4184 29.9902ZM31.4318 37.8989C31.1403 37.9977 30.6772 38.0034 30.3882 37.9109L28.6167 37.3292C28.5528 37.306 28.489 37.2829 28.4284 37.253C28.1525 37.1168 27.9434 36.8964 27.8514 36.625C27.739 36.2933 27.8042 35.9239 28.0341 35.5938L29.0868 34.0549C29.2634 33.799 29.6244 33.5253 29.9226 33.4299L34.405 31.9106C34.3891 32.0784 34.3731 32.2463 34.3674 32.4443C34.365 32.6189 34.3659 32.8034 34.3802 32.9778C34.391 33.1254 34.4119 33.2696 34.4326 33.3803C34.4501 33.5312 34.4844 33.682 34.5083 33.7692C34.5187 33.8329 34.529 33.8798 34.5358 33.8999C34.6044 34.1848 34.7065 34.4695 34.805 34.6939C34.8288 34.7642 34.8424 34.8044 34.8526 34.8178C34.9136 34.9484 34.9747 35.0789 35.0323 35.1827C35.1001 35.3166 35.1712 35.4437 35.2557 35.5608C35.3504 35.7079 35.4618 35.855 35.5733 36.0021C35.6949 36.1458 35.8063 36.2761 35.9243 36.393L31.4318 37.8989ZM37.884 35.7119L36.9561 36.0321C36.8958 36.0525 36.832 36.0461 36.7782 36.0195C36.758 36.0096 36.7311 35.9963 36.7209 35.9829C35.5477 34.9183 34.9959 33.2902 35.28 31.7317C35.2963 31.6477 35.3564 31.5769 35.4335 31.5564L36.3681 31.2396C37.8958 30.7218 38.8614 31.2236 39.369 32.7211C39.6279 33.485 39.6343 34.099 39.3819 34.6104C39.136 35.0915 38.6478 35.4531 37.884 35.7119Z" fill="#292D32"/>
                            <rect x="5" y="31" width="19" height="3" fill="#707070"/>
                            <defs>
                            <linearGradient id="paint0_linear_314_40" x1="23.1024" y1="8" x2="23.1024" y2="29.0697" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F28A1E"/>
                            <stop offset="1" stop-color="#FE6502"/>
                            </linearGradient>
                            <linearGradient id="paint1_linear_314_40" x1="23.1024" y1="8" x2="23.1024" y2="29.0697" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F28A1E"/>
                            <stop offset="1" stop-color="#FE6502"/>
                            </linearGradient>
                            </defs>
                        </svg>

                        <h4 style="margin-top: 10px;" data-lang="wordle">Wordle</h4>
                    </button>
                </div>
            </div>

            <div id="quiz-game" class="game-template" style="display:none;">
                <!-- <h2>Quiz Game</h2> -->
                <div class="quiz-block">
                    <div class="text-up" id="questionNumber"></div>
                    <img src="https://picsum.photos/id/237/200/300" style="width: 250px; height: 250px; display: block; margin: 0 auto; border-radius: 20px" alt="">
                    <div class="text-up" id="questionText"></div>
                </div>
                <form id="quizForm">
                    <div id="choicesContainer"></div>
                </form>
                <button id="prevBtn">Previous</button>
                <button id="nextBtn">Next</button>
            </div>


            <!-- <div class="quiz-container">
                <div class="header">
                    <a href="#" class="back-button">←</a>
                    <span class="question-number">3/10</span>
                    <span class="share-button">⋮</span>
                </div>
                
                <div class="question">
                    Which soccer team won the FIFA World Cup for the first time?
                </div>

                <div class="options">
                    <label class="option">
                        <input type="radio" name="answer" value="uruguay">
                        <span>Uruguay</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="answer" value="brazil">
                        <span>Brazil</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="answer" value="italy">
                        <span>Italy</span>
                    </label>
                    <label class="option">
                        <input type="radio" name="answer" value="germany">
                        <span>Germany</span>
                    </label>
                </div>

                <button class="submit-button">Submit Answer</button>
            </div> -->

            <div id="guess-who-game" class="game-template" style="display:none;">
                <!-- <h2>Guess Who</h2> -->
                <div id="characterImage"></div>
                <div id="questionPrompt">Ask a question to guess the character's name!</div>
                <input type="text" id="guessInput" placeholder="Enter your guess">
                <button id="submitGuess">Guess</button>
                <div id="feedback"></div>
            </div>

            <div id="card-flip-game" class="game-template" style="display:none;">
                <div id="g"></div>
                <div class="logo">
                    <!-- <p class="info">Click the P to get started.</p> -->
                    <div class="card left">
                        <div class="flipper">
                            <div class="f c1">F</div>
                            <div class="b contentbox" id="stats">
                                <div class="padded">
                                    <h2>Figures</h2>
                                    Looks like you haven't FLIPped yet.
                                    <a href="javascript:;" class="playnow">Play now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card active twist">
                        <div class="flipper">
                            <div class="b f">
                                <div class="c2">L</div>
                            </div>
                        </div>
                    </div>
                    <div class="card left">
                        <div class="flipper">
                            <div class="f c3">I</div>
                            <div class="b contentbox instructions">
                                <div class="padded">
                                    <h2>Instructions</h2>
                                    <p>Press [p] to pause, or [ESC] to abandon game.</p>
                                    <p>Flip is a timed card memory game...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="flipper">
                            <div class="f c4">P</div>
                            <div class="b contentbox levels">
                                <a href="javascript:;" data-level="8" class="play">Casual</a>
                                <a href="javascript:;" data-level="18" class="play">Medium</a>
                                <a href="javascript:;" data-level="32" class="play">Hard</a>
                            </div>
                        </div>
                    </div>
                    <p class="info">Flip works best in Chrome, decent in Firefox, IE10 and Opera;</p>
                </div>
            </div>

            <div id="wordle-game" class="game-template" style="display:none;">
                <!-- <h2>Wordle</h2> -->
                <div id="wordle-grid"></div>
                <input type="text" id="wordleInput" maxlength="5" placeholder="Enter 5-letter word">
                <button id="submitWordleGuess">Submit</button>
                <div id="wordleFeedback"></div>
            </div>
        </div>
    </div>

    <?php require_once 'component/footer.php'; ?>
</body>

</html>