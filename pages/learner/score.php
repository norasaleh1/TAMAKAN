<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: Auth.php?tab=login&msg=" . urlencode("Please log in first."));
  exit();
}

if (isset($_POST['quiz_id']) && is_numeric($_POST['quiz_id'])) {
  $quizID = (int)$_POST['quiz_id'];
} elseif (isset($_GET['quiz_id']) && is_numeric($_GET['quiz_id'])) {
  $quizID = (int)$_GET['quiz_id'];
} else {
  exit("<p style='color:red'>❌ Invalid or missing quiz ID.</p>");
}

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
  exit("<p style='color:red'>❌ Quiz not found in database.</p>");
}

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

$userAnswers = $_POST['answer'] ?? [];

if (empty($userAnswers)) {
  echo "<p style='color:#555;text-align:center'>ℹ️ No answers submitted — showing quiz info only.</p>";
}

$scorePercent = 0;
if (!empty($userAnswers)) {
  $questionIDs = array_keys($userAnswers);
  $in  = str_repeat('?,', count($questionIDs) - 1) . '?';
  $qStmt = $pdo->prepare("SELECT id, correctAnswer FROM quizquestion WHERE id IN ($in)");
  $qStmt->execute($questionIDs);
  $correctAnswers = $qStmt->fetchAll(PDO::FETCH_KEY_PAIR);

  $totalQuestions = count($questionIDs);
  $correctCount = 0;
  foreach ($userAnswers as $qid => $ans) {
    if (isset($correctAnswers[$qid]) && $correctAnswers[$qid] === $ans) {
      $correctCount++;
    }
  }
  $scorePercent = round(($correctCount / $totalQuestions) * 100, 2);

  $insert = $pdo->prepare("INSERT INTO takenquiz (quizID, score) VALUES (:qid, :score)");
  $insert->execute([':qid' => $quizID, ':score' => $scorePercent]);
}

if ($scorePercent >= 80) {
  $video = "videos/Applause.mp4";
  $msg   = "Excellent! Great job ";
} elseif ($scorePercent >= 50) {
  $video = "videos/keep-going.mp4";
  $msg   = "Good effort! Try for higher next time 💪";
} else {
  $video = "videos/Sorry.mp4";
  $msg   = "Don’t give up! Review and try again ";
}
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Quiz Score & Feedback</title>
  <link rel="stylesheet" href="header.css" />
  <link rel="stylesheet" href="score.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="score-wrap">
  <div class="topbar">
    <h1 class="title">Quiz in <?= htmlspecialchars($quiz['topicName']) ?></h1>
    <a href="learnerHome.php" class="back-link">Back to Homepage</a>
  </div>

  <div class="card">
    <h2 class="title">Educator:</h2>
    <div class="edu-line">
      <img 
        src="<?= htmlspecialchars(user_img_src($quiz['photoFileName'] ?? null)) ?>" 
        alt="Educator Photo"
        style="
          width: 100px;
          height: 100px;
          border-radius: 50%;
          object-fit: cover;
          border: 3px solid #AEC3B0;
          box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        "
      >
      <div><b><?= htmlspecialchars($quiz['firstName'] . ' ' . $quiz['lastName']) ?></b></div>
    </div>
  </div>

  <div class="score-grid">
    <section class="card">
      <h2 class="title">Quiz Score:</h2>
      <div class="score-value"><?= $scorePercent ?>%</div>
      <p class="score-msg"><?= $msg ?></p>

      <video class="score-video" autoplay muted playsinline controls>
        <source src="<?= $video ?>" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </section>

    <section class="card">
      <h2 class="title">Feedback about Quiz:</h2>
      <form action="submit_feedback.php" method="post" class="feedback-form">
        <input type="hidden" name="quiz_id" value="<?= $quizID ?>">
        <div class="row">
          <label for="rating">Rating (out of 5)</label>
          <select id="rating" name="rating">
            <option value="">— Optional —</option>
            <?php for($i=1;$i<=5;$i++): ?>
              <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="row">
          <label for="comments">Comments:</label>
          <textarea id="comments" name="comments" rows="6" placeholder="Tell us what went well or what could improve"></textarea>
        </div>
        <div class="actions">
          <button type="submit" class="btn btn-primary">Submit Feedback</button>
          <a href="learnerHome.php" class="btn">Back</a>
        </div>
      </form>
    </section>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>document.getElementById('y').textContent = new Date().getFullYear();</script>
</body>
</html>
