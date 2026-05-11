@extends('layouts.app')
@section('title', 'الرئيسية')

@section('content')
    <div x-data="dashboardPage()" x-init="init()"
        class="min-h-screen bg-surface relative overflow-x-hidden transition-colors duration-300">

        {{-- Ambient background blobs --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-[700px] h-[700px] rounded-full"
                style="background:radial-gradient(circle,rgba(14,165,164,.07) 0%,transparent 70%)"></div>
            <div class="absolute top-60 -left-32 w-[500px] h-[500px] rounded-full"
                style="background:radial-gradient(circle,rgba(99,102,241,.05) 0%,transparent 70%)"></div>
            <div class="absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full"
                style="background:radial-gradient(circle,rgba(245,158,11,.04) 0%,transparent 70%)"></div>
        </div>

        <div class="relative max-w-[1200px] mx-auto px-6 pt-10 pb-20">

            {{-- ── Greeting ──────────────────────────────────────────── --}}
            <div class="mb-11 dash-greeting" style="animation:fadeUp .5s ease both">
                <p class="text-[12px] mb-1.5 tracking-wide text-sub" x-text="todayStr"></p>
                <h1 class="font-h1 text-[30px] font-[900] leading-tight text-on-surface">
                    مرحباً، <span style="color:#0ea5a4" x-text="firstName"></span>
                </h1>
                <p class="text-[13px] text-sub mt-1.5">ماذا تريد أن تدير اليوم؟</p>
            </div>

            {{-- ── Bento Grid ────────────────────────────────────────── --}}
            <div class="grid grid-cols-4 gap-4">

                {{-- ════ TIER 1 — Hero cards (2-col wide) ════ --}}

                {{-- Employees --}}
                <a x-show="can('view_employees')" href="/employees" class="nav-card col-span-2"
                    style="--accent:#0ea5a4;--glow:rgba(14,165,164,.22);--d:40ms">
                    <div class="card-blob"
                        style="background:radial-gradient(circle,rgba(14,165,164,.18),transparent 70%);width:280px;height:280px;top:-80px;right:-60px">
                    </div>
                    <div class="card-body" style="min-height:210px">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="card-icon"
                                    style="background:rgba(14,165,164,.12);border-color:rgba(14,165,164,.35)">
                                    <span class="material-symbols-outlined text-[22px]"
                                        style="color:#0ea5a4;font-variation-settings:'FILL' 1">group</span>
                                </div>
                                <div>
                                    <h2 class="card-title">إدارة الموظفين</h2>
                                    <p class="card-sub">بيانات · عقود · سجلات</p>
                                </div>
                            </div>
                            <span class="chip"
                                style="color:#10b981;background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3)">نشط</span>
                        </div>
                        <div class="mt-auto pt-7">
                            <div class="flex items-baseline gap-2.5">
                                <span class="hero-num" x-text="stats.total_active_employees ?? '—'"></span>
                                <span class="hero-label">موظف نشط</span>
                            </div>
                            <div class="card-cta" style="color:#0ea5a4">
                                <span>عرض السجلات</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Approvals --}}
                <a x-show="canAny('approve_leaves','approve_purchases')" href="/approvals" class="nav-card col-span-2"
                    style="--accent:#f59e0b;--glow:rgba(245,158,11,.2);--d:90ms">
                    <div class="card-blob"
                        style="background:radial-gradient(circle,rgba(245,158,11,.16),transparent 70%);width:260px;height:260px;top:-70px;left:-50px">
                    </div>
                    <div class="card-body" style="min-height:210px">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="card-icon"
                                    style="background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.35)">
                                    <span class="material-symbols-outlined text-[22px]"
                                        style="color:#f59e0b;font-variation-settings:'FILL' 1">check_circle</span>
                                </div>
                                <div>
                                    <h2 class="card-title">مسارات الموافقات</h2>
                                    <p class="card-sub">إجازات · مشتريات · طلبات</p>
                                </div>
                            </div>
                            <div class="flex gap-1.5">
                                <span x-show="(stats.pending_requests?.purchases||0)>0" class="chip"
                                    style="color:#f43f5e;background:rgba(244,63,94,.1);border-color:rgba(244,63,94,.3);display:none">
                                    <span x-text="stats.pending_requests?.purchases"></span> مشتريات
                                </span>
                                <span x-show="(stats.pending_requests?.leaves||0)>0" class="chip"
                                    style="color:#f59e0b;background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3);display:none">
                                    <span x-text="stats.pending_requests?.leaves"></span> إجازات
                                </span>
                            </div>
                        </div>
                        <div class="mt-auto pt-7">
                            <div class="flex items-baseline gap-2.5">
                                <span class="hero-num" x-text="stats.pending_requests?.total ?? '—'"></span>
                                <span class="hero-label">طلب معلق</span>
                            </div>
                            <div class="card-cta" style="color:#f59e0b">
                                <span>مراجعة الطلبات</span>
                                <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- ════ TIER 2 — Medium cards (1-col) ════ --}}

                {{-- Leave Requests --}}
                <a href="/leave-requests" class="nav-card col-span-1"
                    style="--accent:#3b82f6;--glow:rgba(59,130,246,.18);--d:140ms">
                    <div class="card-blob"
                        style="background:radial-gradient(circle,rgba(59,130,246,.15),transparent 70%);width:180px;height:180px;top:-50px;right:-40px">
                    </div>
                    <div class="card-body" style="min-height:168px">
                        <div class="flex justify-between items-center mb-5">
                            <div class="card-icon sm"
                                style="background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.3)">
                                <span class="material-symbols-outlined text-[18px]" style="color:#3b82f6">event_busy</span>
                            </div>
                            <span x-show="(stats.pending_requests?.leaves||0)>0" class="badge"
                                style="background:rgba(59,130,246,.15);color:#60a5fa;display:none"
                                x-text="stats.pending_requests?.leaves"></span>
                        </div>
                        <h3 class="card-title mb-auto">طلبات الإجازة</h3>
                        <div class="mt-5">
                            <div class="flex justify-between text-[11px] mb-2">
                                <span class="text-slate-600">نسبة القبول</span>
                                <span style="color:#3b82f6" x-text="leaveRate + '%'"></span>
                            </div>
                            <div class="prog-track">
                                <div class="prog-bar" style="background:#3b82f6" :style="'width:' + leaveRate + '%'"></div>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Attendance --}}
                <a href="/attendance" class="nav-card col-span-1"
                    style="--accent:#10b981;--glow:rgba(16,185,129,.18);--d:190ms">
                    <div class="card-blob"
                        style="background:radial-gradient(circle,rgba(16,185,129,.14),transparent 70%);width:180px;height:180px;bottom:-40px;left:-40px">
                    </div>
                    <div class="card-body" style="min-height:168px">
                        <div class="mb-5">
                            <div class="card-icon sm"
                                style="background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.3)">
                                <span class="material-symbols-outlined text-[18px]"
                                    style="color:#10b981;font-variation-settings:'FILL' 1">fingerprint</span>
                            </div>
                        </div>
                        <h3 class="card-title mb-auto">سجل الحضور</h3>
                        <div class="mt-5 flex items-baseline gap-2">
                            <span class="text-[30px] font-[900] text-white leading-none"
                                x-text="(stats.attendance_today?.present_rate ?? 94) + '%'"></span>
                            <div class="flex items-center gap-1" style="color:#10b981">
                                <span class="material-symbols-outlined text-[13px]">trending_up</span>
                                <span class="text-[11px]">اليوم</span>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Payroll --}}
                <a x-show="can('view_payroll')" href="/payroll" class="nav-card col-span-1"
                    style="--accent:#8b5cf6;--glow:rgba(139,92,246,.18);--d:240ms">
                    <div class="card-blob"
                        style="background:radial-gradient(circle,rgba(139,92,246,.14),transparent 70%);width:200px;height:200px;top:-60px;left:-50px">
                    </div>
                    <div class="card-body" style="min-height:168px">
                        <div class="mb-5">
                            <div class="card-icon sm"
                                style="background:rgba(139,92,246,.12);border-color:rgba(139,92,246,.3)">
                                <span class="material-symbols-outlined text-[18px]"
                                    style="color:#8b5cf6;font-variation-settings:'FILL' 1">payments</span>
                            </div>
                        </div>
                        <h3 class="card-title mb-auto">مسير الرواتب</h3>
                        <div class="mt-5">
                            <span class="text-[24px] font-[900] text-white leading-none"
                                x-text="formatMoney(stats.payroll_this_month?.total_amount)"></span>
                            <span class="text-[12px] text-slate-500"> ر.س</span>
                        </div>
                    </div>
                </a>

                {{-- Purchases --}}
                <a href="/purchase-requests" class="nav-card col-span-1"
                    style="--accent:#f43f5e;--glow:rgba(244,63,94,.18);--d:290ms">
                    <div class="card-blob"
                        style="background:radial-gradient(circle,rgba(244,63,94,.14),transparent 70%);width:180px;height:180px;top:-50px;right:-40px">
                    </div>
                    <div class="card-body" style="min-height:168px">
                        <div class="flex justify-between items-center mb-5">
                            <div class="card-icon sm"
                                style="background:rgba(244,63,94,.12);border-color:rgba(244,63,94,.3)">
                                <span class="material-symbols-outlined text-[18px]"
                                    style="color:#f43f5e;font-variation-settings:'FILL' 1">shopping_cart</span>
                            </div>
                            <span x-show="(stats.pending_requests?.purchases||0)>0" class="badge"
                                style="background:rgba(244,63,94,.15);color:#fb7185;display:none"
                                x-text="stats.pending_requests?.purchases"></span>
                        </div>
                        <h3 class="card-title mb-auto">طلبات الشراء</h3>
                        <div class="mt-5">
                            <div class="flex justify-between text-[11px] mb-2">
                                <span class="text-slate-600">معدل الموافقة</span>
                                <span style="color:#f43f5e" x-text="budgetRate + '%'"></span>
                            </div>
                            <div class="prog-track">
                                <div class="prog-bar" style="background:#f43f5e" :style="'width:' + budgetRate + '%'"></div>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- ════ TIER 3 — Compact utility cards (1-col) ════ --}}

                <a href="/org-chart" class="nav-card compact col-span-1"
                    style="--accent:#06b6d4;--glow:rgba(6,182,212,.15);--d:340ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(6,182,212,.1);border-color:rgba(6,182,212,.3)">
                            <span class="material-symbols-outlined text-[16px]" style="color:#06b6d4">account_tree</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">الهيكل التنظيمي</p>
                            <p class="util-sub">3 مستويات إدارية</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

                <a x-show="can('manage_periods')" href="/periods" class="nav-card compact col-span-1"
                    style="--accent:#f59e0b;--glow:rgba(245,158,11,.15);--d:385ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3)">
                            <span class="material-symbols-outlined text-[16px]"
                                style="color:#f59e0b;font-variation-settings:'FILL' 1">timer</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">الفترات</p>
                            <p class="util-sub">قواعد الدخول والتأخير</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

                <a x-show="can('manage_shifts')" href="/shifts" class="nav-card compact col-span-1"
                    style="--accent:#f97316;--glow:rgba(249,115,22,.15);--d:390ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(249,115,22,.1);border-color:rgba(249,115,22,.3)">
                            <span class="material-symbols-outlined text-[16px]" style="color:#f97316">schedule</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">ورديات الدوام</p>
                            <p class="util-sub">3 ورديات نشطة</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

                <a x-show="can('manage_roles')" href="/roles" class="nav-card compact col-span-1"
                    style="--accent:#a855f7;--glow:rgba(168,85,247,.15);--d:440ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(168,85,247,.1);border-color:rgba(168,85,247,.3)">
                            <span class="material-symbols-outlined text-[16px]"
                                style="color:#a855f7;font-variation-settings:'FILL' 1">admin_panel_settings</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">الأدوار والصلاحيات</p>
                            <p class="util-sub">6 أدوار معرّفة</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

                <a x-show="can('manage_company_settings')" href="/company" class="nav-card compact col-span-1"
                    style="--accent:#0ea5a4;--glow:rgba(14,165,164,.15);--d:485ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(14,165,164,.1);border-color:rgba(14,165,164,.3)">
                            <span class="material-symbols-outlined text-[16px]"
                                style="color:#0ea5a4;font-variation-settings:'FILL' 1">business</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">إعدادات الشركة</p>
                            <p class="util-sub">الاسم وأنواع العقود</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

                <a x-show="can('manage_departments')" href="/departments" class="nav-card compact col-span-1"
                    style="--accent:#14b8a6;--glow:rgba(20,184,166,.15);--d:490ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(20,184,166,.1);border-color:rgba(20,184,166,.3)">
                            <span class="material-symbols-outlined text-[16px]"
                                style="color:#14b8a6;font-variation-settings:'FILL' 1">apartment</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">الأقسام</p>
                            <p class="util-sub">هيكلة الأقسام</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

                <a x-show="can('manage_rules')" href="/approval-rules" class="nav-card compact col-span-1"
                    style="--accent:#eab308;--glow:rgba(234,179,8,.15);--d:540ms">
                    <div class="util-body">
                        <div class="card-icon xs" style="background:rgba(234,179,8,.1);border-color:rgba(234,179,8,.3)">
                            <span class="material-symbols-outlined text-[16px]"
                                style="color:#eab308;font-variation-settings:'FILL' 1">gavel</span>
                        </div>
                        <div class="flex-1">
                            <p class="util-title">القواعد والحدود</p>
                            <p class="util-sub">قواعد الموافقة</p>
                        </div>
                        <span
                            class="material-symbols-outlined text-[15px] text-slate-700 group-hover:text-slate-400 transition-colors">arrow_back_ios</span>
                    </div>
                </a>

            </div>{{-- /grid --}}
        </div>
    </div>

    <style>
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Card base ───────────────────────────────────────────────────── */
        .nav-card {
            position: relative;
            display: flex;
            flex-direction: column;
            background: var(--c-card);
            border: 1px solid var(--c-border);
            border-radius: 20px;
            overflow: hidden;
            text-decoration: none;
            cursor: pointer;
            opacity: 0;
            animation: fadeUp .55s cubic-bezier(.22, 1, .36, 1) both;
            animation-delay: var(--d, 0ms);
            transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
        }

        .nav-card {
            box-shadow: var(--c-shadow);
        }

        .nav-card:hover {
            transform: translateY(-5px) scale(1.005);
            border-color: var(--accent, #0ea5a4);
            box-shadow: 0 28px 70px -18px var(--glow, rgba(14, 165, 164, .2));
        }

        .nav-card.compact {
            flex-direction: row;
        }

        /* ── Blob background ─────────────────────────────────────────────── */
        .card-blob {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            opacity: 1;
            transition: transform .4s ease;
        }

        .nav-card:hover .card-blob {
            transform: scale(1.15);
        }

        /* ── Card body ───────────────────────────────────────────────────── */
        .card-body {
            position: relative;
            z-index: 1;
            padding: 22px 22px 20px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .util-body {
            position: relative;
            z-index: 1;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
        }

        /* ── Icons ───────────────────────────────────────────────────────── */
        .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            border: 1px solid;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform .22s ease;
        }

        .nav-card:hover .card-icon {
            transform: scale(1.08);
        }

        .card-icon.sm {
            width: 36px;
            height: 36px;
            border-radius: 10px;
        }

        .card-icon.xs {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            flex-shrink: 0;
        }

        /* ── Typography ──────────────────────────────────────────────────── */
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--c-text-1);
            line-height: 1.3;
        }

        .card-sub {
            font-size: 11px;
            color: var(--c-text-3);
            margin-top: 2px;
        }

        .hero-num {
            font-size: 46px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -1px;
            font-family: 'Tajawal', sans-serif;
            color: var(--c-text-1);
        }

        .hero-label {
            font-size: 13px;
            color: var(--c-text-3);
        }

        .card-cta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 14px;
            opacity: .7;
            transition: opacity .2s;
        }

        .nav-card:hover .card-cta {
            opacity: 1;
        }

        .util-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--c-text-1);
        }

        .util-sub {
            font-size: 11px;
            color: var(--c-text-3);
            margin-top: 2px;
        }

        /* ── Chips / badges ──────────────────────────────────────────────── */
        .chip {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid;
            white-space: nowrap;
        }

        .badge {
            font-size: 11px;
            font-weight: 800;
            padding: 2px 9px;
            border-radius: 999px;
            min-width: 24px;
            text-align: center;
        }

        /* ── Progress bar ────────────────────────────────────────────────── */
        .prog-track {
            height: 3px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .06);
            overflow: hidden;
        }

        .prog-bar {
            height: 100%;
            border-radius: 999px;
            transition: width .8s cubic-bezier(.22, 1, .36, 1);
        }
    </style>

