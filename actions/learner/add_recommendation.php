<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username   = "root";
$password   = "root"; 
$database   = "tamakan";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>");
}

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $learnerID = $_SESSION['user_id'];
    $topicID   = $_POST['topicID'] ?? '';
    $educatorID = $_POST['educatorID'] ?? '';
    $question  = trim($_POST['question'] ?? '');
    $answerA   = trim($_POST['answerA'] ?? '');
    $answerB   = trim($_POST['answerB'] ?? '');
    $answerC   = trim($_POST['answerC'] ?? '');
    $answerD   = trim($_POST['answerD'] ?? '');
    $correct   = $_POST['correctAnswer'] ?? '';

    $figureFileName = null;
    if (!empty($_FILES['figure']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['figure']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('fig_', true) . '.' . $ext;
        $targetPath = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['figure']['tmp_name'], $targetPath)) {
            $figureFileName = $newName;
        }
    }

    $quizStmt = $pdo->prepare("SELECT id FROM quiz WHERE educatorID = :edu AND topicID = :topic LIMIT 1");
    $quizStmt->execute([':edu' => $educatorID, ':topic' => $topicID]);
    $quiz = $quizStmt->fetch();

    if (!$quiz) {
        die("<p style='color:red'>❌ No matching quiz found for this educator and topic.</p>");
    }

    $quizID = $quiz['id'];

    $insert = $pdo->prepare("
        INSERT INTO recommendedquestion
        (quizID, learnerID, question, questionFigureFileName,
         answerA, answerB, answerC, answerD, correctAnswer, status)
        VALUES
        (:quizID, :learnerID, :question, :figure,
         :a, :b, :c, :d, :correct, 'pending')
    ");

    $insert->execute([
        ':quizID'   => $quizID,
        ':learnerID'=> $learnerID,
        ':question' => $question,
        ':figure'   => $figureFileName,
        ':a'        => $answerA,
        ':b'        => $answerB,
        ':c'        => $answerC,
        ':d'        => $answerD,
        ':correct'  => $correct
    ]);

    header("Location: learnerHome.php?msg=" . urlencode("Recommendation submitted successfully!"));
    exit();
}
?>
