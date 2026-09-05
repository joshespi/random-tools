<?php
$pageTitle = 'Meme Builder';
include 'includes/header.php';
?>
<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">

<h1 class="text-2xl font-bold text-zinc-100 mb-1 tracking-tight">Meme Builder</h1>
<p class="text-zinc-500 mb-7 text-sm">Pick a template or upload your own image, drag text onto it, and download. Nothing is uploaded or stored — it all stays in your browser.</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    <!-- Left: canvas + templates -->
    <div class="lg:col-span-3 space-y-3">

        <div class="rounded-xl border border-zinc-800 bg-zinc-925 p-4">
            <canvas id="memeCanvas" class="w-full h-auto rounded-lg touch-none bg-black"></canvas>
        </div>

        <div class="flex flex-wrap gap-2">
            <button onclick="setAspect(1000,1000)" data-aspect="1000x1000"
                    class="aspect-btn px-3 py-1.5 border rounded-lg text-sm transition-colors bg-zinc-800 border-zinc-700 text-zinc-300">
                Square
            </button>
            <button onclick="setAspect(1200,675)" data-aspect="1200x675"
                    class="aspect-btn px-3 py-1.5 border rounded-lg text-sm transition-colors bg-zinc-800 border-zinc-700 text-zinc-300">
                Landscape
            </button>
            <button onclick="setAspect(800,1000)" data-aspect="800x1000"
                    class="aspect-btn px-3 py-1.5 border rounded-lg text-sm transition-colors bg-zinc-800 border-zinc-700 text-zinc-300">
                Portrait
            </button>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden bg-zinc-925">
            <div class="px-4 py-2.5 border-b border-zinc-800 text-xs text-zinc-600 font-medium uppercase tracking-widest">
                Templates
            </div>
            <div id="templateGrid" class="grid grid-cols-4 sm:grid-cols-5 gap-2 p-4"></div>
        </div>

        <div class="flex flex-wrap gap-2">
            <label class="cursor-pointer px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-xs text-zinc-400 transition-colors">
                Upload background
                <input type="file" accept="image/*" class="hidden" onchange="handleUpload(event)">
            </label>
            <button onclick="resetAll()"
                    class="px-3 py-1.5 bg-transparent border border-zinc-800 rounded-lg text-xs text-zinc-600 hover:text-zinc-400 transition-colors">
                Reset
            </button>
        </div>
    </div>

    <!-- Right: text layers -->
    <div class="lg:col-span-2 space-y-3">

        <div class="flex flex-wrap gap-2">
            <button onclick="addTextLayer('top')"
                    class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors">
                + Top text
            </button>
            <button onclick="addTextLayer('bottom')"
                    class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors">
                + Bottom text
            </button>
            <button onclick="addTextLayer('center')"
                    class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-sm text-zinc-300 transition-colors">
                + Text
            </button>
        </div>

        <div class="rounded-xl border border-zinc-800 overflow-hidden bg-zinc-925">
            <div class="px-4 py-2.5 border-b border-zinc-800 text-xs text-zinc-600 font-medium uppercase tracking-widest">
                Text layers
            </div>
            <div id="layerList" class="divide-y divide-zinc-800/60 max-h-52 overflow-y-auto"></div>
        </div>

        <div id="editorPanel" class="rounded-xl border border-zinc-800 bg-zinc-925 p-4"></div>

        <button onclick="downloadMeme()"
                class="w-full py-3 bg-red-700 hover:bg-red-600 active:scale-95 rounded-xl text-white font-bold text-lg tracking-tight transition-all">
            Download PNG
        </button>
    </div>
</div>

<script>
const TEMPLATES = [
    { id: 'white',   name: 'White',   type: 'color',    value: '#ffffff' },
    { id: 'black',   name: 'Black',   type: 'color',    value: '#000000' },
    { id: 'sunset',  name: 'Sunset',  type: 'gradient', stops: ['#f97316', '#db2777', '#4c1d95'] },
    { id: 'ocean',   name: 'Ocean',   type: 'gradient', stops: ['#0ea5e9', '#0369a1', '#082f49'] },
    { id: 'forest',  name: 'Forest',  type: 'gradient', stops: ['#22c55e', '#166534', '#052e14'] },
    { id: 'fire',    name: 'Fire',    type: 'gradient', stops: ['#facc15', '#ea580c', '#7f1d1d'] },
    { id: 'grid',    name: 'Grid',    type: 'pattern-grid' },
    { id: 'checker', name: 'Checker', type: 'pattern-checker' },
];

const canvas = document.getElementById('memeCanvas');
let bg = { ...TEMPLATES[0] };
let bgImage = null;
let layers = [];
let selectedId = null;
let nextId = 1;
let dragging = false;
let dragOffset = { x: 0, y: 0 };
let currentAspect = '1000x1000';

