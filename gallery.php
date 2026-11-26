<?php
$pageTitle = 'Gallery'; $active = 'gallery';
require __DIR__ . '/includes/header.php';
$pdo = db();
$cats = $pdo->query('SELECT DISTINCT category FROM gallery ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
$cat = in_array($_GET['cat'] ?? '', $cats, true) ? $_GET['cat'] : '';
$st = $pdo->prepare('SELECT * FROM gallery' . ($cat ? ' WHERE category=?' : '') . ' ORDER BY created_at DESC, id DESC');
$st->execute($cat ? [$cat] : []);
$rows = $st->fetchAll();
?>
<section class="banner"><div class="wrap"><h1><?= c('gallery.banner_title') ?></h1><p><?= c('gallery.banner_sub') ?></p></div></section>
<section class="section"><div class="wrap">
  <div class="filters"><a href="<?= url('gallery.php') ?>" class="<?= !$cat ? 'on' : '' ?>">All</a>
  <?php foreach ($cats as $c): ?><a href="<?= url('gallery.php?cat=' . urlencode($c)) ?>" class="<?= $cat === $c ? 'on' : '' ?>"><?= e($c) ?></a><?php endforeach; ?></div>
  <?php if (!$rows): ?><p class="empty">No photos yet.</p><?php endif; ?>
  <div class="gal">
    <?php foreach ($rows as $g): ?>
      <a href="<?= img($g['image_path']) ?>" data-lb="<?= img($g['image_path']) ?>" data-cap="<?= e($g['image_title']) ?>"><img src="<?= img($g['image_path']) ?>" alt="<?= e($g['image_title']) ?>" loading="lazy"></a>
    <?php endforeach; ?>
  </div>
</div></section>
<div id="lightbox" role="dialog" aria-label="Photo viewer"><button class="x" aria-label="Close">&times;</button><button class="lp" aria-label="Previous">&#10094;</button><img src="" alt=""><p></p><button class="ln" aria-label="Next">&#10095;</button></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
