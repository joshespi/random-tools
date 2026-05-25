<?php
$pageTitle = 'Tip';
include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Tip Calculator</h1>
<p class="text-zinc-500 mb-7 text-sm">Enter the bill, pick a tip, split however many ways.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <div class="lg:col-span-2 space-y-3">
        <div class="rounded-xl p-5 border border-zinc-800 space-y-5" style="background:#111113;">

            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Options</div>

            <!-- Bill -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Bill amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-sm font-mono">$</span>
                    <input id="bill" type="number" min="0" step="0.01" placeholder="0.00"
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-lg pl-7 pr-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-1 focus:ring-red-600">
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
                    <input id="tipSlider" type="range" min="0" max="50" value="20" class="flex-1">
                    <span class="w-12 text-right">
                        <input id="tipInput" type="number" min="0" max="50" value="20"
                               class="w-12 bg-zinc-800 border border-zinc-700 rounded px-1 py-0.5 text-sm text-red-500 font-mono text-center focus:outline-none focus:ring-1 focus:ring-red-600">
                    </span>
                    <span class="text-sm text-zinc-500 font-mono">%</span>
                </div>
            </div>

            <!-- Split -->
            <div>
                <div class="flex justify-between mb-2">
                    <label class="text-sm text-zinc-400">Split</label>
                    <span id="splitLabel" class="text-sm font-mono text-red-500">1</span>
                </div>
                <input id="splitSlider" type="range" min="1" max="20" value="1"
                       oninput="document.getElementById('splitLabel').textContent = this.value; render()"
                       class="w-full">
            </div>

        </div>
    </div>

    <!-- Results -->
    <div class="lg:col-span-3 space-y-3">

        <!-- Per person callout -->
        <div class="rounded-xl p-5 border border-zinc-800 text-center" style="background:#111113;">
            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest mb-3">Each person pays</div>
            <div id="perPersonTotal" class="text-5xl font-extrabold text-zinc-100 tracking-tight mb-1">—</div>
            <div id="perPersonBreak" class="text-xs text-zinc-600 font-mono mt-2"></div>
        </div>

        <!-- Breakdown rows -->
        <div class="rounded-xl border border-zinc-800 overflow-hidden" style="background:#111113;">
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
            <button id="roundDownBtn" type="button"
                    class="flex-1 py-2.5 rounded-xl border border-zinc-800 text-sm text-zinc-400 hover:text-zinc-200 hover:border-zinc-600 transition-colors font-mono"
                    style="background:#111113;">
                Round down per person
            </button>
            <button id="roundUpBtn" type="button"
                    class="flex-1 py-2.5 rounded-xl border border-zinc-800 text-sm text-zinc-400 hover:text-zinc-200 hover:border-zinc-600 transition-colors font-mono"
                    style="background:#111113;">
                Round up per person
            </button>
        </div>

    </div>
</div>

<script>
function fmt(n) {
    return '$' + n.toFixed(2);
}

function getValues() {
    const bill  = Math.max(0, parseFloat(document.getElementById('bill').value) || 0);
    const tip   = Math.max(0, Math.min(50, parseFloat(document.getElementById('tipInput').value) || 0));
    const split = Math.max(1, parseInt(document.getElementById('splitSlider').value, 10) || 1);
    return { bill, tip, split };
}

function render(overridePerPerson) {
    const { bill, tip, split } = getValues();
    const tipAmt  = bill * (tip / 100);
    const total   = bill + tipAmt;
    const perPerson = overridePerPerson !== undefined ? overridePerPerson : total / split;

    document.getElementById('rBill').textContent     = fmt(bill);
    document.getElementById('rTip').textContent      = fmt(tipAmt);
    document.getElementById('rTotal').textContent    = fmt(total);
    document.getElementById('rTipLabel').textContent = `Tip (${tip}%)`;

    if (bill > 0) {
        document.getElementById('perPersonTotal').textContent = fmt(perPerson);
        if (split > 1) {
            document.getElementById('perPersonBreak').textContent =
                `${fmt(bill / split)} bill + ${fmt(tipAmt / split)} tip`;
        } else {
            document.getElementById('perPersonBreak').textContent = '';
        }
    } else {
        document.getElementById('perPersonTotal').textContent = '—';
        document.getElementById('perPersonBreak').textContent = '';
    }
}

function setTip(pct) {
    document.getElementById('tipInput').value  = pct;
    document.getElementById('tipSlider').value = pct;
    document.querySelectorAll('.tip-btn').forEach(btn => {
        const active = Number(btn.dataset.pct) === pct;
        btn.classList.toggle('border-red-700', active);
        btn.classList.toggle('text-red-400',   active);
        btn.classList.toggle('border-zinc-700', !active);
        btn.classList.toggle('text-zinc-300',   !active);
    });
    render();
}

document.querySelectorAll('.tip-btn').forEach(btn => {
    btn.addEventListener('click', () => setTip(Number(btn.dataset.pct)));
});

document.getElementById('tipSlider').addEventListener('input', function () {
    document.getElementById('tipInput').value = this.value;
    document.querySelectorAll('.tip-btn').forEach(b => {
        b.classList.remove('border-red-700', 'text-red-400');
        b.classList.add('border-zinc-700', 'text-zinc-300');
    });
    render();
});

document.getElementById('tipInput').addEventListener('input', function () {
    const v = Math.max(0, Math.min(50, parseFloat(this.value) || 0));
    document.getElementById('tipSlider').value = v;
    document.querySelectorAll('.tip-btn').forEach(b => {
        b.classList.remove('border-red-700', 'text-red-400');
        b.classList.add('border-zinc-700', 'text-zinc-300');
    });
    render();
});

document.getElementById('bill').addEventListener('input', render);

document.getElementById('roundUpBtn').addEventListener('click', () => {
    const { total, split } = (() => {
        const v = getValues();
        return { total: v.bill * (1 + v.tip / 100), split: v.split };
    })();
    render(Math.ceil(total / split * 100) / 100);
});

document.getElementById('roundDownBtn').addEventListener('click', () => {
    const { total, split } = (() => {
        const v = getValues();
        return { total: v.bill * (1 + v.tip / 100), split: v.split };
    })();
    render(Math.floor(total / split * 100) / 100);
});

setTip(20);
</script>

<?php include 'includes/footer.php'; ?>
