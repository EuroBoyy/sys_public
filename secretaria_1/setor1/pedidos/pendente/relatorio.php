<?php
session_start();
require_once "../../../../conexao/conexao.php";
require_once '../../../../vendor/autoload.php';
require_once '../../../../vendor/tecnickcom/tcpdf/tcpdf.php'; // Incluir o arquivo TCPDF

// Função para gerar o PDF
function generatePDF($html) {
    // Crie uma nova instância da classe TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Defina o cabeçalho e o rodapé
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Defina o nome padrão do arquivo PDF
    $pdf->SetTitle('Relatório Mensal de Pedidos');
    $pdf->SetHeaderData('', 0, 'Relatório Mensal de Pedidos', '');

    // Adicione uma página
    $pdf->AddPage();

    // Defina o conteúdo HTML
    $pdf->writeHTML($html, true, false, true, false, '');

    // Saída do PDF
    $pdf->Output('relatorio_pedidos.pdf', 'D');
}

// Inicializar variável para armazenar o conteúdo HTML do relatório
$html = '';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar os dados do formulário
    $mes = $_POST["mes"];
    $ano = $_POST["ano"];

    // Consultar o banco de dados para obter os pedidos do usuário para o mês e o ano selecionados
    // Substitua as consultas SQL e a conexão com o banco de dados de acordo com a sua estrutura
    $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
    $stmt = $conexao->prepare("SELECT * FROM pedidos_pendentes WHERE id_funcionarios = ? AND MONTH(data_do_pedido) = ? AND YEAR(data_do_pedido) = ?");
    $stmt->execute([$_SESSION["id_funcionarios"], $mes, $ano]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar se há pedidos encontrados
    if ($pedidos) {
        // Iterar sobre os pedidos encontrados
        foreach ($pedidos as $pedido) {
            // Recuperar o caminho do arquivo JSON do pedido
            $caminho_arquivo_json = $pedido['arquivo_json'];
            
            // Verificar se o caminho do arquivo JSON está definido e não vazio
            if (!empty($caminho_arquivo_json)) {
                // Ler o conteúdo do arquivo JSON
                $json_pedido = file_get_contents($caminho_arquivo_json);

                // Decodificar o JSON em um array associativo
                $dados_pedido = json_decode($json_pedido, true);

                // Verificar se a decodificação foi bem-sucedida e se existem itens no pedido
                if ($dados_pedido && isset($dados_pedido['itens_do_pedido'])) {
                    // Adicionar informações do pedido ao HTML do relatório
                    $html .= "<h2>ID do Pedido: {$pedido['id_pendente']}</h2>";
                    $html .= "<p>Data do Pedido: {$pedido['data_do_pedido']}</p>";
                    $html .= "<ul>";
                    foreach ($dados_pedido['itens_do_pedido'] as $item) {
                        $html .= "<li>{$item['nome_do_produto']} - Quantidade: {$item['quantidade']}, Preço: {$item['preco']}</li>";
                    }
                    $html .= "</ul>";
                } else {
                    // Caso não haja itens no pedido ou ocorra um erro na decodificação do JSON
                    $html .= "<p>Nenhum item encontrado para este pedido.</p>";
                }
            } else {
                // Caso o caminho do arquivo JSON não esteja definido ou esteja vazio
                $html .= "<p>Caminho do arquivo JSON inválido para este pedido.</p>";
            }
        }
    } else {
        // Caso não haja pedidos encontrados para o mês e ano selecionados
        $html .= "<p>Nenhum pedido encontrado para o mês e ano selecionados.</p>";
    }
}

// Verificar se o botão "Gerar PDF" foi clicado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerar_pdf'])) {
    // Gerar o PDF com o conteúdo HTML
    generatePDF($html);
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Mensal de Pedidos</title>
</head>
<body>
    <h1>Relatório Mensal de Pedidos</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="mes">Mês:</label>
        <select name="mes" id="mes">
            <?php
            // Gerar opções para os meses
            for ($i = 1; $i <= 12; $i++) {
                printf('<option value="%d">%s</option>', $i, date("F", mktime(0, 0, 0, $i, 1)));
            }
            ?>
        </select>
        <label for="ano">Ano:</label>
        <select name="ano" id="ano">
            <?php
            // Gerar opções para os anos (você pode definir um intervalo de anos conforme necessário)
            for ($i = date("Y"); $i >= 2020; $i--) {
                echo "<option value='$i'>$i</option>";
            }
            ?>
        </select>
        <button type="submit" name="gerar_relatorio">Gerar Relatório</button>
    </form>

    <!-- Exibir o relatório na página -->
    <div id="relatorio">
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerar_relatorio'])) {
            // Se o formulário foi enviado, exiba o relatório
            echo $html; 
        }
        ?>
    </div>

    <!-- Botão para baixar o PDF -->
    <?php 
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerar_relatorio'])) {
        // Se o formulário foi enviado, exiba o botão para baixar o PDF
        echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        echo '<input type="hidden" name="mes" value="' . $_POST["mes"] . '">';
        echo '<input type="hidden" name="ano" value="' . $_POST["ano"] . '">';
        echo '<button type="submit" name="gerar_pdf">Baixar PDF</button>';
        echo '</form>';
    }
    ?>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['gerar_pdf'])) {
        // Se o botão "Baixar PDF" foi clicado, gere o PDF
        generatePDF($html);
    }
    ?>
</body>
</html>
