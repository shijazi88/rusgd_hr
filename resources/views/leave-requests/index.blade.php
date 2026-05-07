@extends('layouts.app')
@section('title', 'طلبات الإجازة')

@section('content')
<div x-data="leaveRequestsPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20">

        {{-- Header --}}
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="font-h1 text-[20px] font-bold text-on-surface mb-1">طلبات الإجازة</h1>
                <p class="text-on-surface-variant font-body-secondary text-[12px]">إدارة أرصدة وطلبات إجازات الموظفين</p>
            </div>
            <button @click="openCreate()"
                    class="bg-primary-container hover:brightness-110 text-white px-5 py-2.5 rounded-[10px] font-bold flex items-center gap-2 transition-all text-sm">
                طلب إجازة جديد <span class="text-xl leading-none">+</span>
            </button>
        </div>

        {{-- Balance Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <template x-for="bal in balances" :key="bal.leave_type_id">
                <div class="bg-surface-container p-[18px] rounded-[14px] border border-outline-variant hover:border-primary-container/40 transition-all"
                     style="box-shadow: var(--c-shadow)">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-on-surface-variant text-sm" x-text="bal.leave_type?.name || '—'"></span>
                        <span class="material-symbols-outlined" :class="balIconColor(bal.leave_type?.name)">event_available</span>
                    </div>
                    <div class="text-on-surface font-bold text-lg mb-3">
                        <span x-text="bal.total_days - bal.used_days"></span>
                        <span class="text-on-surface-variant font-normal text-sm"> / <span x-text="bal.total_days"></span> يوم</span>
                    </div>
                    <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700"
                             :class="balBarColor(bal.leave_type?.name)"
                             :style="'width:'+balPct(bal)+'%'"></div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Table --}}
        <div class="bg-surface-container rounded-xl border border-outline-variant overflow-hidden"
             style="box-shadow: var(--c-shadow)">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr>
                        <th class="p-5">الموظف</th>
                        <th class="p-5">نوع الإجازة</th>
                        <th class="p-5">الفترة</th>
                        <th class="p-5">المدة</th>
                        <th class="p-5">الحالة</th>
                        <th class="p-5">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    <template x-if="loading">
                        <tr><td colspan="6" class="p-10 text-center">
                            <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                        </td></tr>
                    </template>
                    <template x-for="req in requests" :key="req.id">
                        <tr class="hover:bg-surface-container-high transition-colors">
                            <td class="p-5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-primary-container border border-outline-variant"
                                         style="background: rgba(14,165,164,0.1)"
                                         x-text="initials(req.employee?.name)"></div>
                                    <span class="text-on-surface text-sm font-medium" x-text="req.employee?.name||'—'"></span>
                                </div>
                            </td>
                            <td class="p-5 text-on-surface-variant text-sm" x-text="req.leave_type?.name||'—'"></td>
                            <td class="p-5 text-on-surface text-sm whitespace-nowrap" dir="ltr"
                                x-text="(req.from_date||'') + '  —  ' + (req.to_date||'')"></td>
                            <td class="p-5 text-on-surface font-bold text-sm" x-text="(req.days||0)+' أيام'"></td>
                            <td class="p-5">
                                <span :class="sBadge(req.status)" x-text="sLabel(req.status)"></span>
                            </td>
                            <td class="p-5">
                                <div x-show="req.status==='pending' || req.status==='initial_approval'" class="flex gap-2">
                                    <button @click="approve(req)"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all rs-badge-active hover:opacity-90">موافق</button>
                                    <button @click="openReject(req)"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all rs-badge-terminated hover:opacity-90">رفض</button>
                                </div>
                                <span x-show="req.status!=='pending' && req.status!=='initial_approval'" class="text-on-surface-variant text-sm">—</span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && requests.length===0">
                        <tr><td colspan="6" class="p-10 text-center text-on-surface-variant text-sm">لا توجد طلبات</td></tr>
                    </template>
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="p-4 border-t border-outline-variant flex justify-between items-center text-xs text-on-surface-variant">
                <span x-text="'عرض '+requests.length+' من '+(meta.total||0)"></span>
                <div class="flex gap-2">
                    <button @click="fetchRequests(meta.current_page-1)" :disabled="meta.current_page<=1"
                            class="rs-page-btn">السابق</button>
                    <button @click="fetchRequests(meta.current_page+1)" :disabled="meta.current_page>=meta.last_page"
                            class="rs-page-btn">التالي</button>
                </div>
            </div>
        </div>
    </main>

    {{-- Create Modal --}}
    <div x-show="showCreate" style="display:none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-surface-container w-full max-w-lg rounded-[20px] p-7 border border-outline-variant shadow-2xl"
             style="box-shadow: var(--c-shadow)">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-on-surface">طلب إجازة جديد</h3>
                <button @click="showCreate=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="space-y-4">
                {{-- Employee dropdown --}}
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">الموظف</label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                            <span x-text="allEmployees.find(e => String(e.id)===String(cf.employee_id))?.name || 'اختر موظف'"></span>
                            <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                        </button>
                        <div x-show="open" class="rs-panel" x-transition style="display:none">
                            <button type="button" @click="cf.employee_id=''; open=false"
                                    class="rs-opt" :class="cf.employee_id==='' ? 'selected':''">اختر موظف</button>
                            <template x-for="e in allEmployees" :key="e.id">
                                <button type="button" @click="cf.employee_id=e.id; open=false"
                                        class="rs-opt" :class="String(cf.employee_id)===String(e.id) ? 'selected':''"
                                        x-text="e.name"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Leave type dropdown --}}
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">نوع الإجازة</label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                            <span x-text="leaveTypes.find(lt => String(lt.id)===String(cf.leave_type_id))?.name || 'اختر النوع'"></span>
                            <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                        </button>
                        <div x-show="open" class="rs-panel" x-transition style="display:none">
                            <button type="button" @click="cf.leave_type_id=''; open=false"
                                    class="rs-opt" :class="cf.leave_type_id==='' ? 'selected':''">اختر النوع</button>
                            <template x-for="lt in leaveTypes" :key="lt.id">
                                <button type="button" @click="cf.leave_type_id=lt.id; open=false"
                                        class="rs-opt" :class="String(cf.leave_type_id)===String(lt.id) ? 'selected':''"
                                        x-text="lt.name"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Date range --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">من</label>
                        <input x-model="cf.from_date" type="date" dir="ltr"
                               class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">إلى</label>
                        <input x-model="cf.to_date" type="date" dir="ltr"
                               class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">السبب</label>
                    <textarea x-model="cf.reason" rows="3"
                              class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm resize-none"></textarea>
                </div>

                <div x-show="cErr" class="text-error text-sm" x-text="cErr"></div>
                <div class="flex gap-3 pt-2">
                    <button @click="submitCreate()" :disabled="creating"
                            class="flex-1 bg-primary-container text-white py-2.5 rounded-lg font-bold hover:brightness-110 disabled:opacity-60 text-sm transition-all">
                        <span x-text="creating?'جارٍ الإرسال...':'إرسال'"></span>
                    </button>
                    <button @click="showCreate=false"
                            class="rs-btn-cancel flex-1 py-2.5 text-sm">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="showReject" style="display:none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-surface-container w-full max-w-md rounded-[20px] p-7 border border-outline-variant shadow-2xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-on-surface">سبب الرفض</h3>
                <button @click="showReject=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <textarea x-model="rejectReason" rows="4" placeholder="اكتب سبب الرفض..."
                      class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm resize-none mb-4 placeholder:text-on-surface-variant"></textarea>
            <div class="flex gap-3">
                <button @click="submitReject()"
                        class="flex-1 bg-error text-white py-2.5 rounded-lg font-bold text-sm hover:brightness-110 transition-all">رفض الطلب</button>
                <button @click="showReject=false"
                        class="rs-btn-cancel flex-1 py-2.5 text-sm">إلغاء</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function leaveRequestsPage() {
    return {
        requests:[],meta:{},balances:[],leaveTypes:[],allEmployees:[],loading:false,
        showCreate:false,creating:false,cErr:'',
        cf:{employee_id:'',leave_type_id:'',from_date:'',to_date:'',reason:''},
        showReject:false,rejectReason:'',rejectingId:null,

        initials(n){ return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); },
        sLabel(s){ return {pending:'مرحلة 1', initial_approval:'مرحلة 2', approved:'مقبول', rejected:'مرفوض'}[s] || s; },
        sBadge(s){
            return 'rs-badge ' + ({pending:'rs-badge-on_leave', initial_approval:'rs-badge-on_leave', approved:'rs-badge-active', rejected:'rs-badge-terminated'}[s] || 'rs-badge-inactive');
        },
        balPct(b){ return b.total_days > 0 ? Math.round(((b.total_days - b.used_days) / b.total_days) * 100) : 0; },
        balIconColor(n){ return {'إجازة سنوية':'text-primary-container','إجازة مرضية':'text-emerald-500','إجازة طارئة':'text-amber-500','إجازة بدون راتب':'text-slate-500'}[n] || 'text-primary-container'; },
        balBarColor(n){ return {'إجازة سنوية':'bg-primary-container','إجازة مرضية':'bg-emerald-500','إجازة طارئة':'bg-amber-500','إجازة بدون راتب':'bg-slate-400'}[n] || 'bg-primary-container'; },

        async init(){ await Promise.all([this.fetchRequests(), this.fetchBalances(), this.fetchLeaveTypes(), this.fetchEmployees()]); },

        async fetchRequests(page=1){
            this.loading = true;
            try { const r = await axios.get('/api/v1/leave-requests?page='+page); if(r.data.success){ this.requests = r.data.data; this.meta = r.data.meta; } }
            catch(e){} finally { this.loading = false; }
        },

        async fetchBalances(){
            try {
                const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
                if(user?.employee?.id){ const r = await axios.get('/api/v1/leave-requests/balance?employee_id='+user.employee.id); if(r.data.success) this.balances = r.data.data || []; }
            } catch(e){}
        },

        async fetchLeaveTypes(){ try { const r = await axios.get('/api/v1/leave-types'); if(r.data.success) this.leaveTypes = r.data.data || []; } catch(e){} },
        async fetchEmployees(){ try { const r = await axios.get('/api/v1/employees?per_page=100&status=active'); if(r.data.success) this.allEmployees = r.data.data; } catch(e){} },

        openCreate(){ this.cf = {employee_id:'',leave_type_id:'',from_date:'',to_date:'',reason:''}; this.cErr = ''; this.showCreate = true; },

        async submitCreate(){
            this.cErr = ''; this.creating = true;
            try { await axios.post('/api/v1/leave-requests', this.cf); this.showCreate = false; await this.fetchRequests(); }
            catch(e){ const errs = e.response?.data?.errors; this.cErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'خطأ'); }
            finally { this.creating = false; }
        },

        async approve(req){ try { await axios.post('/api/v1/leave-requests/'+req.id+'/approve'); await this.fetchRequests(this.meta.current_page||1); } catch(e){ alert(e.response?.data?.message||'خطأ'); } },
        openReject(req){ this.rejectingId = req.id; this.rejectReason = ''; this.showReject = true; },
        async submitReject(){
            try { await axios.post('/api/v1/leave-requests/'+this.rejectingId+'/reject', {reason: this.rejectReason}); this.showReject = false; await this.fetchRequests(this.meta.current_page||1); }
            catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },
    };
}
</script>
@endpush
