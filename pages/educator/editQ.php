<?php
session_start();
require __DIR__ . '/includes/config.php'; 

if (empty($_SESSION['user_id']) || (($_SESSION['userType'] ?? null) !== 'educator')) {
    $_SESSION['flash_error'] = 'Account not found. Please log in again';
    redirect('Auth.php?tab=login');
}

$questionId = null;
if (isset($_GET['question_id']) && ctype_digit((string)$_GET['question_id'])) {
    $questionId = (int) $_GET['question_id'];
} elseif (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $questionId = (int) $_GET['id'];
}

if (!$questionId) {
    http_response_code(400);
    exit('Missing or invalid question_id.');
}

$stmt = $pdo->prepare("
    SELECT id, quizID, question, questionFigureFileName,
           answerA, answerB, answerC, answerD, correctAnswer
    FROM quizquestion
    WHERE id = ?
");
$stmt->execute([$questionId]);
$questionRow = $stmt->fetch();

if (!$questionRow) {
    http_response_code(404);
    exit('Question not found.');
}

$quizId = (int)$questionRow['quizID'];

$topicName = null;
$topicStmt = $pdo->prepare("
    SELECT t.topicName
    FROM quiz q
    JOIN topic t ON t.id = q.topicID
    WHERE q.id = ?
");
$topicStmt->execute([$quizId]);
if ($t = $topicStmt->fetch()) {
    $topicName = $t['topicName'];
}

$errors = [];

if (is_post()) {
    $qText   = trim($_POST['question'] ?? '');
    $ansA    = trim($_POST['answerA'] ?? '');
    $ansB    = trim($_POST['answerB'] ?? '');
    $ansC    = trim($_POST['answerC'] ?? '');
    $ansD    = trim($_POST['answerD'] ?? '');
    $correct = strtoupper(trim($_POST['correct'] ?? ''));

    if ($qText === '')                    $errors[] = 'Question is required.';
    if ($ansA === '' || $ansB === '' ||
        $ansC === '' || $ansD === '')     $errors[] = 'All answers are required.';
    if (!in_array($correct, ['A','B','C','D'], true))
        $errors[] = 'Correct answer must be A, B, C, or D.';

    $oldFigure = $questionRow['questionFigureFileName'] ?? null;
    $newFigure = $oldFigure;

    if (!empty($_FILES['figure']['name'])) {
        if ($_FILES['figure']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['figure']['tmp_name'];
            $imgInfo = @getimagesize($tmp);
            if ($imgInfo === false) {
                $errors[] = 'The uploaded file is not a valid image.';
            } else {
                $ext = strtolower(pathinfo($_FILES['figure']['name'], PATHINFO_EXTENSION));
                if ($ext === 'jpeg') $ext = 'jpg';
                $allowed = ['png','jpg','svg'];
                if (!in_array($ext, $allowed, true)) {
                    $errors[] = 'Only PNG, JPG, or SVG files are allowed.';
                } else {
                    $dir = __DIR__ . '/uploads';
                    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
                    $filename = uniqid('q_', true) . '.' . $ext;
                    if (!move_uploaded_file($tmp, "$dir/$filename")) {
                        $errors[] = 'Image upload failed.';
                    } else {
                        $newFigure = "uploads/$filename";
                        if ($oldFigure && strpos($oldFigure, 'uploads/') === 0) {
                            $absOld = __DIR__ . '/' . $oldFigure;
                            if (is_file($absOld)) { @unlink($absOld); }
                        }
                    }
                }
            }
        } elseif ($_FILES['figure']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Image upload failed.';
        }
    }

    if (!$errors) {
        $upd = $pdo->prepare("
            UPDATE quizquestion
            SET question = :q,
                questionFigureFileName = :fig,
                answerA = :a,
                answerB = :b,
                answerC = :c,
                answerD = :d,
                correctAnswer = :correct
            WHERE id = :id
        ");
        $upd->execute([
            ':q'       => $qText,
            ':fig'     => $newFigure,
            ':a'       => $ansA,
            ':b'       => $ansB,
            ':c'       => $ansC,
            ':d'       => $ansD,
            ':correct' => $correct,
            ':id'      => $questionId,
        ]);

        $paramName = isset($_GET['quizID']) ? 'quizID' : 'quiz_id';
        redirect('quiz.php?quizID=' . urlencode((string)$quizId));
    } else {
        $questionRow['question']           = $qText;
        $questionRow['answerA']            = $ansA;
        $questionRow['answerB']            = $ansB;
        $questionRow['answerC']            = $ansC;
        $questionRow['answerD']            = $ansD;
        $questionRow['correctAnswer']      = $correct;
        $questionRow['questionFigureFileName'] = $newFigure;
    }
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Question</title>
  <link rel="stylesheet" href="header.css" />
  <link rel="stylesheet" href="editQ.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
  <section class="card narrow">
    <h1 class="page-title">Edit Question</h1>

    <?php if (!empty($errors)): ?>
      <div style="background:#ffe9e9;color:#b40000;padding:12px 14px;border-radius:12px;margin-bottom:14px;">
        <strong>Please fix the following:</strong>
        <ul style="margin:8px 0 0 18px;">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

<form class="q-form" action="editQ_process.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="question_id" value="<?= e($questionId) ?>" />

      <?php if ($topicName): ?>
        <div class="field">
          <label>Topic</label>
          <input type="text" value="<?= e($topicName) ?>" disabled>
          <small class="hint">This quiz topic is auto-detected from the question.</small>
        </div>
      <?php endif; ?>

      <div class="field">
        <label for="qtext">Question</label>
        <textarea id="qtext" name="question" rows="5" required><?= e($questionRow['question']) ?></textarea>
      </div>

      <div class="field figure-row">
        <div class="figure-uploader">
          <label for="qfig">Upload Question Figure (optional)</label>
          <input id="qfig" name="figure" type="file" accept="image/*" />
          <small class="hint">Choose a new file to replace the current figure.</small>
        </div>

        <div class="current-figure">
          <div class="label">Current Figure</div>
          <div class="preview" style="max-width:220px;">
            <?php if (!empty($questionRow['questionFigureFileName'])): ?>
              <img src="<?= e($questionRow['questionFigureFileName']) ?>" alt="Current figure" style="max-width:100%;height:auto;">
            <?php else: ?>
              <div style="color:#777;">No image</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="grid">
        <div class="field">
          <label for="ansA">Answer A</label>
          <input id="ansA" name="answerA" type="text" required value="<?= e($questionRow['answerA']) ?>" />
        </div>
        <div class="field">
          <label for="ansB">Answer B</label>
          <input id="ansB" name="answerB" type="text" required value="<?= e($questionRow['answerB']) ?>" />
        </div>
        <div class="field">
          <label for="ansC">Answer C</label>
          <input id="ansC" name="answerC" type="text" required value="<?= e($questionRow['answerC']) ?>" />
        </div>
        <div class="field">
          <label for="ansD">Answer D</label>
          <input id="ansD" name="answerD" type="text" required value="<?= e($questionRow['answerD']) ?>" />
        </div>
      </div>

      <div class="field inline">
        <label for="correct">Correct Answer</label>
        <select id="correct" name="correct" required>
          <?php
            $cur = $questionRow['correctAnswer'];
            foreach (['A','B','C','D'] as $opt) {
              $sel = ($cur === $opt) ? 'selected' : '';
              echo "<option value=\"$opt\" $sel>$opt</option>";
            }
          ?>
        </select>
      </div>

      <div class="actions">
        <button type="submit" class="btn primary">Save</button>
        <?php
          $paramName = isset($_GET['quizID']) ? 'quizID' : 'quiz_id';
        ?>
        <a href="quiz.php?<?= $paramName ?>=<?= e($quizId) ?>" class="btn secondary">Cancel</a>
      </div>
    </form>
  </section>
</main>

<?php include 'footer.php'; ?>

<script>document.getElementById('y').textContent = new Date().getFullYear();</script>
</body>
</html>
