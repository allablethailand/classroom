<?php
// บรรทัดแรกสุดของไฟล์
session_start();

// โค้ดส่วนอื่นๆ ของหน้าจะเริ่มที่นี่

$transactions = [
    [
        'id' => 1,
        'title' => "Currency exchange",
        'amount' => "$ 350.00",
        'status' => "pending",
        'transactionId' => "698094554317",
        'date' => "17 Sep 2023",
        'time' => "11:21 AM",
        'icon' => "exchange",
    ],
    [
        'id' => 2,
        'title' => "Cash-in",
        'subtitle' => "From ABC Bank ATM",
        'amount' => "$ 100.00",
        'status' => "confirmed",
        'transactionId' => "564925374920",
        'date' => "17 Sep 2023",
        'time' => "10:34 AM",
        'icon' => "plus",
    ],
    [
        'id' => 3,
        'title' => "Cashback from purchase",
        'subtitle' => "Purchase from Amazon.com",
        'amount' => "$ 1.75",
        'status' => "confirmed",
        'transactionId' => "685746354219",
        'date' => "16 Sep 2023",
        'time' => "16:08 PM",
        'icon' => "buy",
    ],
    [
        'id' => 4,
        'title' => "Transfer to card",
        'amount' => "$ 9000.00",
        'status' => "confirmed",
        'transactionId' => "698094554317",
        'date' => "16 Sep 2023",
        'time' => "11:21 AM",
        'icon' => "plus",
    ],
    [
        'id' => 5,
        'title' => "Transfer to card",
        'subtitle' => "Not enough funds",
        'amount' => "$ 9267.00",
        'status' => "canceled",
        'transactionId' => "097967542786",
        'date' => "15 Sep 2023",
        'time' => "10:11 AM",
        'icon' => "plus",
    ],
];

// Function to return an icon HTML placeholder based on icon name
function getIconHtml($icon)
{
    // This is a stub. Replace with actual icon rendering logic, e.g. SVG or <i> with classes.
    return "<span class='icon-$icon'></span>";
}


