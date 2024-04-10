<?php 
session_start();
require_once "../../../../conexao/conexao.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['id_funcionarios'])) {
    // Se não estiver logado, acesso negado
    header("Location: ../../../../tela_login/tela_login.php");
    exit();
}

// Verificar se o usuário está no setor correto
$setor_usuario = $_SESSION["setor_funcionarios"];

try {
    $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o formulário foi enviado e se o arquivo foi carregado corretamente
    if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['id_pendente']) && isset($_FILES['validacao_pdf'])) {
        $id_pendente = $_POST['id_pendente'];
        $file_name = $_FILES['validacao_pdf']['name'];
        $file_tmp = $_FILES['validacao_pdf']['tmp_name'];
        $file_dest = '../realizar/uploads/' . $file_name;

        // Tentar mover o arquivo para o diretório de uploads
        if (move_uploaded_file($file_tmp, $file_dest)) {
            // Determinar o campo de PDF e o próximo setor com base no setor do usuário
            $pdf_field = "setor{$setor_usuario}_pdf";
            $next_setor = $setor_usuario + 1;

            // Atualizar o PDF e o status do pedido
            $sql_update_pedido = "UPDATE pedidos_pendentes SET {$pdf_field} = ?, status_do_pedido = CONCAT('Validado_Setor', ?) WHERE id_pendente = ?";
            $stmt_update_pedido = $conexao->prepare($sql_update_pedido);
            $stmt_update_pedido->execute([$file_dest, $setor_usuario, $id_pendente]);

            header("Location: ../pendente/pendentes.php");
            exit();
        } else {
            echo "Erro ao enviar o arquivo.";
            exit();
        }
    }

    // Consultar os pedidos pendentes
    $stmt = $conexao->prepare("SELECT * FROM pedidos_pendentes WHERE status_do_pedido = 'Validado_Setor6'");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erro ao conectar com o banco de dados:" . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Pendentes - Setor <?php echo $setor_usuario; ?></title>
</head>
<body>
    <a href="../../home.php">HOME</a>
    <h1>Pedidos Pendentes - Setor <?php echo $setor_usuario; ?></h1>
    <table border="1">
        <tr>
            <th>ID Pedido</th>
            <th>ID Funcionário</th>
            <th>Nome do Produto</th>
            <th>Quantidade</th>
            <th>Arquivo PDF</th>
            <th>Status</th>
            <th>Ação</th>
        </tr>
        <?php foreach ($pedidos as $pedido) : ?>
        <tr>
            <td><?php echo $pedido['id_pendente']; ?></td>
            <td><?php echo $pedido['id_funcionarios']; ?></td>
            <td><?php echo $pedido['nome_do_produto']; ?></td>
            <td><?php echo $pedido['quantidade_do_produtos']; ?></td>
            <td><?php echo $pedido["setor{$setor_usuario}_pdf"]; ?></td>
            <td><?php echo $pedido['status_do_pedido']; ?></td>
            <td>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_pendente" value="<?php echo $pedido['id_pendente']; ?>">
                    <input type="file" name="validacao_pdf" required accept="application/pdf">
                    <button type="submit">Enviar Validação</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
