<?php 

require_once "../../../../conexao/conexao.php";
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_funcionarios'])) {
    // Se não estiver logado, redirecionar para a tela de login
    header("Location: ../../../tela_login/tela_login.php");
    exit();
}

// Verificar se o usuário está no setor correto
if ($_SESSION["setor_funcionarios"] !== 1) {
    // Se não estiver no setor correto, redirecionar para a página inicial
    header("Location: ../tela_login/tela_login.php");
    exit();
}

// Verificar se o arquivo foi enviado corretamente
if (isset($_FILES['excelFile']['name'])) {
    // Obter o caminho do arquivo temporário
    $excelFilePath = $_FILES['excelFile']['tmp_name'];

    // Carregar o arquivo Excel
    require_once '../../../../vendor/autoload.php';
    $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($excelFilePath);

    // Obter a página do arquivo
    $worksheet = $spreadsheet->getActiveSheet();
    // Obter dados da primeira linha (cabeçalho)
    $firstRowData = $worksheet->toArray()[0];
    // Definir um array de mapeamento das colunas do Excel para o banco de dados
    $excelColumnMapping = [
        'DISCRIMINACAO' => 'nome_do_produto',
        'MARCA' => 'marca_produto',
        'UNID.' => 'unidade_produto',
        'QUANT.' => 'quantidade_do_produto',
        'P.UNITÁRIO' => 'preco_unitario',
        'P. TOTAL' => 'preco_total',
    ];

    echo "Mapeamento de Colunas:<br>";
    print_r($excelColumnMapping);
    echo "<br>";

    try {
        // Conexão com o banco de dados
        $pdo = new PDO('mysql:host=localhost;dbname=sys_public', 'root', 'leody2005');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Iterar pelas linhas do Excel e inserir no banco de dados
        $dataRows = $worksheet->toArray();
        array_shift($dataRows); // Remove a primeira linha (cabeçalho)

        // Dados adicionais
        $cnpjEmpresa = $_POST['cnpj-empresa'];
        $numContrato = $_POST['num-contrato'];
        $numLicitacao = $_POST['num-lic'];
        $nameEmpresa = $_POST['nome-empresa'];
        $objetoEmpresa = $_POST['objeto-empresa'];
        $tipoObjeto = $_POST['tipo_objeto'];
        $modalidadeLicitacao = $_POST['modalidade'];
        $tipoLicitacao = $_POST['tipo_licitacao'];

        // Inicializar o número da linha
        $lineNumber = 1;

        foreach ($dataRows as $rowData) {
            $rowDataMapped = [];
            foreach ($excelColumnMapping as $excelColumn => $dbColumn) {
                $index = array_search($excelColumn, $firstRowData);
                $rowDataMapped[$dbColumn] = $rowData[$index];
            }
        
            // Formatando os valores de preco_unitario e preco_total
            $rowDataMapped['preco_unitario'] = str_replace(',', '.', $rowDataMapped['preco_unitario']); // Substitui a vírgula pelo ponto
        
            // Convertendo para float
            $rowDataMapped['preco_unitario'] = floatval($rowDataMapped['preco_unitario']);
        
            // Calculando o preço total
            $rowDataMapped['preco_total'] = $rowDataMapped['preco_unitario'] * $rowDataMapped['quantidade_do_produto'];
        
            // Gerar ID aleatório
            $idGerado = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        
            // Preparar inserção de dados no banco
            $stmt = $pdo->prepare("INSERT INTO estoque (id_produto, numero_licitacao, objeto_tipo, modalidade_tipo, tipo_licitacao, cnpj_empresa, numero_contrato, objeto_licitacao, nome_empresa, nome_do_produto, unidade_produto, quantidade_do_produto, marca_produto, preco_unitario, preco_total, linha_da_tabela, data_da_lic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
            // Importar dados
            $stmt->execute([$idGerado, $numLicitacao, $tipoObjeto, $modalidadeLicitacao, $tipoLicitacao, $cnpjEmpresa, $numContrato, $objetoEmpresa, $nameEmpresa, $rowDataMapped['nome_do_produto'], $rowDataMapped['unidade_produto'], $rowDataMapped['quantidade_do_produto'], $rowDataMapped['marca_produto'], $rowDataMapped['preco_unitario'], $rowDataMapped['preco_total'], $lineNumber]);
            
        
            // Incrementar o número da linha
            $lineNumber++;
        }
        
        // Redirecionar após o loop
        header("Location: inserir_produtos.php");
        exit();
        
    } catch (PDOException $e) {
        echo "Erro de PDO: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "Nenhum arquivo encontrado.";
}
?>
