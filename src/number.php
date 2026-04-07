<?php
$pageTitle = 'Numbers';
include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Random Numbers</h1>
<p class="text-zinc-500 mb-7 text-sm">Generate one or many random integers in a range. Optional no-duplicate and sort modes.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Controls -->
    <div class="lg:col-span-2 space-y-3">

        <div class="rounded-xl p-5 border border-zinc-800 space-y-5" style="background:#111113;">
            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Settings</div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-zinc-400 mb-1.5">Min</label>
                    <input type="number" id="minVal" value="1"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 font-mono focus:outline-none focus:ring-1 focus:ring-red-600">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-1.5">Max</label>
                    <input type="number" id="maxVal" value="100"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 font-mono focus:outline-none focus:ring-1 focus:ring-red-600">
                </div>
            </div>

            <div>
                <div class="flex justify-between mb-2">
                    <label class="text-sm text-zinc-400">How many</label>
                    <span id="countLabel" class="text-sm font-mono text-red-500">1</span>
                </div>
                <input type="range" id="genCount" min="1" max="100" value="1"
                       oninput="document.getElementById('countLabel').textContent = this.value"
                       class="w-full">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="nodupes" class="accent-red-600">
                    <span class="text-sm text-zinc-300">No duplicates</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="sorted" class="accent-red-600">
                    <span class="text-sm text-zinc-300">Sort ascending</span>
                </label>
            </div>
        </div>

        <button onclick="generate()"
                class="w-full py-4 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-xl tracking-tight transition-all">
            Generate
        </button>

        <!-- Presets -->
        <div class="rounded-xl p-4 border border-zinc-800" style="background:#111113;">
            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest mb-3">Quick presets</div>
            <div class="flex flex-wrap gap-2">
                <button onclick="preset(1,6,1)"       class="px-2.5 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded text-xs text-zinc-400 font-mono transition-colors">1–6</button>
                <button onclick="preset(1,10,1)"      class="px-2.5 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded text-xs text-zinc-400 font-mono transition-colors">1–10</button>
                <button onclick="preset(1,100,1)"     class="px-2.5 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded text-xs text-zinc-400 font-mono transition-colors">1–100</button>
                <button onclick="preset(1,49,6,true)" class="px-2.5 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded text-xs text-zinc-400 font-mono transition-colors">Lottery</button>
                <button onclick="preset(0,255,3)"     class="px-2.5 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded text-xs text-zinc-400 font-mono transition-colors">RGB</button>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="lg:col-span-3 space-y-3">
        <div id="resultPanel"
             class="rounded-xl border border-zinc-800 p-6 min-h-[200px] flex items-center justify-center" style="background:#111113;">
            <span class="text-zinc-600 text-sm">Set range and click Generate</span>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden" style="background:#111113;">
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-zinc-800">
                <span class="text-xs text-zinc-600 font-medium uppercase tracking-widest">History</span>
                <button onclick="clearHistory()" class="text-xs text-zinc-700 hover:text-zinc-400 transition-colors">Clear</button>
            </div>
            <div id="historyList" class="divide-y divide-zinc-800/60 max-h-64 overflow-y-auto font-mono">
                <div class="px-4 py-3 text-zinc-600 text-sm">No results yet</div>
            </div>
        </div>
    </div>
</div>

<script>
let history = JSON.parse(localStorage.getItem('randomizer_numbers') || '[]');

function randInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function generate() {
    const min     = parseInt(document.getElementById('minVal').value, 10);
    const max     = parseInt(document.getElementById('maxVal').value, 10);
    const count   = parseInt(document.getElementById('genCount').value, 10);
    const nodupes = document.getElementById('nodupes').checked;
    const sorted  = document.getElementById('sorted').checked;

    if (isNaN(min) || isNaN(max)) { alert('Enter valid min and max values.'); return; }
    if (min > max) { alert('Min must be ≤ max.'); return; }

    const range = max - min + 1;
    if (nodupes && count > range) {
        alert(`Can't generate ${count} unique numbers in ${min}–${max} (only ${range} possible).`);
        return;
    }

    let results = [];
    if (nodupes) {
        const pool = Array.from({ length: range }, (_, i) => min + i);
        for (let i = 0; i < count; i++) {
            const j = i + Math.floor(Math.random() * (pool.length - i));
            [pool[i], pool[j]] = [pool[j], pool[i]];
        }
        results = pool.slice(0, count);
    } else {
        for (let i = 0; i < count; i++) results.push(randInt(min, max));
    }

    if (sorted) results.sort((a, b) => a - b);

    const record = { min, max, count, nodupes, sorted, results, ts: Date.now() };
    history.unshift(record);
    if (history.length > 50) history.pop();
    localStorage.setItem('randomizer_numbers', JSON.stringify(history));

    renderResult(record);
    renderHistory();
}

function renderResult(record) {
    const panel = document.getElementById('resultPanel');
    const { results, min, max } = record;

    if (results.length === 1) {
        panel.innerHTML = `
            <div class="text-center">
                <div class="text-6xl font-black text-zinc-100 font-mono tracking-tight mb-2">${results[0]}</div>
                <div class="text-zinc-600 text-xs font-mono">${min} – ${max}</div>
            </div>`;
        return;
    }

    const nums = results.map(n =>
        `<span class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 font-mono text-zinc-200 text-sm">${n}</span>`
    ).join('');

    const sum = results.reduce((s, v) => s + v, 0);
    const avg = (sum / results.length).toFixed(2);

    panel.innerHTML = `
        <div class="w-full space-y-4">
            <div class="flex flex-wrap gap-1.5">${nums}</div>
            <div class="text-xs text-zinc-600 font-mono flex gap-4 flex-wrap">
                <span>n=${results.length}</span>
                <span>sum=${sum}</span>
                <span>avg=${avg}</span>
                <span>min=${Math.min(...results)}</span>
                <span>max=${Math.max(...results)}</span>
            </div>
            <button onclick="copyNumbers()" class="px-3 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-500 hover:text-zinc-300 transition-colors font-mono">
                copy csv
            </button>
        </div>`;

    window._lastResults = results;
}

function copyNumbers() {
    if (!window._lastResults) return;
    navigator.clipboard.writeText(window._lastResults.join(', ')).then(() => {
        const btn = document.querySelector('[onclick="copyNumbers()"]');
        if (btn) { btn.textContent = 'copied'; setTimeout(() => btn.textContent = 'copy csv', 1500); }
    });
}

function renderHistory() {
    const list = document.getElementById('historyList');
    if (history.length === 0) {
        list.innerHTML = '<div class="px-4 py-3 text-zinc-600 text-sm">No results yet</div>';
        return;
    }
    list.innerHTML = history.slice(0, 30).map(h => `
        <div class="flex items-center gap-3 px-4 py-2 text-sm">
            <span class="text-xs text-zinc-700 w-20 shrink-0">${h.min}–${h.max}</span>
            <span class="flex-1 text-zinc-500 text-xs truncate">${h.results.join(', ')}</span>
            <span class="text-xs text-zinc-600 shrink-0">×${h.results.length}</span>
        </div>`).join('');
}

function clearHistory() {
    history = [];
    localStorage.removeItem('randomizer_numbers');
    document.getElementById('resultPanel').innerHTML = '<span class="text-zinc-600 text-sm">Set range and click Generate</span>';
    renderHistory();
}

function preset(min, max, count, nodupes = false) {
    document.getElementById('minVal').value   = min;
    document.getElementById('maxVal').value   = max;
    document.getElementById('genCount').value = count;
    document.getElementById('countLabel').textContent = count;
    document.getElementById('nodupes').checked = nodupes;
    document.getElementById('sorted').checked  = nodupes;
    generate();
}

renderHistory();
</script>

<?php include 'includes/footer.php'; ?>
