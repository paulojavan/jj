<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Adicionado para usar DB::raw
use Illuminate\Support\Facades\Hash; // Adicionado para Hash

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Supondo que você tenha uma view chamada 'auth.login'
        return view('login');
    }

    /**
     * Processa a tentativa de login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Tenta encontrar o usuário pelo login
        $user = User::where('login', $credentials['login'])->first();

        // Verifica se o usuário existe e a senha está correta (usando verificação simples, idealmente use Hash::check)

// ... (outro código)

        // Verifica se o usuário existe e a senha está correta
        if ($user && Hash::check($credentials['password'], $user->password)) {

            // Verifica se o usuário está ativo
            if ($user->status !== 'ativo') {
                return back()->withErrors([
                    'login' => 'Usuário inativo.',
                ])->onlyInput('login');
            }

            // Verifica se o usuário NÃO é administrador
            if ($user->nivel !== 'administrador') {
                $cidadeUsuario = $user->cidade;
                $diaSemanaAtual = strtolower(Carbon::now()->locale('pt_BR')->dayName); // Obtém o nome do dia da semana em português (ex: 'segunda-feira')
                $horaAtual = Carbon::now()->format('H:i:s');

                // Consulta o horário permitido para a cidade e dia atuais
                $horarioPermitido = Horario::where('cidade', $cidadeUsuario)
                                        ->where('dia', $diaSemanaAtual)
                                        ->where('inicio', '<=', $horaAtual)
                                        ->where('final', '>=', $horaAtual)
                                        ->exists(); // Verifica se existe algum registro que satisfaça as condições

                if (!$horarioPermitido) {

                    return redirect()->route('login')->with('horario_error', 'Acesso fora do horário permitido para sua cidade.')->onlyInput('login');
                }
            }

            // Autentica o usuário na sessão
            Auth::login($user);
            $request->session()->regenerate();

            // Redireciona para a rota 'welcome'
            // Certifique-se de que a rota 'welcome' está definida em routes/web.php
            return redirect()->route('dashboard'); // intended() tenta redirecionar para a URL anterior ou usa a rota 'welcome' como padrão

        }

        // Se a autenticação falhar
        return redirect()->route('login')->with('horario_error', 'As credenciais fornecidas não correspondem aos nossos registros.')->onlyInput('login');

    }

    /**
     * Faz logout do usuário.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redireciona para a página de login
        return redirect()->route('login');
    }

     /**
     * Exibe a página de boas-vindas.
     *
     * @return \Illuminate\View\View
     */
    public function welcome()
    {
        // Supondo que você tenha uma view chamada 'welcome'
        // Você pode passar dados do usuário autenticado para a view se necessário
        // $user = Auth::user();
        return view('welcome'); // Certifique-se que a view 'welcome.blade.php' existe em resources/views
    }
}
