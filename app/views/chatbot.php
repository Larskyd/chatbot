<?php include __DIR__ . '/header.php'; ?>

<div class="container">
  <h1>Oppskrift Chatbot</h1>

  <!-- Velkomstmelding hvis brukeren er logget inn -->
  <?php $currentUser = $currentUser ?? $_SESSION['user_email'] ?? null; ?>
  <?php if ($currentUser): ?>
    <p class="page-greeting">Velkommen tilbake, <?php echo htmlspecialchars($currentUser); ?>!</p>
  <?php endif; ?>

  <!-- Response fra query -->
  <?php
  // forvent at controller setter: $responseType, $responseData, $responseMessage
  if (!empty($responseMessage) || !empty($responseType)): ?>
    <div style="margin-top:16px;padding:10px;border:1px solid #eee;background:#fafafa;">
      <strong>Bot:</strong>
      <div style="margin-top:8px;">
        <?php if (($responseType ?? '') === 'cards' && !empty($responseData['items'])): ?>
          <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php foreach ($responseData['items'] as $i => $item): 
              $name = htmlspecialchars($item['name'] ?? '');
              $thumb = htmlspecialchars($item['thumbnail'] ?? '');
              ?>
              <div style="width:140px;text-align:center;">
                <?php if ($thumb): ?>
                  <img src="<?php echo $thumb; ?>" alt="<?php echo $name; ?>" style="width:100%;height:90px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                <?php endif; ?>
                <div style="font-size:.9rem;padding-top:6px;"><?php echo ($i+1) . '. ' . $name; ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <p style="margin-top:8px;"><?php echo htmlspecialchars($responseMessage); ?></p>

        <?php elseif (($responseType ?? '') === 'detail' && !empty($responseData)): 
            $name = htmlspecialchars($responseData['name'] ?? '');
            $thumb = htmlspecialchars($responseData['thumbnail'] ?? '');
            $instr = nl2br(htmlspecialchars($responseData['instructions'] ?? ''));
          ?>
          <div style="display:flex;gap:12px;">
            <?php if ($thumb): ?>
              <div style="flex:0 0 180px;"><img src="<?php echo $thumb; ?>" alt="<?php echo $name; ?>" style="width:180px;height:120px;object-fit:cover;border:1px solid #ddd;border-radius:4px;"></div>
            <?php endif; ?>
            <div>
              <h2 style="margin:0 0 6px 0;"><?php echo $name; ?></h2>
              <div><?php echo $instr; ?></div>
            </div>
          </div>

        <?php else: ?>
          <div><?php echo nl2br(htmlspecialchars($responseMessage)); ?></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>


  <?php
  // Sett inputverdi: behold tidligere tekst bare hvis POST uten svar/feilmelding,
  // ellers tøm feltet etter submit.
  $inputValue = '';
  if (!($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($responseMessage))) {
      $inputValue = $_POST['q'] ?? '';
  }
  ?>

  <!-- Chatbot input -->
  <form method="post" style="margin-top:20px;">
    <label for="chat-q">Skriv spørsmål eller kommando (f.eks. "kategori", "tilfeldig", "fra Italy"):</label>
    <div style="display:flex;gap:.5rem;margin-top:.5rem;">
      <input id="chat-q" name="q" type="text" value="<?php echo htmlspecialchars($inputValue); ?>"
        placeholder="Hva vil du vite? (kategori / tilfeldig / fra Norge / historikk)" style="flex:1;padding:.5rem;">
      <button type="submit">Send</button>
    </div>
  </form>

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