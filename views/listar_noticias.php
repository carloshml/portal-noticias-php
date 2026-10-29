<?php
session_start();
require_once('../controllers/noticia-list-all.php');
$erro = isset($_GET['erro']) ? $_GET['erro'] : 0;
$nome_usuario = $_SESSION['nome_usuario'];
$noticias = fetchNoticias();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Últimas Notícias</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        form {
            position: relative !important;
        }

        .form-edit-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 10;
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="home.php">
                <i class="fas fa-newspaper me-2"></i>Notícias Atuais - Todas as Noticias
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-sm-inline"><?= $nome_usuario ?></span>
                <a class="btn btn-outline-danger" href="../services/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </nav>

    <div class=" container py-5 ">
        <div class="d-flex justify-content-end mb-3">
            <a href="home.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
        <div class="row">
            <?php foreach ($noticias as $row):
                $foto_content = $row['foto'];
                if (is_resource($foto_content)) {
                    $foto_content = stream_get_contents($foto_content);
                }
                $base64 = $foto_content ? base64_encode($foto_content) : '';

                $dataCriacao = date('d/m/Y', strtotime($row['data_criacao']));

                $array = [
                    "noticia_id" => $row['noticia_id'],
                    "titulo" => $row['titulo'],
                    "texto" => $row['texto'],
                    "tag1" => $row['tag1'],
                    "tag2" => $row['tag2'],
                    "tag3" => $row['tag3'],
                    "nomeAutor" => $row['nome_autor'],
                    "dataCriacao" => $dataCriacao
                ];
                ?>

                <div class="col-md-4 mb-3">
                    <div class="news-card">
                        <?php if (isset($_SESSION['id_usuario'])): ?>
                            <form method="post" enctype="multipart/form-data"
                                action="views/escrever_noticia.php?noticia_id=<?= $row['noticia_id'] ?>&erro=Edite Sua Noticia&noticia=<?= urlencode(json_encode($array)) ?>"
                                class="form-edit-btn">
                                <button class="btn btn-sm btn-primary rounded-circle edit-btn" type="submit"
                                    title="Editar Notícia">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($base64): ?>
                            <img class="news-image" src="data:image/jpeg;base64,<?= $base64 ?>"
                                alt="<?= htmlspecialchars($row['titulo']) ?>">
                        <?php endif; ?>

                        <div class="p-4">
                            <h2 class="news-title">

                                <a
                                    href="noticia-read.php?noticia_id=<?= $row['noticia_id'] ?>&erro=Edite Sua Noticia&noticia=<?= urlencode(json_encode($array)) ?>">
                                    <?= htmlspecialchars($row['titulo']) ?> </a>

                            </h2>
                            <div class="news-content"><?= nl2br(htmlspecialchars($row['texto'])) ?></div>

                            <div class="mt-3 mb-4">
                                <?php foreach (['tag1', 'tag2', 'tag3'] as $tag):
                                    if (!empty($row[$tag])): ?>
                                        <span class="tag-badge"><?= htmlspecialchars($row[$tag]) ?></span>
                                    <?php endif;
                                endforeach; ?>
                            </div>

                            <div class="author-info text-end">
                                Publicado por <strong><?= htmlspecialchars($row['nome_autor']) ?></strong> |
                                <?= date('d/m/Y', strtotime($row['data_criacao'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>


    <?php
    if (!isset($_SESSION['id_usuario'])) {
        echo '<footer class="bg-light py-3 fixed-bottom border-top text-right">
				<a href="#" id="btn_abrir_login" class="pad-4" data-bs-toggle="modal" data-bs-target="#modal_login">
				login
				</a>
				<a  class="pad-4" href="views/usuario-inscrevase.php">Inscrever-se</a>
			</footer>';
    }
    ?>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>





<!-- Modal  login-->
<div class="modal fade" id="modal_login" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> Entrar </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container align-middle">
                    <div class="form-row">
                        <div class="col-md-4">
                            <div class="jumbotron">
                                <h1>Bem vindo</h1>
                            </div>
                        </div>
                        <div class="col ">
                            <h3>Já possui uma conta?</h3>
                            <form method="post" action="services/valida_acesso.php" id="formLogin">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="campo_usuario" name="login"
                                        placeholder="Usuário" />
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control red" id="campo_senha" name="senha"
                                        placeholder="Senha" />
                                </div>
                                <div>
                                    <?php
                                    if ($erro == 1) {
                                        echo '<font color="FF0000">usuário ou senha inválido(s)</font>';
                                    }
                                    if ($erro == 2) {
                                        echo '<font color="FF0000"> É necessário entrar na página</font>';
                                    }
                                    ?>
                                </div>
                                <button type="buttom" class="btn btn-primary" id="btn_login">Entrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>