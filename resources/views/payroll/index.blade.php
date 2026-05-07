@extends('layouts.app')
@section('title', 'مسير الرواتب')

@section('content')
<div x-data="payrollPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20">

        {{-- Header --}}
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="font-h1 text-[20px] font-bold text-on-surface mb-1">مسير الرواتب</h1>
                <p class="font-body-secondary text-[12px] text-on-surface-variant">إدارة الرواتب الشهرية والخصومات</p>
            </div>
            <button @click="openRun()"
                    class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-bold text-sm flex items-center gap-2 hover:brightness-110 transition-all active:scale-95">
                <span>تشغيل مسير الرواتب</span>
                <span class="material-symbols-outlined text-[18px]">play_arrow</span>
            </button>
        </div>

        {{-- Runs list --}}
        <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden mb-8 transition-colors"
             style="box-shadow:var(--c-shadow)">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-h2 text-[16px] text-on-surface font-bold">سجل المسيرات</h2>
            </div>
            <div class="divide-y divide-outline-variant">
                <template x-if="loading">
                    <div class="p-10 text-center">
                        <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                    </div>
                </template>
                <template x-for="run in runs" :key="run.id">
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-container-high transition-colors cursor-pointer group"
                         @click="selectRun(run)">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-primary-container border border-outline-variant transition-colors group-hover:border-primary-container/40"
                                 style="background:rgba(14,165,164,0.1)">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface text-sm" x-text="monthName(run.month) + ' ' + run.year"></p>
                                <p class="text-xs text-on-surface-variant mt-0.5" x-text="(run.items_count||0) + ' موظفين'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <span class="font-bold text-primary-container font-body-main"
                                  x-text="formatMoney(run.total_amount) + ' ر.س'"></span>
                            <span :class="run.status==='completed'
                                    ? 'rs-badge rs-badge-active'
                                    : 'rs-badge rs-badge-on_leave'"
                                  x-text="run.status==='completed' ? 'معتمد' : 'قيد التنفيذ'"></span>
                            <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_left</span>
                        </div>
                    </div>
                </template>
                <template x-if="!loading && runs.length===0">
                    <div class="p-10 text-center text-on-surface-variant text-sm">لم يتم تشغيل مسير رواتب بعد</div>
                </template>
            </div>
        </div>

        {{-- Selected Run Details --}}
        <div x-show="selectedRun" style="display:none;">
            <div class="flex justify-between items-center mb-5">
                <h2 class="font-h2 text-[18px] text-on-surface font-bold">
                    تفاصيل مسير <span x-text="monthName(selectedRun?.month) + ' ' + selectedRun?.year"></span>
                </h2>
                <div class="bg-surface-container px-8 py-4 rounded-xl border border-primary-container/25 flex flex-col items-end gap-1"
                     style="box-shadow:var(--c-shadow)">
                    <span class="text-on-surface-variant text-xs">الإجمالي الكلي</span>
                    <div class="flex items-baseline gap-2">
                        <span class="font-bold text-[22px] text-primary-container"
                              x-text="formatMoney(selectedRun?.total_amount)"></span>
                        <span class="font-h2 text-sm text-on-surface-variant">ر.س</span>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden"
                 style="box-shadow:var(--c-shadow)">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr>
                                <th class="p-5">الموظف</th>
                                <th class="p-5">أساسي</th>
                                <th class="p-5">سكن</th>
                                <th class="p-5">نقل</th>
                                <th class="p-5">خصومات</th>
                                <th class="p-5">الإجمالي</th>
                                <th class="p-5">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="text-on-surface divide-y divide-outline-variant">
                            <template x-for="item in runItems" :key="item.id">
                                <tr class="hover:bg-surface-container-high transition-colors">
                                    <td class="p-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-primary-container border border-outline-variant"
                                                 style="background:rgba(14,165,164,0.1)"
                                                 x-text="initials(item.employee?.name)"></div>
                                            <div>
                                                <p class="font-bold text-[14px] text-on-surface" x-text="item.employee?.name||'—'"></p>
                                                <p class="text-[11px] text-on-surface-variant" x-text="item.employee?.job_title||''"></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5 font-bold text-sm text-on-surface" x-text="formatMoney(item.base_salary)"></td>
                                    <td class="p-5 text-sm text-on-surface-variant" x-text="formatMoney(item.housing_allowance)"></td>
                                    <td class="p-5 text-sm text-on-surface-variant" x-text="formatMoney(item.transport_allowance)"></td>
                                    <td class="p-5 text-sm font-medium"
                                        :class="item.deductions > 0 ? 'text-error' : 'text-on-surface-variant'"
                                        x-text="item.deductions > 0 ? '- '+formatMoney(item.deductions) : '—'"></td>
                                    <td class="p-5 font-bold text-sm text-primary-container" x-text="formatMoney(item.net_salary)"></td>
                                    <td class="p-5">
                                        <span class="rs-badge rs-badge-active">معتمد</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    {{-- Run Payroll Modal --}}
    <div x-show="showRun" style="display:none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-surface-container w-full max-w-lg rounded-2xl border border-outline-variant shadow-2xl overflow-hidden">

            {{-- Modal header --}}
            <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                <h2 class="font-h1 text-[18px] text-on-surface font-bold">تشغيل مسير الرواتب</h2>
                <button @click="showRun=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-7 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    {{-- Month custom dropdown --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">الشهر</label>
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                            <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                                <span x-text="months[runForm.month - 1] || 'اختر'"></span>
                                <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                            </button>
                            <div x-show="open" class="rs-panel" x-transition style="display:none">
                                <template x-for="(m, i) in months" :key="i">
                                    <button type="button" @click="runForm.month = i + 1; open = false"
                                            class="rs-opt" :class="runForm.month === i + 1 ? 'selected' : ''"
                                            x-text="m"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Year input --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">السنة</label>
                        <input x-model="runForm.year" type="number" dir="ltr"
                               class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface text-sm focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none">
                    </div>
                </div>

                {{-- Warning --}}
                <div class="bg-amber-500/10 border border-amber-500/25 p-4 rounded-lg flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-500 text-[18px] shrink-0">warning</span>
                    <p class="text-amber-600 dark:text-amber-500 text-[13px] leading-relaxed">يرجى التأكد من صحة البيانات قبل الاعتماد. لا يمكن التراجع عن العملية بعد التأكيد.</p>
                </div>

                <div x-show="runErr" class="text-error text-sm" x-text="runErr"></div>
            </div>

            {{-- Modal footer --}}
            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex gap-4">
                <button @click="submitRun()" :disabled="running"
                        class="flex-1 bg-primary-container text-white py-3 rounded-lg font-bold text-sm hover:brightness-110 active:scale-95 transition-all disabled:opacity-60">
                    <span x-text="running ? 'جارٍ التشغيل...' : 'تأكيد وتشغيل المسير'"></span>
                </button>
                <button @click="showRun=false"
                        class="rs-btn-cancel flex-1 py-3 text-sm">إلغاء</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function payrollPage() {
    return {
        runs:[], runItems:[], loading:false,
        selectedRun:null,
        showRun:false, running:false, runErr:'',
        runForm:{ month: new Date().getMonth()+1, year: new Date().getFullYear() },
        months:['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],

        initials(n){ return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); },
        formatMoney(v){ return v ? Number(v).toLocaleString('ar-SA', {maximumFractionDigits:0}) : '0'; },
        monthName(m){ return this.months[(m||1)-1] || ''; },

        async init(){
            if (await window.$guard('view_payroll')) return;
            this.loading = true;
            await this.fetchRuns();
        },

        async fetchRuns(){
            try { const r = await axios.get('/api/v1/payroll-runs'); if(r.data.success){ this.runs = r.data.data; } }
            catch(e){} finally { this.loading = false; }
        },

        async selectRun(run){
            this.selectedRun = run;
            try { const r = await axios.get('/api/v1/payroll-runs/'+run.id); if(r.data.success) this.runItems = r.data.data.items || []; }
            catch(e){}
        },

        openRun(){ this.runForm = {month: new Date().getMonth()+1, year: new Date().getFullYear()}; this.runErr = ''; this.showRun = true; },

        async submitRun(){
            this.runErr = ''; this.running = true;
            try {
                const r = await axios.post('/api/v1/payroll-runs', this.runForm);
                this.showRun = false; await this.fetchRuns();
                if(r.data.success) this.selectRun(r.data.data);
            } catch(e){
                const errs = e.response?.data?.errors;
                this.runErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'خطأ');
            } finally { this.running = false; }
        },
    };
}
</script>
@endpush
