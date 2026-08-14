<?php
session_start();
require __DIR__ . '/includes/config.php';

if (empty($_SESSION['user_id']) || (($_SESSION['userType'] ?? null) !== 'educator')) {
    $_SESSION['flash_error'] = 'Account not found. Please log in again';
    redirect('Auth.php?tab=login');
}

$quizID = filter_input(INPUT_GET, 'quizID', FILTER_VALIDATE_INT);
if (!$quizID) {
    redirect('quiz.php');
}

$topicName = null;
$q = $pdo->prepare("
  SELECT t.topicName
  FROM quiz q
  JOIN topic t ON t.id = q.topicID
  WHERE q.id = ?
");
$q->execute([$quizID]);
if ($row = $q->fetch()) $topicName = $row['topicName'];
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Add Question</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="header.css" />
  <link rel="stylesheet" href="AddQ.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
  <section class="card narrow">
    <h1 class="page-title">Add New Question</h1>

    <?php if ($topicName): ?>
      <p class="muted" style="margin-top:-8px">Topic: <strong><?= htmlspecialchars($topicName) ?></strong></p>
    <?php endif; ?>

    <form action="addQ_process.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="quizID" value="<?= (int)$quizID ?>"/>

      <div class="field">
        <label>Question</label>
        <textarea name="question" rows="5" required></textarea>
      </div>

      <div class="field">
        <label>Question Figure (optional)</label>
        <input name="figure" type="file" accept="image/*">
      </div>

      <div class="grid">
        <div class="field"><label>Answer A</label><input name="answerA" required></div>
        <div class="field"><label>Answer B</label><input name="answerB" required></div>
        <div class="field"><label>Answer C</label><input name="answerC" required></div>
        <div class="field"><label>Answer D</label><input name="answerD" required></div>
      </div>

      <div class="field inline">
        <label>Correct Answer</label>
        <select name="correct" required>
          <option value="" disabled selected>Select…</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>
      </div>

      <div class="actions">
        <button type="submit" class="btn primary">Add</button>
        <a href="quiz.php?quizID=<?= (int)$quizID ?>" class="btn secondary">Cancel</a>
      </div>
    </form>
  </section>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
