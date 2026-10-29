<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  header("Location: ../index.php?erro=2");
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gerenciar Contatos</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script language="JavaScript" src="funcoes-sistema.js"></script>
  <!-- Custom CSS -->
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --accent-color: #4cc9f0;
      --light-color: #f8f9fa;
      --dark-color: #212529;
    }

    body {
      background-color: #f5f7fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .navbar {
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      background: white !important;
      padding: 1rem 0;
    }

    .page-header {
      background: white;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .btn-add {
      background-color: var(--primary-color);
      border: none;
      padding: 0.5rem 1.5rem;
      font-weight: 500;
    }

    .btn-add:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
    }

    .table-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      padding: 2rem;
    }

    .action-btn {
      width: 35px;
      height: 35px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin: 0 3px;
    }

    .action-btn:hover {
      transform: scale(1.1);
    }

    .modal-content {
      border-radius: 12px;
      border: none;
    }

    .modal-header {
      background: var(--primary-color);
      color: white;
      border-radius: 12px 12px 0 0 !important;
    }

    .form-label {
      font-weight: 500;
    }

    /* Modern table styling */
    #contactsTable {
      width: 100% !important;
    }

    #contactsTable thead th {
      border-bottom: 2px solid var(--primary-color);
      font-weight: 600;
    }

    #contactsTable tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
    }

    .badge-gender {
      padding: 0.35em 0.65em;
      font-size: 0.75em;
      font-weight: 500;
    }

    .badge-male {
      background-color: #d1e7ff;
      color: #0a58ca;
    }

    .badge-female {
      background-color: #ffd1e7;
      color: #ca0a7a;
    }
  </style>


  <script language="JavaScript" src="salvar_usuarios.js"></script>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
      <a class="navbar-brand fw-bold" href="home.php">
        <i class="fas fa-address-book me-2"></i> Notícias Atuais - Usuários
      </a>
      <div class="d-flex align-items-center">
        <a class="btn btn-outline-danger" href="../services/logout.php">
          <i class="fas fa-sign-out-alt"></i> Sair
        </a>
      </div>
    </div>
  </nav>
  <div id="mensagem-upload" class="text-center"></div>

  <!-- Main Content -->
  <div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h2 class="mb-0"><i class="fas fa-users me-2"></i>Gerenciar Usuários</h2>
        </div>
        <div class="col-md-6 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
          <a href="home.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
          </a>
          <button id="btn_abrir_modal_para_inserir" type="button" class="btn btn-add text-white" data-bs-toggle="modal"
            data-bs-target="#createModal">
            <i class="fas fa-plus me-2"></i>Novo Contato
          </button>
        </div>
      </div>
    </div>

    <!-- Contacts Table -->
    <div class="table-container">
      <table id="contactsTable" class="table table-hover" style="width:100%">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Login</th>
            <th>Endereço</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Sexo</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody id="todo_contatos">
          <!-- Contacts will be loaded dynamically -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create Contact Modal -->
  <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createModalLabel"><i class="fas fa-user-plus me-2"></i>Adicionar Contato</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="form_contato" class="row g-3">
            <div class="col-md-6">
              <label for="in_m_c_nome" class="form-label">Nome</label>
              <input type="text" class="form-control" id="in_m_c_nome" name="nome" required>
              <div id="erro_nome" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="in_m_c_login" class="form-label">Login</label>
              <input type="text" class="form-control" id="in_m_c_login" name="login" required>
              <div id="erro_login" class="invalid-feedback-local"></div>
            </div>

            <div class="col-12">
              <label for="in_m_c_endereco" class="form-label">Endereço</label>
              <input type="text" class="form-control" id="in_m_c_endereco" name="endereco" required>
              <div id="erro_endereco" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="in_m_c_telefone" class="form-label">Telefone</label>
              <input type="text" class="form-control" id="in_m_c_telefone" name="telefone" required>
              <div id="erro_telefone" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="in_m_c_email" class="form-label">Email</label>
              <input type="email" class="form-control" id="in_m_c_email" name="email" required>
              <div id="erro_email1" class="invalid-feedback-local"></div>
              <div id="erro_email2" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="in_m_c_senha" class="form-label">Senha</label>
              <input type="password" class="form-control" id="in_m_c_senha" name="senha" required>
            </div>

            <div class="col-md-6">
              <label for="in_m_c_senha2" class="form-label">Repita a Senha</label>
              <input type="password" class="form-control" id="in_m_c_senha2" name="senha2" required>
              <div id="erro_senha" class="invalid-feedback-local"></div>
              <div id="erro_senha2" class="invalid-feedback-local"></div>
            </div>

            <div class="col-12">
              <label class="form-label">Sexo</label>
              <div class="d-flex gap-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sexo" id="sexoM" value="M" required>
                  <label class="form-check-label" for="sexoM">Masculino</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sexo" id="sexoF" value="F">
                  <label class="form-check-label" for="sexoF">Feminino</label>
                </div>
              </div>
              <div id="erro_sexo" class="invalid-feedback-local"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button id="btn_salvar_contato" class="btn btn-primary">Salvar Contato</button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Contact Modal -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewModalLabel"><i class="fas fa-user-circle me-2"></i>Detalhes do Contato</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-bold">Nome:</label>
            <div class="col-sm-9">
              <div class="form-control-plaintext" id="rd_nome_contato"></div>
            </div>
          </div>
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-bold">Login:</label>
            <div class="col-sm-9">
              <div class="form-control-plaintext" id="rd_login_contato"></div>
            </div>
          </div>
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-bold">Endereço:</label>
            <div class="col-sm-9">
              <div class="form-control-plaintext" id="rd_endereco_contato"></div>
            </div>
          </div>
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-bold">Telefone:</label>
            <div class="col-sm-9">
              <div class="form-control-plaintext" id="rd_telefone_contato"></div>
            </div>
          </div>
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-bold">Email:</label>
            <div class="col-sm-9">
              <div class="form-control-plaintext" id="rd_email_contato"></div>
            </div>
          </div>
          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label fw-bold">Sexo:</label>
            <div class="col-sm-9">
              <div class="form-control-plaintext" id="rd_sexo_contato"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">
            <i class="fas fa-exclamation-triangle me-2"></i> Confirmar Exclusão!
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir este contato? Esta ação não pode ser desfeita.</p>
          <p class="fw-bold"> usuario : <span id="deleteContactName"></span></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button id="btn-deletar-contato-concluir" class="btn btn-danger">Confirmar Exclusão</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Contact Modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel"><i class="fas fa-user-edit me-2"></i>Editar Contato</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="form_contato_update" class="row g-3">
            <input type="hidden" id="input_id_contato" name="id">
            <div class="col-md-6">
              <label for="input_nome_contato" class="form-label">Nome</label>
              <input type="text" class="form-control" id="input_nome_contato" name="nome" required>
              <div id="erro_nomeup" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="input_login_contato" class="form-label">Login</label>
              <input type="text" class="form-control" id="input_login_contato" name="login" required>
              <div id="erro_loginup" class="invalid-feedback-local"></div>
            </div>

            <div class="col-12">
              <label for="input_endereco_contato" class="form-label">Endereço</label>
              <input type="text" class="form-control" id="input_endereco_contato" name="endereco" required>
              <div id="erro_enderecoup" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="input_telefone_contato" class="form-label">Telefone</label>
              <input type="text" class="form-control" id="input_telefone_contato" name="telefone" required>
              <div id="erro_telefoneup" class="invalid-feedback-local"></div>
            </div>

            <div class="col-md-6">
              <label for="input_email_contato" class="form-label">Email</label>
              <input type="email" class="form-control" id="input_email_contato" name="email" required>
              <div id="erro_email1up" class="invalid-feedback-local"></div>
              <div id="erro_email2up" class="invalid-feedback-local"></div>
            </div>

            <div class="col-12">
              <label class="form-label">Sexo</label>
              <div class="d-flex gap-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sexo" id="sexo_M" value="M" required>
                  <label class="form-check-label" for="sexo_M">Masculino</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="sexo" id="sexo_F" value="F">
                  <label class="form-check-label" for="sexo_F">Feminino</label>
                </div>
              </div>
              <div id="erro_sexoup" class="invalid-feedback-local"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button id="btn_concluir_update_contato" class="btn btn-primary">Salvar Alterações</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>


</body>

</html>