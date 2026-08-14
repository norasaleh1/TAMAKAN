<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "false";
    exit;
}

$id       = $_POST['id'];
$quizID   = $_POST['quizID'];
$approve  = $_POST['approve'] ?? "";
$comment  = $_POST['comment'] ?? "";

$status = ($approve === "yes") ? "approved" : "disapproved";

$update = $pdo->prepare("
    UPDATE recommendedquestion
    SET status = :status,
        comments = :c
    WHERE id = :id
");

if(!$update->execute([
    ':status' => $status,
    ':c'      => $comment,
    ':id'     => $id
])){
    echo "false";
    exit;
}

if ($approve === "yes") {

    $copy = $pdo->prepare("
        INSERT INTO quizquestion 
            (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
        SELECT quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer
        FROM recommendedquestion
        WHERE id = :id
    ");

    if(!$copy->execute([':id' => $id])){
        echo "false";
        exit;
    }
}

echo "true";
exit;
