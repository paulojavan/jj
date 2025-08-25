<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Parcela;
use App\Models\Autorizado;
use App\Models\MultaConfiguracao;
use App\Services\ParcelaCalculoService;
use App\Http\Requests\ConsultaParcelaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ParcelaController extends Controller
{
    private ParcelaCalculoService $calculoService;

    public function __construct(ParcelaCalculoService $calculoService)
    {
        $this->calculoService = $calculoService;
    }

    /**
     * Exibe a página de consulta por CPF
     */
    public function index()
    {
        return view('parcelas.consulta');
    }

    /**
     * Processa a consulta por CPF e exibe as parcelas
     */
    public function consultar(ConsultaParcelaRequest $request)
    {
        try {
            $cpf = $request->validated()['cpf'];

            // Busca o cliente pelo CPF (mantendo a formatação da máscara)
            $cliente = cliente::where('cpf', $cpf)->first();

            if (!$cliente) {
                return redirect()->route('parcelas.index')
                    ->with('error', 'Cliente não encontrado com este CPF');
            }

            // Busca as parcelas pendentes do cliente
            $parcelasData = $this->buscarParcelasPendentes($cliente->id);

            if (empty($parcelasData['titular']) && empty($parcelasData['autorizados'])) {
                return view('parcelas.resultado', [
                    'cliente' => $cliente,
                    'parcelasData' => $parcelasData,
                    'mensagem' => 'Nenhuma parcela pendente encontrada'
                ]);
            }

            return view('parcelas.resultado', [
                'cliente' => $cliente,
                'parcelasData' => $parcelasData
            ]);

        } catch (Exception $e) {
            Log::error('Erro na consulta de parcelas: ' . $e->getMessage());

            return redirect()->route('parcelas.index')
                ->with('error', 'Erro interno do sistema. Tente novamente.');
        }
    }

    /**
     * Busca as parcelas pendentes do cliente, separando titular e autorizados
     */
    private function buscarParcelasPendentes(int $clienteId): array
    {
        try {
            // Busca configuração de multa
            $config = MultaConfiguracao::getConfiguracao();

            // Busca todas as parcelas pendentes do cliente
            $parcelas = Parcela::where('id_cliente', $clienteId)
                ->where('status', 'aguardando pagamento')
                ->orderBy('data_vencimento', 'asc')
                ->get();

            $parcelasData = [
                'titular' => [],
                'autorizados' => []
            ];

            foreach ($parcelas as $parcela) {
                $parcelaProcessada = $this->processarParcela($parcela, $config);

                if (is_null($parcela->id_autorizado)) {
                    // Parcela do titular
                    $parcelasData['titular'][] = $parcelaProcessada;
                } else {
                    // Parcela de autorizado
                    $autorizado = Autorizado::find($parcela->id_autorizado);
                    $nomeAutorizado = $autorizado ? $autorizado->nome : 'Autorizado não encontrado';

                    if (!isset($parcelasData['autorizados'][$parcela->id_autorizado])) {
                        $parcelasData['autorizados'][$parcela->id_autorizado] = [
                            'nome' => $nomeAutorizado,
                            'parcelas' => []
                        ];
                    }

                    $parcelasData['autorizados'][$parcela->id_autorizado]['parcelas'][] = $parcelaProcessada;
                }
            }

            return $parcelasData;

        } catch (Exception $e) {
            Log::error('Erro ao buscar parcelas pendentes: ' . $e->getMessage());
            return ['titular' => [], 'autorizados' => []];
        }
    }

    /**
     * Processa uma parcela individual, calculando valores e dias de atraso
     */
    private function processarParcela(Parcela $parcela, MultaConfiguracao $config): array
    {
        $diasAtraso = $this->calculoService->calcularDiasAtraso($parcela->data_vencimento);
        $valorAPagar = $this->calculoService->calcularValorAPagar($parcela, $config);

        return [
            'id' => $parcela->id_parcelas,
            'ticket' => $parcela->ticket,
            'numero' => $parcela->numero,
            'valor_parcela' => $parcela->valor_parcela,
            'valor_parcela_formatado' => $this->calculoService->formatarValor($parcela->valor_parcela),
            'data_vencimento' => $parcela->data_vencimento->format('d/m/Y'),
            'dias_atraso' => $diasAtraso,
            'valor_a_pagar' => $valorAPagar,
            'valor_a_pagar_formatado' => $this->calculoService->formatarValor($valorAPagar),
            'tem_multa_juros' => $valorAPagar > $parcela->valor_parcela
        ];
    }

    /**
     * Formata CPF removendo caracteres especiais
     */
    private function formatarCpf(string $cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }

    /**
     * Valida formato do CPF
     */
    private function validarFormatoCpf(string $cpf): bool
    {
        // Remove caracteres especiais
        $cpfLimpo = $this->formatarCpf($cpf);

        // Verifica se tem 11 dígitos
        if (strlen($cpfLimpo) !== 11) {
            return false;
        }

        // Verifica se não são todos os dígitos iguais
        if (preg_match('/(\d)\1{10}/', $cpfLimpo)) {
            return false;
        }

        return true;
    }
}
