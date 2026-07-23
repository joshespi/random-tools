<?php
$pageTitle = 'Dice';
include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Dice Roller</h1>
<p class="text-zinc-500 mb-7 text-sm">Pick a die, set the count and modifier, roll.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Left: options -->
    <div class="lg:col-span-2 space-y-3">
        <div class="rounded-xl p-5 border border-zinc-800 space-y-5 bg-zinc-925">
            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Options</div>

            <!-- Die type -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Die type</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ([4, 6, 8, 10, 12, 20, 100] as $sides): ?>
                        <button type="button" data-sides="<?= $sides ?>"
                                class="die-btn flex-1 min-w-[52px] py-1.5 rounded-lg border border-zinc-700 text-sm font-mono text-zinc-300 hover:border-red-700 hover:text-red-400 transition-colors"
                                style="background:#1c1c1e;">
                            d<?= $sides ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Number of dice -->
            <div>
                <div class="flex justify-between mb-2">
                    <label class="text-sm text-zinc-400">Number of dice</label>
                    <span id="countLabel" class="text-sm font-mono text-red-500">1</span>
                </div>
                <input id="countSlider" type="range" min="1" max="10" value="1" class="w-full">
            </div>

            <!-- Modifier -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Modifier</label>
                <div class="flex items-center gap-2">
                    <button type="button" id="modDec"
                            class="w-9 h-9 flex items-center justify-center bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-zinc-300 text-lg font-bold transition-colors">&minus;</button>
                    <input id="modInput" type="number" value="0"
                           class="w-16 bg-zinc-800 border border-zinc-700 rounded-lg px-2 py-1.5 text-sm text-zinc-100 text-center font-mono focus:outline-none focus:ring-1 focus:ring-red-600">
                    <button type="button" id="modInc"
                            class="w-9 h-9 flex items-center justify-center bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-zinc-300 text-lg font-bold transition-colors">+</button>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="clearDiceHistory()"
                    class="px-3 py-1.5 bg-transparent border border-zinc-800 rounded-lg text-xs text-zinc-600 hover:text-zinc-400 transition-colors">
                Clear history
            </button>
        </div>
    </div>

    <!-- Right: roll + result + history -->
    <div class="lg:col-span-3 space-y-3">

        <div class="rounded-xl p-5 border border-zinc-800 text-center space-y-4 bg-zinc-925">
            <div id="notationPreview" class="text-xs text-zinc-600 font-mono h-4">1d6</div>

            <div id="diceTray" class="min-h-[170px] flex flex-wrap content-center justify-center gap-3 py-2">
                <span class="text-zinc-600 text-sm">Press Roll to throw the dice</span>
            </div>

            <button id="rollBtn" onclick="doRoll()"
                    class="w-full py-4 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-xl tracking-tight transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100">
                Roll
            </button>

            <div id="totalResult" class="text-5xl font-extrabold text-zinc-100 tracking-tight min-h-[3.5rem] flex items-center justify-center">&mdash;</div>
            <div id="totalBreak" class="text-xs text-zinc-600 font-mono min-h-[1rem]"></div>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden bg-zinc-925">
            <div class="px-4 py-2.5 border-b border-zinc-800 text-xs text-zinc-600 font-medium uppercase tracking-widest">
                Recent rolls
            </div>
            <div id="diceHistory" class="divide-y divide-zinc-800/60 max-h-64 overflow-y-auto">
                <div class="px-4 py-3 text-zinc-600 text-sm">No rolls yet</div>
            </div>
        </div>
    </div>
</div>

