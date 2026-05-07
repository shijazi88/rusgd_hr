@extends('layouts.app')
@section('title', 'طلبات الشراء')

@section('content')
<div x-data="purchaseRequestsPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20">

        {{-- Header --}}
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="font-h1 text-[20px] font-[800] text-on-surface mb-1">طلبات الشراء</h1>
                <p class="text-on-surface-variant text-[12px] font-body-secondary">إدارة وتتبع ميزانية المشتريات والطلبات المعلقة</p>
            </div>
            <button @click="openCreate()"
                    class="bg-primary-container text-white px-5 py-2.5 rounded-[10px] font-bold text-sm flex items-center gap-2 hover:brightness-110 transition-all">
                طلب شراء جديد <span class="text-xl leading-none">+</span>
            </button>
        </div>

        {{-- Budget Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">

            {{-- Approved --}}
            <div class="bg-surface-container rounded-[16px] p-6 border border-outline-variant hover:border-primary-container/40 transition-all"
                 style="box-shadow:var(--c-shadow)">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-on-surface-variant text-sm font-body-secondary">الميزانية الشهرية</span>
                    <span class="material-symbols-outlined text-primary-container opacity-60">account_balance_wallet</span>
                </div>
                <div class="font-bold text-2xl text-on-surface mb-3 font-body-main"
                     x-text="formatMoney(budgetStats.approved?.total||0) + ' ر.س'"></div>
                <div class="w-full bg-surface-container-high h-1.5 rounded-full mb-2 overflow-hidden">
                    <div class="bg-primary-container h-full rounded-full transition-all duration-700"
                         :style="'width:'+approvedRate+'%'"></div>
                </div>
                <div class="text-[12px] text-on-surface-variant font-body-secondary">من إجمالي الطلبات المقدمة</div>
            </div>

            {{-- Pending --}}
            <div class="bg-surface-container rounded-[16px] p-6 border border-outline-variant hover:border-amber-500/40 transition-all"
                 style="box-shadow:var(--c-shadow)">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-on-surface-variant text-sm font-body-secondary">معلقة</span>
                    <span class="material-symbols-outlined text-amber-500 opacity-60">pending_actions</span>
                </div>
                <div class="font-bold text-2xl text-amber-500 mb-3 font-body-main"
                     x-text="formatMoney(budgetStats.pending?.total||0) + ' ر.س'"></div>
                <div class="text-[12px] text-on-surface-variant font-body-secondary"
                     x-text="(budgetStats.pending?.count||0) + ' طلبات تنتظر'"></div>
            </div>

            {{-- Completed --}}
            <div class="bg-surface-container rounded-[16px] p-6 border border-outline-variant hover:border-emerald-500/40 transition-all"
                 style="box-shadow:var(--c-shadow)">
                <div class="flex justify-between items-start mb-4">
                    <span class="text-on-surface-variant text-sm font-body-secondary">مكتملة</span>
                    <span class="material-symbols-outlined text-emerald-500 opacity-60">check_circle</span>
                </div>
                <div class="font-bold text-2xl text-emerald-500 mb-3 font-body-main"
                     x-text="formatMoney(budgetStats.approved?.total||0) + ' ر.س'"></div>
                <div class="text-[12px] text-on-surface-variant font-body-secondary"
                     x-text="(budgetStats.approved?.count||0) + ' طلبات'"></div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-surface-container rounded-xl border border-outline-variant overflow-hidden"
             style="box-shadow:var(--c-shadow)">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">مقدم الطلب</th>
                        <th class="px-6 py-4">البند</th>
                        <th class="px-6 py-4">القيمة</th>
                        <th class="px-6 py-4">التاريخ</th>
                        <th class="px-6 py-4">الحالة</th>
                        <th class="px-6 py-4 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant">
                    <template x-if="loading">
                        <tr><td colspan="7" class="p-10 text-center">
                            <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                        </td></tr>
                    </template>
                    <template x-for="(pr, idx) in requests" :key="pr.id">
                        <tr class="hover:bg-surface-container-high transition-colors">
                            <td class="px-6 py-4 text-primary-container font-medium font-body-secondary"
                                x-text="'PR-'+String(pr.id).padStart(3,'0')"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-primary-container border border-outline-variant"
                                         style="background:rgba(14,165,164,0.1)"
                                         x-text="initials(pr.employee?.name)"></div>
                                    <span class="text-on-surface" x-text="pr.employee?.name||'—'"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-on-surface" x-text="pr.item_name||'—'"></td>
                            <td class="px-6 py-4 font-body-secondary text-on-surface font-semibold"
                                x-text="formatMoney(pr.amount) + ' ر.س'"></td>
                            <td class="px-6 py-4 font-body-secondary text-on-surface-variant"
                                x-text="pr.created_at?.split('T')[0]||'—'"></td>
                            <td class="px-6 py-4">
                                <span :class="sBadge(pr.status)" x-text="sLabel(pr.status)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div x-show="pr.status==='pending' || pr.status==='initial_approval'" class="flex items-center justify-center gap-2">
                                    <button @click="approve(pr)"
                                            class="rs-badge rs-badge-active hover:opacity-90 transition-opacity px-3 py-1 rounded-md text-[12px] font-bold">موافق</button>
                                    <button @click="openReject(pr)"
                                            class="rs-badge rs-badge-terminated hover:opacity-90 transition-opacity px-3 py-1 rounded-md text-[12px] font-bold">رفض</button>
                                    <button x-show="isOwner(pr) && pr.status==='pending'" @click="deleteReq(pr)"
                                            class="p-1.5 rounded-lg text-on-surface-variant hover:text-error hover:bg-error/10 transition-all"
                                            title="إلغاء الطلب">
                                        <span class="material-symbols-outlined text-[17px]">delete</span>
                                    </button>
                                </div>
                                <span x-show="pr.status!=='pending' && pr.status!=='initial_approval'"
                                      class="text-on-surface-variant flex justify-center">—</span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && requests.length===0">
                        <tr><td colspan="7" class="p-10 text-center text-on-surface-variant text-sm">لا توجد طلبات شراء</td></tr>
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
        <div class="bg-surface-container w-full max-w-xl rounded-[20px] p-7 border border-outline-variant shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-on-surface">طلب شراء جديد</h3>
                <button @click="showCreate=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            {{-- Info banner --}}
            <div class="bg-primary-container/8 border border-primary-container/25 rounded-xl p-4 mb-5 flex items-center gap-3">
                <span class="material-symbols-outlined text-primary-container text-[18px]">info</span>
                <p class="text-sm text-primary-container font-body-secondary">سيتم إرسال الطلب للمراجعة والموافقة وفق قواعد الشراء المعتمدة</p>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">اسم البند</label>
                        <input x-model="cf.item_name" type="text" placeholder="مثال: أجهزة حاسوب"
                               class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm placeholder:text-on-surface-variant">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">القيمة (ر.س)</label>
                        <input x-model="cf.amount" type="number" dir="ltr" placeholder="0.00"
                               class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm placeholder:text-on-surface-variant">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">الكمية</label>
                    <input x-model="cf.quantity" type="number" dir="ltr" placeholder="1"
                           class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm placeholder:text-on-surface-variant">
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">السبب / المبرر</label>
                    <textarea x-model="cf.reason" rows="3" placeholder="اشرح لماذا تحتاج لهذا الطلب..."
                              class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm resize-none placeholder:text-on-surface-variant"></textarea>
                </div>
                <div x-show="cErr" class="text-error text-sm" x-text="cErr"></div>
                <div class="flex gap-3 pt-2">
                    <button @click="submitCreate()" :disabled="creating"
                            class="flex-1 bg-primary-container text-white py-2.5 rounded-lg font-bold hover:brightness-110 disabled:opacity-60 text-sm transition-all">
                        <span x-text="creating?'جارٍ الإرسال...':'إرسال الطلب'"></span>
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
function purchaseRequestsPage() {
    return {
        requests:[], meta:{}, budgetStats:{}, loading:false,
        showCreate:false, creating:false, cErr:'',
        cf:{item_name:'', amount:'', quantity:1, reason:''},
        showReject:false, rejectReason:'', rejectingId:null,

        get approvedRate(){
            const app = this.budgetStats.approved?.total || 0;
            const pen = this.budgetStats.pending?.total  || 0;
            const tot = app + pen;
            return tot > 0 ? Math.min(Math.round((app/tot)*100), 100) : 0;
        },

        initials(n){ return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase(); },
        formatMoney(v){ return v ? Number(v).toLocaleString('ar-SA', {maximumFractionDigits:0}) : '0'; },
        sLabel(s){ return {pending:'مرحلة 1', initial_approval:'مرحلة 2', approved:'مكتمل', rejected:'مرفوض'}[s] || s; },
        sBadge(s){
            return 'rs-badge ' + ({pending:'rs-badge-on_leave', initial_approval:'rs-badge-on_leave', approved:'rs-badge-active', rejected:'rs-badge-terminated'}[s] || 'rs-badge-inactive');
        },

        isOwner(pr){
            try { const u = JSON.parse(localStorage.getItem('auth_user')||'{}'); return u?.employee?.id === pr.employee_id || u?.employee?.id === pr.employee?.id; }
            catch(e){ return false; }
        },

        async init(){ await Promise.all([this.fetchRequests(), this.fetchBudgetStats()]); },

        async fetchRequests(page=1){
            this.loading = true;
            try { const r = await axios.get('/api/v1/purchase-requests?page='+page); if(r.data.success){ this.requests = r.data.data; this.meta = r.data.meta; } }
            catch(e){} finally { this.loading = false; }
        },

        async fetchBudgetStats(){
            try { const r = await axios.get('/api/v1/purchase-requests/budget-stats'); if(r.data.success) this.budgetStats = r.data.data || {}; }
            catch(e){}
        },

        openCreate(){
            const user = JSON.parse(localStorage.getItem('auth_user')||'{}');
            this.cf = {item_name:'', amount:'', quantity:1, reason:'', employee_id: user?.employee?.id||''};
            this.cErr = ''; this.showCreate = true;
        },

        async submitCreate(){
            this.cErr = ''; this.creating = true;
            try { await axios.post('/api/v1/purchase-requests', this.cf); this.showCreate = false; await Promise.all([this.fetchRequests(), this.fetchBudgetStats()]); }
            catch(e){ const errs = e.response?.data?.errors; this.cErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message||'خطأ'); }
            finally { this.creating = false; }
        },

        async approve(pr){ try { await axios.post('/api/v1/purchase-requests/'+pr.id+'/approve'); await Promise.all([this.fetchRequests(this.meta.current_page||1), this.fetchBudgetStats()]); } catch(e){ alert(e.response?.data?.message||'خطأ'); } },
        openReject(pr){ this.rejectingId = pr.id; this.rejectReason = ''; this.showReject = true; },
        async submitReject(){
            try { await axios.post('/api/v1/purchase-requests/'+this.rejectingId+'/reject', {reason: this.rejectReason}); this.showReject = false; await Promise.all([this.fetchRequests(this.meta.current_page||1), this.fetchBudgetStats()]); }
            catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },
        async deleteReq(pr){
            if(!confirm('هل أنت متأكد من الإلغاء؟')) return;
            try { await axios.delete('/api/v1/purchase-requests/'+pr.id); await this.fetchRequests(this.meta.current_page||1); }
            catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },
    };
}
</script>
@endpush
