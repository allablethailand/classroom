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
            'image' => 'images/question1.jpg',
            'choices' => ['A. Cat', 'B. Dog', 'C. Rabbit', 'D. Bird'],
            'correct' => 1
        ],
        [
            'text' => 'Which color is dominant?',
            'image' => 'images/question2.jpg',
            'choices' => ['A. Red', 'B. Blue', 'C. Green', 'D. Yellow'],
            'correct' => 2
        ],
        [
            'text' => 'Identify the object.',
            'image' => 'images/question3.jpg',
            'choices' => ['A. Car', 'B. Boat', 'C. Plane', 'D. Train'],
            'correct' => 0
        ],
        [
            'text' => 'What is shown in this picture?',
            'image' => 'images/question1.jpg',
            'choices' => ['A. Cat', 'B. Dog', 'C. Rabbit', 'D. Bird'],
            'correct' => 1
        ],
        [
            'text' => 'Which color is dominant?',
            'image' => 'images/question2.jpg',
            'choices' => ['A. Red', 'B. Blue', 'C. Green', 'D. Yellow'],
            'correct' => 2
        ],
        [
            'text' => 'Identify the object.',
            'image' => 'images/question3.jpg',
            'choices' => ['A. Car', 'B. Boat', 'C. Plane', 'D. Train'],
            'correct' => 0
        ],
        [
            'text' => 'What is shown in this picture?',
            'image' => 'images/question1.jpg',
            'choices' => ['A. Cat', 'B. Dog', 'C. Rabbit', 'D. Bird'],
            'correct' => 1
        ],
        [
            'text' => 'Which color is dominant?',
            'image' => 'images/question2.jpg',
            'choices' => ['A. Red', 'B. Blue', 'C. Green', 'D. Yellow'],
            'correct' => 2
        ],
        [
            'text' => 'Identify the object.',
            'image' => 'images/question3.jpg',
            'choices' => ['A. Car', 'B. Boat', 'C. Plane', 'D. Train'],
            'correct' => 0
        ],
        [
            'text' => 'Identify the object.',
            'image' => 'images/question3.jpg',
            'choices' => ['A. Car', 'B. Boat', 'C. Plane', 'D. Train'],
            'correct' => 0
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

if ($_POST['action'] == 'submitAnswer') {
    $questionIndex = $_POST['questionIndex'];
    $selectedAnswer = $_POST['selectedAnswer'];

    // Here you would typically validate the answer against the database
    // For demonstration, let's assume every answer is correct if it's "A"
    $isCorrect = ($selectedAnswer === 'A');

    echo json_encode(['correct' => $isCorrect]);
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