<style>
    .die-tile {
        width: 64px; height: 64px;
        border-radius: 1rem;
        border: 2px solid #3f3f46;
        background: linear-gradient(145deg, #27272a, #17171a);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.05), 0 3px 8px rgba(0,0,0,.35);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .die-tile.tumbling {
        animation-name: dieShake;
        animation-timing-function: ease-in-out;
        animation-iteration-count: infinite;
        animation-direction: alternate;
        border-color: #52525b;
    }
    .die-tile.settled {
        animation: dieSettle 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        border-color: #b91c1c;
        transition: border-color 0.5s ease-out;
    }
    @keyframes dieShake {
        from { transform: rotate(-4deg) scale(0.96); }
        to   { transform: rotate(4deg) scale(1.04); }
    }
    @keyframes dieSettle {
        0%   { transform: scale(1.18) rotate(0deg); }
        60%  { transform: scale(0.94) rotate(0deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    .die-face-inner {
        font-weight: 800;
        color: #f4f4f5;
        line-height: 1;
    }
    .die-face-inner.num-6, .die-face-inner.num-8, .die-face-inner.num-10, .die-face-inner.num-12, .die-face-inner.num-20 {
        font-size: 1.5rem;
    }
    .die-face-inner.num-100 {
        font-size: 1.15rem;
    }
    #totalResult.reveal { animation: totalIn 0.25s ease-out; }
    @keyframes totalIn {
        from { opacity: 0; transform: translateY(4px) scale(0.96); }
        to   { opacity: 1; transform: none; }
    }
</style>

<script>
const STORAGE_KEY = 'randomizer_dice_v1';

function defaultState() {
    return {
        config: { sides: 6, count: 1, modifier: 0 },
        history: [],
    };
}

function saveState() {
    saveJSON(STORAGE_KEY, state);
}

let state = loadJSON(STORAGE_KEY, p => p && p.config, defaultState);

const dieButtons     = document.querySelectorAll('.die-btn');
const countSliderEl  = document.getElementById('countSlider');
const countLabelEl   = document.getElementById('countLabel');
const modInputEl     = document.getElementById('modInput');
const notationEl     = document.getElementById('notationPreview');
const trayEl         = document.getElementById('diceTray');
const rollBtn        = document.getElementById('rollBtn');
const totalResultEl  = document.getElementById('totalResult');
const totalBreakEl   = document.getElementById('totalBreak');
const historyEl      = document.getElementById('diceHistory');

const TL = [4, 4], TR = [20, 4], ML = [4, 12], C = [12, 12], MR = [20, 12], BL = [4, 20], BR = [20, 20];
const PIP_LAYOUT = {
    1: [C],
    2: [TL, BR],
    3: [TL, C, BR],
    4: [TL, TR, BL, BR],
    5: [TL, TR, C, BL, BR],
    6: [TL, TR, ML, MR, BL, BR],
};

function pipSvg(value) {
    const dots = (PIP_LAYOUT[value] || []).map(([x, y]) =>
        `<circle cx="${x}" cy="${y}" r="2.1" fill="currentColor"></circle>`
    ).join('');
    return `<svg width="28" height="28" viewBox="0 0 24 24" class="text-zinc-100" aria-hidden="true">${dots}</svg>`;
}

function setSides(sides) {
    state.config.sides = sides;
    setActiveButton(
        dieButtons,
        btn => Number(btn.dataset.sides) === sides,
        ['border-red-700', 'text-red-400'],
        ['border-zinc-700', 'text-zinc-300']
    );
    saveState();
    updateNotation();
}

dieButtons.forEach(btn => {
    btn.addEventListener('click', () => setSides(Number(btn.dataset.sides)));
});

countSliderEl.addEventListener('input', function () {
    state.config.count = Number(this.value);
    countLabelEl.textContent = this.value;
    updateNotation();
});
countSliderEl.addEventListener('change', saveState);

function setModifier(value) {
    const v = Math.max(-999, Math.min(999, parseInt(value, 10) || 0));
    state.config.modifier = v;
    modInputEl.value = v;
    updateNotation();
}

modInputEl.addEventListener('input', function () { setModifier(this.value); });
modInputEl.addEventListener('change', saveState);
document.getElementById('modDec').addEventListener('click', () => { setModifier(state.config.modifier - 1); saveState(); });
document.getElementById('modInc').addEventListener('click', () => { setModifier(state.config.modifier + 1); saveState(); });

function notation(sides, count, modifier) {
    const mod = modifier !== 0 ? (modifier > 0 ? `+${modifier}` : `${modifier}`) : '';
    return `${count}d${sides}${mod}`;
}

function updateNotation() {
    const { sides, count, modifier } = state.config;
    notationEl.textContent = notation(sides, count, modifier);
}

function buildTiles(count) {
    trayEl.innerHTML = '';
    const tiles = [];
    for (let i = 0; i < count; i++) {
        const tile = document.createElement('div');
        tile.className = 'die-tile tumbling';
        tile.style.animationDuration = `${180 + Math.random() * 80}ms`;
        const inner = document.createElement('div');
        inner.className = `die-face-inner font-mono num-${state.config.sides}`;
        inner.textContent = '?';
        tile.appendChild(inner);
        trayEl.appendChild(tile);
        tiles.push({ tile, inner });
    }
    return tiles;
}

function tickDie(inner, sides, stopAt, onDone) {
    const start = performance.now();
    function tick() {
        inner.textContent = 1 + Math.floor(Math.random() * sides);
        const elapsed = performance.now() - start;
        if (elapsed >= stopAt) { onDone(); return; }
        setTimeout(tick, 55 + Math.random() * 30);
    }
    tick();
}

function settleDie(tile, inner, finalVal, sides) {
    tile.classList.remove('tumbling');
    if (sides === 6) {
        inner.innerHTML = pipSvg(finalVal);
    } else {
        inner.textContent = finalVal;
    }
    void tile.offsetWidth;
    tile.classList.add('settled');
}

const STAGGER_SPAN = 420;
const TUMBLE_BASE  = 750;

function doRoll() {
    const { sides, count, modifier } = state.config;
    const finals = Array.from({ length: count }, () => 1 + Math.floor(Math.random() * sides));

    rollBtn.disabled = true;
    totalResultEl.classList.remove('reveal');
    totalResultEl.textContent = '—';
    totalBreakEl.textContent = '';
    const tiles = buildTiles(count);

    const maxStop = count > 1 ? TUMBLE_BASE + STAGGER_SPAN : TUMBLE_BASE;
    finals.forEach((finalVal, i) => {
        const { tile, inner } = tiles[i];
        const stopAt = count > 1 ? TUMBLE_BASE + (i / (count - 1)) * STAGGER_SPAN : TUMBLE_BASE;
        tickDie(inner, sides, stopAt, () => settleDie(tile, inner, finalVal, sides));
    });

    setTimeout(() => {
        revealTotal(finals, modifier, sides, count);
        rollBtn.disabled = false;
    }, maxStop + 150);
}

function revealTotal(finals, modifier, sides, count) {
    const sum = finals.reduce((a, b) => a + b, 0);
    const total = sum + modifier;

    totalResultEl.textContent = total;
    void totalResultEl.offsetWidth;
    totalResultEl.classList.add('reveal');

    totalBreakEl.textContent = modifier !== 0
        ? `${finals.join(' + ')} = ${sum}, ${modifier > 0 ? '+' : ''}${modifier} mod → ${total}`
        : `${finals.join(' + ')} = ${total}`;

    pushHistory(sides, count, modifier, finals, total);
}

function pushHistory(sides, count, modifier, rolls, total) {
    pushCapped(state.history, { sides, count, modifier, rolls, total, ts: Date.now() }, 200);
    saveState();
    renderHistory();
}

function renderHistory() {
    renderCappedList(historyEl, state.history, 30, h => {
        const label = `${notation(h.sides, h.count, h.modifier)} = ${h.total}`;
        return `<div class="flex items-center gap-2 px-4 py-2.5">
            <span class="flex-1 text-sm text-zinc-300 font-mono">${label}</span>
            <span class="text-xs text-zinc-600">${h.rolls.join(', ')}</span>
        </div>`;
    }, '<div class="px-4 py-3 text-zinc-600 text-sm">No rolls yet</div>');
}

function clearDiceHistory() {
    if (!confirm('Clear roll history?')) return;
    state.history = [];
    saveState();
    renderHistory();
}

function init() {
    countSliderEl.value = state.config.count;
    countLabelEl.textContent = state.config.count;
    modInputEl.value = state.config.modifier;
    setSides(state.config.sides);
    renderHistory();
}

init();
</script>

<?php include 'includes/footer.php'; ?>