canvas.width = 1000;
canvas.height = 1000;

function templatePreviewStyle(tpl) {
    if (tpl.type === 'color') return `background:${tpl.value};`;
    if (tpl.type === 'gradient') return `background:linear-gradient(135deg, ${tpl.stops.join(', ')});`;
    if (tpl.type === 'pattern-grid') return `background-color:#18181b; background-image:linear-gradient(#3f3f46 1px, transparent 1px), linear-gradient(90deg, #3f3f46 1px, transparent 1px); background-size:8px 8px;`;
    if (tpl.type === 'pattern-checker') return `background: repeating-conic-gradient(#27272a 0% 25%, #3f3f46 0% 50%) 0 0/10px 10px;`;
    return '';
}

function renderTemplateGrid() {
    const el = document.getElementById('templateGrid');
    el.innerHTML = TEMPLATES.map(tpl => `
        <button onclick="setBackground('${tpl.id}')" title="${tpl.name}"
                class="aspect-square rounded-lg border-2 transition-colors ${bg.id === tpl.id ? 'border-red-600' : 'border-zinc-800 hover:border-zinc-600'}"
                style="${templatePreviewStyle(tpl)}"></button>
    `).join('');
}

function setBackground(id) {
    const tpl = TEMPLATES.find(t => t.id === id);
    if (!tpl) return;
    bg = { ...tpl };
    bgImage = null;
    redraw();
    renderTemplateGrid();
}

function setAspect(w, h) {
    canvas.width = w;
    canvas.height = h;
    currentAspect = w + 'x' + h;
    redraw();
    highlightAspect();
}

function highlightAspect() {
    const buttons = document.querySelectorAll('.aspect-btn');
    setActiveButton(buttons, b => b.dataset.aspect === currentAspect,
        ['bg-red-700', 'border-red-700', 'text-white'],
        ['bg-zinc-800', 'border-zinc-700', 'text-zinc-300']);
}

function drawCover(ctx, img, w, h) {
    const ir = img.width / img.height;
    const cr = w / h;
    let sx, sy, sw, sh;
    if (ir > cr) { sh = img.height; sw = sh * cr; sx = (img.width - sw) / 2; sy = 0; }
    else { sw = img.width; sh = sw / cr; sx = 0; sy = (img.height - sh) / 2; }
    ctx.drawImage(img, sx, sy, sw, sh, 0, 0, w, h);
}

function drawBackground(ctx, w, h) {
    if (bg.type === 'color') {
        ctx.fillStyle = bg.value;
        ctx.fillRect(0, 0, w, h);
    } else if (bg.type === 'gradient') {
        const grad = ctx.createLinearGradient(0, 0, w, h);
        bg.stops.forEach((c, i) => grad.addColorStop(i / (bg.stops.length - 1), c));
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);
    } else if (bg.type === 'pattern-grid') {
        ctx.fillStyle = '#18181b';
        ctx.fillRect(0, 0, w, h);
        ctx.strokeStyle = '#3f3f46';
        ctx.lineWidth = 1;
        const step = Math.max(20, Math.round(w / 25));
        for (let x = 0; x <= w; x += step) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke(); }
        for (let y = 0; y <= h; y += step) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke(); }
    } else if (bg.type === 'pattern-checker') {
        const size = Math.max(20, Math.round(w / 16));
        for (let y = 0, row = 0; y < h; y += size, row++) {
            for (let x = 0, col = 0; x < w; x += size, col++) {
                ctx.fillStyle = (row + col) % 2 === 0 ? '#27272a' : '#3f3f46';
                ctx.fillRect(x, y, size, size);
            }
        }
    } else if (bg.type === 'image' && bgImage) {
        drawCover(ctx, bgImage, w, h);
    } else {
        ctx.fillStyle = '#000000';
        ctx.fillRect(0, 0, w, h);
    }
}

function measureLayerBox(ctx, layer, w, h) {
    ctx.font = `${layer.fontSize}px Anton, Impact, sans-serif`;
    const lines = layer.text.split('\n');
    const lineHeight = layer.fontSize * 1.15;
    const totalH = lineHeight * lines.length;
    let maxW = 0;
    lines.forEach(l => { maxW = Math.max(maxW, ctx.measureText(l || ' ').width); });
    const cx = layer.xPct * w;
    const cy = layer.yPct * h;
    const boxW = maxW + 20;
    const boxH = totalH + 16;
    let boxX;
    if (layer.align === 'left') boxX = cx - 10;
    else if (layer.align === 'right') boxX = cx - boxW + 10;
    else boxX = cx - boxW / 2;
    return { x: boxX, y: cy - boxH / 2, w: boxW, h: boxH };
}

