<!DOCTYPE html>
<html>
<head>
    <title>Mat-chatbot</title>
    <meta charset="UTF-8">
    <!-- Dynamisk generering av sti til CSS-fil -->
    <?php
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $root = (strpos($scriptDir, '/public') !== false)
        ? rtrim(str_replace('/public', '', $scriptDir), '/')
        : rtrim($scriptDir, '/');
    $cssUrl = $root . '/public/css/style.css';
    ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssUrl); ?>">
</head>
<body>
    <div class="header">
        <div class="tittel">
            <h1>Mat-chatbot</h1>
        </div>
        <button><a href="/login.php">Logg inn</a></button>
    </div>
