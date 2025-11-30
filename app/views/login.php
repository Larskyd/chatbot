<?php include __DIR__ . '/header.php'; ?>

<div class="auth-page">
  <div class="login-container auth-card">

    <!-- Flash-melding for feil hvis bruker ikke er logget inn -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash flash-error"><?php echo htmlspecialchars($_SESSION['flash_error']); ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Melding for vellykket innlogging -->
    <?php if (!empty($_GET['loggedin'])): ?>
        <div class="flash flash-success">Du er logget inn.</div>
    <?php endif; ?>

    <!-- Melding for vellykket utlogging -->
    <?php if (!empty($_GET['loggedout'])): ?>
        <div class="flash flash-success">Du har nå logget ut.</div>
    <?php endif; ?>

    <!-- Vis feil ved innlogging hvis noen -->
    <?php if (!empty($errors) && is_array($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/?page=login'); ?>" class="auth-form" autocomplete="off" novalidate>
        <input type="hidden" name="action" value="login">
        <h2>Logg inn</h2>

        <label for="login_email">E-post</label>
        <input id="login_email" name="email" type="email" required autofocus>

        <label for="login_password">Passord</label>
        <input id="login_password" name="password" type="password" required>

        <div class="auth-actions">
            <button type="submit" class="btn">Logg inn</button>
            <a class="link-muted" href="<?php echo htmlspecialchars(BASE_URL . '/?page=register'); ?>">Registrer ny bruker</a>
        </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>