function drawLayers(ctx, w, h, forExport) {
    layers.forEach(layer => {
        ctx.font = `${layer.fontSize}px Anton, Impact, sans-serif`;
        ctx.textAlign = layer.align;
        ctx.textBaseline = 'middle';
        const lines = layer.text.split('\n');
        const lineHeight = layer.fontSize * 1.15;
        const totalH = lineHeight * (lines.length - 1);
        const x = layer.xPct * w;
        const yStart = layer.yPct * h - totalH / 2;
        lines.forEach((line, i) => {
            const y = yStart + i * lineHeight;
            if (layer.stroke) {
                ctx.lineWidth = Math.max(2, layer.fontSize / 9);
                ctx.strokeStyle = layer.strokeColor;
                ctx.lineJoin = 'round';
                ctx.strokeText(line, x, y);
            }
            ctx.fillStyle = layer.color;
            ctx.fillText(line, x, y);
        });
        if (!forExport && layer.id === selectedId) {
            const box = measureLayerBox(ctx, layer, w, h);
            ctx.save();
            ctx.setLineDash([6, 4]);
            ctx.strokeStyle = '#dc2626';
            ctx.lineWidth = 1.5;
            ctx.strokeRect(box.x, box.y, box.w, box.h);
            ctx.restore();
        }
    });
}

function redraw(forExport) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawBackground(ctx, canvas.width, canvas.height);
    drawLayers(ctx, canvas.width, canvas.height, forExport);
}

function hitTest(ctx, px, py, w, h) {
    for (let i = layers.length - 1; i >= 0; i--) {
        const box = measureLayerBox(ctx, layers[i], w, h);
        if (px >= box.x && px <= box.x + box.w && py >= box.y && py <= box.y + box.h) return layers[i];
    }
    return null;
}

function getCanvasPos(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: (e.clientX - rect.left) * (canvas.width / rect.width),
        y: (e.clientY - rect.top) * (canvas.height / rect.height),
    };
}

function onPointerDown(e) {
    const pos = getCanvasPos(e);
    const ctx = canvas.getContext('2d');
    const hit = hitTest(ctx, pos.x, pos.y, canvas.width, canvas.height);
    if (hit) {
        selectedId = hit.id;
        dragging = true;
        dragOffset.x = pos.x - hit.xPct * canvas.width;
        dragOffset.y = pos.y - hit.yPct * canvas.height;
        canvas.setPointerCapture(e.pointerId);
    } else {
        selectedId = null;
    }
    redraw();
    renderLayerList();
    updateEditorPanel();
}

function onPointerMove(e) {
    if (!dragging) return;
    const layer = layers.find(l => l.id === selectedId);
    if (!layer) return;
    const pos = getCanvasPos(e);
    layer.xPct = Math.min(1, Math.max(0, (pos.x - dragOffset.x) / canvas.width));
    layer.yPct = Math.min(1, Math.max(0, (pos.y - dragOffset.y) / canvas.height));
    redraw();
}

function onPointerUp() {
    dragging = false;
}

canvas.addEventListener('pointerdown', onPointerDown);
canvas.addEventListener('pointermove', onPointerMove);
canvas.addEventListener('pointerup', onPointerUp);
canvas.addEventListener('pointercancel', onPointerUp);

function addTextLayer(preset) {
    const presets = {
        top:    { text: 'TOP TEXT',    xPct: 0.5, yPct: 0.08 },
        bottom: { text: 'BOTTOM TEXT', xPct: 0.5, yPct: 0.92 },
        center: { text: 'TEXT',        xPct: 0.5, yPct: 0.5 },
    };
    const p = presets[preset] || presets.center;
    const layer = {
        id: String(nextId++),
        text: p.text,
        xPct: p.xPct,
        yPct: p.yPct,
        fontSize: Math.round(canvas.height * 0.09),
        color: '#ffffff',
        stroke: true,
        strokeColor: '#000000',
        align: 'center',
    };
    layers.push(layer);
    selectedId = layer.id;
    redraw();
    renderLayerList();
    updateEditorPanel();
}

function selectLayer(id) {
    selectedId = id;
    redraw();
    renderLayerList();
    updateEditorPanel();
}

function deleteLayer(id) {
    layers = layers.filter(l => l.id !== id);
    if (selectedId === id) selectedId = null;
    redraw();
    renderLayerList();
    updateEditorPanel();
}

function updateLayer(field, value) {
    const layer = layers.find(l => l.id === selectedId);
    if (!layer) return;
    if (field === 'fontSize') layer.fontSize = parseInt(value, 10) || layer.fontSize;
    else layer[field] = value;
    redraw();
    if (field === 'text') renderLayerList();
}

