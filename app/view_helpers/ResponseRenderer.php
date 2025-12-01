<?php
class ResponseRenderer
{
    /**
     * Render en chatbot-respons som HTML.
     */
    public static function render(array $res): string
    {
        $type = $res['type'] ?? 'text';
        $data = $res['data'] ?? null;
        $msg = $res['message'] ?? '';

        ob_start();
        echo '<div class="bot-response">';
        echo '<div class="bot-message-text">' . nl2br(htmlspecialchars($msg)) . '</div>';

        if ($type === 'cards' && !empty($data['items']) && is_array($data['items'])) {
            echo '<div class="cards">';
            foreach ($data['items'] as $i => $it) {
                $name = htmlspecialchars($it['name'] ?? '');
                $thumb = htmlspecialchars($it['thumbnail'] ?? '');
                echo '<div class="card">';
                if ($thumb) echo '<img src="' . $thumb . '" alt="' . $name . '" class="card-thumb">';
                echo '<div class="card-title">' . (($i+1) . '. ' . $name) . '</div>';
                echo '</div>';
            }
            echo '</div>';
        } elseif ($type === 'detail' && !empty($data)) {
            $name = htmlspecialchars($data['name'] ?? '');
            $instr = nl2br(htmlspecialchars($data['instructions'] ?? ''));
            $thumb = htmlspecialchars($data['thumbnail'] ?? '');
            echo '<div class="detail-block">';
            if ($thumb) echo '<img src="' . $thumb . '" class="detail-thumb" alt="' . $name . '">';
            echo '<div class="detail-body"><h3>' . $name . '</h3><div>' . $instr . '</div></div>';
            echo '</div>';
        }

        echo '</div>';
        return ob_get_clean();
    }
}