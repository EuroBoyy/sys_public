<?php
session_start();
require_once "../../../../conexao/conexao.php";
require_once '../../../../vendor/autoload.php';
require_once '../../../../vendor/tecnickcom/tcpdf/tcpdf.php'; // Incluir o arquivo TCPDF

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

try {
    // Obter o ID do funcionário da sessão
    $id_funcionarios = $_SESSION['id_funcionarios'];

    // Definir o nome do funcionário como padrão
    $nome_funcionario = "";

    // Obter o nome do funcionário
    try {
        $conexao = new PDO("mysql:host=localhost;dbname=sys_public", "root", "leody2005");
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conexao->prepare("SELECT nome_funcionarios FROM funcionarios WHERE id_funcionarios = :id_funcionarios");
        $stmt->bindParam(':id_funcionarios', $id_funcionarios);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $nome_funcionario = $resultado['nome_funcionarios'];
        } else {
            // Se não encontrar o funcionário, definir um nome padrão
            $nome_funcionario = "Funcionário Desconhecido";
        }
    } catch (PDOException $e) {
        echo "Erro ao obter o nome do funcionário: " . $e->getMessage();
        exit();
    }

    // Verificar se o ID do funcionário selecionado foi enviado
    if (!isset($_POST['funcionario'])) {
        echo "ID do funcionário não foi enviado.";
        exit();
    }

    // Obter o ID do funcionário selecionado do formulário
    $id_funcionario_selecionado = $_POST['funcionario'];

    // Verificar se o ID do funcionário selecionado é válido
    if (empty($id_funcionario_selecionado)) {
        echo "ID do funcionário é inválido.";
        exit();
    }

    // Verificar se os dados JSON foram recebidos corretamente
    $carrinho_json = file_get_contents('php://input');
    $carrinho = json_decode($carrinho_json, true);

    // Verificar se os dados foram decodificados corretamente
    if ($carrinho === null || !isset($carrinho['carrinho'])) {
        echo "Dados do carrinho ausentes ou inválidos.";
        exit();
    }

    // Estabelecer a conexão com o banco de dados
    $conexao = new mysqli("localhost", "root", "leody2005", "sys_public");

    // Verificar se houve algum erro na conexão
    if ($conexao->connect_error) {
        die("Erro de conexão com o banco de dados: " . $conexao->connect_error);
    }

    // Iniciar uma transação
    $conexao->begin_transaction();

    date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário para São Paulo
    $data_pedido = date('Y-m-d H:i:s');

    // Montar os dados do pedido
    $pedido = [
        'nome_funcionario' => $nome_funcionario,
        'data_do_pedido' => $data_pedido,
        'itens_do_pedido' => []
    ];

    // Limitar o número de produtos a serem inseridos no pedido
    $limite_produtos = 100;
    $contador_produtos = 0;

    // Calcular o total do pedido
    $total_pedido = 0;

    // Recuperar informações adicionais da tabela "estoque"
    $stmtEstoque = $conexao->prepare("SELECT numero_licitacao, modalidade_tipo, objeto_tipo, objeto_licitacao, numero_contrato, nome_empresa FROM estoque WHERE id_produto = ?");
    $stmtEstoque->bind_param("i", $produto_id);

    foreach ($carrinho['carrinho'] as $item) {
        // Verificar se já atingiu o limite de produtos
        if ($contador_produtos >= $limite_produtos) {
            break;
        }
        $preco_final = $item['preco'] * $item['quantidade'];
        $total_pedido += $preco_final; // Adicionar o preço final do item ao total do pedido

        $produto_id = $item['id'];
        $quantidade = $item['quantidade']; // Obtém a quantidade do item do carrinho
        $pedido['itens_do_pedido'][] = [
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'preco' => $preco_final, // Agora, armazenamos o preço final do item
            'nome_do_produto' => $item['nome'],
            'numero_contrato' => $item['numero_contrato'],
            'cnpj_empresa' => $item['cnpj_empresa'],
            'nome_empresa' => $item['nome_empresa'],
            'linha_da_tabela' => $item['linha_da_tabela'],
            'descricao_produto' => $item['descricao_produto'],
            'marca_produto' => $item['marca_produto'],
            'unidade_produto' => $item['unidade_produto'],
            'objeto_licitacao' => $item['objeto_licitacao'] // Adicionando o objeto_licitacao
        ];


        // Atualizar o estoque subtraindo a quantidade do produto
        $stmtUpdateEstoque = $conexao->prepare("UPDATE estoque SET quantidade_do_produto = quantidade_do_produto - ? WHERE id_produto = ?");
        $stmtUpdateEstoque->bind_param("ii", $quantidade, $produto_id);
        $stmtUpdateEstoque->execute();

        $contador_produtos++;
    }

    // Adicionar o total do pedido ao array do pedido
    $pedido['total_pedido'] = $total_pedido;

    // Gerar um ID aleatório
    function gerar_id_aleatorio() {
        $id_gerado = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        return $id_gerado;
    }
    $id_gerado = gerar_id_aleatorio();

    // Converter o pedido em JSON
    $pedido_json = json_encode($pedido);

    // Criar um nome único para o arquivo
    $nome_arquivo = 'pedido_' . uniqid() . '.json';
    $caminho_arquivo = '../realizar/uploads/' . $nome_arquivo;

    // Armazenar o JSON em um arquivo
    file_put_contents($caminho_arquivo, $pedido_json);

    // Inserir os dados do pedido na tabela pedidos_pendentes
    $stmt = $conexao->prepare("INSERT INTO pedidos_pendentes (id_pendente, id_funcionarios, nome_funcionarios, status_do_pedido, arquivo_json, data_do_pedido, total_pedido, objeto_licitacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssd", $id_gerado, $id_funcionarios, $nome_funcionario, $id_funcionario_selecionado, $caminho_arquivo, $data_pedido, $total_pedido, $item['objeto_licitacao']);

    // Executar a inserção
    if ($stmt->execute() && $conexao->affected_rows > 0) {
        // Commit da transação
        $conexao->commit();
        echo "Pedido processado com sucesso!";

        // Inicializar o TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Definir informações do documento
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Seu Nome');
        $pdf->SetTitle('Pedido');
        $pdf->SetSubject('Pedido');
        $pdf->SetKeywords('Pedido, PDF');

        // Definir margens
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // Definir modo de fonte padrão
        $pdf->SetFont('dejavusans', '', 10);

        // Adicionar uma página
        $pdf->AddPage();

        // Recuperar informações adicionais da tabela "estoque" para o primeiro item do pedido
        $primeiro_item = $pedido['itens_do_pedido'][0];
        $produto_id = $primeiro_item['produto_id'];
        $stmtEstoque->execute();
        $resultadoEstoque = $stmtEstoque->get_result()->fetch_assoc();
        $primeiro_item['numero_licitacao'] = $resultadoEstoque['numero_licitacao'];
        $primeiro_item['modalidade_tipo'] = $resultadoEstoque['modalidade_tipo'];
        $primeiro_item['objeto_tipo'] = $resultadoEstoque['objeto_tipo'];
        $primeiro_item['objeto_licitacao'] = $resultadoEstoque['objeto_licitacao'];
        $primeiro_item['numero_contrato'] = $resultadoEstoque['numero_contrato'];
        $primeiro_item['nome_empresa'] = $resultadoEstoque['nome_empresa'];

        // Atualizar o primeiro item do pedido com as informações adicionais
        $pedido['itens_do_pedido'][0] = $primeiro_item;

        // Montar o conteúdo do PDF
        $conteudo_pdf = '<h1 text-align="center">SOLICITAÇÃO DE EMPENHO</h1>';
        $conteudo_pdf .= '<p>Sirvo-me deste expediente para solicitar o empenhamento das despesas conforme descritas abaixo.</p>';
        $conteudo_pdf .= '<h1>DETALHES DO PEDIDO</h1>';
        $conteudo_pdf .= '<p><strong>Nome do Funcionário:</strong> ' . $nome_funcionario . '</p>';
        $conteudo_pdf .= '<p><strong>Status do Pedido:</strong> Pendente</p>';
        $conteudo_pdf .= '<p><strong>Data do Pedido:</strong> ' . date('Y-m-d H:i:s') . '</p>';

        // Incluir informações do primeiro item do pedido no conteúdo do PDF
        $conteudo_pdf .= '<p><strong>Licitação Nº:</strong> ' . $primeiro_item['numero_licitacao'] . '</p>';
        $conteudo_pdf .= '<p><strong>Modalidade:</strong> ' . $primeiro_item['modalidade_tipo'] . '</p>';
        $conteudo_pdf .= '<p><strong>Tipo do Objeto:</strong> ' . $primeiro_item['objeto_tipo'] . '</p>';
        $conteudo_pdf .= '<p><strong>Objeto:</strong> ' . $primeiro_item['objeto_licitacao'] . '</p>';
        $conteudo_pdf .= '<p><strong>Contrato Nº:</strong> ' . $primeiro_item['numero_contrato'] . '</p>';
        $conteudo_pdf .= '<p><strong>Empresa:</strong> ' . $primeiro_item['nome_empresa'] . '</p>';

        // Iterar sobre os itens do pedido para preencher a tabela
        $conteudo_pdf .= '<h2>Itens do Pedido</h2>';
        $conteudo_pdf .= '<table border="1" cellpadding="5">';
        $conteudo_pdf .= '<tr><th>Produto</th><th>Marca</th><th>Unid.</th><th>Quant.</th><th>P.UNITÁRIO</th></tr>';
        foreach ($pedido['itens_do_pedido'] as $item) {
            $conteudo_pdf .= '<tr>';
            $conteudo_pdf .= '<td>' . $item['nome_do_produto'] . '</td>';
            $conteudo_pdf .= '<td>' . $item['marca_produto'] . '</td>';
            $conteudo_pdf .= '<td>' . $item['unidade_produto'] . '</td>';
            $conteudo_pdf .= '<td>' . $item['quantidade'] . '</td>';
            $conteudo_pdf .= '<td>R$' . number_format($item['preco'] / $item['quantidade'], 2) . '</td>'; // Preço unitário
            $conteudo_pdf .= '</tr>';
        }
        $conteudo_pdf .= '</table>';

        // Adicionar total do pedido
        $conteudo_pdf .= '<p><strong>Total do Pedido:</strong> R$' . number_format($total_pedido, 2) . '</p>';


        // Adicionar o conteúdo ao PDF
        $pdf->writeHTML($conteudo_pdf, true, false, true, false, '');

        // Definir o diretório onde os arquivos PDF serão salvos
        $dir_destino = __DIR__ . '/../realizar/uploads/';

        // Definir nome do arquivo
        $nome_arquivo_pdf = 'pedido_' . $id_gerado . '.pdf';

        // Salvar o PDF no servidor
        $pdf->Output($dir_destino . $nome_arquivo_pdf, 'F');


        exit();
        
    } else {
        echo "Erro ao inserir pedido na tabela pedidos_pendentes.";
    }

    // Fechar a conexão
    $conexao->close();
} catch (Exception $e) {
    // Em caso de erro, fazer rollback da transação
    $conexao->rollback();
    echo "Erro ao processar o pedido: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    echo "Trace: " . $e->getTraceAsString();
}
?>
