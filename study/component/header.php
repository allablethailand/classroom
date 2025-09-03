<?php

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

if ($currentScreen == 'group')
{
    $currentScreen = 'academy';
}

if (!isset($_SESSION['student_id'])) {
    header("Location: /classroom/study/login");
    exit();
}
?>

<head>
    <link rel="stylesheet" href="/classroom/study/css/header.css?v=<?php echo time(); ?>">
</head>

<div class="orange-header">
    <?php
    if ($currentScreen == 'menu') {
    ?>

        <div class="container-topnav">
            <div class="header-topnav">
                <div class="title-group-topnav">
                    <span>
                        <img src="https://www.trandar.com//public/news_img/Green%20Tech%20Leadership%20(png).png"   alt="error" style="width: 50px; height: 50px; border-radius: 100%;">



                        <!-- <div style="width: 20px; height: 20px; background-color: white; color: green; font-weight: bold; 
                                    font-size: 30px; width: 54px; height: 54px; display: flex; justify-content: center; 
                                    align-items: center; border-radius: 50%; user-select: none;">
                            G
                        </div> -->
                    </span>
                    <div class="">
                        <h1>Green Tech</h1>
                        <p>Hello ! Mukrob Kratium</p>
                    </div>       
                </div>
                <div class="icons">
                    <button class="bell-button" onclick="alert('Notifications');">
                        <span>
                            <i class="far fa-bell" style="font-size: 20px;"></i>
                        </span>
                    </button>
                    <a href="profile" class="" style="background-color: white; border-radius: 100%">
                        
                        <img width="25" id="avatar_h" name="avatar_h" title="test" src="/images/default.png" onerror="this.src='/images/default.png'">
                    </a>

                    <!-- <div id="profile-right">
							<span class="profile-img" style="border:4px solid #FF9900">
								<img width="50" id="avatar_h" name="avatar_h" title="Admin" src="/images/default.png" onerror="this.src='/images/default.png'">
							</span>
                </div> -->
                </div>
            </div>

            <div class="balance-container">
                <p class="balance-label">Group Element:</p>
                <p class="balance-amount"> &nbsp; Fire 🔥</p>
            </div>
        </div>

    <?php
    } else {
    ?>
        <div class="header">
            <button class="back-button" onclick="window.history.back();">
                <span class="back-arrow">←</span>
            </button>
            <h1 class="header-title"><?php echo ucfirst($currentScreen); ?></h1>
            <button class="bell-button" onclick="alert('Notifications');">
                <span>
                    <i class="far fa-bell" style="font-size: 20px;"></i>
                </span>
            </button>
            <div class="header-spacer"></div>
        </div>
    <?php
    }
    ?>

</div>