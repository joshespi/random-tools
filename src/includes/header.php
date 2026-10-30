<?php
include __DIR__ . '/pages.php';
$current = basename($_SERVER['PHP_SELF']);
$page = $PAGES[$current] ?? null;
$fullTitle = !empty($page['title']) ? $page['title'] . ' · Random Tools' : 'Random Tools';
$metaDesc = $page['description'] ?? 'A small collection of everyday tools: pickers, dice, passphrases and more. Everything runs in your browser.';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#09090b">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title><?= htmlspecialchars($fullTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Random Tools">
    <meta property="og:title" content="<?= htmlspecialchars($fullTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css?v=<?= @filemtime(__DIR__ . '/../assets/app.css') ?: 1 ?>">
    <?= $extraHead ?? '' ?>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }

        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function loadJSON(key, isValid, fallback) {
            try {
                const raw = localStorage.getItem(key);
                if (!raw) return fallback();
                const parsed = JSON.parse(raw);
                return isValid(parsed) ? parsed : fallback();
            } catch { return fallback(); }
        }

        function saveJSON(key, value) {
            try { localStorage.setItem(key, JSON.stringify(value)); } catch {}
        }

        function pushCapped(arr, item, max) {
            arr.push(item);
            if (arr.length > max) arr.shift();
        }

        function renderCappedList(el, items, limit, mapFn, emptyHtml) {
            el.innerHTML = items.length === 0
                ? emptyHtml
                : items.slice(-limit).reverse().map(mapFn).join('');
        }

        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            return new Promise((resolve, reject) => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
                document.body.appendChild(ta);
                ta.select();
                let ok = false;
                try { ok = document.execCommand('copy'); } catch {}
                ta.remove();
                ok ? resolve() : reject(new Error('copy failed'));
            });
        }

        function isTypingTarget(el) {
            return !!el.closest('input, textarea, select, [contenteditable]');
        }

        function downloadBlob(blob, filename) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            setTimeout(() => URL.revokeObjectURL(url), 100);
        }

        // Space triggers the page's main action unless a control has focus.
        function bindActionKey(btnId) {
            document.addEventListener('keydown', e => {
                if (e.key !== ' ' || isTypingTarget(e.target) || e.target.closest('button, a')) return;
                const btn = document.getElementById(btnId);
                if (btn && !btn.disabled) { e.preventDefault(); btn.click(); }
            });
        }

        // Non-blocking replacement for alert(): a short status message at the bottom of the screen.
        function showToast(message, kind) {
            let host = document.getElementById('toastHost');
            if (!host) {
                host = document.createElement('div');
                host.id = 'toastHost';
                host.setAttribute('role', 'status');
                host.setAttribute('aria-live', 'polite');
                host.className = 'fixed bottom-4 inset-x-0 z-[60] flex flex-col items-center gap-2 px-4 pointer-events-none';
                document.body.appendChild(host);
            }
            const t = document.createElement('div');
            t.className = 'pointer-events-auto max-w-sm rounded-lg border px-4 py-2.5 text-sm shadow-lg bg-zinc-900 '
                + (kind === 'error' ? 'border-red-900 text-red-300' : 'border-zinc-700 text-zinc-200');
            t.textContent = message;
            host.appendChild(t);
            setTimeout(() => t.remove(), 3500);
        }

        // Replacement for confirm()/prompt(). Resolves true/false for a confirm,
        // or the entered string / null for a prompt (when opts.input is set).
        function appDialog(opts) {
            return new Promise(resolve => {
                const d = document.createElement('dialog');
                d.className = 'rounded-xl border border-zinc-700 bg-zinc-900 text-zinc-100 p-0 w-[calc(100%-2rem)] max-w-sm backdrop:bg-black/60';
                const form = document.createElement('form');
                form.method = 'dialog';
                form.className = 'p-5 space-y-4';
                const msg = document.createElement('p');
                msg.className = 'text-sm text-zinc-200';
                msg.textContent = opts.message;
                form.appendChild(msg);
                let input = null;
                if (opts.input) {
                    input = document.createElement('input');
                    input.type = 'text';
                    input.value = opts.defaultValue || '';
                    input.setAttribute('aria-label', opts.message);
                    input.className = 'w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600';
                    form.appendChild(input);
                }
                const row = document.createElement('div');
                row.className = 'flex justify-end gap-2';
                const cancel = document.createElement('button');
                cancel.type = 'button';
                cancel.textContent = 'Cancel';
                cancel.className = 'px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors';
                const ok = document.createElement('button');
                ok.type = 'submit';
                ok.textContent = opts.confirmLabel || 'OK';
                ok.className = (opts.danger ? 'bg-red-800 hover:bg-red-700' : 'bg-red-700 hover:bg-red-600')
                    + ' px-3 py-1.5 rounded-lg text-sm text-white font-semibold transition-colors';
                row.append(cancel, ok);
                form.appendChild(row);
                d.appendChild(form);
                document.body.appendChild(d);

                let result = opts.input ? null : false;
                cancel.addEventListener('click', () => d.close());
                form.addEventListener('submit', () => { result = input ? input.value : true; });
                d.addEventListener('click', e => { if (e.target === d) d.close(); });
                d.addEventListener('close', () => { d.remove(); resolve(result); });
                d.showModal();
                (input || ok).focus();
                if (input) input.select();
            });
        }

        const confirmAction = (message, confirmLabel) =>
            appDialog({ message, confirmLabel: confirmLabel || 'Confirm', danger: true });
        const promptText = (message, defaultValue) =>
            appDialog({ message, input: true, defaultValue });

        function setActiveButton(buttons, isActive, activeClasses, inactiveClasses) {
            buttons.forEach(btn => {
                const active = isActive(btn);
                btn.setAttribute('aria-pressed', active);
                activeClasses.forEach(c => btn.classList.toggle(c, active));
                inactiveClasses.forEach(c => btn.classList.toggle(c, !active));
            });
        }
    </script>
