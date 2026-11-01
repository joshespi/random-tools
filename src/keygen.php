<?php
// Presets fill the form; editing any field afterwards flips the preset to "custom".
$presets = [
    'laravel'    => ['label' => 'Laravel APP_KEY (AES-256)', 'bytes' => 32, 'encoding' => 'base64',    'prefix' => 'base64:'],
    'laravel128' => ['label' => 'Laravel APP_KEY (AES-128)', 'bytes' => 16, 'encoding' => 'base64',    'prefix' => 'base64:'],
    'hex'        => ['label' => 'Hex secret (256-bit)',      'bytes' => 32, 'encoding' => 'hex',       'prefix' => ''],
    'jwt'        => ['label' => 'JWT HS512 secret',          'bytes' => 64, 'encoding' => 'base64url', 'prefix' => ''],
    'uuid'       => ['label' => 'UUID v4',                   'bytes' => 16, 'encoding' => 'uuid',      'prefix' => ''],
    'custom'     => ['label' => 'Custom',                    'bytes' => 32, 'encoding' => 'base64',    'prefix' => ''],
];
$encodings = ['base64' => 'Base64', 'base64url' => 'Base64 URL-safe', 'hex' => 'Hex', 'uuid' => 'UUID v4'];
$first = $presets['laravel'];

$field = 'w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-red-600';
include 'includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <form id="keyForm" class="lg:col-span-2 space-y-3" onsubmit="event.preventDefault();">
        <div class="rounded-xl p-5 border border-zinc-800 space-y-5 bg-zinc-925">
            <div class="text-xs text-zinc-500 font-medium uppercase tracking-widest">Options</div>

            <div>
                <label for="preset" class="block text-sm text-zinc-400 mb-2">Preset</label>
                <select id="preset" name="preset" class="<?= $field ?>">
                    <?php foreach ($presets as $id => $p): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($p['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="encoding" class="block text-sm text-zinc-400 mb-2">Encoding</label>
                <select id="encoding" name="encoding" class="<?= $field ?>">
                    <?php foreach ($encodings as $id => $label): ?>
                        <option value="<?= $id ?>" <?= $first['encoding'] === $id ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="bytesRow">
                <label for="bytes" class="block text-sm text-zinc-400 mb-2">Random bytes</label>
                <input type="number" id="bytes" name="bytes" min="1" max="256" value="<?= $first['bytes'] ?>" class="<?= $field ?>">
            </div>

            <div>
                <label for="prefix" class="block text-sm text-zinc-400 mb-2">Prefix <span class="text-zinc-600">(optional)</span></label>
                <input type="text" id="prefix" name="prefix" value="<?= htmlspecialchars($first['prefix']) ?>"
                       placeholder="e.g. base64: or sk_" class="<?= $field ?>">
            </div>

            <div>
                <label for="quantity" class="block text-sm text-zinc-400 mb-2">How many</label>
                <input type="number" id="quantity" name="quantity" min="1" max="20" value="1" class="<?= $field ?>">
            </div>
        </div>

        <button type="button" id="rerollBtn"
                class="w-full py-3.5 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-lg tracking-tight transition-all">
            Generate
        </button>
    </form>

    <div class="lg:col-span-3 order-first lg:order-none">
        <div id="keyList" class="space-y-2"></div>
        <div class="mt-3 text-xs text-zinc-500 font-mono flex flex-wrap gap-x-4 gap-y-1">
            <span id="bitsSummary"></span>
            <span>generated in your browser, never sent anywhere</span>
        </div>
    </div>
</div>

<script>
const PRESETS = <?= json_encode($presets) ?>;
const form = document.getElementById('keyForm');

function encode(buf, encoding) {
    if (encoding === 'hex') return Array.from(buf, b => b.toString(16).padStart(2, '0')).join('');
    if (encoding === 'uuid') {
        buf[6] = (buf[6] & 0x0f) | 0x40;
        buf[8] = (buf[8] & 0x3f) | 0x80;
        const h = encode(buf, 'hex');
        return `${h.slice(0, 8)}-${h.slice(8, 12)}-${h.slice(12, 16)}-${h.slice(16, 20)}-${h.slice(20)}`;
    }
    const b64 = btoa(String.fromCharCode(...buf));
    return encoding === 'base64url' ? b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '') : b64;
}

function render() {
    const encoding = form.encoding.value;
    const isUuid = encoding === 'uuid';
    const bytes = isUuid ? 16 : Math.max(1, Math.min(256, parseInt(form.bytes.value, 10) || 32));
    const qty = Math.max(1, Math.min(20, parseInt(form.quantity.value, 10) || 1));
    const keys = Array.from({ length: qty }, () =>
        form.prefix.value + encode(crypto.getRandomValues(new Uint8Array(bytes)), encoding));

    document.getElementById('bytesRow').classList.toggle('hidden', isUuid);
    document.getElementById('bitsSummary').textContent = `${isUuid ? 122 : bytes * 8} bits of randomness`;
    document.getElementById('keyList').innerHTML = keys.map(k => `
        <div class="rounded-xl border border-zinc-800 px-4 py-3 flex items-center gap-3 bg-zinc-925">
            <code class="flex-1 font-mono text-sm text-zinc-100 break-all select-all">${escHtml(k)}</code>
            <button type="button" data-key="${escHtml(k)}"
                    class="key-copy shrink-0 px-3 py-1 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-500 hover:text-zinc-300 transition-colors font-mono">
                copy
            </button>
        </div>
    `).join('');
}

function copyText(btn, text) {
    const flash = (label, cls) => {
        const orig = btn.textContent;
        btn.textContent = label;
        btn.classList.add(...cls);
        setTimeout(() => {
            btn.textContent = orig;
            btn.classList.remove(...cls);
        }, 1500);
    };
    copyToClipboard(text)
        .then(() => flash('copied', ['text-red-500', 'border-red-900']))
        .catch(() => flash('press Ctrl+C', ['text-yellow-600']));
}

form.addEventListener('input', e => {
    if (e.target === form.preset) {
        // "Custom" keeps whatever is currently entered.
        if (form.preset.value !== 'custom') {
            const p = PRESETS[form.preset.value];
            form.encoding.value = p.encoding;
            form.bytes.value = p.bytes;
            form.prefix.value = p.prefix;
        }
    } else if (['encoding', 'bytes', 'prefix'].includes(e.target.name)) {
        form.preset.value = 'custom';
    }
    render();
});
document.getElementById('keyList').addEventListener('click', e => {
    const btn = e.target.closest('.key-copy');
    if (btn) copyText(btn, btn.dataset.key);
});
document.getElementById('rerollBtn').addEventListener('click', render);

render();
</script>

<?php include 'includes/footer.php'; ?>
