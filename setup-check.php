<?php
// Local setup checker – open http://localhost/rusekere-school/setup-check.php
// Only works from the same computer. DELETE this file before uploading the site to a live server.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(404); exit('Not found.'); }
require __DIR__ . '/config/database.php';
$rows = [];
$add = function (string $name, bool $ok, string $detail = '') use (&$rows) { $rows[] = [$name, $ok, $detail]; };

$add('PHP version (7.4 or newer)', version_compare(PHP_VERSION, '7.4.0', '>='), PHP_VERSION);
foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'gd'] as $ext) $add("PHP extension: $ext", extension_loaded($ext), extension_loaded($ext) ? 'enabled' : 'MISSING – enable it in php.ini');
$add('Detected BASE_URL', true, BASE_URL === '' ? '(site root)' : BASE_URL);
$add('Project folder', true, ROOT_PATH);

$pageFiles = ['index.php','about.php','announcements.php','news.php','article.php','gallery.php','contact.php','admin/login.php','admin/dashboard.php','admin/change_password.php','admin/announcements/index.php','admin/news/index.php','admin/gallery/index.php','admin/staff/index.php','admin/messages/index.php','admin/users/index.php'];
foreach ($pageFiles as $f) $add("File: $f", is_file(ROOT_PATH . '/' . $f), is_file(ROOT_PATH . '/' . $f) ? 'found' : 'MISSING');

try {
    $pdo = db();
    $add('Database connection (' . DB_NAME . ')', true, 'connected');
    foreach (['users','announcements','news','gallery','staff','contact_messages','login_attempts','site_stats','slider'] as $t) {
        try { $n = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(); $add("Table: $t", true, "$n row(s)"); }
        catch (Throwable $e) { $add("Table: $t", false, 'MISSING – import database/rusekere_school.sql'); }
    }
} catch (Throwable $e) { $add('Database connection', false, $e->getMessage()); }

foreach (['uploads/news','uploads/gallery','uploads/staff','uploads/announcements'] as $d)
    $add("Writable: $d", is_writable(ROOT_PATH . '/' . $d), is_writable(ROOT_PATH . '/' . $d) ? 'yes' : 'NOT writable – image uploads will fail');
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Setup check</title>
<style>body{font-family:system-ui,Arial,sans-serif;max-width:820px;margin:2rem auto;padding:0 1rem}table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:.45rem .6rem;text-align:left}.ok{color:#0a7a2f;font-weight:600}.bad{color:#c0262d;font-weight:600}</style></head><body>
<h1>Rusekere School – setup check</h1>
<table><tr><th>Check</th><th>Result</th><th>Details</th></tr>
<?php foreach ($rows as [$n, $ok, $d]): ?><tr><td><?= htmlspecialchars($n) ?></td><td class="<?= $ok ? 'ok' : 'bad' ?>"><?= $ok ? 'OK' : 'PROBLEM' ?></td><td><?= htmlspecialchars($d) ?></td></tr><?php endforeach; ?>
</table>
<p>Fix every <span class="bad">PROBLEM</span>, then open the <a href="<?= htmlspecialchars(BASE_URL) ?>/index.php">home page</a>. Delete this file before going live.</p>
</body></html>
