<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Library;
use Illuminate\Support\Facades\Auth;
use App\Services\CallNumberService;
use Illuminate\Support\Str;

class LibraryController extends Controller
{
    /**
     * Display a listing of libraries.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->isCurator() && !$user->isManager()) {
            abort(403, __('Acesso negado.'));
        }

        $query = Library::orderBy('name', 'asc');

        // Non-curators only see the libraries they manage
        if (!$user->isCurator()) {
            $managedIds = $user->getManagedLibraryIds();
            $query->whereIn('id', $managedIds);
        }

        $libraries = $query->paginate(10);
        return view('libraries.index', compact('libraries'));
    }

    /**
     * Show the form for creating a new library.
     */
    public function create()
    {
        return view('libraries.create');
    }

    /**
     * Store a newly created library.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:libraries,name',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'library_type' => 'nullable|string|in:Pessoal,Pública,Universitária,Especializada,Outro',
            'color_scheme' => 'nullable|string|in:emerald,navy_blue,royal_burgundy,warm_amber,slate_dark,deep_violet',
            'custom_logo_url' => 'nullable|string|max:2048',
            'custom_logo_file' => 'nullable|image|max:2048',
            'subtitle' => 'nullable|string|max:255',
            'is_public_page' => 'nullable|boolean',
            'share_code' => [
                'nullable',
                'string',
                'min:3',
                'max:12',
                'alpha_num',
                \Illuminate\Validation\Rule::unique('libraries', 'share_code'),
                function ($attribute, $value, $fail) {
                    if (\App\Models\CentralItem::where('share_code', $value)->exists()) {
                        $fail(__('O código de compartilhamento já está sendo utilizado por um livro.'));
                    }
                }
            ],
            'is_active' => 'required|boolean',
        ]);

        $logoUrl = $validated['custom_logo_url'] ?? null;
        if ($request->hasFile('custom_logo_file')) {
            $file = $request->file('custom_logo_file');
            $fileName = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('logos');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $file->move($targetDir, $fileName);
            $logoUrl = '/logos/' . $fileName;
        }

        $customFields = $this->parseCustomFields($request->input('custom_fields'));
        $callNumberFormat = $this->parseCallNumberFormat($request->input('call_number_format'));

        $this->ensureMigrationsRun();

        $library = Library::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'library_type' => $validated['library_type'] ?? null,
            'color_scheme' => $validated['color_scheme'] ?? 'emerald',
            'custom_logo_url' => $logoUrl,
            'subtitle' => $validated['subtitle'] ?? null,
            'is_public_page' => $request->has('is_public_page'),
            'share_code' => !empty($validated['share_code']) ? strtoupper($validated['share_code']) : null,
            'is_active' => $validated['is_active'],
            'custom_fields' => $customFields,
            'call_number_format' => $callNumberFormat,
            'call_number_counters' => [],
        ]);

        // Link the creator as manager of this library
        $user->libraries()->attach($library->id, ['role' => 'manager']);

        // Upgrade user's global role to manager if they were a reader
        if ($user->role === 'user') {
            $user->role = 'manager';
            $user->save();
        }

        return redirect()->route('libraries.index')->with('success', __('Unidade cadastrada com sucesso!'));
    }

    /**
     * Show the edit form for a library.
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (!$user->isCurator() && !$user->isManagerOf($id)) {
            abort(403, __('Acesso negado.'));
        }

        $library = Library::findOrFail($id);
        return view('libraries.edit', compact('library'));
    }

    /**
     * Update the library.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isCurator() && !$user->isManagerOf($id)) {
            abort(403, __('Acesso negado.'));
        }

        $library = Library::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:libraries,name,' . $library->id,
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'library_type' => 'nullable|string|in:Pessoal,Pública,Universitária,Especializada,Outro',
            'color_scheme' => 'nullable|string|in:emerald,navy_blue,royal_burgundy,warm_amber,slate_dark,deep_violet',
            'custom_logo_url' => 'nullable|string|max:2048',
            'custom_logo_file' => 'nullable|image|max:2048',
            'subtitle' => 'nullable|string|max:255',
            'is_public_page' => 'nullable|boolean',
            'share_code' => [
                'nullable',
                'string',
                'min:3',
                'max:12',
                'alpha_num',
                \Illuminate\Validation\Rule::unique('libraries', 'share_code')->ignore($library->id),
                function ($attribute, $value, $fail) {
                    if (\App\Models\CentralItem::where('share_code', $value)->exists()) {
                        $fail(__('O código de compartilhamento já está sendo utilizado por um livro.'));
                    }
                }
            ],
            'is_active' => 'required|boolean',
        ]);

        $logoUrl = $validated['custom_logo_url'] ?? $library->custom_logo_url;
        if ($request->hasFile('custom_logo_file')) {
            $file = $request->file('custom_logo_file');
            $fileName = 'logo_' . $library->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('logos');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $file->move($targetDir, $fileName);
            $logoUrl = '/logos/' . $fileName;
        }

        $customFields = $this->parseCustomFields($request->input('custom_fields'));
        $callNumberFormat = $this->parseCallNumberFormat($request->input('call_number_format'));

        $this->ensureMigrationsRun();

        $library->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'library_type' => $validated['library_type'] ?? null,
            'color_scheme' => $validated['color_scheme'] ?? 'emerald',
            'custom_logo_url' => $logoUrl,
            'subtitle' => $validated['subtitle'] ?? null,
            'is_public_page' => $request->has('is_public_page'),
            'share_code' => !empty($validated['share_code']) ? strtoupper($validated['share_code']) : $library->share_code,
            'is_active' => $validated['is_active'],
            'custom_fields' => $customFields,
            'call_number_format' => $callNumberFormat,
        ]);

        return redirect()->route('libraries.index')->with('success', __('Unidade atualizada com sucesso!'));
    }

    /**
     * Delete the library.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user->isCurator() && !$user->isManagerOf($id)) {
            abort(403, __('Acesso negado.'));
        }

        $library = Library::findOrFail($id);

        // Prevent deletion if the library has physical copies
        if ($library->localCopies()->count() > 0) {
            return redirect()->route('libraries.index')->with('error', __('Não é possível excluir esta unidade pois ela possui exemplares físicos cadastrados.'));
        }

        $library->delete();

        return redirect()->route('libraries.index')->with('success', __('Unidade excluída com sucesso!'));
    }

    /**
     * Get custom fields and call number format for a library (JSON API).
     */
    public function getCustomFieldsAndFormat($id)
    {
        $library = Library::findOrFail($id);
        $customFields = $library->getCustomFields();

        foreach ($customFields as &$field) {
            if (($field['type'] ?? '') === 'integer' && !empty($field['auto_increment'])) {
                $field['suggested_value'] = $library->getLowestAvailableAutoIncrementValue($field['key']);
            }
        }
        unset($field);

        return response()->json([
            'id' => $library->id,
            'name' => $library->name,
            'custom_fields' => $customFields,
            'call_number_format' => $library->getCallNumberFormat(),
            'has_call_number_format' => $library->hasCallNumberFormat(),
            'standard_fields' => CallNumberService::getStandardFields(),
        ]);
    }

