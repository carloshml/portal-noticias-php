<?php
require_once __DIR__ . '/../config/banco.php';
require_once __DIR__ . '/tag.php';
class NoticiaService
{


    private $link;

    public function __construct()
    {
        $db = new Banco();
        $this->link = $db->conectar();
    }

    public function __destruct()
    {
        Banco::desconectar();
    }


    public function findById($id_noticia)
    {

        $sql = " SELECT noticia.id as noticia_id , data_criacao,titulo, texto, foto,"
            . " pessoa.nome as nome_autor  "
            . " FROM noticia inner join pessoa  on   noticia.id_autor =  pessoa.id"
            . " where noticia.id = :id_noticia "
            . " ORDER BY noticia.id DESC limit 5;";
        $stmt = $this->link->prepare($sql);
        $stmt->bindColumn('data_criacao', $data_criacao);
        $stmt->bindColumn('titulo', $titulo);
        $stmt->bindColumn('texto', $texto);
        $stmt->bindColumn('noticia_id', $noticia_id);
        $stmt->bindColumn('foto', $foto, PDO::PARAM_LOB);
        $stmt->bindColumn('nome_autor', $nome_autor);
        $stmt->bindValue(":id_noticia", $id_noticia, PDO::PARAM_INT);
        $stmt->execute();
        return $this->anexarTags($stmt->fetchAll(PDO::FETCH_ASSOC));

    }

    public function countByIdUsuario($id_usuario)
    {
        $total = 0;
        $sql = " SELECT COUNT(id)  as total FROM noticia "
            . " where noticia.id_autor = :id_usuario ;";
        $stmt = $this->link->prepare($sql);
        $stmt->bindColumn('total', $total);
        $stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_BOUND);
        return $total;

    }

    public function numeroTotalNoticias()
    {
        $total = null;
        $sql = "SELECT COUNT(id) as total FROM noticia;";
        $stmt = $this->link->prepare($sql);
        $stmt->bindColumn('total', $total);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_BOUND);        
        return $total;
    }

    public function get5Noticia()
    {
        try {
            $sql = "SELECT noticia.id as noticia_id , data_criacao,titulo, texto, foto,"
                . "pessoa.nome as nome_autor  "
                . "FROM noticia inner join pessoa  on   noticia.id_autor =  pessoa.id  ORDER BY noticia.id DESC limit 5;";
            $stmt = $this->link->prepare($sql);
            $stmt->bindColumn('data_criacao', $data_criacao);
            $stmt->bindColumn('titulo', $titulo);
            $stmt->bindColumn('texto', $texto);
            $stmt->bindColumn('noticia_id', $noticia_id);
            $stmt->bindColumn('foto', $foto, PDO::PARAM_LOB);
            $stmt->bindColumn('nome_autor', $nome_autor);
            $stmt->execute();
            return $this->anexarTags($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }

    public function contarMaisRecentesQue($id_noticia)
    {
        $sql = "SELECT COUNT(*) FROM noticia WHERE noticia.id > :id_noticia";
        $stmt = $this->link->prepare($sql);
        $stmt->bindValue(":id_noticia", (int) $id_noticia, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getNoticiasPaginadas($limite, $offset)
    {
        $sql = "SELECT noticia.id as noticia_id , data_criacao,titulo, texto, foto,"
            . "pessoa.nome as nome_autor  "
            . "FROM noticia inner join pessoa  on   noticia.id_autor =  pessoa.id "
            . "ORDER BY noticia.id DESC LIMIT :limite OFFSET :offset;";
        $stmt = $this->link->prepare($sql);
        $stmt->bindValue(":limite", (int) $limite, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $this->anexarTags($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getAllNoticia()
    {
        try {
            $sql = "SELECT noticia.id as noticia_id , data_criacao,titulo, texto, foto,"
                . "pessoa.nome as nome_autor  "
                . "FROM noticia inner join pessoa  on   noticia.id_autor =  pessoa.id  ORDER BY noticia.id DESC;";
            $stmt = $this->link->prepare($sql);
            $stmt->bindColumn('data_criacao', $data_criacao);
            $stmt->bindColumn('titulo', $titulo);
            $stmt->bindColumn('texto', $texto);
            $stmt->bindColumn('noticia_id', $noticia_id);
            $stmt->bindColumn('foto', $foto, PDO::PARAM_LOB);
            $stmt->bindColumn('nome_autor', $nome_autor);
            $stmt->execute();
            return $this->anexarTags($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }

    /** Anexa as tags normalizadas e mantém tag1/tag2/tag3 para compatibilidade. */
    private function anexarTags(array $rows): array
    {
        if (!$rows) {
            return $rows;
        }

        $ids = array_map(static fn($row) => (int) $row['noticia_id'], $rows);
        $tagService = new TagService();
        $mapa = $tagService->tagsPorNoticias($ids);

        foreach ($rows as &$row) {
            $nomes = $mapa[(int) $row['noticia_id']] ?? [];
            $row['tags'] = $nomes;
            $row['tag1'] = $nomes[0] ?? null;
            $row['tag2'] = $nomes[1] ?? null;
            $row['tag3'] = $nomes[2] ?? null;
        }
        unset($row);

        return $rows;
    }
}
?>