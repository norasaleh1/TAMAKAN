<?php
/**
 * TAMAKAN – Database & App bootstrap
 * مكان واحد نضبط فيه الاتصال بقاعدة البيانات والإعدادات العامة
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- DB Credentials ---------- */
/*
  ملاحظة مهمة لمستخدمي MAMP:
  - على Windows غالباً كلمة المرور فاضية "" (افتراضي MAMP Windows)
  - على macOS غالباً كلمة المرور "root"
  لو ما اشتغل الاتصال، جرّبي تبديل قيمة الـ password بين "" و "root"
*/
$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = 'tamakan';
$dbUser = 'root';
$dbPass = 'root';

// ← لأن كلمة المرور عندك "root" حسب MAMP
    // جرّبي '' (ويندوز) أو 'root' (ماك)

/* ---------- Build DSN ---------- */
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

/* ---------- Create PDO ---------- */
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // أخطاء واضحة
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch كـ array
        PDO::ATTR_EMULATE_PREPARES   => false,                  // أمان أفضل
    ]);
} catch (PDOException $e) {
    // رسالة مفيدة أثناء التطوير (ثم اعملي صفحة خطأ لاحقاً لو حبيتي)
    http_response_code(500);
    exit('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

/* ---------- Helpers (اختيارية لكن مفيدة) ---------- */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function is_post(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

// ✅ Create MySQLi connection for old scripts
$conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if (!$conn) {
    die("❌ MySQLi Connection failed: " . mysqli_connect_error());
}
