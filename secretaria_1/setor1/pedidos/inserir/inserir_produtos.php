<?php 
session_start();
require_once "../../../../conexao/conexao.php";

//verificar se o user esta logado
if(!isset($_SESSION['id_funcionarios'])){
    //senao estiver logado, acesso negado
    header("Location: ../../../tela_login/tela_login.php");
    exit();
}

//verificar se ouser esta no setor correto
if($_SESSION["setor_funcionarios"] !==1){
    //senao, acesso negado
    header("Location: ../tela_login/tela_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSERIR | PRODUTOS</title>
    <link rel="stylesheet" href="../../../../css/inserir.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
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
                  <li><a href="../../home.php" class="nav__link">Home</a></li>

                  <li><a href="../../funcionarios/cadastro.php" class="nav__link">Funcionários</a></li>

                  <!--=============== DROPDOWN 1 ===============-->
                  <li class="dropdown__item">
                     <div class="nav__link">
                        Pedidos <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                     </div>
                     
                     <ul class="dropdown__menu">
                        <!--=============== DROPDOWN SUBMENU ===============-->
                        <li class="dropdown__subitem">
                           <div class="dropdown__link">
                              <i class="ri-newspaper-line"></i> Licitação <i class="ri-add-line dropdown__add"></i>
                           </div>
         
                           <ul class="dropdown__submenu">
                              <li>
                                 <a href="#" class="dropdown__sublink">
                                    <i class="ri-cash-line"></i> Serviços
                                 </a>
                              </li>
         
                              <li>
                                 <a href="inserir_produtos.php" class="dropdown__sublink">
                                    <i class="ri-refund-2-line"></i> Produtos
                                 </a>
                              </li>
                           </ul>
                        </li>
                        <li class="dropdown__subitem">
                           <div class="dropdown__link">
                              <i class="ri-dropbox-line"></i> Pedidos <i class="ri-add-line dropdown__add"></i>
                           </div>

                           <ul class="dropdown__submenu">
                              <li>
                                 <a href="../realizar/realizar_pedido.php" class="dropdown__sublink">
                                    <i class="ri-add-box-line"></i> Realizar Pedidos
                                 </a>
                              </li>
      
                              <li>
                                 <a href="../pendente/pendentes.php" class="dropdown__sublink">
                                    <i class="ri-bar-chart-box-line"></i> Status Pedidos
                                 </a>
                              </li>
      
                              <li>
                                 <a href="../historico/historico.php" class="dropdown__sublink">
                                    <i class="ri-history-line"></i> Histórico Pedidos
                                 </a>
                              </li>
                           </ul>
                        </li>
                        
                     </ul>
                  </li>
                  <li><a href="../../logout.php" class="nav__link">Logout</a></li>
               </div>
         </nav>
</header>
    <div class="form-prod">
        <form class="file-upload-form" action="processar_produtos.php" method="POST" enctype="multipart/form-data">
            <h2>- Inserir Informações Licitação -</h2>
            <div class="select_geral">
                <div class="modalidade_lic">
                    <p>*Modalidade</p>
                    <div class="modalidade">
                        <select name="modalidade" class="select_modalidade">
                            <option value="">Selecione...</option>
                            <option value="Pregao Eletronico">Pregão Eletrônico</option>
                            <option value="Pregao Presencial">Pregão Presencial</option>
                            <option value="Dispensa por Valor">Dispensa Por Valor</option>
                            <option value="Dispensa por outros Motivos">Dispensa Por Outros Motivos</option>
                            <option value="Concorrencia Eletronica">Concorrência Eletrônica</option>
                            <option value="Inexigibilidade">Inexigibilidade</option>
                            <option value="Credenciamento">Credenciamento</option>
                        </select>
                    </div>
                </div>
                <div class="objeto_tipo">
                    <p>*Tipo Objeto</p>
                    <div class="objetos_tipos">
                        <select name="tipo_objeto" class="select_objeto">
                            <option value="">Selecione...</option>
                            <option value="Compra Comum">Compra - COMUM</option>
                            <option value="Servico Comum">Serviço - COMUM</option>
                            <option value="Servico Eng Comum">Serviço Eng - COMUM</option>
                        </select>
                    </div>
                </div>
            </div>
            <p>*Campos Empresa</p>
            <div class="empresa">
                <input type="number" name="cnpj-empresa" class="cnpj" placeholder="CNPJ X°XXXXXXX" oninput="javascript: if (this.value.length > 14) this.value = this.value.slice(0, 14);" required>
                <input type="text" name="nome-empresa" class="cnpj" placeholder="Nome Exemplo..." required>
            </div>
            <div class="empresa">
                <div class="contrato_licitacao">
                    <p>*Número Contrato</p>
                    <input type="number" name="num-contrato" class="contrato" placeholder="N°0001/24" required>
                </div>
                <div class="numero_lici">
                    <p>*Número Licitação</p>
                    <input type="number" name="num-lic" class="contrato" placeholder="N° 0001/24" required>
                </div>
            </div>
            <div class="form-floating">
                <p>*Objeto Licitação</p>
                <textarea id="myTextarea" name="objeto-empresa" class="form-control" placeholder="Empresa Especializada em Distribuição de Combustível..." id="floatingTextarea" rows="6" maxlength="570"></textarea>
                <script>
                    document.getElementById("myTextarea").addEventListener("input", function() {
                        var lines = this.value.split("\n").length;
                        if (lines > 6) {
                            this.value = this.value.substring(0, this.value.lastIndexOf("\n"));
                        }
                        });
                </script>
            </div>
            <p class="p-arquivo">*Inserir Produtos:</p>
            <label for="excelFile" class="file-upload-label"> Escolha o Arquivo 
                <input type="file" id="excelFile" name="excelFile" class="file-input">
                <div class="file-upload-container">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125" stroke="#fffffff" stroke-width="2"></path>
                        <path d="M17 15V18M17 21V18M17 18H14M17 18H20" stroke="#fffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
            </label>
            <a href="exemplo/products.xlsx">Arquvivo de Exemplo</a>
            <input type="hidden" name="tipo_licitacao" value="Licitacao Produtos">
            <input type="submit" value="Enviar" class="submit">
        </form>
    </div>
    <script>
        document.querySelector('.custom-file-button').addEventListener('click', function() {
            document.querySelector('.file-input').click();
        });
    </script>
</body>
</html>
