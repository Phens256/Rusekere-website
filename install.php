<?php
/**
 * Rusekere School – one-time web installer.
 * Open  http://localhost/rusekere-school/install.php  (or https://yourdomain/install.php) in the browser,
 * type your database details, press "Install". It will:
 *   1. test the connection,  2. import database/rusekere_school.sql,
 *   3. write config/db-settings.php,  4. lock itself (config/installed.lock).
 * Afterwards DELETE this file (install.php) from the server.
 */
declare(strict_types=1);
session_name('RUSEKERE_INSTALL');
session_start();

$root = str_replace('\\', '/', __DIR__);
$cfgDir = $root . '/config';
$lock = $cfgDir . '/installed.lock';
$sqlFile = $root . '/database/rusekere_school.sql';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Base URL for links (works in sub-folders)
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/install.php');
$base = rtrim(dirname($script), '/');

function split_sql(string $sql): array {
    $out = []; $buf = ''; $n = strlen($sql); $q = '';
    for ($i = 0; $i < $n; $i++) {
        $c = $sql[$i]; $d = $sql[$i + 1] ?? '';
        if ($q === '') {
            if ($c === '-' && $d === '-' && (($sql[$i + 2] ?? ' ') === ' ' || ($sql[$i + 2] ?? '') === "\n" || ($sql[$i + 2] ?? '') === "\r")) { while ($i < $n && $sql[$i] !== "\n") $i++; continue; }
            if ($c === '#') { while ($i < $n && $sql[$i] !== "\n") $i++; continue; }
            if ($c === '/' && $d === '*') { $e = strpos($sql, '*/', $i + 2); $i = $e === false ? $n : $e + 1; continue; }
            if ($c === "'" || $c === '"' || $c === '`') { $q = $c; $buf .= $c; continue; }
            if ($c === ';') { if (trim($buf) !== '') $out[] = trim($buf); $buf = ''; continue; }
            $buf .= $c;
        } else {
            $buf .= $c;
            if ($c === '\\' && $q !== '`') { $buf .= $d; $i++; continue; }
            if ($c === $q) { if ($d === $q) { $buf .= $d; $i++; } else { $q = ''; } }
        }
    }
    if (trim($buf) !== '') $out[] = trim($buf);
    return $out;
}

$done = false; $errors = []; $manual = null; $adminChanged = false;
$v = ['host' => 'localhost', 'name' => '', 'user' => '', 'create' => '', 'email' => '', 'replace' => ''];

