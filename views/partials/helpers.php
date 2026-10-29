<?php
/** Funções de apoio compartilhadas pelas páginas públicas. */

/** Escapa texto para saída segura no HTML. */
function esc($valor): string
{
	return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Limita o texto a um resumo de uma linha. */
function resumir($texto, int $limite = 200): string
{
	$limpo = trim((string) preg_replace('/\s+/u', ' ', (string) $texto));
	if (mb_strlen($limpo) <= $limite) {
		return $limpo;
	}
	return rtrim(mb_substr($limpo, 0, $limite)) . '…';
}

/** Primeira letra do nome, usada no avatar. */
function inicial($nome): string
{
	$nome = trim((string) $nome);
	return $nome === '' ? '?' : mb_strtoupper(mb_substr($nome, 0, 1, 'UTF-8'), 'UTF-8');
}

/** "1 notícia" / "5 notícias". */
function pluralNoticias(int $total, string $singular = 'notícia', string $plural = 'notícias'): string
{
	return $total . ' ' . ($total === 1 ? $singular : $plural);
}
