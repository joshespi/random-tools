<?php
$pageTitle = 'Coin';
include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Coin Flip</h1>
<p class="text-zinc-500 mb-7 text-sm">Flip a coin. Streaks and history are saved locally.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Left: coin + flip -->
    <div class="lg:col-span-3 space-y-3">
        <div class="rounded-xl p-5 border border-zinc-800 text-center space-y-5 bg-zinc-925">
            <div class="coin-stage">
                <div class="coin-toss" id="coinToss">
                    <div class="coin" id="coin">
                        <div class="coin-face coin-front font-mono">H</div>
                        <div class="coin-face coin-back font-mono">T</div>
                    </div>
                </div>
            </div>

            <button id="flipBtn" onclick="doFlip()"
                    class="w-full py-4 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-xl tracking-tight transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100">
                Flip
            </button>
            <div id="flipResultLabel" class="text-xs text-zinc-600 font-mono h-4"></div>
        </div>
    </div>

    <!-- Right: tally + history -->
    <div class="lg:col-span-2 space-y-3">
        <div class="rounded-xl border border-zinc-800 bg-zinc-925 p-4">
            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest mb-3">Tally</div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div>
                    <div id="headsCount" class="text-2xl font-extrabold font-mono text-zinc-100">0</div>
                    <div class="text-xs text-zinc-600 mt-1">Heads</div>
                </div>
                <div>
                    <div id="tailsCount" class="text-2xl font-extrabold font-mono text-zinc-100">0</div>
                    <div class="text-xs text-zinc-600 mt-1">Tails</div>
                </div>
                <div>
                    <div id="streakCount" class="text-2xl font-extrabold font-mono text-red-500">0</div>
                    <div class="text-xs text-zinc-600 mt-1">Streak</div>
                </div>
            </div>
            <button onclick="resetTally()"
                    class="w-full mt-4 px-3 py-1.5 bg-transparent border border-zinc-800 rounded-lg text-xs text-zinc-600 hover:text-zinc-400 transition-colors">
                Reset tally
            </button>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden bg-zinc-925">
            <div class="px-4 py-2.5 border-b border-zinc-800 text-xs text-zinc-600 font-medium uppercase tracking-widest">
                Recent flips
            </div>
            <div id="flipHistory" class="flex flex-wrap gap-1.5 p-4 max-h-28 overflow-y-auto">
                <span class="text-zinc-600 text-sm">No flips yet</span>
            </div>
        </div>
    </div>
</div>

<style>
    .coin-stage { display: flex; align-items: center; justify-content: center; padding: 1.25rem 0; perspective: 1000px; }
    .coin-toss  { width: 128px; height: 128px; }
    .coin {
        width: 128px; height: 128px; position: relative;
        transform-style: preserve-3d;
        transition: transform 1.05s cubic-bezier(.33,0,.2,1);
    }
    .coin-face {
        position: absolute; inset: 0; border-radius: 9999px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 2.75rem;
        backface-visibility: hidden; -webkit-backface-visibility: hidden;
        border: 3px solid rgba(255,255,255,0.08);
        box-shadow: 0 6px 18px rgba(0,0,0,.5), inset 0 0 0 2px rgba(255,255,255,.04);
    }
    .coin-front { background: radial-gradient(circle at 35% 30%, #52525b, #18181b 70%); color: #f4f4f5; }
    .coin-back  { background: radial-gradient(circle at 35% 30%, #b91c1c, #3f0d0d 70%); color: #fecaca; transform: rotateY(180deg); }

    .coin-toss.tossing { animation: coinArc 1.05s cubic-bezier(.33,.66,.66,1) 1; }
    @keyframes coinArc {
        0%   { transform: translateY(0); }
        40%  { transform: translateY(-26px); }
        100% { transform: translateY(0); }
    }
</style>

<script>
const STORAGE_KEY = 'randomizer_coin_v1';

function defaultState() {
    return { heads: 0, tails: 0, streak: { face: null, count: 0 }, history: [] };
}

function saveState() {
    saveJSON(STORAGE_KEY, state);
}

let state = loadJSON(STORAGE_KEY, p => p && typeof p.heads === 'number', defaultState);
let coinRotation = 0;

const flipBtn         = document.getElementById('flipBtn');
const coinEl          = document.getElementById('coin');
const tossEl          = document.getElementById('coinToss');
const flipResultLabelEl = document.getElementById('flipResultLabel');
const headsCountEl    = document.getElementById('headsCount');
const tailsCountEl    = document.getElementById('tailsCount');
const streakCountEl   = document.getElementById('streakCount');
const flipHistoryEl   = document.getElementById('flipHistory');

function doFlip() {
    flipBtn.disabled = true;

    const result = Math.random() < 0.5 ? 'H' : 'T';

    const spins = 4 + Math.floor(Math.random() * 2);
    const mod = coinRotation % 360;
    const faceOffset = result === 'T' ? 180 : 0;
    const delta = spins * 360 + ((faceOffset - mod + 360) % 360);
    coinRotation += delta;

    coinEl.style.transform = `rotateY(${coinRotation}deg)`;

    tossEl.classList.remove('tossing');
    void tossEl.offsetWidth;
    tossEl.classList.add('tossing');

    setTimeout(() => finishFlip(result), 1100);
}

function finishFlip(result) {
    updateTally(result);
    pushFlipHistory(result);
    flipResultLabelEl.textContent = result === 'H' ? 'Heads' : 'Tails';
    flipBtn.disabled = false;
}

function updateTally(result) {
    if (result === 'H') state.heads++; else state.tails++;
    if (state.streak.face === result) {
        state.streak.count++;
    } else {
        state.streak = { face: result, count: 1 };
    }
    saveState();
    renderTally();
}

function renderTally() {
    headsCountEl.textContent = state.heads;
    tailsCountEl.textContent = state.tails;
    streakCountEl.textContent = state.streak.count;
}

function pushFlipHistory(face) {
    pushCapped(state.history, { face, ts: Date.now() }, 200);
    saveState();
    renderFlipHistory();
}

function renderFlipHistory() {
    renderCappedList(flipHistoryEl, state.history, 40, (h, idx) => {
        const isLatest = idx === 0;
        const isHeads = h.face === 'H';
        return `<span class="w-7 h-7 flex items-center justify-center rounded-full text-xs font-mono font-bold
                     ${isHeads ? 'bg-zinc-800 text-zinc-200 border border-zinc-700' : 'bg-red-950/60 text-red-300 border border-red-900'}
                     ${isLatest ? 'ring-1 ring-red-600' : ''}">${h.face}</span>`;
    }, '<span class="text-zinc-600 text-sm">No flips yet</span>');
}

function resetTally() {
    if (!confirm('Reset tally, streak, and history?')) return;
    state = defaultState();
    saveState();
    renderTally();
    renderFlipHistory();
    flipResultLabelEl.textContent = '';
}

function init() {
    renderTally();
    renderFlipHistory();
}

init();
</script>

<?php include 'includes/footer.php'; ?>
