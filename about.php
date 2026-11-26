<?php
$pageTitle = 'About Us'; $active = 'about';
require __DIR__ . '/includes/header.php';
?>
<section class="banner" style="--bn:url('<?= url('assets/images/school/administration-block.jpg') ?>')"><div class="wrap"><h1><?= c('about.banner_title') ?></h1><p><?= ct('about.banner_sub') !== '' ? c('about.banner_sub') : 'Rusekere Secondary School · ' . e(site('motto')) ?></p></div></section>

<section class="section"><div class="wrap split">
  <div class="prose reveal">
    <span class="eyebrow"><?= c('about.hist_eyebrow') ?></span><h2><?= c('about.hist_title') ?></h2>
    <p><?= c('about.hist_p1') ?></p>
    <?= ch('about.hist_p2') ?>
  </div>
  <img class="reveal" src="<?= ci('about.hist_img') ?>" alt="School building at Rusekere Secondary School" loading="lazy">
</div></section>

<section class="section alt"><div class="wrap split">
  <img class="reveal" src="<?= ci('about.found_img') ?>" alt="Students and staff at Rusekere Secondary School" loading="lazy">
  <div class="prose reveal">
    <span class="eyebrow"><?= c('about.found_eyebrow') ?></span><h2><?= c('about.found_title') ?></h2>
    <?= ch('about.found_text') ?>
    <div class="quote"><?= c('about.found_quote') ?></div>
  </div>
</div></section>

<section class="section"><div class="wrap">
  <div class="grid g2">
    <div class="tile reveal"><i class="fa-solid fa-eye"></i><h3><?= c('about.vision_title') ?></h3><p><?= c('about.vision_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-bullseye"></i><h3><?= c('about.mission_title') ?></h3><p><?= c('about.mission_text') ?></p></div>
  </div>
  <div class="sec-head" style="margin-top:3rem"><h2><?= c('about.values_title') ?></h2></div>
  <div class="grid g4">
    <div class="tile reveal"><i class="fa-solid fa-star"></i><h3><?= c('about.v1_title') ?></h3><p><?= c('about.v1_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-scale-balanced"></i><h3><?= c('about.v2_title') ?></h3><p><?= c('about.v2_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-hands-holding-circle"></i><h3><?= c('about.v3_title') ?></h3><p><?= c('about.v3_text') ?></p></div>
    <div class="tile reveal"><i class="fa-solid fa-people-group"></i><h3><?= c('about.v4_title') ?></h3><p><?= c('about.v4_text') ?></p></div>
  </div>
</div></section>

<section class="section cream"><div class="wrap">
  <div class="sec-head"><h2><?= c('about.prog_title') ?></h2></div>
  <div class="grid g2">
    <div class="card"><img src="<?= url('assets/images/academics/classroom.jpg') ?>" alt="Classroom learning" loading="lazy"><div class="body"><h3><?= c('about.ol_title') ?></h3><p><?= c('about.ol_text') ?></p></div></div>
    <div class="card"><img src="<?= url('assets/images/academics/a-level-students.jpg') ?>" alt="A-Level students at Rusekere Secondary School" loading="lazy"><div class="body"><h3><?= c('about.al_title') ?></h3><p><?= c('about.al_text') ?></p></div></div>
  </div>
</div></section>

<section class="section"><div class="wrap split">
  <div class="prose reveal">
    <span class="eyebrow"><?= c('about.fac_eyebrow') ?></span><h2><?= c('about.fac_title') ?></h2>
    <p><?= c('about.fac_text') ?></p>
    <ul><?= cl('about.fac_list') ?></ul>
  </div>
  <img class="reveal" src="<?= ci('about.fac_img') ?>" alt="School dormitory" loading="lazy">
</div></section>

<section class="section alt"><div class="wrap">
  <div class="sec-head"><h2><?= c('about.sp_title') ?></h2><p><?= c('about.sp_sub') ?></p></div>
  <div class="grid g3">
    <div class="photo-card"><img src="<?= url('assets/images/sports/volleyball-2.jpg') ?>" alt="Volleyball" loading="lazy"><div><h3>Volleyball &amp; Netball</h3></div></div>
    <div class="photo-card"><img src="<?= url('assets/images/projects/project-shelter-2.jpg') ?>" alt="School project" loading="lazy"><div><h3>Cattle Rearing</h3></div></div>
    <div class="photo-card"><img src="<?= url('assets/images/projects/poultry-house.jpg') ?>" alt="Chickens in the school poultry house" loading="lazy"><div><h3>Chicken Rearing</h3></div></div>
  </div>
  <p style="text-align:center;margin-top:2rem"><a class="btn" href="<?= url('gallery.php') ?>"><?= c('about.btn1') ?></a> <a class="btn btn-brown" href="<?= url('contact.php') ?>"><?= c('about.btn2') ?></a></p>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
