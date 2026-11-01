@props(['value' => '', 'disabled' => false])

@php
    $phoneValue = old('phone', $value ?? '');
    $currentLocale = App::getLocale();
    
    // Determine selected DDI
    $selectedDdi = '+55'; // default pt_BR
    if ($phoneValue) {
        if (str_starts_with($phoneValue, '+55')) $selectedDdi = '+55';
        elseif (str_starts_with($phoneValue, '+1')) $selectedDdi = '+1';
        elseif (str_starts_with($phoneValue, '+34')) $selectedDdi = '+34';
        elseif (str_starts_with($phoneValue, '+351')) $selectedDdi = '+351';
        elseif (str_starts_with($phoneValue, '+54')) $selectedDdi = '+54';
        elseif (str_starts_with($phoneValue, '+52')) $selectedDdi = '+52';
        elseif (str_starts_with($phoneValue, '+44')) $selectedDdi = '+44';
        elseif (str_starts_with($phoneValue, '+33')) $selectedDdi = '+33';
        elseif (str_starts_with($phoneValue, '+49')) $selectedDdi = '+49';
        elseif (str_starts_with($phoneValue, '+39')) $selectedDdi = '+39';
        else $selectedDdi = 'other';
    } else {
        if ($currentLocale === 'en_US') $selectedDdi = '+1';
        elseif ($currentLocale === 'es') $selectedDdi = '+34';
        else $selectedDdi = '+55';
    }
@endphp

<div class="space-y-1.5">
    <label for="phone_display" class="block text-xs uppercase font-bold text-slate-400 tracking-wider">{{ __('Telefone Celular') }}</label>
    <div class="flex gap-2">
        <select id="country_code_select" onchange="updatePhoneMask()" {{ $disabled ? 'disabled' : '' }}
            class="bg-slate-950 border border-slate-800/80 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-blue-500 disabled:opacity-60 disabled:cursor-not-allowed transition cursor-pointer flex-shrink-0">
            <option value="+55" {{ $selectedDdi === '+55' ? 'selected' : '' }}>🇧🇷 +55 (Brasil)</option>
            <option value="+1" {{ $selectedDdi === '+1' ? 'selected' : '' }}>🇺🇸 +1 (EUA / Canadá)</option>
            <option value="+34" {{ $selectedDdi === '+34' ? 'selected' : '' }}>🇪🇸 +34 (Espanha)</option>
            <option value="+351" {{ $selectedDdi === '+351' ? 'selected' : '' }}>🇵🇹 +351 (Portugal)</option>
            <option value="+54" {{ $selectedDdi === '+54' ? 'selected' : '' }}>🇦🇷 +54 (Argentina)</option>
            <option value="+52" {{ $selectedDdi === '+52' ? 'selected' : '' }}>🇲🇽 +52 (México)</option>
            <option value="+44" {{ $selectedDdi === '+44' ? 'selected' : '' }}>🇬🇧 +44 (Reino Unido)</option>
            <option value="+33" {{ $selectedDdi === '+33' ? 'selected' : '' }}>🇫🇷 +33 (França)</option>
            <option value="+49" {{ $selectedDdi === '+49' ? 'selected' : '' }}>🇩🇪 +49 (Alemanha)</option>
            <option value="+39" {{ $selectedDdi === '+39' ? 'selected' : '' }}>🇮🇹 +39 (Itália)</option>
            <option value="other" {{ $selectedDdi === 'other' ? 'selected' : '' }}>{{ __('Outro Código') }}</option>
        </select>
        
        <input type="text" id="phone_display" oninput="formatPhoneInput()" placeholder="+55 (11) 99999-9999" value="{{ $phoneValue }}" {{ $disabled ? 'disabled' : '' }}
            class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 disabled:opacity-60 disabled:cursor-not-allowed transition">
        
        <input type="hidden" id="phone" name="phone" value="{{ $phoneValue }}">
    </div>
    @error('phone') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
</div>

<script>
    function updatePhoneMask() {
        formatPhoneInput();
    }

    function formatPhoneInput() {
        const ddiSelect = document.getElementById('country_code_select');
        const phoneDisplay = document.getElementById('phone_display');
        const phoneHidden = document.getElementById('phone');
        if (!ddiSelect || !phoneDisplay || !phoneHidden) return;

        const selectedDdi = ddiSelect.value;
        let rawVal = phoneDisplay.value;

        let digits = rawVal.replace(/\D/g, '');
        let formatted = '';

        if (selectedDdi === '+55') {
            // Strip leading 55 if user typed +55
            if (digits.startsWith('55') && digits.length > 2) {
                digits = digits.substring(2);
            }
            if (digits.length === 0) formatted = '';
            else if (digits.length <= 2) formatted = `+55 (${digits}`;
            else if (digits.length <= 6) formatted = `+55 (${digits.slice(0, 2)}) ${digits.slice(2)}`;
            else if (digits.length <= 10) formatted = `+55 (${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
            else formatted = `+55 (${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7, 11)}`;
            phoneDisplay.placeholder = "+55 (11) 99999-9999";
        } else if (selectedDdi === '+1') {
            // Strip leading 1
            if (digits.startsWith('1') && digits.length > 1) {
                digits = digits.substring(1);
            }
            if (digits.length === 0) formatted = '';
            else if (digits.length <= 3) formatted = `+1 (${digits}`;
            else if (digits.length <= 6) formatted = `+1 (${digits.slice(0, 3)}) ${digits.slice(3)}`;
            else formatted = `+1 (${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
            phoneDisplay.placeholder = "+1 (555) 000-0000";
        } else if (selectedDdi === '+34') {
            // Strip leading 34
            if (digits.startsWith('34') && digits.length > 2) {
                digits = digits.substring(2);
            }
            if (digits.length === 0) formatted = '';
            else if (digits.length <= 3) formatted = `+34 ${digits}`;
            else if (digits.length <= 5) formatted = `+34 ${digits.slice(0, 3)} ${digits.slice(3)}`;
            else if (digits.length <= 7) formatted = `+34 ${digits.slice(0, 3)} ${digits.slice(3, 5)} ${digits.slice(5)}`;
            else formatted = `+34 ${digits.slice(0, 3)} ${digits.slice(3, 5)} ${digits.slice(5, 7)} ${digits.slice(7, 9)}`;
            phoneDisplay.placeholder = "+34 600 00 00 00";
        } else if (selectedDdi === 'other') {
            formatted = rawVal.startsWith('+') ? rawVal : (digits ? '+' + digits : '');
            phoneDisplay.placeholder = "+XX XXX XXX XXXX";
        } else {
            // Standard international code fallback
            const ddiDigits = selectedDdi.replace(/\D/g, '');
            if (digits.startsWith(ddiDigits) && digits.length > ddiDigits.length) {
                digits = digits.substring(ddiDigits.length);
            }
            if (digits.length === 0) formatted = '';
            else if (digits.length <= 3) formatted = `${selectedDdi} ${digits}`;
            else if (digits.length <= 6) formatted = `${selectedDdi} ${digits.slice(0, 3)} ${digits.slice(3)}`;
            else if (digits.length <= 9) formatted = `${selectedDdi} ${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`;
            else formatted = `${selectedDdi} ${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6, 10)}`;
            phoneDisplay.placeholder = `${selectedDdi} 999 999 9999`;
        }

        phoneDisplay.value = formatted;
        phoneHidden.value = formatted;
    }

    document.addEventListener('DOMContentLoaded', function() {
        formatPhoneInput();
    });
</script>
