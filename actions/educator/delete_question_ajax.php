<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/plain; charset=utf-8');

if (empty($_SESSION['user_id']) || ($_SESSION['userType'] ?? '') !== 'educator') {
    echo 'Unauthorized';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Invalid method';
    exit;
}

$questionID = isset($_POST['questionID']) ? (int)$_POST['questionID'] : 0;
$quizID     = isset($_POST['quizID']) ? (int)$_POST['quizID'] : 0;

if ($questionID <= 0 || $quizID <= 0) {
    echo 'Invalid IDs';
    exit;
}



$stmt_img = $conn->prepare("SELECT questionFigureFileName FROM quizquestion WHERE id = ?");
$stmt_img->bind_param("i", $questionID);
$stmt_img->execute();
$result_img = $stmt_img->get_result();

if ($result_img && $result_img->num_rows > 0) {
    $row = $result_img->fetch_assoc();
    $img = $row['questionFigureFileName'];

    // حذف الصورة إن وجدت
    $imgPath = __DIR__ . "/uploads/" . $img;
    if (!empty($img) && file_exists($imgPath)) {
        unlink($imgPath);
    }
}

$stmt_img->close();


$stmt_del = $conn->prepare("DELETE FROM quizquestion WHERE id = ? AND quizID = ?");
$stmt_del->bind_param("ii", $questionID, $quizID);
$stmt_del->execute();

if ($stmt_del->affected_rows > 0) {
    echo 'true';
} else {
    echo 'false';
}

$stmt_del->close();
$conn->close();
?>
