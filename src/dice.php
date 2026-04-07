<?php
$pageTitle = 'Dice';
include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Dice Roller</h1>
<p class="text-zinc-500 mb-7 text-sm">Roll any die, any number of times. Nat 1s and max rolls are highlighted.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Controls -->
    <div class="lg:col-span-2 space-y-3">

        <div class="rounded-xl p-5 border border-zinc-800 space-y-4" style="background:#111113;">
            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Die type</div>
            <div id="diceGrid" class="grid grid-cols-4 gap-2"></div>
            <div class="flex items-center gap-2 pt-1 border-t border-zinc-800">
                <span class="text-sm text-zinc-500 font-mono">d</span>
                <input type="number" id="customSides" min="2" value="100"
                       class="w-20 bg-zinc-800 border border-zinc-700 rounded-lg px-2 py-1.5 text-sm text-zinc-100 text-center focus:outline-none focus:ring-1 focus:ring-red-600 font-mono">
                <button onclick="selectCustom()"
                        class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors">
                    Custom
                </button>
            </div>
        </div>

        <div class="rounded-xl p-5 border border-zinc-800" style="background:#111113;">
            <div class="flex justify-between mb-2">
                <label class="text-sm text-zinc-400">Number of dice</label>
                <span id="countLabel" class="text-sm font-mono text-red-500">1</span>
            </div>
            <input type="range" id="diceCount" min="1" max="20" value="1"
                   oninput="document.getElementById('countLabel').textContent = this.value"
                   class="w-full">
        </div>

        <div class="rounded-xl p-5 border border-zinc-800" style="background:#111113;">
            <label class="block text-sm text-zinc-400 mb-2">Modifier</label>
            <input type="number" id="modifier" value="0"
                   class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600 font-mono">
        </div>

        <button onclick="roll()"
                class="w-full py-4 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-xl tracking-tight transition-all">
            Roll
        </button>
    </div>

    <!-- Results -->
    <div class="lg:col-span-3 space-y-3">
        <div id="resultPanel" class="rounded-xl border border-zinc-800 p-6 min-h-[160px] flex items-center justify-center" style="background:#111113;">
            <span class="text-zinc-600 text-sm">Select dice and roll</span>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden" style="background:#111113;">
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-zinc-800">
                <span class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Roll history</span>
                <button onclick="clearHistory()" class="text-xs text-zinc-700 hover:text-zinc-400 transition-colors">Clear</button>
            </div>
            <div id="historyList" class="divide-y divide-zinc-800/60 max-h-72 overflow-y-auto font-mono">
                <div class="px-4 py-3 text-zinc-600 text-sm">No rolls yet</div>
            </div>
        </div>
    </div>
</div>

<script>
const STANDARD_DICE = [4, 6, 8, 10, 12, 20, 100];
let selectedSides = 6;
let history = JSON.parse(localStorage.getItem('randomizer_dice') || '[]');

function buildDiceGrid() {
    const grid = document.getElementById('diceGrid');
    grid.innerHTML = STANDARD_DICE.map(d => `
        <button id="die-${d}" onclick="selectDie(${d})"
                class="die-btn py-2 text-sm font-bold font-mono rounded-lg border transition-colors
                       ${d === selectedSides
                           ? 'bg-red-900/50 border-red-700 text-red-400'
                           : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200'}">
            d${d}
        </button>`).join('');
}

function selectDie(sides) {
    selectedSides = sides;
    STANDARD_DICE.forEach(d => {
        const btn = document.getElementById(`die-${d}`);
        if (!btn) return;
        btn.className = `die-btn py-2 text-sm font-bold font-mono rounded-lg border transition-colors ${
            d === sides
                ? 'bg-red-900/50 border-red-700 text-red-400'
                : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200'}`;
    });
}

function selectCustom() {
    const val = parseInt(document.getElementById('customSides').value, 10);
    if (!val || val < 2) { alert('Minimum 2 sides'); return; }
    selectedSides = val;
    STANDARD_DICE.forEach(d => {
        const btn = document.getElementById(`die-${d}`);
        if (btn) btn.className = 'die-btn py-2 text-sm font-bold font-mono rounded-lg border transition-colors bg-zinc-800 border-zinc-700 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200';
    });
}

function roll() {
    const count    = parseInt(document.getElementById('diceCount').value, 10);
    const modifier = parseInt(document.getElementById('modifier').value, 10) || 0;
    const rolls    = Array.from({ length: count }, () => Math.floor(Math.random() * selectedSides) + 1);
    const total    = rolls.reduce((s, v) => s + v, 0) + modifier;

    const record = { dice: `${count}d${selectedSides}`, rolls, modifier, total, ts: Date.now() };
    history.unshift(record);
    if (history.length > 100) history.pop();
    localStorage.setItem('randomizer_dice', JSON.stringify(history));

    renderResult(record);
    renderHistory();
}

function renderResult(record) {
    const panel = document.getElementById('resultPanel');
    const modStr = record.modifier !== 0
        ? `<span class="text-zinc-600"> ${record.modifier > 0 ? '+' : ''}${record.modifier}</span>`
        : '';

    const diceHtml = record.rolls.map(r => {
        const isMax = r === selectedSides;
        const isMin = r === 1 && selectedSides > 1;
        const cls   = isMax ? 'text-green-400 border-green-900 bg-green-950/50'
                    : isMin ? 'text-red-500 border-red-900 bg-red-950/50'
                    : 'text-zinc-300 border-zinc-700 bg-zinc-800';
        return `<span class="inline-flex items-center justify-center w-12 h-12 rounded-lg border font-mono font-bold text-lg ${cls}">${r}</span>`;
    }).join('');

    panel.innerHTML = `
        <div class="w-full text-center space-y-4">
            <div class="text-5xl font-black text-zinc-100 tracking-tight font-mono">${record.total}${modStr}</div>
            <div class="text-zinc-600 text-xs font-mono">${record.dice}${record.modifier !== 0 ? (record.modifier > 0 ? ` + ${record.modifier}` : ` − ${Math.abs(record.modifier)}`) : ''} = ${record.total}</div>
            <div class="flex flex-wrap gap-2 justify-center">${diceHtml}</div>
            ${record.rolls.length > 1 ? `<div class="text-xs text-zinc-700 font-mono">sum: ${record.rolls.reduce((s,v)=>s+v,0)}</div>` : ''}
        </div>`;
}

function renderHistory() {
    const list = document.getElementById('historyList');
    if (history.length === 0) {
        list.innerHTML = '<div class="px-4 py-3 text-zinc-600 text-sm">No rolls yet</div>';
        return;
    }
    list.innerHTML = history.slice(0, 40).map(h => `
        <div class="flex items-center gap-3 px-4 py-2">
            <span class="text-xs text-zinc-600 w-16 shrink-0">${h.dice}${h.modifier ? (h.modifier>0?'+':'')+h.modifier : ''}</span>
            <span class="flex-1 text-xs text-zinc-600 truncate">[${h.rolls.join(', ')}]</span>
            <span class="font-bold text-zinc-200">${h.total}</span>
        </div>`).join('');
}

function clearHistory() {
    history = [];
    localStorage.removeItem('randomizer_dice');
    document.getElementById('resultPanel').innerHTML = '<span class="text-zinc-600 text-sm">Select dice and roll</span>';
    renderHistory();
}

buildDiceGrid();
renderHistory();
</script>

<?php include 'includes/footer.php'; ?>
