-- Normaliza as tags das notícias.
-- Mantém as colunas legadas tag1/tag2/tag3 na tabela noticia por compatibilidade,
-- mas a fonte de verdade passa a ser tags + noticia_tags.
-- A rotina é idempotente e pode ser executada mais de uma vez.

CREATE TABLE IF NOT EXISTS tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  ultima_utilizacao DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tags_nome (nome),
  KEY idx_tags_ultima_utilizacao (ultima_utilizacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS noticia_tags (
  noticia_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (noticia_id, tag_id),
  KEY idx_noticia_tags_tag (tag_id),
  CONSTRAINT fk_noticia_tags_noticia FOREIGN KEY (noticia_id) REFERENCES noticia (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_noticia_tags_tag FOREIGN KEY (tag_id) REFERENCES tags (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TEMPORARY TABLE tmp_noticia_tags (
  noticia_id INT NOT NULL,
  nome VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL,
  data_criacao DATETIME NOT NULL
) ENGINE=InnoDB;

INSERT INTO tmp_noticia_tags (noticia_id, nome, data_criacao)
SELECT id, TRIM(tag1), data_criacao FROM noticia WHERE tag1 IS NOT NULL AND TRIM(tag1) <> ''
UNION ALL
SELECT id, TRIM(tag2), data_criacao FROM noticia WHERE tag2 IS NOT NULL AND TRIM(tag2) <> ''
UNION ALL
SELECT id, TRIM(tag3), data_criacao FROM noticia WHERE tag3 IS NOT NULL AND TRIM(tag3) <> '';

INSERT IGNORE INTO tags (nome, ultima_utilizacao)
SELECT nome, LEAST(MAX(data_criacao), NOW())
FROM tmp_noticia_tags
GROUP BY nome;

UPDATE tags t
INNER JOIN (
  SELECT nome, LEAST(MAX(data_criacao), NOW()) AS ultima
  FROM tmp_noticia_tags
  GROUP BY nome
) x ON x.nome = t.nome
SET t.ultima_utilizacao = GREATEST(COALESCE(t.ultima_utilizacao, x.ultima), x.ultima);

UPDATE tags
SET ultima_utilizacao = LEAST(ultima_utilizacao, NOW())
WHERE ultima_utilizacao > NOW();

INSERT IGNORE INTO noticia_tags (noticia_id, tag_id)
SELECT tmp.noticia_id, t.id
FROM tmp_noticia_tags tmp
INNER JOIN tags t ON t.nome = tmp.nome;

DROP TEMPORARY TABLE tmp_noticia_tags;
