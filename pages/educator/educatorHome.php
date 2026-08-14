<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Auth.php?error=login_required");
    exit;
}
if (($_SESSION['userType'] ?? null) !== 'educator') {
    header("Location: Auth.php?error=not_educator");
    exit;
}

$userId = (int)$_SESSION['user_id'];

/* helpers */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function user_img_src(?string $file): string {
  $name = $file ? basename($file) : 'default.png';
  $full = __DIR__ . '/uploads/' . $name;
  return is_file($full) ? ('uploads/'.$name) : 'uploads/default.png';
}


$stmt = $pdo->prepare("SELECT firstName, lastName, emailAddress, photoFileName FROM `user` WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();


$topicStmt = $pdo->prepare("
    SELECT t.topicName
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    WHERE q.educatorID = :eid
");
$topicStmt->execute([':eid' => $userId]);
$educatorTopics = $topicStmt->fetchAll(PDO::FETCH_COLUMN);


$quizStmt = $pdo->prepare("
    SELECT q.id AS quizID, t.topicName
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    WHERE q.educatorID = :id
");
$quizStmt->execute([':id' => $userId]);
$quizzes = $quizStmt->fetchAll();

$stmtRec = $pdo->prepare("
    SELECT r.*, q.topicID, t.topicName,
           u.firstName AS learnerFirst, u.lastName AS learnerLast,
           u.photoFileName AS learnerPhoto
    FROM recommendedquestion r
    JOIN quiz q   ON r.quizID = q.id
    JOIN topic t  ON q.topicID = t.id
    JOIN `user` u ON r.learnerID = u.id
    WHERE q.educatorID = ?
      AND r.status = 'pending'
    ORDER BY r.id DESC
");
$stmtRec->execute([$userId]);
$recommended = $stmtRec->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Educator Home</title>
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="educator.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    .options .correct { background-color:#d4edda; padding:5px; border-radius:4px; font-weight:bold; }
    .id-photo, .image-learner { border-radius:50%; object-fit:cover; }
  </style>
</head>
<body>

<?php include 'header.php'; ?>

<main>
  <h1>Welcome Educator, <?= e($_SESSION['firstName'] ?? '') ?></h1>

  <div class="card usercard" id="userCard" style="margin-bottom:1rem">
    <div style="display:flex;gap:1rem;align-items:center">
      <div style="padding:8px 25px 8px 33px;">
        <img class="id-photo"
             src="<?= e(user_img_src($user['photoFileName'] ?? null)) ?>"
             width="100" height="100" alt="Educator photo">
      </div>
      <div>
        <div id="user-name"><?= e(($user['firstName'] ?? '').' '.($user['lastName'] ?? '')) ?></div>
        <div id="user-email"><?= e($user['emailAddress'] ?? '') ?></div>
        <div id="Educator-specialty">
          Topics :
          <?php if (!empty($educatorTopics)) {
              echo e(implode(', ', $educatorTopics));
            } else {
              echo 'No topics assigned';
            } ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row-eq">
    <div class="card" style="margin-top:1rem">
      <h2>Your Quizzes</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Topic</th>
            <th>Number of Questions</th>
            <th>Quiz Statistics</th>
            <th>Quiz Feedback</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($quizzes as $q): ?>
          <tr>
            <td>
              <a href="quiz.php?quizID=<?= (int)$q['quizID'] ?>">
                <?= e($q['topicName']) ?>
              </a>
            </td>
            <td>
              <?php
                $countQ = $pdo->query("SELECT COUNT(*) FROM quizquestion WHERE quizID=".(int)$q['quizID'])->fetchColumn();
                echo (int)$countQ;
              ?>
            </td>
            <td>
              <?php
                $stats = $pdo->query("SELECT COUNT(*) AS c, AVG(score) AS avg FROM takenquiz WHERE quizID=".(int)$q['quizID'])->fetch();
                if ((int)$stats['c'] === 0) {
                  echo "quiz not taken yet";
                } else {
                  echo "Number of Quiz Takers: <strong>".(int)$stats['c']."</strong><br>";
                  echo "Average Score: <strong>".round((float)$stats['avg'], 1)."%</strong>";
                }
              ?>
            </td>
            <td>
              <?php
                $fb = $pdo->query("SELECT COUNT(*) AS c, AVG(rating) AS avg FROM quizfeedback WHERE quizID=".(int)$q['quizID'])->fetch();
                if ((int)$fb['c'] === 0) {
                  echo "no feedback yet";
                } else {
                  echo "Average Rating: <strong>".round((float)$fb['avg'], 1)."/5</strong><br>";
                  $commentsCount = $pdo->query("
                    SELECT COUNT(*) FROM quizfeedback
                    WHERE quizID=".(int)$q['quizID']." AND comments IS NOT NULL AND comments <> ''
                  ")->fetchColumn();
                  if ((int)$commentsCount > 0) {
                    echo '<a href="Comments.php?quizID='.(int)$q['quizID'].'">Comments</a>';
                  }
                }
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <section class="card" id="recommended" style="margin-top:1rem">
      <h2>Recommended Questions</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Topic</th>
            <th>Learner</th>
            <th>Question</th>
            <th>Review</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recommended as $r): ?>
          <tr>
            <td><?= e($r['topicName']) ?></td>
            <td>
              <div class="learner" style="display:flex;gap:10px;align-items:center">
                <img class="image-learner"
                     src="<?= e(user_img_src($r['learnerPhoto'] ?? null)) ?>"
                     width="44" height="44" alt="Learner photo">
                <div><strong><?= e(($r['learnerFirst'] ?? '').' '.($r['learnerLast'] ?? '')) ?></strong></div>
              </div>
            </td>
            <td>
              <?php if (!empty($r['questionFigureFileName'])): ?>
                <img src="<?= e('uploads/'.basename($r['questionFigureFileName'])) ?>" width="55" alt="">
              <?php endif; ?>
              <p class="q-text"><?= e($r['question']) ?></p>
              <ol class="options" type="A">
                <li class="<?= ($r['correctAnswer']==='A'?'correct':'') ?>"><?= e($r['answerA']) ?></li>
                <li class="<?= ($r['correctAnswer']==='B'?'correct':'') ?>"><?= e($r['answerB']) ?></li>
                <li class="<?= ($r['correctAnswer']==='C'?'correct':'') ?>"><?= e($r['answerC']) ?></li>
                <li class="<?= ($r['correctAnswer']==='D'?'correct':'') ?>"><?= e($r['answerD']) ?></li>
              </ol>
            </td>
            <td>
                
<form class="review-form" method="POST" action="submit_recommendation.php">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input type="hidden" name="quizID" value="<?= (int)$r['quizID'] ?>">
                <p>Comment:</p>
                <textarea name="comment" rows="3"></textarea>
                <p>Approve:</p>
                <label><input type="radio" name="approve" value="yes" required> Yes</label>
                <label><input type="radio" name="approve" value="no"> No</label>
                <button type="submit" class="btn">Submit</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>document.getElementById('y').textContent = new Date().getFullYear();</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="educatorJS.js"></script>


</body>
</html>
