<?php include __DIR__ . '/header.php'; ?>

<h1>Oppskrift Chatbot</h1>

<!-- Kategori visnings knapp -->
<form method="post" style="margin-top:20px;">
  <button type="submit" name="showCategories" value="1" id="showCategories">Se alle kategorier</button>
</form>

<!-- Vis kategorier hvis tilgjengelig -->
<?php if (!empty($allCategories)): ?>
  <p>Tilgjengelige kategorier:
    <?php echo implode(", ", array_map('htmlspecialchars', $allCategories)); ?>
  </p>
<?php endif; ?>

<!-- Område basert oppskriftssøk -->
<form method="post" style="margin-top:20px;">
  <label for="area">Skriv inn et område (land):</label>
  <input type="text" id="area" name="area" placeholder="F.eks. Canadian">
  <button type="submit" name="showRecipesByArea" value="1">Se oppskrifter etter område</button>
</form>

<!-- Vis oppskrifter basert på område hvis tilgjengelig -->
<?php if (!empty($recipesByArea)): ?>
  <h2>Oppskrifter fra området "<?php echo htmlspecialchars($area); ?>"</h2>
  <div style="display:flex; flex-wrap:wrap; gap:20px; margin-top:20px;">
    <?php foreach ($recipesByArea as $recipe): ?>
      <div style="border:1px solid #ccc; padding:10px; width:200px;">
        <img src="<?php echo htmlspecialchars($recipe['thumbnail']); ?>"
             alt="<?php echo htmlspecialchars($recipe['name']); ?>"
             style="width:100%;">
        <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
      </div>
    <?php endforeach; ?>
  </div>
<?php elseif (isset($_POST['showRecipesByArea'])): ?>
  <p>Ingen oppskrifter funnet for området "<?php echo htmlspecialchars($area); ?>".</p>
<?php endif; ?>

<!-- Footer inkludering -->
<?php include __DIR__ . '/footer.php'; ?>
