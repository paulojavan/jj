<?php

namespace App\Http\Controllers;

use App\Models\Subgrupo;
use Illuminate\Http\Request;

class SubgrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subgrupos = Subgrupo::orderBy('subgrupo', 'asc')->paginate(20);
        return view('subgrupo.index', ['subgrupos' => $subgrupos]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subgrupo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subgrupo' => 'required|string|max:255',
        ]);

        Subgrupo::create([
            'subgrupo' => $request->subgrupo,
        ]);

        return redirect()->route('subgrupos.index')->with('success', 'Subgrupo cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subgrupo $subgrupo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $subgrupo = Subgrupo::findOrFail($id);
        return view('subgrupo.edit', compact('subgrupo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subgrupo $subgrupo) {
        $request->validate([
            'subgrupo' => 'required|string|max:255',
        ]);

        $subgrupo->update([
            'subgrupo' => $request->subgrupo,
        ]);

        return redirect()->route('subgrupos.index')->with('success', 'Subgrupo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subgrupo $subgrupo) {
        $subgrupo->delete();
        return redirect()->route('subgrupos.index')->with('success', 'Subgrupo excluído com sucesso!');
    }
}
