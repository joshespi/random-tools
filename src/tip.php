<?php
include 'includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <div class="lg:col-span-2 space-y-3">
        <div class="rounded-xl p-5 border border-zinc-800 space-y-5 bg-zinc-925">

            <div class="text-xs text-zinc-500 font-medium uppercase tracking-widest">Options</div>

            <!-- Bill -->
            <div>
                <label for="bill" class="block text-sm text-zinc-400 mb-2">Bill amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-sm font-mono">$</span>
                    <input id="bill" type="number" min="0" step="0.01" placeholder="0.00"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-lg pl-7 pr-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-red-600">
                </div>
            </div>

            <!-- Tip % quick buttons -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Tip percentage</label>
                <div class="flex gap-2 mb-3">
                    <?php foreach ([15, 18, 20, 25] as $pct): ?>
                        <button type="button" data-pct="<?= $pct ?>"
                                class="tip-btn flex-1 py-1.5 rounded-lg border border-zinc-700 text-sm font-mono text-zinc-300 hover:border-red-700 hover:text-red-400 transition-colors"
                                style="background:#1c1c1e;">
                            <?= $pct ?>%
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="flex items-center gap-3">
                    <input id="tipSlider" aria-label="Tip percent slider" type="range" min="0" max="50" value="20" class="flex-1">
                    <span class="w-12 text-right">
                        <input id="tipInput" aria-label="Tip percent" type="number" min="0" max="50" value="20"
                               class="w-12 bg-zinc-800 border border-zinc-700 rounded px-1 py-0.5 text-sm text-red-500 font-mono text-center focus:outline-none focus:ring-1 focus:ring-red-600">
                    </span>
                    <span class="text-sm text-zinc-500 font-mono">%</span>
                </div>
            </div>

            <!-- Split -->
            <div>
                <div class="flex justify-between mb-2">
                    <label for="splitSlider" class="text-sm text-zinc-400">Split</label>
                    <span id="splitLabel" class="text-sm font-mono text-red-500">1</span>
                </div>
                <input id="splitSlider" type="range" min="1" max="20" value="1"
                       class="w-full">
            </div>

        </div>
    </div>

    <!-- Results -->
    <div class="lg:col-span-3 space-y-3">

        <!-- Per person callout -->
        <div class="rounded-xl p-5 border border-zinc-800 text-center bg-zinc-925">
            <div class="text-xs text-zinc-500 font-medium uppercase tracking-widest mb-3">Each person pays</div>
            <div id="perPersonTotal" aria-live="polite" class="text-5xl font-extrabold text-zinc-100 tracking-tight mb-1">—</div>
            <div id="perPersonBreak" class="text-xs text-zinc-500 font-mono mt-2"></div>
        </div>

        <!-- Breakdown rows -->
        <div class="rounded-xl border border-zinc-800 overflow-hidden bg-zinc-925">
            <div class="divide-y divide-zinc-800/60">
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-zinc-400">Bill</span>
                    <span id="rBill" class="text-sm font-mono text-zinc-100">—</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-zinc-400" id="rTipLabel">Tip (20%)</span>
                    <span id="rTip" class="text-sm font-mono text-zinc-100">—</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-zinc-400">Total</span>
                    <span id="rTotal" class="text-sm font-mono text-zinc-100 font-semibold">—</span>
                </div>
            </div>
        </div>

        <!-- Round buttons -->
        <div class="flex gap-2">
            <button type="button" data-round="down"
                    class="round-btn flex-1 py-2.5 rounded-xl border border-zinc-800 text-sm text-zinc-400 hover:text-zinc-200 hover:border-zinc-600 transition-colors font-mono bg-zinc-925">
                Round down to whole $
            </button>
            <button type="button" data-round="up"
                    class="round-btn flex-1 py-2.5 rounded-xl border border-zinc-800 text-sm text-zinc-400 hover:text-zinc-200 hover:border-zinc-600 transition-colors font-mono bg-zinc-925">
                Round up to whole $
            </button>
        </div>

    </div>
</div>

