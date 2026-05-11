{{--
    Time picker — 3 dropdowns (HH : MM AM/PM) + "now" button.

    Backed by window.tp() which keeps an internal 12-hour display synced
    with a 24-hour HH:MM:SS string in the parent form state.

    Usage:
        @include('partials.time-picker', [
            'get' => '() => form.checkin_start_at',
            'set' => 'v => { form.checkin_start_at = v }',
        ])

    `$get` and `$set` are injected unescaped — they're inline JS arrow
    functions, never user input, so XSS isn't a concern here.
--}}
<div x-data="tp({!! $get !!}, {!! $set !!})" x-init="init()" dir="ltr"
     class="inline-flex items-center gap-1.5">

    {{-- Hour --}}
    <select x-model="h" class="rs-time-select" aria-label="Hour">
        <template x-for="i in 12" :key="i">
            <option :value="String(i).padStart(2,'0')" x-text="String(i).padStart(2,'0')"></option>
        </template>
    </select>

    <span class="text-on-surface-variant font-mono text-sm font-bold select-none">:</span>

    {{-- Minute (0-59) --}}
    <select x-model="m" class="rs-time-select" aria-label="Minute">
        <template x-for="i in 60" :key="i - 1">
            <option :value="String(i - 1).padStart(2,'0')" x-text="String(i - 1).padStart(2,'0')"></option>
        </template>
    </select>

    {{-- AM/PM --}}
    <select x-model="ap" class="rs-time-select rs-time-ap" aria-label="AM or PM">
        <option value="AM">AM</option>
        <option value="PM">PM</option>
    </select>

    {{-- Now --}}
    <button type="button" @click="setCurrent()" title="الوقت الحالي" class="rs-time-now">
        <span class="material-symbols-outlined text-[14px]">schedule</span>
    </button>
</div>
