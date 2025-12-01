<?php include __DIR__ . '/header.php'; ?>

<h2>Historikk</h2>

<?php if (empty($history)): ?>
    <p>Ingen tidligere samtaler funnet.</p>
<?php else: ?>
    <table class="history-table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Tid</th>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Spørsmål</th>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Type</th>
                <th style="text-align:left;padding:8px;border-bottom:1px solid #ddd;">Kort svar</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $entry): ?>
            <tr class="history-row" data-id="<?php echo htmlspecialchars($entry['id']); ?>" style="cursor:pointer;">
                <td style="padding:8px;border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($entry['created_at'] ?? ''); ?></td>
                <td style="padding:8px;border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($entry['query_text'] ?? ''); ?></td>
                <td style="padding:8px;border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($entry['response_type'] ?? ''); ?></td>
                <td style="padding:8px;border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($entry['response_summary'] ?? ''); ?></td>
            </tr>
            <tr class="history-detail" id="detail-<?php echo htmlspecialchars($entry['id']); ?>" style="display:none;">
                <td colspan="4" style="padding:8px;border-bottom:1px solid #eee;background:#fafafa;">
                    <div class="full-response" data-id="<?php echo htmlspecialchars($entry['id']); ?>">Laster...</div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>

<script>
document.addEventListener('click', function (e) {
  const tr = e.target.closest('.history-row');
  if (!tr) return;
  const id = tr.getAttribute('data-id');
  const detail = document.getElementById('detail-' + id);
  if (!detail) return;
  // toggle visibility
  if (detail.style.display === 'none' || detail.style.display === '') {
    // hent full detalj via AJAX (ny endpoint ikke nødvendig hvis controller kan levere; vi bruker existing getDetail via XHR)
    fetch('?page=history_detail&id=' + encodeURIComponent(id))
      .then(r => r.ok ? r.json() : Promise.reject('error'))
      .then(data => {
        const container = detail.querySelector('.full-response');
        container.innerHTML = '<strong>Fullt svar:</strong><pre style="white-space:pre-wrap;">' + (data.response_text ? escapeHtml(data.response_text) : '') + '</pre>'
                            + (data.metadata ? ('<details><summary>Metadata</summary><pre>' + escapeHtml(JSON.stringify(data.metadata, null, 2)) + '</pre></details>') : '');
      })
      .catch(()=> {
        detail.querySelector('.full-response').textContent = 'Kunne ikke hente detalj.';
      });
    detail.style.display = 'table-row';
  } else {
    detail.style.display = 'none';
  }
}, false);

function escapeHtml(s){ return (s+'').replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; }); }
</script>