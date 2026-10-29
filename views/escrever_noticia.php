<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: ../index.php?erro=2");
  exit;
}

require_once('../DAO/noticia.php');
$nome_usuario = $_SESSION['nome_usuario'];
$id_usuario = $_SESSION['id_usuario'];
$erro = $_GET['erro'] ?? 0;
$sucesso = $_GET['sucesso'] ?? 0;
$noticia = $_GET['noticia'] ?? '{}';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <title>Escrever Notícia</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script type="text/javascript">
    const noticia = <?= $noticia ?>;
    const erro = <?= json_encode($erro) ?>;
    const sucesso = <?= $sucesso ?>;   
  </script>
  <script language="JavaScript" src="funcoes-sistema.js"></script>
  <script language="JavaScript" src="escrever_noticia.js"></script>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light py-3">
    <div class="container">
      <a class="navbar-brand fw-bold" href="home.php">
        <i class="fas fa-newspaper me-2"></i>Notícias Atuais - Escreva ou Edite
      </a>
      <div class="d-flex align-items-center">
        <span class="me-3 d-none d-sm-inline"><?= $nome_usuario ?></span>
        <a class="btn btn-outline-danger" href="../services/logout.php">
          <i class="fas fa-sign-out-alt"></i> Sair
        </a>
      </div>
    </div>
  </nav>
  <div id="mensagem-upload" class="text-center"></div>
  <section class="container mt-5">
    <div class="card shadow-sm border-0">
      <div class="card-header text-white d-flex flex-wrap justify-content-between align-items-center gap-2"
        style="background-color: var(--primary-color);">
        <h4 class="mb-0">Escreva Sua Notícia</h4>
        <a href="../index.php" class="btn btn-sm btn-light" aria-label="Voltar para a página inicial">
          <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
      </div>
      <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data"
          action="<?= !empty($_REQUEST['noticia_id']) ? '../services/noticia_update.php' : '../services/noticia_salvar.php' ?>"
          id="formNoticia">

          <input type="hidden" name="noticia_id" id="noticia_id"
            value="<?= htmlspecialchars($_REQUEST['noticia_id'] ?? '') ?>">

          <div class="mb-3 form-floating">
            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título" required>
            <label for="titulo">Título</label>
          </div>

          <div class="mb-3">
            <label for="texto" class="form-label">Texto</label>
            <textarea name="texto" id="texto" class="form-control" rows="8" required></textarea>
          </div>

          <div class="mb-4">
            <label for="imagem_file" class="form-label">Imagem</label>
            <div class="row align-items-center g-3">
              <div class="col-md-8">
                <input type="file" id="imagem_file" name="imagem" class="form-control" accept="image/*">
              </div>
              <div class="col-md-4">
                <div id="imagePreviewContainer" class="border rounded p-2 text-center bg-light" style="display: none;">
                  <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded" style="max-height: 180px;">
                  <small class="d-block mt-2 text-muted">Pré-visualização</small>
                </div>
              </div>
            </div>
          </div>


          <div class="row g-3 mb-4">
            <div class="col-md"><input type="text" name="tag1" id="tag1" class="form-control" placeholder="Tag 1"
                maxlength="10"></div>
            <div class="col-md"><input type="text" name="tag2" id="tag2" class="form-control" placeholder="Tag 2"
                maxlength="10"></div>
            <div class="col-md"><input type="text" name="tag3" id="tag3" class="form-control" placeholder="Tag 3"
                maxlength="10"></div>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-check me-2"></i><?= !empty($_REQUEST['noticia_id']) ? 'Editar' : 'Salvar' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>

</body>

</html>