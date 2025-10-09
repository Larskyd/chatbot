<html>
<head>
    <title>Chatbot</title>
</head>
<body>
    <!--- Inklusjon av header --->
    <?php include 'header.php'; ?>
    <!--- Knapp for å vise frem kategorier --->
    <form class="kategoriknapp" method="post" style="margin-top:20px;">
        <button type="submit" name="showCategories" value="1" style="padding:10px 20px; background-color:#2a7ae2; color:white; border:none; border-radius:5px; cursor:pointer; margin-top:20px;">
            Se alle kategorier
        </button>
    </form>
    <!--- Vis svar fra knappen --->
        <?php if (!empty($allCategories)): ?>
            <p>Tilgjengelige kategorier: <?php echo implode(", ", array_map('htmlspecialchars', $allCategories)); ?></p>
        <?php endif; ?>

    <!--- Inklusjon av footer --->
    <?php include 'footer.php'; ?>
</body>
</html>