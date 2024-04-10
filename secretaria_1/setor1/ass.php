<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assinatura</title>
<!-- Inclua a biblioteca signature_pad -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<style>
    #signature-pad {
        border: 1px solid #000;
        width: 300px;
        height: 200px;
    }
</style>
</head>
<body>
<h2>Assinatura</h2>
<!-- Crie um canvas onde a assinatura será desenhada -->
<canvas id="signature-pad"></canvas>
<br>
<button id="clear-button">Limpar</button>
<button id="save-button">Salvar</button>
<!-- Inclua o script JavaScript -->
<script>
    // Selecione o canvas e configure o contexto
    var canvas = document.getElementById('signature-pad');
    var ctx = canvas.getContext('2d');

    // Configure o tamanho do canvas para corresponder ao tamanho do contêiner
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;

    // Inicialize a biblioteca SignaturePad
    var signaturePad = new SignaturePad(canvas, {
        // Reduza o atraso (delay)
        throttle: 10,
        // Ajuste a sensibilidade da caneta
        minDistance: 0
    });

    // Limpar a assinatura
    document.getElementById('clear-button').addEventListener('click', function() {
        signaturePad.clear();
    });

    // Salvar a assinatura
    document.getElementById('save-button').addEventListener('click', function() {
        // Verifica se há uma assinatura
        if (signaturePad.isEmpty()) {
            alert('Por favor, forneça uma assinatura primeiro.');
        } else {
            // Converte a assinatura para uma imagem base64
            var dataURL = signaturePad.toDataURL();
            // Envie a assinatura para o servidor ou faça qualquer outra operação desejada
            console.log(dataURL); // Aqui você pode enviar a assinatura para o servidor via AJAX, por exemplo
            // Ou você pode armazenar a assinatura localmente ou fazer qualquer outra coisa com ela
        }
    });
</script>
</body>
</html>
