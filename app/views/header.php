<!DOCTYPE html>
<html>
<head>
    <title>Mat-chatbot</title>
    <meta charset="UTF-8">
    <!-- Dynamisk generering av sti til CSS-fil -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>/css/style.css">
</head>
<body>
<header class="header">
    <div class="tittel">
        <h1><a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=chatbot">Mat-chatbot</a></h1>
    </div>

    <nav class="right-links" aria-label="Hovedlenker">
        <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=login">Logg inn</a>
        <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=register">Registrer</a>
        <a href="<?php echo htmlspecialchars(BASE_URL); ?>/?page=history">Historikk</a>
    </nav>
</header>
