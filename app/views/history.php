
<?php include __DIR__ . '/header.php'; ?>

<h2>Historikk</h2>

<?php
// Hjelpefunksjon for sikker escaping
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Formatér metadata (JSON) for visning
function format_metadata($meta): string {
    if ($meta === null || $meta === '') {
        return '';
    }
    $decoded = json_decode($meta, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $pretty = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return nl2br(e($pretty));
    }
    return e($meta);
}
?>

<?php if (empty($history)): ?>
    <p>Ingen tidligere samtaler funnet.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Bruker ID</th>
            <th>Tekst</th>
            <th>Svar</th>
            <th>Tidspunkt</th>
        </tr>
        <?php foreach ($history as $entry): ?>
            <tr>
                <td><?php echo e($entry['id'] ?? ''); ?></td>
                <td><?php echo e($entry['user_id'] ?? ''); ?></td>
                <td><?php echo e($entry['query_text'] ?? ''); ?></td>
                <td><?php echo e($entry['response_text'] ?? ''); ?></td>
                <td><?php echo e($entry['created_at'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>