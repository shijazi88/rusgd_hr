@extends('layouts.app')
@section('title', 'سجل الحضور')

@section('content')
<div x-data="attendancePage()" x-init="init()" class="bg-[#071426] min-h-screen">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20">

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="font-h1 text-[20px] font-bold text-[#F1F5F9] mb-1">سجل الحضور</h1>
                <p class="text-[#475569] font-body-secondary text-[12px]">متابعة الحضور اليومي وساعات العمل</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative bg-surface-container border border-[#0B1F3A] rounded-lg px-4 py-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">calendar_today</span>
                    <input x-model="selectedDate" @change="fetchRecords(1)" type="date" dir="ltr"
                           class="bg-transparent border-none text-on-surface focus:ring-0 text-sm outline-none">
                </div>
                <button @click="openManual()" class="bg-primary-container text-white px-5 py-2 rounded-lg font-bold text-sm hover:brightness-110 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">add</span>
                    تسجيل يدوي
                </button>
            </div>
        </div>

        <!-- Stats Row (5 cards) -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-[#0B1F3A] p-5 border border-[#0B1F3A] hover:border-primary-container/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body-secondary text-sm text-on-surface-variant">حاضر</span>
                    <span class="material-symbols-outlined text-primary-container bg-primary-container/10 p-2 rounded text-[18px]">person_check</span>
                </div>
                <div class="font-body-main text-4xl font-bold text-primary-container" x-text="dayStats.present||0"></div>
            </div>
            <div class="bg-[#0B1F3A] p-5 border border-[#0B1F3A] hover:border-red-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body-secondary text-sm text-on-surface-variant">غائب</span>
                    <span class="material-symbols-outlined text-red-400 bg-red-400/10 p-2 rounded text-[18px]">person_off</span>
                </div>
                <div class="font-body-main text-4xl font-bold text-red-400" x-text="dayStats.absent||0"></div>
            </div>
            <div class="bg-[#0B1F3A] p-5 border border-[#0B1F3A] hover:border-amber-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body-secondary text-sm text-on-surface-variant">متأخر</span>
                    <span class="material-symbols-outlined text-amber-400 bg-amber-400/10 p-2 rounded text-[18px]">schedule</span>
                </div>
                <div class="font-body-main text-4xl font-bold text-amber-400" x-text="dayStats.late||0"></div>
            </div>
            <div class="bg-[#0B1F3A] p-5 border border-[#0B1F3A] hover:border-blue-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body-secondary text-sm text-on-surface-variant">إجازة</span>
                    <span class="material-symbols-outlined text-blue-400 bg-blue-400/10 p-2 rounded text-[18px]">flight</span>
                </div>
                <div class="font-body-main text-4xl font-bold text-blue-400" x-text="dayStats.on_leave||0"></div>
            </div>
            <div class="bg-[#0B1F3A] p-5 border border-[#0B1F3A] hover:border-slate-500/30 transition-all">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-body-secondary text-sm text-on-surface-variant">لا يوجد</span>
                    <span class="material-symbols-outlined text-slate-400 bg-slate-400/10 p-2 rounded text-[18px]">bedtime</span>
                </div>
                <div class="font-body-main text-4xl font-bold text-slate-400" x-text="dayStats.no_record||0"></div>
            </div>
        </div>

        <!-- Table + Monthly Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- Table (3-col) -->
            <div class="lg:col-span-3">
                <div class="bg-[#0B1F3A] border border-[#0B1F3A] overflow-x-auto rounded-lg">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-surface-container border-b border-[#0B1F3A]">
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium">الموظف</th>
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium">الدخول</th>
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium">الخروج</th>
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium">الساعات</th>
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium">التأخير</th>
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium">الحالة</th>
                                <th class="p-5 font-body-secondary text-sm text-on-surface-variant font-medium text-center">تعديل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0B1F3A]">
                            <template x-if="loading">
                                <tr><td colspan="7" class="p-10 text-center"><span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span></td></tr>
                            </template>
                            <template x-for="rec in records" :key="rec.id">
                                <tr class="hover:bg-white/5 transition-all">
                                    <td class="p-5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-surface-variant flex items-center justify-center text-xs font-bold text-primary-container"
                                                 x-text="initials(rec.employee?.name)"></div>
                                            <span class="font-body-secondary text-sm" x-text="rec.employee?.name||'—'"></span>
                                        </div>
                                    </td>
                                    <td class="p-5 font-body-secondary text-sm" x-text="rec.check_in||'--:--'"></td>
                                    <td class="p-5 font-body-secondary text-sm" x-text="rec.check_out||'--:--'"></td>
                                    <td class="p-5 font-body-secondary text-sm" x-text="rec.work_hours ? rec.work_hours+'h' : '0h'"></td>
                                    <td class="p-5 font-body-secondary text-sm" :class="rec.late_minutes>0?'text-amber-400':'text-slate-400'"
                                        x-text="rec.late_minutes>0 ? rec.late_minutes+'د' : '—'"></td>
                                    <td class="p-5">
                                        <span :class="sBadge(rec.status)" x-text="sLabel(rec.status)"></span>
                                    </td>
                                    <td class="p-5 text-center">
                                        <button @click="openCheckout(rec)" x-show="rec.check_in && !rec.check_out"
                                                class="text-primary-container hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">login</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!loading && records.length===0">
                                <tr><td colspan="7" class="p-10 text-center text-on-surface-variant text-sm">لا توجد سجلات لهذا اليوم</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Monthly Summary (1-col) -->
            <div class="lg:col-span-1">
                <div class="bg-[#0B1F3A] border border-[#0B1F3A] p-8 rounded-lg sticky top-24">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="font-h2 text-lg">ملخص الشهر</h3>
                        <span class="bg-primary-container/10 text-primary-container px-3 py-1 rounded-full text-xs font-bold" x-text="currentMonthName"></span>
                    </div>
                    <div class="space-y-5">
                        <div class="flex justify-between items-center">
                            <span class="font-body-secondary text-sm text-on-surface-variant">أيام الحضور</span>
                            <span class="font-bold" x-text="(monthly.present||0) + ' / ' + (monthly.working_days||0)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-body-secondary text-sm text-on-surface-variant">أيام الغياب</span>
                            <span class="font-bold text-red-400" x-text="monthly.absent||0"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-body-secondary text-sm text-on-surface-variant">مرات التأخير</span>
                            <span class="font-bold text-amber-400" x-text="monthly.late||0"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-body-secondary text-sm text-on-surface-variant">إجمالي الساعات</span>
                            <span class="font-bold" x-text="(monthly.total_hours||0)+'h'"></span>
                        </div>
                        <div class="pt-4 border-t border-[#162030]">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-body-secondary text-sm text-on-surface-variant">نسبة الإنجاز</span>
                                <span class="text-xs text-primary-container" x-text="(monthly.attendance_rate||0)+'%'"></span>
                            </div>
                            <div class="h-1.5 w-full bg-surface-container rounded-full overflow-hidden">
                                <div class="h-full bg-primary-container" :style="'width:'+(monthly.attendance_rate||0)+'%'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Manual Record Modal -->
    <div x-show="showManual" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#0B1F3A] w-full max-w-lg rounded-[20px] p-7 border border-white/10 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-white" x-text="editingId ? 'تسجيل خروج' : 'تسجيل حضور يدوي'"></h3>
                <button @click="showManual=false" class="text-slate-500 hover:text-white"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="space-y-4">
                <template x-if="!editingId">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الموظف</label>
                        <select x-model="mf.employee_id" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm appearance-none">
                            <option value="">اختر موظف</option>
                            <template x-for="e in allEmployees" :key="e.id"><option :value="e.id" x-text="e.name"></option></template>
                        </select>
                    </div>
                </template>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">التاريخ</label>
                        <input x-model="mf.date" type="date" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                    <template x-if="!editingId">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">وقت الدخول</label>
                            <input x-model="mf.check_in" type="time" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                        </div>
                    </template>
                    <template x-if="editingId">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">وقت الخروج</label>
                            <input x-model="mf.check_out" type="time" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                        </div>
                    </template>
                </div>
                <template x-if="!editingId">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الحالة</label>
                        <select x-model="mf.status" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm appearance-none">
                            <option value="present">حاضر</option>
                            <option value="late">متأخر</option>
                            <option value="absent">غائب</option>
                            <option value="on_leave">إجازة</option>
                        </select>
                    </div>
                </template>
                <div x-show="mErr" class="text-error text-sm" x-text="mErr"></div>
                <div class="flex gap-3 pt-2">
                    <button @click="saveRecord()" :disabled="saving" class="flex-1 bg-primary-container text-white py-2.5 rounded-lg font-bold hover:brightness-110 disabled:opacity-60 text-sm">
                        <span x-text="saving?'جارٍ الحفظ...':'حفظ'"></span>
                    </button>
                    <button @click="showManual=false" class="rs-btn-cancel flex-1 py-2.5 text-sm">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function attendancePage() {
    return {
        records:[], meta:{}, dayStats:{}, monthly:{}, allEmployees:[],
        loading:false, selectedDate: new Date().toISOString().split('T')[0],
        showManual:false, saving:false, mErr:'', editingId:null,
        mf:{employee_id:'', date:'', check_in:'', check_out:'', status:'present'},

        get currentMonthName(){
            const months=['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            return months[new Date().getMonth()];
        },

        initials(n){ return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); },

        sLabel(s){ return {present:'حاضر',late:'متأخر',absent:'غائب',on_leave:'إجازة'}[s]||s; },
        sBadge(s){
            return {
                present:'bg-primary-container/10 text-primary-container text-[10px] font-bold px-2 py-1 rounded',
                late:'bg-amber-400/10 text-amber-400 text-[10px] font-bold px-2 py-1 rounded',
                absent:'bg-red-400/10 text-red-400 text-[10px] font-bold px-2 py-1 rounded',
                on_leave:'bg-blue-400/10 text-blue-400 text-[10px] font-bold px-2 py-1 rounded',
            }[s]||'bg-surface-variant text-on-surface-variant text-[10px] font-bold px-2 py-1 rounded';
        },

        async init(){ await Promise.all([this.fetchRecords(), this.fetchEmployees()]); },

        async fetchRecords(page=1){
            this.loading=true;
            try {
                const r=await axios.get('/api/v1/attendance?date='+this.selectedDate+'&page='+page+'&per_page=50');
                if(r.data.success){
                    this.records=r.data.data; this.meta=r.data.meta||{};
                    // compute day stats
                    this.dayStats={
                        present: this.records.filter(r=>r.status==='present').length,
                        late:    this.records.filter(r=>r.status==='late').length,
                        absent:  this.records.filter(r=>r.status==='absent').length,
                        on_leave:this.records.filter(r=>r.status==='on_leave').length,
                        no_record: 0,
                    };
                }
            } catch(e){} finally{this.loading=false;}
            // monthly summary
            try {
                const now=new Date();
                const r2=await axios.get('/api/v1/attendance/monthly-summary?employee_id=&month='+(now.getMonth()+1)+'&year='+now.getFullYear());
                if(r2.data.success) this.monthly=r2.data.data||{};
            } catch(e){}
        },

        async fetchEmployees(){ try { const r=await axios.get('/api/v1/employees?per_page=100&status=active'); if(r.data.success)this.allEmployees=r.data.data; } catch(e){} },

        openManual(){
            this.editingId=null;
            this.mf={employee_id:'', date:this.selectedDate, check_in:'', check_out:'', status:'present'};
            this.mErr=''; this.showManual=true;
        },

        openCheckout(rec){
            this.editingId=rec.id;
            this.mf={employee_id:rec.employee_id, date:rec.date, check_in:rec.check_in, check_out:'', status:rec.status};
            this.mErr=''; this.showManual=true;
        },

        async saveRecord(){
            this.mErr=''; this.saving=true;
            try {
                if(this.editingId){
                    await axios.put('/api/v1/attendance/'+this.editingId, {check_out: this.mf.check_out});
                } else {
                    await axios.post('/api/v1/attendance', {
                        employee_id: this.mf.employee_id,
                        date: this.mf.date,
                        check_in: this.mf.check_in,
                        status: this.mf.status,
                    });
                }
                this.showManual=false; await this.fetchRecords();
            } catch(e){
                const errs=e.response?.data?.errors;
                this.mErr=errs?Object.values(errs).flat().join(' '):(e.response?.data?.message||'خطأ');
            } finally{this.saving=false;}
        },
    };
}
</script>
@endpush
