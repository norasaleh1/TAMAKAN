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
  header("Location: Auth.php?tab=login&msg=" . urlencode("Please log in first."));
  exit();
}

$topics = $pdo->query("SELECT id, topicName FROM topic ORDER BY topicName")->fetchAll();
$educators = $pdo->query("SELECT id, firstName, lastName FROM user WHERE userType='educator' ORDER BY firstName")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recommend a Question</title>
  <link rel="stylesheet" href="header.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="container">
  <section class="card narrow">
    <h1 class="page-title">Recommend a Question</h1>

    <form class="q-form" action="add_recommendation.php" method="post" enctype="multipart/form-data">
      <!-- Topics -->
      <div class="field">
        <label for="topic">Topic</label>
        <select id="topic" name="topicID" required>
          <option value="">Select a topic</option>
          <?php foreach ($topics as $t): ?>
            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['topicName']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Educators -->
      <div class="field">
        <label for="educator">Educator</label>
        <select id="educator" name="educatorID" required>
          <option value="">Select an educator</option>
          <?php foreach ($educators as $e): ?>
            <option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['firstName'].' '.$e['lastName']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Question -->
      <div class="field">
        <label for="qtext">Question</label>
        <textarea id="qtext" name="question" rows="5" required></textarea>
      </div>

      <!-- Optional image -->
      <div class="field">
        <label for="qfig">Question Figure (optional)</label>
        <input id="qfig" name="figure" type="file" accept="image/*" />
      </div>

      <!-- Answers -->
      <div class="grid">
        <div class="field"><label>Answer A</label><input type="text" name="answerA" required></div>
        <div class="field"><label>Answer B</label><input type="text" name="answerB" required></div>
        <div class="field"><label>Answer C</label><input type="text" name="answerC" required></div>
        <div class="field"><label>Answer D</label><input type="text" name="answerD" required></div>
      </div>

      <!-- Correct answer -->
      <div class="field inline">
        <label>Correct Answer</label>
        <select name="correctAnswer" required>
          <option value="">Select…</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>
      </div>

      <div class="actions">
        <button type="submit" class="btn primary">Submit Recommendation</button>
        <a href="learnerHome.php" class="btn secondary">Back</a>
      </div>
    </form>
  </section>
</main>

<?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$("#topic").on("change", function() {
    const topicID = $(this).val();

    $.ajax({
        url: "get_educators_by_topic.php",
        type: "GET",
        data: { topicID: topicID },
        success: function(response) {
            $("#educator").html('<option value="">Select an educator</option>');

            response.forEach(function(e) {
                $("#educator").append(
                    `<option value="${e.id}">${e.firstName} ${e.lastName}</option>`
                );
            });
        }
    });
});
</script>
</body>
</html>
