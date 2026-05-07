@extends('layouts.app')
@section('title', 'ورديات الدوام')

@section('content')
<div x-data="shiftsPage()" x-init="init()" class="bg-[#071426] min-h-screen">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20">

        <!-- Header -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="font-h1 text-[20px] font-bold text-[#F1F5F9]">ورديات الدوام</h1>
                <p class="font-body-secondary text-[12px] text-[#475569] mt-1">إدارة جداول العمل وتوزيع الورديات</p>
            </div>
            <div class="flex gap-3">
                <button @click="openAssign()" class="px-5 py-2 bg-primary-container text-white rounded-lg font-bold text-sm hover:brightness-110 transition-all flex items-center gap-2">
                    تعيين وردية <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">add</span>
                </button>
            </div>
        </div>

        <!-- Shift Definition Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <template x-for="shift in shifts" :key="shift.id">
                <div class="bg-[#071426] border border-[#0B1F3A] p-6 rounded-lg hover:border-primary-container/30 transition-all group"
                     :style="'border-top: 3px solid ' + (shift.color || '#0EA5A4')">
                    <div class="flex justify-between items-start mb-4">
                        <span class="material-symbols-outlined p-2 rounded-lg text-[22px]"
                              :style="'color:' + (shift.color||'#0EA5A4') + '; background:' + (shift.color||'#0EA5A4') + '1a'"
                              x-text="shiftIcon(shift.name)"></span>
                        <span class="text-[11px] font-bold px-3 py-1 rounded-full"
                              :style="'color:' + (shift.color||'#0EA5A4') + '; background:' + (shift.color||'#0EA5A4') + '1a'"
                              x-text="'فترة سماح: ' + (shift.grace_minutes||10) + ' دقيقة'"></span>
                    </div>
                    <h3 class="font-h2 text-lg text-white mb-2" x-text="shift.name"></h3>
                    <p class="font-body-secondary text-sm text-slate-400 tracking-wide" x-text="shift.start_time + ' — ' + shift.end_time"></p>
                </div>
            </template>
        </div>

        <!-- Weekly Schedule Grid -->
        <section class="mb-12">
            <div class="bg-[#071426] border border-[#0B1F3A] rounded-xl overflow-hidden shadow-2xl">
                <div class="p-5 border-b border-slate-800 flex justify-between items-center">
                    <h2 class="font-h2 text-lg text-white">جدول الورديات الأسبوعي</h2>
                    <span class="text-sm text-on-surface-variant font-body-secondary" x-text="weekLabel"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-right">
                        <thead>
                            <tr class="bg-surface-container-low text-slate-400 text-xs font-bold">
                                <th class="p-4 border-b border-slate-800 min-w-[200px]">الموظف</th>
                                <th class="p-4 border-b border-slate-800">السبت</th>
                                <th class="p-4 border-b border-slate-800">الأحد</th>
                                <th class="p-4 border-b border-slate-800">الاثنين</th>
                                <th class="p-4 border-b border-slate-800">الثلاثاء</th>
                                <th class="p-4 border-b border-slate-800">الأربعاء</th>
                                <th class="p-4 border-b border-slate-800">الخميس</th>
                                <th class="p-4 border-b border-slate-800">الجمعة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-for="row in weeklyRows" :key="row.employee_id">
                                <tr class="hover:bg-white/[0.01] transition-colors">
                                    <td class="p-4 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-xs font-bold text-primary-container"
                                             x-text="initials(row.employee_name)"></div>
                                        <span class="text-sm font-medium text-slate-200" x-text="row.employee_name"></span>
                                    </td>
                                    <template x-for="day in ['السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة']" :key="day">
                                        <td class="p-4 text-center" :class="(day==='السبت'||day==='الجمعة') ? 'bg-white/[0.02]' : ''">
                                            <template x-if="row.schedule[day]">
                                                <span class="inline-block px-3 py-1 rounded text-[10px] font-bold"
                                                      :style="shiftChipStyle(row.schedule[day])"
                                                      x-text="row.schedule[day]"></span>
                                            </template>
                                            <template x-if="!row.schedule[day]">
                                                <span class="text-slate-600">--</span>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                            <template x-if="weeklyRows.length===0">
                                <tr><td colspan="8" class="p-8 text-center text-on-surface-variant text-sm">لا توجد تعيينات لهذا الأسبوع</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Assignments Table -->
        <section>
            <div class="bg-[#071426] border border-[#0B1F3A] rounded-xl overflow-hidden">
                <div class="p-5 border-b border-slate-800">
                    <h2 class="font-h2 text-lg text-white">تعيينات الموظفين الحالية</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-slate-400 text-xs font-bold border-b border-slate-800">
                                <th class="p-5">الموظف</th>
                                <th class="p-5">الوردية</th>
                                <th class="p-5">تاريخ البدء</th>
                                <th class="p-5">تاريخ الانتهاء</th>
                                <th class="p-5">الأيام</th>
                                <th class="p-5">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-for="a in assignments" :key="a.id">
                                <tr class="hover:bg-white/[0.01]">
                                    <td class="p-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-slate-800 rounded-lg flex items-center justify-center font-bold text-sm"
                                                 :style="'color:' + (a.shift?.color||'#0EA5A4')"
                                                 x-text="initials(a.employee?.name)"></div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-200" x-text="a.employee?.name||'—'"></div>
                                                <div class="text-xs text-slate-500" x-text="a.employee?.department?.name||a.employee?.department||''"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                              :style="'color:' + (a.shift?.color||'#0EA5A4') + '; background:' + (a.shift?.color||'#0EA5A4') + '1a'">
                                            <span class="w-1.5 h-1.5 rounded-full" :style="'background:' + (a.shift?.color||'#0EA5A4')"></span>
                                            <span x-text="a.shift?.name||'—'"></span>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm text-slate-400 font-body-secondary" x-text="a.start_date||'—'"></td>
                                    <td class="p-5 text-sm text-slate-400 font-body-secondary" x-text="a.end_date||'—'"></td>
                                    <td class="p-5 text-xs text-slate-300" x-text="a.days_of_week||'—'"></td>
                                    <td class="p-5">
                                        <div class="flex gap-3">
                                            <button @click="deleteAssignment(a)" class="text-slate-500 hover:text-error transition-colors">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="assignments.length===0">
                                <tr><td colspan="6" class="p-8 text-center text-on-surface-variant text-sm">لا توجد تعيينات</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- Assign Shift Modal -->
    <div x-show="showAssign" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#0B1F3A] w-full max-w-lg rounded-[20px] p-7 border border-white/10 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-white">تعيين وردية</h3>
                <button @click="showAssign=false" class="text-slate-500 hover:text-white"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الموظف</label>
                    <select x-model="af.employee_id" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm appearance-none">
                        <option value="">اختر موظف</option>
                        <template x-for="e in allEmployees" :key="e.id"><option :value="e.id" x-text="e.name"></option></template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الوردية</label>
                    <select x-model="af.shift_id" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm appearance-none">
                        <option value="">اختر الوردية</option>
                        <template x-for="s in shifts" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">تاريخ البدء</label>
                        <input x-model="af.start_date" type="date" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">تاريخ الانتهاء</label>
                        <input x-model="af.end_date" type="date" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                </div>
                <div x-show="aErr" class="text-error text-sm" x-text="aErr"></div>
                <div class="flex gap-3 pt-2">
                    <button @click="saveAssign()" :disabled="assigning" class="flex-1 bg-primary-container text-white py-2.5 rounded-lg font-bold hover:brightness-110 disabled:opacity-60 text-sm">
                        <span x-text="assigning?'جارٍ الحفظ...':'تعيين'"></span>
                    </button>
                    <button @click="showAssign=false" class="rs-btn-cancel flex-1 py-2.5 text-sm">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function shiftsPage() {
    return {
        shifts:[], assignments:[], allEmployees:[], weeklyRows:[],
        showAssign:false, assigning:false, aErr:'',
        af:{employee_id:'', shift_id:'', start_date:'', end_date:'', days_of_week:''},

        get weekLabel(){
            const now=new Date();
            const start=new Date(now); start.setDate(now.getDate()-now.getDay());
            const end=new Date(start); end.setDate(start.getDate()+6);
            return start.toLocaleDateString('ar')+ ' — ' + end.toLocaleDateString('ar');
        },

        initials(n){ return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); },

        shiftIcon(name){
            if(name?.includes('صباح')) return 'light_mode';
            if(name?.includes('مساء')||name?.includes('مسائ')) return 'wb_twilight';
            return 'dark_mode';
        },

        shiftChipStyle(name){
            if(name?.includes('صباح')) return 'color:#0EA5A4;background:rgba(14,165,164,0.1)';
            if(name?.includes('مساء')||name?.includes('مسائ')) return 'color:#F59E0B;background:rgba(245,158,11,0.1)';
            return 'color:#60A5FA;background:rgba(96,165,250,0.1)';
        },

        async init(){
            if (await window.$guard('manage_shifts')) return;
            await Promise.all([this.fetchShifts(), this.fetchAssignments(), this.fetchEmployees(), this.fetchWeekly()]);
        },

        async fetchShifts(){ try { const r=await axios.get('/api/v1/shifts'); if(r.data.success)this.shifts=r.data.data||[]; } catch(e){} },

        async fetchAssignments(){ try { const r=await axios.get('/api/v1/shift-assignments?per_page=50'); if(r.data.success)this.assignments=r.data.data||[]; } catch(e){} },

        async fetchEmployees(){ try { const r=await axios.get('/api/v1/employees?per_page=100&status=active'); if(r.data.success)this.allEmployees=r.data.data; } catch(e){} },

        async fetchWeekly(){
            try {
                const now=new Date();
                const start=new Date(now); start.setDate(now.getDate()-now.getDay());
                const dateStr=start.toISOString().split('T')[0];
                const r=await axios.get('/api/v1/shifts/weekly-schedule?week_start='+dateStr);
                if(r.data.success){
                    const days=['السبت','الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة'];
                    const raw=r.data.data;
                    if(Array.isArray(raw)){
                        this.weeklyRows=raw.map(emp=>({
                            employee_id: emp.employee_id,
                            employee_name: emp.employee_name,
                            schedule: Object.fromEntries(
                                days.map(d=>[d, emp.schedule?.find?.(s=>s.day===d||s.day_name===d)?.shift_name||null])
                            )
                        }));
                    }
                }
            } catch(e){}
        },

        openAssign(){ this.af={employee_id:'',shift_id:'',start_date:'',end_date:'',days_of_week:'الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس'}; this.aErr=''; this.showAssign=true; },

        async saveAssign(){
            this.aErr=''; this.assigning=true;
            try {
                await axios.post('/api/v1/shift-assignments', this.af);
                this.showAssign=false; await Promise.all([this.fetchAssignments(), this.fetchWeekly()]);
            } catch(e){
                const errs=e.response?.data?.errors;
                this.aErr=errs?Object.values(errs).flat().join(' '):(e.response?.data?.message||'خطأ');
            } finally{this.assigning=false;}
        },

        async deleteAssignment(a){
            if(!confirm('هل أنت متأكد من حذف هذا التعيين؟')) return;
            try { await axios.delete('/api/v1/shift-assignments/'+a.id); await this.fetchAssignments(); }
            catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },
    };
}
</script>
@endpush