function renderLayerList() {
    const el = document.getElementById('layerList');
    if (layers.length === 0) {
        el.innerHTML = '<div class="px-4 py-4 text-zinc-600 text-sm">No text yet — add some above.</div>';
        return;
    }
    el.innerHTML = layers.map(l => `
        <div onclick="selectLayer('${l.id}')"
             class="flex items-center gap-2 px-4 py-2.5 cursor-pointer transition-colors ${l.id === selectedId ? 'bg-zinc-800/60' : 'hover:bg-zinc-800/30'}">
            <span class="flex-1 text-sm text-zinc-200 truncate">${escHtml(l.text.split('\n')[0] || '(empty)')}</span>
            <button onclick="event.stopPropagation(); deleteLayer('${l.id}')"
                    class="w-7 h-7 flex items-center justify-center text-zinc-700 hover:text-red-500 hover:bg-red-950/30 rounded transition-colors text-sm">
                &#x2715;
            </button>
        </div>
    `).join('');
}

function updateEditorPanel() {
    const el = document.getElementById('editorPanel');
    const layer = layers.find(l => l.id === selectedId);
    if (!layer) {
        el.innerHTML = '<p class="text-zinc-600 text-sm">Select or add a text layer to edit it. Drag text directly on the canvas to reposition.</p>';
        return;
    }
    el.innerHTML = `
        <div class="space-y-3">
            <div>
                <label class="text-xs text-zinc-600 font-medium uppercase tracking-widest">Text</label>
                <textarea rows="2" oninput="updateLayer('text', this.value)"
                          class="mt-1 w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:ring-1 focus:ring-red-600 resize-none">${escHtml(layer.text)}</textarea>
            </div>
            <div class="flex items-center gap-3">
                <label class="text-xs text-zinc-600 font-medium uppercase tracking-widest w-16 shrink-0">Size</label>
                <input type="range" min="16" max="200" value="${layer.fontSize}" oninput="updateLayer('fontSize', this.value)" class="flex-1">
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-xs text-zinc-500">
                    <span>Fill</span>
                    <input type="color" value="${layer.color}" oninput="updateLayer('color', this.value)" class="w-8 h-8 rounded bg-zinc-800 border border-zinc-700 cursor-pointer">
                </label>
                <label class="flex items-center gap-2 text-xs text-zinc-500">
                    <input type="checkbox" ${layer.stroke ? 'checked' : ''} onchange="updateLayer('stroke', this.checked)" class="accent-red-600">
                    <span>Outline</span>
                    <input type="color" value="${layer.strokeColor}" oninput="updateLayer('strokeColor', this.value)" class="w-8 h-8 rounded bg-zinc-800 border border-zinc-700 cursor-pointer">
                </label>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-600 font-medium uppercase tracking-widest mr-1">Align</span>
                ${['left', 'center', 'right'].map(a => `
                    <button onclick="updateLayer('align', '${a}')"
                            class="px-3 py-1 rounded-lg text-xs border transition-colors ${layer.align === a ? 'bg-red-700 border-red-700 text-white' : 'bg-zinc-800 border-zinc-700 text-zinc-400 hover:text-zinc-200'}">
                        ${a}
                    </button>
                `).join('')}
            </div>
            <button onclick="deleteLayer('${layer.id}')"
                    class="w-full px-3 py-1.5 bg-transparent hover:bg-red-950/40 border border-zinc-800 hover:border-red-900 rounded-lg text-sm text-red-700 hover:text-red-500 transition-colors">
                Delete text
            </button>
        </div>`;
}

function handleUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('Please choose an image file.');
        event.target.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            bgImage = img;
            bg = { id: null, type: 'image' };
            redraw();
            renderTemplateGrid();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
    event.target.value = '';
}

function resetAll() {
    if (!confirm('Clear all text and reset the background?')) return;
    bg = { ...TEMPLATES[0] };
    bgImage = null;
    layers = [];
    selectedId = null;
    redraw();
    renderTemplateGrid();
    renderLayerList();
    updateEditorPanel();
}

function downloadMeme() {
    redraw(true);
    canvas.toBlob(blob => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'meme.png';
        a.click();
        setTimeout(() => URL.revokeObjectURL(url), 100);
        redraw();
    }, 'image/png');
}

renderTemplateGrid();
highlightAspect();
renderLayerList();
updateEditorPanel();
redraw();
if (document.fonts) {
    document.fonts.load('160px Anton').then(redraw).catch(() => {});
}
</script>

<?php include 'includes/footer.php'; ?>
