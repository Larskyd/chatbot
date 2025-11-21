<?php include __DIR__ . '/header.php'; ?>

<div class="container">

  <h1>Oppskrift Chatbot</h1>

  <?php $currentUser = $currentUser ?? $_SESSION['user_email'] ?? null; ?>

  <!-- Velkomstmelding hvis bruker er logget inn -->
  <?php if ($currentUser): ?>
    <p class="page-greeting">Velkommen tilbake, <?php echo htmlspecialchars($currentUser); ?>!</p>
  <?php endif; ?>

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

    <!-- Tilfeldig matrett knapp -->
  <form method="post" style="margin-top:20px;">
    <button type="submit" name="randomMeal" value="1" id="randomMeal">Få en tilfeldig matrett</button>
  </form>

  <!-- Vis tilfeldig matrett hvis tilgjengelig -->
  <?php if (!empty($randomMeal)): ?>
    <div style="border:1px solid #ccc; padding:10px; width:300px; margin-top:20px;">
      <h2><?php echo htmlspecialchars($randomMeal['name']); ?></h2>
      <img src="<?php echo htmlspecialchars($randomMeal['thumbnail']); ?>"
        alt="<?php echo htmlspecialchars($randomMeal['name']); ?>"
        style="width:100%;">
      <p><strong>Kategori:</strong> <?php echo htmlspecialchars($randomMeal['category']); ?></p>
      <p><strong>Område:</strong> <?php echo htmlspecialchars($randomMeal['area']); ?></p>
      <p><strong>Instruksjoner:</strong></p>
      <p><?php echo nl2br(htmlspecialchars($randomMeal['instructions'])); ?></p>
    </div>
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
</div>

<!-- Footer inkludering -->
<?php include __DIR__ . '/footer.php'; ?>