<?php
$pageTitle = 'Home'; $active = 'home';
require __DIR__ . '/includes/header.php';
$pdo = db();

$slides = [];
try {
    foreach ($pdo->query('SELECT image_path, heading, caption FROM slider WHERE visible=1 ORDER BY display_order, id')->fetchAll() as $r)
        $slides[] = [$r['image_path'], $r['heading'], $r['caption']];
} catch (Throwable $e) { $slides = []; }
if (!$slides) $slides = [
  ['assets/images/school/administration-block.jpg', 'Welcome to Rusekere Secondary School', 'A Catholic-founded government day and boarding school in Kibasi, Kabarole'],
  ['assets/images/school/block-d.jpg', '40 Years of Educating for Progress', 'Four decades of shaping responsible citizens'],
  ['assets/images/school/block-e.jpg', 'Building Knowledge, Discipline and Character', 'Learning in a safe and orderly environment'],
  ['assets/images/academics/computer-lab-2.jpg', 'Excellence in Academics and Practical Skills', 'O-Level and A-Level education with hands-on learning'],
  ['assets/images/school/water-tank-donation.jpg', 'Learning, Growing and Serving Together', 'A school community that works together'],
];

$leaders = $pdo->query("SELECT * FROM staff WHERE status='active' ORDER BY display_order, id LIMIT 4")->fetchAll();

$latest = [];
foreach ($pdo->query("SELECT id, title, description AS txt, created_at, category FROM announcements WHERE published_status=1 ORDER BY created_at DESC LIMIT 3") as $r)
    $latest[] = ['type' => 'Announcement', 'title' => $r['title'], 'txt' => $r['txt'], 'date' => $r['created_at'], 'link' => url('announcements.php#a' . $r['id']), 'cat' => $r['category']];
foreach ($pdo->query("SELECT title, slug, content AS txt, created_at FROM news WHERE published_status=1 ORDER BY created_at DESC LIMIT 3") as $r)
    $latest[] = ['type' => 'News', 'title' => $r['title'], 'txt' => $r['txt'], 'date' => $r['created_at'], 'link' => url('article.php?slug=' . urlencode($r['slug'])), 'cat' => 'News'];
usort($latest, fn($a, $b) => strcmp($b['date'], $a['date']));
$latest = array_slice($latest, 0, 3);

$stats = [];
try {
    foreach ($pdo->query('SELECT label, stat_value, suffix, note FROM site_stats ORDER BY display_order, id')->fetchAll() as $r)
        $stats[] = [$r['stat_value'], $r['suffix'], $r['label'], $r['note']];
} catch (Throwable $e) { $stats = []; }
if (!$stats) $stats = [
  ['40', '+', 'Years of Excellence', ''],
  [site('students'), '', 'Students', ''],
  [site('teachers'), '', 'Teachers', ''],
  ['2', '', 'Academic Levels', 'O-Level & A-Level'],
];
?>
<section class="slider" aria-label="School photographs">
  <?php foreach ($slides as $i => [$src, $h, $p]): ?>
  <div class="slide">
    <img src="<?= url($src) ?>" alt="<?= e($h) ?>" <?= $i ? 'loading="lazy"' : '' ?>>
    <div class="cap"><h2><?= e($h) ?></h2><p><?= e($p) ?></p><a class="btn" href="<?= url('about.php') ?>"><?= c('home.slider_btn') ?></a></div>
  </div>
  <?php endforeach; ?>
  <button class="prev" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="next" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
  <div class="dots"></div>
</section>

<section class="section"><div class="wrap split">
  <div class="reveal">
    <span class="eyebrow"><?= c('home.welcome_eyebrow') ?></span>
    <h2><?= c('home.welcome_title') ?></h2>
    <p><?= c('home.welcome_p1') ?></p>
    <p><?= c('home.welcome_p2') ?></p>
    <a class="btn" href="<?= url('about.php') ?>"><?= c('home.welcome_btn') ?></a>
  </div>
  <img class="reveal" src="<?= ci('home.welcome_img') ?>" alt="Entrance gate of Rusekere Secondary School" loading="lazy">
</div></section>

<section class="stats"><div class="wrap grid g4">
  <?php foreach ($stats as [$n, $suf, $label, $note]): ?>
  <div class="stat">
    <?php if ($n !== null && $n !== ''): ?><b data-count="<?= (int)$n ?>" data-suffix="<?= e($suf) ?>">0</b>
    <?php else: ?><b>TBC</b><?php endif; ?>
    <span><?= e($label) ?></span><?php if ($note): ?><small><?= e($note) ?></small><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div></section>

