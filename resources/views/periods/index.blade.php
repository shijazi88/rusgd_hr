@extends('layouts.app')
@section('title', 'الفترات')

@section('content')
<div x-data="periodsPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1200px] mx-auto px-8 pt-8 pb-20">

        {{-- Header --}}
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-[20px] font-bold font-h2 text-on-surface mb-1">إعداد الفترات</h1>
                <p class="text-[12px] text-on-surface-variant font-body-secondary">نوافذ الدخول/الخروج، فترات السماح، وقواعد خصم التأخير</p>
            </div>
            <button @click="openCreate()"
                    class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all flex items-center gap-2 text-sm">
                فترة جديدة <span class="text-xl leading-none">+</span>
            </button>
        </div>

        {{-- Periods grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-if="loading">
                <div class="col-span-full flex justify-center py-12">
                    <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                </div>
            </template>

            <template x-for="p in periods" :key="p.id">
                <div class="bg-surface-container rounded-[14px] p-5 border border-outline-variant hover:border-primary-container/30 transition-all flex flex-col gap-3"
                     :class="p.is_stopped ? 'opacity-60' : ''"
                     style="box-shadow: var(--c-shadow); border-top: 3px solid"
                     :style="'border-top-color:' + (p.color || '#0EA5A4')">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-body-main text-on-surface font-semibold text-[15px] truncate" x-text="p.name"></p>
                            <p class="font-body-secondary text-on-surface-variant text-xs mt-0.5" dir="ltr"
                               x-text="(p.checkin?.start_at||'?') + ' → ' + (p.checkout?.end_at||'?')"></p>
                        </div>
                        <div class="flex items-center gap-1 text-on-surface-variant shrink-0">
                            <button @click="openEdit(p)" class="p-2 hover:text-primary-container hover:bg-surface-container-high rounded-full transition-colors">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button @click="deletePeriod(p)" class="p-2 hover:text-error hover:bg-error/10 rounded-full transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <span class="px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant"
                              x-text="window.fmtMinutes(p.total_work_minutes) + ' من العمل'"></span>
                        <template x-if="p.late_tiers?.length">
                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500"
                                  x-text="p.late_tiers.length + ' قاعدة تأخير'"></span>
                        </template>
                        <template x-if="p.is_stopped">
                            <span class="px-2 py-0.5 rounded bg-red-500/10 text-red-500">موقوفة</span>
                        </template>
                        <template x-if="p.allow_no_fingerprint">
                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-500">بدون بصمات</span>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="!loading && periods.length === 0">
                <div class="col-span-full text-center text-on-surface-variant text-sm py-12">
                    لا توجد فترات مسجّلة. ابدأ بإضافة فترة جديدة.
                </div>
            </template>
        </div>
    </main>

    {{-- Period Editor Modal --}}
    <div x-show="showModal" style="display:none;"
         class="fixed inset-0 bg-black/70 backdrop-blur-[4px] z-50 flex items-center justify-center p-4">
        <div class="bg-surface-container w-full max-w-[760px] max-h-[90vh] overflow-y-auto rounded-[20px] shadow-2xl border border-outline-variant flex flex-col">

            <div class="px-6 py-5 border-b border-outline-variant flex justify-between items-center border-r-4 border-r-primary-container bg-surface-container-low rounded-t-[20px] sticky top-0 z-10">
                <h3 class="font-h2 text-on-surface" x-text="editingId ? 'تعديل الفترة' : 'فترة جديدة'"></h3>
                <button @click="showModal=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-6">

                {{-- Basic --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1.5">اسم الفترة <span class="text-error">*</span></label>
                        <input x-model="form.name" type="text" placeholder="مثال: دوام صباحي"
                               class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                    </div>
                    {{-- Total work time is irrelevant when "حضور بدون بصمات" is on
                         (no tracking at all). For "فترة مفتوحة" it IS still needed
                         (that's the only requirement in that mode). --}}
                    <div x-show="!form.allow_no_fingerprint" style="display:none">
                        <label class="block text-sm text-on-surface-variant mb-1.5">إجمالي وقت العمل</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <input :value="workHoursPart" @input="setWorkHours($event.target.value)"
                                       type="number" min="0" max="24"
                                       class="rs-num-input w-full bg-surface border border-outline-variant rounded-lg py-2.5 pr-4 pl-14 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant pointer-events-none">ساعة</span>
                            </div>
                            <div class="relative">
                                <input :value="workMinutesPart" @input="setWorkMinutes($event.target.value)"
                                       type="number" min="0" max="59"
                                       class="rs-num-input w-full bg-surface border border-outline-variant rounded-lg py-2.5 pr-4 pl-14 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant pointer-events-none">دقيقة</span>
                            </div>
                        </div>
                        <p class="text-[11px] text-on-surface-variant mt-1" x-text="'الإجمالي: ' + window.fmtMinutes(form.total_work_minutes || 0)"></p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input x-model="form.is_open_period" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span class="text-on-surface">فترة مفتوحة</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input x-model="form.allow_no_fingerprint" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span class="text-on-surface">حضور بدون بصمات</span>
                    </label>
                    {{-- Stop flag only makes sense for existing periods (deactivation).
                         Hidden in create mode — you can't pause something not saved yet. --}}
                    <label x-show="editingId" style="display:none" class="flex items-center gap-2 cursor-pointer">
                        <input x-model="form.is_stopped" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span class="text-on-surface">إيقاف</span>
                    </label>
                </div>

                {{-- No-fingerprint explainer: typically for managers who aren't on
                     fingerprint attendance at all. Takes precedence over open-period
                     because it's even more permissive — no rules whatsoever. --}}
                <div x-show="form.allow_no_fingerprint" style="display:none"
                     class="bg-violet-500/10 border border-violet-500/30 rounded-lg p-4 flex items-start gap-3">
                    <span class="material-symbols-outlined text-violet-500 text-[20px] mt-0.5"
                          style="font-variation-settings:'FILL' 1">workspace_premium</span>
                    <div class="text-sm text-on-surface leading-relaxed">
                        <p class="font-bold mb-1">حضور بدون بصمات</p>
                        <p class="text-on-surface-variant text-xs">
                            هذا الخيار <span class="text-on-surface font-bold">لا علاقة له بالفترات</span> —
                            يُستخدم عادةً للمديرين الذين لا يسجّلون حضورهم ببصمة.
                            لن تُطبَّق أي قواعد للدخول/الخروج أو خصومات تأخير على الموظفين المرتبطين بهذه الفترة.
                        </p>
                    </div>
                </div>

                {{-- Open-period explainer: shown only when "فترة مفتوحة" is checked
                     AND we're NOT in no-fingerprint mode (no-fingerprint is broader). --}}
                <div x-show="form.is_open_period && !form.allow_no_fingerprint" style="display:none"
                     class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-500 text-[20px] mt-0.5">schedule</span>
                    <div class="text-sm text-on-surface leading-relaxed">
                        <p class="font-bold mb-1">الفترة المفتوحة مُفعّلة</p>
                        <p class="text-on-surface-variant text-xs">
                            في هذه الفترة، الموظف يستطيع الدخول والخروج في أي وقت — المهم أن يكمل
                            <span class="text-on-surface font-bold" x-text="window.fmtMinutes(form.total_work_minutes)"></span>
                            من العمل بين الدخول والخروج. لا تطبق قواعد التأخير ولا أوقات الدخول/الخروج المحددة.
                        </p>
                    </div>
                </div>

                {{-- Check-in --}}
                <fieldset x-show="!form.is_open_period && !form.allow_no_fingerprint" style="display:none"
                          class="border border-outline-variant rounded-[10px] p-4">
                    <legend class="px-2 text-sm font-bold text-on-surface flex items-center gap-2">
                        <input x-model="form.checkin_required" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span>يجب الدخول</span>
                    </legend>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">أدنى وقت مسموح للدخول</label>
                            @include('partials.time-picker', ['get' => '() => form.checkin_earliest_at', 'set' => 'v => { form.checkin_earliest_at = v }'])
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">بداية الدخول</label>
                            @include('partials.time-picker', ['get' => '() => form.checkin_start_at', 'set' => 'v => { form.checkin_start_at = v }'])
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">نهاية الدخول</label>
                            @include('partials.time-picker', ['get' => '() => form.checkin_end_at', 'set' => 'v => { form.checkin_end_at = v }'])
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">أعلى وقت مسموح للدخول</label>
                            @include('partials.time-picker', ['get' => '() => form.checkin_latest_at', 'set' => 'v => { form.checkin_latest_at = v }'])
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input x-model="form.checkin_absence_without_perm" type="checkbox" class="w-4 h-4 accent-primary-container">
                            <span class="text-on-surface">غياب بدون إذن</span>
                        </label>
                        <template x-if="form.checkin_absence_without_perm">
                            <div class="flex items-center gap-2">
                                <input x-model.number="form.checkin_absence_deduction" type="number" min="0" step="0.25" dir="ltr"
                                       class="w-20 bg-surface border border-outline-variant rounded px-2 py-1 text-on-surface text-xs">
                                <select x-model="form.checkin_absence_deduction_type"
                                        class="bg-surface border border-outline-variant rounded px-2 py-1 text-on-surface text-xs">
                                    <option value="hour">ساعة</option>
                                    <option value="day">يوم</option>
                                    <option value="fixed">مبلغ ثابت</option>
                                </select>
                            </div>
                        </template>
                    </div>
                </fieldset>

                {{-- Check-out --}}
                <fieldset x-show="!form.is_open_period && !form.allow_no_fingerprint" style="display:none"
                          class="border border-outline-variant rounded-[10px] p-4">
                    <legend class="px-2 text-sm font-bold text-on-surface flex items-center gap-2">
                        <input x-model="form.checkout_required" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span>يجب الخروج</span>
                    </legend>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">أدنى وقت مسموح للخروج</label>
                            @include('partials.time-picker', ['get' => '() => form.checkout_earliest_at', 'set' => 'v => { form.checkout_earliest_at = v }'])
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">بداية الخروج</label>
                            @include('partials.time-picker', ['get' => '() => form.checkout_start_at', 'set' => 'v => { form.checkout_start_at = v }'])
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">نهاية الخروج</label>
                            @include('partials.time-picker', ['get' => '() => form.checkout_end_at', 'set' => 'v => { form.checkout_end_at = v }'])
                        </div>
                        <div>
                            <label class="block text-xs text-on-surface-variant mb-1">أعلى وقت مسموح للخروج</label>
                            @include('partials.time-picker', ['get' => '() => form.checkout_latest_at', 'set' => 'v => { form.checkout_latest_at = v }'])
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="flex items-center gap-2 cursor-pointer text-xs">
                            <input x-model="form.checkout_next_day" type="checkbox" class="w-4 h-4 accent-primary-container">
                            <span class="text-on-surface">الخروج في اليوم التالي</span>
                        </label>
                    </div>
                </fieldset>

                {{-- Late Tiers — irrelevant for open periods --}}
                <div x-show="!form.is_open_period && !form.allow_no_fingerprint" style="display:none">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500 text-[18px]">timer</span>
                            قواعد خصم التأخير
                        </h4>
                        <button @click="addTier()" type="button"
                                class="text-xs text-primary-container hover:underline font-semibold">+ إضافة قاعدة</button>
                    </div>
                    <div class="border border-outline-variant rounded-[10px] overflow-hidden">
                        <table class="w-full text-right text-sm">
                            <thead>
                                <tr>
                                    <th class="p-2 text-xs font-bold">من الوقت</th>
                                    <th class="p-2 text-xs font-bold">إلى الوقت</th>
                                    <th class="p-2 text-xs font-bold">مقدار الخصم</th>
                                    <th class="p-2 text-xs font-bold">نوع الخصم</th>
                                    <th class="p-2 text-xs font-bold"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                <template x-for="(t, i) in form.late_tiers" :key="i">
                                    <tr>
                                        <td class="p-2">@include('partials.time-picker', ['get' => '() => t.from_time', 'set' => 'v => { t.from_time = v }'])</td>
                                        <td class="p-2">@include('partials.time-picker', ['get' => '() => t.to_time',   'set' => 'v => { t.to_time = v }'])</td>
                                        <td class="p-2"><input x-model.number="t.deduction_amount" type="number" min="0" step="0.25" dir="ltr" class="rs-time-input"></td>
                                        <td class="p-2">
                                            <select x-model="t.deduction_type"
                                                    class="w-full bg-surface border border-outline-variant rounded px-2 py-1.5 text-on-surface text-xs">
                                                <option value="hour">ساعة</option>
                                                <option value="day">يوم</option>
                                                <option value="fixed">مبلغ ثابت</option>
                                                <option value="absence">غياب</option>
                                            </select>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button @click="removeTier(i)" type="button" class="text-on-surface-variant hover:text-error">
                                                <span class="material-symbols-outlined text-[18px]">close</span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="form.late_tiers.length === 0">
                                    <tr><td colspan="5" class="p-4 text-center text-on-surface-variant text-xs">لا قواعد بعد. أضِف قاعدة لخصم التأخير.</td></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="formErr" class="text-error text-sm" x-text="formErr"></div>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex justify-end gap-3 rounded-b-[20px] sticky bottom-0">
                <button @click="showModal=false" class="rs-btn-cancel px-5 py-2.5 text-sm font-semibold">إلغاء</button>
                <button @click="save()" :disabled="saving"
                        class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all text-sm disabled:opacity-60">
                    <span x-text="saving ? 'جارٍ الحفظ...' : 'حفظ'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.rs-time-input {
    width: 100%;
    background: var(--c-bg);
    border: 1px solid var(--c-border-h);
    border-radius: 6px;
    padding: 6px 8px;
    color: var(--c-text-1);
    font-size: 12px;
    font-family: 'JetBrains Mono', monospace;
}
.rs-time-input:focus {
    outline: none;
    border-color: #0ea5a4;
    box-shadow: 0 0 0 2px rgba(14,165,164,0.15);
}
</style>
@endsection

@push('scripts')
<script>
function periodsPage() {
    const empty = () => ({
        name: '', color: '#0EA5A4',
        is_open_period: false, allow_no_fingerprint: false, is_stopped: false,
        checkin_required: true,
        checkin_earliest_at: '06:30:00', checkin_start_at: '08:00:00',
        checkin_end_at: '09:00:00', checkin_latest_at: '11:00:00',
        checkin_after_grace_action: 'entry_only', checkin_after_end_action: 'late_attendance',
        checkin_absence_without_perm: false, checkin_absence_deduction: 0, checkin_absence_deduction_type: 'day',
        checkout_required: true,
        checkout_earliest_at: '14:59:00', checkout_start_at: '15:00:00',
        checkout_end_at: '16:30:00', checkout_latest_at: '17:30:00',
        checkout_after_grace_action: 'exit_only', checkout_next_day: false,
        checkout_absence_without_perm: false, checkout_absence_deduction: 0, checkout_absence_deduction_type: 'day',
        total_work_minutes: 420,
        late_tiers: [],
    });

    return {
        periods: [], loading: false,
        showModal: false, editingId: null, saving: false, formErr: '',
        form: empty(),

        // Split total_work_minutes into editable H/M fields. The stored value
        // stays in minutes — these getters derive the parts on the fly so the
        // form has one source of truth.
        get workHoursPart()   { return Math.floor((this.form.total_work_minutes || 0) / 60); },
        get workMinutesPart() { return (this.form.total_work_minutes || 0) % 60; },
        setWorkHours(v) {
            const h = Math.max(0, Math.min(24, parseInt(v || 0, 10) || 0));
            const m = this.workMinutesPart;
            this.form.total_work_minutes = (h * 60) + m;
        },
        setWorkMinutes(v) {
            const m = Math.max(0, Math.min(59, parseInt(v || 0, 10) || 0));
            const h = this.workHoursPart;
            this.form.total_work_minutes = (h * 60) + m;
        },

        async init() {
            if (await window.$guard('manage_periods')) return;
            await this.fetchPeriods();
        },

        async fetchPeriods() {
            this.loading = true;
            try {
                const r = await axios.get('/api/v1/periods?include_stopped=1');
                if (r.data.success) this.periods = r.data.data || [];
            } catch(e){}
            finally { this.loading = false; }
        },

        openCreate() {
            this.editingId = null;
            this.form = empty();
            this.formErr = '';
            this.showModal = true;
        },

        openEdit(p) {
            this.editingId = p.id;
            this.form = {
                name: p.name, color: p.color || '#0EA5A4',
                is_open_period: !!p.is_open_period,
                allow_no_fingerprint: !!p.allow_no_fingerprint,
                is_stopped: !!p.is_stopped,
                checkin_required: !!p.checkin?.required,
                checkin_earliest_at: p.checkin?.earliest_at || '',
                checkin_start_at:    p.checkin?.start_at    || '',
                checkin_end_at:      p.checkin?.end_at      || '',
                checkin_latest_at:   p.checkin?.latest_at   || '',
                checkin_after_grace_action: p.checkin?.after_grace_action || 'entry_only',
                checkin_after_end_action:   p.checkin?.after_end_action   || 'late_attendance',
                checkin_absence_without_perm:   !!p.checkin?.absence_without_perm,
                checkin_absence_deduction:      Number(p.checkin?.absence_deduction || 0),
                checkin_absence_deduction_type: p.checkin?.absence_deduction_type || 'day',
                checkout_required: !!p.checkout?.required,
                checkout_earliest_at: p.checkout?.earliest_at || '',
                checkout_start_at:    p.checkout?.start_at    || '',
                checkout_end_at:      p.checkout?.end_at      || '',
                checkout_latest_at:   p.checkout?.latest_at   || '',
                checkout_after_grace_action: p.checkout?.after_grace_action || 'exit_only',
                checkout_next_day:    !!p.checkout?.next_day,
                checkout_absence_without_perm:   !!p.checkout?.absence_without_perm,
                checkout_absence_deduction:      Number(p.checkout?.absence_deduction || 0),
                checkout_absence_deduction_type: p.checkout?.absence_deduction_type || 'day',
                total_work_minutes: p.total_work_minutes || 420,
                late_tiers: (p.late_tiers || []).map(t => ({
                    from_time: t.from_time,
                    to_time: t.to_time,
                    deduction_amount: Number(t.deduction_amount),
                    deduction_type: t.deduction_type,
                    min_occurrences: t.min_occurrences || 0,
                })),
            };
            this.formErr = '';
            this.showModal = true;
        },

        addTier() {
            this.form.late_tiers.push({
                from_time: '09:01:00', to_time: '09:30:00',
                deduction_amount: 1, deduction_type: 'hour', min_occurrences: 0,
            });
        },
        removeTier(i) { this.form.late_tiers.splice(i, 1); },

        async save() {
            this.formErr = ''; this.saving = true;
            // Send empty strings as nulls so the backend accepts them
            const payload = JSON.parse(JSON.stringify(this.form));
            for (const k of Object.keys(payload)) {
                if (payload[k] === '') payload[k] = null;
            }
            try {
                if (this.editingId) {
                    await axios.put('/api/v1/periods/' + this.editingId, payload);
                } else {
                    await axios.post('/api/v1/periods', payload);
                }
                this.showModal = false;
                await this.fetchPeriods();
            } catch(e) {
                const errs = e.response?.data?.errors;
                this.formErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'حدث خطأ');
            } finally { this.saving = false; }
        },

        async deletePeriod(p) {
            if (!confirm('هل أنت متأكد من حذف الفترة "' + p.name + '"؟')) return;
            try {
                await axios.delete('/api/v1/periods/' + p.id);
                await this.fetchPeriods();
            } catch(e) {
                alert(e.response?.data?.message || 'لا يمكن حذف الفترة. أوقفها بدلاً من ذلك.');
            }
        },
    };
}
</script>
@endpush
