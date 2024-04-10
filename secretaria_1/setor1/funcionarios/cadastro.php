<?php 
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_funcionarios'])) {
    // Se não estiver logado, acesso negado
    header("Location: ../../../tela_login/tela_login.php");
    exit();
}

// Verificar se o usuário está no setor correto
if ($_SESSION["setor_funcionarios"] !== 1) {
    // Se não, acesso negado
    header("Location: ../../../tela_login/tela_login.php");
    exit();
}

// Função para gerar ID aleatórios que não se repetem
function gerar_id_aleatorio() {
    $id_gerado = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    return $id_gerado;
}

// Obter os dados do cadastro
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Gerar ID aleatório
    $id_funcionarios = gerar_id_aleatorio();

    // Obter e armazenar dados do cadastro
    require_once("../../../conexao/conexao.php");
    $nome_cadastrado = $_POST['nome_funcionarios'];
    $email_cadastrado = $_POST['email_funcionarios'];
    $senha_cadastrada = $_POST['senha_funcionarios'];
    $setor_cadastrado = $_POST['setor_funcionarios'];
    $secretaria_cadastrada = $_POST['secretaria_funcionarios'];
    $assinatura_base64 = $_POST['assinatura']; // Obtém a assinatura base64

    // Converte a assinatura para dados binários
    $assinatura_binario = base64_decode(str_replace('data:image/png;base64,', '', $assinatura_base64));

    // Verifica se a pasta "assinatura" existe e cria se não existir
    $pasta_assinatura = "assinatura";
    if (!file_exists($pasta_assinatura)) {
        mkdir($pasta_assinatura, 0777, true);
    }

    // Define o caminho da assinatura
    $caminho_assinatura = $pasta_assinatura . '/assinatura_' . uniqid() . '.png';

    // Salva a assinatura no servidor
    file_put_contents($caminho_assinatura, $assinatura_binario);

    // Inserir os dados do funcionário no banco de dados
    $sql = "INSERT INTO funcionarios (id_funcionarios, nome_funcionarios, email_funcionarios, senha_funcionarios, setor_funcionarios, secretaria_funcionarios, assinatura) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$id_funcionarios, $nome_cadastrado, $email_cadastrado, $senha_cadastrada, $setor_cadastrado, $secretaria_cadastrada, $caminho_assinatura]);

    // Define a variável de sessão para exibir a mensagem de sucesso
    $_SESSION['cadastro_feito'] = true;
    $_SESSION['new_name_func'] = $nome_cadastrado;
    header("Location: cadastro.php"); // Redireciona para a mesma página para evitar reenvio de formulário
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CADASTRO | FUNCIONÁRIOS</title>
    <link rel="stylesheet" href="../../../css/cadastro.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/fontawesome/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        #signature-pad {
            border: 1px solid #000;
            width: 300px;
            height: 200px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-left">
        <img src="../image/logosite.png" alt="">
    </div>
    <div class="form-container">
        <form action="cadastro.php" method="POST" class="form">
            <p class="tittle">Cadastro | Registro</p>
            <input type="text" name="nome_funcionarios" class="email" placeholder="Walter Silva" required>
            <input type="email" name="email_funcionarios" class="email" placeholder="exemplo@gmail.com" required>
            <input type="password" name="senha_funcionarios" class="password" placeholder="********" required>
            <select name="setor_funcionarios" required>
                <option value="" disabled selected>Selecione o setor</option>
                <option value="1">Setor 1</option>
                <option value="2">Setor 2</option>
                <option value="3">Setor 3</option>
                <option value="4">Setor 4</option>
                <option value="5">Setor 5</option>
                <option value="6">Setor 6</option>
                <option value="7">Setor 7</option>
                <option value="8">Setor 8</option>
                <option value="9">Setor 9</option>
            </select>
            <select name="secretaria_funcionarios" required>
                <option value="" disabled selected>Selecione a Secretaria</option>
                <option value="1">Secretaria 1</option>
                <option value="2">Secretaria 2</option>
                <option value="3">Secretaria 3</option>
                <option value="4">Secretaria 4</option>
                <option value="5">Secretaria 5</option>
                <option value="6">Secretaria 6</option>
                <option value="7">Secretaria 7</option>
                <option value="8">Secretaria 8</option>
            </select>
            <!-- Canvas para a assinatura -->
            <canvas id="signature-pad"></canvas>
            <input type="hidden" name="assinatura" id="assinatura" value="">
            <button type="button" id="clear-button">Limpar</button>
            <button type="button" id="save-button">Salvar</button>
            <input type="submit" name="submit" class="submit" value="Cadastrar">
            <a href="../../setor1/home.php"><p class="sign-up-label">
                Deseja Voltar?<span class="sign-up-link">Voltar</span>
            </p></a>
        </form>
        <div id="notification" style="display:none;"></div>
    </div>
</div>

<!-- Inclua o script JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<script>
    // Selecione o canvas e configure o contexto
    var canvas = document.getElementById('signature-pad');
    var ctx = canvas.getContext('2d');

    // Configure o tamanho do canvas para corresponder ao tamanho do contêiner
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    // Inicialize a biblioteca SignaturePad
    var signaturePad = new SignaturePad(canvas, {
        // Reduza o atraso (delay)
        throttle: 10,
        // Ajuste a sensibilidade da caneta
        minDistance: 0
    });

    // Limpar a assinatura
    document.getElementById('clear-button').addEventListener('click', function() {
        signaturePad.clear();
    });

    // Salvar a assinatura
    document.getElementById('save-button').addEventListener('click', function() {
        // Verifica se há uma assinatura
        if (signaturePad.isEmpty()) {
            alert('Por favor, forneça uma assinatura primeiro.');
        } else {
            // Converte a assinatura para uma imagem base64
            var dataURL = signaturePad.toDataURL();
            // Define o valor do campo de assinatura oculto
            document.getElementById('assinatura').value = dataURL;
            // Agora, envie o formulário
            document.querySelector('form').submit();
        }
    });
</script>

<?php
// Verifica se o cadastro foi feito e exibe a mensagem apenas uma vez
if (isset($_SESSION['cadastro_feito'])) {
    $new_name_func = $_SESSION['new_name_func']; // Recuperar o nome do usuário recém-cadastrado da variável de sessão
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: '$new_name_func, Cadastrado com sucesso.',
                showConfirmButton: false,
                timer: 3000,
                customClass: {
                    title: 'custom-title-class',
                    popup: 'custom-popup-class'
                }
            });
        });
    </script>";
    // Após exibir a mensagem, remova as variáveis de sessão
    unset($_SESSION['cadastro_feito']);
    unset($_SESSION['new_name_func']);
}
?>
</body>
</html>
