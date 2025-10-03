
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
            document.getElementById('mini-game-title').innerHTML += ' - Memory';
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
            choicesHtml += `<label>
                <input type="radio" name="answer" value="${i}" ${checked}> ${choice}
            </label><br>`;
            });
            $('#choicesContainer').html(choicesHtml);
            $('#questionNumber').text(`Question ${index + 1} of ${totalQuestions}`);
            currentQuestionIndex = index;
        }
        }
    });
    }

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

});


//   <div class="text-center mb-4 course-class-info" style="margin-top: 2rem; margin: 1rem">
                
//                 <div class="card group-card h-100 bg-white rounded-small" style=" border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
//                     <div class="panel-heading border-0" style="padding:0;">
//                         <div class="d-flex-bs align-items-center gap-3">
//                             <div class="group-icon-large" style="color: #FFF;">
//                                 <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
//                                 <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
//                             </div>
//                             <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
//                                 <div class="d-flex-bs align-items-center gap-2 mb-1">
//                                     <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
//                                 </div>
//                                 <p class="text-secondary mb-0 small text-truncate-2">
//                                     <?php echo "เกมแรก" ?>
//                                 </p>
//                             </div>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="card group-card h-100 bg-white rounded-small" style=" margin-top: 2rem; margin: 1rem border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
//                     <div class="panel-heading border-0" style="padding:0;">
//                         <div class="d-flex-bs align-items-center gap-3">
//                             <div class="group-icon-large" style="color: #FFF;">
//                                 <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
//                                 <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
//                             </div>
//                             <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
//                                 <div class="d-flex-bs align-items-center gap-2 mb-1">
//                                     <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
//                                 </div>
//                                 <p class="text-secondary mb-0 small text-truncate-2">
//                                     <?php echo "เกมสอง" ?>
//                                 </p>
//                             </div>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="card group-card h-100 bg-white rounded-small" style=" margin-top: 2rem; margin: 1rem border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
//                     <div class="panel-heading border-0" style="padding:0;">
//                         <div class="d-flex-bs align-items-center gap-3">
//                             <div class="group-icon-large" style="color: #FFF;">
//                                 <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
//                                 <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
//                             </div>
//                             <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
//                                 <div class="d-flex-bs align-items-center gap-2 mb-1">
//                                     <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
//                                 </div>
//                                 <p class="text-secondary mb-0 small text-truncate-2">
//                                     <?php echo "เกมสาม" ?>
//                                 </p>
//                             </div>
//                         </div>
//                     </div>
//                 </div>

//                 <div class="card group-card h-100 bg-white rounded-small" style=" margin-top: 2rem; margin: 1rem border-left: 15px solid <?php echo $item['group_color']; ?> !important;">
//                     <div class="panel-heading border-0" style="padding:0;">
//                         <div class="d-flex-bs align-items-center gap-3">
//                             <div class="group-icon-large" style="color: #FFF;">
//                                 <!-- <i class="fas fa-fire-alt" style="width: 50px;"></i> -->
//                                 <img src="<?php echo $item['group_logo']; ?>" class="transparent-bg" alt="error" style="width: 50px; height: 50px; border-radius: 100%;">
//                             </div>
//                             <div class="flex-grow-bs-1" style="min-width: 0; padding-top: 20px">
//                                 <div class="d-flex-bs align-items-center gap-2 mb-1">
//                                     <h4 class="panel-title mb-0 text-truncate d-flex-bs "> <?= $item["group_name"] ?></h4>
//                                 </div>
//                                 <p class="text-secondary mb-0 small text-truncate-2">
//                                     <?php echo "เกมสาม" ?>
//                                 </p>
//                             </div>
//                         </div>
//                     </div>
//                 </div>
//             </div>