if (is_file($lock)) {
    $alreadyInstalled = true;
} else {
    $alreadyInstalled = false;
    if (empty($_SESSION['t'])) $_SESSION['t'] = bin2hex(random_bytes(16));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['t'], (string)($_POST['t'] ?? ''))) { $errors[] = 'The form expired. Please refresh the page and try again.'; }
        $v = ['host' => trim($_POST['host'] ?? ''), 'name' => trim($_POST['name'] ?? ''), 'user' => trim($_POST['user'] ?? ''),
              'create' => !empty($_POST['create']) ? '1' : '', 'email' => trim($_POST['email'] ?? ''), 'replace' => !empty($_POST['replace']) ? '1' : ''];
        $pass = (string)($_POST['pass'] ?? '');
        $apw = (string)($_POST['admin_pass'] ?? ''); $apw2 = (string)($_POST['admin_pass2'] ?? '');

        if ($v['host'] === '') $errors[] = 'Enter the database host (usually "localhost").';
        if (!preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $v['name'])) $errors[] = 'Database name may contain only letters, numbers, underscore and hyphen.';
        if ($v['user'] === '') $errors[] = 'Enter the database username.';
        if ($apw !== '') {
            if (strlen($apw) < 8 || !preg_match('/[a-z]/', $apw) || !preg_match('/[A-Z]/', $apw) || !preg_match('/\d/', $apw)) $errors[] = 'Admin password needs 8+ characters with an uppercase letter, a lowercase letter and a number.';
            if ($apw !== $apw2) $errors[] = 'The two admin passwords do not match.';
        }
        if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid admin email address, or leave it empty.';
        if (!is_file($sqlFile)) $errors[] = 'database/rusekere_school.sql is missing from the site folder.';
        if (!extension_loaded('pdo_mysql')) $errors[] = 'The PHP extension pdo_mysql is not enabled on this server.';

        if (!$errors) {
            try {
                $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
                if ($v['create']) {
                    $srv = new PDO('mysql:host=' . $v['host'] . ';charset=utf8mb4', $v['user'], $pass, $opts);
                    $srv->exec('CREATE DATABASE IF NOT EXISTS `' . $v['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                }
                $pdo = new PDO('mysql:host=' . $v['host'] . ';dbname=' . $v['name'] . ';charset=utf8mb4', $v['user'], $pass, $opts);
                $pdo->exec("SET NAMES utf8mb4");

                $exists = false;
                try { $pdo->query('SELECT 1 FROM users LIMIT 1'); $exists = true; } catch (Throwable $e) {}
                $only = !empty($_POST['only_connect']);
                if ($exists && !$v['replace'] && !$only) {
                    $errors[] = 'This database already contains the school tables. To keep your data, leave it alone and tick "Only save the connection" below; or tick "Replace existing data" to wipe and re-create the tables.';
                    $v['_exists'] = true;
                } else {
                    if (!$exists || $v['replace']) {
                        foreach (split_sql((string)file_get_contents($sqlFile)) as $stmt) {
                            if (preg_match('/^\s*(CREATE\s+DATABASE|USE)\b/i', $stmt)) continue; // we already chose the database
                            $pdo->exec($stmt);
                        }
                    }
                    if ($apw !== '' || $v['email'] !== '') {
                        if ($apw !== '') $pdo->prepare("UPDATE users SET password=? WHERE username='admin'")->execute([password_hash($apw, PASSWORD_DEFAULT)]);
                        if ($v['email'] !== '') $pdo->prepare("UPDATE users SET email=? WHERE username='admin'")->execute([$v['email']]);
                        $adminChanged = $apw !== '';
                    }
                    $code = "<?php\n// Written by install.php on " . date('Y-m-d H:i') . ". Edit if your database details change.\nreturn " .
                        var_export(['host' => $v['host'], 'name' => $v['name'], 'user' => $v['user'], 'pass' => $pass], true) . ";\n";
                    if (@file_put_contents($cfgDir . '/db-settings.php', $code) === false) {
                        $manual = $code;
                    } else {
                        @file_put_contents($lock, 'Installed ' . date('c') . "\nDelete this file only if you want to run install.php again.\n");
                    }
                    $done = true;
                    unset($_SESSION['t']);
                }
            } catch (Throwable $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'Access denied') !== false) $msg .= ' — check the username and password.';
                elseif (stripos($msg, 'Unknown database') !== false) $msg .= ' — create the database first (cPanel → MySQL Databases) or tick "Create the database if it does not exist" (works on your own computer).';
                elseif (stripos($msg, 'refused') !== false || stripos($msg, 'No such file') !== false) $msg .= ' — is MySQL running (XAMPP/WAMP control panel)?';
                $errors[] = 'Database error: ' . $msg;
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Install | Rusekere Secondary School</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#e8f1fb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#26303b;line-height:1.55}
.box{max-width:640px;margin:2rem auto;background:#fff;border-radius:14px;box-shadow:0 6px 22px rgba(11,40,80,.12);padding:1.8rem 1.6rem}
h1{margin:0 0 .2rem;color:#6b3f1d;font-size:1.45rem}.sub{margin:0 0 1.2rem;color:#66727f}
label{display:block;font-weight:600;margin:.9rem 0 .25rem}input[type=text],input[type=password],input[type=email]{width:100%;padding:.65rem .75rem;border:1px solid #cfd6dd;border-radius:8px;font-size:1rem}
small{color:#66727f;display:block;margin-top:.2rem}.chk{display:flex;gap:.5rem;align-items:flex-start;font-weight:400;margin:.7rem 0}
button{margin-top:1.2rem;width:100%;background:#0b4f9c;color:#fff;border:0;border-radius:50px;padding:.85rem;font-size:1.05rem;font-weight:600;cursor:pointer}button:hover{background:#6b3f1d}
.err{background:#fdecea;border:1px solid #f5b5b0;color:#8a1f17;border-radius:8px;padding:.7rem .9rem;margin:.5rem 0}.ok{background:#e7f6ec;border:1px solid #a8dbb8;color:#14532d;border-radius:8px;padding:.8rem 1rem;margin:.6rem 0}
.warn{background:#fff6e0;border:1px solid #f1d58a;color:#6b4a00;border-radius:8px;padding:.8rem 1rem;margin:.8rem 0}
pre{background:#101820;color:#d8f3dc;padding:1rem;border-radius:8px;overflow:auto;font-size:.85rem}a.btn{display:inline-block;background:#0b4f9c;color:#fff;text-decoration:none;padding:.7rem 1.4rem;border-radius:50px;margin:.3rem .3rem 0 0}
hr{border:0;border-top:1px solid #e3e7ec;margin:1.4rem 0}
</style></head><body><div class="box">
<h1>Rusekere Secondary School</h1><p class="sub">Website setup – connect the database</p>

<?php if ($alreadyInstalled): ?>
  <div class="ok"><strong>The website is already set up.</strong></div>
  <p>For safety this installer is locked. Please <strong>delete <code>install.php</code></strong> from the server.</p>
  <p>To change database details later, edit <code>config/db-settings.php</code>. To run this installer again, delete <code>config/installed.lock</code> first.</p>
  <a class="btn" href="<?= $h($base) ?>/index.php">Open website</a><a class="btn" href="<?= $h($base) ?>/admin/login.php">Staff login</a>

<?php elseif ($done): ?>
  <div class="ok"><strong>Success!</strong> The database is connected and the tables are ready.</div>
  <?php if ($manual !== null): ?>
    <div class="warn">The folder <code>config/</code> is not writable, so I could not save the settings automatically.
    Create a file named <code>config/db-settings.php</code> and paste exactly this into it, then save:</div><pre><?= $h($manual) ?></pre>
  <?php endif; ?>
  <p><strong>Staff login:</strong> username <code>admin</code>,
  <?= $adminChanged ? 'and the new password you just chose.' : 'password <code>admin</code> – <strong>change it after the site goes live</strong> (Change Password in the admin menu).' ?></p>
  <div class="warn"><strong>Important:</strong> now delete <code>install.php</code> (and <code>setup-check.php</code> if present) from the server.</div>
  <a class="btn" href="<?= $h($base) ?>/index.php">Open website</a><a class="btn" href="<?= $h($base) ?>/admin/login.php">Staff login</a>

<?php else: ?>
  <?php foreach ($errors as $e): ?><div class="err"><?= $h($e) ?></div><?php endforeach; ?>
  <form method="post" autocomplete="off">
    <input type="hidden" name="t" value="<?= $h($_SESSION['t']) ?>">
    <label for="host">Database host</label><input type="text" id="host" name="host" value="<?= $h($v['host']) ?>" required>
    <small>Almost always <code>localhost</code> (XAMPP, WAMP and cPanel).</small>
    <label for="name">Database name</label><input type="text" id="name" name="name" value="<?= $h($v['name'] ?: 'rusekere_school') ?>" required>
    <small>Local: <code>rusekere_school</code>. cPanel: the full prefixed name, e.g. <code>cpuser_rusekere</code>.</small>
    <label for="user">Database username</label><input type="password" id="user" name="user" autocomplete="off" value="<?= $h($v['user'] ?: '') ?>" placeholder="root (local) or cpuser_dbuser (cPanel)" required>
    <label for="pass">Database password</label><input type="password" id="pass" name="pass" autocomplete="new-password">
    <small>Local XAMPP/WAMP: leave empty when the username is <code>root</code>.</small>
    <label class="chk"><input type="checkbox" name="create" value="1" <?= $v['create'] ? 'checked' : '' ?>> Create the database if it does not exist <span style="color:#66727f">(works on your own computer; on cPanel create it in “MySQL Databases” first)</span></label>
    <hr>
    <strong>Admin account (recommended)</strong>
    <label for="admin_pass">New admin password</label><input type="password" id="admin_pass" name="admin_pass" autocomplete="new-password">
    <small>8+ characters with upper-case, lower-case and a number. Leave empty to keep the password <code>admin</code> (change it later).</small>
    <label for="admin_pass2">Repeat admin password</label><input type="password" id="admin_pass2" name="admin_pass2" autocomplete="new-password">
    <label for="email">Admin email <span style="font-weight:400;color:#66727f">(optional)</span></label><input type="email" id="email" name="email" value="<?= $h($v['email']) ?>">
    <?php if (!empty($v['_exists'])): ?>
      <hr><div class="warn">Tables already exist in this database.</div>
      <label class="chk"><input type="checkbox" name="only_connect" value="1" checked> Only save the connection (keep my existing data)</label>
      <label class="chk"><input type="checkbox" name="replace" value="1"> Replace existing data (deletes everything and re-creates the starting content)</label>
    <?php endif; ?>
    <button type="submit">Install &amp; connect database</button>
  </form>
<?php endif; ?>
</div></body></html>
