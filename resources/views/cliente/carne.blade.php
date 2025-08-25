<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carnê de Pagamento - {{ $ticketData->ticket }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 15px;
            line-height: 1.3;
            color: #333;
            background: white;
        }
        
        .no-print {
            display: block;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .parcela {
                page-break-inside: avoid;
            }
        }
        
        .parcela {
            width: 210mm;
            height: 50mm;
            margin: 0 auto 5mm;
            padding: 3mm;
            background: white;
            border: 1px solid #333;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .parcela-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2mm;
        }
        
        .parcela-info {
            flex: 1;
        }
        
        .cedente-info {
            flex: 1;
            text-align: right;
        }
        
        .parcela-body {
            display: flex;
            justify-content: space-between;
            flex: 1;
        }
        
        .left-section {
            flex: 1;
            padding-right: 5mm;
        }
        
        .right-section {
            flex: 1;
            padding-left: 5mm;
            border-left: 1px solid #333;
        }
        
        .parcela-number {
            font-weight: bold;
            font-size: 15px;
        }
        
        .vencimento {
            font-size: 15px;
            margin-top: 1mm;
        }
        
        .warning {
            font-size: 20px;
            margin-top: 1mm;
            color: #666;
        }
        
        .cedente {
            font-weight: bold;
            font-size: 15px;
        }
        
        .cliente-info {
            font-size: 15px;
            margin-top: 1mm;
        }
        
        .cpf {
            font-size: 15px;
            color: #666;
        }
        
        .data-info {
            font-size: 15px;
            margin-top: 1mm;
        }
        
        .valor-info {
            font-size: 15px;
            margin-top: 1mm;
        }
        
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <!-- Controles (não aparecem na impressão) -->
    <div class="controls no-print">
        <button onclick="window.print()" class="btn">
            🖨️ Imprimir
        </button>
        <a href="{{ route('clientes.compra', [$cliente->id, $ticketData->ticket]) }}" class="btn btn-secondary">
            ← Voltar
        </a>
    </div>

    @foreach ($ticketData->parcelasRelacao as $parcela)
        <div class="parcela">
            <div class="parcela-header">
                <div class="parcela-info">
                    <div class="parcela-number">Parcela: {{ $parcela->numero }}</div>
                    <div class="vencimento">Vencimento: {{ $parcela->vencimento_formatado }}</div>
                    <div class="warning">Após o vencimento será cobrado juros e multa.</div>
                    <div class="cedente">Cedente: JJ de B Pessoa ME</div>
                </div>
                <div class="cedente-info">
                    <div class="cliente-info">Cliente: {{ strtoupper($cliente->nome) }}</div>
                    <div class="cpf">Cpf: {{ $cliente->cpf }}</div>
                    <div class="data-info">Data da emissão: {{ now()->format('d-m-Y') }}<br> Vencimento: {{ $parcela->vencimento_formatado }}</div>
                    <div class="valor-info">Valor da parcela: {{ number_format($parcela->valor_parcela, 2, ',', '.') }} <br>Parcela: {{ $parcela->numero }}</div>
                </div>
            </div>
            {{ $parcela->ticket }}
        </div>
    @endforeach
</body>
</html>