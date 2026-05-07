<!DOCTYPE html>
<html class="dark" lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'تسجيل الدخول') — RushdAI HR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Editorial display serif. Latin: Cormorant Garamond. Arabic: Amiri Naskh
         (Markazi Text retained as fallback). Body sans: Inter / Tajawal.
         Mirrors layouts/app.blade.php. --}}
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Amiri:wght@400;700&family=Markazi+Text:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary-container": "#0ea5a4",
                    "primary": "#5ed9d7",
                    "primary-fixed-dim": "#5ed9d7",
                    "surface": "#091423",
                    "surface-container-lowest": "#050e1d",
                    "surface-container": "#162030",
                    "on-surface": "#d9e3f8",
                    "on-surface-variant": "#bcc9c8",
                    "on-background": "#d9e3f8",
                    "on-primary-container": "#003333",
                    "outline": "#869393",
                    "outline-variant": "#3d4949",
                    "error": "#ffb4ab",
                },
                /* Editorial radius hierarchy — mirrors layouts/app.blade.php */
                borderRadius: {
                    "none":    "0",
                    "xs":      "4px",
                    "sm":      "6px",
                    "DEFAULT": "8px",
                    "md":      "8px",
                    "lg":      "12px",
                    "xl":      "16px",
                    "2xl":     "20px",
                    "3xl":     "24px",
                    "pill":    "9999px",
                    "full":    "9999px",
                },
                fontFamily: {
                    "h1":             ['"Cormorant Garamond"', '"Amiri"', '"Markazi Text"', '"EB Garamond"', "Georgia", "serif"],
                    "h2":             ['"Cormorant Garamond"', '"Amiri"', '"Markazi Text"', '"EB Garamond"', "Georgia", "serif"],
                    "display":        ['"Cormorant Garamond"', '"Amiri"', '"Markazi Text"', '"EB Garamond"', "Georgia", "serif"],
                    "body-main":      ["Inter", "Tajawal", "-apple-system", "BlinkMacSystemFont", '"Segoe UI"', "Roboto", "sans-serif"],
                    "body-secondary": ["Inter", "Tajawal", "-apple-system", "BlinkMacSystemFont", '"Segoe UI"', "Roboto", "sans-serif"],
                    "label-caps":     ["Inter", "Tajawal", "sans-serif"],
                    "mono":           ['"JetBrains Mono"', "ui-monospace", "SFMono-Regular", "Menlo", "monospace"],
                },
                fontSize: {
                    "display-xl":     ["64px", { lineHeight: "1.05", letterSpacing: "-1.5px", fontWeight: "500" }],
                    "display-lg":     ["48px", { lineHeight: "1.1",  letterSpacing: "-1px",   fontWeight: "500" }],
                    "display-md":     ["36px", { lineHeight: "1.15", letterSpacing: "-0.5px", fontWeight: "500" }],
                    "display-sm":     ["28px", { lineHeight: "1.2",  letterSpacing: "-0.3px", fontWeight: "500" }],
                    "h1":             ["40px", { lineHeight: "1.1",  letterSpacing: "-0.8px", fontWeight: "500" }],
                    "h2":             ["28px", { lineHeight: "1.2",  letterSpacing: "-0.3px", fontWeight: "500" }],
                    "title-lg":       ["22px", { lineHeight: "1.3",  fontWeight: "500" }],
                    "title-md":       ["18px", { lineHeight: "1.4",  fontWeight: "500" }],
                    "title-sm":       ["16px", { lineHeight: "1.4",  fontWeight: "500" }],
                    "body-main":      ["16px", { lineHeight: "1.55", fontWeight: "400" }],
                    "body-secondary": ["14px", { lineHeight: "1.55", fontWeight: "400" }],
                    "caption":        ["13px", { lineHeight: "1.4",  fontWeight: "500" }],
                    "caption-up":     ["12px", { lineHeight: "1.4",  letterSpacing: "1.5px", fontWeight: "500" }],
                    "label-caps":     ["11px", { lineHeight: "1",    letterSpacing: "0.05em", fontWeight: "700" }],
                    "code":           ["14px", { lineHeight: "1.6",  fontWeight: "400" }],
                },
                transitionTimingFunction: {
                    "editorial": "cubic-bezier(0.32, 0.72, 0, 1)",
                },
            }
        }
    }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .bg-dot-pattern { background-image: radial-gradient(rgba(14,165,164,0.05) 1px, transparent 1px); background-size: 20px 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }

        /* Editorial typography — mirrors layouts/app.blade.php so the
           login surface matches the rest of the system. */
        body {
            font-family: 'Inter', 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        h1, h2, h3, h4,
        .font-h1, .font-h2, .font-display {
            font-family: 'Cormorant Garamond', 'Amiri', 'Markazi Text', 'EB Garamond', Georgia, serif !important;
            font-weight: 500 !important;
            letter-spacing: -0.02em;
        }
        .font-h1 { letter-spacing: -0.025em; line-height: 1.1; }
        .font-h2 { letter-spacing: -0.015em; line-height: 1.2; }
        html[dir="rtl"] h1,
        html[dir="rtl"] h2,
        html[dir="rtl"] h3,
        html[dir="rtl"] h4,
        html[dir="rtl"] .font-h1,
        html[dir="rtl"] .font-h2,
        html[dir="rtl"] .font-display {
            letter-spacing: 0 !important;
            font-weight: 700 !important;
        }
        h1.font-sans, h2.font-sans, h3.font-sans, h4.font-sans {
            font-family: 'Inter', 'Tajawal', sans-serif !important;
            letter-spacing: 0 !important;
        }
        code, pre, kbd, samp, .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        :root { --ease-editorial: cubic-bezier(0.32, 0.72, 0, 1); }
        button, a, input, select, textarea, [role="button"] {
            transition: background-color 200ms var(--ease-editorial),
                        border-color 200ms var(--ease-editorial),
                        color 200ms var(--ease-editorial),
                        box-shadow 200ms var(--ease-editorial),
                        transform 200ms var(--ease-editorial),
                        opacity 200ms var(--ease-editorial);
        }
        button:active, [role="button"]:active { transform: translateY(0.5px); }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0ms !important;
                transition-duration: 0ms !important;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#071426] text-on-background min-h-screen flex flex-col items-center justify-center relative font-body-main antialiased bg-dot-pattern">
    @yield('content')
    <footer class="absolute bottom-6 w-full flex justify-center pointer-events-none">
        <p class="font-body-secondary text-[11px] text-slate-600">RushdAI Internal System — Authorized Access Only</p>
    </footer>
    @stack('scripts')
</body>
</html>
