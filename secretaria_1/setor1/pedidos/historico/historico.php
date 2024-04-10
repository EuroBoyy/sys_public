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

    // Definir a quantidade de pedidos por página
    $pedidosPorPagina = 3;

    // Obter o número da página atual, ou definir como 1 se não estiver definido
    $paginaAtual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

    // Calcular o deslocamento (offset) para a consulta SQL
    $offset = ($paginaAtual - 1) * $pedidosPorPagina;

    try {
        // Estabelecer conexão com o banco de dados
        $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consultar o total de pedidos pendentes
        // Consultar o total de pedidos pendentes
        // Consultar o total de pedidos pendentes
        $stmt_total = $conexao->prepare("SELECT COUNT(*) as total FROM pedidos_pendentes WHERE id_funcionarios = :id_funcionarios");
        $stmt_total->bindParam(':id_funcionarios', $_SESSION['id_funcionarios'], PDO::PARAM_INT);
        $stmt_total->execute();
        $totalPedidos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];


        // Calcular o total de páginas
        $totalPaginas = ceil($totalPedidos / $pedidosPorPagina);

        // Consultar os pedidos pendentes com status "Pendente", limitando à página atual
        $stmt_pedidos = $conexao->prepare("SELECT id_pendente, id_funcionarios, nome_funcionarios, data_do_pedido, arquivo_json, objeto_licitacao, total_pedido, status_do_pedido FROM pedidos_pendentes WHERE id_funcionarios = :id_funcionarios LIMIT :offset, :limite");
        $stmt_pedidos->bindParam(':id_funcionarios', $_SESSION['id_funcionarios'], PDO::PARAM_INT);
        $stmt_pedidos->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt_pedidos->bindParam(':limite', $pedidosPorPagina, PDO::PARAM_INT);
        $stmt_pedidos->execute();
        $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

        // Iterar sobre cada pedido para decodificar os dados do JSON
        foreach ($pedidos as &$pedido) {
            // Decodificar o JSON para obter os dados do pedido
            $dados_pedido = json_decode(file_get_contents($pedido['arquivo_json']), true);
            
            // Verificar se a decodificação foi bem-sucedida
            if ($dados_pedido !== null) {
                // Adicionar os dados do pedido aos dados recuperados do banco de dados
                $pedido = array_merge($pedido, $dados_pedido);
            } else {
                // Em caso de falha na decodificação, marcar os itens do pedido como vazio
                $pedido['itens_do_pedido'] = [];
            }
        }
        unset($pedido); // Limpar a referência após o loop
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
    <title>Pedidos Pendentes</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../../css/pendentes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
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
                                 <a href="historico.php" class="dropdown__sublink">
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
<h1>Pedidos Pendentes</h1>
<table class="content-table">
    <thead>
        <tr>
        <th>ID do Pedido</th>
        <th>ID do Funcionário</th>
        <th>Ver Pedido</th>
        <th>Data do Pedido</th>
        <th>Objeto</th>
        <th>Linha do Tempo</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($pedidos as $pedido) : ?>
    <tr>
        <td><?php echo $pedido['id_pendente']; ?></td>
        <td><?php echo $pedido['id_funcionarios']; ?></td>
        <td class="form-td">
            <a data-fancybox data-src="#pedido_<?php echo $pedido['id_pendente']; ?>" href="javascript:;" class="view-pedido">Ver Pedido</a>
            <div class="div-modal" style="display: none;" id="pedido_<?php echo $pedido['id_pendente']; ?>">
            <?php
                // Consulta para obter o status do pedido
                $stmt_status = $conexao->prepare("SELECT status_do_pedido FROM pedidos_pendentes WHERE id_pendente = :id_pendente");
                $stmt_status->bindParam(':id_pendente', $pedido['id_pendente']);
                $stmt_status->execute();
                $status_pedido = $stmt_status->fetch(PDO::FETCH_ASSOC)['status_do_pedido'];

                ?>
                <h2 class="h2-status">STATUS - <?php echo $status_pedido; ?></h2>
                <h2>Pedido <?php echo $pedido['id_pendente']; ?></h2>
                        <?php $contrato_exibido = false;
                if (isset($pedido['itens_do_pedido']) && is_array($pedido['itens_do_pedido'])) : 
                    foreach ($pedido['itens_do_pedido'] as $item) : 
                        if (!$contrato_exibido) {
                            echo '<p>Contrato N° ' . $item['numero_contrato'] . '</p>';
                            
                            $contrato_exibido = true;

                            echo '<p>' . 'Nome da Empresa '. $item['nome_empresa'] . '</p>';
                            echo '<p>' . 'CNPJ - N° ' .$item['cnpj_empresa'] . '</p>';
                        }?>
                        <?php endforeach; ?>
                <p><?php echo $pedido['nome_funcionarios']; ?> - <?php echo $pedido['id_funcionarios']; ?> </p>
                <p>Data do Pedido: <?php echo $pedido['data_do_pedido']; ?></p>
                <p>Total R$<?php echo number_format($pedido['total_pedido'], 2, ',', '.'); ?></p>
                    <h3>Itens do Pedido:</h3>
                    <table class="content-table-modal">
                        <thead>
                            <tr>
                                <th>Produto ID</th>
                                <th>Quantidade</th>
                                <th>Nome do Produto</th>
                                <th>Linha da Tabela</th>
                                <th>Unidade</th>
                                <th>Marca do Produto</th>
                                <th>Preço Unitário</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedido['itens_do_pedido'] as $item) : ?>
                                <tr>
                                    <td><?php echo $item['produto_id']; ?></td>
                                    <td><?php echo $item['quantidade']; ?></td>
                                    <td><?php echo $item['nome_do_produto']; ?></td>
                                    <td><?php echo $item['linha_da_tabela']; ?></td>
                                    <td><?php echo $item['unidade_produto']; ?></td>
                                    <td><?php echo $item['marca_produto']; ?></td>
                                    <td>R$<?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </td>
        <td><?php echo $pedido['data_do_pedido']; ?></td>
        <td><?php echo $item['objeto_licitacao']; ?></td>
        <td class="form-td">
        <!-- Adicione um botão ou link para abrir a modal de histórico -->
        <a href="javascript:;" data-fancybox data-src="#historico_pdf_<?php echo $pedido['id_pendente']; ?>" class="view-historico-pdf">Histórico de PDFs</a>
        
        <!-- Estrutura da modal para o histórico de PDFs -->
        <div class="div-modal" style="display: none;" id="historico_pdf_<?php echo $pedido['id_pendente']; ?>">
            <h2>Histórico de PDFs do Pedido <?php echo $pedido['id_pendente']; ?></h2>
            
            <!-- Recupere e exiba os PDFs enviados para este pedido -->
            <?php
            $stmt_pdf = $conexao->prepare("SELECT setor1_pdf, data_setor1, setor2_pdf, data_setor2, setor3_pdf, data_setor3, setor4_pdf, data_setor4 FROM pedidos_pendentes WHERE id_pendente = :id_pendente");
            $stmt_pdf->bindParam(':id_pendente', $pedido['id_pendente']);
            $stmt_pdf->execute();
            $pdfs = $stmt_pdf->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($pdfs as $pdf) {
                echo '<p>Data: ' . $pdf['data_setor1'] . '</p>';
                echo '<p><a href="' . $pdf['setor1_pdf'] . '" target="_blank">Visualizar PDF Setor 1</a></p>';
                echo '<p>Data: ' . $pdf['data_setor2'] . '</p>';
                echo '<p><a href="' . $pdf['setor2_pdf'] . '" target="_blank">Visualizar PDF Setor 2</a></p>';
            }
            ?>
        </div>
        </td>
        <td>R$<?php echo number_format($pedido['total_pedido'], 2, ',', '.'); ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Paginação -->
<div class="pagination">
    <?php if ($totalPaginas > 1): ?>
        <a href="?pagina=1">Primeira</a>
        <?php for ($i = max(1, $paginaAtual - 2); $i <= min($paginaAtual + 2, $totalPaginas); $i++): ?>
            <a <?php if ($i === $paginaAtual) echo 'class="active"'; ?> href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <a href="?pagina=<?php echo $totalPaginas; ?>">Última</a>
    <?php endif; ?>
</div>

<script>
    <?php foreach ($pedidos as $pedido) : ?>
    document.getElementById('svg_<?php echo $pedido['id_pendente']; ?>').addEventListener('click', function() {
        document.getElementById('pdf_<?php echo $pedido['id_pendente']; ?>').click();
    });
    <?php endforeach; ?>
</script>
<script>
    $(document).ready(function() {
        $(".view-pedido").fancybox({
            autoFocus: false,
            touch: false,
            buttons: [
                'close'
            ]
        });
    });
</script>
<script src="../../main.js"></script>
</body>
</html>
