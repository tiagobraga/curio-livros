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
        if (!$currentUser->isAttendant() && !$currentUser->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $search = $request->input('search');
        $roleFilter = $request->input('role');

        $query = User::with('libraries');

        // Restrição de papel e bibliotecas associadas
        if ($currentUser->role === 'manager' || $currentUser->isManager()) {
            $associatedLibraryIds = $currentUser->getManagedLibraryIds();
            $query->whereHas('libraries', function($q) use ($associatedLibraryIds) {
                $q->whereIn('libraries.id', $associatedLibraryIds)
                  ->whereIn('library_user.role', ['user', 'attendant']);
            });
        } elseif ($currentUser->role === 'attendant' || $currentUser->isAttendant()) {
            $associatedLibraryIds = $currentUser->getStaffLibraryIds();
            $query->whereHas('libraries', function($q) use ($associatedLibraryIds) {
                $q->whereIn('libraries.id', $associatedLibraryIds)
                  ->where('library_user.role', 'user');
            });
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

        // Solicitações pendentes para moderação
        $pendingRequests = collect();
        if ($currentUser->isAdmin()) {
            // Admin vê pedidos de papel gestor que aguardam aprovação global
            $pendingRequests = \App\Models\LibraryUserRequest::where('status', 'pending')
                ->where('role', 'manager')
                ->with(['user', 'library'])
                ->get();
        } elseif ($currentUser->isManager()) {
            // Gestor vê pedidos das bibliotecas onde atua especificamente como gestor
            $associatedLibraryIds = $currentUser->getManagedLibraryIds();
            $pendingRequests = \App\Models\LibraryUserRequest::where('status', 'pending')
                ->whereIn('library_id', $associatedLibraryIds)
                ->whereIn('role', ['user', 'attendant', 'manager'])
                ->with(['user', 'library'])
                ->get();
        }

        return view('users.index', compact('users', 'search', 'roleFilter', 'pendingRequests'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $currentUser = Auth::user();
        if ($currentUser->role === 'curator' || $currentUser->role === 'user' || !$currentUser->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        // Determina quais bibliotecas podem ser selecionadas
        if ($currentUser->isAdmin()) {
            $libraries = Library::orderBy('name', 'asc')->get();
        } elseif ($currentUser->role === 'manager' || $currentUser->isManager()) {
            $libraries = $currentUser->getManagedLibraries();
        } else {
            $libraries = $currentUser->getStaffLibraries();
        }

        // Determina quais papéis podem ser criados
        $roles = [];
        if ($currentUser->isAdmin()) {
            $roles = [
                'user' => 'Leitor',
                'attendant' => 'Atendente',
                'manager' => 'Gestor',
                'curator' => 'Curador',
                'admin' => 'Administrador',
            ];
        } elseif ($currentUser->role === 'manager') {
            $roles = [
                'user' => 'Leitor',
                'attendant' => 'Atendente',
            ];
        } elseif ($currentUser->role === 'attendant') {
            $roles = [
                'user' => 'Leitor',
            ];
        }

        return view('users.create', compact('libraries', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->role === 'curator' || $currentUser->role === 'user' || !$currentUser->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        // Backward compatibility for tests/seeding passing 'role' and 'library_ids'
        if (!$request->has('library_roles') && $request->has('library_ids')) {
            $libIds = $request->input('library_ids', []);
            $roleInput = $request->input('role', 'user');
            $libraryRoles = [];
            foreach ($libIds as $libId) {
                $localRole = in_array($roleInput, ['admin', 'curator']) ? 'user' : $roleInput;
                $libraryRoles[$libId] = $localRole;
            }
            $request->merge(['library_roles' => $libraryRoles]);
            if (in_array($roleInput, ['admin', 'curator'])) {
                $request->merge(['global_role' => $roleInput]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
            'global_role' => 'nullable|string|in:admin,curator',
            'library_roles' => 'required|array|min:1',
            'library_roles.*' => 'required|string|in:user,attendant,manager',
        ]);

        $libraryRoles = $validated['library_roles'];

        // Atendentes e gestores só podem criar usuários nas bibliotecas que eles mesmos estão associados com permissão
        if (!$currentUser->isAdmin()) {
            if (!empty($validated['global_role'])) {
                abort(403, 'Você não tem permissão para atribuir perfis globais.');
            }

            foreach ($libraryRoles as $libId => $role) {
                if (!$currentUser->isManagerOf($libId) && !$currentUser->isAttendantOf($libId)) {
                    abort(403, 'Você não tem permissão para associar usuários a unidades fora do seu escopo.');
                }
                if (!$currentUser->isManagerOf($libId) && $role !== 'user') {
                    abort(403, 'Atendentes só podem criar usuários leitores.');
                }
            }
        }

        // Determine highest local role for fallback/global role representation
        $highestRole = 'user';
        $syncData = [];
        foreach ($libraryRoles as $libId => $role) {
            $syncData[$libId] = ['role' => $role];
            if ($role === 'manager') {
                $highestRole = 'manager';
            } elseif ($role === 'attendant' && $highestRole !== 'manager') {
                $highestRole = 'attendant';
            }
        }

        $globalRole = $validated['global_role'] ?? null;
        if (empty($globalRole) || !$currentUser->isAdmin()) {
            $globalRole = $highestRole;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $globalRole,
        ]);

        $user->libraries()->sync($syncData);

        // Send email notification with login data and libraries
        try {
            \Mail::to($user->email)->send(new \App\Mail\UserCreatedMail($user, $validated['password']));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de boas-vindas: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isAttendant() && !$currentUser->isCurator()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        // Restrições de visibilidade/edição baseadas em hierarquia
        if (!$currentUser->isAdmin()) {
            if ($currentUser->role === 'curator') {
                // Curador pode ver qualquer usuário globalmente (sem restrição de biblioteca)
            } elseif ($currentUser->role === 'manager' || $currentUser->isManager()) {
                if (!in_array($user->role, ['user', 'attendant'])) {
                    abort(403, 'Você não tem permissão para visualizar perfis com este cargo.');
                }
                $currentUserLibraryIds = $currentUser->getManagedLibraryIds();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Você não tem permissão para editar usuários de outras unidades.');
                }
            } elseif ($currentUser->role === 'attendant' || $currentUser->isAttendant()) {
                if ($user->role !== 'user') {
                    abort(403, 'Você não tem permissão para visualizar perfis com este cargo.');
                }
                $currentUserLibraryIds = $currentUser->getStaffLibraryIds();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Você não tem permissão para editar usuários de outras unidades.');
                }
            } else {
                abort(403, 'Acesso negado.');
            }
        }

        if ($currentUser->isAdmin()) {
            $libraries = Library::orderBy('name', 'asc')->get();
        } elseif ($currentUser->role === 'manager' || $currentUser->isManager()) {
            $libraries = $currentUser->getManagedLibraries();
        } else {
            $libraries = $currentUser->getStaffLibraries();
        }

        // Determina papéis que aparecem no select de edição
        $roles = [];
        if ($currentUser->isAdmin()) {
            $roles = [
                'user' => 'Leitor',
                'attendant' => 'Atendente',
                'manager' => 'Gestor',
                'curator' => 'Curador',
                'admin' => 'Administrador',
            ];
        } elseif ($currentUser->role === 'manager') {
            $roles = [
                'user' => 'Leitor',
                'attendant' => 'Atendente',
                'manager' => 'Gestor',
            ];
        } elseif ($currentUser->role === 'attendant') {
            $roles = [
                'user' => 'Leitor',
            ];
        } else {
            // Curator has read-only view. We load all roles so they display correctly.
            $roles = [
                'user' => 'Leitor',
                'attendant' => 'Atendente',
                'manager' => 'Gestor',
                'curator' => 'Curador',
                'admin' => 'Administrador',
            ];
        }

        $loans = \App\Models\Loan::with(['localCopy.centralItem', 'localCopy.library', 'attendant'])
            ->where('user_id', $id)
            ->orderBy('loan_date', 'desc')
            ->paginate(15, ['*'], 'loans_page')
            ->withQueryString();

        return view('users.edit', compact('user', 'libraries', 'roles', 'loans'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role === 'curator') {
            abort(403, 'Curadores não têm permissão para editar outros usuários.');
        }
        if (!$currentUser->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        // Impedir gestores/atendentes de alterar usuários fora de sua hierarquia ou escopo
        if (!$currentUser->isAdmin()) {
            if ($currentUser->role === 'manager') {
                if (!in_array($user->role, ['user', 'attendant', 'manager'])) {
                    abort(403, 'Acesso negado.');
                }
                $currentUserLibraryIds = $currentUser->libraries()->pluck('libraries.id')->toArray();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Acesso negado.');
                }
            } elseif ($currentUser->role === 'attendant') {
                if ($user->role !== 'user') {
                    abort(403, 'Acesso negado.');
                }
                $currentUserLibraryIds = $currentUser->libraries()->pluck('libraries.id')->toArray();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Acesso negado.');
                }
            } else {
                abort(403, 'Acesso negado.');
            }
        }

        // Backward compatibility for tests/seeding passing 'role' and 'library_ids'
        if (!$request->has('library_roles') && $request->has('library_ids')) {
            $libIds = $request->input('library_ids', []);
            $roleInput = $request->input('role', 'user');
            $libraryRoles = [];
            foreach ($libIds as $libId) {
                $localRole = in_array($roleInput, ['admin', 'curator']) ? 'user' : $roleInput;
                $libraryRoles[$libId] = $localRole;
            }
            $request->merge(['library_roles' => $libraryRoles]);
            if (in_array($roleInput, ['admin', 'curator'])) {
                $request->merge(['global_role' => $roleInput]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
            'global_role' => 'nullable|string|in:admin,curator',
            'library_roles' => 'required|array|min:1',
            'library_roles.*' => 'required|string|in:user,attendant,manager',
        ]);

        $libraryRoles = $validated['library_roles'];

        // Atendentes e gestores só podem associar a bibliotecas às quais estão autorizados
        if (!$currentUser->isAdmin()) {
            if (!empty($validated['global_role'])) {
                abort(403, 'Acesso negado.');
            }

            foreach ($libraryRoles as $libId => $role) {
                if (!$currentUser->isManagerOf($libId) && !$currentUser->isAttendantOf($libId)) {
                    abort(403, 'Acesso negado.');
                }
                if (!$currentUser->isManagerOf($libId) && $role !== 'user') {
                    abort(403, 'Atendentes só podem atribuir perfil de leitor.');
                }
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        // Determine highest local role for fallback/global role representation
        $highestRole = 'user';
        $syncData = [];
        foreach ($libraryRoles as $libId => $role) {
            $syncData[$libId] = ['role' => $role];
            if ($role === 'manager') {
                $highestRole = 'manager';
            } elseif ($role === 'attendant' && $highestRole !== 'manager') {
                $highestRole = 'attendant';
            }
        }

        $globalRole = $validated['global_role'] ?? null;
        if (empty($globalRole) || !$currentUser->isAdmin()) {
            $globalRole = $highestRole;
        }

        $user->role = $globalRole;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->libraries()->sync($syncData);

        // Send email notification of user profile update
        try {
            \Mail::to($user->email)->send(new \App\Mail\UserUpdatedMail($user));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de atualização: ' . $e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role === 'curator') {
            abort(403, 'Acesso negado.');
        }
        if (!$currentUser->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        // Impedir gestores/atendentes de excluir usuários fora de sua hierarquia ou escopo
        if (!$currentUser->isAdmin()) {
            if ($currentUser->role === 'manager' || $currentUser->isManager()) {
                if (!in_array($user->role, ['user', 'attendant', 'manager'])) {
                    abort(403, 'Acesso negado.');
                }
                $currentUserLibraryIds = $currentUser->getManagedLibraryIds();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Acesso negado.');
                }
            } elseif ($currentUser->role === 'attendant' || $currentUser->isAttendant()) {
                if ($user->role !== 'user') {
                    abort(403, 'Acesso negado.');
                }
                $currentUserLibraryIds = $currentUser->getStaffLibraryIds();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Acesso negado.');
                }
            } else {
                abort(403, 'Acesso negado.');
            }
        }

        // Impede deletar a si mesmo
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'Você não pode excluir a sua própria conta.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }

    /**
     * Update the password of a user.
     */
    public function updatePassword(Request $request, $id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role === 'curator') {
            abort(403, 'Acesso negado.');
        }
        if (!$currentUser->isAttendant()) {
            abort(403, 'Acesso negado.');
        }

        $user = User::findOrFail($id);

        // Impedir gestores/atendentes de alterar senhas de usuários fora de sua hierarquia ou escopo
        if (!$currentUser->isAdmin()) {
            if ($currentUser->role === 'manager' || $currentUser->isManager()) {
                if (!in_array($user->role, ['user', 'attendant', 'manager'])) {
                    abort(403, 'Acesso negado.');
                }
                $currentUserLibraryIds = $currentUser->getManagedLibraryIds();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Acesso negado.');
                }
            } elseif ($currentUser->role === 'attendant' || $currentUser->isAttendant()) {
                if ($user->role !== 'user') {
                    abort(403, 'Acesso negado.');
                }
                $currentUserLibraryIds = $currentUser->getStaffLibraryIds();
                $userLibraryIds = $user->libraries()->pluck('libraries.id')->toArray();
                if (empty(array_intersect($currentUserLibraryIds, $userLibraryIds))) {
                    abort(403, 'Acesso negado.');
                }
            } else {
                abort(403, 'Acesso negado.');
            }
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = \Hash::make($validated['password']);
        $user->save();

        // Enviar e-mail de mudança de senha
        try {
            \Mail::to($user->email)->send(new \App\Mail\UserPasswordChangedMail($user, $validated['password']));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de alteração de senha: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Senha alterada com sucesso!');
    }

    public function editProfile()
    {
        $user = Auth::user();

        $linkedIds = $user->libraries()->pluck('libraries.id')->toArray();
        $pendingIds = \App\Models\LibraryUserRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('library_id')
            ->toArray();

        $allLibraries = Library::where('is_active', true)
            ->orderBy('name')
            ->get();

        $pendingRequests = \App\Models\LibraryUserRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('library')
            ->get();

        return view('users.profile', compact('user', 'allLibraries', 'linkedIds', 'pendingIds', 'pendingRequests'));
    }

    /**
     * Update the logged-in user's own profile settings.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'request_library_ids' => 'nullable|array',
            'request_library_ids.*' => 'exists:libraries,id',
        ]);

        $emailChanged = ($user->email !== $validated['email']);
        $nameChanged = ($user->name !== $validated['name']);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->save();

        // Processa solicitações de novas bibliotecas
        if (!empty($validated['request_library_ids'])) {
            foreach ($validated['request_library_ids'] as $libId) {
                // Evita duplicados em status pending
                $exists = \App\Models\LibraryUserRequest::where('user_id', $user->id)
                    ->where('library_id', $libId)
                    ->where('status', 'pending')
                    ->exists();

                if (!$exists) {
                    \App\Models\LibraryUserRequest::create([
                        'user_id' => $user->id,
                        'library_id' => $libId,
                        'status' => 'pending',
                        'role' => 'user', // default requested role
                    ]);
                }
            }
        }

        // Envia notificações adequadas por e-mail
        if ($emailChanged || $nameChanged) {
            try {
                \Mail::to($user->email)->send(new \App\Mail\UserUpdatedMail($user));
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar e-mail de atualização do perfil próprio: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Seu perfil foi atualizado com sucesso! Quaisquer novas solicitações de vínculo com bibliotecas foram enviadas para moderação.');
    }

    /**
     * Update the logged-in user's own password.
     */
    public function updateProfilePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Envia e-mail de mudança de senha
        try {
            \Mail::to($user->email)->send(new \App\Mail\UserPasswordChangedMail($user, $validated['password']));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de alteração de senha do perfil próprio: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Sua senha foi alterada com sucesso!');
    }

    /**
     * Request connection with a new library.
     */
    public function requestLibraryLink(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'library_id' => 'required|exists:libraries,id',
        ]);

        $libId = $validated['library_id'];

        // Evita duplicados em status pending ou ativo
        $isLinked = $user->libraries()->where('libraries.id', $libId)->exists();
        if ($isLinked) {
            return redirect()->back()->with('error', 'Você já possui vínculo com esta biblioteca.');
        }

        $exists = \App\Models\LibraryUserRequest::where('user_id', $user->id)
            ->where('library_id', $libId)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Já existe uma solicitação pendente para esta biblioteca.');
        }

        \App\Models\LibraryUserRequest::create([
            'user_id' => $user->id,
            'library_id' => $libId,
            'status' => 'pending',
            'role' => 'user', // default role request is user (Reader)
        ]);

        return redirect()->back()->with('success', 'Solicitação de vínculo enviada com sucesso para moderação!');
    }

    /**
     * Approve a pending library user request.
     */
    public function approveRequest($id)
    {
        $currentUser = Auth::user();
        $requestEntry = \App\Models\LibraryUserRequest::findOrFail($id);

        if ($requestEntry->status !== 'pending') {
            return redirect()->back()->with('error', 'Esta solicitação já foi processada.');
        }

        // Validação de permissões:
        // Admin aprova qualquer papel
        // Gestor (por global_role OU pivot) aprova pedidos nas suas bibliotecas
        if ($currentUser->isAdmin()) {
            // Admin pode aprovar tudo
        } elseif ($currentUser->isManager()) {
            $managedLibraryIds = $currentUser->getManagedLibraryIds();
            if (!in_array($requestEntry->library_id, $managedLibraryIds)) {
                abort(403, 'Acesso negado. Você só pode moderar solicitações para as suas unidades.');
            }
        } else {
            abort(403, 'Acesso negado.');
        }

        // Aprovação
        $requestEntry->status = 'approved';
        $requestEntry->save();

        // Associa o usuário à biblioteca com o cargo solicitado
        $requestEntry->user->libraries()->syncWithoutDetaching([
            $requestEntry->library_id => ['role' => $requestEntry->role]
        ]);

        // Atualizar o users.role global para refletir o maior papel
        $user = $requestEntry->user;
        $highestRole = 'user';
        // Recarregar relações para obter a tabela pivot atualizada
        $user->load('libraries');
        foreach ($user->libraries as $lib) {
            $role = $lib->pivot->role;
            if ($role === 'manager') {
                $highestRole = 'manager';
            } elseif ($role === 'attendant' && $highestRole !== 'manager') {
                $highestRole = 'attendant';
            }
        }

        if (!in_array($user->role, ['admin', 'curator'])) {
            $user->role = $highestRole;
            $user->save();
        }

        // Notificar por e-mail
        try {
            \Mail::to($requestEntry->user->email)->send(new \App\Mail\UserUpdatedMail($requestEntry->user));
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de confirmação de aprovação de vínculo: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Solicitação de vínculo aprovada com sucesso!');
    }

    /**
     * Reject a pending library user request.
     */
    public function rejectRequest($id)
    {
        $currentUser = Auth::user();
        $requestEntry = \App\Models\LibraryUserRequest::findOrFail($id);

        if ($requestEntry->status !== 'pending') {
            return redirect()->back()->with('error', 'Esta solicitação já foi processada.');
        }

        // Validação de permissões:
        // Admin rejeita qualquer papel
        // Gestor (por global_role OU pivot) rejeita pedidos nas suas bibliotecas
        if ($currentUser->isAdmin()) {
            // Admin pode rejeitar tudo
        } elseif ($currentUser->isManager()) {
            $managedLibraryIds = $currentUser->getManagedLibraryIds();
            if (!in_array($requestEntry->library_id, $managedLibraryIds)) {
                abort(403, 'Acesso negado. Você só pode moderar solicitações para as suas unidades.');
            }
        } else {
            abort(403, 'Acesso negado.');
        }

        // Rejeição
        $requestEntry->status = 'rejected';
        $requestEntry->save();

        return redirect()->back()->with('success', 'Solicitação de vínculo rejeitada.');
    }
}
