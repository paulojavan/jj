<?php

namespace App\Http\Controllers;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\User;
use App\Models\Cidade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FuncionarioController extends Controller
{

    public function index($status = 'ativo')
    {
        if($status != 'ativo')
        {
            $funcionarios = User::orderBy('name', 'asc')->paginate(10);
        }else{
            $funcionarios = User::where('status', 'ativo')->orderBy('name', 'asc')->paginate(10);
        }


        return view('funcionario.index', ['funcionarios' => $funcionarios]);
    }


    public function cadastro()
    {
        $cidades = Cidade::where('status', 'ativa')->orderBy('cidade', 'asc')->get(); // Obtém todas as cidades com status
        return view('funcionario.cadastro', ['cidades' => $cidades]);
    }

    public function store(Request $request)
    {
        try{


            $file_name = time().'.'.$request->image->extension();
            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('image'));
            // Verifica a orientação da imagem
            $width = $image->width();
            $height = $image->height();

            if ($width > $height) {
                // Imagem na horizontal
                $image->resize(1000, 562);
            } else {
                // Imagem na vertical
                $image->resize(562, 1000);
            }
            $path = 'uploads/funcionarios/'.$file_name;
            Storage::disk('public')->put($path, $image->toJpeg(90));


        User::create([
            'name' => $request->name,
            'login' => $request->login,
            'password' => $request->password,
            'cidade' => $request->cidade,
            'status' => "ativo",
            'image' => $file_name,
            'cadastro_produtos' => $request->has('cadastro_produtos') ? 1 : 0,
            'ajuste_estoque' => $request->has('ajuste_estoque') ? 1 : 0,
            'vendas_crediario' => $request->has('vendas_crediario') ? 1 : 0,
            'limite' => $request->has('limite') ? 1 : 0,
            'recebimentos' => $request->has('recebimentos') ? 1 : 0,
        ]);

        return redirect()->route('funcionario.cadastro')->with('success', 'Funcionário cadastrado com sucesso!');
        }catch( Exception $e){
        return back()->withInput()->with('error', 'Erro ao cadastrar Funcionário!');
        }
    }

    public function edit(User $id)
    {
        $cidades = Cidade::where('status', 'ativa')->OrderBY('cidade', 'asc')->get();
        return view('funcionario.edit', ['funcionario' => $id, 'cidades' => $cidades]);
    }

    public function update(Request $request, User $id)
    {
        try{

        // Check if a new image was uploaded
        if($request->hasFile('image')) {
            // Get the old image path to delete it later
            $oldImagePath = $id->image;

            // Process and upload the new image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($request->file('image'));

            // Verifica a orientação da imagem
            $width = $image->width();
            $height = $image->height();

            if ($width > $height) {
                // Imagem na horizontal
                $image->resize(1000, 562);
            } else {
                // Imagem na vertical
                $image->resize(562, 1000);
            }

            $file_name = time().'.'.$request->image->extension();
            $path = 'uploads/funcionarios/'.$file_name;
            Storage::disk('public')->put($path, $image->toJpeg(90));

            // Delete the old image if it exists using Storage facade
            if($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        } else {
            // Keep the existing image path
            $file_name = $id->image;
        }

        $id->update([
            'name' => $request->name,
            'login' => $request->login,
            'password' => $request->password,
            'cidade' => $request->cidade,
            'status' => $request->status,
            'image' => $file_name,
            'cadastro_produtos' => $request->has('cadastro_produtos') ? 1 : 0,
            'ajuste_estoque' => $request->has('ajuste_estoque') ? 1 : 0,
            'vendas_crediario' => $request->has('vendas_crediario') ? 1 : 0,
            'limite' => $request->has('limite') ? 1 : 0,
            'recebimentos' => $request->has('recebimentos') ? 1 : 0,
        ]);

        }catch( Exception $e){
            return back()->withInput()->with('error', 'Erro ao atualizar Funcionário!'.$e->getMessage());
        }
            return redirect()->route('funcionario.edit', ['id' => $id->id])->with('success', 'Funcionário atualizado com sucesso!');
        }
}
