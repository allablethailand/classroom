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
</head>

<body>
    <?php require_once 'component/header.php'; ?>

    <div class="main-content">
        <div class="container-fluid" style="margin-bottom: 7rem;">
            <h1 class="heading-1" id="mini-game-title">Mini Game</h1>
            <div class="divider-1">
                <span></span>
            </div>

            <div class="row" id="game-menu">
                <div class="actions-grid">
                    <button class="action-card" id="btn-quiz-game">
                        <i class="fas fa-trophy"></i>
                        <h4 style="margin-top: 10px;">Quiz</h4>

                    </button>
                    <button class="action-card" id="btn-memory-game">
                        <i class="fas fa-question-circle"></i>
                        <h4 style="margin-top: 10px;">Guess Who</h4>
                    </button>
                    <button class="action-card" id="btn-card-flip-game">
                        <i class="fas fa-puzzle-piece"></i>
                        <h4 style="margin-top: 10px;">Card Flip</h4>

                    </button>
                    <button class="action-card" id="btn-wordle-game">
                        <i class="fas fa-spell-check"></i>
                        <h4 style="margin-top: 10px;">Wordle</h4>
                    </button>
                </div>
            </div>

            <div id="quiz-game" class="game-template" style="display:none;">
                <!-- <h2>Quiz Game</h2> -->
                <div id="questionNumber"></div>
                <div id="questionText"></div>
                <form id="quizForm">
                    <div id="choicesContainer"></div>
                </form>
                <button id="prevBtn">Previous</button>
                <button id="nextBtn">Next</button>
            </div>

            <div id="guess-who-game" class="game-template" style="display:none;">
                <!-- <h2>Guess Who</h2> -->
                <div id="characterImage"></div>
                <div id="questionPrompt">Ask a question to guess the character's name!</div>
                <input type="text" id="guessInput" placeholder="Enter your guess">
                <button id="submitGuess">Guess</button>
                <div id="feedback"></div>
            </div>

            <div id="card-flip-game" class="game-template" style="display:none;">
                <!-- <h2>Card Flip</h2> -->
                <div id="cardContainer"></div>
                <div id="gameStatus"></div>
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