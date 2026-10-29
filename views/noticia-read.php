<?php
/**
 * Compatibilidade: a leitura agora acontece em /noticias?noticia=ID.
 */
$noticiaId = isset($_GET['noticia_id']) && is_numeric($_GET['noticia_id']) ? (int) $_GET['noticia_id'] : 0;
$destino = '../noticias' . ($noticiaId > 0 ? '?noticia=' . $noticiaId : '');

header('Location: ' . $destino, true, 302);
exit;
