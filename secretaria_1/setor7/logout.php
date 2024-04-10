<?php 
session_start();
if(isset($_SESSION["id_funcionarios"])){
    session_destroy();
}
header("Location: ../../tela_login/tela_login.php");
exit(); // É uma boa prática adicionar exit() após o redirecionamento para garantir que o script seja encerrado
?>
