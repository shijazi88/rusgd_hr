@extends('layouts.app')
@section('title', 'الهيكل التنظيمي')

@section('content')
    <div x-data="orgChart()" x-init="init()" class="min-h-screen bg-[#071426]">

        {{-- Header --}}
        <div class="max-w-[1440px] mx-auto px-8 pt-8 pb-4">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="font-h1 text-xl font-bold text-on-surface mb-1">الهيكل التنظيمي</h1>
                    <p class="text-xs text-on-surface-variant">التسلسل الوظيفي وهيكل الإدارة</p>
                </div>
                <div class="flex items-center gap-5">
                    {{-- Legend --}}
                    <div class="flex items-center gap-5 text-[11px] font-semibold text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,.6)]"></span>
                            المدير العام
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-400 shadow-[0_0_6px_rgba(20,184,166,.6)]"></span>
                            المدير المباشر
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span
                                class="w-2.5 h-2.5 rounded-full bg-indigo-400 shadow-[0_0_6px_rgba(99,102,241,.5)]"></span>
                            الموظف
                        </span>
                    </div>
                    <div class="w-px h-5 bg-[#1e3a5f]"></div>
                    <input x-model="search" @input="doSearch()" type="text" placeholder="بحث عن موظف..."
                        class="bg-surface-container border border-outline-variant rounded-xl px-4 py-2 text-on-surface text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500/30 outline-none w-44 transition-all duration-200">
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="flex flex-col items-center justify-center py-40 gap-3">
            <span class="material-symbols-outlined text-[44px] animate-spin text-teal-500">progress_activity</span>
            <span class="text-sm text-slate-500">جار تحميل الهيكل التنظيمي...</span>
        </div>

        {{-- Chart --}}
        <div x-show="!loading" class="overflow-x-auto overflow-y-visible pb-20">
            <div id="oc-wrapper" class="relative flex justify-center min-w-full px-16 py-4">
                <svg id="oc-svg" class="absolute inset-0 pointer-events-none"
                    style="width:100%;height:100%;overflow:visible;z-index:0"></svg>
                <div id="oc-root" class="relative flex gap-16 justify-center items-start" style="z-index:1"></div>
            </div>
        </div>

        {{-- Empty --}}
        <div x-show="!loading && roots.length === 0" class="text-center py-32 text-slate-500">
            <span class="material-symbols-outlined text-[52px] text-[#1e3a5f]">account_tree</span>
            <p class="mt-3 text-sm">لا يوجد هيكل تنظيمي</p>
        </div>

    </div>

    <style>
        /* ── Tier colour tokens ──────────────────────────────────────────── */
        :root {
            --c0: #f59e0b;
            --c0-bg: rgba(245, 158, 11, .09);
            --c0-ring: rgba(245, 158, 11, .35);
            --c1: #14b8a6;
            --c1-bg: rgba(20, 184, 166, .09);
            --c1-ring: rgba(20, 184, 166, .30);
            --c2: #818cf8;
            --c2-bg: rgba(99, 102, 241, .09);
            --c2-ring: rgba(99, 102, 241, .28);
            --line: var(--rs-line);
        }

        /* ── Card ────────────────────────────────────────────────────────── */
        .oc-card {
            position: relative;
            min-width: 164px;
            max-width: 196px;
            background: var(--c-card);
            border: 1.5px solid var(--c-border);
            border-radius: 16px;
            padding: 18px 16px 13px;
            text-align: center;
            opacity: 0;
            transform: translateY(22px) scale(.95);
            transition: opacity .45s ease, transform .45s ease,
                box-shadow .22s ease, border-color .22s ease;
        }

        .oc-card.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .oc-card:hover {
            transform: translateY(-5px) scale(1) !important;
        }

        .oc-card.l0 {
            border-color: var(--c0-ring);
            background: linear-gradient(150deg, color-mix(in srgb, var(--c-card) 80%, #0ea5a4) 0%, var(--c-card) 100%);
            box-shadow: 0 0 32px rgba(245, 158, 11, .07), inset 0 1px 0 rgba(245, 158, 11, .1);
        }

        .oc-card.l0:hover {
            box-shadow: 0 10px 40px rgba(245, 158, 11, .22);
            border-color: var(--c0);
        }

        .oc-card.l1 {
            border-color: var(--c1-ring);
        }

        .oc-card.l1:hover {
            box-shadow: 0 10px 36px rgba(20, 184, 166, .18);
            border-color: var(--c1);
        }

        .oc-card.l2 {
            border-color: var(--c2-ring);
            background: var(--c-card);
        }

        .oc-card.l2:hover {
            box-shadow: 0 10px 32px rgba(99, 102, 241, .15);
            border-color: var(--c2);
        }

        /* ── Tier badge ──────────────────────────────────────────────────── */
        .oc-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .06em;
            padding: 2px 11px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .l0 .oc-badge {
            background: var(--c0);
            color: #000;
        }

        .l1 .oc-badge {
            background: var(--c1);
            color: #002e2b;
        }

        .l2 .oc-badge {
            background: #6366f1;
            color: #fff;
        }

        /* ── Sequence number ─────────────────────────────────────────────── */
        .oc-seq {
            position: absolute;
            top: 10px;
            left: 11px;
            font-size: 9px;
            font-weight: 800;
            opacity: .45;
        }

        .l0 .oc-seq {
            color: var(--c0);
        }

        .l1 .oc-seq {
            color: var(--c1);
        }

        .l2 .oc-seq {
            color: var(--c2);
        }

        /* ── Status dot ──────────────────────────────────────────────────── */
        .oc-dot {
            position: absolute;
            top: 11px;
            right: 11px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .oc-dot.active {
            background: #22c55e;
            box-shadow: 0 0 0 2.5px rgba(34, 197, 94, .2);
        }

        .oc-dot.on_leave {
            background: #f59e0b;
            box-shadow: 0 0 0 2.5px rgba(245, 158, 11, .2);
        }

        .oc-dot.terminated {
            background: #ef4444;
        }

        .oc-dot.inactive {
            background: #475569;
        }

        /* ── Avatar ──────────────────────────────────────────────────────── */
        .oc-av {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 17px;
            border: 2px solid transparent;
            transition: transform .22s ease, box-shadow .22s ease;
        }

        .oc-card:hover .oc-av {
            transform: scale(1.1);
        }

        .l0 .oc-av {
            background: var(--c0-bg);
            color: var(--c0);
            border-color: var(--c0);
            box-shadow: 0 0 16px rgba(245, 158, 11, .25);
        }

        .l1 .oc-av {
            background: var(--c1-bg);
            color: var(--c1);
            border-color: var(--c1);
        }

        .l2 .oc-av {
            background: var(--c2-bg);
            color: var(--c2);
            border-color: #6366f1;
        }

        /* ── Text ────────────────────────────────────────────────────────── */
        .oc-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--c-text-1);
            line-height: 1.3;
        }

        .oc-title {
            font-size: 11px;
            color: var(--c-text-3);
            margin-top: 3px;
        }

        .oc-dept {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 999px;
            margin-top: 8px;
        }

        .l0 .oc-dept {
            background: var(--c0-bg);
            color: var(--c0);
        }

        .l1 .oc-dept {
            background: var(--c1-bg);
            color: var(--c1);
        }

        .l2 .oc-dept {
            background: var(--c2-bg);
            color: var(--c2);
        }

        /* ── SVG connector glow on hover ─────────────────────────────────── */
        .oc-path {
            transition: stroke .3s, stroke-width .3s;
        }

        /* ── Search states ───────────────────────────────────────────────── */
        .oc-col.dim>.oc-card {
            opacity: .1 !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .oc-col.hi>.oc-card {
            box-shadow: 0 0 0 2px #f59e0b, 0 8px 30px rgba(245, 158, 11, .3) !important;
            border-color: #f59e0b !important;
        }
    </style>

@endsection

@push('scripts')
    <script>
        function orgChart() {
            return {
                roots: [],
                loading: false,
                search: '',
                _seq: 0, // global sequence counter

                async init() {
                    this.loading = true;
                    try {
                        const r = await axios.get('/api/v1/org-chart');
                        if (r.data.success) this.roots = r.data.data || [];
                    } catch (e) {}
                    this.loading = false;
                    await this.$nextTick();
                    setTimeout(() => this.render(), 40);
                },

                initials(name) {
                    return (name || '').trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
                },

                tierLabel(depth) {
                    if (depth === 0) return 'المدير العام';
                    if (depth === 1) return 'المدير المباشر';
                    return 'موظف';
                },

                makeCard(node, depth, seq) {
                    const lvl = Math.min(depth, 2);
                    const card = document.createElement('div');
                    card.className = `oc-card l${lvl}`;
                    card.dataset.id = node.id;
                    card.dataset.name = (node.name || '').toLowerCase();
                    card.dataset.childIds = JSON.stringify((node.children || []).map(c => c.id));

                    card.innerHTML = `
                <div class="oc-badge">${this.tierLabel(depth)}</div>
                <div class="oc-seq">#${seq}</div>
                <div class="oc-dot ${node.status || 'inactive'}"></div>
                <div class="oc-av">${this.initials(node.name)}</div>
                <div class="oc-name">${node.name || '—'}</div>
                <div class="oc-title">${node.job_title || ''}</div>
                ${node.department ? `<div class="oc-dept">${node.department}</div>` : ''}
            `;
                    return card;
                },

                // Build a col: card + optional children-row below
                buildCol(node, depth) {
                    this._seq++;
                    const seq = this._seq;
                    const col = document.createElement('div');
                    col.className = 'oc-col';
                    col.dataset.id = node.id;
                    col.style.cssText = 'display:flex;flex-direction:column;align-items:center;';

                    const card = this.makeCard(node, depth, seq);
                    col.appendChild(card);

                    // Stagger card reveal by depth
                    const delay = depth * 160 + seq * 30;
                    setTimeout(() => card.classList.add('show'), delay);

                    if (node.children && node.children.length > 0) {
                        const gap = 24;
                        const childRow = document.createElement('div');
                        childRow.style.cssText =
                            `display:flex;gap:${gap}px;justify-content:center;align-items:flex-start;padding-top:56px;`;

                        node.children.forEach(child => {
                            childRow.appendChild(this.buildCol(child, depth + 1));
                        });
                        col.appendChild(childRow);
                    }

                    return col;
                },

                // Draw animated bezier SVG paths after layout
                drawConnectors() {
                    const svg = document.getElementById('oc-svg');
                    const wrapper = document.getElementById('oc-wrapper');
                    if (!svg || !wrapper) return;
                    svg.innerHTML = '';

                    const wRect = wrapper.getBoundingClientRect();

                    wrapper.querySelectorAll('.oc-card[data-child-ids]').forEach((parentCard, pi) => {
                        let childIds;
                        try {
                            childIds = JSON.parse(parentCard.dataset.childIds);
                        } catch (e) {
                            return;
                        }
                        if (!childIds.length) return;

                        const pRect = parentCard.getBoundingClientRect();
                        const px = pRect.left + pRect.width / 2 - wRect.left;
                        const py = pRect.bottom - wRect.top + 2;

                        childIds.forEach((cid, ci) => {
                            const childCard = wrapper.querySelector(`.oc-card[data-id="${cid}"]`);
                            if (!childCard) return;
                            const cRect = childCard.getBoundingClientRect();
                            const cx = cRect.left + cRect.width / 2 - wRect.left;
                            const cy = cRect.top - wRect.top - 2;

                            // Cubic bezier: control points at 55% of the vertical gap
                            const cpY = py + (cy - py) * 0.55;
                            const d = `M ${px} ${py} C ${px} ${cpY}, ${cx} ${cpY}, ${cx} ${cy}`;

                            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                            path.setAttribute('d', d);
                            path.setAttribute('fill', 'none');
                            path.setAttribute('stroke', 'var(--line)');
                            path.setAttribute('stroke-width', '2');
                            path.setAttribute('stroke-linecap', 'round');
                            path.classList.add('oc-path');
                            svg.appendChild(path);

                            // Animate draw with stagger
                            const len = path.getTotalLength();
                            path.style.strokeDasharray = len;
                            path.style.strokeDashoffset = len;
                            const drawDelay = (pi + ci) * 60 + 280;
                            path.style.transition =
                                `stroke-dashoffset 0.65s cubic-bezier(.4,0,.2,1) ${drawDelay}ms`;
                            requestAnimationFrame(() => {
                                setTimeout(() => {
                                    path.style.strokeDashoffset = '0';
                                }, 30);
                            });
                        });
                    });
                },

                render() {
                    const root = document.getElementById('oc-root');
                    if (!root) return;
                    root.innerHTML = '';
                    this._seq = 0;

                    this.roots.forEach(r => root.appendChild(this.buildCol(r, 0)));

                    // Wait for layout then draw connectors
                    setTimeout(() => this.drawConnectors(), 90);
                },

                doSearch() {
                    const q = this.search.trim().toLowerCase();
                    document.querySelectorAll('.oc-col').forEach(col => {
                        if (!q) {
                            col.classList.remove('dim', 'hi');
                            return;
                        }
                        const card = col.querySelector(':scope > .oc-card');
                        const match = card && card.dataset.name?.includes(q);
                        col.classList.toggle('dim', !match);
                        col.classList.toggle('hi', !!match);
                    });
                },
            };
        }
    </script>
@endpush
