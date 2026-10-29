<?php
require_once('../DAO/noticia.php');
function fetchNoticias()
{
    try {
        $noticiaService = new NoticiaService();
        return $noticiaService->getAllNoticia();
    } catch (PDOException $e) {
        return [];
    }
}

function contarNoticias()
{
    try {
        $noticiaService = new NoticiaService();
        return (int) $noticiaService->numeroTotalNoticias();
    } catch (PDOException $e) {
        return 0;
    }
}

function fetchNoticiasPaginadas($limite, $offset)
{
    try {
        $noticiaService = new NoticiaService();
        return $noticiaService->getNoticiasPaginadas((int) $limite, (int) $offset);
    } catch (PDOException $e) {
        return [];
    }
}
?>