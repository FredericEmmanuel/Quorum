<?php
//untuk lebih gampang memanggil htmlspecialchars
function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
}