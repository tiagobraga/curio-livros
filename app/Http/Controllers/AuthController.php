<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Library;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $libraries = Library::where('is_active', true)->orderBy('name')->get();
        return view('auth.register', compact('libraries'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'library_id' => ['required', 'exists:libraries,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'user', // Default is Reader (user)
        ]);

        \App\Models\LibraryUserRequest::create([
            'user_id' => $user->id,
            'library_id' => $request->library_id,
            'status' => 'pending',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Cadastro realizado com sucesso! Sua solicitação de vínculo com a biblioteca está aguardando a aprovação de um gestor.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showRegisterLibrary()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register-library');
    }

    public function registerLibrary(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'library_name' => ['required', 'string', 'max:255', 'unique:libraries,name'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'library_type' => ['nullable', 'string', 'in:Pessoal,Pública,Universitária,Especializada,Outro'],
            'is_public_page' => ['nullable', 'boolean'],
            'share_code' => [
                'nullable',
                'string',
                'min:3',
                'max:12',
                'alpha_num',
                \Illuminate\Validation\Rule::unique('libraries', 'share_code'),
                function ($attribute, $value, $fail) {
                    if (\App\Models\CentralItem::where('share_code', $value)->exists()) {
                        $fail('O código de compartilhamento já está sendo utilizado por um livro.');
                    }
                }
            ],
        ]);

        // Create the library
        $library = Library::create([
            'name' => $validated['library_name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'library_type' => $validated['library_type'] ?? null,
            'is_public_page' => $request->has('is_public_page'),
            'share_code' => !empty($validated['share_code']) ? strtoupper($validated['share_code']) : null,
            'is_active' => true,
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'manager', // Creator of library is a manager
        ]);

        // Link user to library as manager
        $user->libraries()->attach($library->id, ['role' => 'manager']);

        // Log user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Biblioteca criada e conta de gestor registrada com sucesso!');
    }

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);
            $url = request()->getSchemeAndHttpHost() . '/reset-password/' . $token . '?email=' . urlencode($user->email);
            
            try {
                Mail::to($user->email)->send(new ResetPasswordMail($user, $url));
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar e-mail de redefinição de senha: ' . $e->getMessage());
                return back()->withErrors(['email' => 'Não foi possível enviar o e-mail de recuperação. Tente novamente mais tarde.']);
            }
        }

        // We return success status even if user doesn't exist for security (avoid enumeration)
        return back()->with('status', 'Se o e-mail estiver cadastrado, enviamos o link de recuperação para ele.');
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Não encontramos nenhum usuário com este endereço de e-mail.']);
        }

        $isValid = Password::broker()->tokenExists($user, $request->token);
        if (!$isValid) {
            return back()->withErrors(['email' => 'Este link de recuperação é inválido ou expirou.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Password::broker()->deleteToken($user);

        return redirect()->route('login')->with('success', 'Sua senha foi redefinida com sucesso! Agora você já pode entrar no sistema.');
    }
}
