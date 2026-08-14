<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "root"; 
$database = "tamakan";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    echo json_encode([]);
    exit;
}

if (!isset($_GET['topicID'])) {
    echo json_encode([]);
    exit;
}

$topicID = $_GET['topicID'];

$stmt = $pdo->prepare("
    SELECT user.id, user.firstName, user.lastName
    FROM quiz 
    JOIN user ON quiz.educatorID = user.id
    WHERE quiz.topicID = :topic
");
$stmt->execute([':topic' => $topicID]);

$educators = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($educators);
?>
