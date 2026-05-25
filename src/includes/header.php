<?php
$nav = [
    'index.php'      => 'Home',
    'picker.php'     => 'Picker',
    'passphrase.php' => 'Passphrase',
    'tip.php'        => 'Tip',
];
$current = basename($_SERVER['PHP_SELF']);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#09090b">
    <link rel="manifest" href="/manifest.json">
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
                    animation: {
                        'pop': 'pop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1)',
                    },
                    keyframes: {
                        pop: {
                            '0%':   { transform: 'scale(0.75)', opacity: '0' },
                            '100%': { transform: 'scale(1)',    opacity: '1' },
                        }
                    }
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
    </script>
</head>
<body class="text-zinc-100 min-h-screen font-sans antialiased">

<nav class="border-b border-zinc-800 sticky top-0 z-50" style="background:#09090b;">
    <div class="max-w-5xl mx-auto px-5 flex items-center h-13 gap-0" style="height:52px;">
        <a href="/index.php" class="select-none mr-6">
            <span class="font-extrabold text-lg tracking-tight text-zinc-100">Espi's</span>
        </a>
        <?php foreach ($nav as $file => $label): ?>
            <?php $active = $current === $file; ?>
            <a href="/<?= $file ?>"
               class="relative px-1 mr-4 text-sm font-medium transition-colors py-1
                      <?= $active ? 'text-zinc-100' : 'text-zinc-500 hover:text-zinc-300' ?>">
                <?= $label ?>
                <?php if ($active): ?>
                    <span class="absolute bottom-0 left-0 right-0 h-px bg-red-600 rounded-full"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-5 py-8">
