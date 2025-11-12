<!DOCTYPE html>
<html>

<head>
    <title>Mat-chatbot</title>
    <meta charset="UTF-8">
    <!-- Dynamisk generering av sti til CSS-fil -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>/css/style.css">
</head>
<?php error_log('SESSION: ' . print_r($_SESSION, true)); ?>

<body>
    <header class="header">
        <div class="tittel">
            <?php if (!empty($_SESSION['user_email'])): ?>
                <h1><a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=chatbot">Mat-chatbot</a></h1>
            <?php else: ?>
                <h1>Mat-chatbot</h1>
            <?php endif; ?>
        </div>

        <nav class="right-links" aria-label="Hovedlenker">
            <?php if (!empty($_SESSION['user_email'])): ?>
                <div class="user-info"><?php echo 'Innlogget som "' . htmlspecialchars($_SESSION['user_email']) . '"'; ?></div>
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=logout">Logg ut</a>
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=history">Historikk</a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=login">Logg inn</a>
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=register">Registrer</a>
            <?php endif; ?>
        </nav>
    </header>