@extends('layouts.app')
@section('title', 'إعدادات الشركة')

@section('content')
<div x-data="companyPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1100px] mx-auto px-8 pt-8 pb-20 space-y-10">

        {{-- Header --}}
        <div>
            <h1 class="text-[20px] font-bold font-h2 text-on-surface mb-1">إعدادات الشركة</h1>
            <p class="text-[12px] text-on-surface-variant font-body-secondary">اسم الشركة، أنواع العقود، والمسميات الوظيفية</p>
        </div>

        {{-- ── Section 1: Company info ───────────────────────────────────── --}}
        <section class="bg-surface-container rounded-xl border border-outline-variant overflow-hidden"
                 style="box-shadow:var(--c-shadow)">
            <div class="px-6 py-5 border-b border-outline-variant border-r-4 border-r-primary-container bg-surface-container-low flex items-center gap-3">
                <span class="material-symbols-outlined text-primary-container text-[22px]"
                      style="font-variation-settings:'FILL' 1">business</span>
                <h2 class="font-h2 text-on-surface text-lg">معلومات الشركة</h2>
            </div>
            <div class="p-6">
                <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">اسم الشركة</label>
                <div class="flex gap-3 max-w-[500px]">
                    <input x-model="company.name" type="text" placeholder="اسم الشركة"
                           class="flex-1 bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                    <button @click="saveCompany()" :disabled="savingCompany"
                            class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all text-sm disabled:opacity-60">
                        <span x-text="savingCompany ? 'جارٍ الحفظ...' : 'حفظ'"></span>
                    </button>
                </div>
                <p x-show="companyMsg" class="text-emerald-500 text-xs mt-2" x-text="companyMsg"></p>
            </div>
        </section>

        {{-- ── Section 2: Contract types ─────────────────────────────────── --}}
        <section class="bg-surface-container rounded-xl border border-outline-variant overflow-hidden"
                 style="box-shadow:var(--c-shadow)">
            <div class="px-6 py-5 border-b border-outline-variant border-r-4 border-r-amber-500 bg-surface-container-low flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-500 text-[22px]"
                          style="font-variation-settings:'FILL' 1">description</span>
                    <h2 class="font-h2 text-on-surface text-lg">أنواع العقود</h2>
                </div>
                <button @click="openCreateContract()"
                        class="bg-primary-container text-white px-4 py-2 rounded-lg font-semibold text-sm hover:brightness-110 transition-all flex items-center gap-1">
                    نوع عقد جديد <span class="text-lg leading-none">+</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm border-collapse">
                    <thead>
                        <tr>
                            <th class="py-3 px-6 font-medium">الاسم</th>
                            <th class="py-3 px-6 font-medium">المعرّف</th>
                            <th class="py-3 px-6 font-medium">الوصف</th>
                            <th class="py-3 px-6 font-medium">عدد الموظفين</th>
                            <th class="py-3 px-6 font-medium">الحالة</th>
                            <th class="py-3 px-6 font-medium text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <template x-if="loadingTypes">
                            <tr><td colspan="6" class="py-10 text-center">
                                <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                            </td></tr>
                        </template>

                        <template x-for="t in contractTypes" :key="t.id">
                            <tr class="hover:bg-surface-container-high transition-colors"
                                :class="!t.is_active ? 'opacity-60' : ''">
                                <td class="py-4 px-6 text-on-surface font-medium" x-text="t.name"></td>
                                <td class="py-4 px-6 text-on-surface-variant text-xs font-mono" dir="ltr" x-text="t.slug"></td>
                                <td class="py-4 px-6 text-on-surface-variant text-xs max-w-[260px] truncate" x-text="t.description || '—'"></td>
                                <td class="py-4 px-6 text-on-surface" x-text="t.employees_count || 0"></td>
                                <td class="py-4 px-6">
                                    <span :class="t.is_active ? 'rs-badge rs-badge-active' : 'rs-badge rs-badge-inactive'"
                                          x-text="t.is_active ? 'مفعّل' : 'موقوف'"></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex justify-center gap-1">
                                        <button @click="openEditContract(t)"
                                                class="p-1.5 text-on-surface-variant hover:text-primary-container hover:bg-surface-container-high rounded transition-colors"
                                                title="تعديل">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="toggleActive(t)"
                                                class="p-1.5 text-on-surface-variant hover:bg-surface-container-high rounded transition-colors"
                                                :class="t.is_active ? 'hover:text-amber-500' : 'hover:text-emerald-500'"
                                                :title="t.is_active ? 'إلغاء التفعيل' : 'تفعيل'">
                                            <span class="material-symbols-outlined text-[18px]"
                                                  x-text="t.is_active ? 'toggle_on' : 'toggle_off'"></span>
                                        </button>
                                        <button @click="deleteContract(t)"
                                                class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded transition-colors"
                                                title="حذف">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loadingTypes && contractTypes.length === 0">
                            <tr><td colspan="6" class="py-10 text-center text-on-surface-variant text-sm">
                                لا توجد أنواع عقود. أضِف أول نوع لتتمكن من إنشاء الموظفين.
                            </td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    {{-- Contract Type Modal --}}
    <div x-show="showContractModal" style="display:none;"
         class="fixed inset-0 bg-black/70 backdrop-blur-[4px] z-50 flex items-center justify-center p-4">
        <div class="bg-surface-container w-full max-w-[500px] rounded-[20px] shadow-2xl border border-outline-variant overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-outline-variant flex justify-between items-center border-r-4 border-r-amber-500 bg-surface-container-low">
                <h3 class="font-h2 text-on-surface" x-text="editingContractId ? 'تعديل نوع العقد' : 'إضافة نوع عقد جديد'"></h3>
                <button @click="showContractModal=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-5">
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">الاسم <span class="text-error">*</span></label>
                    <input x-model="contractForm.name" type="text" placeholder="مثال: دوام جزئي"
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                </div>
                <div x-show="!editingContractId">
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">المعرّف (Slug) — اختياري</label>
                    <input x-model="contractForm.slug" type="text" placeholder="part_time" dir="ltr"
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm font-mono">
                    <p class="text-[11px] text-on-surface-variant mt-1">يُولَّد تلقائياً من الاسم إن تُرِك فارغاً.</p>
                </div>
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">الوصف</label>
                    <textarea x-model="contractForm.description" rows="3" placeholder="وصف موجز…"
                              class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm resize-none"></textarea>
                </div>
                <div x-show="editingContractId" class="flex items-center gap-2.5">
                    <input id="ct_active" x-model="contractForm.is_active" type="checkbox"
                           class="w-4 h-4 accent-primary-container">
                    <label for="ct_active" class="text-sm text-on-surface cursor-pointer select-none">مفعّل</label>
                </div>

                <div x-show="contractErr" class="text-error text-sm" x-text="contractErr"></div>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex justify-end gap-3">
                <button @click="showContractModal=false" class="rs-btn-cancel px-5 py-2.5 text-sm font-semibold">إلغاء</button>
                <button @click="saveContract()" :disabled="savingContract"
                        class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all text-sm disabled:opacity-60">
                    <span x-text="savingContract ? 'جارٍ الحفظ...' : 'حفظ'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function companyPage() {
    return {
        company: { name: '' },
        savingCompany: false, companyMsg: '',

        contractTypes: [], loadingTypes: false,
        showContractModal: false, editingContractId: null, savingContract: false, contractErr: '',
        contractForm: { name: '', slug: '', description: '', is_active: true },

        async init(){
            if (await window.$guard('manage_company_settings')) return;
            await Promise.all([this.fetchCompany(), this.fetchTypes()]);
        },

        // ── Company name ──────────────────────────────────────────────────
        async fetchCompany(){
            try {
                const r = await axios.get('/api/v1/company-settings');
                if (r.data.success) this.company = r.data.data || { name: '' };
            } catch(e){}
        },
        async saveCompany(){
            this.savingCompany = true; this.companyMsg = '';
            try {
                await axios.put('/api/v1/company-settings', { name: this.company.name });
                this.companyMsg = 'تم حفظ التغييرات.';
                setTimeout(() => this.companyMsg = '', 2500);
            } catch(e){
                this.companyMsg = 'فشل الحفظ.';
            } finally { this.savingCompany = false; }
        },

        // ── Contract types ────────────────────────────────────────────────
        async fetchTypes(){
            this.loadingTypes = true;
            try {
                const r = await axios.get('/api/v1/contract-types?include_inactive=1');
                if (r.data.success) this.contractTypes = r.data.data || [];
            } catch(e){}
            finally { this.loadingTypes = false; }
        },
        openCreateContract(){
            this.editingContractId = null;
            this.contractForm = { name: '', slug: '', description: '', is_active: true };
            this.contractErr = '';
            this.showContractModal = true;
        },
        openEditContract(t){
            this.editingContractId = t.id;
            this.contractForm = { name: t.name, slug: t.slug, description: t.description || '', is_active: !!t.is_active };
            this.contractErr = '';
            this.showContractModal = true;
        },
        async saveContract(){
            this.contractErr = ''; this.savingContract = true;
            try {
                if (this.editingContractId) {
                    const { name, description, is_active } = this.contractForm;
                    await axios.put('/api/v1/contract-types/' + this.editingContractId, { name, description, is_active });
                } else {
                    await axios.post('/api/v1/contract-types', this.contractForm);
                }
                this.showContractModal = false;
                await this.fetchTypes();
            } catch(e){
                const errs = e.response?.data?.errors;
                this.contractErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'حدث خطأ');
            } finally { this.savingContract = false; }
        },
        async toggleActive(t){
            try {
                await axios.put('/api/v1/contract-types/' + t.id, { is_active: !t.is_active });
                await this.fetchTypes();
            } catch(e){ alert(e.response?.data?.message || 'حدث خطأ'); }
        },
        async deleteContract(t){
            if (!confirm('هل أنت متأكد من حذف "' + t.name + '"؟')) return;
            try {
                await axios.delete('/api/v1/contract-types/' + t.id);
                await this.fetchTypes();
            } catch(e){
                alert(e.response?.data?.message || 'لا يمكن حذف نوع العقد. ألغِ تفعيله بدلاً من ذلك.');
            }
        },
    };
}
</script>
@endpush
