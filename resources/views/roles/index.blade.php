@extends('layouts.app')
@section('title', 'الأدوار والصلاحيات')

@section('content')
<div x-data="rolesPage()" x-init="init()" class="bg-surface min-h-screen transition-colors duration-300">
<div class="max-w-[1440px] mx-auto px-8 pt-8 pb-20">

    {{-- Header --}}
    <header class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-[20px] font-[800] text-on-surface font-h1">الأدوار والصلاحيات</h1>
            <p class="text-[12px] text-on-surface-variant mt-1">تحديد صلاحيات الوصول لكل دور في النظام</p>
        </div>
        <button @click="openCreate()"
                class="bg-primary-container text-white px-5 py-2.5 rounded-[10px] font-bold text-sm hover:brightness-110 transition-all flex items-center gap-2">
            دور جديد <span class="material-symbols-outlined text-sm">add</span>
        </button>
    </header>

    {{-- Role summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
        <template x-for="role in roles" :key="role.id">
            <div class="bg-surface-container rounded-xl p-4 border-r-4 border border-outline-variant flex items-center justify-between transition-all hover:border-outline"
                 :style="'border-right-color:' + (role.color||'#0EA5A4')"
                 style="box-shadow:var(--c-shadow)">
                <div>
                    <p class="font-bold text-sm text-on-surface" x-text="role.name"></p>
                    <p class="text-[10px] text-on-surface-variant mt-0.5"
                       x-text="(role.permissions?.length||0) + ' صلاحية'"></p>
                </div>
                <button @click="openEdit(role)"
                        class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary-container hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined text-[17px]">edit</span>
                </button>
            </div>
        </template>
    </div>

    {{-- Permission Matrix --}}
    <div class="bg-surface-container rounded-2xl border border-outline-variant overflow-hidden"
         style="box-shadow:var(--c-shadow)">

        {{-- Matrix header --}}
        <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between">
            <h2 class="font-bold text-on-surface text-[15px]">مصفوفة الصلاحيات</h2>
            <p class="text-[12px] text-on-surface-variant">اضغط على الخلية لتعديل الصلاحية مباشرة</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse" style="min-width:700px">
                {{-- Column headers = roles --}}
                <thead>
                    <tr>
                        <th class="p-4 text-right w-48">
                            <span class="text-[11px] text-on-surface-variant uppercase tracking-wider font-bold">الصلاحية</span>
                        </th>
                        <template x-for="role in roles" :key="role.id">
                            <th class="p-4 text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <div class="w-3 h-3 rounded-full" :style="'background:' + (role.color||'#0EA5A4')"></div>
                                    <span class="text-[12px] font-bold text-on-surface" x-text="role.name"></span>
                                    <span class="text-[10px] text-on-surface-variant"
                                          x-text="(role.permissions?.length||0) + ' ✓'"></span>
                                </div>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="group in permissionGroups" :key="group.label">
                        {{-- Group header row --}}
                        <tr class="bg-surface-container-low">
                            <td :colspan="roles.length + 1" class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[15px] text-primary-container"
                                          x-text="group.icon"></span>
                                    <span class="text-[11px] font-bold text-primary-container uppercase tracking-wider"
                                          x-text="group.label"></span>
                                </div>
                            </td>
                        </tr>
                        {{-- Permission rows in this group --}}
                        <template x-for="perm in groupedPerms(group)" :key="perm.id">
                            <tr class="border-b border-outline-variant hover:bg-surface-container-high transition-colors group">
                                <td class="px-4 py-3 text-on-surface text-sm font-medium" x-text="perm.name||perm.slug"></td>
                                <template x-for="role in roles" :key="role.id">
                                    <td class="px-4 py-3 text-center border-r border-outline-variant">
                                        <button @click="togglePerm(role, perm)"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center mx-auto transition-all duration-150"
                                                :class="hasPermission(role, perm)
                                                    ? 'bg-primary-container/15 text-primary-container hover:bg-primary-container/25'
                                                    : 'text-on-surface-variant/30 hover:bg-surface-container-high hover:text-on-surface-variant'"
                                                :title="hasPermission(role, perm) ? 'إلغاء الصلاحية' : 'منح الصلاحية'">
                                            <span class="material-symbols-outlined text-[18px]"
                                                  x-text="hasPermission(role, perm) ? 'check_circle' : 'radio_button_unchecked'"
                                                  :style="hasPermission(role, perm) ? 'font-variation-settings:\'FILL\' 1' : ''"></span>
                                        </button>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Limits footer --}}
        <div class="bg-surface-container-low border-t border-outline-variant px-4 py-4">
            <p class="text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-3">الحدود المالية والإجازات</p>
            <div class="overflow-x-auto">
                <table class="w-full text-center" style="min-width:700px">
                    <tr>
                        <td class="w-48 text-right px-4">
                            <span class="text-[12px] text-on-surface-variant">الحد المالي / حد الإجازة</span>
                        </td>
                        <template x-for="role in roles" :key="role.id">
                            <td class="px-4">
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="bg-amber-500/10 text-amber-600 dark:text-amber-400 px-2.5 py-1 rounded-lg text-[11px] font-bold border border-amber-500/20"
                                          x-text="role.financial_limit > 99998 ? 'غير محدود' : formatMoney(role.financial_limit) + ' ر.س'"></span>
                                    <span class="bg-blue-500/10 text-blue-600 dark:text-blue-400 px-2.5 py-1 rounded-lg text-[11px] font-bold border border-blue-500/20"
                                          x-text="role.leave_limit_days > 0 ? role.leave_limit_days + ' أيام' : 'لا يوجد'"></span>
                                </div>
                            </td>
                        </template>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Create/Edit Modal --}}
