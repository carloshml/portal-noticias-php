<?php
/**
 * Compatibilidade: o editor agora é views/noticia-form.php.
 */
$noticiaId = isset($_GET['noticia_id']) && is_numeric($_GET['noticia_id']) ? (int) $_GET['noticia_id'] : 0;
$destino = 'noticia-form.php' . ($noticiaId > 0 ? '?noticia_id=' . $noticiaId : '');

header('Location: ' . $destino, true, 301);
exit;
