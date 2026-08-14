<?php

session_start();
require __DIR__ . '/includes/config.php';

if (empty($_SESSION['user_id']) || (($_SESSION['userType'] ?? null) !== 'educator')) {
    $_SESSION['flash_error'] = 'Account not found. Please log in again';
    redirect('Auth.php?tab=login');
}

$questionId = filter_input(INPUT_POST, 'question_id', FILTER_VALIDATE_INT);
if (!$questionId) {
    http_response_code(400);
    exit('Missing or invalid question_id.');
}

$oldStmt = $pdo->prepare("
    SELECT id, quizID, questionFigureFileName
    FROM quizquestion
    WHERE id = ?
");
$oldStmt->execute([$questionId]);
$old = $oldStmt->fetch();
if (!$old) {
    http_response_code(404);
    exit('Question not found.');
}
$quizId    = (int)$old['quizID'];
$oldFigure = $old['questionFigureFileName'] ?: null;

$qText   = trim($_POST['question'] ?? '');
$ansA    = trim($_POST['answerA'] ?? '');
$ansB    = trim($_POST['answerB'] ?? '');
$ansC    = trim($_POST['answerC'] ?? '');
$ansD    = trim($_POST['answerD'] ?? '');
$correct = strtoupper(trim($_POST['correct'] ?? ''));

$errors = [];
if ($qText === '') $errors[] = 'Question is required.';
if ($ansA === '' || $ansB === '' || $ansC === '' || $ansD === '') $errors[] = 'All answers are required.';
if (!in_array($correct, ['A','B','C','D'], true)) $errors[] = 'Correct must be A, B, C, or D.';

$newFigure = $oldFigure;

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
                $dir = __DIR__ . '/uploads';
                if (!is_dir($dir)) @mkdir($dir, 0775, true);

                $name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
                    $errors[] = 'Image upload failed.';
                } else {
                    $newFigure = $name; // ← نخزن الاسم فقط
                    // حذف القديمة إن كانت موجودة فعليًا
                    if ($oldFigure) {
                        $absOld = $dir . '/' . $oldFigure;
                        if (is_file($absOld)) @unlink($absOld);
                    }
                }
            }
        }
    } elseif ($err !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Image upload failed.';
    }
}

if ($errors) {
    $_SESSION['flash_error'] = implode(' | ', $errors);
    redirect('editQ.php?question_id=' . urlencode((string)$questionId));
}

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

redirect('quiz.php?quizID=' . urlencode((string)$quizId) . '&updated=1');
