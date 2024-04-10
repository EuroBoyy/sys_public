<?php
session_start();

// Verifica se o formulário de login foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Conectar ao banco de dados
    require_once "../conexao/conexao.php"; // Certifique-se de alterar o caminho do arquivo de acordo com o seu sistema

    // Receber os dados do formulário
    $email = $_POST["email_funcionarios"];
    $senha = $_POST["senha_funcionarios"];

    // Consultar o banco de dados para verificar as credenciais do usuário
    $query = "SELECT id_funcionarios, setor_funcionarios FROM funcionarios WHERE email_funcionarios = ? AND senha_funcionarios = ?";
    $stmt = mysqli_prepare($conexao, $query);
    mysqli_stmt_bind_param($stmt, "ss", $email, $senha);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_funcionario, $setor);
    mysqli_stmt_fetch($stmt);

    // Verificar se as credenciais estão corretas
    if ($id_funcionario !== null) {
        // Definir a variável de sessão para o ID do funcionário
        $_SESSION["id_funcionarios"] = $id_funcionario;

        // Definir a variável de sessão para o setor do usuário
        $_SESSION["setor_funcionarios"] = $setor;

        // Redirecionar para a página do setor correspondente
        switch ($setor) {
            case 1:
                header("Location: ../secretaria_1/setor1/home.php");
                exit();
            case 2:
                header("Location: ../secretaria_1/setor2/home.php");
                exit();
            case 3:
                header("Location: ../secretaria_1/setor3/home.php");
                exit();
            case 4:
                header("Location: ../secretaria_1/setor4/home.php");
                exit();  
            case 5:
                header("Location: ../secretaria_1/setor5/home.php");
                exit();
            case 6:
                header("Location: ../secretaria_1/setor6/home.php");
                exit();
            case 7:
                header("Location: ../secretaria_1/setor7/home.php");
                exit();
            case 8:
                header("Location: ../secretaria_1/setor8/home.php");
                exit();
            case 9:
                header("Location: ../secretaria_1/setor9/home.php");
                exit(); 
            default:
                // Se nenhum setor correspondente for encontrado, redirecionar para uma página de erro
                header("Location: tela_login.php?error=invalid_setor");
                exit();
        }
    } else {
        // Se as credenciais estiverem incorretas, armazenar a mensagem de erro em uma variável de sessão
        $_SESSION['login_error'] = "Credenciais inválidas. Tente novamente.";
        
        // Redirecionar de volta para a página de login
        header("Location: tela_login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TELA | LOGIN</title>
    <link rel="stylesheet" href="../css/tela_login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-left">
            <img src="../image/logosite.png" alt="">
        </div>
        <div class="form-container">
            <form action="tela_login.php" method="POST" class="form">
                <p class="tittle">LOG IN | ENTRAR</p>
                <input type="email" name="email_funcionarios" class="email" placeholder="exemplo@gmail.com" required>
                <input type="password" name="senha_funcionarios" class="password" placeholder="********" required>
                <input type="submit" name="submit" class="submit" value="Entrar">
            </form>
        </div>
    </div>

    <?php 
    // Verifica se há uma mensagem de erro armazenada na variável de sessão
    if(isset($_SESSION['login_error'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        // Exibe o alerta usando SweetAlert2
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "<?php echo $_SESSION['login_error']; ?>"
        });
    </script>
    <?php 
    // Limpa a mensagem de erro da variável de sessão após exibi-la
    unset($_SESSION['login_error']);
    endif; ?>
</body>
</html>
