<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (empty($_SESSION['user_id']) || (($_SESSION['userType'] ?? null) !== 'learner')) {
    $_SESSION['flash_error'] = 'Account not found. Please log in again';
    redirect('Auth.php');
}

$learnerId = (int)$_SESSION['user_id'];

$u = $pdo->prepare("
  SELECT firstName, lastName, emailAddress, photoFileName
  FROM `user`
  WHERE id = ?
");
$u->execute([$learnerId]);
$me = $u->fetch();
if (!$me) {
  $_SESSION['flash_error'] = 'Account not found. Please log in again';
  redirect('Auth.php');
}

$topics = $pdo->query("SELECT id, topicName FROM topic ORDER BY topicName")->fetchAll();

$sql = "
  SELECT 
    q.id,
    t.topicName,
    CONCAT(e.firstName,' ',e.lastName) AS educatorName,
    e.photoFileName AS educatorPhoto,
    COUNT(qq.id) AS question_count
  FROM quiz q
  JOIN topic t   ON t.id = q.topicID
  JOIN `user` e  ON e.id = q.educatorID
  LEFT JOIN quizquestion qq ON qq.quizID = q.id
  GROUP BY q.id, t.topicName, educatorName
  ORDER BY q.id DESC
";
$qs = $pdo->query($sql);
$quizzes = $qs->fetchAll();


$recStmt = $pdo->prepare("
  SELECT 
      rq.id,
      t.topicName,
      CONCAT(u.firstName,' ',u.lastName) AS educatorName,
      rq.question,
      rq.questionFigureFileName,
      rq.correctAnswer,
      COALESCE(rq.status, 'pending') AS status,
      rq.comments
  FROM recommendedquestion rq
  JOIN quiz   q ON q.id = rq.quizID
  JOIN topic  t ON t.id = q.topicID
  JOIN `user` u ON u.id = q.educatorID
  WHERE rq.learnerID = ?
  ORDER BY rq.id DESC
");
$recStmt->execute([$learnerId]);
$recs = $recStmt->fetchAll();

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function user_img_src(?string $file): string {
  $name = $file ? basename($file) : 'default.png';
  $candidates = [
    'uploads/' . $name,
    'images/users/' . $name,
    'images/' . $name
  ];

  foreach ($candidates as $rel) {
    if (is_file(__DIR__ . '/' . $rel)) {
      return $rel;
    }
  }
  return 'uploads/default.png';
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Learner Home</title>
  <link rel="stylesheet" href="header.css" />
  <link rel="stylesheet" href="learnerHomepage.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script src="transition.js" defer></script>
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">

  <section class="hero">
    <section class="infoCard">
      <h2 class="section-title">My Information</h2>
      <div class="user-box">
        <img class="avatar-lg" src="<?= e(user_img_src($me['photoFileName'] ?? null)) ?>" alt="Learner photo" />
        <ul class="user-info">
          <li><strong>Name:</strong> <?= e($me['firstName'].' '.$me['lastName']) ?></li>
          <li><strong>Email:</strong> <?= e($me['emailAddress']) ?></li>
        </ul>
      </div>
    </section>

    <h1 class="welcome-text">
      Welcome <span class="learner-name"><?= e($me['firstName']) ?></span>
    </h1>
  </section>

  <section class="card">
    <div class="flex-between">
      <h2 class="section-title">Available Quizzes</h2>
      <div class="filters">
        <label for="topicFilter" style="margin-right:6px;font-weight:600;">Topic:</label>
        <select id="topicFilter" name="topic_id">
          <option value="">All topics</option>
          <?php foreach ($topics as $t): ?>
            <option value="<?= (int)$t['id'] ?>">
              <?= e($t['topicName']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Topic</th>
            <th class="col-edu">Educator</th>
            <th># Questions</th>
            <th>Take Quiz</th>
          </tr>
        </thead>
        <tbody id="quizRows">
        <?php if (!$quizzes): ?>
          <tr><td colspan="4" style="color:#777">No quizzes found.</td></tr>
        <?php else: foreach ($quizzes as $q): ?>
          <tr>
            <td><?= e($q['topicName']) ?></td>
            <td>
              <div class="edu-cell">
                <img class="edu-avatar" src="<?= e(user_img_src($q['educatorPhoto'] ?? null)) ?>" alt="">
                <span class="edu-name"><?= e($q['educatorName']) ?></span>
              </div>
            </td>
            <td><?= (int)$q['question_count'] ?></td>
            <td>
              <?php if ((int)$q['question_count'] > 0): ?>
                <a class="link" href="takequiz.php?quiz_id=<?= (int)$q['id'] ?>">Start</a>
              <?php else: ?>
                <span style="color:#888">No questions</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card">
    <h2 class="section-title">My Recommended Questions</h2>
    <div class="table-wrapper">
      <table class="table rq-table">
        <thead>
          <tr>
            <th>Topic</th>
            <th>Educator</th>
            <th>Question</th>
            <th>Correct</th>
            <th>Status / Comment</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$recs): ?>
          <tr><td colspan="5" style="color:#777">No recommended questions yet.</td></tr>
        <?php else: foreach ($recs as $row): ?>
          <tr>
            <td><?= e($row['topicName']) ?></td>
            <td><?= e($row['educatorName']) ?></td>
            <td>
              <?php if (!empty($row['questionFigureFileName'])): ?>
                <img src="<?= e('uploads/'.$row['questionFigureFileName']) ?>" width="45" style="vertical-align:middle;margin-right:6px;">
              <?php endif; ?>
              <?= e($row['question']) ?>
            </td>
            <td><b><?= e($row['correctAnswer']) ?></b></td>
            <td>
              <strong>Status:</strong> <?= e(ucfirst(strtolower($row['status']))) ?><br>
              <strong>Comment:</strong> <?= e($row['comments'] ?: '—') ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <section class="links-row">
      <a class="btn secondary" href="recommend_question.php">Recommend a New Question</a>
    </section>
  </section>

</main>

<?php include 'footer.php'; ?>

<script>
  document.getElementById('y').textContent = new Date().getFullYear();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const topicSelect = document.getElementById('topicFilter');
  const tbody       = document.getElementById('quizRows');

  if (!topicSelect || !tbody) return;

  topicSelect.addEventListener('change', function () {
    const topicId = topicSelect.value; 

    const url = 'get_quizzes.php?topic_id=' + encodeURIComponent(topicId);

    fetch(url)
      .then(function (response) {
        if (!response.ok) throw new Error('Network error');
        return response.json();
      })
      .then(function (data) {
        renderTable(data);
      })
      .catch(function (err) {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="4" style="color:#b91c1c">Error loading quizzes.</td></tr>';
      });
  });

  function renderTable(quizzes) {
    tbody.innerHTML = '';

    if (!quizzes || quizzes.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" style="color:#777">No quizzes found for this topic.</td></tr>';
      return;
    }

    quizzes.forEach(function (q) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${q.topicName}</td>
        <td>
          <div class="edu-cell">
            <img class="edu-avatar" src="${q.educatorPhoto}" alt="">
            <span class="edu-name">${q.educatorName}</span>
          </div>
        </td>
        <td>${q.question_count}</td>
        <td>
          ${q.question_count > 0 
            ? `<a class="link" href="takequiz.php?quiz_id=${q.id}">Start</a>`
            : `<span style="color:#888">No questions</span>`
          }
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  
});
</script>

</body>
</html>
