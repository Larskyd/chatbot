<?php include __DIR__ . '/header.php'; ?>

<div class="container chat-container">

  <aside class="bot-panel" aria-hidden="false">
    <div class="bot-avatar">
      <!-- Legg bilde i public/images/lamegkoke.png -->
      <img src="<?php echo htmlspecialchars(BASE_URL); ?>/images/lamegkoke.png" alt="LamegKoke" />
    </div>
    <div class="bot-name">
      <h1>Kokebot</h1>
      <p>Dette er Kokebot, din personlige kokk! <br> Spør han for å finne ut hva du kan lage til middag.</p>
    </div>
  </aside>

  <main class="chat-panel" role="main" aria-live="polite">
    <header class="chat-header">
      <h2>Chat med Kokebot</h2>
      <?php $currentUser = $currentUser ?? $_SESSION['user_email'] ?? null; ?>
      <?php if ($currentUser): ?>
        <div class="small">Velkommen tilbake, <?php echo htmlspecialchars($currentUser); ?>!</div>
      <?php endif; ?>
    </header>

    <section id="messages" class="messages">
      <!-- Vis brukerens siste melding som user-boble hvis POST -->
      <?php
      $lastUserMsg = trim($_POST['q'] ?? '');
      if ($lastUserMsg !== ''): ?>
        <div class="message user">
          <div class="bubble"><?php echo nl2br(htmlspecialchars($lastUserMsg)); ?></div>
        </div>
      <?php endif; ?>

      <!-- Bot respons (bruk eksisterende struktur) -->
      <?php if (!empty($responseMessage) || !empty($responseType)): ?>
        <div class="message bot">
          <div class="bubble">
            <p class="msg-note"><?php echo nl2br(htmlspecialchars($responseMessage)); ?></p>
            <?php if (($responseType ?? '') === 'cards' && !empty($responseData['items'])): ?>
              <div class="cards">
                <?php foreach ($responseData['items'] as $i => $item):
                  $name = htmlspecialchars($item['name'] ?? '');
                  $thumb = htmlspecialchars($item['thumbnail'] ?? '');
                ?>
                  <div class="card">
                    <?php if ($thumb): ?>
                      <img src="<?php echo $thumb; ?>" alt="<?php echo $name; ?>" class="card-thumb">
                    <?php endif; ?>
                    <div class="card-title"><?php echo ($i + 1) . '. ' . $name; ?></div>
                  </div>
                <?php endforeach; ?>
              </div>

            <?php elseif (($responseType ?? '') === 'detail' && !empty($responseData)):
              $name = htmlspecialchars($responseData['name'] ?? '');
              $thumb = htmlspecialchars($responseData['thumbnail'] ?? '');
              $instr = nl2br(htmlspecialchars($responseData['instructions'] ?? ''));
            ?>
              <div class="detail">
                <?php if ($thumb): ?>
                  <img src="<?php echo $thumb; ?>" alt="<?php echo $name; ?>" class="detail-thumb">
                <?php endif; ?>
                <div class="detail-body">
                  <h3><?php echo $name; ?></h3>
                  <div class="instructions"><?php echo $instr; ?></div>
                </div>
              </div>

            <?php else: ?>
              <div><?php echo nl2br(htmlspecialchars($responseMessage)); ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <form id="chat-form" method="post" class="chat-form" action="">
      <label for="chat-q" class="sr-only">Skriv melding</label>
      <input id="chat-q" name="q" type="text" value="<?php echo htmlspecialchars($inputValue ?? ''); ?>"
        placeholder="Skriv f.eks. 'kategori', 'tilfeldig' eller 'fra Italy'..." autocomplete="off" />
      <button type="submit" class="btn">Send</button>
    </form>

  </main>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<script>
  (function() {
    const messages = document.getElementById('messages');
    if (messages) {
      messages.scrollTop = messages.scrollHeight;
    }
    const input = document.getElementById('chat-q');
    if (input) input.focus();

    // Fjern tekstfelt etter submit (frontend) hvis du ønsker umiddelbar clearing
    const form = document.getElementById('chat-form');
    if (form) {
      form.addEventListener('submit', function() {
        // la server rydde på PRG; her tømmer vi input for visuelt inntrykk før reload
        setTimeout(() => {
          try {
            input.value = '';
          } catch (e) {}
        }, 10);
      });
    }
  })();
</script>

<style>
  /* Enkel tilgjengelighet helper */
  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
</style>