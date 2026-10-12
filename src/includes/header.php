<?php
include __DIR__ . '/pages.php';
$current = basename($_SERVER['PHP_SELF']);
$page = $PAGES[$current] ?? null;
$fullTitle = !empty($page['title']) ? $page['title'] . ' · Random Tools' : 'Random Tools';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#09090b">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title><?= htmlspecialchars($fullTitle) ?></title>
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

        function setActiveButton(buttons, isActive, activeClasses, inactiveClasses) {
            buttons.forEach(btn => {
                const active = isActive(btn);
                activeClasses.forEach(c => btn.classList.toggle(c, active));
                inactiveClasses.forEach(c => btn.classList.toggle(c, !active));
            });
        }
    </script>
</head>
<body class="text-zinc-100 min-h-screen font-sans antialiased">

<nav class="border-b border-zinc-800 sticky top-0 z-50 bg-zinc-950">
    <div class="max-w-5xl mx-auto px-5 flex items-center h-[52px] gap-0 overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <a href="/index.php" class="select-none mr-6 shrink-0">
            <span class="font-extrabold text-lg tracking-tight text-zinc-100">Random Tools</span>
        </a>
        <?php foreach ($PAGES as $file => $navPage): ?>
            <?php $active = $current === $file; ?>
            <a href="/<?= $file ?>"
               class="relative px-1 mr-4 text-sm font-medium transition-colors py-1 shrink-0
                      <?= $active ? 'text-zinc-100' : 'text-zinc-500 hover:text-zinc-300' ?>">
                <?= $navPage['nav'] ?>
                <?php if ($active): ?>
                    <span class="absolute bottom-0 left-0 right-0 h-px bg-red-600 rounded-full"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-5 py-8">
<?php if (!empty($page['title'])): ?>
    <h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight"><?= htmlspecialchars($page['title']) ?></h1>
    <p class="text-zinc-500 mb-7 text-sm"><?= htmlspecialchars($page['subtitle'] ?? '') ?></p>
<?php endif; ?>
