<?php include __DIR__ . '/header.php'; ?>

<div class="container">

    <?php if (!empty($_GET['saved'])): ?>
        <p style="color:green;">Bruker registrert.</p>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div style="display:flex;gap:40px;flex-wrap:wrap;">
        <!-- Register form -->
        <form method="post" action="<?php echo htmlspecialchars(BASE_URL . '/?page=register'); ?>">
            <input type="hidden" name="action" value="register">
            <h2>Registrer</h2>
            <label for="reg_email">E-post</label><br>
            <input id="reg_email" name="email" type="email" required><br><br>

            <label for="reg_password">Passord</label><br>
            <input id="reg_password" name="password" type="password" required><br><br>

            <button type="submit" class="btn">Registrer</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>