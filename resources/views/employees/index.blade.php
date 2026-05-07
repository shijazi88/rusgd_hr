@extends('layouts.app')
@section('title', 'إدارة الموظفين')

@section('content')
<div x-data="employeesPage()" x-init="init()" class="bg-[#071426] min-h-screen">
    <div class="max-w-[1440px] mx-auto px-8 py-8">

        <!-- Page Header -->
        <header class="flex justify-between items-end mb-8">
            <div>
                <h1 class="font-h1 text-[20px] font-[800] text-[#F1F5F9]">سجلات الموظفين</h1>
                <p class="font-body-secondary text-[12px] text-[#475569] mt-1">إدارة بيانات الموظفين والعقود</p>
            </div>
            <div class="flex gap-3">
                <button @click="openCreate()"
                        class="px-5 py-2 rounded-xl bg-primary-container text-white hover:brightness-110 active:scale-95 transition-all font-body-secondary font-semibold flex items-center gap-2 text-sm">
                    موظف جديد
                    <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">add</span>
                </button>
            </div>
        </header>

        <!-- Main Table Card -->
        <section class="bg-[#0B1F3A] rounded-2xl border border-white/5 shadow-sm flex flex-col">

            <!-- Filter Bar -->
            <div class="p-5 border-b border-[#050F1E] flex gap-4 items-center bg-[#071426] rounded-t-2xl flex-wrap">
                <div class="relative">
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input x-model="search" @input.debounce.400ms="fetchEmployees(1)"
                           type="text" placeholder="البحث عن موظف..."
                           class="w-[220px] bg-[#050F1E] border border-outline-variant rounded-xl pr-10 pl-4 py-2 text-on-surface font-body-secondary text-sm focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none placeholder:text-outline-variant transition-colors">
                </div>
                {{-- Department filter --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative" style="min-width:170px">
                    <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                        <span x-text="departments.find(d => String(d.id)===String(filterDept))?.name || 'كل الأقسام'"></span>
                        <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                    </button>
                    <div x-show="open" class="rs-panel"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-y-95"
                         x-transition:enter-end="opacity-100 scale-y-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-y-100"
                         x-transition:leave-end="opacity-0 scale-y-95"
                         style="display:none">
                        <button type="button" @click="filterDept=''; open=false; fetchEmployees(1)"
                                class="rs-opt" :class="filterDept==='' ? 'selected':''">كل الأقسام</button>
                        <template x-for="d in departments" :key="d.id">
                            <button type="button" @click="filterDept=d.id; open=false; fetchEmployees(1)"
                                    class="rs-opt" :class="String(filterDept)===String(d.id) ? 'selected':''"
                                    x-text="d.name"></button>
                        </template>
                    </div>
                </div>

                {{-- Status filter --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative" style="min-width:150px">
                    <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                        <span x-text="({active:'نشط', on_leave:'في إجازة', terminated:'منتهي'})[filterStatus] || 'كل الحالات'"></span>
                        <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                    </button>
                    <div x-show="open" class="rs-panel"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-y-95"
                         x-transition:enter-end="opacity-100 scale-y-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-y-100"
                         x-transition:leave-end="opacity-0 scale-y-95"
                         style="display:none">
                        <button type="button" @click="filterStatus=''; open=false; fetchEmployees(1)"
                                class="rs-opt" :class="filterStatus==='' ? 'selected':''">كل الحالات</button>
                        <button type="button" @click="filterStatus='active'; open=false; fetchEmployees(1)"
                                class="rs-opt" :class="filterStatus==='active' ? 'selected':''">نشط</button>
                        <button type="button" @click="filterStatus='on_leave'; open=false; fetchEmployees(1)"
                                class="rs-opt" :class="filterStatus==='on_leave' ? 'selected':''">في إجازة</button>
                        <button type="button" @click="filterStatus='terminated'; open=false; fetchEmployees(1)"
                                class="rs-opt" :class="filterStatus==='terminated' ? 'selected':''">منتهي</button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-right font-body-secondary text-sm">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 font-normal">الموظف</th>
                            <th class="px-6 py-4 font-normal">القسم / المسمى</th>
                            <th class="px-6 py-4 font-normal">المدير المباشر</th>
                            <th class="px-6 py-4 font-normal">تاريخ الانضمام</th>
                            <th class="px-6 py-4 font-normal">الحالة</th>
                            <th class="px-6 py-4 font-normal text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#050F1E] text-on-surface">
                        <template x-if="loading">
                            <tr><td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[32px] animate-spin text-primary-container">progress_activity</span>
                            </td></tr>
                        </template>
                        <template x-if="!loading && employees.length === 0">
                            <tr><td colspan="6" class="px-6 py-12 text-center text-on-surface-variant text-sm">لا يوجد موظفون</td></tr>
                        </template>
                        <template x-for="emp in employees" :key="emp.id">
                            <tr class="hover:bg-[#071426]/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-surface-variant flex items-center justify-center text-xs font-bold text-primary-container shrink-0"
                                             x-text="initials(emp.name)"></div>
                                        <div>
                                            <p class="font-semibold text-on-background" x-text="emp.name"></p>
                                            <p class="text-xs text-outline" x-text="(emp.job_title?.name || emp.job_title) || '—'"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-outline" x-text="emp.department?.name || emp.department || '—'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-outline" x-text="emp.manager?.name || '—'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-outline font-body-secondary" x-text="emp.hire_date || '—'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="statusBadge(emp.status)" x-text="statusLabel(emp.status)"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openEdit(emp)" class="text-outline hover:text-primary-container transition-colors p-1">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="deleteEmp(emp)" class="text-outline hover:text-error transition-colors p-1">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-[#050F1E] flex justify-between items-center text-xs text-outline">
                <span x-text="'عرض ' + employees.length + ' من ' + (meta.total||0) + ' موظف'"></span>
                <div class="flex gap-2">
                    <button @click="fetchEmployees(meta.current_page - 1)"
                            :disabled="meta.current_page <= 1"
                            class="rs-page-btn">السابق</button>
                    <span class="px-3 py-1 rounded-lg bg-primary-container text-white font-bold text-xs" x-text="meta.current_page || 1"></span>
                    <button @click="fetchEmployees(meta.current_page + 1)"
                            :disabled="!meta.next_page_url && meta.current_page >= meta.last_page"
                            class="rs-page-btn">التالي</button>
                </div>
            </div>
        </section>

    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" style="display:none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="bg-[#0B1F3A] w-full max-w-xl rounded-[20px] p-7 border border-white/10 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-white font-h2" x-text="editingId ? 'تعديل الموظف' : 'إضافة موظف جديد'"></h3>
                <button @click="showModal=false" class="text-slate-500 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الاسم الكامل</label>
                        <input x-model="form.name" type="text" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container focus:border-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">البريد الإلكتروني</label>
                        <input x-model="form.email" type="email" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container focus:border-primary-container outline-none text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">رقم الهاتف</label>
                        <input x-model="form.phone" type="tel" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container focus:border-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">القسم</label>
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                            <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                                <span x-text="departments.find(d => String(d.id)===String(form.department_id))?.name || 'اختر القسم'"></span>
                                <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                            </button>
                            <div x-show="open" class="rs-panel" x-transition style="display:none">
                                <button type="button" @click="onDepartmentChange(''); open=false"
                                        class="rs-opt" :class="form.department_id==='' ? 'selected':''">اختر القسم</button>
                                <template x-for="d in departments" :key="d.id">
                                    <button type="button" @click="onDepartmentChange(d.id); open=false"
                                            class="rs-opt" :class="String(form.department_id)===String(d.id) ? 'selected':''"
                                            x-text="d.name"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Job Title (cascades from department) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">المسمى الوظيفي</label>
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                            <button type="button" @click="form.department_id ? (open = !open) : null"
                                    class="rs-trigger" :data-open="open"
                                    :class="!form.department_id ? 'opacity-60 cursor-not-allowed' : ''">
                                <span x-text="jobTitles.find(j => String(j.id)===String(form.job_title_id))?.name
                                              || (form.department_id ? 'اختر المسمى الوظيفي' : 'اختر القسم أولاً')"></span>
                                <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                            </button>
                            <div x-show="open" class="rs-panel" x-transition style="display:none">
                                <template x-if="loadingJobTitles">
                                    <div class="rs-opt text-on-surface-variant">جارٍ التحميل...</div>
                                </template>
                                <template x-if="!loadingJobTitles && jobTitles.length === 0">
                                    <div class="rs-opt text-on-surface-variant">
                                        لا توجد مسميات وظيفية في هذا القسم — أضِفها من صفحة الأقسام.
                                    </div>
                                </template>
                                <template x-for="j in jobTitles" :key="j.id">
                                    <button type="button" @click="form.job_title_id=j.id; open=false"
                                            class="rs-opt" :class="String(form.job_title_id)===String(j.id) ? 'selected':''"
                                            x-text="j.name"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Contract Type (from API list) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">نوع العقد</label>
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                            <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                                <span x-text="contractTypes.find(t => String(t.id)===String(form.contract_type_id))?.name || 'اختر نوع العقد'"></span>
                                <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                            </button>
                            <div x-show="open" class="rs-panel" x-transition style="display:none">
                                <template x-if="contractTypes.length === 0">
                                    <div class="rs-opt text-on-surface-variant">
                                        لا توجد أنواع عقود — أضِفها من صفحة إعدادات الشركة.
                                    </div>
                                </template>
                                <template x-for="t in contractTypes" :key="t.id">
                                    <button type="button" @click="form.contract_type_id=t.id; open=false"
                                            class="rs-opt" :class="String(form.contract_type_id)===String(t.id) ? 'selected':''"
                                            x-text="t.name"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الراتب الأساسي</label>
                        <input x-model="form.base_salary" type="number" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">بدل السكن</label>
                        <input x-model="form.housing_allowance" type="number" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">بدل النقل</label>
                        <input x-model="form.transport_allowance" type="number" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">تاريخ التوظيف</label>
                        <input x-model="form.hire_date" type="date" dir="ltr" class="w-full bg-[#071426] border border-[#0B1F3A] text-slate-200 rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">الحالة</label>
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                            <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                                <span x-text="({active:'نشط', on_leave:'في إجازة', terminated:'منتهي'})[form.status] || 'اختر'"></span>
                                <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                            </button>
                            <div x-show="open" class="rs-panel" x-transition style="display:none">
                                <button type="button" @click="form.status='active'; open=false"
                                        class="rs-opt" :class="form.status==='active' ? 'selected':''">نشط</button>
                                <button type="button" @click="form.status='on_leave'; open=false"
                                        class="rs-opt" :class="form.status==='on_leave' ? 'selected':''">في إجازة</button>
                                <button type="button" @click="form.status='terminated'; open=false"
                                        class="rs-opt" :class="form.status==='terminated' ? 'selected':''">منتهي</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Level selector --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">المستوى الوظيفي</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="opt in levelOptions" :key="opt.val">
                            <button type="button" @click="form.level = opt.val; form.manager_id = ''"
                                    :class="form.level === opt.val
                                        ? 'border-primary-container bg-primary-container/10 text-primary-container'
                                        : 'border-outline-variant text-on-surface-variant hover:border-outline hover:text-on-surface hover:bg-surface-container-high'"
                                    class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border text-xs font-semibold transition-all duration-150">
                                <span class="material-symbols-outlined text-[20px]" x-text="opt.icon"></span>
                                <span x-text="opt.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Manager picker (hidden for main_manager) --}}
                <div x-show="form.level !== 'main_manager'" x-transition.opacity.duration.200ms>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">المدير المباشر</label>
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button type="button" @click="open = !open" class="rs-trigger" :data-open="open">
                            <span x-text="availableManagers().find(e => String(e.id)===String(form.manager_id))?.name || '— اختر المدير المباشر —'"></span>
                            <span class="material-symbols-outlined rs-trigger-icon">expand_more</span>
                        </button>
                        <div x-show="open" class="rs-panel" x-transition style="display:none">
                            <button type="button" @click="form.manager_id=''; open=false"
                                    class="rs-opt" :class="form.manager_id==='' ? 'selected':''">— اختر —</button>
                            <template x-for="e in availableManagers()" :key="e.id">
                                <button type="button" @click="form.manager_id=e.id; open=false"
                                        class="rs-opt" :class="String(form.manager_id)===String(e.id) ? 'selected':''">
                                    <span x-text="e.name + (e.job_title?.name ? ' · ' + e.job_title.name : '')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p x-show="loadingManagers" class="text-[11px] text-slate-500 mt-1">جار تحميل الموظفين...</p>
                </div>

                <div x-show="formError" class="text-error text-sm" x-text="formError"></div>
                <div class="flex gap-3 pt-2">
                    <button @click="saveEmployee()"
                            :disabled="saving"
                            class="flex-1 bg-primary-container text-white py-2.5 rounded-lg font-bold hover:brightness-110 transition-all disabled:opacity-60 text-sm">
                        <span x-text="saving ? 'جارٍ الحفظ...' : 'حفظ'"></span>
                    </button>
                    <button @click="showModal=false" class="rs-btn-cancel flex-1 py-2.5 text-sm">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function employeesPage() {
    const emptyForm = {
        name:'', email:'', phone:'',
        department_id:'', job_title_id:'', manager_id:'', contract_type_id:'',
        hire_date:'', status:'active',
        base_salary:0, housing_allowance:0, transport_allowance:0,
        level: 'employee',
    };
    return {
        employees:[], meta:{}, departments:[], allEmployees:[], loading:false, loadingManagers:false,
        contractTypes:[], jobTitles:[], loadingJobTitles:false,
        search:'', filterDept:'', filterStatus:'',
        showModal:false, editingId:null, form:{...emptyForm},
        formError:'', saving:false,

        levelOptions: [
            { val: 'main_manager',  label: 'مدير عام',      icon: 'workspace_premium' },
            { val: 'direct_manager',label: 'مدير مباشر',    icon: 'supervisor_account' },
            { val: 'employee',      label: 'موظف',           icon: 'person' },
        ],

        availableManagers() {
            // Build the set of IDs that are someone's manager (derived from the list)
            const managerIds = new Set(
                this.allEmployees
                    .filter(e => e.manager?.id)
                    .map(e => e.manager.id)
            );

            return this.allEmployees.filter(e => {
                if (e.id === this.editingId) return false; // never pick self

                if (this.form.level === 'direct_manager') {
                    // A direct manager reports to a main manager (root: no manager themselves)
                    return !e.manager;
                }
                if (this.form.level === 'employee') {
                    // An employee reports to a direct manager (has a manager AND has reports)
                    return !!e.manager && managerIds.has(e.id);
                }
                return false;
            });
        },

        initials(name) {
            return (name||'').trim().split(/\s+/).slice(0,2).map(w=>w[0]).join('').toUpperCase();
        },

        statusLabel(s) {
            return {active:'نشط', on_leave:'في إجازة', terminated:'منتهي'}[s] || s;
        },

        statusBadge(s) {
            return 'rs-badge rs-badge-' + (s || 'inactive');
        },

        async init() {
            await Promise.all([this.fetchEmployees(), this.fetchDepartments(), this.fetchContractTypes()]);
        },

        async onDepartmentChange(deptId) {
            this.form.department_id = deptId;
            this.form.job_title_id = ''; // clear stale selection — old dept's job title won't match
            this.jobTitles = [];
            if (deptId) await this.fetchJobTitles(deptId);
        },

        async fetchContractTypes() {
            try {
                const r = await axios.get('/api/v1/contract-types');
                if (r.data.success) this.contractTypes = r.data.data || [];
            } catch(e){}
        },

        async fetchJobTitles(deptId) {
            this.loadingJobTitles = true;
            try {
                const r = await axios.get('/api/v1/job-titles?department_id=' + deptId);
                if (r.data.success) this.jobTitles = r.data.data || [];
            } catch(e){ this.jobTitles = []; }
            finally { this.loadingJobTitles = false; }
        },

        async fetchEmployees(page=1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({page});
                if (this.search)       params.set('search', this.search);
                if (this.filterDept)   params.set('department_id', this.filterDept);
                if (this.filterStatus) params.set('status', this.filterStatus);
                const r = await axios.get('/api/v1/employees?' + params);
                if (r.data.success) {
                    this.employees = r.data.data;
                    this.meta      = r.data.meta;
                }
            } catch(e) {} finally { this.loading = false; }
        },

        async fetchDepartments() {
            try {
                const r = await axios.get('/api/v1/departments');
                if (r.data.success) this.departments = r.data.data;
            } catch(e) {}
        },

        async fetchAllEmployees() {
            this.loadingManagers = true;
            try {
                const r = await axios.get('/api/v1/employees?per_page=200');
                if (r.data.success) this.allEmployees = r.data.data;
            } catch(e) {}
            this.loadingManagers = false;
        },

        openCreate() {
            this.editingId = null;
            this.form = {...emptyForm};
            this.jobTitles = [];
            this.formError = '';
            this.showModal = true;
            this.fetchAllEmployees();
        },

        async openEdit(emp) {
            this.editingId = emp.id;
            const managerId = emp.manager?.id || '';
            const deptId    = emp.department?.id || '';
            this.form = {
                name: emp.name, email: emp.email,
                phone: emp.phone||'',
                department_id: deptId,
                job_title_id: emp.job_title?.id || '',
                manager_id: managerId,
                contract_type_id: emp.contract_type?.id || '',
                hire_date: emp.hire_date||'', status: emp.status||'active',
                base_salary: emp.base_salary||0,
                housing_allowance: emp.housing_allowance||0,
                transport_allowance: emp.transport_allowance||0,
                level: managerId ? 'employee' : 'main_manager',
            };
            this.formError = '';
            this.showModal = true;
            this.fetchAllEmployees();
            // Load this dept's job titles so the current selection has a label
            if (deptId) await this.fetchJobTitles(deptId);
        },

        async saveEmployee() {
            this.formError = '';
            this.saving = true;
            try {
                const { level, ...payload } = this.form;
                if (level === 'main_manager') payload.manager_id = null;

                if (this.editingId) {
                    await axios.put('/api/v1/employees/' + this.editingId, payload);
                } else {
                    await axios.post('/api/v1/employees', payload);
                }
                this.showModal = false;
                await this.fetchEmployees(this.meta.current_page || 1);
            } catch(e) {
                const errs = e.response?.data?.errors;
                this.formError = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'خطأ في الحفظ');
            } finally { this.saving = false; }
        },

        async deleteEmp(emp) {
            if (!confirm('هل أنت متأكد من حذف ' + emp.name + '؟')) return;
            try {
                await axios.delete('/api/v1/employees/' + emp.id);
                await this.fetchEmployees(this.meta.current_page || 1);
            } catch(e) { alert(e.response?.data?.message || 'خطأ في الحذف'); }
        },
    };
}
</script>
@endpush
