<?php
$pageTitle = 'Picker';
include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Weighted Picker</h1>
<p class="text-zinc-500 mb-7 text-sm">Higher weight = picked more often. Options show their percentage chance.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Left: option management -->
    <div class="lg:col-span-3 space-y-3">

        <!-- Set selector -->
        <div class="rounded-xl p-4 border border-zinc-800" style="background:#111113;">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Set</span>
                <select id="setSelect"
                        class="flex-1 min-w-0 bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600">
                </select>
                <button onclick="promptNewSet()"
                        class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors">
                    New
                </button>
                <button onclick="renameCurrentSet()"
                        class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors">
                    Rename
                </button>
                <button onclick="deleteCurrentSet()"
                        class="px-3 py-1.5 bg-transparent hover:bg-red-950/40 border border-zinc-800 hover:border-red-900 rounded-lg text-sm text-red-700 hover:text-red-500 transition-colors">
                    Delete
                </button>
            </div>
        </div>

        <!-- Options list -->
        <div class="rounded-xl border border-zinc-800 overflow-hidden" style="background:#111113;">
            <div class="flex items-center gap-2 px-4 py-2.5 border-b border-zinc-800 text-xs text-zinc-600 font-medium uppercase tracking-widest">
                <span class="flex-1">Option</span>
                <span class="w-20 text-center">Weight</span>
                <span class="w-12 text-right">Chance</span>
                <span class="w-8"></span>
            </div>
            <div id="optionList" class="divide-y divide-zinc-800/60"></div>
            <!-- Add row -->
            <div class="flex items-center gap-2 px-4 py-3 border-t border-zinc-800">
                <input id="newName" type="text" placeholder="Add an option..."
                       class="flex-1 bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-1.5 text-sm text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-1 focus:ring-red-600">
                <input id="newWeight" type="number" min="1" value="1"
                       class="w-20 bg-zinc-800 border border-zinc-700 rounded-lg px-2 py-1.5 text-sm text-zinc-100 text-center focus:outline-none focus:ring-1 focus:ring-red-600">
                <button onclick="addOption()"
                        class="w-8 h-8 flex items-center justify-center bg-red-700 hover:bg-red-600 rounded-lg text-white text-xl font-bold leading-none transition-colors">
                    +
                </button>
            </div>
        </div>

        <!-- Import / Export -->
        <div class="flex flex-wrap gap-2">
            <button onclick="exportSet()"
                    class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-400 transition-colors">
                Export JSON
            </button>
            <label class="cursor-pointer px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-400 transition-colors">
                Import JSON
                <input type="file" accept=".json,application/json" class="hidden" onchange="importSet(event)">
            </label>
            <button onclick="clearHistory()"
                    class="px-3 py-1.5 bg-transparent border border-zinc-800 rounded-lg text-xs text-zinc-600 hover:text-zinc-400 transition-colors">
                Clear history
            </button>
        </div>
    </div>

    <!-- Right: pick + result -->
    <div class="lg:col-span-2 space-y-3">

        <div class="rounded-xl p-5 border border-zinc-800 text-center space-y-5" style="background:#111113;">
            <button id="pickBtn" onclick="doPick()"
                    class="w-full py-4 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-xl tracking-tight transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100">
                Pick
            </button>
            <div id="result" class="min-h-[72px] flex items-center justify-center">
                <span class="text-zinc-600 text-sm">Press Pick to choose</span>
            </div>
            <div id="weightNote" class="text-xs text-zinc-600 hidden"></div>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden" style="background:#111113;">
            <div class="px-4 py-2.5 border-b border-zinc-800 text-xs text-zinc-600 font-medium uppercase tracking-widest">
                Recent picks
            </div>
            <div id="historyList" class="divide-y divide-zinc-800/60 max-h-64 overflow-y-auto">
                <div class="px-4 py-3 text-zinc-600 text-sm">No picks yet</div>
            </div>
        </div>
    </div>
</div>

<script>
const STORAGE_KEY = 'randomizer_picker_v1';

function defaultState() {
    return {
        sets: {
            'Lunch': [
                { name: "Freddy's",      weight: 1 },
                { name: 'JJs',           weight: 1 },
                { name: 'Mosidas',       weight: 1 },
                { name: 'Thai',          weight: 1 },
                { name: 'Great Steak',   weight: 1 },
                { name: 'Mcdonalds',     weight: 1 },
                { name: 'Taco Bell',     weight: 1 },
                { name: 'KFC',           weight: 1 },
                { name: 'Arbys',         weight: 1 },
                { name: 'Cafe Rio',      weight: 1 },
                { name: 'Little Ceasers', weight: 1 },
                { name: 'Betos',         weight: 1 },
            ]
        },
        current: 'Lunch',
        history: []
    };
}

