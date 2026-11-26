<?php
require_once __DIR__ . '/includes/functions.php';
$slug = (string)($_GET['slug'] ?? '');
$pg = null;
try {
    $st = db()->prepare('SELECT * FROM pages WHERE slug = ?'); $st->execute([$slug]); $pg = $st->fetch() ?: null;
} catch (Throwable $e) { $pg = null; }
// Unpublished pages can only be previewed by a logged-in administrator.
if ($pg && !$pg['published_status'] && !(is_admin())) $pg = null;
if (!$pg) { http_response_code(404); $pageTitle = 'Page not found'; $active = ''; }
else { $pageTitle = $pg['title']; $active = 'page-' . $pg['slug']; }
require __DIR__ . '/includes/header.php';
if (!$pg): ?>
<section class="banner"><div class="wrap"><h1>Page not found</h1><p>Sorry, we could not find that page.</p></div></section>
<section class="section"><div class="wrap"><p class="empty">Please use the menu above, or <a href="<?= url('index.php') ?>">return to the home page</a>.</p></div></section>
<?php else: ?>
<section class="banner"<?= $pg['banner_image'] ? ' style="--bn:url(\'' . e(url($pg['banner_image'])) . '\')"' : '' ?>><div class="wrap"><h1><?= e($pg['title']) ?></h1><?php if ($pg['subtitle'] !== ''): ?><p><?= e($pg['subtitle']) ?></p><?php endif; ?></div></section>
<section class="section"><div class="wrap" style="max-width:860px">
  <?php if (!$pg['published_status']): ?><div class="alert alert-warning">Draft preview – visitors cannot see this page until you publish it.</div><?php endif; ?>
  <div class="prose"><?= cms_block_html((string)$pg['content']) ?></div>
</div></section>
<?php endif;
require __DIR__ . '/includes/footer.php';
