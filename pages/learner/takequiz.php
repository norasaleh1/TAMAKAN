<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: Auth.php?tab=login&msg=" . urlencode("Please log in first."));
  exit();
}

if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
  exit("❌ Invalid quiz ID.");
}

$quizID = (int)$_GET['quiz_id'];

$stmt = $pdo->prepare("
  SELECT q.id AS quizID, t.topicName, u.firstName, u.lastName, u.photoFileName
  FROM quiz q
  JOIN topic t ON q.topicID = t.id
  JOIN user u ON q.educatorID = u.id
  WHERE q.id = :qid
");
$stmt->execute([':qid' => $quizID]);
$quiz = $stmt->fetch();

if (!$quiz) {
  exit("❌ Quiz not found in the database.");
}

/* Helper: resolve user image path with smart fallbacks */
function user_img_src(?string $file): string {
  $name = $file ? basename($file) : 'default.png';
  $candidates = [
    'uploads/' . $name,
    'images/users/' . $name,
    'images/' . $name
  ];
  foreach ($candidates as $rel) {
    if (is_file(__DIR__ . '/' . $rel)) return $rel;
  }
  return 'uploads/default.png';
}

$qStmt = $pdo->prepare("SELECT * FROM quizquestion WHERE quizID = :qid");
$qStmt->execute([':qid' => $quizID]);
$allQuestions = $qStmt->fetchAll();

if (!$allQuestions) {
  exit("⚠️ No questions found for this quiz.");
}

if (count($allQuestions) > 5) {
  $randomKeys = array_rand($allQuestions, 5);
  $selectedQuestions = array_map(fn($k) => $allQuestions[$k], $randomKeys);
} else {
  $selectedQuestions = $allQuestions;
}
?>

<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8" />
  
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Take Quiz</title>
  <link rel="stylesheet" href="header.css" />
  <link rel="stylesheet" href="takequiz.css" />
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="quiz-wrap">
  <div class="quiz-head">
    <h1>Quiz: <?= htmlspecialchars($quiz['topicName']) ?></h1>
    <div class="quiz-edu">
      <img src="<?= htmlspecialchars(user_img_src($quiz['photoFileName'] ?? null)) ?>" alt="Educator">
      <span>Educator: <b><?= htmlspecialchars($quiz['firstName'] . " " . $quiz['lastName']) ?></b></span>
    </div>
  </div>

  <p class="quiz-count">Answer all questions and then submit</p>

  <form id="quizForm" method="post" action="score.php">
    <input type="hidden" name="quiz_id" value="<?= $quizID ?>">

    <?php
    $qNum = 1;
    foreach ($selectedQuestions as $q):
      $qID = $q['id'];
    ?>
      <div class="quiz-card">
        <p><b>Q<?= $qNum++ ?>.</b> <?= nl2br(htmlspecialchars($q['question'])) ?></p>

        <?php if (!empty($q['questionFigureFileName'])): ?>
          <img src="uploads/<?= htmlspecialchars($q['questionFigureFileName']) ?>" class="q-figure" alt="Question Image">
        <?php endif; ?>

        <label class="option"><input type="radio" name="answer[<?= $qID ?>]" value="A" required> <?= htmlspecialchars($q['answerA']) ?></label>
        <label class="option"><input type="radio" name="answer[<?= $qID ?>]" value="B"> <?= htmlspecialchars($q['answerB']) ?></label>
        <label class="option"><input type="radio" name="answer[<?= $qID ?>]" value="C"> <?= htmlspecialchars($q['answerC']) ?></label>
        <label class="option"><input type="radio" name="answer[<?= $qID ?>]" value="D"> <?= htmlspecialchars($q['answerD']) ?></label>
      </div>
    <?php endforeach; ?>

    <div class="quiz-submit">
      <button type="submit" class="btn-submit">Submit</button>
    </div>
  </form>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
