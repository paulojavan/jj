<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parcela;
use App\Models\Cliente;
use Carbon\Carbon;

class MensagemAvisoController extends Controller
{
    public function index()
    {
        // Obter a data atual
        $dataAtual = Carbon::now();
        
        // Encontrar todas as parcelas vencidas há pelo menos 7 dias com status "aguardando pagamento"
        $dataLimite = $dataAtual->copy()->subDays(7);
        $parcelasVencidas = Parcela::where('status', 'aguardando pagamento')
            ->where('data_vencimento', '<=', $dataLimite->toDateString())
            ->get();
        
        // Criar um array para armazenar os IDs dos clientes sem duplicatas
        $idsClientes = [];
        foreach ($parcelasVencidas as $parcela) {
            if (!in_array($parcela->id_cliente, $idsClientes)) {
                $idsClientes[] = $parcela->id_cliente;
            }
        }
        
        // Obter os clientes cujo campo cobranca é null ou tem mês/ano diferentes do atual
        $clientes = cliente::whereIn('id', $idsClientes)
            ->where(function ($query) use ($dataAtual) {
                $query->whereNull('cobranca')
                      ->orWhereRaw('YEAR(cobranca) != ? OR MONTH(cobranca) != ?', [
                          $dataAtual->year,
                          $dataAtual->month
                      ]);
            })
            ->get();
        
        return view('mensagens-aviso.index', compact('clientes'));
    }
    
    public function enviarMensagem(Request $request, $id)
    {
        // Atualizar o campo cobranca para a data atual
        $cliente = cliente::findOrFail($id);
        $cliente->cobranca = Carbon::now();
        $cliente->save();
        
        // Extrair os dois primeiros nomes do cliente
        $nomes = explode(' ', $cliente->nome);
        $primeirosNomes = implode(' ', array_slice($nomes, 0, 2));
        
        // Formatar o número de telefone (remover caracteres especiais)
        $telefone = preg_replace('/\D/', '', $cliente->telefone);
        
        // Montar a mensagem
        $mensagem = "Essa é uma mensagem automática da loja Joécio calçados.\n";
        $mensagem .= "Olá *{$primeirosNomes}*, estamos passando apenas para lembrar que suas parcelas venceram recentemente.\n";
        $mensagem .= "Fique atento as datas de vencimento e evite juros em suas parcelas.\n";
        $mensagem .= "Voce pode acompanhar suas parcelas através do link:\n";
        $mensagem .= "https://joeciocalçados.com.br/acompanharParcelas.php";
        
        // Codificar a mensagem para URL
        $mensagemCodificada = urlencode($mensagem);
        
        // Retornar uma resposta que abre o link em uma nova aba
        return response()->json([
            'url' => "https://wa.me/55{$telefone}?text={$mensagemCodificada}"
        ]);
    }
}