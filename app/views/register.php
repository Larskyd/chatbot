<?php include __DIR__ . '/header.php'; ?>

<div class="auth-page">
  <div class="login-container auth-card">

    <?php if (!empty($_GET['saved'])): ?>
        <div class="flash flash-success">Bruker registrert.</div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/?page=register'); ?>" class="auth-form" autocomplete="off" novalidate>
        <input type="hidden" name="action" value="register">
        <h2>Registrer</h2>

        <label for="name">Navn</label>
        <input id="name" name="name" type="text" required>

        <label for="reg_email">E-post</label>
        <input id="reg_email" name="email" type="email" required autofocus>

        <label for="reg_password">Passord</label>
        <input id="reg_password" name="password" type="password" required>

        <div class="auth-actions">
            <button type="submit" class="btn">Registrer</button>
            <a class="link-muted" href="<?php echo htmlspecialchars(BASE_URL . '/?page=login'); ?>">Allerede bruker? Logg inn</a>
        </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>