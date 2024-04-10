<?php 
    // nome do host
    $dbHost = 'localhost';
    // user do banco de dados
    $dbUsername = 'root';
    // senha do user 
    $dbPassword = 'leody2005';
    // nome do banco de dados
    $dbName = 'sys_public';
    

    // estabelecer a conexão com a variavel $conexao e com as variavéis do banco
    $conexao = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    // teste de conectividade
    //  if($conexao->connect_errno){
    //      echo "ERROR";
    //  }else{
    //      echo " CONEXÃO ESTABELECIDA";
    //  }


?>