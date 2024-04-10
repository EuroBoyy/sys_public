<?php 
session_start();
require_once "../../../../conexao/conexao.php";

// Verificar se o usuário está logado
if (!isset($_SESSION['id_funcionarios'])) {
    // Se não estiver logado, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}

// Verificar se o usuário está no setor correto
if ($_SESSION["setor_funcionarios"] !== 1) {
    // Se não, acesso negado
    header("Location: ../tela_login/tela_login.php");
    exit();
}

// Verificar se o formulário foi enviado e se o arquivo foi carregado corretamente
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['id_pendente']) && isset($_FILES['validacao_pdf'])) {
    $id_pendente = $_POST['id_pendente'];
    $file_name = $_FILES['validacao_pdf']['name'];
    $file_tmp = $_FILES['validacao_pdf']['tmp_name'];
    $file_dest = '../realizar/uploads/' . $file_name;

    // Tentar mover o arquivo para o diretório de uploads
    if (move_uploaded_file($file_tmp, $file_dest)) {
        try {
            $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
            $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Iniciar uma transação
            $conexao->beginTransaction();

            // Atualizar o status do pedido para validado
            $sql_update_pedido = "UPDATE pedidos_pendentes SET status_do_pedido = 'Validado_Setor1', contrato_pdf = ? WHERE id_pendente = ?";
            $stmt_update_pedido = $conexao->prepare($sql_update_pedido);
            $stmt_update_pedido->execute([$file_dest, $id_pendente]);

            // Obter a quantidade do produto do pedido
            $sql_obter_quantidade = "SELECT id_do_produto, quantidade_do_produtos FROM pedidos_pendentes WHERE id_pendente = ?";
            $stmt_obter_quantidade = $conexao->prepare($sql_obter_quantidade);
            $stmt_obter_quantidade->execute([$id_pendente]);
            $pedido = $stmt_obter_quantidade->fetch(PDO::FETCH_ASSOC);

            // Atualizar a quantidade disponível no estoque
            $sql_atualizar_estoque = "UPDATE estoque SET quantidade_do_produto = quantidade_do_produto - ? WHERE id_produto = ?";
            $stmt_atualizar_estoque = $conexao->prepare($sql_atualizar_estoque);
            $stmt_atualizar_estoque->execute([$pedido['quantidade_do_produtos'], $pedido['id_do_produto']]);

            // Confirmar a transação
            $conexao->commit();

            header("Location: ../pendente/pendentes.php");
            exit();
        } catch (PDOException $e) {
            // Desfazer a transação em caso de erro
            $conexao->rollback();
            echo "Erro ao processar o pedido: " . $e->getMessage();
            exit();
        }
    } else {
        echo "Erro ao enviar o arquivo.";
    }
} else {
    echo "Os campos do formulário não foram enviados corretamente.";
}
?>
