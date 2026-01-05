
$(document).ready(function() {
    $('.action-card').click(function() {
    $('#game-menu').hide();
    switch(this.id) {
        case 'btn-quiz-game':
            $('#quiz-game').show();
            document.getElementById('mini-game-title').innerHTML += ' - Quiz';
            loadQuestion(0); // Start quiz game
            break;
        case 'btn-memory-game':
            $('#guess-who-game').show();
            document.getElementById('mini-game-title').innerHTML += ' - Guess Who';
            break;
        case 'btn-card-flip-game':
            $('#card-flip-game').show();
            document.getElementById('mini-game-title').innerHTML += ' - Card Flip';
            break;
        case 'btn-wordle-game':
            $('#wordle-game').show();
            document.getElementById('mini-game-title').innerHTML += ' - Wordle';
            break;
    }
});

    let currentQuestionIndex = 0;
    let userAnswers = [];
    let questions = [];
    let totalQuestions = 0;

    // Load questions from server via AJAX on start or navigation
    // QUESTION SETUP
    function loadQuestion(index) {
        $.ajax({
            url: 'actions/game.php',
            method: 'POST',
            data: { 
            action: 'getQuizGame', 
            questionIndex: index
            },
            dataType: 'json',
            success: function(response) {
            if(response.success) {
                totalQuestions = response.totalQuestions;
                $('#questionText').text(response.question.text);
                let choicesHtml = '';
                response.question.choices.forEach((choice, i) => {
                const checked = userAnswers[index] === i ? 'checked' : '';
                let choiceNum = choice.split('.')[0]; // Extract number and dot
                let choiceText = choice.substring(choice.indexOf('.') + 1).trim(); // Extract text after number and dot
                choicesHtml += `<label class="option">
                    <input type="radio" name="answer" value="${i}" ${checked}>
                        <span>${choiceNum}.</span> 
                        <span style="margin-left:0.4rem;">
                            ${choiceText}
                        </span>
                </label>`;
                });
                $('#choicesContainer').html(choicesHtml);
                $('#questionNumber').text(`Question ${index + 1} of ${totalQuestions}`);
                currentQuestionIndex = index;
            }
            }
        });
    }

    // const container = document.querySelector('.option'); // parent of .option elements

    // container.addEventListener('click', (e) => {
    // if (e.target.classList.contains('option')) {
    //     // remove selected class from all options
    //     document.querySelectorAll('.option').forEach(opt => opt.classList.remove('selected'));
    //     // add selected class to clicked option
    //     e.target.classList.add('selected');
    // }
    // });

    const choicesContainer = document.getElementById('choicesContainer');

    choicesContainer.addEventListener('click', (event) => {
        if (event.target.classList.contains('option')) {
            // Remove 'selected' from all options
            const options = choicesContainer.querySelectorAll('.option');
            options.forEach(opt => opt.classList.remove('selected'));

            // Add 'selected' to the clicked option
            event.target.classList.add('selected');
        }
    });


  // Save user answer on choice selection
  $(document).on('change', 'input[name="answer"]', function() {
    const selectedAnswer = parseInt($(this).val());
    userAnswers[currentQuestionIndex] = selectedAnswer;
    // Optionally validate answer immediately via AJAX
    $.ajax({
      url: 'game.php',
      method: 'POST',
      data: { 
        action: 'submitAnswer',
        questionIndex: currentQuestionIndex, 
        selectedAnswer: selectedAnswer },
      dataType: 'json',
      success: function(resp) {
        if(resp.correct) {
          alert('Correct!');
        } else {
          alert('Wrong answer.');
        }
      }
    });
  });

  // Next question button
  $('#nextBtn').click(function() {
    if(currentQuestionIndex < totalQuestions - 1) {
      loadQuestion(currentQuestionIndex + 1);
    }
  });

  // Previous question button
  $('#prevBtn').click(function() {
    if(currentQuestionIndex > 0) {
      loadQuestion(currentQuestionIndex - 1);
    }
  });

    totalQuestions = questions.length;
    userAnswers = Array(totalQuestions).fill(null);
    // Initial load of first question
    loadQuestion(0);





    // FLIP CARD GAME MENU

    document.getElementById('btn-card-flip-game').addEventListener('click', function() {
  // Hide other games
  document.querySelectorAll('.game-template').forEach(div => div.style.display = 'none');
  // Show flip card game
  document.getElementById('card-flip-game').style.display = 'block';

  // Initialize Flip Game (run your codepen JS init function)
  if (typeof initFlipGame === 'function') initFlipGame();
});
   let flipGameInitialized = false;



    // FLIP CARD GAME CODE   
   function initFlipGame() {
    if(flipGameInitialized) return;
    flipGameInitialized = true;
  
    function set(key, value) { localStorage.setItem(key, value); }
    function get(key)        { return localStorage.getItem(key); }
    function increase(el)    { set(el, parseInt( get(el) ) + 1); }
    function decrease(el)    { set(el, parseInt( get(el) ) - 1); }

    var toTime = function(nr){
        if(nr == '-:-') return nr;
        else { var n = ' '+nr/1000+' '; return n.substr(0, n.length-1)+'s'; }
    };

    function updateStats(){
        $('#card-flip-game #stats').html('<div class="padded"><h2>Figures: <span>'+
        '<b>'+get('flip_won')+'</b><i>Won</i>'+
        '<b>'+get('flip_lost')+'</b><i>Lost</i>'+
        '<b>'+get('flip_abandoned')+'</b><i>Abandoned</i></span></h2>'+
        '<ul><li><b>Best Casual:</b> <span>'+toTime( get('flip_casual') )+'</span></li>'+
        '<li><b>Best Medium:</b> <span>'+toTime( get('flip_medium') )+'</span></li>'+
        '<li><b>Best Hard:</b> <span>'+toTime( get('flip_hard') )+'</span></li></ul>'+
        '<ul><li><b>Total Flips:</b> <span>'+parseInt( ( parseInt(get('flip_matched')) + parseInt(get('flip_wrong')) ) * 2)+'</span></li>'+
        '<li><b>Matched Flips:</b> <span>'+get('flip_matched')+'</span></li>'+
        '<li><b>Wrong Flips:</b> <span>'+get('flip_wrong')+'</span></li></ul></div>');
    };

    function shuffle(array) {
        var currentIndex = array.length, temporaryValue, randomIndex;
        while (0 !== currentIndex) {
            randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex -= 1;
            temporaryValue = array[currentIndex];
            array[currentIndex] = array[randomIndex];
            array[randomIndex] = temporaryValue;
        }
        return array;
    };

    function startScreen(text){
        $('#card-flip-game #g').removeAttr('class').empty();
        $('#card-flip-game .logo').fadeIn(250);

        $('.c1').text(text.substring(0, 1));
        $('.c2').text(text.substring(1, 2));
        $('.c3').text(text.substring(2, 3));
        $('.c4').text(text.substring(3, 4));

        // If won game
        if(text == 'nice'){
        increase('flip_won');
        decrease('flip_abandoned');
        }

        // If lost game
        else if(text == 'fail'){
        increase('flip_lost');
        decrease('flip_abandoned');
        }

        // Update stats
        updateStats();
    };

    /* LOAD GAME ACTIONS */

    // Init localStorage
    if( !get('flip_won') && !get('flip_lost') && !get('flip_abandoned') ){
        //Overall Game stats
        set('flip_won', 0);
        set('flip_lost', 0);
        set('flip_abandoned', 0);
        //Best times
        set('flip_casual', '-:-');
        set('flip_medium', '-:-');
        set('flip_hard', '-:-');
        //Cards stats
        set('flip_matched', 0);
        set('flip_wrong', 0);
    }

    // Fill stats
    if( get('flip_won') > 0 || get('flip_lost') > 0 || get('flip_abandoned') > 0) {updateStats();}

    // Toggle start screen cards
    $('#card-flip-game .logo .card:not(".twist")').on('click', function(e){
        $(this).toggleClass('active').siblings().not('.twist').removeClass('active');
        if( $(e.target).is('.playnow') ) { $('#card-flip-game .logo .card').last().addClass('active'); }
    });

    // Start game
    $('#card-flip-game .play').on('click', function(){
        increase('flip_abandoned');
            $('.info').fadeOut();

        var difficulty = '',
            timer      = 1500,
            level      = $(this).data('level');

        // Set game timer and difficulty   
        if     (level ==  8) { difficulty = 'casual'; timer *= level * 4; }
        else if(level == 18) { difficulty = 'medium'; timer *= level * 5; }
        else if(level == 32) { difficulty = 'hard';   timer *= level * 6; }	    

        $('#card-flip-game #g').addClass(difficulty);

        $('#card-flip-game .logo').fadeOut(250, function(){
        var startGame  = $.now(),
            obj = [];

        // Create and add shuffled cards to game
        for(i = 0; i < level; i++) { obj.push(i); }

        var shu      = shuffle( $.merge(obj, obj) ),
            cardSize = 100/Math.sqrt(shu.length);


        let pairsCount = shu.length / 2;
        let emojiPairs = [];

        for(let i = 0; i < pairsCount; i++) {
            let randomEmojiCode = Math.floor(Math.random() * (0x1F64F - 0x1F600 + 1)) + 0x1F600;
            let emojiChar = String.fromCodePoint(randomEmojiCode);
            emojiPairs.push(emojiChar, emojiChar); // Push pairs
        }

        emojiPairs = shuffle(emojiPairs);

        // Then create cards from emojiPairs instead of shu values
        for(let i = 0; i < shu.length; i++) {
            $('<div class="card" style="width:'+70+'px;height:'+70+'px;">'+
            '<div class="flipper"><div class="f"></div><div class="b" data-f="'+emojiPairs[i]+'"></div></div>'+
            '</div>').appendTo('#g');
        }
        
        // Set card actions
        $('#card-flip-game #g .card').on({
            'click touchstart' : function(e){
             e.preventDefault(); // Prevent double fire on mobile
            if($('#card-flip-game #g').attr('data-paused') == 1) {return;}
            var data = $(this).addClass('active').find('.b').attr('data-f');

            if( $('#card-flip-game #g').find('.card.active').length > 1){
                setTimeout(function(){
                var thisCard = $('#g .active .b[data-f='+data+']');

                if( thisCard.length > 1 ) {
                    thisCard.parents('.card').toggleClass('active card found').empty(); //yey
                    increase('flip_matched');

                    // Win game
                    if( !$('#card-flip-game #g .card').length ){
                    var time = $.now() - startGame;
                    if( get('flip_'+difficulty) == '-:-' || get('flip_'+difficulty) > time ){
                        set('flip_'+difficulty, time); // increase best score
                    }

                    startScreen('nice');
                    }
                }
                else {
                    $('#g .card.active').removeClass('active'); // fail
                    increase('flip_wrong');
                }
                }, 401);
            }
            }
        });

        // Add timer bar
        $('<i class="timer"></i>')
            .prependTo('#g')
            .css({
            'animation' : 'timer '+timer+'ms linear'
            })
            .one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function(e) {
            startScreen('fail'); // fail game
            });

        // Set keyboard (p)ause and [esc] actions
        $(window).off().on('keyup', function(e){
            // Pause game. (p)
            if(e.keyCode == 80){
            if( $('#card-flip-game #g').attr('data-paused') == 1 ) { //was paused, now resume
                $('#card-flip-game #g').attr('data-paused', '0');
                $('.timer').css('animation-play-state', 'running');
                $('.pause').remove();
            }
            else {
                $('#card-flip-game #g').attr('data-paused', '1');
                $('.timer').css('animation-play-state', 'paused');
                $('<div class="pause"></div>').appendTo('body');
            }
            }
            // Abandon game. (ESC)
            if(e.keyCode == 27){
            startScreen('flip');
            // If game was paused
            if( $('#card-flip-game #g').attr('data-paused') == 1 ){
                $('#card-flip-game #g').attr('data-paused', '0');
                $('.pause').remove();
            }
            $(window).off();
            }
        });
        });
    });
        
        

    // Paste CodePen JS code here, replace $(function(){ ... }) with this function body
    }
});