?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>History • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/history.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/history.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
    <?php require_once 'component/header.php'; ?>
    <div class="main-content" style="margin-top: 10px;  margin-bottom: 5rem;">
        <div class="container-fluid">
                <div class="nav-content">
                    <div class="account-picker">
                        <!-- <div class="account-bg-layer"></div> -->
                        <div class="account-content">
                            <div class="visa-logo">
                                <svg class="icon-svg" viewBox="0 0 52 20">
                                    <path d="M33.4082 0C35.203 0 36.9297 0.673828 36.9297 0.673828L36.3701 3.99414C34.6961 3.04739 30.881 2.81965 30.8809 4.56836C30.8809 6.3172 36.3457 6.83824 36.3457 10.708C36.3457 14.5778 32.3136 16 29.6416 16C26.9931 15.9999 25.25 15.1429 25.2197 15.1279L25.8027 11.6592C27.4084 12.978 32.2656 13.3539 32.2656 11.2578C32.2655 9.16176 26.8496 9.11192 26.8496 5.11816C26.8497 0.872545 31.4644 8.98359e-05 33.4082 0ZM50 15.7314H46.4023V15.7354L45.9434 13.4404H40.9658L40.1494 15.7354H36.0684L41.9072 1.43066C41.9072 1.43066 42.2615 0.277591 43.7139 0.277344H46.8652L50 15.7314ZM22.8086 15.7314H18.8936L21.3398 0.277344H25.2559L22.8086 15.7314ZM8.24121 0.272461C9.93021 0.272461 10.0988 1.65109 10.1006 1.66602L11.4209 8.61621L11.8633 10.9062L15.5977 0.277344H19.8105L13.5811 15.7275H9.50098L6.09766 2.30566C5.1277 1.70712 4.22036 1.29312 3.50684 1.01758C3.47644 1.00583 3.44667 0.993674 3.41699 0.982422C2.55561 0.656189 2 0.537109 2 0.537109L2.00098 0.533203H2L2.07227 0.272461H8.24121ZM42.0928 10.2656H45.3076L44.1494 4.48145L42.0928 10.2656Z" fill="currentColor" />
                                </svg>
                            </div>
                            <div class="account-text">ending with***9749</div>
                            <div class="account-arrow">
                                <svg class="icon-svg" viewBox="0 0 7 8">
                                    <path d="M6.42052 4.754C6.38285 4.79267 6.24062 4.958 6.10813 5.094C5.33139 5.94933 3.30508 7.34933 2.24452 7.77667C2.08346 7.84533 1.67625 7.99067 1.45868 8C1.2502 8 1.05147 7.952 0.861828 7.85467C0.625426 7.71867 0.435785 7.50467 0.331872 7.252C0.264978 7.07667 0.161065 6.552 0.161065 6.54267C0.0571521 5.96867 0 5.036 0 4.00533C0 3.02333 0.0571521 2.12867 0.142231 1.546C0.151973 1.53667 0.255886 0.884667 0.369541 0.661333C0.578016 0.253333 0.985225 0 1.42101 0H1.45868C1.74249 0.01 2.33934 0.263333 2.33934 0.272667C3.34275 0.700667 5.32229 2.032 6.11788 2.91667C6.11788 2.91667 6.34194 3.144 6.43936 3.286C6.59133 3.49 6.66667 3.74267 6.66667 3.99533C6.66667 4.27733 6.58159 4.54 6.42052 4.754Z" fill="currentColor" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <button class="nav-button" id="searchBtn">
                        <svg class="icon-svg" viewBox="0 0 45 45" style="width:150px; height:150px;">
                            <g>
                                <path d="M22.2356 14.7298C25.828 14.7301 28.7405 17.6431 28.7405 21.2356C28.7403 22.616 28.3079 23.8947 27.5745 24.9475L29.9329 27.3069C30.382 27.756 30.382 28.4838 29.9329 28.9329C29.4838 29.382 28.756 29.382 28.3069 28.9329L25.9475 26.5745C24.8946 27.3079 23.616 27.7404 22.2356 27.7405C18.6431 27.7405 15.7301 24.828 15.7297 21.2356C15.7297 17.6429 18.6429 14.7298 22.2356 14.7298ZM22.2356 17.0305C19.9131 17.0305 18.0305 18.9132 18.0305 21.2356C18.0309 23.5578 19.9134 25.4407 22.2356 25.4407C24.5575 25.4403 26.4403 23.5576 26.4407 21.2356C26.4407 18.9134 24.5578 17.0309 22.2356 17.0305Z" fill="#26273A" />
                            </g>
                        </svg>
                    </button>
                    <button class="nav-button" id="filterBtn">
                        <svg class="icon-svg" viewBox="0 0 14 13">
                            <g>
                                <path d="M5.55726 8.90859H1.03659C0.464442 8.90859 -8.3819e-08 9.36489 -8.3819e-08 9.92701C-8.3819e-08 10.4884 0.464442 10.9454 1.03659 10.9454H5.55726C6.12941 10.9454 6.59385 10.4884 6.59385 9.92701C6.59385 9.36489 6.12941 8.90859 5.55726 8.90859Z" ," fill="#26273A" opacity="0.4" />
                                <path d="M13.7501 2.32275C13.7501 1.76138 13.2856 1.30509 12.7142 1.30509H8.19357C7.62142 1.30509 7.15698 1.76138 7.15698 2.32275C7.15698 2.88486 7.62142 3.34116 8.19357 3.34116H12.7142C13.2856 3.34116 13.7501 2.88486 13.7501 2.32275Z" fill="#26273A" opacity="0.4" />
                                <path d="M4.72845 2.32276C4.72845 3.60609 3.67047 4.64627 2.36422 4.64627C1.05874 4.64627 0 3.60609 0 2.32276C0 1.04018 1.05874 0 2.36422 0C3.67047 0 4.72845 1.04018 4.72845 2.32276Z" fill="#26273A" />
                                <path d="M13.75 9.89945C13.75 11.182 12.692 12.2222 11.3858 12.2222C10.0803 12.2222 9.02155 11.182 9.02155 9.89945C9.02155 8.61612 10.0803 7.57594 11.3858 7.57594C12.692 7.57594 13.75 8.61612 13.75 9.89945Z" fill="#26273A" />
                            </g>
                        </svg>
                    </button>

                    <!-- Modal Structure -->
                    <div id="bottomModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" style="position: fixed; bottom: 0; margin: 0; width: 100%; max-width: 100%;">
                            <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title">Filters</h4>
                            </div>
                            <div class="modal-body">
                                <!-- Your modal content here -->
                                <p>Put your filter controls or any content here.</p>
                                <div class="">Period</div>
                                <p>Class Section</p>
                                <div class="gap-4" style="display: flex;">
                                    <button class="col-4">Search Result</button>
                                    <button class="col-4">Search Result</button>
                                    <button class="col-4">Search Result</button>
                                </div>
                                <div class="row" style="margin-inline: 0.7rem;">
                                    <button class="btn btn-primary btn-block">Search Result</button>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid row" style="display: flex; gap: 6px;">
                    <div class="filter-selectors">
                        <div class="filter-text">This Week</div>
                    </div>
                    <div class="filter-selectors">
                        <div class="filter-text">This Month</div>
                    </div>
                    <div class="filter-selectors">
                        <div class="filter-text">This Year</div>
                    </div>
                </div>

                <div class="scrollable-frame">
                    <?php foreach ($transactions as $index => $transaction): ?>
                        <div class="transaction-card card-<?php echo ($index + 1); ?>">
                            <div class="transaction-icon">
                                <div class="transaction-logo">
                                    <?php echo getIconHtml($transaction['icon']); ?>
                                </div>
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-title"><?php echo htmlspecialchars($transaction['title']); ?></div>
                                <?php if (!empty($transaction['subtitle'])): ?>
                                    <div class="transaction-subtitle"><?php echo htmlspecialchars($transaction['subtitle']); ?></div>
                                <?php endif; ?>
                            </div>

                             <!-- <div class="transaction-information">
                                <div class="transaction-title"><?php echo htmlspecialchars($transaction['title']); ?></div>
                                <?php if (!empty($transaction['subtitle'])): ?>
                                    <div class="transaction-subtitle"><?php echo htmlspecialchars($transaction['subtitle']); ?></div>
                                <?php endif; ?>
                            </div> -->

                            <!-- <div class="transaction-info">
                                <span class="transaction-date"><?php echo htmlspecialchars($transaction['date']); ?></span>
                                <span class="transaction-time"><?php echo htmlspecialchars($transaction['time']); ?></span>
                            </div> -->
                        </div>
                    <?php endforeach; ?>
                </div>




        </div>
        <?php require_once 'component/footer.php'; ?>
</body>

</html>