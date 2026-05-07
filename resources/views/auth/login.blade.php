@extends('layouts.guest')
@section('title', 'تسجيل الدخول')

@section('content')
<main class="w-full max-w-[420px] mx-auto px-4 relative z-10" x-data="loginPage()">
    <div class="bg-[#0B1F3A] border border-[rgba(255,255,255,0.08)] rounded-[20px] p-[40px] flex flex-col gap-6 shadow-xl">

        <!-- Header -->
        <div class="flex items-center gap-4 mb-2">
            <div class="w-12 h-12 bg-primary-container rounded-lg flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-white text-2xl" style="font-variation-settings:'FILL' 1;">memory</span>
            </div>
            <div>
                <h1 class="font-h2 text-h2 flex items-baseline gap-0.5">
                    <span class="text-slate-100">Rushd</span>
                    <span class="text-primary-container">AI</span>
                </h1>
                <p class="font-label-caps text-label-caps text-slate-500 uppercase mt-1 tracking-wider">Advanced HR OS v2.0</p>
            </div>
        </div>

        <!-- Error Alert -->
        <div x-show="errorMsg"
             class="flex items-center gap-2 bg-error/10 border border-error/20 rounded-lg px-4 py-3"
             style="display:none;">
            <span class="material-symbols-outlined text-error text-[18px]">error</span>
            <span class="text-error font-body-secondary text-[13px]" x-text="errorMsg"></span>
        </div>

        <!-- Form -->
        <form @submit.prevent="doLogin" class="flex flex-col gap-5">

            <!-- Email -->
            <div class="flex flex-col gap-2">
                <label class="font-label-caps text-label-caps text-slate-500 uppercase tracking-wider">البريد الإلكتروني</label>
                <div class="relative">
                    <input x-model="form.email"
                           type="email"
                           dir="ltr"
                           placeholder="name@company.com"
                           :class="emailError ? 'border-error' : 'border-[rgba(255,255,255,0.08)] focus:border-primary-container focus:ring-primary-container'"
                           class="w-full h-[46px] bg-[#050F1E] border rounded-lg px-4 pr-10 text-on-background font-body-main focus:outline-none focus:ring-1 transition-colors">
                    <span x-show="emailError" class="absolute right-3 top-1/2 -translate-y-1/2" style="display:none;">
                        <span class="material-symbols-outlined text-error text-xl">error</span>
                    </span>
                </div>
                <span x-show="emailError" class="text-error font-body-secondary text-[12px] flex items-center gap-1" style="display:none;">
                    <span class="material-symbols-outlined text-[14px]">close</span>
                    <span x-text="emailError"></span>
                </span>
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-2">
                <label class="font-label-caps text-label-caps text-slate-500 uppercase tracking-wider">كلمة المرور</label>
                <div class="relative">
                    <input x-model="form.password"
                           :type="showPass ? 'text' : 'password'"
                           dir="ltr"
                           placeholder="••••••••"
                           class="w-full h-[46px] bg-[#050F1E] border border-[rgba(255,255,255,0.08)] rounded-lg px-4 pl-10 text-on-background font-body-main focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container transition-colors">
                    <button type="button"
                            @click="showPass = !showPass"
                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-primary-container transition-colors">
                        <span class="material-symbols-outlined text-xl" x-text="showPass ? 'visibility_off' : 'visibility'"></span>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                    :disabled="loading"
                    class="w-full h-[46px] bg-primary-container text-white rounded-[10px] font-h2 text-[16px] flex items-center justify-center gap-2 hover:brightness-110 active:scale-[0.98] transition-all mt-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <span x-show="!loading">تسجيل الدخول</span>
                <span x-show="loading" class="flex items-center gap-2" style="display:none;">
                    <span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                    جارٍ الدخول...
                </span>
            </button>

        </form>
    </div>
</main>
@endsection

@push('scripts')
<script>
function loginPage() {
    return {
        form: { email: '', password: '' },
        loading: false,
        showPass: false,
        errorMsg: '',
        emailError: '',

        async doLogin() {
            this.errorMsg = '';
            this.emailError = '';
            this.loading = true;
            try {
                const r = await axios.post('/api/v1/auth/login', this.form);
                if (r.data.success) {
                    localStorage.setItem('auth_token', r.data.data.token);
                    localStorage.setItem('auth_user', JSON.stringify(r.data.data.user));
                    window.location.href = '/';
                }
            } catch(e) {
                const data = e.response?.data;
                if (e.response?.status === 422) {
                    this.emailError = data?.errors?.email?.[0] || '';
                    if (!this.emailError) this.errorMsg = data?.message || 'بيانات غير صحيحة';
                } else if (e.response?.status === 401) {
                    this.errorMsg = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
                } else {
                    this.errorMsg = 'حدث خطأ، يرجى المحاولة مجدداً';
                }
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
