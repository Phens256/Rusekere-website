<?php
$pageTitle = 'News'; $active = 'news';
require __DIR__ . '/includes/header.php';
$pdo = db(); $per = 6; $page = page_num();
$total = (int)$pdo->query('SELECT COUNT(*) FROM news WHERE published_status=1')->fetchColumn();
$st = $pdo->prepare('SELECT n.*, u.full_name FROM news n LEFT JOIN users u ON u.id=n.author_id WHERE n.published_status=1 ORDER BY n.created_at DESC LIMIT :l OFFSET :o');
$st->bindValue(':l', $per, PDO::PARAM_INT); $st->bindValue(':o', ($page - 1) * $per, PDO::PARAM_INT); $st->execute();
$rows = $st->fetchAll();
?>
<section class="banner"><div class="wrap"><h1><?= c('news.banner_title') ?></h1><p><?= c('news.banner_sub') ?></p></div></section>
<section class="section"><div class="wrap">
  <?php if (!$rows): ?><p class="empty">No news articles have been published yet.</p><?php endif; ?>
  <div class="grid g3">
  <?php foreach ($rows as $n): ?>
    <article class="card reveal">
      <a href="<?= url('article.php?slug=' . urlencode($n['slug'])) ?>"><img src="<?= img($n['featured_image']) ?>" alt="<?= e($n['title']) ?>" loading="lazy"></a>
      <div class="body">
        <div class="meta"><i class="fa-regular fa-calendar"></i> <?= e(fmt_date($n['created_at'])) ?></div>
        <h3 style="margin-top:.4rem"><a href="<?= url('article.php?slug=' . urlencode($n['slug'])) ?>" style="color:inherit"><?= e($n['title']) ?></a></h3>
        <p><?= e(excerpt($n['content'], 130)) ?></p>
        <a class="more" href="<?= url('article.php?slug=' . urlencode($n['slug'])) ?>">READ MORE <i class="fa-solid fa-arrow-right"></i></a>
      </div>
    </article>
  <?php endforeach; ?>
  </div>
  <?= pager($total, $per, $page, 'news.php') ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
