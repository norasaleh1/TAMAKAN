<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/config.php';

if (empty($_SESSION['user_id']) || (($_SESSION['userType'] ?? null) !== 'learner')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

function user_img_src_local(?string $file): string {
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

$topicId = filter_input(INPUT_GET, 'topic_id', FILTER_VALIDATE_INT);
$params = [];

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
";

if ($topicId) {
  $sql .= " WHERE q.topicID = ? ";
  $params[] = $topicId;
}

$sql .= " GROUP BY q.id, t.topicName, educatorName
          ORDER BY q.id DESC";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $out = [];
  foreach ($rows as $r) {
    $out[] = [
      'id'             => (int)$r['id'],
      'topicName'      => $r['topicName'],
      'educatorName'   => $r['educatorName'],
      'educatorPhoto'  => user_img_src_local($r['educatorPhoto'] ?? null),
      'question_count' => (int)$r['question_count'],
    ];
  }

  echo json_encode($out);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
