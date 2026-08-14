<?php
session_start();
require __DIR__ . '/includes/config.php'; // $pdo + redirect() + is_post()

if (empty($_SESSION['user_id']) || (($_SESSION['userType'] ?? null) !== 'educator')) {
    $_SESSION['flash_error'] = 'Account not found. Please log in again';
    redirect('Auth.php?tab=login');
}

if (!is_post()) {
    redirect('quiz.php');
}

function ensure_uploads_dir(): void {
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
}
function gen_filename(string $ext): string {
    return bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
}

$quizID   = filter_input(INPUT_POST, 'quizID', FILTER_VALIDATE_INT);
$question = trim($_POST['question'] ?? '');
$answerA  = trim($_POST['answerA'] ?? '');
$answerB  = trim($_POST['answerB'] ?? '');
$answerC  = trim($_POST['answerC'] ?? '');
$answerD  = trim($_POST['answerD'] ?? '');
$correct  = strtoupper(trim($_POST['correct'] ?? ''));

$errors = [];

if (!$quizID)                         $errors[] = 'Missing quizID.';
if ($question === '')                 $errors[] = 'Question is required.';
if ($answerA === '' || $answerB === '' || $answerC === '' || $answerD === '')
                                      $errors[] = 'All answers (A–D) are required.';
if (!in_array($correct, ['A','B','C','D'], true))
                                      $errors[] = 'Correct must be A, B, C, or D.';

if ($quizID) {
    $exists = $pdo->prepare("SELECT COUNT(*) FROM quiz WHERE id=?");
    $exists->execute([$quizID]);
    if (!$exists->fetchColumn()) $errors[] = 'Quiz not found.';
}

$storedFileName = null;
if (!empty($_FILES['figure']['name'])) {
    $err = (int)($_FILES['figure']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_OK) {
        $tmp = $_FILES['figure']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['figure']['name'], PATHINFO_EXTENSION));
        $allowed = ['png','jpg','jpeg','gif','webp','svg'];
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Only PNG, JPG, GIF, WEBP, or SVG are allowed.';
        } else {
            if ($ext !== 'svg') {
                $imgInfo = @getimagesize($tmp);
                if ($imgInfo === false) $errors[] = 'Invalid image file.';
            }
            if (!$errors) {
                if ($ext === 'jpeg') $ext = 'jpg';
                ensure_uploads_dir();
                $name = gen_filename($ext);
                if (!move_uploaded_file($tmp, __DIR__ . '/uploads/' . $name)) {
                    $errors[] = 'Image upload failed.';
                } else {
                    $storedFileName = $name; 
                }
            }
        }
    } elseif ($err !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Image upload failed.';
    }
}

if (!$errors) {
    $st = $pdo->prepare("
        INSERT INTO quizquestion
          (quizID, question, questionFigureFileName, answerA, answerB, answerC, answerD, correctAnswer)
        VALUES (:qid, :q, :fig, :a, :b, :c, :d, :correct)
    ");
    $st->execute([
        ':qid'     => $quizID,
        ':q'       => $question,
        ':fig'     => $storedFileName,   
        ':a'       => $answerA,
        ':b'       => $answerB,
        ':c'       => $answerC,
        ':d'       => $answerD,
        ':correct' => $correct,
    ]);

    redirect('quiz.php?quizID=' . urlencode((string)$quizID));
}

$_SESSION['flash_error'] = implode(' | ', $errors);
redirect('addQ.php?quizID=' . urlencode((string)$quizID));
