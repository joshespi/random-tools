<?php
$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="pt-10 pb-12">
    <h1 class="text-4xl font-extrabold text-zinc-100 tracking-tight mb-2">Espi's Tools</h1>
    <p class="text-zinc-500 text-base max-w-lg">A small collection of randomization utilities.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

    <a href="/picker.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all bg-zinc-925">
        <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Weighted Picker</h2>
        <p class="text-zinc-500 text-sm leading-relaxed">Add options, assign weights, pick. Saves named sets locally with import/export.</p>
    </a>

    <a href="/passphrase.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all bg-zinc-925">
        <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Passphrase Generator</h2>
        <p class="text-zinc-500 text-sm leading-relaxed">Word-based passphrases with configurable count, delimiter, casing, and appended digits or symbols.</p>
    </a>

    <a href="/tip.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all bg-zinc-925">
        <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Tip Calculator</h2>
        <p class="text-zinc-500 text-sm leading-relaxed">Bill amount, tip percentage, and split. Shows tip, total, and per-person breakdown.</p>
    </a>

    <a href="/dice.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all bg-zinc-925">
        <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Dice Roller</h2>
        <p class="text-zinc-500 text-sm leading-relaxed">Roll d4–d100, any count, with a flat modifier. Animated tumble and roll history.</p>
    </a>

    <a href="/coin.php" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all bg-zinc-925">
        <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors">Coin Flip</h2>
        <p class="text-zinc-500 text-sm leading-relaxed">Animated heads-or-tails flip with streak tracking and flip history.</p>
    </a>

</div>

<?php include 'includes/footer.php'; ?>