@endsection

@push('scripts')
    <script>
        function dashboardPage() {
            return {
                stats: {},

                can(perm) {
                    return window.$can(perm);
                },
                canAny(...perms) {
                    return window.$canAny(...perms);
                },

                get firstName() {
                    try {
                        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                        return u.name?.split(' ')[0] || 'مستخدم';
                    } catch (e) {
                        return 'مستخدم';
                    }
                },

                get todayStr() {
                    const d = new Date();
                    const days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                    const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر',
                        'أكتوبر', 'نوفمبر', 'ديسمبر'
                    ];
                    return `${days[d.getDay()]}، ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                },

                get leaveRate() {
                    const app = this.stats.request_stats?.leaves?.approved || 0;
                    const pen = this.stats.pending_requests?.leaves || 0;
                    const tot = app + pen;
                    return tot > 0 ? Math.min(Math.round((app / tot) * 100), 100) : 78;
                },

                get budgetRate() {
                    const app = this.stats.request_stats?.purchases?.approved || 0;
                    const pen = this.stats.pending_requests?.purchases || 0;
                    const tot = app + pen;
                    return tot > 0 ? Math.min(Math.round((app / tot) * 100), 100) : 54;
                },

                formatMoney(v) {
                    if (!v && v !== 0) return '—';
                    return Number(v).toLocaleString('ar-SA', {
                        maximumFractionDigits: 0
                    });
                },

                async init() {
                    try {
                        const r = await axios.get('/api/v1/dashboard/stats');
                        if (r.data.success) this.stats = r.data.data;
                    } catch (e) {}
                }
            };
        }
    </script>
@endpush