<div x-show="showModal" style="display:none;"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
    <div class="bg-surface-container w-full max-w-xl rounded-[20px] border border-outline-variant shadow-2xl overflow-y-auto max-h-[92vh]">

        {{-- Modal header --}}
        <div class="flex justify-between items-center px-6 py-5 border-b border-outline-variant bg-surface-container-low">
            <h3 class="text-lg font-bold text-on-surface" x-text="editingId ? 'تعديل الدور' : 'إضافة دور جديد'"></h3>
            <button @click="showModal=false" class="text-on-surface-variant hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="p-6 space-y-5">

            {{-- Name + Slug --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">اسم الدور</label>
                    <input x-model="form.name" type="text"
                           class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">المعرف (slug)</label>
                    <input x-model="form.slug" type="text" dir="ltr"
                           class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                </div>
            </div>

            {{-- Financial + Leave limits --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">الحد المالي (ر.س)</label>
                    <input x-model="form.financial_limit" type="number" dir="ltr"
                           class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">حد الإجازة (أيام)</label>
                    <input x-model="form.leave_limit_days" type="number" dir="ltr"
                           class="w-full bg-surface-container-low border border-outline-variant text-on-surface rounded-lg py-2.5 px-4 focus:ring-1 focus:ring-primary-container outline-none text-sm">
                </div>
            </div>

            {{-- Color --}}
            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">لون الدور</label>
                <div class="flex items-center gap-3 flex-wrap">
                    <template x-for="c in colorPalette" :key="c">
                        <button type="button" @click="form.color = c"
                                class="w-8 h-8 rounded-full border-2 transition-all"
                                :style="'background:'+c"
                                :class="form.color===c ? 'border-on-surface scale-110' : 'border-transparent hover:scale-105'"></button>
                    </template>
                    <input x-model="form.color" type="color"
                           class="w-8 h-8 rounded-full cursor-pointer border-0 bg-transparent"
                           title="لون مخصص">
                </div>
            </div>

            {{-- Permissions grouped by category --}}
            <div>
                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-3">الصلاحيات</label>
                <div class="space-y-4">
                    <template x-for="group in permissionGroups" :key="group.label">
                        <div class="bg-surface-container-low rounded-xl border border-outline-variant overflow-hidden">
                            {{-- Group header --}}
                            <div class="flex items-center justify-between px-4 py-2.5 border-b border-outline-variant">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[15px] text-primary-container"
                                          x-text="group.icon"></span>
                                    <span class="text-[12px] font-bold text-on-surface" x-text="group.label"></span>
                                </div>
                                {{-- Select all in group --}}
                                <button type="button" @click="toggleGroup(group)"
                                        class="text-[11px] text-primary-container hover:underline font-semibold"
                                        x-text="groupAllSelected(group) ? 'إلغاء الكل' : 'تحديد الكل'"></button>
                            </div>
                            {{-- Permissions in group --}}
                            <div class="grid grid-cols-2 gap-0">
                                <template x-for="perm in groupedPerms(group)" :key="perm.id">
                                    <label class="flex items-center gap-2.5 px-4 py-2.5 cursor-pointer hover:bg-surface-container transition-colors border-b border-outline-variant/50 last:border-b-0">
                                        <div class="relative flex-shrink-0">
                                            <input type="checkbox" :value="perm.id" x-model="form.permission_ids"
                                                   class="sr-only peer">
                                            <div class="w-4 h-4 rounded border-2 border-outline-variant peer-checked:bg-primary-container peer-checked:border-primary-container transition-all flex items-center justify-center"
                                                 :class="form.permission_ids.includes(perm.id) ? 'bg-primary-container border-primary-container' : 'bg-surface-container-low'">
                                                <span class="material-symbols-outlined text-white text-[12px]"
                                                      x-show="form.permission_ids.includes(perm.id)"
                                                      style="font-variation-settings:'FILL' 1">check</span>
                                            </div>
                                        </div>
                                        <span class="text-[13px] text-on-surface" x-text="perm.name||perm.slug"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="formErr" class="text-error text-sm bg-error/10 px-4 py-2 rounded-lg border border-error/20"
                 x-text="formErr"></div>

            <div class="flex gap-3 pt-1">
                <button @click="saveRole()" :disabled="saving"
                        class="flex-1 bg-primary-container text-white py-2.5 rounded-lg font-bold hover:brightness-110 disabled:opacity-60 text-sm transition-all">
                    <span x-text="saving ? 'جارٍ الحفظ...' : 'حفظ الدور'"></span>
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
function rolesPage() {
    return {
        roles: [], permissions: [], loading: false,
        showModal: false, editingId: null, saving: false, formErr: '',
        form: { name:'', slug:'', financial_limit:0, leave_limit_days:0, color:'#0EA5A4', permission_ids:[] },

        colorPalette: ['#0EA5A4','#60A5FA','#22C55E','#F59E0B','#F43F5E','#8B5CF6','#06B6D4','#94A3B8','#334155'],

        permissionGroups: [
            { label:'الموظفون',     icon:'group',                  slugs:['view_employees','edit_employees','delete_employees'] },
            { label:'الرواتب',      icon:'payments',               slugs:['view_payroll','edit_payroll','run_payroll'] },
            { label:'الموافقات',    icon:'check_circle',           slugs:['approve_leaves','approve_purchases'] },
            { label:'الإدارة',      icon:'admin_panel_settings',   slugs:['manage_roles','manage_rules','manage_departments','manage_shifts'] },
            { label:'التقارير',     icon:'analytics',              slugs:['view_reports','view_audit_logs'] },
        ],

        groupedPerms(group){
            return this.permissions.filter(p => group.slugs.includes(p.slug));
        },

        hasPermission(role, perm){
            return role.permissions?.some(p => (p.id || p) === perm.id);
        },

        groupAllSelected(group){
            const ids = this.groupedPerms(group).map(p => p.id);
            return ids.length > 0 && ids.every(id => this.form.permission_ids.includes(id));
        },

        toggleGroup(group){
            const ids = this.groupedPerms(group).map(p => p.id);
            if(this.groupAllSelected(group)){
                this.form.permission_ids = this.form.permission_ids.filter(id => !ids.includes(id));
            } else {
                ids.forEach(id => { if(!this.form.permission_ids.includes(id)) this.form.permission_ids.push(id); });
            }
        },

        async togglePerm(role, perm){
            const has = this.hasPermission(role, perm);
            const perms = role.permissions?.map(p => p.id || p) || [];
            const updated = has ? perms.filter(id => id !== perm.id) : [...perms, perm.id];
            try {
                await axios.put('/api/v1/roles/'+role.id, {
                    name: role.name, slug: role.slug,
                    financial_limit: role.financial_limit,
                    leave_limit_days: role.leave_limit_days,
                    color: role.color,
                    permission_ids: updated,
                });
                await this.fetchRoles();
            } catch(e){}
        },

        formatMoney(v){ return v ? Number(v).toLocaleString('ar-SA', {maximumFractionDigits:0}) : '0'; },

        async init(){
            if (await window.$guard('manage_roles')) return;
            await Promise.all([this.fetchRoles(), this.fetchPermissions()]);
        },

        async fetchRoles(){
            try { const r = await axios.get('/api/v1/roles?per_page=50'); if(r.data.success) this.roles = r.data.data; }
            catch(e){}
        },

        async fetchPermissions(){
            try { const r = await axios.get('/api/v1/permissions'); if(r.data.success) this.permissions = r.data.data || []; }
            catch(e){}
        },

        openCreate(){
            this.editingId = null;
            this.form = { name:'', slug:'', financial_limit:0, leave_limit_days:0, color:'#0EA5A4', permission_ids:[] };
            this.formErr = ''; this.showModal = true;
        },

        openEdit(role){
            this.editingId = role.id;
            this.form = {
                name: role.name, slug: role.slug,
                financial_limit: role.financial_limit || 0,
                leave_limit_days: role.leave_limit_days || 0,
                color: role.color || '#0EA5A4',
                permission_ids: role.permissions?.map(p => p.id || p) || [],
            };
            this.formErr = ''; this.showModal = true;
        },

        async saveRole(){
            this.formErr = ''; this.saving = true;
            try {
                if(this.editingId){
                    await axios.put('/api/v1/roles/'+this.editingId, this.form);
                } else {
                    await axios.post('/api/v1/roles', this.form);
                }
                this.showModal = false; await this.fetchRoles();
            } catch(e){
                const errs = e.response?.data?.errors;
                this.formErr = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'خطأ');
            } finally { this.saving = false; }
        },
    };
}
</script>
@endpush
