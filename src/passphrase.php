<?php
$pageTitle = 'Passphrase';
include 'includes/words.php';

$wordCount   = 4;
$delimiter   = 'symbol';
$customDelim = '';
$caseMode    = 'random';
$appendNum   = false;
$appendSym   = false;
$quantity    = 5;

$delimOptions = [
    'symbol' => 'Random symbol  —  word!word',
    '-'      => 'Dash  —  word-word',
    '_'      => 'Underscore  —  word_word',
    '.'      => 'Dot  —  word.word',
    ' '      => 'Space  —  word word',
    ''       => 'None  —  wordword',
    'number' => 'Digit between  —  word3word',
    'custom' => 'Custom…',
];

include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Passphrase Generator</h1>
<p class="text-zinc-500 mb-7 text-sm">Live preview — tweak settings and the phrases regenerate.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <form id="phraseForm" class="lg:col-span-2 space-y-3" onsubmit="event.preventDefault();">
        <div class="rounded-xl p-5 border border-zinc-800 space-y-5" style="background:#111113;">

            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Options</div>

            <div>
                <div class="flex justify-between mb-2">
                    <label class="text-sm text-zinc-400">Word count</label>
                    <span id="wcLabel" class="text-sm font-mono text-red-500"><?= $wordCount ?></span>
                </div>
                <input type="range" name="word_count" min="2" max="10" value="<?= $wordCount ?>"
                       oninput="document.getElementById('wcLabel').textContent = this.value"
                       class="w-full">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Delimiter</label>
                <select name="delimiter" id="delimSelect" onchange="toggleCustom(this.value)"
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600">
                    <?php foreach ($delimOptions as $val => $label): ?>
                        <option value="<?= htmlspecialchars($val) ?>" <?= $delimiter === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="custom_delim" id="customDelimInput"
                       value="<?= htmlspecialchars($customDelim) ?>"
                       placeholder="e.g.  ::  or  ~"
                       class="mt-2 w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 placeholder-zinc-600 focus:outline-none focus:ring-1 focus:ring-red-600 <?= $delimiter === 'custom' ? '' : 'hidden' ?>">
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Capitalisation</label>
                <div class="grid grid-cols-2 gap-y-2 gap-x-4">
                    <?php foreach (['random' => 'Random caps', 'lower' => 'all lowercase', 'upper' => 'ALL UPPERCASE', 'title' => 'Title Case', 'camel' => 'camelCase'] as $val => $label): ?>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="case_mode" value="<?= $val ?>" <?= $caseMode === $val ? 'checked' : '' ?>
                                   class="accent-red-600">
                            <span class="text-sm text-zinc-300 font-mono"><?= htmlspecialchars($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">Append</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="append_num" <?= $appendNum ? 'checked' : '' ?> class="accent-red-600">
                        <span class="text-sm text-zinc-300">Random 2-digit number</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="append_sym" <?= $appendSym ? 'checked' : '' ?> class="accent-red-600">
                        <span class="text-sm text-zinc-300">Random symbol  ( ! @ # $ … )</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm text-zinc-400 mb-2">How many</label>
                <input type="number" name="quantity" min="1" max="20" value="<?= $quantity ?>"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600">
            </div>
        </div>

        <button type="button" id="rerollBtn"
                class="w-full py-3.5 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-lg tracking-tight transition-all">
            Re-roll
        </button>
    </form>

    <div class="lg:col-span-3">
        <div id="phraseList" class="space-y-2"></div>
        <div class="mt-3 text-xs text-zinc-700 font-mono flex flex-wrap gap-x-4 gap-y-1">
            <span>pool: <?= count($WORDS) ?> words</span>
            <span id="entropySummary"></span>
            <span>click a passphrase to select all</span>
        </div>
    </div>
</div>

<script>
const WORDS   = <?= json_encode($WORDS) ?>;
const SYMBOLS = ['!','@','#','$','%','^','&','*'];
const POOL    = WORDS.length;

function rand(n) {
    const buf = new Uint32Array(1);
    crypto.getRandomValues(buf);
    return buf[0] % n;
}

function getSettings() {
    const f = document.getElementById('phraseForm');
    return {
        count:       Math.max(2, Math.min(10, parseInt(f.word_count.value, 10) || 4)),
        delimiter:   f.delimiter.value,
        customDelim: f.custom_delim.value,
        caseMode:    f.case_mode.value,
        appendNum:   f.append_num.checked,
        appendSym:   f.append_sym.checked,
        quantity:    Math.max(1, Math.min(20, parseInt(f.quantity.value, 10) || 5)),
    };
}

function applyCase(word, mode, isFirst) {
    const lower = word.toLowerCase();
    if (mode === 'upper')  return word.toUpperCase();
    if (mode === 'title')  return lower.charAt(0).toUpperCase() + lower.slice(1);
    if (mode === 'camel')  return isFirst ? lower : lower.charAt(0).toUpperCase() + lower.slice(1);
    if (mode === 'random') return rand(2) ? lower.charAt(0).toUpperCase() + lower.slice(1) : lower;
    return lower;
}

function buildPhrase(s) {
    const want = Math.min(s.count, POOL);
    const picked = [];
    const used = new Set();
    while (picked.length < want) {
        const i = rand(POOL);
        if (used.has(i)) continue;
        used.add(i);
        picked.push(applyCase(WORDS[i], s.caseMode, picked.length === 0));
    }

    let phrase;
    if (s.delimiter === 'symbol' || s.delimiter === 'number') {
        const parts = [];
        const sep = s.delimiter === 'symbol'
            ? () => SYMBOLS[rand(SYMBOLS.length)]
            : () => String(rand(10));
        picked.forEach((w, j) => {
            parts.push(w);
            if (j < picked.length - 1) parts.push(sep());
        });
        phrase = parts.join('');
    } else if (s.delimiter === 'custom') {
        phrase = picked.join(s.customDelim);
    } else {
        phrase = picked.join(s.delimiter);
    }

    if (s.appendNum) phrase += String(10 + rand(90));
    if (s.appendSym) phrase += SYMBOLS[rand(SYMBOLS.length)];

    return phrase;
}

function entropyBits(s) {
    let bits = s.count * Math.log2(POOL);
    if (s.delimiter === 'number') bits += (s.count - 1) * Math.log2(10);
    if (s.delimiter === 'symbol') bits += (s.count - 1) * Math.log2(SYMBOLS.length);
    if (s.caseMode === 'random')  bits += s.count;
    if (s.appendNum) bits += Math.log2(90);
    if (s.appendSym) bits += Math.log2(SYMBOLS.length);
    return Math.round(bits);
}

function strengthLabel(bits) {
    if (bits < 40) return { label: 'weak',      cls: 'text-yellow-600' };
    if (bits < 60) return { label: 'okay',      cls: 'text-zinc-400'   };
    if (bits < 80) return { label: 'strong',    cls: 'text-emerald-500'};
    return            { label: 'excellent', cls: 'text-emerald-400'};
}

function render() {
    const s = getSettings();
    const list = document.getElementById('phraseList');
    const phrases = [];
    for (let i = 0; i < s.quantity; i++) phrases.push(buildPhrase(s));

    list.innerHTML = phrases.map(p => `
        <div class="rounded-xl border border-zinc-800 px-4 py-3 flex items-center gap-3" style="background:#111113;">
            <code class="flex-1 font-mono text-sm text-zinc-100 break-all select-all tracking-wide">${escHtml(p)}</code>
            <button type="button" data-phrase="${escHtml(p)}"
                    class="phrase-copy shrink-0 px-3 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-500 hover:text-zinc-300 transition-colors font-mono">
                copy
            </button>
        </div>
    `).join('');

    const bits = entropyBits(s);
    const st = strengthLabel(bits);
    document.getElementById('entropySummary').innerHTML =
        `~${bits} bits <span class="${st.cls}">${st.label}</span>`;
}

function toggleCustom(val) {
    const el = document.getElementById('customDelimInput');
    el.classList.toggle('hidden', val !== 'custom');
    if (val === 'custom') el.focus();
    render();
}

function copyText(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'copied';
        btn.classList.add('text-red-500', 'border-red-900');
        setTimeout(() => {
            btn.textContent = orig;
            btn.classList.remove('text-red-500', 'border-red-900');
        }, 1500);
    });
}

document.getElementById('phraseList').addEventListener('click', e => {
    const btn = e.target.closest('.phrase-copy');
    if (btn) copyText(btn, btn.dataset.phrase);
});
document.getElementById('phraseForm').addEventListener('input', render);
document.getElementById('phraseForm').addEventListener('change', render);
document.getElementById('rerollBtn').addEventListener('click', render);

render();
</script>

<?php include 'includes/footer.php'; ?>
