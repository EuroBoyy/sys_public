<?php 
session_start();

// Verificar se o usuário está logado
if(!isset($_SESSION['id_funcionarios'])){
    // Se não estiver logado, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}

// Verificar se o usuário está no setor correto
if($_SESSION["setor_funcionarios"] !== 2){
    // Se não, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/home.css">
    <title>HOME | SYS PUBLIC</title>
</head>
<body>
<header class="header">
         <nav class="nav container">
            <div class="nav__data">
               <a href="#" class="nav__logo">
                  <i class="ri-home-2-line"></i> SYS PUBLIC
               </a>
               
               <div class="nav__toggle" id="nav-toggle">
                  <i class="ri-menu-line nav__burger"></i>
                  <i class="ri-close-line nav__close"></i>
               </div>
            </div>

            <!--=============== NAV MENU ===============-->
            <div class="nav__menu" id="nav-menu">
               <ul class="nav__list">
                  <li><a href="home.php" class="nav__link">Home</a></li>

                  <!--=============== DROPDOWN 1 ===============-->
                  <li class="dropdown__item">
                     <div class="nav__link">
                        Pedidos <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                     </div>
                     
                     <ul class="dropdown__menu">
                        <!--=============== DROPDOWN SUBMENU ===============-->
                        <li class="dropdown__subitem">
                           <div class="dropdown__link">
                              <i class="ri-dropbox-line"></i> Pedidos <i class="ri-add-line dropdown__add"></i>
                           </div>

                           <ul class="dropdown__submenu">     
                              <li>
                                 <a href="pedidos/pendente/pendentes.php" class="dropdown__sublink">
                                    <i class="ri-bar-chart-box-line"></i> Status Pedidos
                                 </a>
                              </li>
      
                              <li>
                                 <a href="pedidos/historico/historico.php" class="dropdown__sublink">
                                    <i class="ri-history-line"></i> Histórico Pedidos
                                 </a>
                              </li>
                           </ul>
                        </li>
                        
                     </ul>
                  </li>
                  <li><a href="logout.php" class="nav__link">Logout</a></li>
               </div>
         </nav>
      </header>

    <script src="main.js"></script>
</body>
</html>
