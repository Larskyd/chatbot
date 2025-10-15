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

    <!--- Inoutfelt for område --->
        <form method="post" style="margin-top:20px;">
            <label for="area" style="display:block; margin-bottom:10px;">Skriv inn et område (land):</label>
            <input type="text" id="area" name="area" placeholder="F.eks. Canadian"
                style="padding:10px; width:300px; border:1px solid #ccc; border-radius:5px; margin-right:10px;">
            <button type="submit" name ="showRecipesByArea" value="1"
                style="padding:10px 20px; background-color:#2a7ae2; color:white; border:none; border-radius:5px; cursor:pointer;">
                Se oppskrifter etter område
            </button>
        </form>

    <!--- Vis oppskrifter fra område --->
    <?php if (!empty($recipesByArea)): ?>
        <h2>Oppskrifter fra området "<?php echo htmlspecialchars($area); ?>"</h2>
        <div style="display:flex; flex-wrap:wrap; gap:20px; margin-top:20px;">
            <?php foreach ($recipesByArea as $recipe): ?>
                <div style="border:1px solid #ccc; border-radius:5px; padding:10px; width:200px; text-align:center; background:#fff;">
                    <img src="<?php echo htmlspecialchars($recipe['thumbnail']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" style="width:100%; border-radius:5px;">
                    <h3 style="margin:10px 0;"><?php echo htmlspecialchars($recipe['name']); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (isset($_POST['showRecipesByArea'])): ?>
        <p>Ingen oppskrifter funnet for området "<?php echo htmlspecialchars($area); ?>".</p>
    <?php endif; ?>

    <!--- Inklusjon av footer --->

    <?php include 'footer.php'; ?>