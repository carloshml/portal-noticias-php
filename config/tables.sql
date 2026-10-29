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
