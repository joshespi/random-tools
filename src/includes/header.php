<?php
include __DIR__ . '/pages.php';
$current = basename($_SERVER['PHP_SELF']);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#09090b">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <title><?= htmlspecialchars($pageTitle ?? 'Tools') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    sans: ['Inter', 'system-ui', 'sans-serif'],
                    mono: ['"JetBrains Mono"', 'monospace'],
                },
                extend: {
                    colors: {
                        zinc: {
                            925: '#111113',
                            950: '#09090b',
                        }
                    },
                }
            }
        }
    </script>
    <style>
        body { background-color: #09090b; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #52525b; }
        input[type=range] { -webkit-appearance: none; appearance: none; height: 4px; border-radius: 2px; background: #27272a; outline: none; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: #dc2626; cursor: pointer; border: 2px solid #09090b; }
        input[type=range]::-moz-range-thumb { width: 14px; height: 14px; border-radius: 50%; background: #dc2626; cursor: pointer; border: 2px solid #09090b; }
    </style>
    <script>
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
            localStorage.setItem(key, JSON.stringify(value));
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
        <?php foreach ($PAGES as $file => $page): ?>
            <?php $active = $current === $file; ?>
            <a href="/<?= $file ?>"
               class="relative px-1 mr-4 text-sm font-medium transition-colors py-1 shrink-0
                      <?= $active ? 'text-zinc-100' : 'text-zinc-500 hover:text-zinc-300' ?>">
                <?= $page['nav'] ?>
                <?php if ($active): ?>
                    <span class="absolute bottom-0 left-0 right-0 h-px bg-red-600 rounded-full"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-5 py-8">
