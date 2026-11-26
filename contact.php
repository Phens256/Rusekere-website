<?php
require_once __DIR__ . '/includes/functions.php';
$errors = []; $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = ['name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'subject' => trim($_POST['subject'] ?? ''), 'message' => trim($_POST['message'] ?? '')];
    // Anti-spam: honeypot, minimum fill time, session rate limit
    if (!empty($_POST['website'])) { flash('success', 'Thank you! Your message has been sent.'); redirect('contact.php'); }
    if (time() - (int)($_SESSION['form_t'] ?? 0) < 4) $errors[] = 'Please take a moment to complete the form, then submit again.';
    $_SESSION['sent'] = array_filter($_SESSION['sent'] ?? [], fn($t) => $t > time() - 600);
    if (count($_SESSION['sent']) >= 3) $errors[] = 'You have sent several messages recently. Please try again in a few minutes.';
    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 120) $errors[] = 'Please enter your name (2–120 characters).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) $errors[] = 'Please enter a valid email address.';
    if (mb_strlen($old['subject']) < 3 || mb_strlen($old['subject']) > 200) $errors[] = 'Please enter a subject (3–200 characters).';
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 3000) $errors[] = 'Your message must be 10–3000 characters.';
    if (preg_match('/[\r\n]/', $old['name'] . $old['email'] . $old['subject'])) $errors[] = 'Invalid characters in the form.';
    if (preg_match_all('#https?://#i', $old['message']) > 2) $errors[] = 'Please remove extra links from your message.';
    if (!$errors) {
        $c = db()->prepare('SELECT COUNT(*) FROM contact_messages WHERE sender_email=? AND submitted_at > NOW() - INTERVAL 10 MINUTE');
        $c->execute([$old['email']]);
        if ($c->fetchColumn() >= 3) $errors[] = 'Too many messages from this address. Please try again later.';
    }
    if (!$errors) {
        db()->prepare('INSERT INTO contact_messages (sender_name, sender_email, subject, message) VALUES (?,?,?,?)')
            ->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
        $_SESSION['sent'][] = time();
        flash('success', 'Thank you! Your message has been sent to the school office.');
        redirect('contact.php');
    }
}
$_SESSION['form_t'] = $_SESSION['form_t'] ?? time();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') $_SESSION['form_t'] = time();
$pageTitle = 'Contact Us'; $active = 'contact';
require __DIR__ . '/includes/header.php';
?>
<section class="banner"><div class="wrap"><h1><?= c('contact.banner_title') ?></h1><p><?= c('contact.banner_sub') ?></p></div></section>
<section class="section"><div class="wrap contact-grid">
  <div>
    <h2><?= c('contact.touch_title') ?></h2>
    <div class="info-row"><i class="fa-solid fa-school"></i><div><strong><?= e(site('name')) ?></strong><br><span class="meta"><?= e(site('motto')) ?></span></div></div>
    <div class="info-row"><i class="fa-solid fa-location-dot"></i><div><strong>Location</strong><br><?= e(site('location')) ?></div></div>
    <div class="info-row"><i class="fa-solid fa-phone"></i><div><strong>Telephone</strong><br><?= e(site('phone')) ?><?php if (site('phone2')): ?><br><?= e(site('phone2')) ?><?php endif; ?></div></div>
    <div class="info-row"><i class="fa-solid fa-envelope"></i><div><strong>Email</strong><br><a href="mailto:<?= e(site('email')) ?>"><?= e(site('email')) ?></a><?php if (site('email2')): ?><br><a href="mailto:<?= e(site('email2')) ?>"><?= e(site('email2')) ?></a><?php endif; ?></div></div>
    <div class="info-row"><i class="fa-solid fa-inbox"></i><div><strong>Postal address</strong><br><?= e(trim(site('po_box') . (site('postal_town') ? ', ' . site('postal_town') : ''), ', ')) ?></div></div>
    <p><a class="btn" href="<?= e(site('maps_url')) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-map-location-dot"></i> Find us on Google Maps</a></p>
  </div>
  <div class="card"><div class="body">
    <h2><?= c('contact.form_title') ?></h2>
    <?php show_flash(); foreach ($errors as $er) echo '<div class="alert alert-danger">' . e($er) . '</div>'; ?>
    <form method="post" action="<?= url('contact.php') ?>" novalidate>
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
      <label for="name">Your name</label><input type="text" id="name" name="name" maxlength="120" required value="<?= e($old['name']) ?>">
      <label for="email">Email address</label><input type="email" id="email" name="email" maxlength="150" required value="<?= e($old['email']) ?>">
      <label for="subject">Subject</label><input type="text" id="subject" name="subject" maxlength="200" required value="<?= e($old['subject']) ?>">
      <label for="message">Message</label><textarea id="message" name="message" maxlength="3000" required><?= e($old['message']) ?></textarea>
      <p><button class="btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Send Message</button></p>
    </form>
  </div></div>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
