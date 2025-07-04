<?php
require_once __DIR__ . '/../config/banco.php';

if (!empty($_POST)) {
    //Acompanha os erros de validação
    $nomeError = null;
    $enderecoErro = null;
    $telefoneErro = null;
    $emailErro = null;
    $sexoErro = null;
    $loginErro = null;
    $senhaErro = null;
    $senhaErro2 = null;

    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $sexo = $_POST['sexo'] ?? null;
    $login = $_POST['login'];
    $senha = md5($_POST['senha']);

    //Validaçao dos campos:
    $valido = true;
    if (empty($_POST['senha'])) {
        $senhaErro = 'Por favor digite  a senha!';
        $valido = false;
    }

    if ($_POST['senha'] != $_POST['senha2']) {
        $senhaErro2 = 'Senhas não coincidem!';
        $valido = false;
    }

    if (empty($nome)) {
        $nomeError = 'Por favor digite o seu nome!';
        $valido = false;
    }

    if (empty($endereco)) {
        $enderecoErro = 'Por favor digite o seu endereço!';
        $valido = false;
    }

    if (empty($login)) {
        $loginErro = 'Por favor digite o seu Login!';
        $valido = false;
    }

    if (empty($telefone)) {
        $telefoneErro = 'Por favor digite o número do telefone!';
        $valido = false;
    }

    $emailError = '';

    if (empty($email)) {
        $emailErro = 'Por favor digite o endereço de email';
        $valido = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = 'Por favor digite um endereço de email válido!';
        $valido = false;

    }

    if (empty($sexo)) {
        $sexoErro = 'Por favor digite esse campo!';
        $valido = false;
    }

    //Inserindo no Banco:
    if ($valido) {
        $pdo = Banco::conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO pessoa (nome, endereco, telefone, email, sexo,senha, login) VALUES(?,?,?,?,?,?,?)";
        $q = $pdo->prepare($sql);
        $q->execute(array($nome, $endereco, $telefone, $email, $sexo, $senha, $login));
        Banco::desconectar();
        // header("Location: index.php");    
        $valido = $valido ? 'true' : 'false';
        echo '[{"valido":' . $valido . '}]';

    } else {
        $valido = $valido ? 'true' : 'false';
        $temErroNome = $nomeError ? 'true' : 'false';
        $temErroEndereco = $enderecoErro ? 'true' : 'false';
        $temErroTelefone = $telefoneErro ? 'true' : 'false';
        $temErroEmailTamanho = $emailErro ? 'true' : 'false';
        $temErroEmailValidade = $emailError ? 'true' : 'false';
        $temErroSexo = $sexoErro ? 'true' : 'false';
        $temErroSenha = $senhaErro ? 'true' : 'false';
        $temErroSenha2 = $senhaErro2 ? 'true' : 'false';
        $temErroLogin = $loginErro ? 'true' : 'false';

        echo '['
            . '{"valido":' . $valido . '},'
            . '{"temErro":' . $temErroNome . ', "motivo":"' . $nomeError . '"},'
            . '{"temErro":' . $temErroEndereco . ',"motivo":"' . $enderecoErro . '"},'
            . '{"temErro":' . $temErroTelefone . ',"motivo":"' . $telefoneErro . '"},'
            . '{"temErro":' . $temErroEmailTamanho . ',"motivo":"' . $emailErro . '"},'
            . '{"temErro":' . $temErroEmailValidade . ',"motivo":"' . $emailError . '"},'
            . '{"temErro":' . $temErroSexo . ',"motivo":"' . $sexoErro . '"},'
            . '{"temErro":' . $temErroSenha . ',"motivo":"' . $senhaErro . '"},'
            . '{"temErro":' . $temErroSenha2 . ',"motivo":"' . $senhaErro2 . '"},'
            . '{"temErro":' . $temErroLogin . ',"motivo":"' . $loginErro . '"}'
            . ']';
    }
} else {
    echo 'método POST vazio';
}
?>