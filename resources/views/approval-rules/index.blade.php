@extends('layouts.app')
@section('title', 'القواعد والحدود')

@section('content')
<div x-data="approvalRulesPage()" x-init="init()" class="bg-[#071426] min-h-screen">
    <main class="max-w-[1200px] mx-auto px-8 pt-8 pb-20">

        <!-- Header -->
        <div class="flex justify-between items-end mb-12">
            <div>
                <h1 class="text-[20px] font-bold font-h2 text-on-surface mb-1">القواعد والحدود</h1>
                <p class="text-[12px] text-on-surface-variant font-body-secondary">إدارة قواعد العمل وحدود الموافقات التلقائية</p>
            </div>
            <button @click="openCreate('financial')" class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all flex items-center gap-2 text-sm">
                قاعدة جديدة <span class="text-xl leading-none">+</span>
            </button>
        </div>

        <!-- Two-column Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Financial Rules -->
            <section class="flex flex-col gap-4">
                <div class="flex items-center gap-3 mb-1 px-1">
                    <span class="material-symbols-outlined text-amber-500" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span>
                    <h2 class="font-h2 text-on-surface text-lg">قواعد مالية</h2>
                </div>
                <template x-for="rule in financialRules" :key="rule.id">
                    <div class="rs-subtle rounded-[10px] p-4 flex justify-between items-center border-l-4 transition-all hover:border-primary-container/30"
                         :class="ruleBorderColor(rule)">
                        <div>
                            <p class="font-body-main text-on-surface font-semibold mb-1" x-text="rule.name"></p>
                            <p class="font-body-secondary text-on-surface-variant text-sm" x-text="rule.approver_role||rule.approver||'تلقائي'"></p>
                        </div>
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <button @click="openEdit(rule)" class="p-2 hover:text-primary-container transition-colors rounded-full hover:bg-surface-container">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button @click="deleteRule(rule)" class="p-2 hover:text-error transition-colors rounded-full hover:bg-surface-container">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="financialRules.length===0">
                    <div class="text-center text-on-surface-variant text-sm py-6">لا توجد قواعد مالية</div>
                </template>
            </section>

            <!-- Leave Rules -->
            <section class="flex flex-col gap-4">
                <div class="flex items-center gap-3 mb-1 px-1">
                    <span class="material-symbols-outlined text-primary-container" style="font-variation-settings:'FILL' 1;">calendar_month</span>
                    <h2 class="font-h2 text-on-surface text-lg">قواعد الإجازات</h2>
                </div>
                <template x-for="rule in leaveRules" :key="rule.id">
                    <div class="rs-subtle rounded-[10px] p-4 flex justify-between items-center border-l-4 transition-all hover:border-primary-container/30"
                         :class="ruleBorderColor(rule)">
                        <div>
                            <p class="font-body-main text-on-surface font-semibold mb-1" x-text="rule.name"></p>
                            <p class="font-body-secondary text-on-surface-variant text-sm" x-text="rule.approver_role||rule.approver||'تلقائي'"></p>
                        </div>
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <button @click="openEdit(rule)" class="p-2 hover:text-primary-container transition-colors rounded-full hover:bg-surface-container">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button @click="deleteRule(rule)" class="p-2 hover:text-error transition-colors rounded-full hover:bg-surface-container">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="leaveRules.length===0">
                    <div class="text-center text-on-surface-variant text-sm py-6">لا توجد قواعد إجازات</div>
                </template>
            </section>

        </div>
    </main>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" style="display:none;" class="fixed inset-0 bg-black/70 backdrop-blur-[4px] z-50 flex items-center justify-center p-4">
        <div class="bg-[#0B1F3A] w-full max-w-[600px] rounded-[20px] shadow-2xl border border-surface-container overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-surface-container flex justify-between items-center border-r-4 border-r-primary-container bg-surface-container-low">
                <h3 class="font-h2 text-on-surface" x-text="editingId ? 'تعديل القاعدة' : 'إضافة قاعدة جديدة'"></h3>
                <button @click="showModal=false" class="text-surface-variant hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-6 flex flex-col gap-5">
                <!-- Type Toggle -->
                <div class="flex bg-surface-container p-1 rounded-lg">
                    <button @click="form.type='financial'"
                            :class="form.type==='financial' ? 'bg-primary-container text-white shadow-sm' : 'text-on-surface-variant hover:text-white'"
                            class="flex-1 py-2 text-center rounded-md font-body-main font-semibold transition-all text-sm">مالية</button>
                    <button @click="form.type='leave'"
                            :class="form.type==='leave' ? 'bg-primary-container text-white shadow-sm' : 'text-on-surface-variant hover:text-white'"
                            class="flex-1 py-2 text-center rounded-md font-body-main font-semibold transition-all text-sm">إجازات</button>
                </div>
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1">اسم القاعدة</label>
                    <input x-model="form.name" type="text" placeholder="مثال: المشتريات الطارئة"
                           class="w-full bg-[#050F1E] border border-surface-container rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                </div>
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1" x-text="form.type==='financial' ? 'الحد الأقصى (ر.س)' : 'الحد الأقصى (أيام)'"></label>
                    <input x-model="form.max_amount" type="number" dir="ltr" :placeholder="form.type === 'financial' ? '5000' : '30'"
                           class="w-full bg-[#050F1E] border border-surface-container rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                </div>
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1">المعتمد (الموافق)</label>
                    <div class="flex border border-outline-variant rounded-lg overflow-hidden bg-surface-container-low">
                        <button type="button"
                                @click="form.approver_role = 'main_manager'"
                                :class="form.approver_role === 'main_manager'
                                    ? 'bg-primary-container text-white'
                                    : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'"
                                class="flex-1 py-2.5 px-4 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">workspace_premium</span>
                            مدير عام
                        </button>
                        <div class="w-px bg-outline-variant"></div>
                        <button type="button"
                                @click="form.approver_role = 'direct_manager'"
                                :class="form.approver_role === 'direct_manager'
                                    ? 'bg-primary-container text-white'
                                    : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface'"
                                class="flex-1 py-2.5 px-4 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">supervisor_account</span>
                            مدير مباشر
                        </button>
                    </div>
                </div>
                <div x-show="formErr" class="text-error text-sm" x-text="formErr"></div>
            </div>
            <div class="px-6 py-4 bg-surface-container-lowest border-t border-surface-container flex justify-end gap-3">
                <button @click="showModal=false" class="rs-btn-cancel px-5 py-2.5 text-sm font-semibold">إلغاء</button>
                <button @click="saveRule()" :disabled="saving" class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all text-sm disabled:opacity-60">
                    <span x-text="saving?'جارٍ الحفظ...':'حفظ القاعدة'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function approvalRulesPage() {
    return {
        rules:[], loading:false,
        showModal:false, editingId:null, saving:false, formErr:'',
        form:{type:'financial', name:'', max_amount:'', approver_role:''},

        get financialRules(){ return this.rules.filter(r=>r.type==='financial'||r.rule_type==='financial'); },
        get leaveRules(){ return this.rules.filter(r=>r.type==='leave'||r.rule_type==='leave'); },

        ruleBorderColor(rule){
            const idx=this.rules.indexOf(rule)%4;
            return ['border-l-emerald-500','border-l-primary-container','border-l-amber-500','border-l-error'][idx]||'border-l-primary-container';
        },

        async init(){
            if (await window.$guard('manage_rules')) return;
            await this.fetchRules();
        },

        async fetchRules(){
            try { const r=await axios.get('/api/v1/approval-rules'); if(r.data.success)this.rules=r.data.data||[]; }
            catch(e){}
        },

        openCreate(type='financial'){
            this.editingId=null;
            this.form={type, name:'', max_amount:'', approver_role:'direct_manager'};
            this.formErr=''; this.showModal=true;
        },

        openEdit(rule){
            this.editingId=rule.id;
            this.form={
                type: rule.type||rule.rule_type||'financial',
                name: rule.name,
                max_amount: rule.max_amount||rule.financial_limit||'',
                approver_role: rule.approver_role||rule.approver||'',
            };
            this.formErr=''; this.showModal=true;
        },

        async saveRule(){
            this.formErr=''; this.saving=true;
            try {
                if(this.editingId){
                    await axios.put('/api/v1/approval-rules/'+this.editingId, this.form);
                } else {
                    await axios.post('/api/v1/approval-rules', this.form);
                }
                this.showModal=false; await this.fetchRules();
            } catch(e){
                const errs=e.response?.data?.errors;
                this.formErr=errs?Object.values(errs).flat().join(' '):(e.response?.data?.message||'خطأ');
            } finally{this.saving=false;}
        },

        async deleteRule(rule){
            if(!confirm('هل أنت متأكد من حذف هذه القاعدة؟')) return;
            try { await axios.delete('/api/v1/approval-rules/'+rule.id); await this.fetchRules(); }
            catch(e){ alert(e.response?.data?.message||'خطأ'); }
        },
    };
}
</script>
@endpush
