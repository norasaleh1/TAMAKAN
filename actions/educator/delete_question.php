<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_GET['quizID']) || !isset($_GET['questionID'])) {
    die("❌ Missing parameters.");
}

$quizID     = intval($_GET['quizID']);
$questionID = intval($_GET['questionID']);

$sql_img = "SELECT questionFigureFileName FROM quizquestion WHERE id = $questionID";
$res_img = mysqli_query($conn, $sql_img);

if ($res_img && mysqli_num_rows($res_img) > 0) {
    $row = mysqli_fetch_assoc($res_img);
    $img = $row['questionFigureFileName'];

    if (!empty($img) && file_exists("uploads/" . $img)) {
        unlink("uploads/" . $img);   
    }
}

mysqli_query($conn, "DELETE FROM quizquestion WHERE id = $questionID");

header("Location: quiz.php?quizID=$quizID&deleted=1");
exit();
