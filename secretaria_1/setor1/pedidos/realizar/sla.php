<?php
session_start();
require_once "../../../../conexao/conexao.php";
require_once '../../../../vendor/autoload.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['id_funcionarios'])) {
    // Se não estiver logado, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}

// Verificar se o usuário está no setor correto
if ($_SESSION["setor_funcionarios"] !== 1) {
    // Se não, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}

// Conectar ao banco de dados
try {
    $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro ao conectar com o banco de dados:" . $e->getMessage();
    exit();
}

// Consultar contratos disponíveis
$stmt = $conexao->query("SELECT DISTINCT numero_contrato, objeto_licitacao FROM estoque");
$contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inicializar variáveis de produtos e licitação
$produtos = [];
$licitacao = [];

// Verificar se o contrato foi enviado via POST
if(isset($_POST['contrato'])) {
    $numeroContrato = $_POST['contrato'];

    // Consultar produtos associados ao contrato
    $stmt = $conexao->prepare("SELECT * FROM estoque WHERE numero_contrato = ? AND quantidade_do_produto > 0");
    $stmt->execute([$numeroContrato]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultar informações da licitação
    $stmtLic = $conexao->prepare("SELECT cnpj_empresa, nome_empresa, objeto_licitacao, data_da_lic, numero_licitacao, tipo_licitacao FROM estoque WHERE numero_contrato = ? LIMIT 1");
    $stmtLic->execute([$numeroContrato]);
    $licitacao = $stmtLic->fetch(PDO::FETCH_ASSOC);

    // Verificar se há informações de licitação para o contrato selecionado
    if (empty($licitacao)) {
        // Nenhuma informação de licitação encontrada para o contrato selecionado
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../../css/realizar.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/fontawesome/css/all.css">
    <title>Lista de Produtos</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
                                 <a href="../inserir/inserir_produtos.php" class="dropdown__sublink">
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
                                 <a href="realizar_pedido.php" class="dropdown__sublink">
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
<div class="superior-card">
    <div class="left-card">
            <form method="POST" class="form-select">
                <p>Selecione um Contrato</p>
                <div class="select-container">
                    <select id="contrato" name="contrato" class="select-box" data-objeto-licitacao="<?php echo isset($licitacao['objeto_licitacao']) ? $licitacao['objeto_licitacao'] : ''; ?>">
                        <option value="">Selecione...</option>
                                    <?php foreach ($contratos as $contrato) : ?>
                                        <option value="<?php echo $contrato['numero_contrato']; ?>" data-objeto-licitacao="<?php echo isset($contrato['objeto_licitacao']) ? $contrato['objeto_licitacao'] : ''; ?>"><?php echo $contrato['numero_contrato']; ?></option>
                                    <?php endforeach; ?>
                    </select>
                </div>
                <?php
                    // Consultar funcionários disponíveis
                    $stmtFuncionarios = $conexao->query("SELECT id_funcionarios, nome_funcionarios FROM funcionarios");
                    $funcionarios = $stmtFuncionarios->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <!-- Selecione um Funcionário -->
                    <p>Selecione um Funcionário</p>
                    <div class="select-container">
                        <select id="funcionario" name="funcionario" class="select-box">
                            <option value="">Selecione...</option>
                            <?php foreach ($funcionarios as $funcionario) : ?>
                                <option value="<?php echo $funcionario['id_funcionarios']; ?>"><?php echo $funcionario['nome_funcionarios']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <input type="hidden" id="objeto_licitacao" name="objeto_licitacao" value="<?php echo isset($licitacao['objeto_licitacao']) ? $licitacao['objeto_licitacao'] : ''; ?>">
                <input type="submit" id="submit-button" value="Selecionar" class="submit">
            </form>
            <div class="geral_card">
                <div class="objeto_card">
                    <?php if(isset($licitacao) && !empty($licitacao)) : ?>
                        <div class="lic_card">
                            <p><h3>Tipo</h3> <?php echo $licitacao['tipo_licitacao']; ?></p>
                            <p><h3>N° Licitação </h3><?php echo $licitacao['numero_licitacao']; ?></p>
                        </div>
                        <p> <h3>Objeto da Licitação:</h3> <?php echo $licitacao['objeto_licitacao']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="info_card">
                    <?php if(isset($licitacao) && !empty($licitacao)) : ?>
                        <p><h3>Empresa</h3> <?php echo $licitacao['nome_empresa']; ?></p>
                        <p><h3>CNPJ N°</h3> <?php echo $licitacao['cnpj_empresa']; ?></p>
                        <p><h3>Data</h3> <?php echo $licitacao['data_da_lic']; ?></p>
                </div>
            </div>
        <div class="finalizar-card">
            <div class="finalizar">
                <button id="finalizar-pedido" class="submit-2">Finalizar Pedido</button>
            </div>
        </div>
</div>
<div class="inferior-card">
    
</div>
<div class="container-div">
    <?php else: ?>
        <p class="p-error">Nenhuma informação de licitação encontrada para o contrato selecionado.</p>
    <?php endif; ?>
    <div class="center-card">
    <?php if(isset($produtos)): ?>
        <div class="content1">
            <p>Estoque</p>
            <table class="content-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Saldo</th>
                        <th>Quantidade</th>
                        <th>Unidade</th> <!-- Nova coluna para a quantidade -->
                        <th>Adicionar ao Carrinho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto) : ?>
                        <tr>
                            <td><?php echo $produto['id_produto']; ?></td>
                            <td class="nome_produto"><?php echo $produto['nome_do_produto']; ?></td>
                            <td><?php echo 'R$' . number_format($produto['preco_unitario'], 2, ',', '.'); ?></td>
                            <td><?php echo $produto['quantidade_do_produto']; ?></td>
                            <td>
                                <input type="number" class="quantidade" value="1" min="1" max="10"> <!-- Campo de entrada para a quantidade -->
                            </td>
                            <td><?php echo $produto['unidade_produto'];?></td>
                            <td>
                                <button class="adicionar-carrinho" 
                                        data-objeto="<?php echo $produto['objeto_licitacao']; ?>"
                                        data-id="<?php echo $produto['id_produto']; ?>"
                                        data-nome="<?php echo $produto['nome_do_produto']; ?>"
                                        data-preco="<?php echo $produto['preco_unitario']; ?>"
                                        data-unidade="<?php echo $produto['unidade_produto']; ?>"
                                        data-contrato="<?php echo $produto['numero_contrato']; ?>"
                                        data-cnpj="<?php echo $produto['cnpj_empresa']; ?>"
                                        data-nome-empresa="<?php echo $produto['nome_empresa']; ?>"
                                        data-linha-tabela="<?php echo $produto['linha_da_tabela']; ?>"
                                        data-descricao="<?php echo $produto['descricao_produto']; ?>"
                                        data-marca="<?php echo $produto['marca_produto']; ?>">Adicionar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    </div>

        <div class="rigth-card">
                <div id="carrinho" class="cart">
                    <p>Info Pedido</p>
                    <table id="lista-carrinho" class="content-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th>Remover</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Os itens do carrinho serão exibidos aqui -->
                        </tbody>
                    </table>
                    <div id="soma-total"></div>
                </div>
        </div>
</div>
<script>
    $(document).ready(function(){

        // Array para armazenar os itens do carrinho
        var carrinho = [];

        // Função para calcular a soma total do carrinho
        function calcularSomaTotal() {
            var somaTotal = 0;
            carrinho.forEach(function(item) {
                somaTotal += parseFloat(item.preco) * parseFloat(item.quantidade);
            });
            $('#soma-total').text('Total Pedido: R$ ' + somaTotal.toFixed(2));
        }

        // Atualizar a exibição do carrinho na página e calcular a soma total
        function atualizarCarrinho() {
            $('#lista-carrinho tbody').empty();
            carrinho.forEach(function(item) {
                var itemHtml = '<tr>' + 
                                '<td>' + item.id + '</td>' +
                                '<td>' + item.nome + '</td>' +
                                '<td>' + 'R$' + parseFloat(item.preco).toFixed(2).replace('.', ',') + '</td>' +
                                '<td>' + item.quantidade + '</td>' +
                                '<td><button class="remover-carrinho" data-id="' + item.id + '">Remover</button></td>' +
                            '</tr>';
                $('#lista-carrinho tbody').append(itemHtml);
            });
            calcularSomaTotal(); // Calcular e exibir a soma total
        }

        // Evento change para atualizar o objeto de licitação quando um contrato é selecionado
        var objetoLicitacao = ""; // Variável para armazenar o valor do objeto de licitação

        $('#contrato').change(function() {
            objetoLicitacao = $(this).find('option:selected').data('objeto-licitacao');
            console.log("Valor de objetoLicitacao:", objetoLicitacao); // Verifica se o valor está sendo capturado corretamente
        });

        // Adicionar produto ao carrinho
        $(document).on('click', '.adicionar-carrinho', function(){
            var idProduto = $(this).data('id');
            var nomeProduto = $(this).data('nome');
            var precoProduto = $(this).data('preco');
            var numeroContrato = $(this).data('contrato');
            var cnpjEmpresa = $(this).data('cnpj');
            var nomeEmpresa = $(this).data('nome-empresa');
            var linhaTabela = $(this).data('linha-tabela');
            var descricaoProduto = $(this).data('descricao');
            var marcaProduto = $(this).data('marca');
            var unidadeProduto = $(this).data('unidade');
            var quantidade = $(this).closest('tr').find('.quantidade').val(); // Capturar o valor da quantidade
            adicionarAoCarrinho(idProduto, nomeProduto, precoProduto, numeroContrato, cnpjEmpresa, nomeEmpresa, linhaTabela, descricaoProduto, marcaProduto, unidadeProduto, quantidade);
        });

        // Remover produto do carrinho
        $(document).on('click', '.remover-carrinho', function(){
            var idProduto = $(this).data('id');
            removerDoCarrinho(idProduto);
        });

        // Função para adicionar produto ao carrinho
        function adicionarAoCarrinho(idProduto, nomeProduto, precoProduto, numeroContrato, cnpjEmpresa, nomeEmpresa, linhaTabela, descricaoProduto, marcaProduto, unidadeProduto,quantidade) {
            var objetoLicitacao = $('#contrato option:selected').data('objeto-licitacao'); // Obtém o valor de objeto_licitacao do contrato selecionado

            var itemCarrinho = { 
                id: idProduto, 
                nome: nomeProduto, 
                preco: precoProduto,
                numero_contrato: numeroContrato,
                linha_da_tabela: linhaTabela,
                descricao_produto: descricaoProduto,
                marca_produto: marcaProduto,
                unidade_produto:unidadeProduto,
                quantidade: quantidade, // Adicionar a quantidade ao objeto do carrinho
                objeto_licitacao: objetoLicitacao // Adicionar objeto_licitacao ao objeto do carrinho
            };

            // Verificar se os campos 'cnpj_empresa' e 'nome_empresa' estão presentes antes de atribuir ao itemCarrinho
            if (cnpjEmpresa !== undefined) {
                itemCarrinho.cnpj_empresa = cnpjEmpresa;
            }

            if (nomeEmpresa !== undefined) {
                itemCarrinho.nome_empresa = nomeEmpresa;
            }

            carrinho.push(itemCarrinho);
            atualizarCarrinho();
        }

        // Função para remover produto do carrinho
        function removerDoCarrinho(idProduto) {
            carrinho = carrinho.filter(function(item) {
                return item.id !== idProduto;
            });
            atualizarCarrinho();
        }

        // Enviar pedido para processamento
        $('#finalizar-pedido').click(function(){
        // Obter o valor selecionado do funcionário
        var funcionarioSelecionado = $('#funcionario').val();

        // Verificar se um funcionário foi selecionado
        if (funcionarioSelecionado === '') {
            alert('Por favor, selecione um funcionário.');
            return; // Abortar o envio do pedido se nenhum funcionário foi selecionado
        }

        // Obter a soma total do carrinho
        var somaTotal = 0;
        carrinho.forEach(function(item) {
            somaTotal += parseFloat(item.preco) * parseFloat(item.quantidade);
            // Adicione a somaTotal a cada item do carrinho
            item.somaTotal = somaTotal;
        });

        // Criar objeto com os dados do carrinho, o funcionário selecionado e a soma total
        var dadosPedido = {
            carrinho: carrinho,
            funcionario: funcionarioSelecionado, // Incluir o funcionário selecionado nos dados do pedido
            somaTotal: somaTotal
        };

        // Enviar dados do pedido para processamento em JSON
        $.ajax({
            url: 'finalizar_pedido.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dadosPedido), // Incluir os dados do pedido no objeto enviado
            success: function(response){
                alert(response); // Exemplo: exibir a resposta do servidor
                window.location.href = window.location.href;
            }
        });
    });
    });
</script>
</body>
</html>