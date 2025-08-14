<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duplicata - {{ $ticketData->ticket }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            
            .duplicata {
                width: 100%;
                height: 100vh;
                page-break-inside: avoid;
            }
        }
        
        .duplicata {
            width: 210mm;
            height: 148mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
            border: 1px solid #ddd;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        
        .subtitle {
            font-size: 12px;
            font-style: italic;
            color: #666;
        }
        
        .company-info {
            flex: 2;
            text-align: center;
            padding: 0 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .company-details {
            font-size: 10px;
            color: #666;
        }
        
        .duplicata-info {
            flex: 1;
            text-align: right;
            border: 1px solid #333;
            padding: 10px;
            background: #f9f9f9;
        }
        
        .duplicata-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .info-box {
            border: 1px solid #333;
            padding: 5px;
            background: #f9f9f9;
        }
        
        .info-label {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .info-value {
            font-weight: bold;
            font-size: 11px;
        }
        
        .client-info {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #333;
        }
        
        .client-info h3 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .client-details {
            font-size: 11px;
            line-height: 1.6;
        }
        
        .terms {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #333;
            background: #f9f9f9;
            font-size: 10px;
            line-height: 1.5;
            text-align: justify;
        }
        
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #333;
            padding-top: 15px;
            font-size: 11px;
        }
        
        .signature-section {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 30px auto 5px;
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

    <div class="duplicata">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">Joécio</div>
                <div class="subtitle">Calçados</div>
            </div>
            
            <div class="company-info">
                <div class="company-name">JJ de B Pessoa ME</div>
                <div class="company-details">
                    Cnpj: 18.836.623/0001-64<br>
                    Endereço: Rua rosa xavier, 51<br>
                    Tabira - Pernambuco
                </div>
            </div>
            
            <div class="duplicata-info">
                <div class="duplicata-title">Duplicata</div>
                <div style="font-size: 10px;">
                    Data de emissão:<br>
                    <strong>{{ now()->format('d-m-Y') }}</strong><br><br>
                    Data da compra:<br>
                    <strong>{{ $ticketData->data->format('d-m-Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Informações principais -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label">Valor da compra</div>
                <div class="info-value">{{ number_format($ticketData->valor, 2, ',', '.') }}</div>
            </div>
            
            <div class="info-box">
                <div class="info-label">Número da duplicata:</div>
                <div class="info-value">{{ $ticketData->id_ticket }}</div>
            </div>
            
            <div class="info-box">
                <div class="info-label">Vencimento da primeira parcela:</div>
                <div class="info-value">{{ $ticketData->parcelasRelacao->first()->vencimento_formatado ?? 'N/A' }}</div>
            </div>
            
            <div class="info-box">
                <div class="info-label">Quantidade de parcelas:</div>
                <div class="info-value">{{ $ticketData->parcelas }}</div>
            </div>
        </div>

        <!-- Dados do Cliente -->
        <div class="client-info">
            <h3>Cliente: {{ strtoupper($cliente->nome) }}</h3>
            <div class="client-details">
                <strong>Endereço:</strong> {{ strtoupper($cliente->rua) }}, {{ $cliente->numero }}<br>
                <strong>Número:</strong> {{ $cliente->numero }}, {{ strtoupper($cliente->bairro) }}, {{ strtoupper($cliente->cidade) }}
            </div>
        </div>

        <!-- Termos e Condições -->
        <div class="terms">
            Reconheço e confesso que possuo o débito de <strong>R$ {{ number_format($ticketData->valor, 2, ',', '.') }}</strong> e que pagarei a Joecio Calçados - Jabira ou a quem esta estabelecer nas datas acordadas. Aceito que o não pagamento das parcelas no vencimento gerará multa e juros sobre o débito, e serão efetuados procedimentos de cobrança, negativação e protesto. Autorizo se necessário contato telefônico, e-mail e sms. Confirmo a veracidade das informações e que resido no endereço acima.
            <br><br>
            <strong>Data do aceite:</strong> {{ $ticketData->data->format('d-m-Y H:i') }}
        </div>

        <!-- Rodapé com assinaturas -->
        <div class="footer">
            <div class="signature-section">
                <div>{{ strtoupper($cliente->nome) }}</div>
                <div>{{ $cliente->cpf }}</div>
            </div>
            
            <div class="signature-section">
                <div>JJ de B Pessoa ME</div>
                <div>18.836.623/0001-64</div>
            </div>
        </div>
    </div>
</body>
</html>