function loadState() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : defaultState();
    } catch { return defaultState(); }
}

function saveState() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
}

let state = loadState();

function weightedPick(options) {
    const total = options.reduce((s, o) => s + Number(o.weight), 0);
    if (total <= 0 || options.length === 0) return null;
    let r = Math.random() * total;
    for (const o of options) {
        r -= Number(o.weight);
        if (r <= 0) return o.name;
    }
    return options[options.length - 1].name;
}

function renderSetSelect() {
    const sel = document.getElementById('setSelect');
    sel.innerHTML = '';
    Object.keys(state.sets).forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        if (name === state.current) opt.selected = true;
        sel.appendChild(opt);
    });
}

function renderOptions() {
    const list = document.getElementById('optionList');
    const options = state.sets[state.current] || [];

    if (options.length === 0) {
        list.innerHTML = '<div class="px-4 py-4 text-zinc-600 text-sm">No options yet — add one below.</div>';
        return;
    }

    const total = options.reduce((s, o) => s + Number(o.weight), 0);

    list.innerHTML = options.map((opt, i) => {
        const pct = total > 0 ? Math.round((Number(opt.weight) / total) * 100) : 0;
        return `
        <div class="flex items-center gap-2 px-4 py-2.5 hover:bg-zinc-800/30 transition-colors">
            <input type="text"
                   value="${escHtml(opt.name)}"
                   onchange="updateOption(${i}, 'name', this.value)"
                   class="flex-1 bg-transparent border border-transparent hover:border-zinc-700 focus:border-red-700 rounded px-2 py-1 text-sm text-zinc-200 focus:outline-none focus:bg-zinc-800">
            <input type="number" min="1"
                   value="${Number(opt.weight)}"
                   onchange="updateOption(${i}, 'weight', this.value)"
                   class="w-16 bg-zinc-800 border border-zinc-700 rounded px-2 py-1 text-sm text-zinc-200 text-center focus:outline-none focus:ring-1 focus:ring-red-600">
            <span class="w-10 text-right text-xs text-zinc-600 font-mono">${pct}%</span>
            <button onclick="removeOption(${i})"
                    class="w-7 h-7 flex items-center justify-center text-zinc-700 hover:text-red-500 hover:bg-red-950/30 rounded transition-colors text-sm">
                &#x2715;
            </button>
        </div>`;
    }).join('');
}

function renderHistory() {
    const list = document.getElementById('historyList');
    const history = state.history;
    if (history.length === 0) {
        list.innerHTML = '<div class="px-4 py-3 text-zinc-600 text-sm">No picks yet</div>';
        return;
    }
    list.innerHTML = history.slice(-30).reverse().map(h => `
        <div class="flex items-center gap-2 px-4 py-2.5">
            <span class="flex-1 text-sm text-zinc-300">${escHtml(h.pick)}</span>
            <span class="text-xs text-zinc-600">${escHtml(h.set)}</span>
        </div>
    `).join('');
}

function render() {
    renderSetSelect();
    renderOptions();
    renderHistory();
}

function addOption() {
    const nameEl   = document.getElementById('newName');
    const weightEl = document.getElementById('newWeight');
    const name   = nameEl.value.trim();
    const weight = Math.max(1, parseInt(weightEl.value, 10) || 1);
    if (!name) { nameEl.focus(); return; }
    state.sets[state.current].push({ name, weight });
    saveState();
    nameEl.value = '';
    weightEl.value = '1';
    nameEl.focus();
    renderOptions();
}

document.getElementById('newName').addEventListener('keydown', e => {
    if (e.key === 'Enter') addOption();
});

function removeOption(i) {
    state.sets[state.current].splice(i, 1);
    saveState();
    renderOptions();
}

function updateOption(i, field, value) {
    const opt = state.sets[state.current][i];
    if (field === 'weight') {
        opt.weight = Math.max(1, parseInt(value, 10) || 1);
    } else {
        opt.name = value.trim();
    }
    saveState();
    renderOptions();
}

document.getElementById('setSelect').addEventListener('change', function () {
    state.current = this.value;
    saveState();
    render();
});

function promptNewSet() {
    const name = prompt('New set name:');
    if (!name || !name.trim()) return;
    const trimmed = name.trim();
    if (state.sets[trimmed]) { alert('A set with that name already exists.'); return; }
    state.sets[trimmed] = [];
    state.current = trimmed;
    saveState();
    render();
}

