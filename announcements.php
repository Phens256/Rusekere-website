<?php
$pageTitle = 'Announcements'; $active = 'announcements';
require __DIR__ . '/includes/header.php';
$pdo = db();
$cats = ['Important', 'General', 'Admissions', 'Academic', 'Events', 'Sports'];
$cat = in_array($_GET['cat'] ?? '', $cats, true) ? $_GET['cat'] : '';
$per = 8; $page = page_num();
$where = 'published_status=1' . ($cat ? ' AND category=:c' : '');
$cnt = $pdo->prepare("SELECT COUNT(*) FROM announcements WHERE $where"); $cat ? $cnt->execute([':c' => $cat]) : $cnt->execute();
$total = (int)$cnt->fetchColumn();
$st = $pdo->prepare("SELECT * FROM announcements WHERE $where ORDER BY (category='Important') DESC, created_at DESC LIMIT :l OFFSET :o");
if ($cat) $st->bindValue(':c', $cat);
$st->bindValue(':l', $per, PDO::PARAM_INT); $st->bindValue(':o', ($page - 1) * $per, PDO::PARAM_INT); $st->execute();
$rows = $st->fetchAll();
?>
<section class="banner"><div class="wrap"><h1><?= c('announcements.banner_title') ?></h1><p><?= c('announcements.banner_sub') ?></p></div></section>
<section class="section"><div class="wrap" style="max-width:960px">
  <div class="filters"><a href="<?= url('announcements.php') ?>" class="<?= !$cat ? 'on' : '' ?>">All</a>
  <?php foreach ($cats as $c): ?><a href="<?= url('announcements.php?cat=' . urlencode($c)) ?>" class="<?= $cat === $c ? 'on' : '' ?>"><?= e($c) ?></a><?php endforeach; ?></div>
  <?php if (!$rows): ?><p class="empty">No announcements to show.</p><?php endif; ?>
  <?php foreach ($rows as $a): $imp = $a['category'] === 'Important'; ?>
  <article class="card item <?= $imp ? 'important' : '' ?> <?= $a['image'] ? '' : 'noimg' ?>" id="a<?= (int)$a['id'] ?>">
    <?php if ($a['image']): ?><img src="<?= img($a['image']) ?>" alt="<?= e($a['title']) ?>" loading="lazy"><?php endif; ?>
    <div>
      <span class="tag <?= $imp ? 'imp' : '' ?>"><?= $imp ? '<i class="fa-solid fa-bell"></i> ' : '' ?><?= e($a['category']) ?></span>
      <h3 style="margin-top:.6rem"><?= e($a['title']) ?></h3>
      <div class="meta"><i class="fa-regular fa-calendar"></i> <?= e(fmt_date($a['created_at'])) ?></div>
      <p style="color:var(--ink)"><?= nl2br(e($a['description'])) ?></p>
    </div>
  </article>
  <?php endforeach; ?>
  <?= pager($total, $per, $page, 'announcements.php' . ($cat ? '?cat=' . urlencode($cat) : '')) ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
