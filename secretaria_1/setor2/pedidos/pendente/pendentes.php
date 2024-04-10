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
if ($_SESSION["setor_funcionarios"] !== 2) {
    // Se não, acesso negado
    header("Location: ../../tela_login/tela_login.php");
    exit();
}

// Validar e processar o envio do arquivo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['pdf']) && isset($_POST['pedido_id'])) {
    $pedido_id = $_POST['pedido_id'];
    $arquivo = $_FILES['pdf'];

    // Verificar se o arquivo foi enviado com sucesso
    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        // Definir o diretório de destino para o upload do arquivo
        $diretorio_destino = "../../../setor1/pedidos/realizar/uploads/";

        // Gerar um nome único para o arquivo
        $nome_arquivo = uniqid() . '_' . basename($arquivo['name']);
        $caminho_arquivo = $diretorio_destino . $nome_arquivo;

        // Mover o arquivo para o diretório de destino
        if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
            // Arquivo movido com sucesso, agora execute o UPDATE no banco de dados
            try {
                // Estabelecer conexão com o banco de dados
                $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
                $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Executar o UPDATE na tabela de pedidos pendentes
                $stmt = $conexao->prepare("UPDATE pedidos_pendentes SET status_do_pedido = 'Validado_Setor2', setor2_pdf = :arquivo_validacao, data_setor2 = NOW() WHERE id_pendente = :id_pendente");
                $stmt->bindParam(':arquivo_validacao', $caminho_arquivo);
                $stmt->bindParam(':id_pendente', $pedido_id);
                $stmt->execute();

                echo "Arquivo enviado com sucesso e pedido validado.";
            } catch (PDOException $e) {
                echo "Erro ao conectar com o banco de dados:" . $e->getMessage();
                exit();
            }
        } else {
            echo "Erro ao mover o arquivo para o diretório de destino.";
        }
    } else {
        echo "Erro no envio do arquivo.";
    }
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
    $stmt_total = $conexao->prepare("SELECT COUNT(*) AS total FROM pedidos_pendentes WHERE status_do_pedido = 'Validado_Setor1'");
    $stmt_total->execute();
    $totalPedidos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

    // Calcular o total de páginas
    $totalPaginas = ceil($totalPedidos / $pedidosPorPagina);

    
    // Consultar os pedidos pendentes com status "Pendente", limitando à página atual
    $stmt_pedidos = $conexao->prepare("SELECT id_pendente, id_funcionarios, nome_funcionarios, data_do_pedido, arquivo_json, objeto_licitacao, total_pedido, status_do_pedido FROM pedidos_pendentes WHERE status_do_pedido = 'Validado_Setor1' LIMIT :offset, :limite");
    $stmt_pedidos->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt_pedidos->bindParam(':limite', $pedidosPorPagina, PDO::PARAM_INT);
    $stmt_pedidos->execute();
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

    // Iterar sobre cada pedido para decodificar os dados do JSON
    foreach ($pedidos as &$pedido) {
        // Construir o caminho completo para o arquivo JSON
        $caminho_arquivo_json = "../../../setor1/pedidos/realizar/uploads/" . basename($pedido['arquivo_json']);

        // Decodificar o JSON para obter os dados do pedido
        $dados_pedido = json_decode(file_get_contents($caminho_arquivo_json), true);
        
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
            <a href="home.php" class="nav__logo">
                <i class="ri-home-2-line"></i> Sys Public
            </a>
            <div class="nav__toggle" id="nav-toggle">
                <i class="ri-menu-line nav__burger"></i>
                <i class="ri-close-line nav__close"></i>
            </div>
        </div>
        <div class="nav__menu" id="nav-menu">
            <ul class="nav__list">
                <li><a href="../../home.php" class="nav__link">Home</a></li>
                <li class="dropdown__item">
                    <div class="nav__link">
                        Pedidos <i class="ri-arrow-down-s-line dropdown__arrow"></i>
                    </div>
                    <ul class="dropdown__menu">
                        <li>
                            <a href="../historico/historico.php" class="dropdown__link">
                                <i class="ri-file-paper-2-line"></i> Histórico Pedidos
                            </a>
                        </li>
                        <li>
                            <a href="pendentes.php" class="dropdown__link">
                                <i class="ri-list-check-2"></i> Pedidos Pendentes
                            </a>
                        </li>
                        <li>
                            <a href="../realizar/realizar_pedido.php" class="dropdown__link">
                                <i class="ri-dropbox-line"></i> Realizar Pedidos
                            </a>
                        </li>
                    </ul>
                </li>
                <li><a href="logout.php" class="nav__link">Sair</a></li>
            </ul>
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
                <div class="form-td">
                    <form id="form_<?php echo $pedido['id_pendente']; ?>" action="pendentes.php" method="post" enctype="multipart/form-data">
                        <p>Validar</p>
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id_pendente']; ?>">
                        <div class="file-upload-container">
                            <svg id="svg_<?php echo $pedido['id_pendente']; ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 3H12H8C6.34315 3 5 4.34315 5 6V18C5 19.6569 6.34315 21 8 21H11M13.5 3L19 8.625M13.5 3V7.625C13.5 8.17728 13.9477 8.625 14.5 8.625H19M19 8.625V11.8125" stroke="#fffffff" stroke-width="2"></path>
                                <path d="M17 15V18M17 21V18M17 18H14M17 18H20" stroke="#fffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <input type="file" id="pdf_<?php echo $pedido['id_pendente']; ?>" name="pdf" accept=".pdf" class="input-file" style="display: none;">
                        <input type="submit" value="Enviar" class="submit-validar">
                    </form>
                </div>
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
