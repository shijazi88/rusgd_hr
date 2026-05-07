@extends('layouts.app')
@section('title', 'مسارات الموافقات')

@section('content')
<div x-data="approvalsPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1440px] mx-auto px-8 pt-8 pb-20 space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-end">
            <div>
                <h1 class="text-[20px] font-bold text-on-surface font-h2 mb-1">مسارات الموافقات</h1>
                <p class="text-[12px] text-on-surface-variant font-body-secondary">مراجعة الطلبات المعلقة والتاريخية</p>
            </div>
        </div>

        <!-- Tab Bar -->
        <div class="flex gap-2 overflow-x-auto no-scrollbar pb-1">
            <template x-for="tab in tabs" :key="tab.key">
                <button @click="activeTab=tab.key; fetchItems(1)"
                        :class="activeTab===tab.key
                            ? 'bg-primary-container/10 border-primary-container/30 text-primary-container font-semibold'
                            : 'border-outline-variant text-on-surface-variant hover:border-primary-container/40 hover:text-primary-container'"
                        class="flex items-center gap-2 px-4 py-2 rounded-full border text-sm transition-all shrink-0">
                    <span x-text="tab.label"></span>
                    <span class="text-xs px-1.5 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant"
                          x-text="tab.count||0"></span>
                </button>
            </template>
        </div>

        <!-- Table -->
        <div class="bg-surface-container rounded-xl border border-outline-variant overflow-hidden" style="box-shadow:var(--c-shadow)">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm border-collapse">
                    <thead>
                        <tr>
                            <th class="py-4 px-6 font-medium">رقم الطلب</th>
                            <th class="py-4 px-6 font-medium">النوع</th>
                            <th class="py-4 px-6 font-medium">مقدم الطلب</th>
                            <th class="py-4 px-6 font-medium">التفاصيل</th>
                            <th class="py-4 px-6 font-medium">التاريخ</th>
                            <th class="py-4 px-6 font-medium">الحالة</th>
                            <th class="py-4 px-6 font-medium text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <template x-if="loading">
                            <tr><td colspan="7" class="py-12 text-center">
                                <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                            </td></tr>
                        </template>

                        <template x-for="item in items" :key="item.type+'-'+item.id">
                            <tr class="hover:bg-surface-container-high transition-colors">

                                <!-- Reference -->
                                <td class="py-4 px-6 text-primary-container font-medium whitespace-nowrap" x-text="item.reference||('REQ-'+String(item.id).padStart(4,'0'))"></td>

                                <!-- Type -->
                                <td class="py-4 px-6">
                                    <span :class="typeBadge(item.type)" x-text="typeLabel(item.type)"></span>
                                </td>

                                <!-- Requester -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-primary-container border border-outline-variant"
                                             style="background:rgba(14,165,164,0.1)"
                                             x-text="initials(item.requester?.name)"></div>
                                        <div>
                                            <p class="text-on-surface font-medium text-sm" x-text="item.requester?.name||'—'"></p>
                                            <p class="text-on-surface-variant text-xs" x-text="item.requester?.department||''"></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Details -->
                                <td class="py-4 px-6 text-on-surface-variant max-w-[180px] truncate" x-text="item.details||'—'"></td>

                                <!-- Date -->
                                <td class="py-4 px-6 text-on-surface-variant whitespace-nowrap" x-text="(item.created_at||'').slice(0,10)||'—'"></td>

                                <!-- Status + Stage progress -->
                                <td class="py-4 px-6">
                                    <span :class="sBadge(item.status)" x-text="sLabel(item.status)"></span>
                                    <!-- Management progress indicators for initial_approval -->
                                    <template x-if="item.status==='initial_approval' && item.management_status">
                                        <div class="flex gap-3 mt-1.5">
                                            <span class="text-[11px] flex items-center gap-1"
                                                  :class="item.management_status.ceo_approved ? 'text-emerald-500' : 'text-on-surface-variant'">
                                                <span class="material-symbols-outlined text-[14px]"
                                                      style="font-variation-settings:'FILL' 1"
                                                      x-text="item.management_status.ceo_approved ? 'check_circle' : 'radio_button_unchecked'"></span>
                                                CEO
                                            </span>
                                            <span class="text-[11px] flex items-center gap-1"
                                                  :class="item.management_status.hr_approved ? 'text-emerald-500' : 'text-on-surface-variant'">
                                                <span class="material-symbols-outlined text-[14px]"
                                                      style="font-variation-settings:'FILL' 1"
                                                      x-text="item.management_status.hr_approved ? 'check_circle' : 'radio_button_unchecked'"></span>
                                                HR
                                            </span>
                                        </div>
                                    </template>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-center">
                                    <div class="flex justify-center gap-1">
                                        <template x-if="item.can_act">
                                            <div class="flex gap-1">
                                                <button @click="approve(item)"
                                                        class="p-1.5 text-emerald-500 hover:bg-emerald-500/10 rounded transition-colors"
                                                        title="موافقة">
                                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                                </button>
                                                <button @click="openReject(item)"
                                                        class="p-1.5 text-error hover:bg-error/10 rounded transition-colors"
                                                        title="رفض">
                                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                                </button>
                                            </div>
                                        </template>
                                        <button @click="openDetails(item)"
                                                class="p-1.5 text-on-surface-variant hover:bg-surface-container-high rounded transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">info</span>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        </template>

                        <template x-if="!loading && items.length===0">
                            <tr><td colspan="7" class="py-12 text-center text-on-surface-variant text-sm">لا توجد طلبات</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-outline-variant flex justify-between items-center text-xs text-on-surface-variant">
                <span x-text="'عرض '+items.length+' من '+(meta.total||0)+' طلب'"></span>
                <div class="flex gap-2">
                    <button @click="fetchItems(meta.current_page-1)" :disabled="meta.current_page<=1"
                            class="rs-page-btn px-3 py-1 rounded text-xs disabled:opacity-40">السابق</button>
                    <button @click="fetchItems(meta.current_page+1)" :disabled="meta.current_page>=meta.last_page"
                            class="rs-page-btn px-3 py-1 rounded text-xs disabled:opacity-40">التالي</button>
                </div>
            </div>
        </div>

    </main>

    <!-- Details Modal -->
    <div x-show="showDetails" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDetails=false"></div>
        <div class="relative w-full max-w-lg bg-surface-container rounded-[20px] shadow-2xl border border-outline-variant flex flex-col overflow-hidden">

            <!-- Modal header -->
            <div class="flex justify-between items-center px-6 py-5 border-b border-outline-variant bg-surface-container-low border-r-4 border-r-primary-container">
                <h2 class="text-lg font-bold text-on-surface font-h2">تفاصيل الطلب</h2>
                <button @click="showDetails=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto max-h-[60vh]" x-show="selectedItem">
                <!-- Info grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rs-subtle rounded-[10px] p-4 flex flex-col gap-1 border border-outline-variant">
                        <span class="text-[10px] text-on-surface-variant uppercase tracking-wider">رقم الطلب</span>
                        <span class="text-on-surface font-bold text-sm" x-text="selectedItem?.reference||'—'"></span>
                    </div>
                    <div class="rs-subtle rounded-[10px] p-4 flex flex-col gap-1 border border-outline-variant">
                        <span class="text-[10px] text-on-surface-variant uppercase tracking-wider">مقدم الطلب</span>
                        <span class="text-on-surface font-bold text-sm" x-text="selectedItem?.requester?.name||'—'"></span>
                    </div>
                    <div class="rs-subtle rounded-[10px] p-4 flex flex-col gap-1 border border-outline-variant">
                        <span class="text-[10px] text-on-surface-variant uppercase tracking-wider">النوع</span>
                        <span class="text-on-surface font-bold text-sm" x-text="typeLabel(selectedItem?.type)"></span>
                    </div>
                    <div class="rs-subtle rounded-[10px] p-4 flex flex-col gap-1 border border-outline-variant">
                        <span class="text-[10px] text-on-surface-variant uppercase tracking-wider">التفاصيل</span>
                        <span class="text-on-surface font-bold text-sm" x-text="selectedItem?.details||'—'"></span>
                    </div>
                </div>

                <!-- Stage tracker -->
                <div class="rs-subtle rounded-[10px] p-4 border border-outline-variant">
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider mb-3">مسار الموافقة</p>
                    <div class="flex items-center gap-0">
                        <!-- Stage 1 -->
                        <div class="flex flex-col items-center gap-1 flex-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                 :class="stageClass(selectedItem, 1)">
                                <span class="material-symbols-outlined text-[16px]"
                                      style="font-variation-settings:'FILL' 1"
                                      x-text="stageIcon(selectedItem, 1)"></span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant text-center">المدير المباشر</span>
                        </div>
                        <!-- Connector -->
                        <div class="h-0.5 w-8 flex-shrink-0 mb-4"
                             :class="stageConnector(selectedItem)"></div>
                        <!-- Stage 2 CEO -->
                        <div class="flex flex-col items-center gap-1 flex-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                 :class="stageClass(selectedItem, 2, 'ceo')">
                                <span class="material-symbols-outlined text-[16px]"
                                      style="font-variation-settings:'FILL' 1"
                                      x-text="stageIcon(selectedItem, 2, 'ceo')"></span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant text-center">CEO</span>
                        </div>
                        <!-- Connector -->
                        <div class="h-0.5 w-4 flex-shrink-0 mb-4 bg-outline-variant"></div>
                        <!-- Stage 2 HR -->
                        <div class="flex flex-col items-center gap-1 flex-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                 :class="stageClass(selectedItem, 2, 'hr')">
                                <span class="material-symbols-outlined text-[16px]"
                                      style="font-variation-settings:'FILL' 1"
                                      x-text="stageIcon(selectedItem, 2, 'hr')"></span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant text-center">HR</span>
                        </div>
                        <!-- Connector -->
                        <div class="h-0.5 w-8 flex-shrink-0 mb-4"
                             :class="selectedItem?.status==='approved' ? 'bg-emerald-500' : 'bg-outline-variant'"></div>
                        <!-- Final -->
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm"
                                 :class="selectedItem?.status==='approved' ? 'bg-emerald-500/20 text-emerald-500' : 'bg-surface-container-high text-on-surface-variant'">
                                <span class="material-symbols-outlined text-[16px]"
                                      style="font-variation-settings:'FILL' 1">done_all</span>
                            </div>
                            <span class="text-[11px] text-on-surface-variant text-center">مكتمل</span>
                        </div>
                    </div>
                </div>

                <!-- Rejection reason -->
                <template x-if="selectedItem?.rejection_reason">
                    <div class="rs-subtle rounded-[10px] p-4 border border-error/30 bg-error/5">
                        <p class="text-[10px] text-error uppercase tracking-wider mb-1">سبب الرفض</p>
                        <p class="text-on-surface text-sm" x-text="selectedItem.rejection_reason"></p>
                    </div>
                </template>

                <!-- Approval steps log -->
                <template x-if="selectedItem?.approval_steps?.length">
                    <div class="rs-subtle rounded-[10px] p-4 border border-outline-variant">
                        <p class="text-[10px] text-on-surface-variant uppercase tracking-wider mb-3">سجل الموافقات</p>
                        <div class="space-y-2">
                            <template x-for="(step, i) in selectedItem.approval_steps" :key="i">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]"
                                              style="font-variation-settings:'FILL' 1"
                                              :class="step.action==='approved' ? 'text-emerald-500' : 'text-error'"
                                              x-text="step.action==='approved' ? 'check_circle' : 'cancel'"></span>
                                        <span class="text-on-surface" x-text="step.name||'—'"></span>
                                        <span class="text-[10px] text-on-surface-variant px-1.5 py-0.5 rounded bg-surface-container-high"
                                              x-text="'مرحلة '+step.level"></span>
                                    </div>
                                    <span class="text-on-surface-variant text-xs" x-text="(step.at||'').slice(0,10)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal footer actions -->
            <div class="px-6 py-4 border-t border-outline-variant flex gap-3" x-show="selectedItem?.can_act">
                <button @click="approve(selectedItem); showDetails=false;"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">check</span> موافقة
                </button>
                <button @click="openReject(selectedItem); showDetails=false;"
                        class="flex-1 bg-error/10 text-error border border-error/30 hover:bg-error/20 font-semibold py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">close</span> رفض
                </button>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showReject" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-surface-container w-full max-w-md rounded-[20px] p-7 border border-outline-variant shadow-2xl">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-on-surface font-h2">سبب الرفض</h3>
                <button @click="showReject=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <textarea x-model="rejectReason" rows="4"
                      placeholder="اكتب سبب الرفض..."
                      class="w-full bg-surface border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm resize-none mb-4"></textarea>
            <div class="flex gap-3">
                <button @click="submitReject()" class="flex-1 bg-error text-white py-2.5 rounded-lg font-bold text-sm hover:brightness-110 transition-all">رفض</button>
                <button @click="showReject=false" class="rs-btn-cancel flex-1 py-2.5 text-sm font-semibold">إلغاء</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function approvalsPage() {
    return {
        items:[], meta:{}, counts:{all:0, pending:0, approved:0, rejected:0},
        loading:false, activeTab:'pending',
        showDetails:false, selectedItem:null,
        showReject:false, rejectReason:'', rejectingItem:null,

        get tabs(){
            return [
                {key:'pending',  label:'بانتظار إجراء', count:this.counts.pending,  badgeClass:'bg-amber-500/10 text-amber-500'},
                {key:'approved', label:'موافق عليه',    count:this.counts.approved, badgeClass:'bg-emerald-500/10 text-emerald-500'},
                {key:'rejected', label:'مرفوض',         count:this.counts.rejected, badgeClass:'bg-red-500/10 text-red-500'},
                {key:'all',      label:'الكل',           count:this.counts.all,      badgeClass:'bg-surface-variant text-on-surface-variant'},
            ];
        },

        initials(n){ return (n||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]||'').join('').toUpperCase(); },
        typeLabel(t){ return {leave:'إجازة', purchase:'شراء'}[t]||t||'—'; },
        typeBadge(t){
            return t==='leave'
                ? 'bg-primary-container/10 text-primary-container px-2 py-1 rounded text-xs border border-primary-container/20'
                : 'bg-blue-500/10 text-blue-400 px-2 py-1 rounded text-xs border border-blue-500/20';
        },
        sLabel(s){
            return {pending:'مرحلة 1 — المدير', initial_approval:'مرحلة 2 — الإدارة', approved:'موافق عليه', rejected:'مرفوض'}[s]||s;
        },
        sBadge(s){
            const b='px-2 py-1 rounded text-xs border ';
            return {
                pending:          b+'bg-amber-500/10 text-amber-500 border-amber-500/20',
                initial_approval: b+'bg-primary-container/10 text-primary-container border-primary-container/20',
                approved:         b+'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                rejected:         b+'bg-red-500/10 text-red-400 border-red-500/20',
            }[s]||b+'bg-surface-variant text-on-surface-variant border-outline-variant';
        },

        // Stage tracker helpers
        stageClass(item, level, who){
            if(!item) return 'bg-surface-container-high text-on-surface-variant';
            if(item.status==='rejected') return 'bg-error/10 text-error';
            if(level===1){
                const done = item.status==='initial_approval'||item.status==='approved';
                return done ? 'bg-emerald-500/20 text-emerald-500' : 'bg-amber-500/20 text-amber-500';
            }
            if(level===2 && item.management_status){
                const ok = who==='ceo' ? item.management_status.ceo_approved : item.management_status.hr_approved;
                return ok ? 'bg-emerald-500/20 text-emerald-500' : (item.status==='initial_approval' ? 'bg-amber-500/20 text-amber-500' : 'bg-surface-container-high text-on-surface-variant');
            }
            return 'bg-surface-container-high text-on-surface-variant';
        },
        stageIcon(item, level, who){
            if(!item) return 'radio_button_unchecked';
            if(item.status==='rejected') return 'cancel';
            if(level===1) return (item.status==='initial_approval'||item.status==='approved') ? 'check_circle' : 'pending';
            if(level===2 && item.management_status){
                const ok = who==='ceo' ? item.management_status.ceo_approved : item.management_status.hr_approved;
                return ok ? 'check_circle' : (item.status==='initial_approval' ? 'pending' : 'radio_button_unchecked');
            }
            return 'radio_button_unchecked';
        },
        stageConnector(item){
            if(!item) return 'bg-outline-variant';
            return (item.status==='initial_approval'||item.status==='approved') ? 'bg-emerald-500' : 'bg-outline-variant';
        },

        async init(){
            if (await window.$guard('approve_leaves','approve_purchases')) return;
            await Promise.all([this.fetchCounts(), this.fetchItems()]);
        },

        async fetchCounts(){
            try {
                const [r1, r2, r3, r4] = await Promise.all([
                    axios.get('/api/v1/approvals/pending-count'),
                    axios.get('/api/v1/approvals?per_page=1&status=pending'),
                    axios.get('/api/v1/approvals?per_page=1&status=approved'),
                    axios.get('/api/v1/approvals?per_page=1&status=rejected'),
                ]);
                const d = r1.data.data||{};
                this.counts = {
                    all:      (r2.data.meta?.total||0)+(r3.data.meta?.total||0)+(r4.data.meta?.total||0),
                    pending:  d.total||0,
                    approved: r3.data.meta?.total||0,
                    rejected: r4.data.meta?.total||0,
                };
            } catch(e){}
        },

        async fetchItems(page=1){
            this.loading=true;
            try {
                const params = new URLSearchParams({page});
                if(this.activeTab!=='all') params.set('status', this.activeTab);
                const r = await axios.get('/api/v1/approvals?'+params);
                if(r.data.success){ this.items=r.data.data; this.meta=r.data.meta||{}; }
            } catch(e){} finally{ this.loading=false; }
        },

        openDetails(item){ this.selectedItem=item; this.showDetails=true; },
        openReject(item){ this.rejectingItem=item; this.rejectReason=''; this.showReject=true; },

        async approve(item){
            try {
                const ep = item.type==='leave' ? '/api/v1/leave-requests/' : '/api/v1/purchase-requests/';
                await axios.post(ep+item.id+'/approve');
                await Promise.all([this.fetchItems(this.meta.current_page||1), this.fetchCounts()]);
            } catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },

        async submitReject(){
            if(!this.rejectingItem) return;
            try {
                const ep = this.rejectingItem.type==='leave' ? '/api/v1/leave-requests/' : '/api/v1/purchase-requests/';
                await axios.post(ep+this.rejectingItem.id+'/reject', {reason: this.rejectReason});
                this.showReject=false;
                await Promise.all([this.fetchItems(this.meta.current_page||1), this.fetchCounts()]);
            } catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },
    };
}
</script>
@endpush
