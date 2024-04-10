<?php
session_start();

// Verificar se os dados do produto foram recebidos via POST
if(isset($_POST['id_produto']) && isset($_POST['nome_produto'])){
    // Adicionar o produto ao carrinho (usando a sessão PHP)
    $_SESSION['carrinho'][] = array(
        'id_produto' => $_POST['id_produto'],
        'nome_produto' => $_POST['nome_produto']
    );
}
?>
