
<?php include __DIR__ . '/header.php'; ?>

<div class="container">
  <h1>Oppskrift Chatbot</h1>

  <!-- Velkomstmelding hvis brukeren er logget inn -->
  <?php $currentUser = $currentUser ?? $_SESSION['user_email'] ?? null; ?>
  <?php if ($currentUser): ?>
    <p class="page-greeting">Velkommen tilbake, <?php echo htmlspecialchars($currentUser); ?>!</p>
  <?php endif; ?>

  <!-- Chat input -->
  <form method="post" style="margin-top:20px;">
    <label for="chat-q">Skriv spørsmål eller kommando (f.eks. "kategori", "tilfeldig", "fra Italy"):</label>
    <div style="display:flex;gap:.5rem;margin-top:.5rem;">
      <input id="chat-q" name="q" type="text" value="<?php echo htmlspecialchars($_POST['q'] ?? ''); ?>"
             placeholder="Hva vil du vite? (kategori / tilfeldig / fra Norge / historikk)" style="flex:1;padding:.5rem;">
      <button type="submit">Send</button>
    </div>
  </form>

  <!-- Response text -->
  <?php if (!empty($response)): ?>
    <div style="margin-top:16px;padding:10px;border:1px solid #eee;background:#fafafa;">
      <strong>Bot:</strong> <?php echo htmlspecialchars($response); ?>
    </div>
  <?php endif; ?>

  <!-- Categories -->
  <?php if (!empty($allCategories)): ?>
    <p style="margin-top:12px"><strong>Kategorier:</strong> <?php echo implode(", ", array_map('htmlspecialchars', $allCategories)); ?></p>
  <?php endif; ?>

  <!-- Random meal -->
  <?php if (!empty($randomMeal)): ?>
    <div style="border:1px solid #ccc; padding:10px; width:300px; margin-top:12px;">
      <h2><?php echo htmlspecialchars($randomMeal['name']); ?></h2>
      <img src="<?php echo htmlspecialchars($randomMeal['thumbnail']); ?>" alt="" style="width:100%;">
      <p><strong>Kategori:</strong> <?php echo htmlspecialchars($randomMeal['category']); ?></p>
      <p><strong>Område:</strong> <?php echo htmlspecialchars($randomMeal['area']); ?></p>
      <p><?php echo nl2br(htmlspecialchars($randomMeal['instructions'] ?? '')); ?></p>
    </div>
  <?php endif; ?>

  <!-- Recipes by area -->
  <?php if (!empty($recipesByArea)): ?>
    <h2 style="margin-top:16px">Oppskrifter fra "<?php echo htmlspecialchars($area ?? ''); ?>"</h2>
    <div style="display:flex; flex-wrap:wrap; gap:20px; margin-top:10px;">
      <?php foreach ($recipesByArea as $recipe): ?>
        <div style="border:1px solid #ccc; padding:10px; width:200px;">
          <img src="<?php echo htmlspecialchars($recipe['thumbnail']); ?>" alt="" style="width:100%;">
          <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Search results fallback -->
  <?php if (!empty($searchResults)): ?>
    <h2 style="margin-top:16px">Søkeresultater</h2>
    <ul>
      <?php foreach ($searchResults as $r): ?>
        <li><?php echo htmlspecialchars($r['name'] ?? $r['title'] ?? ''); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/footer.php'; ?>