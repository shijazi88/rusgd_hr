@extends('layouts.app')
@section('title', 'الأقسام')

@section('content')
<div x-data="departmentsPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
    <main class="max-w-[1200px] mx-auto px-8 pt-8 pb-20">

        {{-- Header --}}
        <div class="flex justify-between items-end mb-10">
            <div>
                <h1 class="text-[20px] font-bold font-h2 text-on-surface mb-1">الأقسام والمسميات الوظيفية</h1>
                <p class="text-[12px] text-on-surface-variant font-body-secondary">انقر على أي قسم لإدارة مسمياته الوظيفية</p>
            </div>
            <button @click="openCreate()"
                    class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all flex items-center gap-2 text-sm">
                قسم جديد <span class="text-xl leading-none">+</span>
            </button>
        </div>

        {{-- Departments list (cards stack vertically so each can expand) --}}
        <div class="flex flex-col gap-4">
            <template x-if="loading">
                <div class="flex justify-center py-12">
                    <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                </div>
            </template>

            <template x-for="dept in departments" :key="dept.id">
                <div class="bg-surface-container rounded-[14px] border border-outline-variant overflow-hidden transition-all"
                     :class="expandedId === dept.id ? 'border-primary-container/40' : 'hover:border-primary-container/30'"
                     style="box-shadow: var(--c-shadow)">

                    {{-- Header row --}}
                    <div class="p-5 flex justify-between items-start gap-3 cursor-pointer"
                         @click="toggleExpand(dept.id)">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                                 style="background:rgba(14,165,164,0.1)">
                                <span class="material-symbols-outlined text-primary-container text-[20px]"
                                      style="font-variation-settings:'FILL' 1">apartment</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-body-main text-on-surface font-semibold text-[15px] truncate"
                                   x-text="dept.name"></p>
                                <p class="font-body-secondary text-on-surface-variant text-xs mt-0.5"
                                   x-text="dept.parent?.name ? ('تابع لـ: ' + dept.parent.name) : 'قسم رئيسي'"></p>
                            </div>
                        </div>

                        {{-- Stats + chevron --}}
                        <div class="flex items-center gap-5 shrink-0">
                            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs">
                                <span class="material-symbols-outlined text-[16px]">group</span>
                                <span class="text-on-surface font-bold" x-text="dept.employees_count || 0"></span>
                                <span>موظف</span>
                            </div>
                            <div class="flex items-center gap-1 text-on-surface-variant" @click.stop>
                                <button @click="openEdit(dept)"
                                        class="p-2 hover:text-primary-container hover:bg-surface-container-high rounded-full transition-colors"
                                        title="تعديل القسم">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button @click="deleteDept(dept)"
                                        class="p-2 hover:text-error hover:bg-error/10 rounded-full transition-colors"
                                        title="حذف القسم">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                            <span class="material-symbols-outlined text-on-surface-variant text-[20px] transition-transform"
                                  :class="expandedId === dept.id ? 'rotate-180' : ''">expand_more</span>
                        </div>
                    </div>

                    {{-- Expanded panel: job titles --}}
                    <div x-show="expandedId === dept.id" x-collapse style="display:none;"
                         class="border-t border-outline-variant bg-surface-container-low px-5 py-4">

                        <div class="flex justify-between items-center mb-3">
                            <p class="text-on-surface text-sm font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] text-primary-container">badge</span>
                                المسميات الوظيفية في هذا القسم
                            </p>
                            <button @click="openCreateJobTitle(dept)"
                                    class="bg-primary-container/10 text-primary-container border border-primary-container/30 hover:bg-primary-container hover:text-white transition-all px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1">
                                مسمى جديد <span class="text-base leading-none">+</span>
                            </button>
                        </div>

                        {{-- Job titles list for this dept --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            <template x-for="jt in jobTitlesByDept[dept.id] || []" :key="jt.id">
                                <div class="bg-surface-container rounded-lg p-3 border border-outline-variant flex justify-between items-center gap-2"
                                     :class="!jt.is_active ? 'opacity-60' : ''">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-on-surface text-sm font-medium truncate" x-text="jt.name"></p>
                                        <p class="text-on-surface-variant text-[11px] mt-0.5">
                                            <span x-text="(jt.employees_count || 0) + ' موظف'"></span>
                                            <template x-if="!jt.is_active"><span class="text-amber-500"> · موقوف</span></template>
                                        </p>
                                    </div>
                                    <div class="flex gap-0.5 shrink-0">
                                        <button @click="openEditJobTitle(jt)"
                                                class="p-1.5 text-on-surface-variant hover:text-primary-container hover:bg-surface-container-high rounded transition-colors"
                                                title="تعديل">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </button>
                                        <button @click="toggleJobTitleActive(jt)"
                                                class="p-1.5 text-on-surface-variant hover:bg-surface-container-high rounded transition-colors"
                                                :class="jt.is_active ? 'hover:text-amber-500' : 'hover:text-emerald-500'"
                                                :title="jt.is_active ? 'إلغاء التفعيل' : 'تفعيل'">
                                            <span class="material-symbols-outlined text-[16px]"
                                                  x-text="jt.is_active ? 'toggle_on' : 'toggle_off'"></span>
                                        </button>
                                        <button @click="deleteJobTitle(jt)"
                                                class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded transition-colors"
                                                title="حذف">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="(jobTitlesByDept[dept.id] || []).length === 0">
                                <div class="col-span-full text-center text-on-surface-variant text-xs py-4">
                                    لا توجد مسميات وظيفية في هذا القسم بعد.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!loading && departments.length === 0">
                <div class="text-center text-on-surface-variant text-sm py-12">
                    لا توجد أقسام مسجّلة. ابدأ بإضافة قسم جديد.
                </div>
            </template>
        </div>
    </main>

    {{-- ── Department Create / Edit Modal ──────────────────────────────── --}}
    <div x-show="showModal" style="display:none;"
         class="fixed inset-0 bg-black/70 backdrop-blur-[4px] z-50 flex items-center justify-center p-4">
        <div class="bg-surface-container w-full max-w-[500px] rounded-[20px] shadow-2xl border border-outline-variant overflow-hidden flex flex-col">

            <div class="px-6 py-5 border-b border-outline-variant flex justify-between items-center border-r-4 border-r-primary-container bg-surface-container-low">
                <h3 class="font-h2 text-on-surface" x-text="editingId ? 'تعديل القسم' : 'إضافة قسم جديد'"></h3>
                <button @click="showModal=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-5">
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">اسم القسم <span class="text-error">*</span></label>
                    <input x-model="form.name" type="text" placeholder="مثال: تطوير البرمجيات"
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                </div>

                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">القسم الرئيسي (اختياري)</label>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="rs-trigger w-full flex justify-between items-center px-4 py-2.5 rounded-lg text-sm">
                            <span x-text="form.parent_id ? (parentName(form.parent_id) || 'اختر قسم رئيسي') : 'بدون قسم رئيسي'"
                                  :class="form.parent_id ? 'text-on-surface' : 'text-on-surface-variant'"></span>
                            <span class="material-symbols-outlined text-on-surface-variant text-[20px]">expand_more</span>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="rs-panel absolute z-50 mt-1 w-full max-h-[240px] overflow-y-auto"
                             style="display:none">
                            <button type="button" @click="form.parent_id=null; open=false"
                                    class="rs-opt"
                                    :class="!form.parent_id ? 'selected' : ''">
                                — بدون قسم رئيسي —
                            </button>
                            <template x-for="d in departments" :key="d.id">
                                <button type="button"
                                        @click="if (!isInvalidParent(d.id)) { form.parent_id = d.id; open = false; }"
                                        class="rs-opt"
                                        :class="[
                                            form.parent_id === d.id ? 'selected' : '',
                                            isInvalidParent(d.id) ? 'opacity-40 cursor-not-allowed' : ''
                                        ]"
                                        :disabled="isInvalidParent(d.id)"
                                        x-text="d.name + (d.id === editingId ? ' (نفس القسم)' : (isInvalidParent(d.id) ? ' (قسم فرعي)' : ''))"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-show="formErr" class="text-error text-sm" x-text="formErr"></div>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex justify-end gap-3">
                <button @click="showModal=false" class="rs-btn-cancel px-5 py-2.5 text-sm font-semibold">إلغاء</button>
                <button @click="saveDept()" :disabled="saving"
                        class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all text-sm disabled:opacity-60">
                    <span x-text="saving ? 'جارٍ الحفظ...' : 'حفظ'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Job Title Create / Edit Modal ──────────────────────────────── --}}
    <div x-show="showJtModal" style="display:none;"
         class="fixed inset-0 bg-black/70 backdrop-blur-[4px] z-50 flex items-center justify-center p-4">
        <div class="bg-surface-container w-full max-w-[500px] rounded-[20px] shadow-2xl border border-outline-variant overflow-hidden flex flex-col">

            <div class="px-6 py-5 border-b border-outline-variant flex justify-between items-center border-r-4 border-r-primary-container bg-surface-container-low">
                <h3 class="font-h2 text-on-surface" x-text="editingJtId ? 'تعديل المسمى الوظيفي' : 'إضافة مسمى وظيفي جديد'"></h3>
                <button @click="showJtModal=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 flex flex-col gap-5">
                <div class="bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface-variant">
                    القسم: <span class="text-on-surface font-bold" x-text="jtForm.department_name || '—'"></span>
                </div>
                <div>
                    <label class="block text-sm font-body-secondary text-on-surface-variant mb-1.5">اسم المسمى <span class="text-error">*</span></label>
                    <input x-model="jtForm.name" type="text" placeholder="مثال: Senior Backend Engineer"
                           class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-on-surface focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-all text-sm">
                </div>
                <div x-show="editingJtId" class="flex items-center gap-2.5">
                    <input id="jt_active" x-model="jtForm.is_active" type="checkbox"
                           class="w-4 h-4 accent-primary-container">
                    <label for="jt_active" class="text-sm text-on-surface cursor-pointer select-none">مفعّل</label>
                </div>
                <div x-show="jtErr" class="text-error text-sm" x-text="jtErr"></div>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex justify-end gap-3">
                <button @click="showJtModal=false" class="rs-btn-cancel px-5 py-2.5 text-sm font-semibold">إلغاء</button>
                <button @click="saveJobTitle()" :disabled="savingJt"
                        class="bg-primary-container text-white px-5 py-2.5 rounded-lg font-body-main font-semibold hover:brightness-110 transition-all text-sm disabled:opacity-60">
                    <span x-text="savingJt ? 'جارٍ الحفظ...' : 'حفظ'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function departmentsPage() {
    return {
        departments: [], loading: false,
        expandedId: null,

        // Department modal state
        showModal: false, editingId: null, saving: false, formErr: '',
        form: { name: '', parent_id: null },

        // Job title state
        jobTitlesByDept: {},
        showJtModal: false, editingJtId: null, savingJt: false, jtErr: '',
        jtForm: { name: '', department_id: null, department_name: '', is_active: true },

        async init(){
            if (await window.$guard('manage_departments')) return;
            await this.fetchDepartments();
        },

        // ── Departments ────────────────────────────────────────────────────
        async fetchDepartments(){
            this.loading = true;
            try {
                const r = await axios.get('/api/v1/departments');
                if (r.data.success) this.departments = r.data.data || [];
            } catch(e){}
            finally { this.loading = false; }
        },

        parentName(id){
            const d = this.departments.find(x => x.id === id);
            return d ? d.name : '';
        },

        get invalidParentIds(){
            if (!this.editingId) return new Set();
            const blocked = new Set([this.editingId]);
            let added = true;
            while (added) {
                added = false;
                for (const d of this.departments) {
                    const pid = d.parent?.id ?? d.parent_id ?? null;
                    if (pid && blocked.has(pid) && !blocked.has(d.id)) {
                        blocked.add(d.id);
                        added = true;
                    }
                }
            }
            return blocked;
        },
        isInvalidParent(id){ return this.invalidParentIds.has(id); },

        async toggleExpand(deptId){
            this.expandedId = this.expandedId === deptId ? null : deptId;
            if (this.expandedId === deptId && !this.jobTitlesByDept[deptId]) {
                await this.fetchJobTitles(deptId);
            }
        },

        openCreate(){
            this.editingId = null;
            this.form = { name: '', parent_id: null };
            this.formErr = '';
            this.showModal = true;
        },

        openEdit(dept){
            this.editingId = dept.id;
            this.form = {
                name: dept.name,
                parent_id: dept.parent?.id ?? dept.parent_id ?? null,
            };
            this.formErr = '';
            this.showModal = true;
        },

        async saveDept(){
            this.formErr = '';
            this.saving = true;
            try {
                const payload = { name: this.form.name, parent_id: this.form.parent_id };
                if (this.editingId) {
                    await axios.put('/api/v1/departments/' + this.editingId, payload);
                } else {
                    await axios.post('/api/v1/departments', payload);
                }
                this.showModal = false;
                await this.fetchDepartments();
            } catch(e){
                const errs = e.response?.data?.errors;
                this.formErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'حدث خطأ');
            } finally {
                this.saving = false;
            }
        },

        async deleteDept(dept){
            if (!confirm('هل أنت متأكد من حذف القسم "' + dept.name + '"؟')) return;
            try {
                await axios.delete('/api/v1/departments/' + dept.id);
                if (this.expandedId === dept.id) this.expandedId = null;
                await this.fetchDepartments();
            } catch(e){
                alert(e.response?.data?.message || 'لا يمكن حذف القسم — قد يحتوي على موظفين أو أقسام فرعية.');
            }
        },

        // ── Job titles ─────────────────────────────────────────────────────
        async fetchJobTitles(deptId){
            try {
                const r = await axios.get('/api/v1/job-titles?include_inactive=1&department_id=' + deptId);
                if (r.data.success) this.jobTitlesByDept[deptId] = r.data.data || [];
            } catch(e){
                this.jobTitlesByDept[deptId] = [];
            }
        },

        openCreateJobTitle(dept){
            this.editingJtId = null;
            this.jtForm = { name: '', department_id: dept.id, department_name: dept.name, is_active: true };
            this.jtErr = '';
            this.showJtModal = true;
        },

        openEditJobTitle(jt){
            this.editingJtId = jt.id;
            const dept = this.departments.find(d => d.id === jt.department_id);
            this.jtForm = {
                name: jt.name,
                department_id: jt.department_id,
                department_name: dept?.name || jt.department?.name || '',
                is_active: !!jt.is_active,
            };
            this.jtErr = '';
            this.showJtModal = true;
        },

        async saveJobTitle(){
            this.jtErr = ''; this.savingJt = true;
            const deptId = this.jtForm.department_id;
            try {
                if (this.editingJtId) {
                    await axios.put('/api/v1/job-titles/' + this.editingJtId, {
                        name: this.jtForm.name,
                        is_active: this.jtForm.is_active,
                    });
                } else {
                    await axios.post('/api/v1/job-titles', {
                        department_id: deptId,
                        name: this.jtForm.name,
                    });
                }
                this.showJtModal = false;
                await this.fetchJobTitles(deptId);
            } catch(e){
                const errs = e.response?.data?.errors;
                this.jtErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'حدث خطأ');
            } finally {
                this.savingJt = false;
            }
        },

        async toggleJobTitleActive(jt){
            try {
                await axios.put('/api/v1/job-titles/' + jt.id, { is_active: !jt.is_active });
                await this.fetchJobTitles(jt.department_id);
            } catch(e){ alert(e.response?.data?.message || 'حدث خطأ'); }
        },

        async deleteJobTitle(jt){
            if (!confirm('هل أنت متأكد من حذف "' + jt.name + '"؟')) return;
            try {
                await axios.delete('/api/v1/job-titles/' + jt.id);
                await this.fetchJobTitles(jt.department_id);
            } catch(e){
                alert(e.response?.data?.message || 'لا يمكن حذف المسمى الوظيفي. ألغِ تفعيله بدلاً من ذلك.');
            }
        },
    };
}
</script>
@endpush
