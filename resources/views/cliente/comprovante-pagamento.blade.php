<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento - {{ $cliente->nome }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .sacado-info {
            text-align: right;
            flex: 1;
        }
        
        .sacado-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .content-wrapper {
            display: flex;
            gap: 40px;
            margin-top: 30px;
        }
        
        .left-column {
            flex: 1;
        }
        
        .right-column {
            flex: 1;
        }
        
        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .parcelas-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .parcela-item {
            margin-bottom: 8px;
            font-size: 13px;
            line-height: 1.3;
        }
        
        .parcela-id {
            font-weight: bold;
        }
        
        .info-item {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .info-label {
            font-weight: bold;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .codigo-pagamento {
            font-family: monospace;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
    </button>

    <div class="header">
        <div class="company-info">
            <div class="company-name">Joécio Calçados</div>
        </div>
        <div class="sacado-info">
            <div class="sacado-title">Sacado:</div>
            <div>{{ strtoupper($cliente->nome) }}</div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="left-column">
            <div class="section-title">Parcela(s) paga(s):</div>
            <ul class="parcelas-list">
                @foreach ($pagamento->parcelas as $parcela)
                    <li class="parcela-item">
                        <span class="parcela-id">Identificação da parcela:</span> {{ $parcela->id_parcelas }} | 
                        <strong>Nº da parcela:</strong> {{ $parcela->numero }}| 
                        <strong>Valor pago:</strong> R$ {{ number_format($parcela->valor_pago, 2, ',', '.') }}
                    </li>
                @endforeach
            </ul>
        </div>
        
        <div class="right-column">
            <div class="info-item">
                <span class="info-label">Código do pagamento:</span><br>
                <span class="codigo-pagamento">{{ $pagamento->ticket }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Data do pagamento:</span><br>
                {{ $pagamento->data->format('d/m/Y H:i') }}
            </div>
            
            <div class="info-item">
                <span class="info-label">Total pago:</span><br>
                <strong>R$ {{ number_format($pagamento->parcelas->sum('valor_pago'), 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog when page loads
        window.onload = function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>