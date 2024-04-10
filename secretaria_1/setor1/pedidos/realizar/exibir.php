<?php
// Estabeleça a conexão com o banco de dados
$conexao = new mysqli("localhost", "root", "leody2005", "sys_public");

// Verifique se houve algum erro na conexão
if ($conexao->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conexao->connect_error);
}

// Consulta SQL para obter todos os pedidos
$sql = "SELECT arquivo_json FROM pedidos_pendentes";

// Prepare a consulta
$stmt = $conexao->prepare($sql);

// Execute a consulta
$stmt->execute();

// Associe o resultado da consulta a uma variável
$stmt->bind_result($caminho_arquivo);

// Iterar sobre os resultados para exibir cada pedido
while ($stmt->fetch()) {
    // Verifique se o caminho do arquivo foi obtido com sucesso
    if (!$caminho_arquivo) {
        die("O caminho do arquivo não foi encontrado.");
    }

    // Ler o conteúdo do arquivo JSON
    $conteudo_json = file_get_contents($caminho_arquivo);

    // Decodificar o JSON
    $pedido = json_decode($conteudo_json, true);

    // Verificar se a decodificação foi bem-sucedida
    if ($pedido === null) {
        die("Erro ao decodificar o JSON.");
    }

    // Exibir as informações do pedido
    echo "Contrato ID: " . $pedido['contrato_id'] . "<br>";
    echo "Produto ID: " . $pedido['produto_id'] . "<br>";
    echo "Quantidade: " . $pedido['quantidade'] . "<br>";
    echo "<hr>"; // Separador entre pedidos
}

// Fechar a declaração e a conexão
$stmt->close();
$conexao->close();
?>
