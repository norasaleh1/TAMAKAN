<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/includes/config.php';

$quizID = intval($_GET['quizID']);

$sql = "SELECT * FROM quizquestion WHERE quizID = $quizID";
$result = mysqli_query($conn, $sql);

$queryQuiz = "
    SELECT t.topicName
    FROM quiz q
    JOIN topic t ON q.topicID = t.id
    WHERE q.id = $quizID
";
$resultQuiz = mysqli_query($conn, $queryQuiz);
$rowQuiz = mysqli_fetch_assoc($resultQuiz);
$quizName = $rowQuiz['topicName'] ?? "Unknown";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz – <?php echo $quizName; ?></title>
  <link rel="stylesheet" href="quiz.css"/>
  <link rel="stylesheet" href="header.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<main class="container">
<section class="card">

  <a href="AddQ.php?quizID=<?php echo $quizID; ?>" class="add-Q">Add New Question</a>

  <h1>Quiz for <span class="accent"><?php echo $quizName; ?></span></h1>
<p id="deleteMsg" style="color:green; font-weight:bold;"></p>

  <?php if (isset($_GET['deleted'])): ?>
      <p style="color:green; font-weight:bold;">
          ✅ Question deleted successfully!
      </p>
  <?php endif; ?>

  <?php if (mysqli_num_rows($result) > 0): ?>

  <div class="table-wrap">
    <table class="table table-quiz">
      <thead>
        <tr>
          <th>Question</th>
          <th></th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
       <tr id="row_<?php echo $row['id']; ?>">

          <td>
            <?php if (!empty($row['questionFigureFileName'])): ?>
              <img src="uploads/<?php echo htmlspecialchars($row['questionFigureFileName']); ?>" width="55" height="55">
            <?php endif; ?>

            <p class="q"><?php echo htmlspecialchars($row['question']); ?></p>

            <ol class="options" type="A">
              <li <?php if ($row['correctAnswer'] == 'A') echo 'class="correct"'; ?>>
                <?php echo htmlspecialchars($row['answerA']); ?>
              </li>
              <li <?php if ($row['correctAnswer'] == 'B') echo 'class="correct"'; ?>>
                <?php echo htmlspecialchars($row['answerB']); ?>
              </li>
              <li <?php if ($row['correctAnswer'] == 'C') echo 'class="correct"'; ?>>
                <?php echo htmlspecialchars($row['answerC']); ?>
              </li>
              <li <?php if ($row['correctAnswer'] == 'D') echo 'class="correct"'; ?>>
                <?php echo htmlspecialchars($row['answerD']); ?>
              </li>
            </ol>
          </td>

          <td class="action">
            <a href="editQ.php?id=<?php echo $row['id']; ?>&quizID=<?php echo $quizID; ?>"
               class="underline">edit</a>
          </td>

          <td class="action">
           <a href="#"
   class="underline danger delete-btn"
   data-qid="<?php echo $row['id']; ?>"
   data-quiz="<?php echo $quizID; ?>">
   delete
</a>

          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php else: ?>
      <p style="margin-top:20px;">
        <strong>No questions found for this quiz.</strong>
      </p>
  <?php endif; ?>

</section>
</main>

<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<script>
$(document).on("click", ".delete-btn", function(e) {
    e.preventDefault();

    if (!confirm("Delete this question?")) return;

    let questionID = $(this).data("qid");
    let quizID     = $(this).data("quiz");
    let row        = $(this).closest("tr");

    $.ajax({
        url: "delete_question_ajax.php",
        type: "POST",
        data: { questionID: questionID, quizID: quizID },
        success: function(res) {
            res = res.trim();

            if (res === "true") {

                row.remove();

                $("#deleteMsg").text("✅ Question deleted successfully!");

                setTimeout(() => {
                    $("#deleteMsg").text("");
                }, 3000);

                if ($("tbody tr").length === 0) {

                    $(".table-wrap").remove();

                    $(".card").append(`
                        <p style="margin-top:20px;">
                            <strong>No questions found for this quiz.</strong>
                        </p>
                    `);
                }

            } else {
                alert("❌ Failed to delete: " + res);
            }
        },
        error: function() {
            alert("❌ Server error, try again.");
        }
    });
});



</script>



<script>document.getElementById('y').textContent = new Date().getFullYear();</script>
</body>
</html>
