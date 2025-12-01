<?php include __DIR__ . '/header.php'; ?>

<div class="container chat-container">

  <aside class="bot-panel" aria-hidden="false">
    <div class="bot-avatar">
      <img src="<?php echo htmlspecialchars(BASE_URL); ?>/images/lamegkoke.png" alt="Kokebot" />
    </div>
    <div class="bot-name">
      <h1>Kokebot</h1>
      <p>Din samtalehistorikk med Kokebot.</p>
    </div>
  </aside>

  <main class="chat-panel" role="main" aria-live="polite">
    <header class="chat-header">
      <h2>Historikk</h2>
      <?php $currentUser = $currentUser ?? $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? null; ?>
      <?php if ($currentUser): ?>
        <div class="small">Viser siste forespørsler for <?php echo htmlspecialchars($currentUser); ?></div>
      <?php endif; ?>
    </header>

    <section id="history-messages" class="messages">
      <?php if (empty($history)): ?>
        <div class="message bot">
          <div class="bubble">Ingen tidligere samtaler funnet.</div>
        </div>
      <?php else: ?>
        <?php foreach ($history as $entry): 
            $id = (int)($entry['id'] ?? 0);
            $time = htmlspecialchars($entry['created_at'] ?? '');
            $query = htmlspecialchars($entry['query_text'] ?? '');
            $type = htmlspecialchars($entry['response_type'] ?? '');
            $summary = htmlspecialchars($entry['response_summary'] ?? '');
        ?>
          <div class="history-row" data-id="<?php echo $id; ?>">
            <div class="message bot history-item" role="button" tabindex="0" aria-expanded="false" aria-controls="detail-<?php echo $id; ?>">
              <div class="bubble">
                <div class="history-head">
                  <strong class="history-query"><?php echo $query; ?></strong>
                  <small class="history-time"><?php echo $time; ?></small>
                </div>
                <div class="history-meta">
                  <span class="history-type"><?php echo $type ?: 'text'; ?></span>
                  <?php if ($summary !== ''): ?>
                    <span class="history-summary"> — <?php echo $summary; ?></span>
                  <?php endif; ?>
                </div>
                <div id="detail-<?php echo $id; ?>" class="history-detail" style="display:none;margin-top:8px;">
                  <div class="full-response">Laster...</div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <div class="chat-footer" style="padding-top:10px;border-top:1px solid #f0f0f0;margin-top:8px;">
      <a href="<?php echo htmlspecialchars(BASE_URL . '/?page=chatbot'); ?>" class="btn">Tilbake til chat</a>
    </div>
  </main>
</div>

<?php include __DIR__ . '/footer.php'; ?>
