<?php 
session_start();

//verificar se o user esta logado
if(!isset($_SESSION['id_funcionarios'])){
    //senao estiver logado, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}

//verificar se ouser esta no setor correto
if($_SESSION["setor_funcionarios"] !==8){
    //senao, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SETOR 8 | HOME</title>
</head>
<body>
    <a href="logout.php">LOG OUT</a><br>
    <a href="../setor8/pedidos/pendente/pendentes.php">VALIDAR PEDIDO</a>
</body>
</html>