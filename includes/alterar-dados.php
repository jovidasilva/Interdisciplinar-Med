<?php
#saidofakegpt
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../cfg/config.php');

function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $email);
}

function alterar_dados_contato($conn, $idusuario, $email, $telefone) {
    if (!validar_email($email)) {
        $_SESSION['msg'] = 'Email inválido.';
        $_SESSION['form_data'] = ['email' => $email, 'telefone' => $telefone];
        header('Location: perfil.php');
        exit();
    }

    $stmt = $conn->prepare("UPDATE usuarios SET email = ?, telefone = ? WHERE idusuario = ?");
    $stmt->bind_param("ssi", $email, $telefone, $idusuario);

    if ($stmt->execute()) {
        $_SESSION['msg'] = 'Dados de contato atualizados com sucesso!';
        return true;
    } else {
        $_SESSION['msg'] = 'Erro ao atualizar dados de contato: ' . $stmt->error;
        return false;
    }
}

function alterar_login($conn, $idusuario, $login_antigo, $login_novo) {
    $stmt = $conn->prepare("SELECT login FROM usuarios WHERE idusuario = ? AND login = ?");
    $stmt->bind_param("is", $idusuario, $login_antigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE usuarios SET login = ? WHERE idusuario = ?");
        $stmt->bind_param("si", $login_novo, $idusuario);

        if ($stmt->execute()) {
            $_SESSION['msg'] = 'Login alterado com sucesso!';
            return true;
        } else {
            $_SESSION['msg'] = 'Erro ao alterar login: ' . $stmt->error;
            return false;
        }
    } else {
        $_SESSION['msg'] = 'Login Atual incorreto.';
        return false;
    }
}

function alterar_senha($conn, $idusuario, $senha_antiga, $senha_nova) {
    $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE idusuario = ?");
    $stmt->bind_param("i", $idusuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        if (password_verify($senha_antiga, $usuario['senha'])) {
            $senha_nova_hash = password_hash($senha_nova, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE idusuario = ?");
            $stmt->bind_param("si", $senha_nova_hash, $idusuario);

            if ($stmt->execute()) {
                $_SESSION['msg'] = 'Senha alterada com sucesso!';
                return true;
            } else {
                $_SESSION['msg'] = 'Erro ao alterar senha: ' . $stmt->error;
                return false;
            }
        } else {
            $_SESSION['msg'] = 'Senha Atual incorreta.';
            return false;
        }
    } else {
        $_SESSION['msg'] = 'Usuário não encontrado.';
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idusuario = intval($_POST['idusuario']);
    $action = $_POST['action'];

    $conn->begin_transaction();
    try {
        if ($action == 'alterar_dados_contato') {
            $email = trim($_POST['email']);
            $telefone = trim($_POST['telefone']);
            alterar_dados_contato($conn, $idusuario, $email, $telefone);
        } elseif ($action == 'alterar_login') {
            $login_antigo = trim($_POST['login_antigo']);
            $login_novo = trim($_POST['login_novo']);
            alterar_login($conn, $idusuario, $login_antigo, $login_novo);
        } elseif ($action == 'alterar_senha') {
            $senha_antiga = trim($_POST['senha_antiga']);
            $senha_nova = trim($_POST['senha_nova']);
            alterar_senha($conn, $idusuario, $senha_antiga, $senha_nova);
        } else {
            $_SESSION['msg'] = 'Ação inválida.';
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['msg'] = 'Erro ao processar a solicitação: ' . $e->getMessage();
    }

    $conn->close();
    header('Location: perfil.php');
    exit();
} else {
    $_SESSION['msg'] = 'Requisição inválida.';
    header('Location: perfil.php');
    exit();
}