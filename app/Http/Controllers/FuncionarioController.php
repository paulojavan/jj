<?php

namespace App\Http\Controllers;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\User;
use App\Models\Cidade;
use App\Http\Requests\StoreFuncionarioRequest;
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

    public function store(StoreFuncionarioRequest $request)
    {
        // Validação já é feita pelo StoreFuncionarioRequest

        try {
            // Verificar se o arquivo de imagem foi enviado
            if (!$request->hasFile('image')) {
                return back()->withInput()->with('error', 'Nenhuma imagem foi enviada.');
            }

            // Processar a imagem
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

            // Criar o funcionário
            User::create([
                'name' => $request->name,
                'login' => $request->login,
                'password' => bcrypt($request->password), // Hash da senha
                'cidade' => $request->cidade,
                'nivel' => 'usuario', // Definir nível padrão
                'status' => 'ativo',
                'image' => $file_name,
                'cadastro_produtos' => $request->has('cadastro_produtos') ? 1 : 0,
                'ajuste_estoque' => $request->has('ajuste_estoque') ? 1 : 0,
                'vendas_crediario' => $request->has('vendas_crediario') ? 1 : 0,
                'limite' => $request->has('limite') ? 1 : 0,
                'recebimentos' => $request->has('recebimentos') ? 1 : 0,
            ]);

            return redirect()->route('funcionario.cadastro')->with('success', 'Funcionário cadastrado com sucesso!');
            
        } catch (Exception $e) {
            // Log do erro para debug
            \Log::error('Erro ao cadastrar funcionário: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao cadastrar funcionário: ' . $e->getMessage());
        }
    }

    public function edit(User $id)
    {
        $cidades = Cidade::where('status', 'ativa')->OrderBY('cidade', 'asc')->get();
        return view('funcionario.edit', ['funcionario' => $id, 'cidades' => $cidades]);
    }

    public function update(Request $request, User $id)
    {
        // Validação dos dados
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users,login,' . $id->id,
            'password' => 'nullable|string|min:6',
            'cidade' => 'required|string|max:255',
            'status' => 'required|in:ativo,inativo',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'login.required' => 'O login é obrigatório.',
            'login.unique' => 'Este login já está em uso.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'cidade.required' => 'A cidade é obrigatória.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser ativo ou inativo.',
            'image.image' => 'O arquivo deve ser uma imagem.',
            'image.mimes' => 'A imagem deve ser do tipo: jpeg, png, jpg ou gif.',
            'image.max' => 'A imagem não pode ser maior que 2MB.',
        ]);

        try {
            // Check if a new image was uploaded
            if($request->hasFile('image')) {
                // Get the old image path to delete it later
                $oldImagePath = 'uploads/funcionarios/' . $id->image;

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
                if($id->image && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            } else {
                // Keep the existing image path
                $file_name = $id->image;
            }

            // Preparar dados para atualização
            $updateData = [
                'name' => $request->name,
                'login' => $request->login,
                'cidade' => $request->cidade,
                'status' => $request->status,
                'image' => $file_name,
                'cadastro_produtos' => $request->has('cadastro_produtos') ? 1 : 0,
                'ajuste_estoque' => $request->has('ajuste_estoque') ? 1 : 0,
                'vendas_crediario' => $request->has('vendas_crediario') ? 1 : 0,
                'limite' => $request->has('limite') ? 1 : 0,
                'recebimentos' => $request->has('recebimentos') ? 1 : 0,
            ];

            // Só atualizar a senha se foi fornecida
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $id->update($updateData);

            return redirect()->route('funcionario.edit', ['id' => $id->id])->with('success', 'Funcionário atualizado com sucesso!');
            
        } catch (Exception $e) {
            \Log::error('Erro ao atualizar funcionário: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar funcionário: ' . $e->getMessage());
        }
    }
}
