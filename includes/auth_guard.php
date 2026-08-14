<?php
// includes/auth_guard.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/** تأكد أن المستخدم مسجّل دخول */
function require_login(): void {
  if (empty($_SESSION['user_id']) || empty($_SESSION['userType'])) {
    // رجّعه لصفحة Auth مع رسالة
    header('Location: Auth.php?tab=login&msg=' . urlencode('Please log in first.'));
    exit;
  }
}

/** تأكد من نوع المستخدم */
function require_role(string $role): void {
  require_login();
  if (($_SESSION['userType'] ?? '') !== $role) {
    // لو نوعه غلط، رجّعه للّوج إن برسالة
    header('Location: Auth.php?tab=login&msg=' . urlencode('You are not authorized to view this page.'));
    exit;
  }
}