</head>
<body class="text-zinc-100 min-h-screen font-sans antialiased">

<nav class="border-b border-zinc-800 sticky top-0 z-50 bg-zinc-950" aria-label="Main">
    <div id="navScroller" class="max-w-5xl mx-auto px-5 flex items-center h-[52px] gap-0 overflow-x-auto" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
        <a href="/index.php" class="select-none mr-6 shrink-0">
            <span class="font-extrabold text-lg tracking-tight text-zinc-100">Random Tools</span>
        </a>
        <?php foreach ($PAGES as $file => $navPage): ?>
            <?php $active = $current === $file; ?>
            <a href="/<?= $file ?>"<?= $active ? ' aria-current="page"' : '' ?>
               class="relative px-1 mr-4 text-sm font-medium transition-colors py-1 shrink-0
                      <?= $active ? 'text-zinc-100' : 'text-zinc-500 hover:text-zinc-300' ?>">
                <?= $navPage['nav'] ?>
                <?php if ($active): ?>
                    <span class="absolute bottom-0 left-0 right-0 h-px bg-red-600 rounded-full"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div id="navFade" class="pointer-events-none absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-zinc-950 to-transparent hidden" aria-hidden="true"></div>
</nav>
<script>
    (function () {
        const sc = document.getElementById('navScroller'), fade = document.getElementById('navFade');
        const upd = () => fade.classList.toggle('hidden', sc.scrollLeft + sc.clientWidth >= sc.scrollWidth - 4);
        sc.addEventListener('scroll', upd, { passive: true });
        window.addEventListener('resize', upd);
        const act = sc.querySelector('[aria-current]');
        if (act) act.scrollIntoView({ inline: 'center', block: 'nearest' });
        upd();
    })();
</script>

<main class="max-w-5xl mx-auto px-5 py-8">
<?php if (!empty($page['title'])): ?>
    <h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight"><?= htmlspecialchars($page['title']) ?></h1>
    <p class="text-zinc-500 mb-7 text-sm"><?= htmlspecialchars($page['subtitle'] ?? '') ?></p>
<?php endif; ?>
