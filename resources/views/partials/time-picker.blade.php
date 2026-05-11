{{--
    Time picker — 3 fully-styled custom dropdowns (HH : MM AM/PM) + "now".

    Single x-data scope (window.tp). NO nested x-data — Alpine v3's nested
    scope inheritance is unreliable for closures, so all open flags live
    on the parent component. Outside-click closes any open dropdown.

    Usage:
        @include('partials.time-picker', [
            'get' => '() => form.checkin_start_at',
            'set' => 'v => { form.checkin_start_at = v }',
        ])
--}}
<div x-data="tp({!! $get !!}, {!! $set !!})" x-init="init()" dir="ltr"
     @click.outside="closeAll()"
     class="inline-flex items-center gap-1.5">

    {{-- Hour --}}
    <div class="relative">
        <button type="button" @click.stop="toggle('openH')" class="rs-time-trigger" :data-open="openH">
            <span class="rs-time-value" x-text="h || '--'"></span>
            <span class="material-symbols-outlined rs-time-chevron">expand_more</span>
        </button>
        <div x-show="openH" style="display:none"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="rs-time-dropdown">
            <template x-for="hh in hours" :key="hh">
                <button type="button"
                        @click="h = hh; openH = false"
                        class="rs-time-option"
                        :class="h === hh ? 'selected' : ''"
                        x-text="hh"></button>
            </template>
        </div>
    </div>

    <span class="rs-time-colon">:</span>

    {{-- Minute --}}
    <div class="relative">
        <button type="button" @click.stop="toggle('openM')" class="rs-time-trigger" :data-open="openM">
            <span class="rs-time-value" x-text="m || '--'"></span>
            <span class="material-symbols-outlined rs-time-chevron">expand_more</span>
        </button>
        <div x-show="openM" style="display:none"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="rs-time-dropdown">
            <template x-for="mm in minutes" :key="mm">
                <button type="button"
                        @click="m = mm; openM = false"
                        class="rs-time-option"
                        :class="m === mm ? 'selected' : ''"
                        x-text="mm"></button>
            </template>
        </div>
    </div>

    {{-- AM/PM --}}
    <div class="relative">
        <button type="button" @click.stop="toggle('openAp')" class="rs-time-trigger" :data-open="openAp">
            <span class="rs-time-value" x-text="ap || '--'"></span>
            <span class="material-symbols-outlined rs-time-chevron">expand_more</span>
        </button>
        <div x-show="openAp" style="display:none"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="rs-time-dropdown">
            <button type="button" @click="ap = 'AM'; openAp = false"
                    class="rs-time-option" :class="ap === 'AM' ? 'selected' : ''">AM</button>
            <button type="button" @click="ap = 'PM'; openAp = false"
                    class="rs-time-option" :class="ap === 'PM' ? 'selected' : ''">PM</button>
        </div>
    </div>

    {{-- Now --}}
    <button type="button" @click="setCurrent()" title="الوقت الحالي" class="rs-time-now">
        <span class="material-symbols-outlined text-[14px]">schedule</span>
    </button>
</div>
