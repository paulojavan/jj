<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupos = Grupo::orderBy('grupo', 'asc')->paginate(20);
        return view('grupo.index', ['grupos' => $grupos]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('grupo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'grupo' => 'required|string|max:255',
        ]);

        Grupo::create([
            'grupo' => $request->grupo,
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Grupo $grupo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $grupo = Grupo::findOrFail($id);
        return view('grupo.edit', compact('grupo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grupo $grupo) {
        $request->validate([
            'grupo' => 'required|string|max:255',
        ]);

        $grupo->update([
            'grupo' => $request->grupo,
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grupo $grupo) {
        $grupo->delete();
        return redirect()->route('grupos.index')->with('success', 'Grupo excluído com sucesso!');
    }
}
