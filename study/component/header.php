<?php
// header.php

// Get current directory or page identifier, example by parsing URL path
$uriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePath = 'classroom/study';

if (strpos($uriPath, $basePath) === 0) {
    // Remove the basePath segment from the path
    $relativePath = trim(substr($uriPath, strlen($basePath)), '/');
} else {
    $relativePath = $uriPath;
}

$segments = explode('/', $relativePath);
$currentScreen = isset($segments[0]) && $segments[0] !== '' ? $segments[0] : 'menu';
?>
<head>
    <link rel="stylesheet" href="/classroom/study/css/header.css?v=<?php echo time(); ?>">
</head>

 <div class="orange-header">
<?php
switch ($currentScreen) {
    case 'study':
        ?>
        <div class="header">
            <button class="back-button" onclick="window.history.back();">
                <span class="back-arrow">←</span>
            </button>
            <h1 class="header-title">Study</h1>
            <button class="bell-button" onclick="alert('Notifications');">
                <span>
                    <i class="far fa-bell" style="font-size: 20px;"></i>
                </span>
            </button>
            <div class="header-spacer"></div>
        </div>
        <?php
        break;

    case 'student':
        ?>
        <div class="header">
            <button class="back-button" onclick="window.history.back();">
                <span class="back-arrow">←</span>
            </button>
            <h1 class="header-title">Student</h1>
            <!-- <button class="bell-button" onclick="alert('Notifications');">
                <span>
                    <i class="far fa-bell" style="font-size: 20px;"></i>
                </span>
            </button> -->
            <div class="header-spacer"></div>
        </div>
        <?php
        break;

    case 'support':
        ?>
        <div class="header">
            <button class="back-button" onclick="window.history.back();">
                <span class="back-arrow">←</span>
            </button>
            <h1 class="header-title">Support</h1>
            <button class="bell-button" onclick="alert('Notifications');">
                <span>
                    <i class="far fa-bell" style="font-size: 20px;"></i>
                </span>
            </button>
            <div class="header-spacer"></div>
        </div>
        <?php
        break;

    default:
        // Default main tab header
        ?>
        <div class="container-topnav">
            <div class="header-topnav">
                <div class="title-group-topnav">
                    <h1>Origami Class</h1>
                    <p>HELLO!</p>
                </div>
                <div class="icons">
                    <span>
                        <i class="far fa-bell" style="font-size: 20px; color:white;"></i>
                    </span>
                </div>
            </div>
    
            <div class="balance-container">
                <p class="balance-label">Group Element:</p>
                <p class="balance-amount">Fire</p>
            </div>
        </div>
        <?php
        break;
}
?>
</div>