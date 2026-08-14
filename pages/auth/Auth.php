<?php
session_start();
require_once __DIR__ . '/includes/config.php'; 


function flash_redirect(string $url, array $params = []): void {
  if ($params) { $url .= (str_contains($url,'?') ? '&' : '?') . http_build_query($params); }
  header("Location: $url"); exit;
}

function save_user_photo(string $fieldName, int $userId): ?string {
  if (empty($_FILES[$fieldName]['name']) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    return null; 
  }
  $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
  $allowed = ['png','jpg','jpeg','gif','webp'];
  if (!in_array($ext, $allowed, true)) return null;
  if ($ext === 'jpeg') $ext = 'jpg';

  $dir = __DIR__ . '/uploads';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);

  $name = "u{$userId}_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

  return move_uploaded_file($_FILES[$fieldName]['tmp_name'], $dir . '/' . $name)
         ? $name : null;
}

function verify_and_upgrade_password(array $row, string $plain, PDO $pdo): bool {
  $stored = $row['password'] ?? '';
  $uid    = (int)$row['id'];

  if (str_starts_with($stored, '$2y$')) {
    return password_verify($plain, $stored);
  }
  if ($stored === $plain) {
    $newHash = password_hash($plain, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE `user` SET `password` = :h WHERE id = :id");
    $upd->execute([':h'=>$newHash, ':id'=>$uid]);
    return true;
  }
  return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $formType = $_POST['form_type'] ?? '';

  /* =============== LOGIN =============== */
  if ($formType === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
      flash_redirect('Auth.php', ['tab'=>'login','msg'=>'Please fill in all fields','email'=>$email]);
    }

    $stmt = $pdo->prepare("SELECT id, firstName, userType, password FROM `user` WHERE emailAddress=:em LIMIT 1");
    $stmt->execute([':em'=>$email]);
    $u = $stmt->fetch();

    if ($u && verify_and_upgrade_password($u, $pass, $pdo)) {
      session_regenerate_id(true);
      $_SESSION['user_id']   = (int)$u['id'];
      $_SESSION['firstName'] = $u['firstName'];
      $_SESSION['userType']  = $u['userType'];
      header('Location: ' . ($u['userType'] === 'educator' ? 'educatorHome.php' : 'learnerHome.php'));
      exit;
    }
    flash_redirect('Auth.php', ['tab'=>'login','msg'=>'Invalid email or password','email'=>$email]);
  }

  /* =============== SIGNUP =============== */
  if ($formType === 'signup') {
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $role   = ($_POST['role'] ?? 'learner') === 'educator' ? 'educator' : 'learner';
    $topicsSelected = $_POST['topics'] ?? []; // educator only

    if (!$first || !$last || !$email || !$pass) {
      flash_redirect('Auth.php', ['tab'=>'register','msg'=>'Please fill all required fields']);
    }

    if ($role === 'educator' && empty($topicsSelected)) {
      flash_redirect('Auth.php', ['tab'=>'register','msg'=>'Please choose at least one topic for educator.','role'=>'educator']);
    }

    try {
      $photoFileName = 'default.png';
      $ins = $pdo->prepare("
        INSERT INTO `user` (firstName,lastName,emailAddress,`password`,photoFileName,userType)
        VALUES (:fn,:ln,:em,:pw,:ph,:ut)
      ");
      $ins->execute([
        ':fn'=>$first, ':ln'=>$last, ':em'=>$email,
        ':pw'=>password_hash($pass, PASSWORD_DEFAULT),
        ':ph'=>$photoFileName, ':ut'=>$role
      ]);
      $newUserId = (int)$pdo->lastInsertId();

      if (!empty($_FILES['profile']['name'])) {
        $saved = save_user_photo('profile', $newUserId);
        if ($saved) {
          $pdo->prepare("UPDATE `user` SET photoFileName=:p WHERE id=:id")
              ->execute([':p'=>$saved, ':id'=>$newUserId]);
          $photoFileName = $saved;
        }
      }

      if ($role === 'educator' && !empty($topicsSelected)) {
        $in  = str_repeat('?,', count($topicsSelected)-1) . '?';
        $q   = $pdo->prepare("SELECT id FROM topic WHERE topicName IN ($in)");
        $q->execute(array_values($topicsSelected));
        $topicIds = $q->fetchAll(PDO::FETCH_COLUMN);

        if ($topicIds) {
          $insQuiz = $pdo->prepare("INSERT INTO quiz (educatorID, topicID) VALUES (:edu, :tid)");
          foreach ($topicIds as $tid) {
            $insQuiz->execute([':edu'=>$newUserId, ':tid'=>(int)$tid]);
          }
        }
      }

      session_regenerate_id(true);
      $_SESSION['user_id']   = $newUserId;
      $_SESSION['firstName'] = $first;
      $_SESSION['userType']  = $role;
      header('Location: ' . ($role === 'educator' ? 'educatorHome.php' : 'learnerHome.php'));
      exit;

    } catch (PDOException $e) {
      flash_redirect('Auth.php', [
        'tab'=>'login',
        'msg'=>'<span class="form-msg">This email is already registered. Please log in.</span>',
        'email'=>$email
      ]);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TAMAKAN • Login / Register</title>
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="login.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>


  <div class="container <?= (($_GET['tab'] ?? '')==='register') ? 'active' : '' ?>">

    <!-- Login -->
    <div class="form-box login">
      <form method="POST" action="Auth.php" autocomplete="on">
        <input type="hidden" name="form_type" value="login" />
        <h1>Login</h1>

        <?php if (!empty($_GET['msg']) && (($_GET['tab'] ?? '')!=='register')): ?>
          <p class="form-msg" style="color:#d10000; font-weight:bold; text-align:center;">
            <?= $_GET['msg'] ?>
          </p>
        <?php endif; ?>

        <div class="input-box">
          <input type="email" name="email" placeholder="Email"
                 value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" required />
          <i class="bx bxs-envelope"></i>
        </div>

        <div class="input-box">
          <input type="password" name="password" placeholder="Password" required />
          <i class="bx bxs-lock-alt"></i>
        </div>

        <button type="submit" class="btn">Login</button>
      </form>
    </div>

    <!-- Register -->
    <div class="form-box register">
      <form method="POST" action="Auth.php" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="form_type" value="signup" />
        <h1>Registration</h1>

        <?php if (!empty($_GET['msg']) && (($_GET['tab'] ?? '')==='register')): ?>
          <p class="form-msg" style="color:#d10000; font-weight:bold; text-align:center;">
            <?= $_GET['msg'] ?>
          </p>
        <?php endif; ?>

        <div class="user-type">
          <span class="label">You are:</span>
          <?php $roleGet = $_GET['role'] ?? ''; ?>
          <label><input type="radio" name="role" value="learner"  <?= ($roleGet==='educator'?'':'checked') ?>> learner</label>
          <label><input type="radio" name="role" value="educator" <?= ($roleGet==='educator'?'checked':'') ?>> educator</label>
        </div>

        <div class="input-box"><input type="text" name="first_name" placeholder="First name" required /><i class="bx bxs-user"></i></div>
        <div class="input-box"><input type="text" name="last_name"  placeholder="Last name"  required /><i class="bx bxs-user"></i></div>
        <div class="input-box"><input type="email" name="email"     placeholder="Email"      required /><i class="bx bxs-envelope"></i></div>
        <div class="input-box"><input type="password" name="password" placeholder="Password" required /><i class="bx bxs-lock-alt"></i></div>

        <div class="input-box">
          <label class="label">Profile image (optional)</label>
          <input type="file" id="profile" name="profile" accept="image/*" />
        </div>

        <div class="topics hidden">
          <span class="label">Specialized topics (choose at least one):</span>
          <?php
            try {
              $topics = $pdo->query("SELECT topicName FROM topic ORDER BY topicName ASC")->fetchAll(PDO::FETCH_COLUMN);
              if ($topics) {
                foreach ($topics as $t) {
                  echo '<label><input type="checkbox" name="topics[]" value="'.htmlspecialchars($t).'"> '.htmlspecialchars($t).'</label>';
                }
              } else {
                echo '<p>No topics found in database.</p>';
              }
            } catch (PDOException $e) {
              echo '<p>Error loading topics.</p>';
            }
          ?>
        </div>

        <button type="submit" class="btn">Register</button>
      </form>
    </div>

    <div class="toggle-box">
      <div class="toggle-panel toggle-left">
        <img class="panel-logo" src="images/logo.png" alt="Logo" />
        <h1>TAMAKAN</h1>
        <p>Don't have an account?</p>
        <button class="btn register-btn">Register</button>
      </div>
      <div class="toggle-panel toggle-right">
        <img class="panel-logo" src="images/logo.png" alt="Logo" />
        <h1>TAMAKAN</h1>
        <p>Already have an account?</p>
        <button class="btn login-btn">Login</button>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const container = document.querySelector('.container');
      document.querySelector('.register-btn')?.addEventListener('click', ()=>container?.classList.add('active'));
      document.querySelector('.login-btn')?.addEventListener('click',   ()=>container?.classList.remove('active'));

      const regForm = document.querySelector('.form-box.register form');
      function toggleTopics(){
        if (!regForm) return;
        const role   = regForm.querySelector('input[name="role"]:checked')?.value;
        const topics = regForm.querySelector('.topics');
        if (!topics) return;
        if (role === 'educator') {
          topics.classList.remove('hidden'); topics.style.display = '';
        } else {
          topics.classList.add('hidden'); topics.style.display = 'none';
          topics.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        }
      }
      regForm?.querySelectorAll('input[name="role"]').forEach(r => r.addEventListener('change', toggleTopics));
      toggleTopics();
    });
  </script>
</body>
</html>
