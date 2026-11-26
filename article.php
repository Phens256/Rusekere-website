<?php
require_once __DIR__ . '/includes/functions.php';
$st = db()->prepare('SELECT n.*, u.full_name FROM news n LEFT JOIN users u ON u.id=n.author_id WHERE n.slug=? AND n.published_status=1');
$st->execute([(string)($_GET['slug'] ?? '')]);
$a = $st->fetch();
if (!$a) { http_response_code(404); $pageTitle = 'Article not found'; $active = 'news'; require __DIR__ . '/includes/header.php';
  echo '<section class="section"><div class="wrap"><p class="empty">Sorry, that article could not be found. <a href="' . url('news.php') . '">Back to news</a></p></div></section>';
  require __DIR__ . '/includes/footer.php'; exit; }
$pageTitle = $a['title']; $active = 'news';
require __DIR__ . '/includes/header.php';
$more = db()->prepare('SELECT title, slug, created_at FROM news WHERE published_status=1 AND id<>? ORDER BY created_at DESC LIMIT 3');
$more->execute([$a['id']]);
?>
<section class="banner"><div class="wrap"><h1 style="font-size:clamp(1.4rem,3vw,2.1rem)"><?= e($a['title']) ?></h1>
<p><?= e(fmt_date($a['created_at'])) ?><?= $a['full_name'] ? ' · By ' . e($a['full_name']) : '' ?></p></div></section>
<section class="section"><div class="wrap" style="max-width:820px">
  <?php if ($a['featured_image']): ?><img class="article-img" src="<?= img($a['featured_image']) ?>" alt="<?= e($a['title']) ?>"><?php endif; ?>
  <div class="prose"><?= nl2br(e($a['content'])) ?></div>
  <p style="margin-top:2rem"><a class="btn btn-light" href="<?= url('news.php') ?>"><i class="fa-solid fa-arrow-left"></i> Back to News</a></p>
  <?php $m = $more->fetchAll(); if ($m): ?><h3 style="margin-top:2.4rem">More news</h3><ul>
    <?php foreach ($m as $x): ?><li><a href="<?= url('article.php?slug=' . urlencode($x['slug'])) ?>"><?= e($x['title']) ?></a> <span class="meta">· <?= e(fmt_date($x['created_at'])) ?></span></li><?php endforeach; ?></ul><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
