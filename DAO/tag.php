<?php
require_once __DIR__ . '/../config/banco.php';

class TagService
{
    private $link;

    public function __construct()
    {
        $this->link = Banco::conectar();
    }

    public function __destruct()
    {
        Banco::desconectar();
    }

    /** Retorna as tags usadas mais recentemente. */
    public function getRecentes($limite = 5): array
    {
        $limite = max(1, (int) $limite);

        $sql = "SELECT id, nome, ultima_utilizacao
                FROM tags
                WHERE ultima_utilizacao IS NOT NULL
                ORDER BY ultima_utilizacao DESC, nome ASC
                LIMIT :limite";

        $stmt = $this->link->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Retorna um mapa noticia_id => lista de nomes de tags. */
    public function tagsPorNoticias(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT nt.noticia_id, t.nome
                FROM noticia_tags nt
                INNER JOIN tags t ON t.id = nt.tag_id
                WHERE nt.noticia_id IN ($placeholders)
                ORDER BY t.nome ASC";

        $stmt = $this->link->prepare($sql);
        $stmt->execute($ids);

        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linha) {
            $mapa[(int) $linha['noticia_id']][] = $linha['nome'];
        }

        return $mapa;
    }

    /**
     * Sincroniza as tags de uma notícia.
     * Deve ser chamado dentro da transação de criação/edição da notícia.
     */
    public function sincronizarNoticia(int $noticiaId, array $tags, bool $atualizarLegado = true): void
    {
        $tags = $this->normalizar($tags);

        $stmt = $this->link->prepare('DELETE FROM noticia_tags WHERE noticia_id = ?');
        $stmt->execute([$noticiaId]);

        $inserirTag = $this->link->prepare(
            'INSERT INTO tags (nome, ultima_utilizacao, created_at)
             VALUES (?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), ultima_utilizacao = NOW()'
        );
        $inserirRelacao = $this->link->prepare(
            'INSERT IGNORE INTO noticia_tags (noticia_id, tag_id) VALUES (?, ?)'
        );
        $buscarNome = $this->link->prepare('SELECT nome FROM tags WHERE id = ?');

        $nomesCanonicos = [];
        foreach ($tags as $nome) {
            $inserirTag->execute([$nome]);
            $tagId = (int) $this->link->lastInsertId();
            $inserirRelacao->execute([$noticiaId, $tagId]);

            $buscarNome->execute([$tagId]);
            $nomesCanonicos[] = (string) $buscarNome->fetchColumn();
        }

        if ($atualizarLegado) {
            $legado = array_pad(array_slice($nomesCanonicos, 0, 3), 3, null);
            $stmt = $this->link->prepare('UPDATE noticia SET tag1 = ?, tag2 = ?, tag3 = ? WHERE id = ?');
            $stmt->execute([$legado[0], $legado[1], $legado[2], $noticiaId]);
        }
    }

    /** Remove espaços e duplicidades, preservando a grafia da primeira ocorrência. */
    private function normalizar(array $tags): array
    {
        $saida = [];
        foreach ($tags as $tag) {
            $tag = trim((string) $tag);
            if ($tag === '') {
                continue;
            }

            $chave = mb_strtolower($tag, 'UTF-8');
            if (!isset($saida[$chave])) {
                $saida[$chave] = $tag;
            }
        }

        return array_values($saida);
    }
}
