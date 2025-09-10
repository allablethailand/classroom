<?php
// Example data - replace with your own
$backgroundColor = '#C8E6E0';
$rating = 100;
$category = 'Education';
$title = 'Learn PHP, HTML, and CSS from scratch';
$memberAvatars = ['AB', 'CD', 'EF', 'GH']; // initials or letters in place of avatars
$participants = 100;


$backgroundColor_two = '#E6E0C8';
$rating_two = 20;
$category_two = 'Web Development';
$title_two = 'Modern Frontend Frameworks';
$memberAvatars_two = ['N', 'O', 'P']; // initials or letters in place of avatars
$participants_two = 20;


$courses = [
    [
        'id' => 1,
        'title' => 'Introduction to PHP',
        'instructor' => 'Kohn Doe',
        'bannerImage' => '',
        'instructorImage' => '',
        'category' => 'Programming',
        'participants' => 100,
    ],
    [
        'id' => 2,
        'title' => 'Mastering CSS Grid',
        'instructor' => 'Jane Smith',
        'bannerImage' => '',
        'instructorImage' => '',
        'category' => 'Design',
        'participants' => 80,
    ],
    [
        'id' => 3,
        'title' => 'TESTT',
        'instructor' => 'Molly Maybe',
        'bannerImage' => '',
        'instructorImage' => '',
        'category' => 'Graphic Design',
        'participants' => 50,
    ],
    [
        'id' => 4,
        'title' => 'HELLO WORLD',
        'instructor' => 'Steave Gen',
        'bannerImage' => '',
        'instructorImage' => '',
        'category' => 'Management',
        'participants' => 20,
    ],
];



function renderCourseCard($course)
{
    $initial = mb_substr($course['instructor'], 0, 1);
?>
    <a href="classinfo">
    <div class="card course-card" role="region" aria-label="<?php echo htmlspecialchars($course['title']); ?>" tabindex="0">
        <!-- <div class="card-banner" style="background-image: url('<?php echo htmlspecialchars($course['bannerImage']); ?>'), url('dist/images/card_banner.jpg');"> -->
        <div class="card-banner" style="background-color: #AAA8FA;">
            <div class="card-header">
                <div class="rating" aria-label="Rating">
                    <svg class="nav-icon" fill="currentColor" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 508 508" xml:space="preserve">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <g>
                                <g>
                                    <path d="M108.6,143.45c-30.2,0-54.7,24.5-54.7,54.7c0,30.1,24.5,54.7,54.7,54.7c30.2,0,54.7-24.5,54.7-54.7 C163.3,167.95,138.8,143.45,108.6,143.45z M108.6,224.65c-14.6,0-26.5-11.9-26.5-26.5c-0.1-14.6,11.8-26.5,26.5-26.5 c14.6,0,26.5,11.9,26.5,26.5C135.1,212.75,123.2,224.65,108.6,224.65z"></path>
                                </g>
                            </g>
                            <g>
                                <g>
                                    <path d="M254,113.55c-31.3,0-56.8,25.5-56.8,56.8s25.5,56.8,56.8,56.8s56.8-25.5,56.8-56.8S285.3,113.55,254,113.55z M254,197.85 c-15.2,0-27.5-12.4-27.5-27.5c0-15.1,12.3-27.5,27.5-27.5s27.5,12.4,27.5,27.5C281.5,185.55,269.1,197.85,254,197.85z"></path>
                                </g>
                            </g>
                            <g>
                                <g>
                                    <path d="M399.5,143.45c-30.2,0-54.7,24.5-54.7,54.7c0,30.1,24.5,54.7,54.7,54.7s54.7-24.5,54.7-54.7 C454.2,167.95,429.7,143.45,399.5,143.45z M399.5,224.65c-14.6,0-26.5-11.9-26.5-26.5c0-14.6,11.9-26.5,26.5-26.5 c14.6,0,26.5,11.9,26.5,26.5C426,212.75,414.1,224.65,399.5,224.65z"></path>
                                </g>
                            </g>
                            <g>
                                <g>
                                    <path d="M399.5,271.85c-17.7,0-35.2,4.4-50.8,12.7c-12.1-15.3-29.3-26.4-49.4-30.4c-29.7-5.9-60.7-5.9-90.4,0 c-20,4-37.3,15.1-49.4,30.4c-15.6-8.3-33.1-12.7-50.8-12.7c-60,0-108.7,48.7-108.7,108.5c0,7.8,6.3,14.1,14.1,14.1h479.8 c7.8,0,14.1-6.3,14.1-14.1C508,320.55,459.3,271.85,399.5,271.85z M140.8,338.15v28.2H29.5c6.7-37.6,39.6-66.2,79.1-66.2 c13,0,25.7,3.3,37.1,9.2C142.5,318.35,140.8,328.05,140.8,338.15z M339,366.25H169v-28.2c0-27.4,19.1-51,45.3-56.3 c26.1-5.2,53.4-5.2,79.5,0c26.2,5.3,45.2,29,45.2,56.4V366.25z M367.3,366.25v-28.2c0-10-1.7-19.7-4.9-28.8 c11.4-6,24.2-9.2,37.2-9.2c39.5,0,72.4,28.6,79.1,66.2H367.3z"></path>
                                </g>
                            </g>
                        </g>
                    </svg>
                    <span><?php echo htmlspecialchars($course['participants']); ?></span>
                </div>
                <div class="icon-wrapper" aria-label="Icon">
                    <svg class="featured-more-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                    <!-- <button>
                            </button> -->
                </div>

            </div>


            <div class="avatar-wrapper" title="<?php echo htmlspecialchars($course['instructor']); ?>">
                <?php if (!empty($course['instructorImage'])): ?>
                    <img src="<?php echo htmlspecialchars($course['instructorImage']); ?>" alt="<?php echo htmlspecialchars($course['instructor']); ?>" class="avatar-img" />
                <?php else: ?>
                    <?php echo htmlspecialchars($initial); ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-content">
            <?php if (!empty($course['category'])): ?>
                <span class="category-chip"><?php echo htmlspecialchars($course['category']); ?></span>
            <?php endif; ?>
            <h3 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h3>
            <p class="card-instructor"><?php echo htmlspecialchars($course['instructor']); ?></p>
        </div>



    </div>

    </a>
<?php
}
?>

<!-- click to view member course-detail, courese currecilum -->



<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/images/logo_new.ico" type="image/x-icon">
    <title>Class • ORIGAMI SYSTEM</title>
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
    <link rel="stylesheet" href="/classroom/study/css/class.css?v=<?php echo time(); ?>">
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
    <script src="/classroom/study/js/class.js?v=<?php echo time(); ?>" type="text/javascript"></script>
</head>

<body>
    <?php require_once 'component/header.php'; ?>
    <div class="min-vh-100 bg-ori-gray">
        <div class="container-fluid" style="margin-bottom: 7rem;">
            <div class="text-center mb-4" style="margin-top: 2rem;">
                <!-- <h1 class="display-4 fw-bold text-dark mb-bs-5 text-center">
                    Classroom
                </h1> -->
            </div>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <?php renderCourseCard($course); ?>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <?php require_once 'component/footer.php'; ?>

</body>


</html>