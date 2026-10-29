-- Tabela: pessoa
CREATE TABLE pessoa (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) COLLATE utf8_unicode_ci NOT NULL,
  login VARCHAR(80) COLLATE utf8_unicode_ci NOT NULL,
  endereco VARCHAR(80) COLLATE utf8_unicode_ci NOT NULL,
  telefone VARCHAR(35) COLLATE utf8_unicode_ci NOT NULL,
  email VARCHAR(40) COLLATE utf8_unicode_ci NOT NULL,
  sexo VARCHAR(1) COLLATE utf8_unicode_ci NOT NULL,
  senha VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_pessoa_login (login),
  UNIQUE KEY uq_pessoa_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Tabela: noticia
CREATE TABLE noticia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(50) NOT NULL,
  texto TEXT NOT NULL,
  tag1 VARCHAR(10),
  tag2 VARCHAR(10),
  tag3 VARCHAR(10),
  id_autor INT UNSIGNED,
  foto LONGBLOB,
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_noticia_autor FOREIGN KEY (id_autor) REFERENCES pessoa(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Tabela: tags
CREATE TABLE tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  ultima_utilizacao DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tags_nome (nome),
  KEY idx_tags_ultima_utilizacao (ultima_utilizacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Tabela: noticia_tags (N:N entre notícia e tag)
CREATE TABLE noticia_tags (
  noticia_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (noticia_id, tag_id),
  KEY idx_noticia_tags_tag (tag_id),
  CONSTRAINT fk_noticia_tags_noticia FOREIGN KEY (noticia_id) REFERENCES noticia (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_noticia_tags_tag FOREIGN KEY (tag_id) REFERENCES tags (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
