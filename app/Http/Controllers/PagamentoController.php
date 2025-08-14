<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Parcela;
use App\Models\MultaConfiguracao;
use App\Models\Pagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PagamentoController extends Controller
{
    public function show(Cliente $cliente)
    {
        $multaConfig = MultaConfiguracao::first();

        $parcelasTitular = Parcela::where('id_cliente', $cliente->id)
            ->whereNull('id_autorizado')
            ->where('status', 'aguardando pagamento')
            ->orderBy('data_vencimento')
            ->get();

        $parcelasAutorizados = Parcela::where('id_cliente', $cliente->id)
            ->whereNotNull('id_autorizado')
            ->where('status', 'aguardando pagamento')
            ->with('autorizado') // Carrega o relacionamento com o autorizado
            ->orderBy('data_vencimento')
            ->get()
            ->groupBy('id_autorizado');

        $today = Carbon::today();

        $calcularValorAPagar = function (Parcela $parcela) use ($multaConfig, $today) {
            $dataVencimento = Carbon::parse($parcela->data_vencimento);
            $diffDias = $today->diffInDays($dataVencimento, false);

            $diasAtraso = $diffDias < 0 ? abs($diffDias) : 0;
            $valorAPagar = $parcela->valor_parcela;

            if ($diasAtraso > $multaConfig->dias_carencia) {
                $taxaJurosDiaria = $multaConfig->taxa_juros / 30;
                $diasParaJuros = min($diasAtraso, $multaConfig->dias_cobranca);
                $valorJuros = ($parcela->valor_parcela * ($taxaJurosDiaria / 100)) * $diasParaJuros;
                $valorMulta = $parcela->valor_parcela * ($multaConfig->taxa_multa / 100);
                $valorAPagar += $valorMulta + $valorJuros;
            }

            return (object)['valor' => round($valorAPagar, 2), 'dias_atraso' => $diasAtraso];
        };

        // Arrays para armazenar os valores calculados
        $valoresParcelasTitular = [];
        $valoresParcelasAutorizados = [];

        $parcelasTitular->each(function ($parcela) use ($calcularValorAPagar, &$valoresParcelasTitular) {
            $calculo = $calcularValorAPagar($parcela);
            $valoresParcelasTitular[$parcela->id_parcelas] = $calculo->valor;
            $parcela->valor_a_pagar = $calculo->valor;
            $parcela->dias_atraso = $calculo->dias_atraso;
        });

        foreach ($parcelasAutorizados as $autorizadoId => $parcelasDoAutorizado) {
            foreach ($parcelasDoAutorizado as $parcela) {
                $calculo = $calcularValorAPagar($parcela);
                $valoresParcelasAutorizados[$parcela->id_parcelas] = $calculo->valor;
                $parcela->valor_a_pagar = $calculo->valor;
                $parcela->dias_atraso = $calculo->dias_atraso;
            }
        }

        return view('pagamentos.show', compact('cliente', 'parcelasTitular', 'parcelasAutorizados'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        // Validar os dados da requisição
        $request->validate([
            'parcelas' => 'required|array|min:1',
            'parcelas.*' => 'exists:parcelas,id_parcelas',
            'dinheiro' => 'nullable|string',
            'pix' => 'nullable|string',
            'cartao' => 'nullable|string',
        ]);

        // Converter valores monetários para formato numérico
        $dinheiro = $this->parseCurrency($request->input('dinheiro', '0'));
        $pix = $this->parseCurrency($request->input('pix', '0'));
        $cartao = $this->parseCurrency($request->input('cartao', '0'));

        // Verificar se os valores estão corretos
        $totalPago = $dinheiro + $pix + $cartao;
        
        // Obter as parcelas selecionadas
        $parcelasIds = $request->input('parcelas');
        $parcelas = Parcela::whereIn('id_parcelas', $parcelasIds)->get();
        
        // Obter configuração de multa
        $multaConfig = MultaConfiguracao::first();
        $today = Carbon::today();
        
        // Função para calcular valor a pagar
        $calcularValorAPagar = function (Parcela $parcela) use ($multaConfig, $today) {
            $dataVencimento = Carbon::parse($parcela->data_vencimento);
            $diffDias = $today->diffInDays($dataVencimento, false);

            $diasAtraso = $diffDias < 0 ? abs($diffDias) : 0;
            $valorAPagar = $parcela->valor_parcela;

            if ($diasAtraso > $multaConfig->dias_carencia) {
                $taxaJurosDiaria = $multaConfig->taxa_juros / 30;
                $diasParaJuros = min($diasAtraso, $multaConfig->dias_cobranca);
                $valorJuros = ($parcela->valor_parcela * ($taxaJurosDiaria / 100)) * $diasParaJuros;
                $valorMulta = $parcela->valor_parcela * ($multaConfig->taxa_multa / 100);
                $valorAPagar += $valorMulta + $valorJuros;
            }

            return round($valorAPagar, 2);
        };
        
        // Calcular o total das parcelas selecionadas
        $totalParcelas = 0;
        $valoresParcelas = []; // Array para armazenar os valores calculados
        foreach ($parcelas as $parcela) {
            $valorAPagar = $calcularValorAPagar($parcela);
            $valoresParcelas[$parcela->id_parcelas] = $valorAPagar;
            $totalParcelas += $valorAPagar;
        }
        
        // Verificar se o total pago corresponde ao total das parcelas
        $diferenca = abs($totalPago - $totalParcelas);
        if ($diferenca > 0.01) {
            // Mensagem de depuração mais detalhada
            $debugInfo = [
                'dinheiro' => $dinheiro,
                'pix' => $pix,
                'cartao' => $cartao,
                'totalPago' => $totalPago,
                'totalParcelas' => $totalParcelas,
                'diferenca' => $diferenca,
                'parcelasIds' => $parcelasIds,
                'quantidadeParcelas' => count($parcelas)
            ];
            
            \Log::info('Erro de valores no pagamento', $debugInfo);
            
            return redirect()->back()->with('error', 'Valores não correspondem ao total das parcelas selecionadas. Total das parcelas com juros/multa: R$ ' . number_format($totalParcelas, 2, ',', '.') . ' | Total informado: R$ ' . number_format($totalPago, 2, ',', '.') . ' | Diferença: R$ ' . number_format($diferenca, 2, ',', '.'));
        }

        // Gerar token único
        $ticket = Str::uuid()->toString();
        
        // Obter data e hora atuais
        $dataHora = Carbon::now();
        $data = $dataHora; // Agora passamos o objeto Carbon completo com data e hora

        // Armazenar na tabela pagamentos
        $pagamento = Pagamento::create([
            'id_cliente' => $cliente->id,
            'ticket' => $ticket,
            'data' => $data, // Passa o objeto Carbon completo
        ]);

        // Obter ID do vendedor logado
        $idVendedor = Auth::id();

        // Determinar método de pagamento
        $metodo = 'dinheiro';
        if ($pix > 0) {
            $metodo = 'pix';
        } elseif ($cartao > 0) {
            $metodo = 'cartao';
        }

        // Atualizar cada parcela selecionada
        foreach ($parcelas as $parcela) {
            // Obter o valor calculado para esta parcela
            $valorParcela = $valoresParcelas[$parcela->id_parcelas];
            
            // Para uma parcela única, os valores são exatamente os informados
            if (count($parcelas) == 1) {
                $dinheiroParcela = $dinheiro;
                $pixParcela = $pix;
                $cartaoParcela = $cartao;
            } else {
                // Calcular proporção desta parcela em relação ao total
                $proporcao = $totalParcelas > 0 ? $valorParcela / $totalParcelas : 0;
                
                // Calcular valores de cada método de pagamento para esta parcela
                $dinheiroParcela = round($dinheiro * $proporcao, 2);
                $pixParcela = round($pix * $proporcao, 2);
                $cartaoParcela = round($cartao * $proporcao, 2);
            }

            // Atualizar parcela (sem tentar salvar valor_a_pagar)
            $parcela->update([
                'ticket_pagamento' => $ticket,
                'data_pagamento' => $data,
                'hora' => $dataHora->toTimeString(), // Usar a hora do objeto Carbon
                'valor_pago' => $valorParcela,
                'dinheiro' => $dinheiroParcela,
                'pix' => $pixParcela,
                'cartao' => $cartaoParcela,
                'metodo' => $metodo,
                'id_vendedor' => $idVendedor,
                'status' => 'pago'
            ]);
        }

        return redirect()->back()->with('success', 'Pagamento realizado com sucesso! ' . count($parcelas) . ' parcela(s) foram pagas.');
    }

    /**
     * Converte valor monetário em formato brasileiro para float
     */
    private function parseCurrency($value)
    {
        if (!$value || $value === '') return 0;
        
        // Remove espaços em branco
        $value = trim($value);
        
        // Remove pontos e substitui vírgula por ponto
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        
        return floatval($value);
    }
}
