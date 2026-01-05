<?php
session_start();
$base_include = $_SERVER['DOCUMENT_ROOT'];
$base_path = '';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $exl_path = explode('/', $request_uri);
    if (!file_exists($base_include . "/dashboard.php")) {
        $base_path .= "/" . $exl_path[1];
    }
    $base_include .= "/" . $exl_path[1];
}
define('BASE_PATH', $base_path);
define('BASE_INCLUDE', $base_include);
require_once $base_include . '/lib/connect_sqli.php';
require_once $base_include . '/classroom/study/actions/student_func.php';

if (isset($_POST) && $_POST['action'] == 'getQuizGame') {
    // Mock question data for quiz game
    $questions = [
        [
            'text' => 'What is shown in this picture?',
            'image' => 'https://picsum.photos/id/237/400/300',
            'choices' => ['A. Cat', 'B. Dog', 'C. Rabbit', 'D. Bird'],
            'correct' => 1
        ],
        [
            'text' => 'Which color is dominant?',
            'image' => 'https://picsum.photos/id/1025/400/300',
            'choices' => ['A. Red', 'B. Blue', 'C. Green', 'D. Yellow'],
            'correct' => 2
        ],
        [
            'text' => 'Identify the object.',
            'image' => 'https://picsum.photos/id/1/400/300',
            'choices' => ['A. Car', 'B. Boat', 'C. Plane', 'D. Train'],
            'correct' => 0
        ],
        [
            'text' => 'What is shown in this picture?',
            'image' => 'https://picsum.photos/id/237/400/300',
            'choices' => ['A. Cat', 'B. Dog', 'C. Rabbit', 'D. Bird'],
            'correct' => 1
        ],
        [
            'text' => 'Which color is dominant?',
            'image' => 'https://picsum.photos/id/1025/400/300',
            'choices' => ['A. Red', 'B. Blue', 'C. Green', 'D. Yellow'],
            'correct' => 2
        ],
        [
            'text' => 'Identify the object.',
            'image' => 'https://picsum.photos/id/1/400/300',
            'choices' => ['A. Car', 'B. Boat', 'C. Plane', 'D. Train'],
            'correct' => 0
        ],
        [
            'text' => 'What is the main subject?',
            'image' => 'https://picsum.photos/id/100/400/300',
            'choices' => ['A. Landscape', 'B. Portrait', 'C. Animal', 'D. Building'],
            'correct' => 0
        ],
        [
            'text' => 'Describe the scene.',
            'image' => 'https://picsum.photos/id/1015/400/300',
            'choices' => ['A. Urban', 'B. Rural', 'C. Coastal', 'D. Mountain'],
            'correct' => 1
        ],
        [
            'text' => 'What is the weather like?',
            'image' => 'https://picsum.photos/id/1018/400/300',
            'choices' => ['A. Sunny', 'B. Cloudy', 'C. Rainy', 'D. Snowy'],
            'correct' => 0
        ],
        [
            'text' => 'Identify the time of day.',
            'image' => 'https://picsum.photos/id/1035/400/300',
            'choices' => ['A. Morning', 'B. Afternoon', 'C. Evening', 'D. Night'],
            'correct' => 2
        ],
        [
            'text' => 'What type of image is this?',
            'image' => 'https://picsum.photos/id/1043/400/300',
            'choices' => ['A. Nature', 'B. Architecture', 'C. Abstract', 'D. People'],
            'correct' => 0
        ],
        [
            'text' => 'Choose the best description.',
            'image' => 'https://picsum.photos/id/1067/400/300',
            'choices' => ['A. Busy', 'B. Peaceful', 'C. Dramatic', 'D. Mysterious'],
            'correct' => 1
        ],
        [
            'text' => 'Final question - describe the mood.',
            'image' => 'https://picsum.photos/id/1074/400/300',
            'choices' => ['A. Happy', 'B. Serene', 'C. Energetic', 'D. Melancholic'],
            'correct' => 1
        ]
    ];

    // Use question index sent from frontend or default to 0
    $questionIndex = isset($_POST['questionIndex']) ? intval($_POST['questionIndex']) : 0;

    // Validate questionIndex boundaries
    if ($questionIndex >= 0 && $questionIndex < count($questions)) {
        $question = $questions[$questionIndex];
        $response = [
            'success' => true,
            'totalQuestions' => count($questions),
            'question' => [
                'text' => $question['text'],
                'image' => $question['image'],
                'choices' => $question['choices'],
                'correct' => $question['correct']  // You may omit this in response in real use for security
            ]
        ];
    } else {
        $response = ['success' => false, 'error' => 'Invalid question index'];
    }

    echo json_encode($response);
}

if (isset($_POST['action']) && $_POST['action'] == 'submitAnswer') {
    $questionIndex = $_POST['questionIndex'];
    $selectedAnswer = $_POST['selectedAnswer'];

    // Here you would typically validate the answer against the database
    // For demonstration, let's assume every answer is correct if it's "A"
    $isCorrect = ($selectedAnswer === 'A');

    echo json_encode(['correct' => $isCorrect]);
}

if (isset($_POST['action']) && $_POST['action'] == 'submitQuiz') {
    $answers = isset($_POST['answers']) ? $_POST['answers'] : [];
    
    // Get correct answers from questions array
    $questions = [
        ['correct' => 1], ['correct' => 2], ['correct' => 0], ['correct' => 0],
        ['correct' => 1], ['correct' => 0], ['correct' => 2], ['correct' => 0],
        ['correct' => 1], ['correct' => 1]
    ];
    
    $score = 0;
    $total = count($questions);
    
    foreach ($answers as $index => $selectedAnswer) {
        if (isset($questions[$index]) && $questions[$index]['correct'] == $selectedAnswer) {
            $score++;
        }
    }
    
    $response = [
        'success' => true,
        'score' => $score,
        'total' => $total,
        'percentage' => round(($score / $total) * 100, 2)
    ];
    
    echo json_encode($response);
}

if ($_POST['action'] == 'getMemoryGame') {
    // $student_id = $_POST['student_id'];
    // $classroom_id = $_POST['classroom_id'];

    // $item = select_data(
    //     "g.group_id, g.group_name, g.group_logo, g.group_color",
    //     "student s
    //     JOIN classroom_student cs ON s.student_id = cs.student_id
    //     JOIN classroom_group g ON cs.group_id = g.group_id",
    //     "WHERE s.student_id = {$student_id} AND cs.classroom_id = {$classroom_id} AND cs.status = 1"
    // );

    // if($item){
    //     echo json_encode(['success' => true, 'data' => $item[0]]);
    // } else {
    //     echo json_encode(['error' => 'Group not found']);
    // }



}

if ($_POST['action'] == 'getPuzzleGame') {
    $student_id = $_POST['student_id'];
    $classroom_id = $_POST['classroom_id'];

    $item = select_data(
        "g.group_id, g.group_name, g.group_logo, g.group_color",
        "student s
            JOIN classroom_student cs ON s.student_id = cs.student_id
            JOIN classroom_group g ON cs.group_id = g.group_id",
        "WHERE s.student_id = {$student_id} AND cs.classroom_id = {$classroom_id} AND cs.status = 1"
    );

    if ($item) {
        echo json_encode(['success' => true, 'data' => $item[0]]);
    } else {
        echo json_encode(['error' => 'Group not found']);
    }
}