<script>
const billEl          = document.getElementById('bill');
const tipSliderEl      = document.getElementById('tipSlider');
const tipInputEl       = document.getElementById('tipInput');
const splitSliderEl    = document.getElementById('splitSlider');
const splitLabelEl     = document.getElementById('splitLabel');
const rBillEl          = document.getElementById('rBill');
const rTipEl           = document.getElementById('rTip');
const rTotalEl         = document.getElementById('rTotal');
const rTipLabelEl      = document.getElementById('rTipLabel');
const perPersonTotalEl = document.getElementById('perPersonTotal');
const perPersonBreakEl = document.getElementById('perPersonBreak');
const tipButtons       = document.querySelectorAll('.tip-btn');
const roundButtons     = document.querySelectorAll('.round-btn');

const TIP_ACTIVE_CLASSES   = ['border-red-700', 'text-red-400'];
const TIP_INACTIVE_CLASSES = ['border-zinc-700', 'text-zinc-300'];

function fmt(n) {
    return '$' + n.toFixed(2);
}

function getValues() {
    const bill  = Math.max(0, parseFloat(billEl.value) || 0);
    const tip   = Math.max(0, Math.min(50, parseFloat(tipInputEl.value) || 0));
    const split = Math.max(1, parseInt(splitSliderEl.value, 10) || 1);
    return { bill, tip, split };
}

let roundMode = null; // null | 'up' | 'down' — whole dollars per person

function render() {
    const { bill, tip, split } = getValues();
    let tipAmt = bill * (tip / 100);
    let total  = bill + tipAmt;
    let perPerson = total / split;
    let tipPct = tip;
    let rounded = false;

    if (roundMode && bill > 0) {
        const fn = roundMode === 'up' ? Math.ceil : Math.floor;
        const candidate = fn(Math.round(perPerson * 100) / 100);
        if (candidate * split >= bill) { // never round down into the bill itself
            rounded   = true;
            perPerson = candidate;
            total     = perPerson * split;
            tipAmt    = total - bill;
            tipPct    = Math.round((tipAmt / bill) * 1000) / 10;
        }
    }

    rBillEl.textContent     = fmt(bill);
    rTipEl.textContent      = fmt(tipAmt);
    rTotalEl.textContent    = fmt(total);
    rTipLabelEl.textContent = 'Tip (' + tipPct + '%)';

    if (bill > 0) {
        perPersonTotalEl.textContent = fmt(perPerson);
        perPersonBreakEl.textContent = split > 1
            ? fmt(bill / split) + ' bill + ' + fmt(tipAmt / split) + ' tip'
            : '';
    } else {
        perPersonTotalEl.textContent = '—';
        perPersonBreakEl.textContent = '';
    }

    if (roundMode && bill > 0 && !rounded) roundMode = null; // rounding down would cut into the bill
    setActiveButton(roundButtons, btn => rounded && btn.dataset.round === roundMode,
        TIP_ACTIVE_CLASSES, ['border-zinc-800', 'text-zinc-400']);
}

function clearTipActive() {
    setActiveButton(tipButtons, () => false, TIP_ACTIVE_CLASSES, TIP_INACTIVE_CLASSES);
}

function setTip(pct) {
    tipInputEl.value  = pct;
    tipSliderEl.value = pct;
    setActiveButton(tipButtons, btn => Number(btn.dataset.pct) === pct, TIP_ACTIVE_CLASSES, TIP_INACTIVE_CLASSES);
    render();
}

tipButtons.forEach(btn => {
    btn.addEventListener('click', () => setTip(Number(btn.dataset.pct)));
});

tipSliderEl.addEventListener('input', function () {
    tipInputEl.value = this.value;
    clearTipActive();
    render();
});

tipInputEl.addEventListener('input', function () {
    const v = Math.max(0, Math.min(50, parseFloat(this.value) || 0));
    tipSliderEl.value = v;
    clearTipActive();
    render();
});

billEl.addEventListener('input', render);

splitSliderEl.addEventListener('input', function () {
    splitLabelEl.textContent = this.value;
    render();
});

roundButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        roundMode = roundMode === btn.dataset.round ? null : btn.dataset.round;
        render();
    });
});

setTip(20);
</script>

<?php include 'includes/footer.php'; ?>
