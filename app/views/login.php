<?php include __DIR__ . '/header.php'; ?>

<div class="container">

    <?php if (!empty($_GET['loggedin'])): ?>
        <p style="color:green;">Du er logget inn.</p>
    <?php endif; ?>

    <?php if (!empty($_GET['loggedout'])): ?>
        <p style="color:green;">Du er logget ut.</p>
    <?php endif; ?>


    <?php if (!empty($errors) && is_array($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div style="display:flex;gap:40px;flex-wrap:wrap;">
        <!-- Login form -->
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/?page=login'); ?>">
            <input type="hidden" name="action" value="login">
            <h2>Logg inn</h2>
            <label for="login_email">E-post</label><br>
            <input id="login_email" name="email" type="email" required><br><br>

            <label for="login_password">Passord</label><br>
            <input id="login_password" name="password" type="password" required><br><br>

            <button type="submit" class="btn">Logg inn</button>
        </form>
    </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>