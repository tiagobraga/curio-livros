<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Library;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isManager()) {
            abort(403, 'Acesso negado.');
        }

        $search = $request->input('search');
        $roleFilter = $request->input('role');

        $query = User::with('library');

        // Se for gestor (não admin), vê apenas os usuários vinculados à sua biblioteca
        if ($currentUser->role === 'manager' && $currentUser->library_id) {
            $query->where('library_id', $currentUser->library_id);
        }

        // Filtro de Busca
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro de Papel/Role
        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('users.index', compact('users', 'search', 'roleFilter'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $currentUser = Auth::user();
        if (!$currentUser->isManager()) {
            abort(403, 'Acesso negado.');
        }

        // Determina quais bibliotecas podem ser selecionadas
        if ($currentUser->isAdmin()) {
            $libraries = Library::orderBy('name', 'asc')->get();
        } else {
            $libraries = Library::where('id', $currentUser->library_id)->get();
        }

        // Determina quais papéis podem ser criados
        $roles = [
            'user' => 'Leitor',
            'attendant' => 'Atendente',
        ];

        if ($currentUser->isAdmin()) {
            $roles['manager'] = 'Gestor';
            $roles['curator'] = 'Curador';
            $roles['admin'] = 'Administrador';
        }

        return view('users.create', compact('libraries', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isManager()) {
            abort(403, 'Acesso negado.');
        }

        $allowedRoles = ['user', 'attendant'];
        if ($currentUser->isAdmin()) {
            $allowedRoles = ['user', 'attendant', 'manager', 'curator', 'admin'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in($allowedRoles)],
            'library_id' => 'nullable|exists:libraries,id',
        ]);

        // Gestores normais só podem criar usuários na sua própria biblioteca
        $libraryId = $validated['library_id'];
        if (!$currentUser->isAdmin()) {
            $libraryId = $currentUser->library_id;
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'library_id' => $libraryId,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isManager()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        // Garante que gestor não edite usuários de outra unidade
        if (!$currentUser->isAdmin() && $user->library_id !== $currentUser->library_id) {
            abort(403, 'Você não tem permissão para editar usuários de outras unidades.');
        }

        if ($currentUser->isAdmin()) {
            $libraries = Library::orderBy('name', 'asc')->get();
        } else {
            $libraries = Library::where('id', $currentUser->library_id)->get();
        }

        $roles = [
            'user' => 'Leitor',
            'attendant' => 'Atendente',
        ];

        if ($currentUser->isAdmin()) {
            $roles['manager'] = 'Gestor';
            $roles['curator'] = 'Curador';
            $roles['admin'] = 'Administrador';
        }

        return view('users.edit', compact('user', 'libraries', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isManager()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        if (!$currentUser->isAdmin() && $user->library_id !== $currentUser->library_id) {
            abort(403, 'Acesso negado.');
        }

        $allowedRoles = ['user', 'attendant'];
        if ($currentUser->isAdmin()) {
            $allowedRoles = ['user', 'attendant', 'manager', 'curator', 'admin'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in($allowedRoles)],
            'library_id' => 'nullable|exists:libraries,id',
        ]);

        $libraryId = $validated['library_id'];
        if (!$currentUser->isAdmin()) {
            $libraryId = $currentUser->library_id;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->library_id = $libraryId;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isManager()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        if (!$currentUser->isAdmin() && $user->library_id !== $currentUser->library_id) {
            abort(403, 'Acesso negado.');
        }

        // Impede deletar a si mesmo
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'Você não pode excluir a sua própria conta.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
