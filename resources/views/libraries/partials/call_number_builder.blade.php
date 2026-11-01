<!-- CAMPOS PERSONALIZADOS E CONSTRUTOR DE NÚMERO DE CHAMADA -->
<div class="space-y-8 pt-6 border-t border-slate-800">
    <!-- Hidden inputs to submit JSON to backend -->
    <input type="hidden" name="custom_fields" id="custom_fields_json" value="">
    <input type="hidden" name="call_number_format" id="call_number_format_json" value="">

    <!-- SEÇÃO: CAMPOS PERSONALIZADOS DO ACERVO -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                {{ __('Campos Personalizados do Acervo') }}
            </h2>
            <button type="button" onclick="toggleCustomFieldForm()" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2 rounded-xl text-xs transition duration-150 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Novo Campo') }}
            </button>
        </div>

        <p class="text-xs text-slate-400">
            {{ __('Defina campos específicos para o acervo desta unidade (ex: Número de Tombo, Coleção Especial, Classificação Interna). Eles ficarão visíveis para preenchimento ao cadastrar exemplares nesta biblioteca.') }}
        </p>

        <!-- Formulário de Adicionar/Editar Campo Personalizado (Inline / Expansível) -->
        <div id="custom-field-form" class="hidden bg-slate-950 border border-primary/30 rounded-2xl p-4 space-y-4">
            <h3 id="cf_form_title" class="text-xs font-bold uppercase tracking-wider text-slate-300">{{ __('Adicionar Novo Campo Personalizado') }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase text-slate-400">{{ __('Nome / Rótulo do Campo') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="new_cf_label" placeholder="{{ __('ex: Código do Tombo, Coleção, etc.') }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-primary">
                </div>
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold uppercase text-slate-400">{{ __('Tipo de Dado') }} <span class="text-red-500">*</span></label>
                    <select id="new_cf_type" onchange="handleFieldTypeChange()" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-primary">
                        <option value="string">{{ __('Texto Curto (String)') }}</option>
                        <option value="integer">{{ __('Número Inteiro (Integer)') }}</option>
                        <option value="select">{{ __('Seleção de Opções (Select)') }}</option>
                        <option value="boolean">{{ __('Verdadeiro / Falso (Booleano)') }}</option>
                    </select>
                </div>
            </div>

            <!-- Opção de Auto Incremento (para inteiro) -->
            <div id="cf_auto_increment_wrapper" class="hidden flex items-center gap-2 pt-1">
                <input type="checkbox" id="new_cf_auto_increment" class="w-4 h-4 rounded border-slate-800 bg-slate-900 text-primary focus:ring-primary">
                <label for="new_cf_auto_increment" class="text-xs font-medium text-slate-300 select-none cursor-pointer">
                    {{ __('Ativar Auto Incremento (gera um contador numérico sequencial automático para cada novo livro desta biblioteca)') }}
                </label>
            </div>

            <!-- Opções de Seleção (para select) -->
            <div id="cf_options_wrapper" class="hidden space-y-1 pt-1">
                <label class="block text-[11px] font-bold uppercase text-slate-400">{{ __('Opções da Lista (separadas por vírgula)') }} <span class="text-red-500">*</span></label>
                <input type="text" id="new_cf_options" placeholder="{{ __('ex: Ficção, Biografia, Poesia, História') }}" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-100 focus:outline-none focus:border-primary">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="toggleCustomFieldForm(false)" class="bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-slate-800 font-medium px-4 py-2 rounded-xl text-xs transition duration-150">
                    {{ __('Cancelar') }}
                </button>
                <button type="button" id="cf_submit_btn" onclick="saveCustomField()" class="bg-primary hover:bg-primary-hover text-white font-medium px-5 py-2 rounded-xl text-xs transition duration-150 shadow-sm">
                    {{ __('Salvar Campo') }}
                </button>
            </div>
        </div>

        <!-- Lista de Campos Personalizados Cadastrados -->
        <div id="custom-fields-list" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Renderizado dinamicamente via JS -->
        </div>
        <div id="no-custom-fields-msg" class="text-xs text-slate-500 italic py-3 text-center bg-slate-950/50 border border-slate-800/60 rounded-2xl">
            {{ __('Nenhum campo personalizado cadastrado para esta biblioteca.') }}
        </div>
    </div>

    <!-- SEÇÃO: CONSTRUTOR DO NÚMERO DE CHAMADA AUTOMÁTICO -->
    <div class="space-y-4 pt-4 border-t border-slate-800">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                </svg>
                {{ __('Configurador do Número de Chamada Automático') }}
            </h2>
            <button type="button" onclick="clearCallNumberFormat()" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-red-400 border border-slate-800 hover:border-red-900/40 font-medium px-3 py-1.5 rounded-xl text-xs transition duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                {{ __('Limpar Formato') }}
            </button>
        </div>

        <p class="text-xs text-slate-400">
            {{ __('Monte o padrão do número de chamada arrastando ou clicando nos campos disponíveis. Você pode estipular a quantidade de caracteres de cada campo (deixe em branco para usar o texto completo) e adicionar separadores estáticos.') }}
        </p>

        <!-- Builder Grid: Left (Palette) & Right (Drop Target) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- Paleta de Campos Disponíveis (lg:col-span-5) -->
            <div class="lg:col-span-5 space-y-4 bg-slate-950/80 border border-slate-800 rounded-2xl p-4">
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                    {{ __('1. Campos Disponíveis (Clique para Adicionar)') }}
                </span>

                <!-- Standard Fields -->
                <div class="space-y-1.5">
                    <span class="block text-[11px] font-semibold text-slate-400">{{ __('Metadados Padrões da Obra') }}</span>
                    <div class="flex flex-wrap gap-1.5" id="standard-palette-chips">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Custom Fields -->
                <div class="space-y-1.5 pt-2 border-t border-slate-850">
                    <span class="block text-[11px] font-semibold text-slate-400">{{ __('Campos Personalizados Desta Unidade') }}</span>
                    <div class="flex flex-wrap gap-1.5" id="custom-palette-chips">
                        <!-- Populated by JS -->
                    </div>
                    <span id="no-custom-chips-msg" class="text-[11px] text-slate-500 italic block">
                        {{ __('Nenhum campo personalizado cadastrado.') }}
                    </span>
                </div>

                <!-- Static Separators -->
                <div class="space-y-1.5 pt-2 border-t border-slate-850">
                    <span class="block text-[11px] font-semibold text-slate-400">{{ __('Separadores e Símbolos Estáticos') }}</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" onclick="addStaticSeparator('-')" class="px-3 py-1.5 rounded-xl text-xs font-mono font-medium bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-800 hover:border-slate-700 transition duration-150 flex items-center gap-1.5 shadow-sm">
                            <span>-</span> <span class="text-[10px] text-slate-400">({{ __('Hífen') }})</span>
                        </button>
                        <button type="button" onclick="addStaticSeparator('/')" class="px-3 py-1.5 rounded-xl text-xs font-mono font-medium bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-800 hover:border-slate-700 transition duration-150 flex items-center gap-1.5 shadow-sm">
                            <span>/</span> <span class="text-[10px] text-slate-400">({{ __('Barra') }})</span>
                        </button>
                        <button type="button" onclick="addStaticSeparator('.')" class="px-3 py-1.5 rounded-xl text-xs font-mono font-medium bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-800 hover:border-slate-700 transition duration-150 flex items-center gap-1.5 shadow-sm">
                            <span>.</span> <span class="text-[10px] text-slate-400">({{ __('Ponto') }})</span>
                        </button>
                        <button type="button" onclick="addStaticSeparator(' ')" class="px-3 py-1.5 rounded-xl text-xs font-mono font-medium bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-800 hover:border-slate-700 transition duration-150 flex items-center gap-1.5 shadow-sm">
                            <span class="italic text-[11px]">␣</span> <span class="text-[10px] text-slate-400">({{ __('Espaço') }})</span>
                        </button>
                        <div class="flex items-center gap-1.5">
                            <input type="text" id="custom-static-input" placeholder="{{ __('Outro...') }}" maxlength="10" class="w-20 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-primary">
                            <button type="button" onclick="addCustomStaticSeparator()" class="px-3 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-semibold transition duration-150 shadow-sm" title="{{ __('Adicionar separador personalizado') }}">
                                +
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sequência Selecionada / Drop Target (lg:col-span-7) -->
            <div class="lg:col-span-7 space-y-4 bg-slate-950/80 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-850">
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            {{ __('2. Sequência do Número de Chamada') }}
                        </span>
                        <span class="text-[11px] text-slate-500 font-medium">
                            {{ __('Arraste ou use as setas para reordenar') }}
                        </span>
                    </div>

                    <!-- Drop Zone List -->
                    <div id="call-number-format-list" class="space-y-2 min-h-[160px] p-2 rounded-xl bg-slate-900/40 border-2 border-dashed border-slate-800/80 transition"
                        ondragover="handleFormatDragOver(event)"
                        ondragleave="handleFormatDragLeave(event)"
                        ondrop="handleFormatDrop(event)">
                        <!-- Populated by JS -->
                    </div>

                    <div id="format-empty-msg" class="text-xs text-slate-500 italic text-center py-8">
                        {{ __('Nenhum campo selecionado. Clique nos campos à esquerda para montar a estrutura.') }}
                    </div>
                </div>

                <!-- SIMULADOR / PRÉ-VISUALIZAÇÃO EM TEMPO REAL -->
                <div class="mt-4 pt-4 border-t border-slate-800 bg-slate-900/80 rounded-xl p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            {{ __('Pré-visualização em Tempo Real') }}
                        </span>
                        <span class="text-[10px] text-slate-400">
                            {{ __('Exemplo: livro "My Book Title", autor "Aronofsky", ano 2007...') }}
                        </span>
                    </div>

                    <div class="bg-black/40 border border-slate-750 rounded-xl px-4 py-3 flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-medium">{{ __('Número de Chamada Gerado:') }}</span>
                        <span id="call-number-preview" class="font-mono text-base font-bold text-emerald-400 tracking-wide select-all">
                            <!-- Populated by JS -->
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($isEdit) && !empty($library))
        <!-- BOTÃO DE RECALCULAR NÚMEROS DE CHAMADA EM LOTE (EXCLUSIVO EDIÇÃO) -->
        <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-primary/5 border border-primary/20 rounded-2xl p-4">
            <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    {{ __('Recalcular Número de Chamada do Acervo') }}
                </h4>
                <p class="text-xs text-slate-400">
                    {{ __('Atualiza retroativamente o número de chamada de todos os exemplares já cadastrados nesta biblioteca de acordo com a regra atual.') }}
                </p>
            </div>
            <button type="button" onclick="submitRecalculateCallNumbers()" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium px-4 py-2.5 rounded-xl text-xs transition duration-150 shadow-lg shadow-emerald-950/15 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ __('Recalcular para Todo o Acervo') }}
            </button>
        </div>
    @endif
