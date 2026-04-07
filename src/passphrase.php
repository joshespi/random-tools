<?php
$pageTitle = 'Passphrase';
include 'includes/words.php';

$wordCount   = max(2, min(10, (int)($_POST['word_count']  ?? 4)));
$delimiter   = $_POST['delimiter']   ?? '-';
$customDelim = $_POST['custom_delim'] ?? '';
$caseMode    = $_POST['case_mode']   ?? 'lower';
$appendNum   = isset($_POST['append_num']);
$appendSym   = isset($_POST['append_sym']);
$quantity    = max(1, min(20, (int)($_POST['quantity'] ?? 5)));

$delimOptions = [
    '-'      => 'Dash  —  word-word',
    '_'      => 'Underscore  —  word_word',
    '.'      => 'Dot  —  word.word',
    ' '      => 'Space  —  word word',
    ''       => 'None  —  wordword',
    'number' => 'Digit between  —  word3word',
    'custom' => 'Custom…',
];

$symbols = ['!', '@', '#', '$', '%', '^', '&', '*'];

function buildPassphrase(array $words, int $count, string $delim, string $customDelim,
                         string $caseMode, bool $appendNum, bool $appendSym, array $symbols): string
{
    $pool = $words;
    shuffle($pool);
    $picked = array_slice($pool, 0, min($count, count($pool)));

    $picked = array_map(function($w) use ($caseMode) {
        return match($caseMode) {
            'upper' => strtoupper($w),
            'title' => ucfirst($w),
            'camel' => ucfirst($w),
            default => strtolower($w),
        };
    }, $picked);

    if ($caseMode === 'camel') {
        $picked[0] = strtolower($picked[0]);
        for ($i = 1; $i < count($picked); $i++) $picked[$i] = ucfirst($picked[$i]);
    }

    if ($delim === 'number') {
        $parts = [];
        foreach ($picked as $j => $w) {
            $parts[] = $w;
            if ($j < count($picked) - 1) $parts[] = (string)random_int(0, 9);
        }
        $phrase = implode('', $parts);
    } elseif ($delim === 'custom') {
        $phrase = implode($customDelim, $picked);
    } else {
        $phrase = implode($delim, $picked);
    }

    if ($appendNum) $phrase .= random_int(10, 99);
    if ($appendSym) $phrase .= $symbols[array_rand($symbols)];

    return $phrase;
}

$results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 0; $i < $quantity; $i++) {
        $results[] = buildPassphrase(
            $WORDS, $wordCount, $delimiter, $customDelim,
            $caseMode, $appendNum, $appendSym, $symbols
        );
    }
}

include 'includes/header.php';
?>

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Passphrase Generator</h1>
<p class="text-zinc-500 mb-7 text-sm">Generate memorable word-based passphrases. Adjust options and click Generate.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Settings form -->
    <form method="POST" class="lg:col-span-2 space-y-3">
        <div class="rounded-xl p-5 border border-zinc-800 space-y-5" style="background:#111113;">

            <div class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Options</div>

            <!-- Word count -->
            <div>
                <div class="flex justify-between mb-2">
                    <label class="text-sm text-zinc-400">Word count</label>
                    <span id="wcLabel" class="text-sm font-mono text-red-500"><?= $wordCount ?></span>
                </div>
                <input type="range" name="word_count" min="2" max="10" value="<?= $wordCount ?>"
                       oninput="document.getElementById('wcLabel').textContent = this.value"
                       class="w-full">
            </div>

            <!-- Delimiter -->
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

            <!-- Case -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Capitalisation</label>
                <div class="grid grid-cols-2 gap-y-2 gap-x-4">
                    <?php foreach (['lower' => 'all lowercase', 'upper' => 'ALL UPPERCASE', 'title' => 'Title Case', 'camel' => 'camelCase'] as $val => $label): ?>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="case_mode" value="<?= $val ?>" <?= $caseMode === $val ? 'checked' : '' ?>
                                   class="accent-red-600">
                            <span class="text-sm text-zinc-300 font-mono"><?= htmlspecialchars($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Extras -->
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

            <!-- Quantity -->
            <div>
                <label class="block text-sm text-zinc-400 mb-2">How many</label>
                <input type="number" name="quantity" min="1" max="20" value="<?= $quantity ?>"
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600">
            </div>
        </div>

        <button type="submit"
                class="w-full py-3.5 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-lg tracking-tight transition-all">
            Generate
        </button>
    </form>

    <!-- Results -->
    <div class="lg:col-span-3">
        <?php if (empty($results)): ?>
            <div class="rounded-xl border border-zinc-800 p-10 text-center text-zinc-600 text-sm" style="background:#111113;">
                Results will appear here.
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($results as $phrase): ?>
                    <div class="rounded-xl border border-zinc-800 px-4 py-3 flex items-center gap-3" style="background:#111113;">
                        <code class="flex-1 font-mono text-sm text-zinc-100 break-all select-all tracking-wide">
                            <?= htmlspecialchars($phrase) ?>
                        </code>
                        <button onclick="copyText(this, <?= htmlspecialchars(json_encode($phrase)) ?>)"
                                class="shrink-0 px-3 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-500 hover:text-zinc-300 transition-colors font-mono">
                            copy
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 text-xs text-zinc-700 font-mono">
                pool: <?= count($WORDS) ?> words &nbsp;&mdash;&nbsp; click a passphrase to select all
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleCustom(val) {
    const el = document.getElementById('customDelimInput');
    el.classList.toggle('hidden', val !== 'custom');
    if (val === 'custom') el.focus();
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
</script>

<?php include 'includes/footer.php'; ?>
