<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Autorizado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Exception;
use Illuminate\Support\Str;

class AutorizadoController extends Controller
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
    public function create(Request $request)
    {
        $cliente_id = $request->route('cliente_id');
        return view('autorizado.create', compact('cliente_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required',
                'rg' => 'required',
                'cpf' => 'required',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $cliente = Cliente::findOrFail($request->cliente_id);
            $pasta = $cliente->pasta ?? $cliente->cpf;

            $file_name = 'foto_autorizado_' . time() . '.' . $request->file('foto')->extension();
            $path = "uploads/clientes/{$pasta}/{$file_name}";

            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('foto'));
            $image->resize(562, 1000);
            Storage::disk('public')->put($path, $image->toJpeg(90));

            Autorizado::create([
                'idCliente' => $request->cliente_id,
                'nome' => $request->nome,
                'rg' => $request->rg,
                'cpf' => $request->cpf,
                'foto' => $file_name,
                'pasta' => $pasta,
            ]);

            return redirect()->route('clientes.edit', ['cliente' => $request->cliente_id])
                            ->with('success', 'Autorizado cadastrado com sucesso!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erro ao cadastrar autorizado: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Autorizado $autorizado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Autorizado $autorizado)
    {
        return view('autorizado.edit', compact('autorizado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Autorizado $autorizado)
    {
        try {
            $request->validate([
                'nome' => 'required',
                'rg' => 'required',
                'cpf' => 'required',
            ]);

            $cliente = Cliente::findOrFail($autorizado->idCliente);
            $pasta = $cliente->pasta ?? $cliente->cpf;

            $autorizado->update([
                'nome' => $request->nome,
                'rg' => $request->rg,
                'cpf' => $request->cpf,
                'pasta' => $pasta,
            ]);

            return redirect()->back()->with('success', 'Informações atualizadas com sucesso!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erro ao atualizar informações: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Autorizado $autorizado)
    {
        //
    }

    public function upload_documentos_autorizado(Request $request, Autorizado $id)
    {
        try {
            $request->validate([
                'id' => 'required',
                'documento' => 'required|in:foto,rg_frente,rg_verso,cpf_foto',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $autorizado = Autorizado::findOrFail($request->input('id'));
            $cliente = Cliente::findOrFail($autorizado->idCliente);
            $pasta = $cliente->pasta ?? $cliente->cpf;

            if (is_null($autorizado->pasta)) {
                $autorizado->pasta = $pasta;
                $autorizado->save();
            }

            $documento = $request->input('documento');
            $file_name = "{$documento}_autorizado_" . time() . '.' . $request->file('foto')->extension();
            $path = "uploads/clientes/{$pasta}/{$file_name}";

            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('foto'));
            $width = $image->width();
            $height = $image->height();

            if ($width > $height) {
                $image->resize(1000, 562);
            } else {
                $image->resize(562, 1000);
            }

            Storage::disk('public')->put($path, $image->toJpeg(90));

            $autorizado->update([
                $documento => $file_name,
            ]);

            return back()->with('success', 'Documento enviado com sucesso!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erro ao enviar documento: ' . $e->getMessage());
        }
    }

    public function alterar_foto_autorizado(Request $request, Autorizado $id)
    {
        try {
            $request->validate([
                'id' => 'required',
                'documento2' => 'required|in:foto,rg_frente,rg_verso,cpf_foto',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $autorizado = Autorizado::findOrFail($request->input('id'));
            $cliente = Cliente::findOrFail($autorizado->idCliente);
            $pasta = $cliente->pasta ?? $cliente->cpf;

            if (is_null($autorizado->pasta)) {
                $autorizado->pasta = $pasta;
                $autorizado->save();
            }

            $campo = $request->input('documento2');
            $oldImagePath = null;
            switch ($campo) {
                case 'foto':
                    $oldImagePath = $autorizado->foto ? "uploads/clientes/{$pasta}/{$autorizado->foto}" : null;
                    break;
                case 'rg_frente':
                    $oldImagePath = $autorizado->rg_frente ? "uploads/clientes/{$pasta}/{$autorizado->rg_frente}" : null;
                    break;
                case 'rg_verso':
                    $oldImagePath = $autorizado->rg_verso ? "uploads/clientes/{$pasta}/{$autorizado->rg_verso}" : null;
                    break;
                case 'cpf_foto':
                    $oldImagePath = $autorizado->cpf_foto ? "uploads/clientes/{$pasta}/{$autorizado->cpf_foto}" : null;
                    break;
                default:
                    return back()->with('error', 'Campo de imagem inválido.');
            }

            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            $file_name = "{$campo}_autorizado_" . time() . '.' . $request->file('image')->extension();
            $path = "uploads/clientes/{$pasta}/{$file_name}";

            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('image'));
            $width = $image->width();
            $height = $image->height();

            if ($width > $height) {
                $image->resize(1000, 562);
            } else {
                $image->resize(562, 1000);
            }

            Storage::disk('public')->put($path, $image->toJpeg(90));

            $autorizado->update([
                $campo => $file_name,
            ]);

            return back()->with('success', 'Imagem atualizada com sucesso!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erro ao atualizar imagem: ' . $e->getMessage());
        }
    }
}