</div>

<script>
    // ESTADO LOCAL DOS CAMPOS PERSONALIZADOS E DO FORMATO DO NÚMERO DE CHAMADA
    let customFields = @json(old('custom_fields') ? (is_string(old('custom_fields')) ? json_decode(old('custom_fields'), true) : old('custom_fields')) : (!empty($library) ? $library->getCustomFields() : [])) || [];
    let callNumberFormat = @json(old('call_number_format') ? (is_string(old('call_number_format')) ? json_decode(old('call_number_format'), true) : old('call_number_format')) : (!empty($library) ? $library->getCallNumberFormat() : [])) || [];

    const standardFields = [
        { key: 'title', label: 'Título da Obra', sample: 'My Book Title' },
        { key: 'author', label: 'Autor', sample: 'Aronofsky' },
        { key: 'publisher', label: 'Editora', sample: 'Companhia' },
        { key: 'isbn', label: 'ISBN', sample: '9788535902778' },
        { key: 'publication_year', label: 'Ano de Publicação', sample: '2007' },
        { key: 'pages', label: 'Número de Páginas', sample: '320' },
        { key: 'subjects', label: 'Assuntos / Tags', sample: 'Che Guevara, Biografia' },
        { key: 'description', label: 'Sinopse', sample: 'Biografia de liderança histórica' }
    ];

    let draggedItemIndex = null;

    document.addEventListener('DOMContentLoaded', () => {
        renderStandardPalette();
        renderCustomFields();
        renderFormatList();
        updateHiddenInputs();
    });

    // --- GERENCIAMENTO DE CAMPOS PERSONALIZADOS ---
    let editingCustomFieldKey = null;

    function toggleCustomFieldForm(forceState = null) {
        const form = document.getElementById('custom-field-form');
        const titleEl = document.getElementById('cf_form_title');
        const submitBtn = document.getElementById('cf_submit_btn');

        if (forceState === false || (forceState === null && !form.classList.contains('hidden') && editingCustomFieldKey)) {
            // Fechando ou cancelando
            editingCustomFieldKey = null;
            document.getElementById('new_cf_label').value = '';
            document.getElementById('new_cf_type').value = 'string';
            document.getElementById('new_cf_options').value = '';
            document.getElementById('new_cf_auto_increment').checked = false;
            handleFieldTypeChange();
            if (titleEl) titleEl.textContent = 'Adicionar Novo Campo Personalizado';
            if (submitBtn) submitBtn.textContent = 'Salvar Campo';
            form.classList.add('hidden');
            return;
        }

        if (forceState === true) {
            form.classList.remove('hidden');
        } else {
            form.classList.toggle('hidden');
        }

        if (!form.classList.contains('hidden')) {
            document.getElementById('new_cf_label').focus();
        } else {
            editingCustomFieldKey = null;
            if (titleEl) titleEl.textContent = 'Adicionar Novo Campo Personalizado';
            if (submitBtn) submitBtn.textContent = 'Salvar Campo';
        }
    }

    function handleFieldTypeChange() {
        const type = document.getElementById('new_cf_type').value;
        const autoIncWrapper = document.getElementById('cf_auto_increment_wrapper');
        const optionsWrapper = document.getElementById('cf_options_wrapper');

        if (type === 'integer') {
            autoIncWrapper.classList.remove('hidden');
        } else {
            autoIncWrapper.classList.add('hidden');
        }

        if (type === 'select') {
            optionsWrapper.classList.remove('hidden');
        } else {
            optionsWrapper.classList.add('hidden');
        }
    }

    function editCustomField(key) {
        const field = customFields.find(f => f.key === key);
        if (!field) return;

        editingCustomFieldKey = key;
        document.getElementById('new_cf_label').value = field.label || field.name || '';
        document.getElementById('new_cf_type').value = field.type || 'string';
        document.getElementById('new_cf_auto_increment').checked = !!field.auto_increment;
        document.getElementById('new_cf_options').value = Array.isArray(field.options) ? field.options.join(', ') : '';

        handleFieldTypeChange();

        const titleEl = document.getElementById('cf_form_title');
        const submitBtn = document.getElementById('cf_submit_btn');
        if (titleEl) titleEl.textContent = 'Editar Campo Personalizado';
        if (submitBtn) submitBtn.textContent = 'Atualizar Campo';

        const form = document.getElementById('custom-field-form');
        form.classList.remove('hidden');
        document.getElementById('new_cf_label').focus();
    }

    function saveCustomField() {
        const label = document.getElementById('new_cf_label').value.trim();
        if (!label) {
            alert('Por favor, informe o nome do campo personalizado.');
            return;
        }

        const type = document.getElementById('new_cf_type').value;
        const isAutoInc = (type === 'integer') && document.getElementById('new_cf_auto_increment').checked;
        const optionsRaw = document.getElementById('new_cf_options').value.trim();

        let options = [];
        if (type === 'select') {
            if (!optionsRaw) {
                alert('Informe ao menos uma opção para o campo de seleção.');
                return;
            }
            options = optionsRaw.split(',').map(s => s.trim()).filter(Boolean);
        }

        if (editingCustomFieldKey) {
            // Modo Edição: atualiza campo existente preservando a chave
            const existingField = customFields.find(f => f.key === editingCustomFieldKey);
            if (existingField) {
                existingField.label = label;
                existingField.name = label;
                existingField.type = type;
                existingField.auto_increment = isAutoInc;
                existingField.options = options;

                // Atualiza rótulo no callNumberFormat se este campo estiver inserido
                callNumberFormat.forEach(item => {
                    if (item.type === 'field' && item.source === 'custom' && item.key === editingCustomFieldKey) {
                        item.label = label;
                    }
                });
            }
        } else {
            // Modo Criação
            const slugKey = 'cf_' + label.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]/g, '_') + '_' + Math.random().toString(36).substring(2, 6);

            const newField = {
                key: slugKey,
                label: label,
                name: label,
                type: type,
                auto_increment: isAutoInc,
                options: options
            };

            customFields.push(newField);
        }

        // Limpa formulário e reseta estado
        toggleCustomFieldForm(false);

        renderCustomFields();
        renderFormatList();
        updateHiddenInputs();
    }

    // Alias para compatibilidade
    function addCustomField() {
        saveCustomField();
    }

    function removeCustomField(key) {
        if (!confirm('Deseja realmente remover este campo personalizado?')) {
            return;
        }
        if (editingCustomFieldKey === key) {
            toggleCustomFieldForm(false);
        }
        customFields = customFields.filter(f => f.key !== key);
        // Remove também do formato do número de chamada caso tenha sido usado
        callNumberFormat = callNumberFormat.filter(item => !(item.type === 'field' && item.source === 'custom' && item.key === key));

        renderCustomFields();
        renderFormatList();
        updateHiddenInputs();
    }

    function renderCustomFields() {
        const listEl = document.getElementById('custom-fields-list');
        const emptyEl = document.getElementById('no-custom-fields-msg');
        listEl.innerHTML = '';

        if (customFields.length === 0) {
            emptyEl.classList.remove('hidden');
        } else {
            emptyEl.classList.add('hidden');
            customFields.forEach(f => {
                const card = document.createElement('div');
                card.className = 'flex items-center justify-between p-3 bg-slate-900/60 border border-slate-800 rounded-xl text-xs';
                
                let typeBadge = '';
                if (f.type === 'integer') {
                    typeBadge = f.auto_increment ? 'Inteiro (Auto Incremento)' : 'Inteiro';
                } else if (f.type === 'select') {
                    typeBadge = `Seleção (${(f.options || []).length} opções)`;
                } else if (f.type === 'boolean') {
                    typeBadge = 'Booleano (Sim/Não)';
                } else {
                    typeBadge = 'Texto (String)';
                }

                card.innerHTML = `
                    <div class="truncate mr-2">
                        <span class="font-semibold text-slate-100 block truncate">${escapeHtml(f.label)}</span>
                        <span class="text-[10px] text-emerald-400 font-mono">${typeBadge}</span>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" onclick="editCustomField('${f.key}')" class="text-slate-400 hover:text-primary p-1.5 rounded-lg hover:bg-primary/10 transition duration-150" title="{{ __('Editar Campo') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        <button type="button" onclick="removeCustomField('${f.key}')" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-red-950/30 transition duration-150" title="{{ __('Remover Campo') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                `;
                listEl.appendChild(card);
            });
        }

        renderCustomPalette();
    }

    // --- PALETA DE CAMPOS ---
    function renderStandardPalette() {
        const container = document.getElementById('standard-palette-chips');
        container.innerHTML = '';
        standardFields.forEach(sf => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'px-3 py-1.5 rounded-xl text-xs font-medium bg-slate-900 hover:bg-slate-800 text-slate-200 hover:text-white border border-slate-800 hover:border-primary/60 transition duration-150 flex items-center gap-1.5 shadow-sm cursor-pointer';
            btn.innerHTML = `
                <span>${escapeHtml(sf.label)}</span>
                <span class="text-primary font-bold text-xs">+</span>
            `;
            btn.onclick = () => addFieldToFormat('standard', sf.key, sf.label);
            container.appendChild(btn);
        });
    }

    function renderCustomPalette() {
        const container = document.getElementById('custom-palette-chips');
        const emptyMsg = document.getElementById('no-custom-chips-msg');
        container.innerHTML = '';

        if (customFields.length === 0) {
            emptyMsg.classList.remove('hidden');
        } else {
            emptyMsg.classList.add('hidden');
            customFields.forEach(cf => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-3 py-1.5 rounded-xl text-xs font-medium bg-primary/15 hover:bg-primary/25 text-emerald-300 border border-primary/30 hover:border-primary transition duration-150 flex items-center gap-1.5 shadow-sm cursor-pointer';
                btn.innerHTML = `
                    <span>${escapeHtml(cf.label)}</span>
                    <span class="text-primary font-bold text-xs">+</span>
                `;
                btn.onclick = () => addFieldToFormat('custom', cf.key, cf.label);
                container.appendChild(btn);
            });
        }
    }

    // --- MANIPULAÇÃO DO FORMATO ---
    function addFieldToFormat(source, key, label) {
        callNumberFormat.push({
            type: 'field',
            source: source,
            key: key,
            label: label,
            length: '',
            casing: 'lower' // Padrão minúsculas e sem acento conforme especificação
        });
        renderFormatList();
        updateHiddenInputs();
    }

    function addStaticSeparator(val) {
        callNumberFormat.push({
            type: 'static',
            value: val
        });
        renderFormatList();
        updateHiddenInputs();
    }

    function addCustomStaticSeparator() {
        const input = document.getElementById('custom-static-input');
        const val = input.value;
        if (val) {
            addStaticSeparator(val);
            input.value = '';
        }
    }

    function removeFormatItem(index) {
        callNumberFormat.splice(index, 1);
        renderFormatList();
        updateHiddenInputs();
    }

    function clearCallNumberFormat() {
        if (callNumberFormat.length > 0 && confirm('Limpar todos os elementos da regra do número de chamada?')) {
            callNumberFormat = [];
            renderFormatList();
            updateHiddenInputs();
        }
    }

    function moveFormatItem(index, direction) {
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= callNumberFormat.length) return;
        const temp = callNumberFormat[index];
        callNumberFormat[index] = callNumberFormat[newIndex];
        callNumberFormat[newIndex] = temp;
        renderFormatList();
        updateHiddenInputs();
    }

    function updateFormatItemLength(index, length) {
        callNumberFormat[index].length = length ? parseInt(length) : '';
        updatePreview();
        updateHiddenInputs();
    }

    function updateFormatItemCasing(index, casing) {
        callNumberFormat[index].casing = casing;
        updatePreview();
        updateHiddenInputs();
    }

    function updateFormatItemStaticValue(index, val) {
        callNumberFormat[index].value = val;
        updatePreview();
        updateHiddenInputs();
    }

    // --- RENDERIZAÇÃO DA LISTA DE FORMATO ---
    function renderFormatList() {
        const listEl = document.getElementById('call-number-format-list');
        const emptyEl = document.getElementById('format-empty-msg');
        listEl.innerHTML = '';

        if (callNumberFormat.length === 0) {
            emptyEl.classList.remove('hidden');
        } else {
            emptyEl.classList.add('hidden');
            callNumberFormat.forEach((item, index) => {
                const el = document.createElement('div');
                el.className = 'flex items-center justify-between gap-3 p-3 bg-slate-900/60 border border-slate-800 hover:border-slate-700 rounded-xl text-xs transition select-none';
                el.draggable = true;
                el.dataset.index = index;

                // Drag and Drop Events
                el.ondragstart = (e) => {
                    draggedItemIndex = index;
                    e.dataTransfer.effectAllowed = 'move';
                    el.classList.add('opacity-40');
                };
                el.ondragend = () => {
                    draggedItemIndex = null;
                    el.classList.remove('opacity-40');
                };

                let contentHtml = '';
                if (item.type === 'static') {
                    contentHtml = `
                        <div class="flex items-center gap-2 flex-1">
                            <span class="cursor-grab text-slate-500 hover:text-slate-300">⋮⋮</span>
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono text-[10px] uppercase font-bold">Separador</span>
                            <input type="text" value="${escapeHtml(item.value)}" onchange="updateFormatItemStaticValue(${index}, this.value)" class="w-16 bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-xs text-slate-100 font-mono text-center focus:outline-none focus:border-primary">
                        </div>
                    `;
                } else {
                    const badgeClass = item.source === 'custom' ? 'bg-primary/20 text-emerald-300' : 'bg-slate-800 text-slate-300';
                    const badgeLabel = item.source === 'custom' ? 'Personalizado' : 'Padrão';

                    contentHtml = `
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="cursor-grab text-slate-500 hover:text-slate-300 flex-shrink-0">⋮⋮</span>
                            <div class="truncate min-w-0">
                                <span class="font-semibold text-slate-100 truncate block">${escapeHtml(item.label || item.key)}</span>
                                <span class="text-[9px] px-1.5 py-0.2 rounded font-mono ${badgeClass}">${badgeLabel}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div class="flex items-center gap-1">
                                <label class="text-[10px] text-slate-400">Tam:</label>
                                <input type="number" min="1" max="100" placeholder="Tudo" value="${item.length || ''}" oninput="updateFormatItemLength(${index}, this.value)" class="w-14 bg-slate-900 border border-slate-800 rounded-lg px-1.5 py-1 text-xs text-slate-100 text-center focus:outline-none focus:border-primary">
                            </div>
                            <div class="flex items-center gap-1">
                                <select onchange="updateFormatItemCasing(${index}, this.value)" class="bg-slate-900 border border-slate-800 rounded-lg px-1.5 py-1 text-[11px] text-slate-200 focus:outline-none focus:border-primary">
                                    <option value="lower" ${item.casing === 'lower' ? 'selected' : ''}>a-z (minúsculas)</option>
                                    <option value="upper" ${item.casing === 'upper' ? 'selected' : ''}>A-Z (maiúsculas)</option>
                                    <option value="original" ${item.casing === 'original' ? 'selected' : ''}>Original</option>
                                </select>
                            </div>
                        </div>
                    `;
                }

                // Controles de Ordenação e Exclusão
                el.innerHTML = `
                    ${contentHtml}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" onclick="moveFormatItem(${index}, -1)" ${index === 0 ? 'disabled' : ''} class="text-slate-400 hover:text-slate-200 p-1 rounded hover:bg-slate-800 transition duration-150 disabled:opacity-20 disabled:cursor-not-allowed" title="{{ __('Mover para cima') }}">
                            ▲
                        </button>
                        <button type="button" onclick="moveFormatItem(${index}, 1)" ${index === callNumberFormat.length - 1 ? 'disabled' : ''} class="text-slate-400 hover:text-slate-200 p-1 rounded hover:bg-slate-800 transition duration-150 disabled:opacity-20 disabled:cursor-not-allowed" title="{{ __('Mover para baixo') }}">
                            ▼
                        </button>
                        <button type="button" onclick="removeFormatItem(${index})" class="text-slate-400 hover:text-red-400 p-1 rounded hover:bg-red-950/30 transition duration-150" title="{{ __('Remover') }}">
                            ✕
                        </button>
                    </div>
                `;

                listEl.appendChild(el);
            });
        }

        updatePreview();
    }

    // Drag Over e Drop na lista
    function handleFormatDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        document.getElementById('call-number-format-list').classList.add('border-primary');
    }

    function handleFormatDragLeave(e) {
        document.getElementById('call-number-format-list').classList.remove('border-primary');
    }

    function handleFormatDrop(e) {
        e.preventDefault();
        document.getElementById('call-number-format-list').classList.remove('border-primary');
        if (draggedItemIndex === null) return;

        const targetCard = e.target.closest('[data-index]');
        if (targetCard) {
            const targetIndex = parseInt(targetCard.dataset.index);
            if (!isNaN(targetIndex) && targetIndex !== draggedItemIndex) {
                const movedItem = callNumberFormat.splice(draggedItemIndex, 1)[0];
                callNumberFormat.splice(targetIndex, 0, movedItem);
                renderFormatList();
                updateHiddenInputs();
            }
        }
    }

    // --- SIMULAÇÃO / PRÉ-VISUALIZAÇÃO EM TEMPO REAL ---
    function updatePreview() {
        const previewEl = document.getElementById('call-number-preview');
        if (!callNumberFormat || callNumberFormat.length === 0) {
            previewEl.textContent = '---';
            return;
        }

        const sample = {
            title: 'My Book Title',
            author: 'Aronofsky',
            publisher: 'Companhia',
            isbn: '9788535902778',
            publication_year: '2007',
            pages: '320',
            subjects: 'Che Guevara, Biografia',
            description: 'Biografia de liderança histórica.'
        };

        let parts = [];
        callNumberFormat.forEach(el => {
            if (el.type === 'static') {
                if (el.value) {
                    parts.push({ is_static: true, val: el.value });
                }
            } else if (el.type === 'field') {
                let raw = '';
                if (el.source === 'standard') {
                    raw = sample[el.key] || '';
                } else if (el.source === 'custom') {
                    raw = '001';
                }

                // Normalização ASCII (remover acentos)
                let clean = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                if (el.length && parseInt(el.length) > 0) {
                    clean = clean.substring(0, parseInt(el.length));
                }

                if (el.casing === 'upper') {
                    clean = clean.toUpperCase();
                } else if (el.casing === 'original') {
                    // mantém
                } else {
                    clean = clean.toLowerCase(); // padrão minúsculas
                }

                clean = clean.trim();
                if (clean !== '') {
                    parts.push({ is_static: false, val: clean });
                }
            }
        });

        let result = '';
        let lastWasStatic = false;
        parts.forEach(p => {
            if (p.is_static) {
                if (result !== '' && !lastWasStatic) {
                    result += p.val;
                    lastWasStatic = true;
                }
            } else {
                result += p.val;
                lastWasStatic = false;
            }
        });

        // Limpa separadores no final
        result = result.replace(/[\s\-\/\.]+$/, '');
        previewEl.textContent = result || '---';
    }

    // Atualiza os inputs hidden do formulário para salvar no banco
    function updateHiddenInputs() {
        document.getElementById('custom_fields_json').value = JSON.stringify(customFields);
        document.getElementById('call_number_format_json').value = JSON.stringify(callNumberFormat);
    }

    function submitRecalculateCallNumbers() {
        if (confirm('{{ __('Deseja realmente recalcular o número de chamada de todos os exemplares desta biblioteca? Esta ação atualizará os números de chamada existentes conforme a regra atual.') }}')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(!empty($library) ? route('libraries.recalculate_call_numbers', $library->id) : '');
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
