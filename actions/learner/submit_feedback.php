<?php

require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $quizID = (int)($_POST['quiz_id'] ?? 0);
  $rating = $_POST['rating'] ?? null;
  $comments = trim($_POST['comments'] ?? '');

  if ($quizID) {
    $stmt = $pdo->prepare("INSERT INTO quizfeedback (quizID, rating, comments) VALUES (:qid, :r, :c)");
    $stmt->execute([':qid'=>$quizID, ':r'=>$rating, ':c'=>$comments]);
  }
  header("Location: learnerHome.php?msg=" . urlencode("Thank you for your feedback!"));
  exit;
}
?>
