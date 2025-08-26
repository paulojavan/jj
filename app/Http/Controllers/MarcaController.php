<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marcas = Marca::orderBy('marca', 'asc')->paginate(20);
        return view('marca.index', ['marcas' => $marcas]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('marca.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'marca' => 'required|string|max:255',
        ]);

        Marca::create([
            'marca' => $request->marca,
            'desconto' => 0,
        ]);

        return redirect()->route('marcas.index')->with('success', 'Marca cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        return view('marca.edit', compact('marca'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Marca $marca) {
        $request->validate([
            'marca' => 'required|string|max:255',
        ]);

        $marca->update([
            'marca' => $request->marca,
        ]);

        return redirect()->route('marcas.index')->with('success', 'Marca atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca) {
        $marca->delete();
        return redirect()->route('marcas.index')->with('success', 'Marca excluída com sucesso!');
    }
}
