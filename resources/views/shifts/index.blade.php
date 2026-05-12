@extends('layouts.app')
@section('title', 'الورديات')

@section('content')
<div x-data="shiftsPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20 space-y-10">

        {{-- Header --}}
        <div class="flex justify-between items-end">
            <div>
                <h1 class="font-h1 text-[20px] font-bold text-on-surface">الورديات</h1>
                <p class="font-body-secondary text-[12px] text-on-surface-variant mt-1">جداول العمل الأسبوعية — كل وردية ترتبط بفترة (أو فترتين) لكل يوم</p>
            </div>
            <div class="flex gap-3">
                {{-- Manager role has manage_shifts but NOT manage_periods — hide the
                     link for them to avoid the /periods guard redirecting back home.
                     CEO/Director/HR all have manage_periods and see the link. --}}
                <a x-show="can('manage_periods')" href="/periods"
                   class="px-4 py-2.5 bg-amber-500/10 text-amber-500 border border-amber-500/30 hover:bg-amber-500 hover:text-white rounded-lg font-semibold text-sm transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">timer</span>
                    إعداد الفترات
                </a>
                <button @click="openCreate()" class="px-5 py-2.5 bg-primary-container text-white rounded-lg font-bold text-sm hover:brightness-110 transition-all flex items-center gap-2">
                    وردية جديدة <span class="text-xl leading-none">+</span>
                </button>
            </div>
        </div>

        {{-- Shifts list --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-if="loading">
                <div class="col-span-full flex justify-center py-12">
                    <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                </div>
            </template>

            <template x-for="shift in shifts" :key="shift.id">
                <div class="bg-surface-container border border-outline-variant rounded-[14px] p-5 hover:border-primary-container/30 transition-all flex flex-col gap-3 cursor-pointer"
                     :class="shift.is_stopped ? 'opacity-60' : ''"
                     :style="'border-top: 3px solid ' + (shift.color || '#0EA5A4') + '; box-shadow: var(--c-shadow)'"
                     @click="openEdit(shift)">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-on-surface font-semibold text-[15px] truncate" x-text="shift.name"></p>
                            <p class="text-on-surface-variant text-xs mt-0.5"
                               x-text="(shift.days?.filter(d => d.first_period_id || d.second_period_id)?.length || 0) + ' يوم عمل/أسبوع'"></p>
                        </div>
                        <div class="flex gap-1 shrink-0" @click.stop>
                            <button @click="openEdit(shift)" class="p-2 text-on-surface-variant hover:text-primary-container hover:bg-surface-container-high rounded-full transition-colors">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button @click="deleteShift(shift)" class="p-2 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-full transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1 pt-2 border-t border-outline-variant/50">
                        <template x-for="d in shift.days || []" :key="d.day_of_week">
                            <span class="text-[10px] px-1.5 py-0.5 rounded"
                                  :class="(d.first_period_id || d.second_period_id) ? 'bg-primary-container/10 text-primary-container' : 'bg-surface-container-high text-on-surface-variant'"
                                  x-text="dayShort(d.day_of_week)"></span>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="!loading && shifts.length === 0">
                <div class="col-span-full text-center text-on-surface-variant text-sm py-12">
                    لا توجد ورديات. أنشئ فترات أولاً، ثم أضف وردية تستخدمها.
                </div>
            </template>
        </div>

        {{-- Assignments Section --}}
        <section>
            <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden"
                 style="box-shadow: var(--c-shadow)">
                <div class="p-5 border-b border-outline-variant flex justify-between items-center">
                    <h2 class="font-h2 text-lg text-on-surface">توزيع الورديات على الموظفين</h2>
                    <button @click="openAssign()" class="px-4 py-2 bg-primary-container text-white rounded-lg font-semibold text-sm hover:brightness-110 transition-all">
                        + تعيين
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr>
                                <th class="p-5">الموظف</th>
                                <th class="p-5">الوردية</th>
                                <th class="p-5">من تاريخ</th>
                                <th class="p-5">إلى تاريخ</th>
                                <th class="p-5">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <template x-for="a in assignments" :key="a.id">
                                <tr class="hover:bg-surface-container-high transition-colors">
                                    <td class="p-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs border border-outline-variant text-primary-container"
                                                 style="background:rgba(14,165,164,0.08)"
                                                 x-text="initials(a.employee?.name)"></div>
                                            <span class="text-sm font-medium text-on-surface" x-text="a.employee?.name || '—'"></span>
                                        </div>
                                    </td>
                                    <td class="p-5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                              :style="'color:' + (a.shift?.color||'#0EA5A4') + '; background:' + (a.shift?.color||'#0EA5A4') + '1a'">
                                            <span class="w-1.5 h-1.5 rounded-full" :style="'background:' + (a.shift?.color||'#0EA5A4')"></span>
                                            <span x-text="a.shift?.name || '—'"></span>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm text-on-surface-variant" dir="ltr" x-text="a.from_date || '—'"></td>
                                    <td class="p-5 text-sm text-on-surface-variant" dir="ltr" x-text="a.to_date || '—'"></td>
                                    <td class="p-5">
                                        <button @click="deleteAssignment(a)"
                                                class="text-on-surface-variant hover:text-error hover:bg-error/10 p-1.5 rounded transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="assignments.length===0">
                                <tr><td colspan="5" class="p-8 text-center text-on-surface-variant text-sm">لا توجد تعيينات</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    {{-- Shift Editor Modal --}}
    <div x-show="showModal" style="display:none;"
         class="fixed inset-0 bg-black/70 backdrop-blur-[4px] z-50 flex items-center justify-center p-4">
        <div class="bg-surface-container w-full max-w-[920px] max-h-[90vh] overflow-y-auto rounded-[20px] shadow-2xl border border-outline-variant flex flex-col">

            <div class="px-6 py-5 border-b border-outline-variant flex justify-between items-center border-r-4 border-r-primary-container bg-surface-container-low rounded-t-[20px] sticky top-0 z-10">
                <h3 class="font-h2 text-on-surface" x-text="editingId ? 'تعديل الوردية' : 'وردية جديدة'"></h3>
                <button @click="showModal=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1.5">اسم الوردية <span class="text-error">*</span></label>
                        <input x-model="form.name" type="text" placeholder="مثال: دوام التعميم الجديد"
                               class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1.5">اللون</label>
                        <input x-model="form.color" type="color" class="w-20 h-10 rounded-lg cursor-pointer border border-outline-variant">
                    </div>
                </div>
                <div class="flex flex-wrap gap-4 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input x-model="form.show_additional_periods" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span class="text-on-surface">إضافة فترة ثانية لكل يوم (للورديات المقسومة)</span>
                    </label>
                    {{-- Stop only available when editing — can't deactivate a shift that isn't saved yet. --}}
                    <label x-show="editingId" style="display:none" class="flex items-center gap-2 cursor-pointer">
                        <input x-model="form.is_stopped" type="checkbox" class="w-4 h-4 accent-primary-container">
                        <span class="text-on-surface">إيقاف</span>
                    </label>
                </div>
                <p class="text-xs text-on-surface-variant -mt-3 flex items-start gap-1.5">
                    <span class="material-symbols-outlined text-[14px] mt-0.5">info</span>
                    <span>
                        <strong>الفترة الأولى/الثانية:</strong> اختياري — فعّل المربع أعلاه فقط إذا كان الموظف يعمل فترتين منفصلتين في اليوم.
                        <br>
                        <strong>المقابل:</strong> مضاعف الأجر اليومي — 1 = أجر عادي، 1.5 = أجر ونصف، 2 = ضعف الأجر. مناسب لأيام العطل أو الجمعة/السبت.
                    </span>
                </p>

                {{-- Weekly grid --}}
                <div class="border border-outline-variant rounded-[10px] overflow-hidden">
                    <table class="w-full text-right text-sm">
                        <thead>
                            <tr>
                                <th class="p-3 text-xs font-bold w-[110px]">اليوم</th>
                                <th class="p-3 text-xs font-bold">الفترة الأولى</th>
                                <th class="p-3 text-xs font-bold" x-show="form.show_additional_periods">الفترة الثانية</th>
                                <th class="p-3 text-xs font-bold w-[120px]">المقابل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <template x-for="(day, i) in form.days" :key="day.day_of_week">
                                <tr :class="(day.day_of_week==='sat'||day.day_of_week==='fri') ? 'bg-surface-container-low/40' : ''">
                                    <td class="p-3 font-bold text-on-surface" x-text="dayLong(day.day_of_week)"></td>
                                    <td class="p-3">
                                        <select x-model.number="day.first_period_id"
                                                class="w-full bg-surface border border-outline-variant rounded px-3 py-1.5 text-on-surface text-sm">
                                            <option :value="null">— عطلة —</option>
                                            <template x-for="p in periods" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="p-3" x-show="form.show_additional_periods">
                                        <select x-model.number="day.second_period_id"
                                                class="w-full bg-surface border border-outline-variant rounded px-3 py-1.5 text-on-surface text-sm"
                                                :disabled="!day.first_period_id">
                                            <option :value="null">— لا يوجد —</option>
                                            <template x-for="p in periods" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="p-3">
                                        <input x-model.number="day.multiplier" type="number" min="0" max="10" step="0.25" dir="ltr"
                                               class="w-full bg-surface border border-outline-variant rounded px-3 py-1.5 text-on-surface text-sm">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px] align-middle">info</span>
                    المقابل: 1 = راتب عادي، 1.5 = يدفع 1.5x، 2 = يدفع ضعف. مناسب للجمعة/السبت أو أيام الإجازات.
                </p>

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

    {{-- Assign Modal --}}
    <div x-show="showAssign" style="display:none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-surface-container w-full max-w-lg rounded-[20px] border border-outline-variant shadow-2xl flex flex-col">
            <div class="px-6 py-5 border-b border-outline-variant flex justify-between items-center border-r-4 border-r-primary-container bg-surface-container-low rounded-t-[20px]">
                <h3 class="font-h2 text-on-surface">تعيين وردية</h3>
                <button @click="showAssign=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 flex flex-col gap-5">
                <div>
                    <label class="block text-sm text-on-surface-variant mb-1.5">الموظف</label>
                    <select x-model="af.employee_id" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface text-sm">
                        <option value="">اختر موظف</option>
                        <template x-for="e in allEmployees" :key="e.id">
                            <option :value="e.id" x-text="e.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-on-surface-variant mb-1.5">الوردية</label>
                    <select x-model="af.shift_id" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface text-sm">
                        <option value="">اختر الوردية</option>
                        <template x-for="s in shifts" :key="s.id">
                            <option :value="s.id" x-text="s.name"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1.5">من تاريخ</label>
                        <input x-model="af.from_date" type="date" dir="ltr"
                               class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface text-sm">
                    </div>
                    <div>
                        <label class="block text-sm text-on-surface-variant mb-1.5">إلى تاريخ</label>
                        <input x-model="af.to_date" type="date" dir="ltr"
                               class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface text-sm">
                    </div>
                </div>
                <div x-show="aErr" class="text-error text-sm" x-text="aErr"></div>
            </div>
            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex justify-end gap-3 rounded-b-[20px]">
                <button @click="showAssign=false" class="rs-btn-cancel px-5 py-2.5 text-sm font-semibold">إلغاء</button>
                <button @click="saveAssign()" :disabled="assigning"
                        class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-bold text-sm disabled:opacity-60">
                    <span x-text="assigning ? 'جارٍ الحفظ...' : 'تعيين'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function shiftsPage() {
    const dayLabels = {
        sat: 'السبت', sun: 'الأحد', mon: 'الإثنين', tue: 'الثلاثاء',
        wed: 'الأربعاء', thu: 'الخميس', fri: 'الجمعة',
    };
    const dayShorts = {
        sat: 'سبت', sun: 'أحد', mon: 'إثن', tue: 'ثلا',
        wed: 'أرب', thu: 'خمي', fri: 'جمع',
    };
    const DAYS = ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];

    const emptyForm = () => ({
        name: '', color: '#0EA5A4',
        show_additional_periods: false, is_stopped: false,
        days: DAYS.map(d => ({ day_of_week: d, first_period_id: null, second_period_id: null, multiplier: 1.00 })),
    });

    return {
        shifts: [], periods: [], assignments: [], allEmployees: [],
        loading: false,
        showModal: false, editingId: null, saving: false, formErr: '',
        form: emptyForm(),
        showAssign: false, assigning: false, aErr: '',
        af: { employee_id: '', shift_id: '', from_date: '', to_date: '' },

        dayLong(d)  { return dayLabels[d] || d; },
        dayShort(d) { return dayShorts[d] || d; },
        initials(n) { return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); },
        // Mirror the dashboard pattern: wrap window.$can in a component method.
        // Direct `window.$can(...)` in x-show didn't evaluate reliably for some
        // users, causing the link to stay hidden even for CEO.
        can(perm) { return window.$can(perm); },

        async init() {
            if (await window.$guard('manage_shifts')) return;
            await Promise.all([
                this.fetchShifts(),
                this.fetchPeriods(),
                this.fetchAssignments(),
                this.fetchEmployees(),
            ]);
        },

        async fetchShifts() {
            this.loading = true;
            try {
                const r = await axios.get('/api/v1/shifts');
                if (r.data.success) this.shifts = r.data.data || [];
            } catch(e){}
            finally { this.loading = false; }
        },

        async fetchPeriods() {
            try {
                const r = await axios.get('/api/v1/periods');
                if (r.data.success) this.periods = r.data.data || [];
            } catch(e){}
        },

        async fetchAssignments() {
            try {
                const r = await axios.get('/api/v1/shift-assignments?per_page=50');
                if (r.data.success) this.assignments = r.data.data || [];
            } catch(e){}
        },

        async fetchEmployees() {
            try {
                const r = await axios.get('/api/v1/employees?per_page=100&status=active');
                if (r.data.success) this.allEmployees = r.data.data || [];
            } catch(e){}
        },

        openCreate() {
            this.editingId = null;
            this.form = emptyForm();
            this.formErr = '';
            this.showModal = true;
        },

        openEdit(shift) {
            this.editingId = shift.id;
            const grid = emptyForm().days;
            (shift.days || []).forEach(d => {
                const idx = DAYS.indexOf(d.day_of_week);
                if (idx === -1) return;
                grid[idx] = {
                    day_of_week: d.day_of_week,
                    first_period_id: d.first_period_id,
                    second_period_id: d.second_period_id,
                    multiplier: Number(d.multiplier || 1.0),
                };
            });
            this.form = {
                name: shift.name, color: shift.color || '#0EA5A4',
                show_additional_periods: !!shift.show_additional_periods,
                is_stopped: !!shift.is_stopped,
                days: grid,
            };
            this.formErr = '';
            this.showModal = true;
        },

        async save() {
            this.formErr = ''; this.saving = true;
            const payload = {
                name: this.form.name,
                color: this.form.color,
                show_additional_periods: this.form.show_additional_periods,
                is_stopped: this.form.is_stopped,
                days: this.form.days.map(d => ({
                    day_of_week: d.day_of_week,
                    first_period_id: d.first_period_id || null,
                    second_period_id: d.second_period_id || null,
                    multiplier: Number(d.multiplier ?? 1.0),
                })),
            };
            try {
                if (this.editingId) {
                    await axios.put('/api/v1/shifts/' + this.editingId, payload);
                } else {
                    await axios.post('/api/v1/shifts', payload);
                }
                this.showModal = false;
                await this.fetchShifts();
            } catch(e) {
                const errs = e.response?.data?.errors;
                this.formErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'حدث خطأ');
            } finally { this.saving = false; }
        },

        async deleteShift(shift) {
            if (!confirm('هل أنت متأكد من حذف الوردية "' + shift.name + '"؟')) return;
            try {
                await axios.delete('/api/v1/shifts/' + shift.id);
                await this.fetchShifts();
            } catch(e) {
                alert(e.response?.data?.message || 'لا يمكن الحذف.');
            }
        },

        openAssign() {
            this.af = { employee_id: '', shift_id: '', from_date: '', to_date: '' };
            this.aErr = '';
            this.showAssign = true;
        },

        async saveAssign() {
            this.aErr = ''; this.assigning = true;
            try {
                await axios.post('/api/v1/shift-assignments', this.af);
                this.showAssign = false;
                await this.fetchAssignments();
            } catch(e) {
                const errs = e.response?.data?.errors;
                this.aErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'حدث خطأ');
            } finally { this.assigning = false; }
        },

        async deleteAssignment(a) {
            if (!confirm('هل أنت متأكد من حذف هذا التعيين؟')) return;
            try {
                await axios.delete('/api/v1/shift-assignments/' + a.id);
                await this.fetchAssignments();
            } catch(e) {
                alert(e.response?.data?.message || 'حدث خطأ');
            }
        },
    };
}
</script>
@endpush