function renameCurrentSet() {
    const name = prompt('Rename set to:', state.current);
    if (!name || !name.trim() || name.trim() === state.current) return;
    const trimmed = name.trim();
    if (state.sets[trimmed]) { alert('A set with that name already exists.'); return; }
    state.sets[trimmed] = state.sets[state.current];
    delete state.sets[state.current];
    state.current = trimmed;
    saveState();
    render();
}

function deleteCurrentSet() {
    const keys = Object.keys(state.sets);
    if (keys.length <= 1) { alert('Cannot delete the last set.'); return; }
    if (!confirm(`Delete set "${state.current}"?`)) return;
    const deleted = state.current;
    delete state.sets[deleted];
    state.current = keys.find(k => k !== deleted);
    saveState();
    render();
}

function doPick() {
    const options = state.sets[state.current] || [];
    const pick = weightedPick(options);
    const resultEl = document.getElementById('result');
    const noteEl   = document.getElementById('weightNote');

    if (!pick) {
        resultEl.innerHTML = '<span class="text-yellow-600 text-sm">Add at least one option first.</span>';
        noteEl.classList.add('hidden');
        return;
    }

    noteEl.classList.add('hidden');
    const btn = document.getElementById('pickBtn');
    btn.disabled = true;

    const ITEM_H   = 60;
    const REEL_LEN = 22;
    const reel = [];
    for (let i = 0; i < REEL_LEN; i++) {
        reel.push(options[Math.floor(Math.random() * options.length)].name);
    }
    reel.push(pick);

    const itemsHtml = reel.map((n, i) => {
        const isWinner = i === reel.length - 1;
        const cls = isWinner
            ? 'text-3xl font-extrabold text-zinc-100 tracking-tight'
            : 'text-2xl font-semibold text-zinc-600';
        return `<div style="height:${ITEM_H}px;" class="flex items-center justify-center ${cls}">${escHtml(n)}</div>`;
    }).join('');

    resultEl.innerHTML = `
        <div class="overflow-hidden w-full relative" style="height:${ITEM_H}px;">
            <div id="pickerReel" style="transform: translateY(0); will-change: transform;">
                ${itemsHtml}
            </div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-3" style="background:linear-gradient(to bottom,#111113,transparent);"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-3" style="background:linear-gradient(to top,#111113,transparent);"></div>
        </div>`;

    requestAnimationFrame(() => {
        const reelEl = document.getElementById('pickerReel');
        reelEl.style.transition = 'transform 1.5s cubic-bezier(0.18, 0.9, 0.3, 1)';
        reelEl.style.transform  = `translateY(-${(reel.length - 1) * ITEM_H}px)`;
    });

    setTimeout(() => {
        const total = options.reduce((s, o) => s + Number(o.weight), 0);
        const chosen = options.find(o => o.name === pick);
        if (chosen && total > 0) {
            const pct = Math.round((Number(chosen.weight) / total) * 100);
            noteEl.textContent = `${pct}% chance`;
            noteEl.classList.remove('hidden');
        }

        state.history.push({ set: state.current, pick, ts: Date.now() });
        if (state.history.length > 200) state.history.shift();
        saveState();
        renderHistory();

        btn.disabled = false;
    }, 1550);
}

function clearHistory() {
    if (!confirm('Clear pick history?')) return;
    state.history = [];
    saveState();
    renderHistory();
}

function exportSet() {
    const data = { name: state.current, options: state.sets[state.current] };
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = state.current.replace(/\s+/g, '_') + '_options.json';
    a.click();
    setTimeout(() => URL.revokeObjectURL(url), 100);
}

function importSet(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        try {
            const data = JSON.parse(e.target.result);
            if (!Array.isArray(data.options)) throw new Error();
            let finalName = (data.name || 'Imported').trim();
            if (state.sets[finalName]) {
                const choice = prompt(`Set "${finalName}" already exists. New name:`, finalName + ' 2');
                if (!choice || !choice.trim()) return;
                finalName = choice.trim();
            }
            state.sets[finalName] = data.options.map(o => ({
                name:   String(o.name || '').trim(),
                weight: Math.max(1, parseInt(o.weight, 10) || 1),
            })).filter(o => o.name);
            state.current = finalName;
            saveState();
            render();
        } catch {
            alert('Could not import: invalid or unrecognised JSON format.');
        }
    };
    reader.readAsText(file);
    event.target.value = '';
}

render();
</script>

<?php include 'includes/footer.php'; ?>
