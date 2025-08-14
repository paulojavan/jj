<?php

namespace App\Http\Controllers;

use App\Models\Desconto;
use Illuminate\Http\Request;

class DescontoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Desconto $desconto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Desconto $desconto)
    {
        return view('descontos.edit', compact('desconto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Desconto $desconto)
    {
        $request->validate([
            'avista' => 'required|numeric|min:0|max:100',
            'pix' => 'required|numeric|min:0|max:100',
            'debito' => 'required|numeric|min:0|max:100',
            'credito' => 'required|numeric|min:0|max:100',
        ]);

        $desconto->update($request->all());

        return redirect()->route('descontos.edit', $desconto)->with('success', 'Descontos atualizados com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Desconto $desconto)
    {
        //
    }
}
