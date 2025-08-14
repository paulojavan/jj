<?php

namespace App\Http\Controllers;

use App\Models\MultaConfiguracao;
use Illuminate\Http\Request;

class MultaConfiguracaoController extends Controller
{

    /**
     * Show the form for editing the penalty configuration.
     */
    public function edit()
    {
        $multaConfiguracao = MultaConfiguracao::getConfiguracao();
        
        return view('multa-configuracao.edit', compact('multaConfiguracao'));
    }

    /**
     * Update the penalty configuration in storage.
     */
    public function update(Request $request)
    {
        $request->validate(
            MultaConfiguracao::validationRules(),
            MultaConfiguracao::validationMessages()
        );

        $multaConfiguracao = MultaConfiguracao::getConfiguracao();
        $multaConfiguracao->update($request->all());

        return redirect()
            ->route('multa-configuracao.edit')
            ->with('success', 'Configuração de multas e juros atualizada com sucesso!');
    }
}