<section class="section alt"><div class="wrap">
  <div class="sec-head"><h2><?= c('home.leaders_title') ?></h2><p><?= c('home.leaders_sub') ?></p></div>
  <div class="grid g4">
    <?php foreach ($leaders as $l): ?>
    <article class="card leader reveal">
      <div class="ph"><?php if ($l['photograph']): ?><img src="<?= img($l['photograph']) ?>" alt="<?= e($l['position']) ?>" loading="lazy"><?php else: ?><i class="fa-solid fa-user-tie"></i><?php endif; ?></div>
      <div class="body"><h3><?= e($l['full_name']) ?></h3><div class="pos"><?= e($l['position']) ?></div><p><?= e(excerpt((string)$l['biography'], 130)) ?></p></div>
    </article>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="section"><div class="wrap">
  <div class="sec-head"><h2><?= c('home.acad_title') ?></h2><p><?= c('home.acad_sub') ?></p></div>
  <div class="grid g3">
    <div class="tile reveal"><i class="fa-solid fa-book-open"></i><h3><?= c('home.t1_title') ?></h3><p><?= c('home.t1_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-graduation-cap"></i><h3><?= c('home.t2_title') ?></h3><p><?= c('home.t2_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-chalkboard-user"></i><h3><?= c('home.t3_title') ?></h3><p><?= c('home.t3_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-scale-balanced"></i><h3><?= c('home.t4_title') ?></h3><p><?= c('home.t4_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-seedling"></i><h3><?= c('home.t5_title') ?></h3><p><?= c('home.t5_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-computer"></i><h3><?= c('home.t6_title') ?></h3><p><?= c('home.t6_text') ?></p></div>
  </div>
  <div class="grid g3" style="margin-top:1.5rem">
    <div class="photo-card reveal"><img src="<?= url('assets/images/academics/computer-lab-1.jpg') ?>" alt="Students in the computer laboratory" loading="lazy"><div><h3><?= c('home.ph1') ?></h3></div></div>
    <div class="photo-card reveal"><img src="<?= url('assets/images/academics/science-practical.jpg') ?>" alt="Students doing a science practical" loading="lazy"><div><h3><?= c('home.ph2') ?></h3></div></div>
    <div class="photo-card reveal"><img src="<?= url('assets/images/academics/classroom.jpg') ?>" alt="Students in a classroom" loading="lazy"><div><h3><?= c('home.ph3') ?></h3></div></div>
  </div>
</div></section>

<section class="section cream"><div class="wrap">
  <div class="sec-head"><h2><?= c('home.sports_title') ?></h2><p><?= c('home.sports_sub') ?></p></div>
  <div class="grid g2">
    <div class="photo-card reveal"><img src="<?= url('assets/images/sports/volleyball.jpg') ?>" alt="Students playing volleyball" loading="lazy"><div><h3><?= c('home.vb_title') ?></h3><p><?= c('home.vb_text') ?></p></div></div>
    <div class="photo-card reveal"><img src="<?= url('assets/images/sports/netball-team.jpg') ?>" alt="Girls' team in matching kit" loading="lazy"><div><h3><?= c('home.nb_title') ?></h3><p><?= c('home.nb_text') ?></p></div></div>
  </div>
</div></section>

<section class="section"><div class="wrap">
  <div class="sec-head"><h2><?= c('home.proj_title') ?></h2><p><?= c('home.proj_sub') ?></p></div>
  <div class="grid g2">
    <article class="card reveal"><img src="<?= url('assets/images/projects/project-shelter-2.jpg') ?>" alt="School cattle-rearing project" loading="lazy"><div class="body"><h3><?= c('home.cattle_title') ?></h3><p><?= c('home.cattle_text') ?></p></div></article>
    <article class="card reveal"><img src="<?= url('assets/images/projects/poultry-house.jpg') ?>" alt="School chicken-rearing project" loading="lazy"><div class="body"><h3><?= c('home.chicken_title') ?></h3><p><?= c('home.chicken_text') ?></p></div></article>
  </div>
  <p class="prose" style="text-align:center;max-width:760px;margin:1.6rem auto 0"><?= c('home.proj_foot') ?></p>
</div></section>

<section class="section alt"><div class="wrap">
  <div class="sec-head"><h2><?= c('home.news_title') ?></h2><p><?= c('home.news_sub') ?></p></div>
  <?php if (!$latest): ?><p class="empty">No announcements or news have been published yet.</p><?php endif; ?>
  <div class="grid g3">
    <?php foreach ($latest as $x): ?>
    <article class="card reveal"><div class="body">
      <span class="tag <?= $x['cat'] === 'Important' ? 'imp' : '' ?>"><?= e($x['type']) ?><?= $x['type'] === 'Announcement' ? ' · ' . e($x['cat']) : '' ?></span>
      <h3 style="margin-top:.7rem"><?= e($x['title']) ?></h3>
      <div class="meta"><i class="fa-regular fa-calendar"></i> <?= e(fmt_date($x['date'])) ?></div>
      <p><?= e(excerpt($x['txt'], 120)) ?></p>
      <a class="more" href="<?= e($x['link']) ?>">READ MORE <i class="fa-solid fa-arrow-right"></i></a>
    </div></article>
    <?php endforeach; ?>
  </div>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
