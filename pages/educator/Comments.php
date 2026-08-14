<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['userType'] !== 'educator') {
    header("Location: Auth.php?error=not_educator");
    exit;
}

if (!isset($_GET['quizID'])) {
    die("Quiz ID is missing.");
}

$quizID = (int) $_GET['quizID'];

$stmt = $pdo->prepare("
    SELECT comments, date
    FROM quizfeedback 
    WHERE quizID = :qid
    ORDER BY date DESC
");
$stmt->execute([':qid' => $quizID]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Comments</title>
  <link rel="stylesheet" href="Comments.css"/>
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<main class="container">
  <section class="card">
    <div class="row">
      <h1>Comments</h1>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Comments</th>
            <th>Date</th>
          </tr>
        </thead>

        <tbody>
          <?php foreach ($comments as $c): ?>
            <tr>
              <td><?php echo htmlspecialchars($c['comments']); ?></td>
              <td class="dates" style="font-size:13px;">
                <?php echo htmlspecialchars($c['date']); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<?php include 'footer.php'; ?>

<script>
document.getElementById('y').textContent = new Date().getFullYear();
</script>

</body>
</html>
