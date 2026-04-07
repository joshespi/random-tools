<?php
$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="pt-10 pb-12">
    <h1 class="text-4xl font-extrabold text-zinc-100 tracking-tight mb-2">Espi's Tools</h1>
    <p class="text-zinc-500 text-base max-w-lg">A small collection of randomization utilities. Pick a tool.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

    <a href="/picker.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all" style="background:#111113;">
        <div class="flex items-start gap-4">
            <span class="text-red-700 text-2xl leading-none mt-0.5 select-none">&#9670;</span>
            <div>
                <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Weighted Picker</h2>
                <p class="text-zinc-500 text-sm leading-relaxed">Add options, assign weights, and pick. Great for lunch, movies, any decision you want biased by preference. Saves named sets locally with import/export.</p>
            </div>
        </div>
    </a>

    <a href="/passphrase.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all" style="background:#111113;">
        <div class="flex items-start gap-4">
            <span class="text-red-700 text-2xl leading-none mt-0.5 select-none">&#9670;</span>
            <div>
                <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Passphrase Generator</h2>
                <p class="text-zinc-500 text-sm leading-relaxed">Generate memorable word-based passphrases. Control word count, delimiter style, capitalisation, and whether to append numbers or symbols.</p>
            </div>
        </div>
    </a>

    <a href="/dice.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all" style="background:#111113;">
        <div class="flex items-start gap-4">
            <span class="text-red-700 text-2xl leading-none mt-0.5 select-none">&#9670;</span>
            <div>
                <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Dice Roller</h2>
                <p class="text-zinc-500 text-sm leading-relaxed">Roll d4 through d100, or any custom die. Roll multiple at once and see individual results and totals with a running history.</p>
            </div>
        </div>
    </a>

    <a href="/number.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all" style="background:#111113;">
        <div class="flex items-start gap-4">
            <span class="text-red-700 text-2xl leading-none mt-0.5 select-none">&#9670;</span>
            <div>
                <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Random Numbers</h2>
                <p class="text-zinc-500 text-sm leading-relaxed">Generate one or many random integers in a range. Optional no-duplicates mode, sorting, quick presets, and copy as CSV.</p>
            </div>
        </div>
    </a>

</div>

<?php include 'includes/footer.php'; ?>
