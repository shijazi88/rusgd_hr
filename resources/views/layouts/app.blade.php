<!DOCTYPE html>
{{-- Theme class set before paint — avoids flash --}}
<html lang="ar" dir="rtl">
<head>
    {{-- No-flash theme init: runs synchronously before CSS parses --}}
    <script>
        (function(){
            var t = localStorage.getItem('rushd-theme') || 'dark';
            document.documentElement.classList.toggle('dark', t === 'dark');
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'الرئيسية') — RushdAI HR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Editorial display serif. Latin: Cormorant Garamond (Copernicus substitute).
         Arabic: Amiri — classical Naskh, the closest visual analogue to a Latin
         literary serif (Markazi Text retained as fallback). Body sans stays
         Inter / Tajawal. --}}
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Amiri:wght@400;700&family=Markazi+Text:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    /* ── Fixed brand colours (same in both modes) ─── */
                    'primary-container': '#0ea5a4',
                    'primary':           '#5ed9d7',
                    'primary-fixed':     '#7df5f4',
                    'primary-fixed-dim': '#5ed9d7',
                    'on-primary':        '#003736',
                    'on-primary-container': '#003333',
                    'inverse-primary':   '#006a69',

                    /* ── Semantic surface tokens → CSS variables ─── */
                    'surface':                   'var(--c-bg)',
                    'surface-dim':               'var(--c-bg)',
                    'surface-bright':            'var(--c-card-h)',
                    'surface-tint':              '#5ed9d7',
                    'surface-variant':           'var(--c-card-m)',
                    'surface-container-lowest':  'var(--c-card-xs)',
                    'surface-container-low':     'var(--c-card-l)',
                    'surface-container':         'var(--c-card-m)',
                    'surface-container-high':    'var(--c-card-h)',
                    'surface-container-highest': 'var(--c-card-m)',
                    'background':                'var(--c-bg)',

                    /* ── Semantic text tokens → CSS variables ──────── */
                    'on-surface':         'var(--c-text-1)',
                    'on-surface-variant': 'var(--c-text-2)',
                    'on-background':      'var(--c-text-1)',

                    /* ── Border / outline tokens ────────────────────── */
                    'outline':         'var(--c-text-3)',
                    'outline-variant': 'var(--c-border-h)',

                    /* ── Status / error ─────────────────────────────── */
                    'error':           'var(--c-error)',
                    'error-container': 'var(--c-err-bg)',
                    'on-error':        'var(--c-on-error)',
                    'on-error-container': 'var(--c-text-1)',

                    /* ── Secondary / tertiary (kept for compatibility) -- */
                    'secondary':               '#b5c7ea',
                    'secondary-container':     '#384a67',
                    'on-secondary':            '#1f314d',
                    'on-secondary-container':  '#a7b9db',
                    'tertiary':                '#bbc7e0',
                    'tertiary-container':      '#8995ac',
                    'on-tertiary':             '#253144',
                    'on-tertiary-container':   '#222e41',
                    'inverse-surface':         'var(--c-text-1)',
                    'inverse-on-surface':      'var(--c-bg)',
                },
                /* ── Editorial radius hierarchy (Claude design system) ──
                   xs  4px · sm 6px · md 8px (buttons/inputs) ·
                   lg 12px (cards) · xl 16px (hero) · 2xl 20px ·
                   pill / full = 9999px. Softer than the prior
                   2-12px scale; cards now feel editorial.       */
                borderRadius: {
                    'none':    '0',
                    'xs':      '4px',
                    'sm':      '6px',
                    'DEFAULT': '8px',
                    'md':      '8px',
                    'lg':      '12px',
                    'xl':      '16px',
                    '2xl':     '20px',
                    '3xl':     '24px',
                    'pill':    '9999px',
                    'full':    '9999px',
                },
                /* ── Type system ──
                   Display = serif (Latin → Cormorant Garamond,
                   Arabic → Amiri Naskh, classical/calligraphic —
                   the closest analogue to a Latin literary serif.
                   Markazi Text kept as defensive fallback).
                   Body = humanist sans (Inter + Tajawal).
                   Code = JetBrains Mono.                          */
                fontFamily: {
                    'h1':             ['"Cormorant Garamond"', '"Amiri"', '"Markazi Text"', '"EB Garamond"', 'Georgia', 'serif'],
                    'h2':             ['"Cormorant Garamond"', '"Amiri"', '"Markazi Text"', '"EB Garamond"', 'Georgia', 'serif'],
                    'display':        ['"Cormorant Garamond"', '"Amiri"', '"Markazi Text"', '"EB Garamond"', 'Georgia', 'serif'],
                    'body-main':      ['Inter', 'Tajawal', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'sans-serif'],
                    'body-secondary': ['Inter', 'Tajawal', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'sans-serif'],
                    'label-caps':     ['Inter', 'Tajawal', 'sans-serif'],
                    'mono':           ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                },
                /* Display sizes: weight 500 + negative tracking (Claude editorial voice).
                   `[dir=rtl]` neutralises the negative tracking for Arabic — see CSS below. */
                fontSize: {
                    'display-xl':     ['64px', { lineHeight: '1.05', letterSpacing: '-1.5px', fontWeight: '500' }],
                    'display-lg':     ['48px', { lineHeight: '1.1',  letterSpacing: '-1px',   fontWeight: '500' }],
                    'display-md':     ['36px', { lineHeight: '1.15', letterSpacing: '-0.5px', fontWeight: '500' }],
                    'display-sm':     ['28px', { lineHeight: '1.2',  letterSpacing: '-0.3px', fontWeight: '500' }],
                    'h1':             ['40px', { lineHeight: '1.1',  letterSpacing: '-0.8px', fontWeight: '500' }],
                    'h2':             ['28px', { lineHeight: '1.2',  letterSpacing: '-0.3px', fontWeight: '500' }],
                    'title-lg':       ['22px', { lineHeight: '1.3',  fontWeight: '500' }],
                    'title-md':       ['18px', { lineHeight: '1.4',  fontWeight: '500' }],
                    'title-sm':       ['16px', { lineHeight: '1.4',  fontWeight: '500' }],
                    'body-main':      ['16px', { lineHeight: '1.55', fontWeight: '400' }],
                    'body-secondary': ['14px', { lineHeight: '1.55', fontWeight: '400' }],
                    'caption':        ['13px', { lineHeight: '1.4',  fontWeight: '500' }],
                    'caption-up':     ['12px', { lineHeight: '1.4',  letterSpacing: '1.5px', fontWeight: '500' }],
                    'label-caps':     ['11px', { lineHeight: '1',    letterSpacing: '0.05em', fontWeight: '700' }],
                    'btn':            ['14px', { lineHeight: '1',    fontWeight: '500' }],
                    'nav-link':       ['14px', { lineHeight: '1.4',  fontWeight: '500' }],
                    'code':           ['14px', { lineHeight: '1.6',  fontWeight: '400' }],
                },
                /* ── Motion timing (Claude pacing — calm, never aggressive).
                   Keyframes (`fade-up`, `fade-in`) live in the inline <style>
                   block below as the single source of truth — Tailwind's
                   `animate-*` utilities reference them by name. */
                transitionTimingFunction: {
                    'editorial': 'cubic-bezier(0.32, 0.72, 0, 1)',
                },
                transitionDuration: {
                    '250': '250ms',
                    '350': '350ms',
                },
                animation: {
                    'fade-up':  'fade-up 400ms cubic-bezier(0.32, 0.72, 0, 1) both',
                    'fade-in':  'fade-in 250ms ease-out both',
                },
            }
        }
    }
    </script>

    <style>
        /* ══════════════════════════════════════════════════════════
           DESIGN TOKEN SYSTEM — from RUSHD AI brand guide
           Light mode = :root default
           Dark  mode = .dark overrides
        ══════════════════════════════════════════════════════════ */

        :root {
            /* ── Backgrounds — clean blue-slate palette ─────────────
               Inspired by Stripe / Linear: cool-tinted whites with
               clear depth hierarchy. Teal accent pops naturally.   */
            --c-bg:      #EDF1F7;   /* page — soft blue-slate        */
            --c-card:    #FFFFFF;   /* cards — pure white            */
            --c-card-xs: #E2E8F2;   /* inputs, tags, active states   */
            --c-card-l:  #F5F7FA;   /* table rows, subtle fills      */
            --c-card-m:  #EDF1F7;   /* filter bars, table headers    */
            --c-card-h:  #DDE4EF;   /* stronger dividers, secondary  */

            /* Card shadow — lifts white cards off the blue-slate bg */
            --c-shadow:  0 1px 3px rgba(15,23,42,0.08),
                         0 1px 2px rgba(15,23,42,0.04);

            /* Text — brand guide values */
            --c-text-1:   #0F172A;
            --c-text-2:   #475569;
            --c-text-3:   #94A3B8;

            /* Borders — brand: rgba(15,23,42,0.08) */
            --c-border:   rgba(15, 23, 42, 0.09);
            --c-border-h: rgba(15, 23, 42, 0.16);

            /* Status */
            --c-error:    #dc2626;
            --c-err-bg:   #fee2e2;
            --c-on-error: #fff;

            /* Floating widget */
            --c-widget-bg:     rgba(255,255,255,0.95);
            --c-widget-border: rgba(15,23,42,0.10);
            --c-widget-strip:  rgba(15,23,42,0.04);

            /* Select option */
            --c-opt-bg: #ffffff;

            /* Avatar ring */
            --c-avatar-ring: rgba(14,165,164,0.45);
        }

        .dark {
            /* Backgrounds — deep navy palette */
            --c-bg:      #071426;
            --c-card:    #0b1f3a;
            --c-card-xs: #050e1d;
            --c-card-l:  #121c2b;
            --c-card-m:  #162030;
            --c-card-h:  #202a3a;

            /* No card elevation in dark — colour contrast is enough */
            --c-shadow:  none;

            /* Text */
            --c-text-1:   #d9e3f8;
            --c-text-2:   #bcc9c8;
            --c-text-3:   #475569;

            /* Borders */
            --c-border:   rgba(255,255,255,0.06);
            --c-border-h: rgba(255,255,255,0.12);

            /* Status */
            --c-error:    #ffb4ab;
            --c-err-bg:   #93000a;
            --c-on-error: #690005;

            /* Floating widget */
            --c-widget-bg:     #0b1d35;
            --c-widget-border: rgba(255,255,255,0.08);
            --c-widget-strip:  rgba(255,255,255,0.04);

            /* Select option */
            --c-opt-bg: #0c1d35;

            /* Avatar ring in dark */
            --c-avatar-ring: rgba(14,165,164,0.35);
        }

        /* ══════════════════════════════════════════════════════════
           EDITORIAL TYPOGRAPHY (Claude design system, applied
           globally so existing pages get the new voice without
           per-page edits). Display headlines pick up the serif
           stack via .font-h1 / .font-h2 / .font-display utilities;
           inline `font-[900]` weights from existing pages are
           softened to a tasteful editorial weight via the rules
           below — Claude's display rule is "weight 400, never bold."
        ══════════════════════════════════════════════════════════ */

        /* Body inherits humanist sans (Inter for Latin → falls through
           to Tajawal for Arabic glyphs). 1.55 line-height is the
           Claude body rhythm. */
        body {
            font-family: 'Inter', 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.55;
            font-feature-settings: "kern", "liga", "calt";
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* Display headlines — serif at editorial weight.
           Coverage:
             - Bare h1/h2/h3/h4 elements (Tailwind base resets all
               heading styling, so all bare headings inherit body
               sans by default; this restores them to the editorial
               serif voice without per-page edits).
             - Explicit utility classes (`.font-h1` / `.font-h2` /
               `.font-display`).
             - Known custom heading classes used in dashboard cards
               (`.card-title`, `.util-title`).
           !important beats inline `font-[900]` / `font-bold` so the
           editorial voice cascades cleanly across the system.    */
        h1, h2, h3, h4,
        .font-h1, .font-h2, .font-display,
        .card-title, .util-title {
            font-family: 'Cormorant Garamond', 'Amiri', 'Markazi Text', 'EB Garamond', Georgia, serif !important;
            font-weight: 500 !important;
            letter-spacing: -0.02em;
            font-feature-settings: "kern", "liga", "dlig";
        }
        .font-h1 { letter-spacing: -0.025em; line-height: 1.1; }
        .font-h2 { letter-spacing: -0.015em; line-height: 1.2; }

        /* Arabic neutralises Latin negative tracking — Amiri / Markazi
           are designed to read at default tracking; tightening the
           glyph spacing breaks the Naskh rhythm. Weight bumped to 700
           because Amiri at 500 reads thinner than Cormorant at 500
           and the editorial contrast must remain visible.            */
        html[dir="rtl"] h1,
        html[dir="rtl"] h2,
        html[dir="rtl"] h3,
        html[dir="rtl"] h4,
        html[dir="rtl"] .font-h1,
        html[dir="rtl"] .font-h2,
        html[dir="rtl"] .font-display,
        html[dir="rtl"] .card-title,
        html[dir="rtl"] .util-title {
            letter-spacing: 0 !important;
            font-weight: 700 !important;
        }

        /* Opt-out — when a heading element should stay sans (e.g.
           a label that happens to be semantically <h3> inside a
           form), apply `.font-sans` to override.                   */
        h1.font-sans, h2.font-sans, h3.font-sans, h4.font-sans {
            font-family: 'Inter', 'Tajawal', sans-serif !important;
            letter-spacing: 0 !important;
        }

        /* Caption uppercase — Claude's category-tag voice */
        .text-caption-up {
            font-family: 'Inter', 'Tajawal', sans-serif;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.125em;
            text-transform: uppercase;
        }

        /* Code — JetBrains Mono with the Claude code rhythm */
        code, pre, kbd, samp, .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-feature-settings: "calt", "ss01";
        }

        /* ══════════════════════════════════════════════════════════
           MOTION — subtle, calm, never aggressive
        ══════════════════════════════════════════════════════════ */

        /* Default ease for buttons / cards / nav — the Claude-pacing
           cubic-bezier feels considered, never bouncy. */
        :root {
            --ease-editorial: cubic-bezier(0.32, 0.72, 0, 1);
        }

        button, a, .rs-trigger, .rs-opt, .rs-btn-cancel, .rs-page-btn,
        input, select, textarea, [role="button"] {
            transition-property: background-color, border-color, color, box-shadow, transform, opacity;
            transition-duration: 200ms;
            transition-timing-function: var(--ease-editorial);
        }

        /* Soft press — Claude's "primary darkens on press" rule.
           Only the active state animates; no hover-grow / shadow-lift. */
        button:active, a:active, [role="button"]:active {
            transform: translateY(0.5px);
        }

        /* Cards lift only with a faint shadow on hover — never scale. */
        .rs-card-hover {
            transition: box-shadow 250ms var(--ease-editorial),
                        border-color 250ms var(--ease-editorial);
        }
        .rs-card-hover:hover {
            box-shadow: 0 1px 3px rgba(15,23,42,0.08), 0 6px 20px rgba(15,23,42,0.06);
        }

        /* Section enter — fade-up. Add `data-enter` on a section to opt in,
           or use Tailwind's `animate-fade-up` / `animate-fade-in` utilities. */
        [data-enter] {
            animation: fade-up 400ms var(--ease-editorial) both;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fade-in {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0ms !important;
                transition-duration: 0ms !important;
            }
        }

        /* ── Global element resets ───────────────────────────────── */
        /* Full class definition — don't rely on Google Fonts CSS to inject it.
           The variable-axis URL only reliably ships @font-face; without an
           explicit font-family + ligature feature here, icons render as raw
           text whenever the convenience class is missing from the response. */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            font-feature-settings: 'liga';
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--c-card-m); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #0ea5a4; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
        [x-cloak] { display: none !important; }

        /* ── Select / dropdown — single authoritative rule ─────────────
           Problem: Tailwind Forms plugin (loaded via ?plugins=forms)
           injects its OWN arrow on the RIGHT. Our page adds manual
           <span> icons. Combined = multiple arrows.

           Solution: one rule with specificity [0,1,1]  (html[dir] +
           select type) which beats:
             • Tailwind Forms'  select {}        → [0,0,1]  ✓
             • Tailwind utility .px-4 {}         → [0,1,0]  ✓
             • Our own         :where(select) {} → [0,0,0]  ✓

           Arrow on the LEFT = RTL "end" side (correct for Arabic).  ── */

        /* 1 — strip appearance from all selects */
        :where(select) { appearance: none; -webkit-appearance: none; }

        /* 2 — single themed arrow, RTL position, proper padding */
        /* Dark mode arrow — slate #94A3B8 */
        html[dir="rtl"] select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 12px center;
            background-size: 14px 14px;
            padding-left: 2.25rem;   /* 36px — clears the arrow */
            padding-right: 0.75rem;  /* reset Tailwind Forms' right padding */
            cursor: pointer;
        }
        /* Light mode arrow — darker for contrast on light bg */
        html[dir="rtl"]:not(.dark) select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        }

        /* 3 — option list dark-theme colours */
        select option,
        select optgroup { background-color: var(--c-opt-bg); color: var(--c-text-1); }
        select option:checked { background-color: var(--c-card-m); }

        /* ── Floating widget ─────────────────────────────────────── */
        .rs-widget-card {
            background: var(--c-widget-bg);
            border: 1px solid var(--c-widget-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .rs-widget-strip {
            background: var(--c-widget-strip);
            border-bottom: 1px solid var(--c-widget-border);
        }
        .rs-widget-row {
            color: var(--c-text-2);
        }
        .rs-widget-row:hover {
            background: var(--c-widget-strip);
            color: var(--c-text-1);
        }

        /* ── Card shadow — light mode only ──────────────────────── */
        html:not(.dark) .bg-\[\#0B1F3A\],
        html:not(.dark) .bg-\[\#0b1f3a\],
        html:not(.dark) .bg-\[\#0c1f39\],
        html:not(.dark) .bg-\[\#0b1d35\] {
            box-shadow: var(--c-shadow) !important;
        }

        /* ══════════════════════════════════════════════════════════
           CUSTOM DROPDOWN  (.rs-trigger / .rs-panel / .rs-opt)
           Replaces native <select> everywhere. Fully styled,
           dark/light adaptive, RTL-correct.
        ══════════════════════════════════════════════════════════ */
        .rs-trigger {
            display: flex; align-items: center; justify-content: space-between;
            gap: 8px; width: 100%; padding: 9px 14px;
            background: var(--c-card-xs);
            border: 1px solid var(--c-border-h);
            border-radius: 10px;
            color: var(--c-text-1);
            font-size: 13px; font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
            text-align: right;
        }
        .rs-trigger:hover { border-color: rgba(14,165,164,.5); }
        .rs-trigger:focus { outline: none; border-color: #0ea5a4; box-shadow: 0 0 0 3px rgba(14,165,164,.15); }
        .rs-trigger .rs-trigger-icon {
            flex-shrink: 0; transition: transform .2s ease;
            color: var(--c-text-3); font-size: 18px;
        }
        .rs-trigger[data-open="true"] .rs-trigger-icon { transform: rotate(180deg); }

        .rs-panel {
            position: absolute; top: calc(100% + 6px); right: 0; left: 0;
            z-index: 300;
            background: var(--c-card);
            border: 1px solid var(--c-border-h);
            border-radius: 12px;
            overflow: hidden;
            max-height: 224px; overflow-y: auto;
        }
        /* Elevation shadow — strong in dark, softer in light */
        .dark  .rs-panel { box-shadow: 0 16px 48px rgba(0,0,0,.55), 0 4px 12px rgba(0,0,0,.3); }
        html:not(.dark) .rs-panel { box-shadow: 0 8px 32px rgba(15,23,42,.14), 0 2px 8px rgba(15,23,42,.08); }

        .rs-opt {
            display: block; width: 100%; text-align: right;
            padding: 9px 14px; font-size: 13px;
            color: var(--c-text-1); cursor: pointer;
            background: transparent; border: none;
            font-family: 'Tajawal', sans-serif;
            transition: background .1s;
        }
        .rs-opt:hover    { background: var(--c-card-m); }
        .rs-opt.selected { background: rgba(14,165,164,.12); color: #0ea5a4; font-weight: 700; }

        /* ── Table headers — global adaptive style ──────────────────────
           All theads get the card-m surface, brand text, teal accent.
           !important beats the hardcoded bg-[#050F1E] / bg-[#0B1F3A]
           classes in individual pages without touching each file.    ── */
        thead,
        thead tr {
            background-color: var(--c-card-m) !important;
        }
        thead {
            border-bottom: 2px solid rgba(14,165,164,0.22) !important;
        }
        thead th {
            color: var(--c-text-2);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        /* Light mode: brand dark-navy for strong, clean header contrast */
        html:not(.dark) thead th { color: #0B1F3A; }

        /* ── Status badges — adaptive dark / light ───────────────────
           A dot indicator + pill. Each status has its own vivid
           colour that looks great on both backgrounds.           ── */
        .rs-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .rs-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Active — emerald */
        .rs-badge-active {
            background: rgba(16,185,129,0.14);
            color: #34d399;
            border-color: rgba(16,185,129,0.22);
        }
        .rs-badge-active::before {
            background: #10b981;
            box-shadow: 0 0 5px rgba(16,185,129,0.7);
        }
        html:not(.dark) .rs-badge-active {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }
        html:not(.dark) .rs-badge-active::before {
            background: #16a34a;
            box-shadow: none;
        }

        /* On Leave — amber */
        .rs-badge-on_leave {
            background: rgba(245,158,11,0.14);
            color: #fbbf24;
            border-color: rgba(245,158,11,0.22);
        }
        .rs-badge-on_leave::before {
            background: #f59e0b;
            box-shadow: 0 0 5px rgba(245,158,11,0.6);
        }
        html:not(.dark) .rs-badge-on_leave {
            background: #fef9c3;
            color: #854d0e;
            border-color: #fde047;
        }
        html:not(.dark) .rs-badge-on_leave::before {
            background: #d97706;
            box-shadow: none;
        }

        /* Terminated — rose */
        .rs-badge-terminated {
            background: rgba(239,68,68,0.12);
            color: #f87171;
            border-color: rgba(239,68,68,0.2);
        }
        .rs-badge-terminated::before {
            background: #ef4444;
            box-shadow: 0 0 5px rgba(239,68,68,0.5);
        }
        html:not(.dark) .rs-badge-terminated {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }
        html:not(.dark) .rs-badge-terminated::before {
            background: #dc2626;
            box-shadow: none;
        }

        /* Inactive / unknown */
        .rs-badge-inactive {
            background: rgba(148,163,184,0.12);
            color: #94a3b8;
            border-color: rgba(148,163,184,0.2);
        }
        .rs-badge-inactive::before { background: #64748b; }
        html:not(.dark) .rs-badge-inactive {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }

        /* ── Cancel / secondary button ──────────────────────────── */
        .rs-btn-cancel {
            background: var(--c-card-h);
            color: var(--c-text-2);
            border: 1px solid var(--c-border-h);
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
        }
        .rs-btn-cancel:hover {
            background: var(--c-card-xs);
            color: var(--c-text-1);
            border-color: var(--c-border-h);
        }

        /* ── Pagination button ───────────────────────────────────── */
        .rs-page-btn {
            padding: 5px 14px;
            border-radius: 8px;
            border: 1px solid rgba(14,165,164,0.35);
            color: #0ea5a4;
            font-size: 13px;
            font-weight: 600;
            background: transparent;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
        }
        .rs-page-btn:hover:not(:disabled) {
            background: #0ea5a4;
            color: #fff;
            border-color: #0ea5a4;
        }
        .rs-page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        /* ── Time picker: 3 custom Alpine dropdowns (HH | MM | AM/PM) + "now"
           button. Native <select> dropped because browsers won't let CSS
           reach the <option> list — these are fully styled custom buttons. */
        .rs-time-trigger {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
            background: var(--c-card-xs);
            border: 1px solid var(--c-border-h);
            border-radius: 8px;
            padding: 5px 6px 5px 10px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 13px;
            color: var(--c-text-1);
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, background .15s;
            min-width: 56px;
        }
        .rs-time-trigger:hover {
            border-color: rgba(14,165,164,.5);
            background: var(--c-card-l);
        }
        .rs-time-trigger[data-open="true"] {
            border-color: #0ea5a4;
            box-shadow: 0 0 0 3px rgba(14,165,164,.15);
            background: var(--c-card);
        }
        .rs-time-trigger .rs-time-value {
            flex: 1;
            text-align: center;
        }
        .rs-time-chevron {
            font-size: 16px !important;
            color: var(--c-text-3);
            transition: transform .15s;
            flex-shrink: 0;
        }
        .rs-time-trigger[data-open="true"] .rs-time-chevron {
            transform: rotate(180deg);
            color: #0ea5a4;
        }
        .rs-time-colon {
            color: var(--c-text-2);
            font-weight: 700;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 14px;
            user-select: none;
        }

        .rs-time-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            left: 0;
            z-index: 300;
            background: var(--c-card);
            border: 1px solid var(--c-border-h);
            border-radius: 10px;
            padding: 4px;
            max-height: 220px;
            overflow-y: auto;
            min-width: 60px;
        }
        .dark .rs-time-dropdown {
            box-shadow: 0 16px 48px rgba(0,0,0,.55), 0 4px 12px rgba(0,0,0,.3);
        }
        html:not(.dark) .rs-time-dropdown {
            box-shadow: 0 8px 32px rgba(15,23,42,.14), 0 2px 8px rgba(15,23,42,.08);
        }
        .rs-time-dropdown::-webkit-scrollbar { width: 4px; }
        .rs-time-dropdown::-webkit-scrollbar-thumb {
            background: var(--c-border-h);
            border-radius: 4px;
        }
        .rs-time-dropdown::-webkit-scrollbar-thumb:hover { background: #0ea5a4; }

        .rs-time-option {
            display: block;
            width: 100%;
            padding: 6px 8px;
            text-align: center;
            background: transparent;
            border: none;
            border-radius: 6px;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 13px;
            color: var(--c-text-1);
            cursor: pointer;
            transition: background .1s, color .1s;
        }
        .rs-time-option:hover { background: var(--c-card-m); }
        .rs-time-option.selected {
            background: rgba(14,165,164,.12);
            color: #0ea5a4;
            font-weight: 700;
        }

        .rs-time-now {
            background: transparent;
            border: 1px solid var(--c-border-h);
            border-radius: 8px;
            padding: 5px 7px;
            color: var(--c-text-2);
            cursor: pointer;
            transition: color .15s, border-color .15s, background .15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .rs-time-now:hover {
            color: #0ea5a4;
            border-color: #0ea5a4;
            background: rgba(14,165,164,.08);
        }

        /* ── Clean number input (no native spinner arrows) ────────────────
           Used by quantity fields, hour/minute splits, etc. The native
           up/down spinner competes visually with custom suffix labels and
           breaks the editorial design — hide it across browsers. */
        .rs-num-input::-webkit-outer-spin-button,
        .rs-num-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .rs-num-input[type="number"] {
            -moz-appearance: textfield;
            text-align: right;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 14px;
        }

        /* ── Page-level surface convenience classes ──────────────── */
        .bg-page    { background-color: var(--c-bg); }
        .bg-card    { background-color: var(--c-card); }
        .bg-card-m  { background-color: var(--c-card-m); }
        .text-main  { color: var(--c-text-1); }
        .text-sub   { color: var(--c-text-2); }
        .border-rs  { border-color: var(--c-border); }

        /* Adaptive subtle card — replaces rgba(255,255,255,0.02) inline styles */
        .rs-subtle  { background: var(--c-card-l) !important; border-color: var(--c-border) !important; }

        /* Org-chart connector line color */
        :root       { --rs-line: rgba(15,23,42,0.18); }
        .dark       { --rs-line: #1b3557; }

        /* ══════════════════════════════════════════════════════════
           LIGHT-MODE OVERRIDES
           Converts every hardcoded dark Tailwind class to the
           correct light-mode token. Specificity beats the utility
           class (html:not(.dark) + class = [0,1,1] vs [0,1,0]).
           This means we never have to touch individual page files.
        ══════════════════════════════════════════════════════════ */

        /* ── Page / section backgrounds ───────────────────────── */
        html:not(.dark) .bg-\[\#071426\]    { background-color: var(--c-bg)     !important; }
        html:not(.dark) .bg-\[\#0B1F3A\]    { background-color: var(--c-card)   !important; }
        html:not(.dark) .bg-\[\#0b1f3a\]    { background-color: var(--c-card)   !important; }
        html:not(.dark) .bg-\[\#050F1E\]    { background-color: var(--c-card-xs)!important; }
        html:not(.dark) .bg-\[\#050f1e\]    { background-color: var(--c-card-xs)!important; }
        html:not(.dark) .bg-\[\#162030\]    { background-color: var(--c-card-m) !important; }
        html:not(.dark) .bg-\[\#0c1f39\]    { background-color: var(--c-card)   !important; }
        html:not(.dark) .bg-\[\#0b1d35\]    { background-color: var(--c-card)   !important; }
        html:not(.dark) .bg-\[\#0a1b30\]    { background-color: var(--c-card)   !important; }
        html:not(.dark) .bg-\[\#10201a\]    { background-color: var(--c-card)   !important; }
        html:not(.dark) .bg-\[\#1a2010\]    { background-color: var(--c-card)   !important; }

        /* ── Text ──────────────────────────────────────────────── */
        html:not(.dark) .text-\[\#F1F5F9\]  { color: var(--c-text-1) !important; }
        html:not(.dark) .text-\[\#f1f5f9\]  { color: var(--c-text-1) !important; }
        html:not(.dark) .text-\[\#d9e3f8\]  { color: var(--c-text-1) !important; }
        html:not(.dark) .text-\[\#475569\]  { color: var(--c-text-2) !important; }
        html:not(.dark) .text-\[\#4b6280\]  { color: var(--c-text-2) !important; }
        html:not(.dark) .text-slate-200     { color: var(--c-text-1) !important; }

        /* ── Borders — solid dark hex ───────────────────────────── */
        html:not(.dark) .border-\[\#0B1F3A\]{ border-color: var(--c-border-h) !important; }
        html:not(.dark) .border-\[\#0b1f3a\]{ border-color: var(--c-border-h) !important; }
        html:not(.dark) .border-\[\#050F1E\]{ border-color: var(--c-border)   !important; }
        html:not(.dark) .border-\[\#050f1e\]{ border-color: var(--c-border)   !important; }
        html:not(.dark) .border-\[\#1e3a5f\]{ border-color: var(--c-border-h) !important; }
        html:not(.dark) .border-\[\#1b3557\]{ border-color: var(--c-border-h) !important; }

        /* ── Borders — white-transparent (invisible in light) ───── */
        html:not(.dark) .border-white\/5                              { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-white\/\[0\.04\]                      { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-white\/\[0\.05\]                      { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-white\/\[0\.06\]                      { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-white\/10                             { border-color: var(--c-border-h) !important; }
        html:not(.dark) .border-\[rgba\(255\,255\,255\,0\.06\)\]      { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-\[rgba\(255\,255\,255\,0\.05\)\]      { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-\[rgba\(255\,255\,255\,0\.04\)\]      { border-color: var(--c-border)   !important; }
        html:not(.dark) .border-\[rgba\(255\,255\,255\,0\.02\)\]      { border-color: var(--c-border)   !important; }

        /* ── Dividers ───────────────────────────────────────────── */
        html:not(.dark) .divide-\[\#050F1E\] > :not([hidden]) ~ :not([hidden]),
        html:not(.dark) .divide-\[\#050f1e\] > :not([hidden]) ~ :not([hidden]),
        html:not(.dark) .divide-\[\#0B1F3A\] > :not([hidden]) ~ :not([hidden]),
        html:not(.dark) .divide-\[\#0b1f3a\] > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--c-border) !important;
        }

        /* ── Table row hover ────────────────────────────────────── */
        html:not(.dark) .hover\:bg-\[\#071426\]\/50:hover { background-color: var(--c-card-l) !important; }

        /* ── Slate shades that are too dark on light backgrounds ── */
        html:not(.dark) .border-slate-800   { border-color: var(--c-border-h) !important; }

        /* ── Dark opacity backgrounds that look muddy in light mode  */
        html:not(.dark) .bg-red-900\/30     { background-color: #fee2e2 !important; }
        html:not(.dark) .bg-red-900\/50     { background-color: #fecaca !important; }
        html:not(.dark) .border-red-900\/50 { border-color: #fca5a5   !important; }
        html:not(.dark) .hover\:bg-red-900\/50:hover { background-color: #fecaca !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface text-on-surface font-body-main min-h-screen antialiased transition-colors duration-300">
<div x-data="appShell()" x-init="init()" x-cloak>

    @yield('content')

    <!-- ── Floating user widget ──────────────────────────────────── -->
    <div class="fixed bottom-6 left-6 z-[999]" x-data="{ open: false }">

        {{-- Avatar trigger --}}
        <button @click="open = !open"
                class="w-11 h-11 rounded-full flex items-center justify-center text-[14px] font-bold text-[#0ea5a4] border-2 transition-all duration-200 shadow-lg select-none focus:outline-none"
                :class="open
                    ? 'border-[#0ea5a4] shadow-[0_0_20px_rgba(14,165,164,0.4)] bg-[rgba(14,165,164,0.15)]'
                    : 'hover:border-[rgba(14,165,164,0.7)] hover:shadow-[0_0_16px_rgba(14,165,164,0.25)] bg-[rgba(14,165,164,0.08)]'"
                style="border-color: var(--c-avatar-ring)"
                x-text="initials">
        </button>

        {{-- Popup card --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             @click.away="open = false"
             class="rs-widget-card absolute bottom-14 left-0 w-[220px] rounded-2xl shadow-2xl overflow-hidden"
             style="display:none">

            {{-- Brand strip --}}
            <div class="rs-widget-strip px-4 py-3 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-primary-container flex items-center justify-center shrink-0 shadow-[0_0_10px_rgba(14,165,164,.4)]">
                    <span class="material-symbols-outlined text-[#050F1E] text-[15px]"
                          style="font-variation-settings:'FILL' 1">memory</span>
                </div>
                <div class="flex items-baseline">
                    <span class="font-h1 text-[16px] font-[900] text-on-surface">Rushd</span>
                    <span class="font-h1 text-[16px] font-[900] text-primary-container">AI</span>
                </div>
            </div>

            {{-- User info --}}
            <div class="px-4 py-3" style="border-bottom: 1px solid var(--c-widget-border)">
                <p class="text-[13px] font-semibold text-on-surface leading-tight" x-text="user.name || '...'"></p>
                <p class="text-[11px] text-primary-container mt-0.5" x-text="user.employee?.job_title || 'مستخدم'"></p>
            </div>

            {{-- Actions --}}
            <div class="px-2 py-2">
                {{-- Theme toggle --}}
                <button @click="toggleTheme()"
                        class="rs-widget-row flex items-center gap-2.5 px-3 py-2 rounded-xl text-[12px] font-medium transition-colors w-full">
                    <span class="material-symbols-outlined text-[17px]" x-text="isDark ? 'light_mode' : 'dark_mode'"></span>
                    <span x-text="isDark ? 'الوضع الفاتح' : 'الوضع الداكن'"></span>
                </button>

                {{-- Home --}}
                <a href="/"
                   class="rs-widget-row flex items-center gap-2.5 px-3 py-2 rounded-xl text-[12px] font-medium transition-colors w-full">
                    <span class="material-symbols-outlined text-[17px]">home</span>
                    الرئيسية
                </a>

                {{-- Logout --}}
                <button @click="doLogout()"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-[12px] font-medium text-[#dc2626] hover:bg-red-500/[0.08] transition-colors w-full"
                        style="color: var(--c-error)">
                    <span class="material-symbols-outlined text-[17px]">logout</span>
                    تسجيل الخروج
                </button>
            </div>

        </div>
    </div>

</div>

<script>
// ── Global permission helpers ───────────────────────────────────────────
// Read permissions/roles from the auth_user blob in localStorage.
// $can('slug') for one perm; $canAny('a','b') for OR; $hasRole('slug').
// $guard([...slugs]) redirects to / if user has none of the perms.
// ── Time picker helper ──────────────────────────────────────────────────
// Backs the 3-dropdown rs-time picker (HH:MM AM/PM) with a getter/setter
// pair that reads from / writes to the parent's form state in 24-hour
// HH:MM:SS format.
//
// Important: all state (incl. dropdown open flags) lives on this single
// component. We do NOT nest x-data — Alpine v3's nested scope inheritance
// is unreliable for closures, so `x-text="h"` from inside a nested
// `x-data="{open:false}"` would render empty. Single flat scope = no
// shadowing, no surprises.
//
// Exposes hours[], minutes[] arrays so the template can iterate via x-for
// without relying on the `i in N` range syntax (some Alpine versions
// silently render nothing).
window.tp = function(getter, setter){
    return {
        h: '12', m: '00', ap: 'AM',
        openH: false, openM: false, openAp: false,
        syncing: false,
        hours:   Array.from({length: 12}, (_, i) => String(i + 1).padStart(2, '0')),
        minutes: Array.from({length: 60}, (_, i) => String(i).padStart(2, '0')),

        get raw() { return getter(); },

        init() {
            this.sync();
            this.$watch('raw', () => this.sync());
            this.$watch('h',  () => this.commit());
            this.$watch('m',  () => this.commit());
            this.$watch('ap', () => this.commit());
        },

        // Open one dropdown, close the other two — like a tab group.
        toggle(which) {
            const all = ['openH', 'openM', 'openAp'];
            all.forEach(k => { if (k !== which) this[k] = false; });
            this[which] = !this[which];
        },

        closeAll() {
            this.openH = false;
            this.openM = false;
            this.openAp = false;
        },

        sync() {
            this.syncing = true;
            const v = getter() || '';
            if (v) {
                const parts = v.split(':');
                let hh = parseInt(parts[0] || '0', 10);
                this.ap = hh >= 12 ? 'PM' : 'AM';
                hh = hh % 12 || 12;
                this.h = String(hh).padStart(2, '0');
                this.m = String(parseInt(parts[1] || '0', 10)).padStart(2, '0');
            } else {
                this.h = '12'; this.m = '00'; this.ap = 'AM';
            }
            this.$nextTick(() => { this.syncing = false; });
        },

        commit() {
            if (this.syncing) return;
            let hh = parseInt(this.h, 10);
            if (this.ap === 'PM' && hh < 12) hh += 12;
            if (this.ap === 'AM' && hh === 12) hh = 0;
            setter(String(hh).padStart(2, '0') + ':' + this.m + ':00');
        },

        setCurrent() {
            const d = new Date();
            let hh = d.getHours();
            this.ap = hh >= 12 ? 'PM' : 'AM';
            hh = hh % 12 || 12;
            this.h = String(hh).padStart(2, '0');
            this.m = String(d.getMinutes()).padStart(2, '0');
        },
    };
};

// ── Minutes → "X ساعة Y دقيقة" formatter ───────────────────────────────
// Used wherever we have a raw minute value and want to show it in
// human-readable form across the system.
window.fmtMinutes = function(total){
    const m = parseInt(total, 10);
    if (!m || m <= 0) return '0 دقيقة';
    const h = Math.floor(m / 60);
    const r = m % 60;
    if (h === 0) return r + ' دقيقة';
    if (r === 0) return h + ' ساعة';
    return h + ' ساعة و ' + r + ' دقيقة';
};

window.$can = function(perm){
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        return (u.permissions || []).includes(perm);
    } catch(e){ return false; }
};
window.$canAny = function(...perms){
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        const have = u.permissions || [];
        return perms.some(p => have.includes(p));
    } catch(e){ return false; }
};
window.$hasRole = function(slug){
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        return (u.roles || []).some(r => r.slug === slug);
    } catch(e){ return false; }
};
// Async — waits up to ~1s for appShell to hydrate auth_user, then redirects to /
// if the current user has none of the listed permissions. Returns true if it
// redirected (so callers can short-circuit init).
window.$guard = async function(...perms){
    for (let i = 0; i < 30 && !localStorage.getItem('auth_user'); i++) {
        await new Promise(r => setTimeout(r, 50));
    }
    // Fail closed: if auth_user never loaded, treat as unauthenticated.
    if (!localStorage.getItem('auth_user')) {
        localStorage.removeItem('auth_token');
        if (window.location.pathname !== '/login') window.location.href = '/login';
        return true;
    }
    if (!window.$canAny(...perms)) {
        window.location.href = '/';
        return true;
    }
    return false;
};

function appShell() {
    return {
        user: {},
        counts: { leaves: 0, purchases: 0, total: 0 },
        isDark: document.documentElement.classList.contains('dark'),

        get initials() {
            if (!this.user?.name) return '؟';
            return this.user.name.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('');
        },

        toggleTheme() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('rushd-theme', this.isDark ? 'dark' : 'light');
        },

        async init() {
            if (!localStorage.getItem('auth_token')) {
                window.location.href = '/login';
                return;
            }
            const cached = localStorage.getItem('auth_user');
            if (cached) try { this.user = JSON.parse(cached); } catch(e) {}

            try {
                const r = await axios.get('/api/v1/auth/me');
                if (r.data.success) {
                    this.user = r.data.data;
                    localStorage.setItem('auth_user', JSON.stringify(r.data.data));
                }
            } catch(e) {}

            try {
                const r = await axios.get('/api/v1/approvals/pending-count');
                if (r.data.success) this.counts = r.data.data;
            } catch(e) {}
        },

        doLogout() {
            axios.post('/api/v1/auth/logout').catch(() => {});
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            window.location.href = '/login';
        }
    };
}
</script>

@stack('scripts')
</body>
</html>