    /**
     * Recalculate call numbers for all books/copies in the specified library.
     */
    public function recalculateCallNumbers(Request $request, $id, CallNumberService $callNumberService)
    {
        $user = Auth::user();
        if (!$user->isCurator() && !$user->isManagerOf($id)) {
            abort(403, __('Acesso negado.'));
        }

        $library = Library::findOrFail($id);
        $count = $callNumberService->recalculateForLibrary($library);

        return redirect()->back()->with('success', __("Número de chamada recalculado com sucesso para :count exemplar(es) desta biblioteca.", ['count' => $count]));
    }

    /**
     * Parse and sanitize custom fields input.
     */
    protected function parseCustomFields($raw): array
    {
        if (empty($raw)) {
            return [];
        }
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        if (!is_array($raw)) {
            return [];
        }

        $sanitized = [];
        foreach ($raw as $field) {
            if (!is_array($field)) continue;
            $label = trim($field['label'] ?? $field['name'] ?? '');
            if ($label === '') continue;

            $key = trim($field['key'] ?? '');
            if ($key === '') {
                $key = 'cf_' . Str::slug($label, '_') . '_' . substr(md5(uniqid()), 0, 4);
            }

            $type = in_array($field['type'] ?? '', ['integer', 'string', 'select', 'boolean']) ? $field['type'] : 'string';
            $autoIncrement = ($type === 'integer') && !empty($field['auto_increment']);

            $options = [];
            if ($type === 'select') {
                $opts = $field['options'] ?? [];
                if (is_string($opts)) {
                    $opts = array_map('trim', explode(',', $opts));
                }
                if (is_array($opts)) {
                    $options = array_values(array_filter(array_map('trim', $opts)));
                }
            }

            $sanitized[] = [
                'key' => $key,
                'label' => $label,
                'name' => $label,
                'type' => $type,
                'auto_increment' => $autoIncrement,
                'options' => $options,
            ];
        }

        return $sanitized;
    }

    /**
     * Parse and sanitize call number format input.
     */
    protected function parseCallNumberFormat($raw): array
    {
        if (empty($raw)) {
            return [];
        }
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        if (!is_array($raw)) {
            return [];
        }

        $sanitized = [];
        foreach ($raw as $element) {
            if (!is_array($element)) continue;
            $type = $element['type'] ?? 'field';

            if ($type === 'static') {
                $val = (string)($element['value'] ?? '');
                if ($val !== '') {
                    $sanitized[] = [
                        'type' => 'static',
                        'value' => $val,
                    ];
                }
            } elseif ($type === 'field') {
                $key = trim($element['key'] ?? '');
                if ($key === '') continue;
                $source = ($element['source'] ?? '') === 'custom' ? 'custom' : 'standard';
                $label = trim($element['label'] ?? $element['name'] ?? $key);
                $length = !empty($element['length']) && is_numeric($element['length']) ? (int)$element['length'] : null;
                $casing = in_array($element['casing'] ?? '', ['lower', 'upper', 'original']) ? $element['casing'] : 'lower';

                $sanitized[] = [
                    'type' => 'field',
                    'source' => $source,
                    'key' => $key,
                    'label' => $label,
                    'name' => $label,
                    'length' => $length,
                    'casing' => $casing,
                ];
            }
        }

        return $sanitized;
    }

    private function ensureMigrationsRun(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('libraries') && !\Illuminate\Support\Facades\Schema::hasColumn('libraries', 'custom_fields')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Erro ao auto-executar migrações: ' . $e->getMessage());
        }
    }
}
