<?php

require_once __DIR__ . '/../config/banco.php';
$id = null;

if (!empty($_POST)) {
    $nomeError = null;
    $enderecoErro = null;
    $telefoneErro = null;
    $emailErro = null;
    $emailError = null;
    $sexoErro = null;
    $loginError = null;

    $id = (int) $_POST['id'];
    $nome = trim($_POST['nome']);
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $email = trim($_POST['email']);
    $sexo = $_POST['sexo'];
    $login = trim($_POST['login']);

    //Validação
    $valido = true;
    if (empty($nome)) {
        $nomeError = 'Por favor digite o nome!';
        $valido = false;
    }

    if (empty($email)) {
        $emailErro = 'Por favor digite o email!';
        $valido = false;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErro = 'Por favor digite um email válido!';
        $valido = false;
    }

    if (empty($endereco)) {
        $endereco = 'Por favor digite o endereço!';
        $valido = false;
    }

    if (empty($telefone)) {
        $telefone = 'Por favor digite o telefone!';
        $valido = false;
    }

    if (empty($endereco)) {
        $endereco = 'Por favor preenche o campo!';
        $valido = false;
    }

    if (empty($login)) {
        $loginError = 'Por favor preenche o login!';
        $valido = false;
    }

    // update data
    if ($valido) {
        try {
            $pdo = Banco::conectar();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Login e e-mail são únicos: ignorar o próprio registro.
            $consulta = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE login = ? AND id <> ?");
            $consulta->execute([$login, $id]);
            if ((int) $consulta->fetchColumn() > 0) {
                $loginError = 'Este login já está em uso.';
                $valido = false;
            }

            $consulta = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE email = ? AND id <> ?");
            $consulta->execute([$email, $id]);
            if ((int) $consulta->fetchColumn() > 0) {
                $emailError = 'Este e-mail já está cadastrado.';
                $valido = false;
            }

            if ($valido) {
                $sql = "UPDATE pessoa 
                       SET nome = ?, endereco = ?, telefone = ?, email = ?, sexo = ?, login = ? 
                     WHERE id = ?";
                $q = $pdo->prepare($sql);
                $q->execute([$nome, $endereco, $telefone, $email, $sexo, $login, $id]);
                echo json_encode([
                    'valido' => true,
                    'mensagem' => 'Atualizado com sucesso',
                    'id' => $id
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'valido' => false,
                'erro' => true,
                'mensagem' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        } finally {
            Banco::desconectar();
        }
    }

    if (!$valido) {
        echo json_encode([
            ['valido' => false],
            ['erroNome' => !empty($nomeError), 'motivo' => $nomeError ?? ''],
            ['erroEndereco' => !empty($enderecoErro), 'motivo' => $enderecoErro ?? ''],
            ['erroTelefone' => !empty($telefoneErro), 'motivo' => $telefoneErro ?? ''],
            ['erroEmailTamanho' => !empty($emailErro), 'motivo' => $emailErro ?? ''],
            ['erroEmailValido' => !empty($emailError), 'motivo' => $emailError ?? ''],
            ['erroSexo' => !empty($sexoErro), 'motivo' => $sexoErro ?? ''],
            ['erroLogin' => !empty($loginError), 'motivo' => $loginError ?? '']
        ], JSON_UNESCAPED_UNICODE);
    }

}

?>